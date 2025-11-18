<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";

// Proses Approve / Reject
if (isset($_POST['action']) && isset($_POST['id_transaksi'])) {
    header('Content-Type: application/json');
    $id_trx = $_POST['id_transaksi'];
    $action = $_POST['action']; // approve atau reject

    try {
        $con->begin_transaction();

        if ($action === 'approve') {
            $stmt = $con->prepare("SELECT id_member, id_paket, total FROM tbl_transaksi_online WHERE id_transaksi = ? AND status = 'pending'");
            $stmt->bind_param("s", $id_trx);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Transaksi tidak ditemukan atau sudah diproses");
            }
            $trx = $result->fetch_assoc();
            $stmt->close();

            $id_member = $trx['id_member'];
            $id_paket  = $trx['id_paket'];

            // Ambil durasi paket
            $stmt = $con->prepare("SELECT durasi_hari FROM tbl_paket WHERE id_paket = ?");
            $stmt->bind_param("i", $id_paket);
            $stmt->execute();
            $durasi = $stmt->get_result()->fetch_assoc()['durasi_hari'] ?? 30;
            $stmt->close();

            // Hitung tanggal berakhir
            $tgl_mulai = date('Y-m-d H:i:s');
            $tgl_berakhir = date('Y-m-d 23:59:59', strtotime("+$durasi days"));

            // Insert ke tbl_membership
            $stmt = $con->prepare("INSERT INTO tbl_membership (id_member, id_transaksi, id_paket, tgl_mulai, tgl_berakhir, sumber) VALUES (?, ?, ?, ?, ?, 'online')");
            $stmt->bind_param("isiss", $id_member, $id_trx, $id_paket, $tgl_mulai, $tgl_berakhir);
            $stmt->execute();
            $stmt->close();

            // Update status member menjadi aktif
            $stmt = $con->prepare("UPDATE tbl_member SET membership_status = 'aktif' WHERE id_member = ?");
            $stmt->bind_param("i", $id_member);
            $stmt->execute();
            $stmt->close();

            // Update status transaksi online
            $stmt = $con->prepare("UPDATE tbl_transaksi_online SET status = 'approved', admin_verifikasi = ?, tgl_verifikasi = NOW() WHERE id_transaksi = ?");
            $stmt->bind_param("is", $_SESSION['id_user'], $id_trx);
            $stmt->execute();
            $stmt->close();

            $con->commit();
            echo json_encode(['success' => true, 'msg' => 'Transaksi berhasil disetujui & membership diaktifkan!']);
        } elseif ($action === 'reject') {
            $stmt = $con->prepare("UPDATE tbl_transaksi_online SET status = 'rejected', admin_verifikasi = ?, tgl_verifikasi = NOW() WHERE id_transaksi = ? AND status = 'pending'");
            $stmt->bind_param("is", $_SESSION['id_user'], $id_trx);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Transaksi tidak ditemukan atau sudah diproses");
            }
            $stmt->close();

            $con->commit();
            echo json_encode(['success' => true, 'msg' => 'Transaksi berhasil ditolak']);
        }
    } catch (Exception $e) {
        $con->rollback();
        echo json_encode(['success' => false, 'msg' => 'Gagal: ' . $e->getMessage()]);
    }
    exit;
}

// Filter status
$status_filter = $_GET['status'] ?? 'pending';
$valid_statuses = ['pending', 'approved', 'rejected', 'all'];
$status_filter = in_array($status_filter, $valid_statuses) ? $status_filter : 'pending';

// Query untuk menghitung statistik
$stats_sql = "SELECT 
    status,
    COUNT(*) as count,
    SUM(total) as total_amount
FROM tbl_transaksi_online 
GROUP BY status";

$stats_result = $con->query($stats_sql);
$stats = [
    'pending' => ['count' => 0, 'total' => 0],
    'approved' => ['count' => 0, 'total' => 0],
    'rejected' => ['count' => 0, 'total' => 0],
    'all' => ['count' => 0, 'total' => 0]
];

