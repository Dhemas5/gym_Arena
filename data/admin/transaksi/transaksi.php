<?php
$body_class = 'sidebar-collapse';
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";

date_default_timezone_set('Asia/Jakarta');

// Default rentang tanggal (hari ini)
$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-d');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

$stmt = $con->prepare("
    SELECT th.*, 
           m.nama AS nama_member, 
           u.username AS nama_kasir 
    FROM tbl_transaksi_header th
    LEFT JOIN tbl_member m ON th.id_member = m.id_member
    LEFT JOIN tbl_user u ON th.id_user_kasir = u.id_user
    WHERE DATE(th.tgl_transaksi) BETWEEN ? AND ?
    ORDER BY th.tgl_transaksi DESC
");
$stmt->bind_param('ss', $tgl_awal, $tgl_akhir);
$stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include '../../../view/master/header.php';
include '../../../view/master/sidebar.php';
?>

<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #f4f6f9;
    }

    .card-glass {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }

    .btn-glass {
        background: rgba(255, 255, 255, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: #333;
        transition: all 0.2s;
    }

    .btn-glass:hover {
        background: rgba(255, 255, 255, 0.6);
    }

    .badge-metode {
        font-size: 12px;
        padding: 6px 10px;
        border-radius: 8px;
    }

    .badge-tunai {
        background: #28a745;
    }

    .badge-transfer {
        background: #007bff;
    }

    .badge-qris {
        background: #ff5722;
    }

    .modal-content.card-glass {
        border-radius: 20px;
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <h1 class="mb-1">Data Transaksi</h1>
        <p class="text-muted">Lihat, filter, dan cek detail transaksi</p>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-glass p-3">
            <form method="get" class="mb-3">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label>Dari Tanggal</label>
                        <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block mt-2">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="data_transaksi.php" class="btn btn-secondary btn-block mt-2">
                            <i class="fas fa-sync-alt mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped table-hover" id="tabelPelatih">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Transaksi</th>
                            <th>Tanggal</th>
                            <th>Member</th>
                            <th>Kasir</th>
                            <th>Metode</th>
                            <th>Total</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($data) === 0): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Tidak ada transaksi.</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1;
                            foreach ($data as $row): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['id_transaksi']) ?></strong></td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['tgl_transaksi'])) ?></td>
                                    <td><?= $row['nama_member'] ? htmlspecialchars($row['nama_member']) : '<em>Umum</em>' ?></td>
                                    <td><?= htmlspecialchars($row['nama_kasir'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $met = strtolower($row['metode_pembayaran']);
                                        if ($met == 'tunai') echo '<span class="badge badge-metode badge-tunai">Tunai</span>';
                                        elseif ($met == 'transfer') echo '<span class="badge badge-metode badge-transfer">Transfer</span>';
                                        else echo '<span class="badge badge-metode badge-qris">QRIS</span>';
                                        ?>
                                    </td>
                                    <td class="text-right">Rp <?= number_format($row['grand_total'], 0, ',', '.') ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info btn-detail" data-id="<?= htmlspecialchars($row['id_transaksi']) ?>">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal Detail Transaksi -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content card-glass">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-receipt mr-2"></i> Detail Transaksi</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>
            </div>
        </div>
    </div>
</div>

<?php include '../../../view/master/footer.php'; ?>

<!-- Library -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>