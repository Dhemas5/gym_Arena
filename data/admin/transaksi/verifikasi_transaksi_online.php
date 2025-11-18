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
        $con->autocommit(FALSE);

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
            $con->query("UPDATE tbl_member SET membership_status = 'aktif' WHERE id_member = $id_member");

            // Update status transaksi online
            $stmt = $con->prepare("UPDATE tbl_transaksi_online SET status = 'paid', verified_by = ?, verified_at = NOW() WHERE id_transaksi = ?");
            $stmt->bind_param("is", $_SESSION['id_user'], $id_trx);
            $stmt->execute();
            $stmt->close();

            $con->commit();
            echo json_encode(['success' => true, 'msg' => 'Transaksi berhasil disetujui & membership diaktifkan!']);
        } elseif ($action === 'reject') {
            $stmt = $con->prepare("UPDATE tbl_transaksi_online SET status = 'rejected', verified_by = ?, verified_at = NOW() WHERE id_transaksi = ? AND status = 'pending'");
            $stmt->bind_param("is", $_SESSION['id_user'], $id_trx);
            $stmt->execute();
            $stmt->close();

            $con->commit();
            echo json_encode(['success' => true, 'msg' => 'Transaksi berhasil ditolak']);
        }
    } catch (Exception $e) {
        $con->rollback();
        echo json_encode(['success' => false, 'msg' => 'Gagal: ' . $e->getMessage()]);
    }

    $con->autocommit(TRUE);
    exit;
}

// Ambil data transaksi pending
$sql = "SELECT ton.*, m.nama AS nama_member, p.nama_paket, p.durasi_hari 
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
                        <h4>Tidak ada pembayaran yang menunggu verifikasi</h4>
                        <p class="text-muted">Semua transaksi online sudah diproses</p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover text-nowrap align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th width="10%">ID Transaksi</th>
                                <th width="18%">Member</th>
                                <th width="20%">Paket</th>
                                <th width="12%">Total Bayar</th>
                                <th width="15%">Tanggal</th>
                                <th width="12%">Bukti Transfer</th>
                                <th width="13%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $pending->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="badge bg-warning text-dark">#<?= htmlspecialchars($row['id_transaksi']) ?></span></td>
                                    <td><strong><?= htmlspecialchars($row['nama_member']) ?></strong></td>
                                    <td>
                                        <?= htmlspecialchars($row['nama_paket']) ?>
                                        <small class="text-muted d-block">(<?= $row['durasi_hari'] ?> hari)</small>
                                    </td>
                                    <td><strong class="text-success">Rp <?= number_format($row['total'], 0, ',', '.') ?></strong></td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['tgl_transaksi'])) ?></td>
                                    <td>
                                        <a href="../../../uploads/bukti/<?= htmlspecialchars($row['bukti_transfer']) ?>"
                                            target="_blank" class="btn btn-sm btn-info lightbox">
                                            <i class="fas fa-image"></i> Lihat
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-success btn-sm btn-approve" data-id="<?= $row['id_transaksi'] ?>">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn btn-danger btn-sm btn-reject" data-id="<?= $row['id_transaksi'] ?>">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
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

<!-- Lightbox CSS -->
<style>
    .lightbox-img {
        max-width: 100%;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    .swal2-popup {
        font-family: 'Segoe UI', sans-serif;
    }
</style>

<?php include '../../../view/master/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelectorAll('.btn-approve, .btn-reject').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const isApprove = this.classList.contains('btn-approve');
            const action = isApprove ? 'approve' : 'reject';

            Swal.fire({
                title: isApprove ? 'Setujui Pembayaran?' : 'Tolak Pembayaran?',
                text: isApprove ? 'Membership member akan langsung aktif!' : 'Transaksi akan ditandai ditolak',
                icon: isApprove ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: isApprove ? '#28a745' : '#dc3545',
                confirmButtonText: 'Ya, ' + (isApprove ? 'Setujui' : 'Tolak'),
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const btnHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    fetch('', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'action=' + action + '&id_transaksi=' + id
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.msg,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => location.reload());
                            } else {
                                Swal.fire('Gagal', res.msg, 'error');
                                btn.disabled = false;
                                btn.innerHTML = btnHtml;
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'Koneksi terputus', 'error');
                            btn.disabled = false;
                            btn.innerHTML = btnHtml;
                        });
                }
            });
        });
    });

    // Lightbox sederhana
    document.querySelectorAll('.lightbox').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const src = this.getAttribute('href');
            Swal.fire({
                imageUrl: src,
                imageAlt: 'Bukti Transfer',
                showConfirmButton: false,
                width: '90%',
                padding: '1rem',
                background: '#000',
                backdrop: 'rgba(0,0,0,0.9)'
            });
        });
    });
</script>