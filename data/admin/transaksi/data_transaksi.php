<?php
$body_class = 'sidebar-collapse';
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";

date_default_timezone_set('Asia/Jakarta');

$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-d');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

// Validasi: tanggal akhir tidak boleh lebih besar dari hari ini
$today = date('Y-m-d');
if ($tgl_akhir > $today) {
    $tgl_akhir = $today;
}

// PERBAIKAN: Query harus sesuai dengan field yang ada di database
$stmt = $con->prepare("
    SELECT 
        th.id_transaksi,
        th.tgl_transaksi,
        th.total,
        th.metode_pembayaran,
        th.jumlah_bayar,
        th.kembalian,
        m.nama AS nama_member, 
        u.username AS nama_kasir 
    FROM tbl_transaksi_offline th
    LEFT JOIN tbl_member m ON th.id_member = m.id_member
    LEFT JOIN tbl_user u ON th.id_kasir = u.id_user
    LEFT JOIN tbl_paket p ON th.id_paket = p.id_paket
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
        background: #0f1117;
        color: #e5e7eb;
    }

    .card-glass {
        background: rgba(25, 27, 45, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        color: #e5e7eb;
    }

    .btn-modern {
        border-radius: 10px;
        font-weight: 600;
        padding: 10px 20px;
        transition: all 0.3s;
        border: none;
        background: #1f2937;
        color: #f3f4f6;
    }

    .btn-modern:hover {
        background: #374151;
        transform: translateY(-2px);
    }

    .badge-metode {
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        color: white;
    }

    .badge-tunai {
        background: linear-gradient(135deg, #22c55e, #16a34a);
    }

    .badge-qris {
        background: linear-gradient(135deg, #f97316, #ea580c);
    }

    .badge-transfer {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .badge-debit {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }

    .table th {
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        color: white;
        border: none;
        font-weight: 600;
        padding: 12px 15px;
    }

    .table td {
        padding: 12px 15px;
        vertical-align: middle;
        background: #111827;
        color: #e5e7eb;
    }

    .table tr:nth-child(even) td {
        background: #1a1f2d;
    }

    .btn-detail {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 12px;
        transition: 0.3s;
        margin: 2px;
    }

    .btn-detail:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
    }

    .btn-cetak {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 12px;
        border: none;
        transition: 0.3s;
        margin: 2px;
    }

    .btn-cetak:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .filter-card {
        background: linear-gradient(135deg, #1e3a8a, #312e81);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    }

    .stat-card {
        background: #1f2937;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        transition: transform 0.3s;
        color: #f3f4f6;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #60a5fa;
    }

    .stat-label {
        color: #9ca3af;
        font-size: 0.9rem;
    }

    .modal-content {
        background: #1f2937;
        border-radius: 15px;
        border: none;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.5);
        color: #f3f4f6;
    }

    .modal-header {
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        color: white;
        border-radius: 15px 15px 0 0;
    }

    .total-highlight {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border-radius: 8px;
        padding: 8px 12px;
        font-weight: 700;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        margin: 0 3px;
        background: #1f2937 !important;
        color: #e5e7eb !important;
        border: none !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #1d4ed8, #1e40af) !important;
        color: white !important;
    }

    /* Date input validation */
    input[type="date"]:invalid {
        border-color: #ff4444;
    }

    input[type="date"]:valid {
        border-color: #22c55e;
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
                        $totalPendapatan += $row['total'];
                        $totalPendapatan += $row['total'];
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
            <form method="get" class="mb-0" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="text-white"><i class="fas fa-calendar-alt"></i> Dari Tanggal</label>
                        <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="form-control" id="tglAwal" max="<?= date('Y-m-d') ?>" style="border-radius: 10px;" required>
                    </div>
                    <div class="col-md-3">
                        <label class="text-white"><i class="fas fa-calendar-check"></i> Sampai Tanggal</label>
                        <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="form-control" id="tglAkhir" max="<?= date('Y-m-d') ?>" style="border-radius: 10px;" required>
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
                        <a href="?export=excel&tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>"
                            class="btn btn-warning btn-modern btn-block mt-2">
                            <i class="fas fa-file-export mr-1"></i> Export Excel
                        </a>
                    </div>
                </div>
                <small class="text-warning mt-2 d-block"><i class="fas fa-info-circle"></i> Tanggal tidak boleh lebih dari hari ini</small>
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
                                <th>Paket</th>
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
                                        <td class="text-right total-highlight">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-detail btn-detail-transaksi" data-id="<?= htmlspecialchars($row['id_transaksi']) ?>">
                                                <i class="fas fa-eye mr-1"></i> Detail
                                            </button>
                                            <button class="btn btn-cetak btn-cetak-nota" data-id="<?= htmlspecialchars($row['id_transaksi']) ?>">
                                                <i class="fas fa-print mr-1"></i> Nota
                                            </button>
                                            <button class="btn btn-warning btn-cetak" onclick="downloadNota('<?= htmlspecialchars($row['id_transaksi']) ?>')" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                                <i class="fas fa-download mr-1"></i> PDF
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
                <button type="button" class="btn btn-primary" id="btnPrintNota">
                    <i class="fas fa-print mr-1"></i> Cetak Nota
                </button>
                <button type="button" class="btn btn-success" id="btnDownloadNota">
                    <i class="fas fa-download mr-1"></i> Download PDF
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        // Set max date untuk input tanggal
        const today = new Date().toISOString().split('T')[0];
        $('#tglAwal').attr('max', today);
        $('#tglAkhir').attr('max', today);

        // Validasi tanggal
        $('#tglAwal, #tglAkhir').on('change', function() {
            const tglAwal = $('#tglAwal').val();
            const tglAkhir = $('#tglAkhir').val();

            if (tglAwal && tglAkhir) {
                if (tglAwal > tglAkhir) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Tanggal awal tidak boleh lebih besar dari tanggal akhir!',
                        confirmButtonColor: '#4361ee'
                    });
                    $('#tglAkhir').val(tglAwal);
                }
            }
        });

        // Inisialisasi DataTable
        $('#tabelTransaksi').DataTable({
            "language": {
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada data yang tersedia",
                "infoFiltered": "(difilter dari _MAX_ total data)",
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
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
                    className: 'btn btn-success btn-modern',
                    filename: 'Data_Transaksi_<?= date('Y-m-d') ?>'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                    className: 'btn btn-danger btn-modern',
                    filename: 'Data_Transaksi_<?= date('Y-m-d') ?>',
                    title: 'Data Transaksi Offline',
                    message: 'Periode: <?= date('d/m/Y', strtotime($tgl_awal)) ?> - <?= date('d/m/Y', strtotime($tgl_akhir)) ?>',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6]
                    },
                    customize: function(doc) {
                        doc.content[1].table.widths = ['5%', '15%', '15%', '15%', '15%', '10%', '15%'];
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> Print',
                    className: 'btn btn-warning btn-modern',
                    title: 'Data Transaksi Offline',
                    message: 'Periode: <?= date('d/m/Y', strtotime($tgl_awal)) ?> - <?= date('d/m/Y', strtotime($tgl_akhir)) ?>',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6]
                    }
                }
            ]
        });

        // Tombol Export Manual
        $('#btnExport').click(function() {
            $('.buttons-excel').click();
        });

        // Detail Transaksi
        let currentTransactionId = null;
        $(document).on('click', '.btn-detail-transaksi', function() {
            currentTransactionId = $(this).data('id');
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
                    id: currentTransactionId
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
                    let detailItems = '';
                    let totalItems = 0;

                    if (h.nama_paket) {
                        detailItems = `
                        <tr>
                            <td>${h.nama_paket}</td>
                            <td class="text-center">1</td>
                            <td class="text-right">Rp ${parseInt(h.total).toLocaleString('id-ID')}</td>
                            <td class="text-right">Rp 0</td>
                            <td class="text-right">Rp ${parseInt(h.total).toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                        totalItems = 1;
                    }

                    const html = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-dark border-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-info"><i class="fas fa-info-circle mr-2"></i>Informasi Transaksi</h6>
                                        <table class="table table-sm table-borderless text-light">
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
                                <div class="card bg-dark border-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-info"><i class="fas fa-calculator mr-2"></i>Ringkasan Pembayaran</h6>
                                        <table class="table table-sm table-borderless text-light">
                                            <tr><th width="60%">Total</th><td class="text-right"><strong>Rp ${parseInt(h.total).toLocaleString('id-ID')}</strong></td></tr>
                                            <tr><th>Dibayar</th><td class="text-right">Rp ${parseInt(h.jumlah_bayar).toLocaleString('id-ID')}</td></tr>
                                            ${h.kembalian > 0 ? 
                                                `<tr><th>Kembalian</th><td class="text-right text-success">+ Rp ${parseInt(h.kembalian).toLocaleString('id-ID')}</td></tr>` : 
                                                ''}
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-info"><i class="fas fa-list mr-2"></i>Detail Items (${totalItems} item)</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-dark">
                                        <thead class="bg-secondary">
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

        // Cetak Nota dari Modal
        $('#btnPrintNota').click(function() {
            if (currentTransactionId) {
                printNota(currentTransactionId);
            }
        });

        // Download PDF dari Modal
        $('#btnDownloadNota').click(function() {
            if (currentTransactionId) {
                downloadNota(currentTransactionId);
            }
        });

        // Cetak Nota langsung dari tabel
        $(document).on('click', '.btn-cetak-nota', function() {
            const idTransaksi = $(this).data('id');
            printNota(idTransaksi);
        });
    });

    // Fungsi untuk cetak nota
    function printNota(idTransaksi) {
        Swal.fire({
            title: 'Menyiapkan Nota...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: 'get_data_nota.php',
            method: 'GET',
            data: {
                id: idTransaksi
            },
            dataType: 'json',
            success: function(res) {
                Swal.close();
                if (res.success) {
                    const data = res.data;
                    const printWindow = window.open('', '_blank', 'width=350,height=500');

                    const html = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Nota Transaksi #${data.id_transaksi}</title>
                            <style>
                                @import url('https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@400;500&display=swap');
                                * {
                                    margin: 0;
                                    padding: 0;
                                    box-sizing: border-box;
                                    font-family: 'Source Code Pro', monospace;
                                }
                                body {
                                    width: 300px;
                                    margin: 0 auto;
                                    padding: 15px;
                                    background: white;
                                    font-size: 14px;
                                    line-height: 1.4;
                                }
                                .header {
                                    text-align: center;
                                    border-bottom: 2px dashed #000;
                                    padding-bottom: 10px;
                                    margin-bottom: 15px;
                                }
                                .header h2 {
                                    font-size: 18px;
                                    margin-bottom: 5px;
                                    text-transform: uppercase;
                                    font-weight: bold;
                                }
                                .header p {
                                    font-size: 12px;
                                    color: #666;
                                }
                                .info-section {
                                    margin-bottom: 15px;
                                }
                                .info-row {
                                    display: flex;
                                    justify-content: space-between;
                                    margin-bottom: 5px;
                                }
                                .info-label {
                                    font-weight: bold;
                                    color: #333;
                                }
                                .info-value {
                                    text-align: right;
                                    color: #333;
                                }
                                .divider {
                                    border: none;
                                    border-top: 1px dashed #000;
                                    margin: 10px 0;
                                }
                                .total-section {
                                    background: #f8f9fa;
                                    padding: 10px;
                                    border-radius: 5px;
                                    margin: 15px 0;
                                    border: 1px dashed #ccc;
                                }
                                .total-row {
                                    display: flex;
                                    justify-content: space-between;
                                    margin-bottom: 5px;
                                    font-weight: 500;
                                }
                                .grand-total {
                                    font-size: 16px;
                                    font-weight: bold;
                                    color: #e74c3c;
                                    border-top: 1px solid #000;
                                    padding-top: 5px;
                                    margin-top: 5px;
                                }
                                .footer {
                                    text-align: center;
                                    margin-top: 20px;
                                    padding-top: 10px;
                                    border-top: 2px dashed #000;
                                    color: #666;
                                    font-size: 12px;
                                }
                                @media print {
                                    body { 
                                        width: 300px !important;
                                        padding: 10px !important;
                                        font-size: 13px !important;
                                    }
                                    .no-print { display: none !important; }
                                    .header h2 { font-size: 16px !important; }
                                }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h2>GYM FITNESS CENTER</h2>
                                <p>Jl. Contoh No. 123, Jakarta</p>
                                <p>Telp: (021) 123-4567</p>
                            </div>
                            
                            <div class="info-section">
                                <div class="info-row">
                                    <span class="info-label">No. Transaksi:</span>
                                    <span class="info-value">${data.id_transaksi}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Tanggal:</span>
                                    <span class="info-value">${data.tgl_transaksi}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Kasir:</span>
                                    <span class="info-value">${data.nama_kasir}</span>
                                </div>
                            </div>
                            
                            <hr class="divider">
                            
                            <div class="info-section">
                                <div class="info-row">
                                    <span class="info-label">Member:</span>
                                    <span class="info-value">${data.nama_member}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Metode:</span>
                                    <span class="info-value">${data.metode_pembayaran}</span>
                                </div>
                            </div>
                            
                            <hr class="divider">
                            
                            <div class="total-section">
                                <div class="total-row">
                                    <span>Total:</span>
                                    <span>Rp ${parseInt(data.total).toLocaleString('id-ID')}</span>
                                </div>
                                <div class="total-row">
                                    <span>Dibayar:</span>
                                    <span>Rp ${parseInt(data.jumlah_bayar).toLocaleString('id-ID')}</span>
                                </div>
                                <div class="total-row">
                                    <span>Kembalian:</span>
                                    <span>Rp ${parseInt(data.kembalian).toLocaleString('id-ID')}</span>
                                </div>
                            </div>
                            
                            <div class="footer">
                                <p>Terima kasih atas kunjungan Anda</p>
                                <p>*** Semoga Sehat Selalu ***</p>
                            </div>
                            
                            <script>
                                setTimeout(() => {
                                    window.print();
                                }, 500);
                                
                                window.onafterprint = function() {
                                    setTimeout(() => {
                                        window.close();
                                    }, 1000);
                                };
                            <\/script>
                        </body>
                        </html>
                    `;

                    printWindow.document.write(html);
                    printWindow.document.close();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.error || 'Tidak dapat mengambil data transaksi',
                        confirmButtonColor: '#4361ee'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: 'Tidak dapat terhubung ke server',
                    confirmButtonColor: '#4361ee'
                });
            }
        });
    }

    // Fungsi untuk download nota sebagai PDF
    function downloadNota(idTransaksi) {
        Swal.fire({
            title: 'Membuat PDF...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: 'get_data_nota.php',
            method: 'GET',
            data: {
                id: idTransaksi
            },
            dataType: 'json',
            success: function(res) {
                Swal.close();
                if (res.success) {
                    const data = res.data;

                    // Buat konten HTML untuk PDF
                    const content = `
                        <div style="font-family: Arial, sans-serif; max-width: 300px; margin: 0 auto; padding: 20px;">
                            <div style="text-align: center; border-bottom: 2px dashed #000; padding-bottom: 10px; margin-bottom: 15px;">
                                <h2 style="font-size: 18px; margin-bottom: 5px; font-weight: bold;">GYM FITNESS CENTER</h2>
                                <p style="font-size: 12px; color: #666; margin: 2px 0;">Jl. Contoh No. 123, Jakarta</p>
                                <p style="font-size: 12px; color: #666; margin: 2px 0;">Telp: (021) 123-4567</p>
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                    <span style="font-weight: bold;">No. Transaksi:</span>
                                    <span>${data.id_transaksi}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                    <span style="font-weight: bold;">Tanggal:</span>
                                    <span>${data.tgl_transaksi}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                    <span style="font-weight: bold;">Kasir:</span>
                                    <span>${data.nama_kasir}</span>
                                </div>
                            </div>
                            
                            <hr style="border: none; border-top: 1px dashed #000; margin: 10px 0;">
                            
                            <div style="margin-bottom: 15px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                    <span style="font-weight: bold;">Member:</span>
                                    <span>${data.nama_member}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                    <span style="font-weight: bold;">Metode:</span>
                                    <span>${data.metode_pembayaran}</span>
                                </div>
                            </div>
                            
                            <hr style="border: none; border-top: 1px dashed #000; margin: 10px 0;">
                            
                            <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 15px 0; border: 1px dashed #ccc;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-weight: 500;">
                                    <span>Total:</span>
                                    <span>Rp ${parseInt(data.total).toLocaleString('id-ID')}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-weight: 500;">
                                    <span>Dibayar:</span>
                                    <span>Rp ${parseInt(data.jumlah_bayar).toLocaleString('id-ID')}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-weight: 500;">
                                    <span>Kembalian:</span>
                                    <span>Rp ${parseInt(data.kembalian).toLocaleString('id-ID')}</span>
                                </div>
                            </div>
                            
                            <div style="text-align: center; margin-top: 20px; padding-top: 10px; border-top: 2px dashed #000; color: #666; font-size: 12px;">
                                <p style="margin: 5px 0;">Terima kasih atas kunjungan Anda</p>
                                <p style="margin: 5px 0;">*** Semoga Sehat Selalu ***</p>
                            </div>
                        </div>
                    `;

                    // Konfigurasi html2pdf
                    const element = document.createElement('div');
                    element.innerHTML = content;

                    const opt = {
                        margin: 10,
                        filename: `Nota_${data.id_transaksi}.pdf`,
                        image: {
                            type: 'jpeg',
                            quality: 0.98
                        },
                        html2canvas: {
                            scale: 2
                        },
                        jsPDF: {
                            unit: 'mm',
                            format: 'a5',
                            orientation: 'portrait'
                        }
                    };

                    // Generate dan download PDF
                    html2pdf().set(opt).from(element).save();

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.error || 'Tidak dapat mengambil data transaksi',
                        confirmButtonColor: '#4361ee'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: 'Tidak dapat terhubung ke server',
                    confirmButtonColor: '#4361ee'
                });
            }
        });
    }
</script>