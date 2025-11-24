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
    FROM tbl_transaksi_offline th
    LEFT JOIN tbl_member m ON th.id_member = m.id_member
    LEFT JOIN tbl_user u ON th.id_kasir = u.id_user
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
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .btn-modern {
        border-radius: 10px;
        font-weight: 600;
        padding: 10px 20px;
        transition: all 0.3s;
        border: none;
    }

    .badge-metode {
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
    }

    .badge-tunai {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }

    .badge-qris {
        background: linear-gradient(135deg, #ff5722, #ff6b35);
        color: white;
    }

    .badge-transfer {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
    }

    .badge-debit {
        background: linear-gradient(135deg, #6f42c1, #8e44ad);
        color: white;
    }

    .table th {
        background: linear-gradient(135deg, #4361ee, #3a56d4);
        color: white;
        border: none;
        font-weight: 600;
        padding: 12px 15px;
    }

    .table td {
        padding: 12px 15px;
        vertical-align: middle;
    }

    .btn-detail {
        background: linear-gradient(135deg, #17a2b8, #138496);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 12px;
        transition: all 0.3s;
    }

    .btn-detail:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
    }

    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #4361ee;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: linear-gradient(135deg, #4361ee, #3a56d4);
        color: white;
        border-radius: 15px 15px 0 0;
        border: none;
    }

    .total-highlight {
        background: linear-gradient(135deg, #06d6a0, #05a87e);
        color: white;
        border-radius: 8px;
        padding: 8px 12px;
        font-weight: 700;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        margin: 0 3px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #4361ee, #3a56d4) !important;
        border: none !important;
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <h1 class="mb-1"><i class="fas fa-receipt text-primary"></i> Data Transaksi Offline</h1>
                <p class="text-muted">Filter tanggal & lihat detail transaksi offline</p>
            </div>
            <div class="col-md-4 text-right">
                <div class="bg-light p-3 rounded d-inline-block">
                    <small class="text-muted">Periode:</small>
                    <strong><?= date('d/m/Y', strtotime($tgl_awal)) ?> - <?= date('d/m/Y', strtotime($tgl_akhir)) ?></strong>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Statistik Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= count($data) ?></div>
                    <div class="stat-label">Total Transaksi</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <?php
                    $totalPendapatan = 0;
                    foreach ($data as $row) {
                        $totalPendapatan += $row['grand_total'];
                    }
                    ?>
                    <div class="stat-number">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></div>
                    <div class="stat-label">Total Pendapatan</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <?php
                    $tunaiCount = 0;
                    foreach ($data as $row) {
                        if (strtoupper($row['metode_pembayaran']) == 'TUNAI') $tunaiCount++;
                    }
                    ?>
                    <div class="stat-number"><?= $tunaiCount ?></div>
                    <div class="stat-label">Transaksi Tunai</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <?php
                    $nonTunaiCount = count($data) - $tunaiCount;
                    ?>
                    <div class="stat-number"><?= $nonTunaiCount ?></div>
                    <div class="stat-label">Transaksi Non-Tunai</div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="filter-card">
            <form method="get" class="mb-0">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="text-white"><i class="fas fa-calendar-alt"></i> Dari Tanggal</label>
                        <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="form-control" style="border-radius: 10px;">
                    </div>
                    <div class="col-md-3">
                        <label class="text-white"><i class="fas fa-calendar-check"></i> Sampai Tanggal</label>
                        <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="form-control" style="border-radius: 10px;">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-light btn-modern btn-block mt-2">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="data_transaksi.php" class="btn btn-outline-light btn-modern btn-block mt-2">
                            <i class="fas fa-sync-alt mr-1"></i> Reset
                        </a>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-warning btn-modern btn-block mt-2" id="btnExport">
                            <i class="fas fa-file-export mr-1"></i> Export
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Transaksi -->
        <div class="card card-glass">
            <div class="card-body">
                <div class="table-responsive">
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
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-receipt fa-2x mb-3 d-block"></i>
                                        Tidak ada transaksi pada periode yang dipilih.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1;
                                foreach ($data as $row): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><strong><?= htmlspecialchars($row['id_transaksi']) ?></strong></td>
                                        <td><?= date('d/m/Y H:i', strtotime($row['tgl_transaksi'])) ?></td>
                                        <td>
                                            <?php if ($row['nama_member']): ?>
                                                <span class="text-primary"><?= htmlspecialchars($row['nama_member']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted"><em>Pelanggan Umum</em></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['nama_kasir'] ?? '-') ?></td>
                                        <td>
                                            <?php
                                            $metode = strtoupper($row['metode_pembayaran']);
                                            if ($metode == 'TUNAI') {
                                                echo '<span class="badge-metode badge-tunai"><i class="fas fa-money-bill-wave mr-1"></i>Tunai</span>';
                                            } elseif ($metode == 'QRIS') {
                                                echo '<span class="badge-metode badge-qris"><i class="fas fa-qrcode mr-1"></i>QRIS</span>';
                                            } elseif ($metode == 'TRANSFER') {
                                                echo '<span class="badge-metode badge-transfer"><i class="fas fa-exchange-alt mr-1"></i>Transfer</span>';
                                            } else {
                                                echo '<span class="badge-metode badge-debit"><i class="fas fa-credit-card mr-1"></i>Debit</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-right total-highlight">Rp <?= number_format($row['grand_total'], 0, ',', '.') ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-detail btn-detail-transaksi" data-id="<?= htmlspecialchars($row['id_transaksi']) ?>">
                                                <i class="fas fa-eye mr-1"></i> Detail
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
    </div>
</section>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-receipt mr-2"></i> Detail Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center text-muted py-4">
                    <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                    <p>Memuat detail transaksi...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btnPrintDetail">
                    <i class="fas fa-print mr-1"></i> Cetak
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../../../view/master/footer.php'; ?>

<!-- JS Libraries -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Inisialisasi DataTable
        var table = $('#tabelTransaksi').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            },
            "pageLength": 10,
            "lengthMenu": [
                [5, 10, 25, 50, -1],
                [5, 10, 25, 50, "Semua"]
            ],
            "order": [
                [2, "desc"]
            ],
            "dom": '<"row"<"col-md-6"B><"col-md-6"f>>rt<"row"<"col-md-6"l><"col-md-6"p>>',
            "buttons": [{
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    className: 'btn btn-success btn-modern'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                    className: 'btn btn-danger btn-modern'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> Print',
                    className: 'btn btn-warning btn-modern'
                }
            ]
        });

        // Tombol Export Manual
        $('#btnExport').click(function() {
            $('.buttons-excel').click();
        });

        // Detail Transaksi
        $(document).on('click', '.btn-detail-transaksi', function() {
            const id = $(this).data('id');
            $('#detailContent').html(`
                <div class="text-center text-muted py-4">
                    <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                    <p>Memuat detail transaksi...</p>
                </div>
            `);
            $('#modalDetail').modal('show');

            $.ajax({
                url: 'detail_transaksi.php',
                method: 'GET',
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    if (!res.success) {
                        $('#detailContent').html(`
                            <div class="alert alert-danger text-center">
                                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                <h5>Error</h5>
                                <p>${res.error || 'Terjadi kesalahan saat memuat data'}</p>
                            </div>
                        `);
                        return;
                    }

                    const h = res.header;
                    const details = res.details;

                    let detailItems = '';
                    let totalItems = 0;

                    details.forEach(detail => {
                        totalItems += detail.qty;
                        detailItems += `
                            <tr>
                                <td>${detail.nama_paket}</td>
                                <td class="text-center">${detail.qty}</td>
                                <td class="text-right">Rp ${parseInt(detail.harga_satuan).toLocaleString('id-ID')}</td>
                                <td class="text-right">Rp ${parseInt(detail.potongan_diskon_item || 0).toLocaleString('id-ID')}</td>
                                <td class="text-right">Rp ${parseInt(detail.sub_total).toLocaleString('id-ID')}</td>
                            </tr>
                        `;
                    });

                    const html = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informasi Transaksi</h6>
                                        <table class="table table-sm table-borderless">
                                            <tr><th width="40%">ID Transaksi</th><td><strong>${h.id_transaksi}</strong></td></tr>
                                            <tr><th>Tanggal</th><td>${new Date(h.tgl_transaksi).toLocaleString('id-ID')}</td></tr>
                                            <tr><th>Member</th><td>${h.nama_member || '<em class="text-muted">Pelanggan Umum</em>'}</td></tr>
                                            <tr><th>Kasir</th><td>${h.nama_kasir || '-'}</td></tr>
                                            <tr><th>Metode Bayar</th><td>
                                                ${h.metode_pembayaran === 'TUNAI' ? '<span class="badge-metode badge-tunai"><i class="fas fa-money-bill-wave mr-1"></i>Tunai</span>' :
                                                  h.metode_pembayaran === 'QRIS' ? '<span class="badge-metode badge-qris"><i class="fas fa-qrcode mr-1"></i>QRIS</span>' :
                                                  h.metode_pembayaran === 'TRANSFER' ? '<span class="badge-metode badge-transfer"><i class="fas fa-exchange-alt mr-1"></i>Transfer</span>' :
                                                  '<span class="badge-metode badge-debit"><i class="fas fa-credit-card mr-1"></i>Debit</span>'}
                                            </td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="fas fa-calculator mr-2"></i>Ringkasan Pembayaran</h6>
                                        <table class="table table-sm table-borderless">
                                            <tr><th width="60%">Sub Total</th><td class="text-right">Rp ${parseInt(h.sub_total).toLocaleString('id-ID')}</td></tr>
                                            <tr><th>Diskon Global</th><td class="text-right">- Rp ${parseInt(h.potongan_diskon_global || 0).toLocaleString('id-ID')}</td></tr>
                                            <tr class="border-top"><th>Grand Total</th><td class="text-right"><strong>Rp ${parseInt(h.grand_total).toLocaleString('id-ID')}</strong></td></tr>
                                            <tr><th>Dibayar</th><td class="text-right">Rp ${parseInt(h.jumlah_dibayar_tunai).toLocaleString('id-ID')}</td></tr>
                                            ${h.jumlah_kembalian > 0 ? 
                                                `<tr><th>Kembalian</th><td class="text-right text-success">+ Rp ${parseInt(h.jumlah_kembalian).toLocaleString('id-ID')}</td></tr>` : 
                                                ''}
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h6><i class="fas fa-list mr-2"></i>Detail Items (${totalItems} item)</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Paket</th>
                                                <th width="10%" class="text-center">Qty</th>
                                                <th width="20%" class="text-right">Harga Satuan</th>
                                                <th width="20%" class="text-right">Diskon Item</th>
                                                <th width="20%" class="text-right">Sub Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${detailItems}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        ${h.keterangan ? `
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <strong><i class="fas fa-sticky-note mr-2"></i>Keterangan:</strong> ${h.keterangan}
                                </div>
                            </div>
                        </div>` : ''}
                    `;

                    $('#detailContent').html(html);
                },
                error: function(xhr, status, error) {
                    $('#detailContent').html(`
                        <div class="alert alert-danger text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <h5>Kesalahan Jaringan</h5>
                            <p>Tidak dapat terhubung ke server. Silakan coba lagi.</p>
                        </div>
                    `);
                }
            });
        });

        // Cetak Detail
        $('#btnPrintDetail').click(function() {
            const content = $('#detailContent').html();
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Detail Transaksi</title>
                    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .table th { background-color: #f8f9fa; }
                        @media print { 
                            .btn { display: none; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="text-center mb-4">
                            <h3 class="mb-1">Detail Transaksi</h3>
                            <p class="text-muted">${new Date().toLocaleString('id-ID')}</p>
                        </div>
                        ${content}
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        });
    });
</script>