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

// Ambil data transaksi pending - DIPERBAIKI: Hapus JOIN ke tbl_admin
$sql = "SELECT 
            ton.*, 
            m.nama AS nama_member, 
            m.email,
            p.nama_paket, 
            p.durasi_hari,
            p.harga_umum,
            p.harga_mahasiswa
        FROM tbl_transaksi_online ton
        JOIN tbl_member m ON ton.id_member = m.id_member
        JOIN tbl_paket p ON ton.id_paket = p.id_paket
        WHERE ton.status = 'pending'
        ORDER BY ton.tgl_transaksi DESC";

$pending = $con->query($sql);
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

        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title"><i class="fas fa-list"></i> Daftar Pembayaran Menunggu Verifikasi</h3>
                <div class="card-tools">
                    <span class="badge badge-light badge-lg"><?= $pending->num_rows ?> pending</span>
                </div>
            </div>

            <div class="card-body table-responsive p-0">
                <?php if ($pending->num_rows == 0): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                        <h4 class="text-success">Tidak ada pembayaran yang menunggu verifikasi</h4>
                        <p class="text-muted">Semua transaksi online sudah diproses</p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover text-nowrap align-middle" id="tabelPelatih">
                        <thead class="table-dark">
                            <tr>
                                <th width="12%">ID Transaksi</th>
                                <th width="15%">Member</th>
                                <th width="18%">Paket</th>
                                <th width="10%">Total Bayar</th>
                                <th width="12%">Tanggal</th>
                                <th width="15%">Bukti Pembayaran</th>
                                <th width="18%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $pending->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-warning text-dark">#<?= htmlspecialchars($row['id_transaksi']) ?></span>
                                        <br>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($row['tgl_transaksi'])) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['nama_member']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['nama_paket']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= $row['durasi_hari'] ?> hari</small>
                                        <br>
                                        <small>
                                            <span class="badge bg-info">Umum: Rp <?= number_format($row['harga_umum'], 0, ',', '.') ?></span>
                                            <?php if ($row['harga_mahasiswa'] > 0): ?>
                                                <br>
                                                <span class="badge bg-success">Mahasiswa: Rp <?= number_format($row['harga_mahasiswa'], 0, ',', '.') ?></span>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong class="text-success">Rp <?= number_format($row['total'], 0, ',', '.') ?></strong>
                                        <?php if (!empty($row['catatan'])): ?>
                                            <br>
                                            <small class="text-muted" title="<?= htmlspecialchars($row['catatan']) ?>">
                                                <i class="fas fa-sticky-note"></i> ada catatan
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y H:i', strtotime($row['tgl_transaksi'])) ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php
                                            $time_diff = time() - strtotime($row['tgl_transaksi']);
                                            $hours_ago = floor($time_diff / (60 * 60));
                                            echo $hours_ago . ' jam lalu';
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $bukti_path = "../../../Uploads/bukti_pembayaran/" . $row['bukti_pembayaran'];
                                        $file_exists = file_exists($bukti_path);
                                        ?>
                                        <button class="btn btn-sm btn-info btn-preview-bukti"
                                            data-bukti="<?= $row['bukti_pembayaran'] ?>"
                                            data-id="<?= $row['id_transaksi'] ?>"
                                            <?= !$file_exists ? 'disabled' : '' ?>>
                                            <i class="fas fa-image"></i>
                                            <?= $file_exists ? 'Lihat Bukti' : 'File Tidak Ada' ?>
                                        </button>
                                        <?php if (!$file_exists): ?>
                                            <br>
                                            <small class="text-danger">File tidak ditemukan</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group-vertical btn-group-sm">
                                            <button class="btn btn-success btn-approve"
                                                data-id="<?= $row['id_transaksi'] ?>"
                                                data-member="<?= htmlspecialchars($row['nama_member']) ?>"
                                                data-paket="<?= htmlspecialchars($row['nama_paket']) ?>"
                                                data-total="Rp <?= number_format($row['total'], 0, ',', '.') ?>">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button class="btn btn-danger btn-reject"
                                                data-id="<?= $row['id_transaksi'] ?>"
                                                data-member="<?= htmlspecialchars($row['nama_member']) ?>">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
        $('.btn-preview-bukti').click(function() {
            const buktiFile = $(this).data('bukti');
            const trxId = $(this).data('id');
            const buktiPath = '../../../Uploads/bukti_pembayaran/' + buktiFile;

            $('#modalTrxId').text('#' + trxId);
            $('#imgBukti').attr('src', buktiPath);
            $('#downloadBukti').attr('href', buktiPath);
            $('#modalBukti').modal('show');
        });

        // Approve Transaksi
        $('.btn-approve').click(function() {
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
        $('.btn-reject').click(function() {
            const id = $(this).data('id');
            const member = $(this).data('member');

            Swal.fire({
                title: 'Tolak Pembayaran?',
                html: `<div class="text-left">
                    <p>Anda akan menolak pembayaran dari:</p>
                    <p><strong>${member}</strong></p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Transaksi akan ditandai sebagai ditolak dan member akan mendapatkan notifikasi.</p>
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
                            confirmButtonText: 'OK',
                            timer: 2000,
                            timerProgressBar: true
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
        <?php if ($pending->num_rows > 0): ?>
            setInterval(() => {
                $.get(window.location.href, function(data) {
                    // Simple check if page has changed
                    const newCount = $(data).find('.badge-lg').text();
                    const currentCount = $('.badge-lg').text();
                    if (newCount !== currentCount) {
                        location.reload();
                    }
                });
            }, 30000);
        <?php endif; ?>
    });
</script>

<style>
    .btn-group-vertical .btn {
        margin-bottom: 2px;
        border-radius: 4px;
    }

    .btn-group-vertical .btn:last-child {
        margin-bottom: 0;
    }

    .table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .badge-lg {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }

    .swal2-popup {
        font-size: 0.9rem;
    }

    .swal2-title {
        font-size: 1.2rem;
    }

    .lightbox-img {
        max-width: 100%;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }
</style>