while ($row = $stats_result->fetch_assoc()) {
    $stats[$row['status']] = [
        'count' => $row['count'],
        'total' => $row['total_amount']
    ];
    $stats['all']['count'] += $row['count'];
    $stats['all']['total'] += $row['total_amount'];
}

// Query data transaksi berdasarkan filter
$where_clause = "";
if ($status_filter !== 'all') {
    $where_clause = "WHERE ton.status = '$status_filter'";
}

$sql = "SELECT 
            ton.*, 
            m.nama AS nama_member, 
            m.email,
            p.nama_paket, 
            p.durasi_hari,
            p.harga_umum,
            p.harga_mahasiswa,
            u.username AS admin_verifikator
        FROM tbl_transaksi_online ton
        JOIN tbl_member m ON ton.id_member = m.id_member
        JOIN tbl_paket p ON ton.id_paket = p.id_paket
        LEFT JOIN tbl_user u ON ton.admin_verifikasi = u.id_user
        $where_clause
        ORDER BY ton.tgl_transaksi DESC";

$transaksi = $con->query($sql);
?>

<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-credit-card"></i> Verifikasi Pembayaran Online</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Admin</a></li>
                    <li class="breadcrumb-item active">Verifikasi Pembayaran</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Statistik Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="info-box bg-gradient-info">
                    <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Menunggu</span>
                        <span class="info-box-number"><?= $stats['pending']['count'] ?></span>
                        <span class="progress-description">
                            Rp <?= number_format($stats['pending']['total'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-gradient-success">
                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Disetujui</span>
                        <span class="info-box-number"><?= $stats['approved']['count'] ?></span>
                        <span class="progress-description">
                            Rp <?= number_format($stats['approved']['total'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-gradient-danger">
                    <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Ditolak</span>
                        <span class="info-box-number"><?= $stats['rejected']['count'] ?></span>
                        <span class="progress-description">
                            Rp <?= number_format($stats['rejected']['total'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-gradient-secondary">
                    <span class="info-box-icon"><i class="fas fa-list"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total</span>
                        <span class="info-box-number"><?= $stats['all']['count'] ?></span>
                        <span class="progress-description">
                            Rp <?= number_format($stats['all']['total'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filter Status</h3>
                <div class="card-tools">
                    <span class="badge badge-light">Total: <?= $transaksi->num_rows ?> transaksi</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="btn-group">
                            <a href="?status=pending" class="btn btn-<?= $status_filter == 'pending' ? 'primary' : 'outline-primary' ?>">
                                <i class="fas fa-clock"></i> Menunggu (<?= $stats['pending']['count'] ?>)
                            </a>
                            <a href="?status=approved" class="btn btn-<?= $status_filter == 'approved' ? 'success' : 'outline-success' ?>">
                                <i class="fas fa-check-circle"></i> Disetujui (<?= $stats['approved']['count'] ?>)
                            </a>
                            <a href="?status=rejected" class="btn btn-<?= $status_filter == 'rejected' ? 'danger' : 'outline-danger' ?>">
                                <i class="fas fa-times-circle"></i> Ditolak (<?= $stats['rejected']['count'] ?>)
                            </a>
                            <a href="?status=all" class="btn btn-<?= $status_filter == 'all' ? 'secondary' : 'outline-secondary' ?>">
                                <i class="fas fa-list"></i> Semua (<?= $stats['all']['count'] ?>)
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 text-right">
                        <div class="small text-muted">
                            Ditampilkan: <strong><?= $status_filter == 'all' ? 'Semua Status' : ucfirst($status_filter) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table Card -->

        <div class="card">
            <div class="card-header bg-<?=
                                        $status_filter == 'pending' ? 'primary' : ($status_filter == 'approved' ? 'success' : ($status_filter == 'rejected' ? 'danger' : 'secondary')) ?> text-white">
                <h3 class="card-title">
                    <i class="fas fa-list"></i>
                    Daftar Transaksi -
                    <?= $status_filter == 'all' ? 'Semua Status' : ucfirst($status_filter) ?>
                </h3>
                <div class="card-tools">
                    <span class="badge badge-light badge-lg"><?= $transaksi->num_rows ?> transaksi</span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if ($transaksi->num_rows == 0): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-5x text-muted mb-3"></i>
                        <h4 class="text-muted">Tidak ada transaksi</h4>
                        <p class="text-muted">Tidak ditemukan transaksi dengan status "<?= $status_filter ?>"</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tabelPelatih" style="font-size: 0.9rem;">
                            <thead class="table-dark">
                                <tr>
                                    <th width="11%">ID TRANSAKSI</th>
                                    <th width="15%">MEMBER</th>
                                    <th width="14%">PAKET</th>
                                    <th width="9%">TOTAL</th>
                                    <th width="13%">TANGGAL</th>
                                    <th width="8%">STATUS</th>
                                    <th width="10%">VERIFIKATOR</th>
                                    <th width="9%">BUKTI</th>
                                    <th width="11%" class="text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $transaksi->fetch_assoc()): ?>
                                    <tr>
                                        <!-- ID TRANSAKSI -->
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-<?= $row['status'] == 'pending' ? 'warning' : ($row['status'] == 'approved' ? 'success' : 'danger') ?> text-dark fw-bold">
                                                    <?= htmlspecialchars($row['id_transaksi']) ?>
                                                </span>
                                                <small class="text-muted mt-1">
                                                    <?= date('d/m/Y', strtotime($row['tgl_transaksi'])) ?>
                                                </small>
                                            </div>
                                        </td>

                                        <!-- MEMBER -->
                                        <td style="white-space: normal; word-break: break-word;">
                                            <div>
                                                <strong class="text-primary d-block"><?= htmlspecialchars($row['nama_member']) ?></strong>
                                                <small class="text-muted d-block" style="font-size: 0.8rem;">
                                                    <?= htmlspecialchars($row['email']) ?>
                                                </small>
                                            </div>
                                        </td>

                                        <!-- PAKET -->
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($row['nama_paket']) ?></strong>
                                                <small class="text-muted d-block">
                                                    <?= $row['durasi_hari'] ?> hari
                                                </small>
                                            </div>
                                        </td>

                                        <!-- TOTAL -->
                                        <td>
                                            <strong class="text-success">Rp <?= number_format($row['total'], 0, ',', '.') ?></strong>
                                        </td>

                                        <!-- TANGGAL -->
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span><?= date('d/m/Y H:i', strtotime($row['tgl_transaksi'])) ?></span>
                                                <?php if ($row['tgl_verifikasi']): ?>
                                                    <small class="text-info">
                                                        Verif: <?= date('d/m/Y H:i', strtotime($row['tgl_verifikasi'])) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <!-- STATUS -->
                                        <td>
                                            <span class="badge badge-<?= $row['status'] == 'pending' ? 'warning' : ($row['status'] == 'approved' ? 'success' : 'danger') ?>">
                                                <?= strtoupper($row['status']) ?>
                                            </span>
                                        </td>

                                        <!-- VERIFIKATOR -->
                                        <td>
                                            <?php if ($row['admin_verifikator']): ?>
                                                <span class="text-success fw-bold"><?= htmlspecialchars($row['admin_verifikator']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- BUKTI -->
                                        <td>
                                            <?php
                                            $bukti_path = "../../../Uploads/bukti_pembayaran/" . $row['bukti_pembayaran'];
                                            $file_exists = file_exists($bukti_path);
                                            ?>
                                            <button class="btn btn-xs btn-info btn-preview-bukti"
                                                data-bukti="<?= $row['bukti_pembayaran'] ?>"
                                                data-id="<?= $row['id_transaksi'] ?>"
                                                <?= !$file_exists ? 'disabled' : '' ?>
                                                title="Lihat Bukti Pembayaran">
                                                <i class="fas fa-image"></i>
                                                <?= $file_exists ? 'Lihat' : 'No File' ?>
                                            </button>
                                        </td>

                                        <!-- AKSI -->
                                        <td class="text-center">
                                            <?php if ($row['status'] == 'pending'): ?>
                                                <div class="btn-group-vertical btn-group-xs">
                                                    <button class="btn btn-success btn-approve"
                                                        data-id="<?= $row['id_transaksi'] ?>"
                                                        data-member="<?= htmlspecialchars($row['nama_member']) ?>"
                                                        data-paket="<?= htmlspecialchars($row['nama_paket']) ?>"
                                                        data-total="Rp <?= number_format($row['total'], 0, ',', '.') ?>"
                                                        title="Setujui Transaksi">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-reject"
                                                        data-id="<?= $row['id_transaksi'] ?>"
                                                        data-member="<?= htmlspecialchars($row['nama_member']) ?>"
                                                        title="Tolak Transaksi">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span class="badge badge-<?= $row['status'] == 'approved' ? 'success' : 'danger' ?>">
                                                    <?= $row['status'] == 'approved' ? 'Check' : 'Cross' ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Bukti -->
<div class="modal fade" id="modalBukti" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-image"></i> Bukti Pembayaran - <span id="modalTrxId"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="imgBukti" src="" alt="Bukti Pembayaran" class="img-fluid rounded" style="max-height: 70vh;">
                <div class="mt-3">
                    <a href="#" id="downloadBukti" class="btn btn-success btn-sm" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../../view/master/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    $(document).ready(function() {
        // Preview Bukti Pembayaran
        $(document).on('click', '.btn-preview-bukti', function() {
            const buktiFile = $(this).data('bukti');
            const trxId = $(this).data('id');
            const buktiPath = '../../../Uploads/bukti_pembayaran/' + buktiFile;

            $('#modalTrxId').text('#' + trxId);
            $('#imgBukti').attr('src', buktiPath);
            $('#downloadBukti').attr('href', buktiPath);
            $('#modalBukti').modal('show');
        });

        // Approve Transaksi
        $(document).on('click', '.btn-approve', function() {
            const id = $(this).data('id');
            const member = $(this).data('member');
            const paket = $(this).data('paket');
            const total = $(this).data('total');

            Swal.fire({
                title: 'Setujui Pembayaran?',
                html: `<div class="text-left">
                    <p><strong>Detail Transaksi:</strong></p>
                    <ul>
                        <li><strong>Member:</strong> ${member}</li>
                        <li><strong>Paket:</strong> ${paket}</li>
                        <li><strong>Total:</strong> ${total}</li>
                    </ul>
                    <p class="text-success"><i class="fas fa-info-circle"></i> Membership akan langsung aktif setelah disetujui!</p>
                   </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check"></i> Ya, Setujui',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                reverseButtons: true,
                width: '600px'
            }).then((result) => {
                if (result.isConfirmed) {
                    processAction(id, 'approve');
                }
            });
        });

        // Reject Transaksi
        $(document).on('click', '.btn-reject', function() {
            const id = $(this).data('id');
            const member = $(this).data('member');

            Swal.fire({
                title: 'Tolak Pembayaran?',
                html: `<div class="text-left">
                    <p>Anda akan menolak pembayaran dari:</p>
                    <p><strong>${member}</strong></p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Transaksi akan ditandai sebagai ditolak.</p>
                   </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-times"></i> Ya, Tolak',
                cancelButtonText: '<i class="fas fa-arrow-left"></i> Batal',
                reverseButtons: true,
                width: '500px'
            }).then((result) => {
                if (result.isConfirmed) {
                    processAction(id, 'reject');
                }
            });
        });

        // Process Action
        function processAction(id, action) {
            const button = $(`.btn-${action}[data-id="${id}"]`);
            const originalHtml = button.html();

            // Disable button and show loading
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

            // Send AJAX request
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: action,
                    id_transaksi: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.msg,
                            icon: 'success',
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: response.msg,
                            icon: 'error',
                            confirmButtonColor: '#dc3545',
                            confirmButtonText: 'OK'
                        });
                        button.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat memproses permintaan.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK'
                    });
                    button.prop('disabled', false).html(originalHtml);
                }
            });
        }

        // Auto refresh every 30 seconds if there are pending transactions
        <?php if ($status_filter == 'pending' && $transaksi->num_rows > 0): ?>
            setInterval(() => {
                location.reload();
            }, 30000);
        <?php endif; ?>
    });
</script>