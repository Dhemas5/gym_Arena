<?php
$body_class = 'sidebar-collapse';
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";

date_default_timezone_set('Asia/Jakarta');

$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-d');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

$stmt = $con->prepare("
    SELECT th.*, m.nama AS nama_member, u.username AS nama_kasir 
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
        color: white;
    }

    .badge-qris {
        background: #ff5722;
        color: white;
    }

    .badge-transfer {
        background: #007bff;
        color: white;
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <h1 class="mb-1">Data Transaksi</h1>
        <p class="text-muted">Filter tanggal & lihat detail transaksi</p>
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
                <table class="table table-bordered table-striped table-hover" id="tabelTransaksi">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Transaksi</th>
                            <th>Tanggal</th>
                            <th>Member</th>
                            <th>Kasir</th>
                            <th>Metode</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Tidak ada transaksi.</td>
                            </tr>
                            <?php else: $no = 1;
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
                                        elseif ($met == 'qris') echo '<span class="badge badge-metode badge-qris">QRIS</span>';
                                        else echo '<span class="badge badge-metode badge-transfer">Transfer</span>';
                                        ?>
                                    </td>
                                    <td class="text-right">Rp <?= number_format($row['grand_total'], 0, ',', '.') ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info btn-detail" data-id="<?= htmlspecialchars($row['id_transaksi']) ?>">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                        <?php endforeach;
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content card-glass">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-receipt mr-2"></i> Detail Transaksi</h5>
                <button type="button" class="close text-white" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>
            </div>
        </div>
    </div>
</div>

<?php include '../../../view/master/footer.php'; ?>

<!-- JS Libraries -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('#tabelTransaksi').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            },
            "pageLength": 25,
            "order": [
                [2, "desc"]
            ]
        });

        $(document).on('click', '.btn-detail', function() {
            const id = $(this).data('id');
            $('#detailContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>');
            $('#modalDetail').modal('show');

            $.get('detail_transaksi.php?id=' + id, function(res) {
                if (!res.success) {
                    $('#detailContent').html('<div class="text-danger text-center">Error: ' + res.error + '</div>');
                    return;
                }

                const h = res.header;
                const d = res.details[0];

                let html = `
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th>ID Transaksi</th><td><strong>${h.id_transaksi}</strong></td></tr>
                            <tr><th>Tanggal</th><td>${new Date(h.tgl_transaksi).toLocaleString('id-ID')}</td></tr>
                            <tr><th>Member</th><td>${h.nama_member || '<em>Pelanggan Umum</em>'}</td></tr>
                            <tr><th>Kasir</th><td>${h.nama_kasir || '-'}</td></tr>
                            <tr><th>Metode</th><td>
                                ${h.metode_pembayaran === 'TUNAI' ? '<span class="badge badge-success">Tunai</span>' :
                                  h.metode_pembayaran === 'QRIS' ? '<span class="badge badge-warning">QRIS</span>' :
                                  '<span class="badge badge-primary">Transfer</span>'}
                            </td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th>Paket</th><td>${d.nama_paket}</td></tr>
                            <tr><th>Harga Satuan</th><td>Rp ${parseInt(d.harga_satuan).toLocaleString('id-ID')}</td></tr>
                            <tr><th>Qty</th><td>${d.qty}</td></tr>
                            <tr><th>Diskon Item</th><td>Rp ${parseInt(d.potongan_diskon_item).toLocaleString('id-ID')}</td></tr>
                            <tr><th>Sub Total</th><td>Rp ${parseInt(h.sub_total).toLocaleString('id-ID')}</td></tr>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Diskon Global:</strong> Rp ${parseInt(h.potongan_diskon_global).toLocaleString('id-ID')}</p>
                        <p><strong>Jumlah Dibayar:</strong> Rp ${parseInt(h.jumlah_dibayar_tunai).toLocaleString('id-ID')}</p>
                        ${h.jumlah_kembalian > 0 ? `<p><strong>Kembalian:</strong> Rp ${parseInt(h.jumlah_kembalian).toLocaleString('id-ID')}</p>` : ''}
                    </div>
                    <div class="col-md-6 text-right">
                        <h4>Grand Total: <strong>Rp ${parseInt(h.grand_total).toLocaleString('id-ID')}</strong></h4>
                    </div>
                </div>
                ${h.keterangan ? `<hr><p><strong>Keterangan:</strong> ${h.keterangan}</p>` : ''}
            `;

                $('#detailContent').html(html);
            }).fail(function() {
                $('#detailContent').html('<div class="text-danger text-center">Gagal memuat data.</div>');
            });
        });
    });
</script>