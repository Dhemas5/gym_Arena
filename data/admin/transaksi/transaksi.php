<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<!-- Tambahkan CSS Modern -->
<style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    .card {
        border-radius: 15px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        transition: all 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .btn {
        border-radius: 8px;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 8px;
        padding: 8px 15px;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px;
    }

    .badge {
        font-size: 0.9em;
        padding: 6px 12px;
        border-radius: 50px;
    }

    .table th {
        background: #f8f9fa;
        font-weight: 600;
    }

    .total-row {
        background: #e9f7ef !important;
        font-weight: bold;
    }
</style>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Data Transaksi Penjualan</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Beranda</a></li>
                    <li class="breadcrumb-item active">Transaksi</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-receipt"></i> Daftar Transaksi</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filter Tanggal -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>Dari Tanggal</label>
                                <input type="date" id="tanggal_awal" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Sampai Tanggal</label>
                                <input type="date" id="tanggal_akhir" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button id="btnFilter" class="btn btn-success btn-block">
                                    <i class="fas fa-search"></i> Tampilkan
                                </button>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button id="btnReset" class="btn btn-secondary btn-block">
                                    <i class="fas fa-sync"></i> Reset
                                </button>
                            </div>
                        </div>

                        <!-- Tabel Transaksi -->
                        <div class="table-responsive">
                            <table id="tabelPelatih" class="table table-bordered table-hover" style="width:100%">
                                <thead class="bg-gradient-primary text-white">
                                    <tr>
                                        <th>No</th>
                                        <th>No. Transaksi</th>
                                        <th>Tanggal</th>
                                        <th>Kasir</th>
                                        <th>Member</th>
                                        <th>Metode</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="dataTransaksi">
                                    <!-- Data akan diisi via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Detail Transaksi -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detail Transaksi</h5>
                <button type="button" class="close text-white" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Detail akan di-load via AJAX -->
            </div>
        </div>
    </div>
</div>

<?php include '../../../view/master/footer.php'; ?>

<!-- Script: DataTables, SweetAlert, AJAX -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        let table;

        // Format Rupiah
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(angka);
        }

        // Load Data Transaksi
        function loadTransaksi(tglAwal = '', tglAkhir = '') {
            $.ajax({
                url: 'proses_transaksi_data.php',
                type: 'GET',
                data: {
                    action: 'get_transaksi',
                    tgl_awal: tglAwal,
                    tgl_akhir: tglAkhir
                },
                dataType: 'json',
                beforeSend: () => {
                    $('#dataTransaksi').html('<tr><td colspan="9" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>');
                },
                success: function(res) {
                    if (res.status === 'success') {
                        let html = '';
                        let no = 1;

                        if (res.data.length === 0) {
                            html = '<tr><td colspan="9" class="text-center text-muted">Tidak ada transaksi pada periode ini.</td></tr>';
                        } else {
                            res.data.forEach(trx => {
                                const badgeMetode = trx.metode_pembayaran === 'Tunai' ? 'badge-success' :
                                    trx.metode_pembayaran === 'Transfer' ? 'badge-info' : 'badge-warning';

                                html += `
                                <tr>
                                    <td>${no++}</td>
                                    <td><strong>${trx.id_transaksi}</strong></td>
                                    <td>${trx.tanggal}</td>
                                    <td>${trx.kasir}</td>
                                    <td>${trx.member || '<em class="text-muted">Umum</em>'}</td>
                                    <td><span class="badge ${badgeMetode}">${trx.metode_pembayaran}</span></td>
                                    <td class="text-right font-weight-bold">${formatRupiah(trx.grand_total)}</td>
                                    <td><span class="badge badge-success">Sukses</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info btn-detail" data-id="${trx.id_transaksi}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>`;
                            });
                        }

                        $('#dataTransaksi').html(html);

                        // Inisialisasi ulang DataTables jika belum
                        if ($.fn.DataTable.isDataTable('#tabelTransaksi')) {
                            $('#tabelTransaksi').DataTable().destroy();
                        }

                        table = $('#tabelTransaksi').DataTable({
                            "pageLength": 10,
                            "lengthMenu": [10, 25, 50, 100],
                            "language": {
                                "search": "Cari:",
                                "lengthMenu": "Tampilkan _MENU_ data",
                                "info": "Menampilkan _START_ - _END_ dari _TOTAL_ transaksi",
                                "paginate": {
                                    "previous": "Sebelumnya",
                                    "next": "Berikutnya"
                                }
                            }
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: () => {
                    Swal.fire('Error', 'Gagal memuat data transaksi.', 'error');
                }
            });
        }

        // Event: Filter Tanggal
        $('#btnFilter').click(function() {
            const awal = $('#tanggal_awal').val();
            const akhir = $('#tanggal_akhir').val();

            if (!awal || !akhir) {
                Swal.fire('Peringatan', 'Pilih rentang tanggal terlebih dahulu!', 'warning');
                return;
            }

            if (new Date(awal) > new Date(akhir)) {
                Swal.fire('Error', 'Tanggal awal tidak boleh lebih besar dari tanggal akhir!', 'error');
                return;
            }

            loadTransaksi(awal, akhir);
        });

        // Reset Filter
        $('#btnReset').click(function() {
            $('#tanggal_awal').val('<?= date('Y-m-d') ?>');
            $('#tanggal_akhir').val('<?= date('Y-m-d') ?>');
            loadTransaksi();
        });

        // Lihat Detail
        $(document).on('click', '.btn-detail', function() {
            const id = $(this).data('id');

            $.ajax({
                url: 'proses_transaksi_data.php',
                type: 'GET',
                data: {
                    action: 'get_detail',
                    id: id
                },
                dataType: 'json',
                beforeSend: () => {
                    $('#detailContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat detail...</div>');
                    $('#modalDetail').modal('show');
                },
                success: function(res) {
                    if (res.status === 'success') {
                        let items = '';
                        res.detail.forEach(d => {
                            items += `
                            <tr>
                                <td>${d.nama_paket}</td>
                                <td class="text-center">${d.qty}</td>
                                <td class="text-right">${formatRupiah(d.harga_satuan)}</td>
                                <td class="text-right">${formatRupiah(d.potongan_diskon_item)}</td>
                                <td class="text-right font-weight-bold">${formatRupiah(d.total_item)}</td>
                            </tr>`;
                        });

                        $('#detailContent').html(`
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr><th>No. Transaksi</th><td><strong>${res.header.id_transaksi}</strong></td></tr>
                                    <tr><th>Tanggal</th><td>${res.header.tanggal}</td></tr>
                                    <tr><th>Kasir</th><td>${res.header.kasir}</td></tr>
                                    <tr><th>Member</th><td>${res.header.member || '<em>Umum</em>'}</td></tr>
                                    <tr><th>Metode</th><td><span class="badge badge-primary">${res.header.metode_pembayaran}</span></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6 text-right">
                                <h5>Sub Total: <strong>${formatRupiah(res.header.sub_total)}</strong></h5>
                                <h5>Diskon Item: <strong>${formatRupiah(res.header.potongan_diskon_global)}</strong></h5>
                                <h3 class="text-success">Grand Total: <strong>${formatRupiah(res.header.grand_total)}</strong></h3>
                                ${res.header.jumlah_dibayar_tunai ? `<small>Kembalian: <strong>${formatRupiah(res.header.jumlah_kembalian)}</strong></small>` : ''}
                            </div>
                        </div>
                        <hr>
                        <h6>Detail Item:</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Paket</th>
                                        <th>Qty</th>
                                        <th>Harga</th>
                                        <th>Diskon</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>${items}</tbody>
                            </table>
                        </div>
                        ${res.header.keterangan ? `<p><strong>Catatan:</strong> ${res.header.keterangan}</p>` : ''}
                    `);
                    } else {
                        $('#detailContent').html('<div class="alert alert-danger">Gagal memuat detail.</div>');
                    }
                }
            });
        });

        // Load data saat halaman dibuka
        loadTransaksi($('#tanggal_awal').val(), $('#tanggal_akhir').val());
    });
</script>