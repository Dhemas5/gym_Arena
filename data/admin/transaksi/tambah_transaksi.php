<?php
$body_class = 'sidebar-collapse';
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";

$queryPaket = $con->query("SELECT id_paket, nama_paket, harga FROM tbl_paket WHERE tipe_penjualan IN ('offline', 'keduanya')");
$queryMember = $con->query("SELECT id_member, nama, email FROM tbl_member WHERE status_membership != 'Aktif' ORDER BY nama ASC");

$pakets = $queryPaket ? $queryPaket->fetch_all(MYSQLI_ASSOC) : [];
$members = $queryMember ? $queryMember->fetch_all(MYSQLI_ASSOC) : [];

$nama_kasir = htmlspecialchars($_SESSION['username'] ?? 'Kasir');
$id_kasir = $_SESSION['id_user'] ?? 1;
$tanggal_saat_ini = date('d/m/Y');
$no_transaksi = 'TRX' . date('ymd') . 'XXXX';

include '../../../view/master/header.php';
include '../../../view/master/sidebar.php';
?>

<!-- Tambahkan CSS Custom -->
<link rel="stylesheet" href="../../../assets/assets_admin/dist/css/custom-tambah_transaksi.css">

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Transaksi Penjualan Paket</h1>
                <p class="text-muted">Lakukan transaksi penjualan paket kepada member atau pelanggan umum</p>
            </div>
            <div class="col-sm-6">
                <a href="transaksi_offline.php" class="btn btn-sm btn-outline-primary float-sm-right">
                    <i class="fas fa-list mr-1"></i> Daftar Transaksi
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">Pilih Member</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-label">Pilih Paket</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Pembayaran</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label">Selesai</div>
            </div>
        </div>

        <form id="formTransaksi">
            <input type="hidden" name="id_user_kasir" value="<?= $id_kasir ?>">
            <input type="hidden" name="id_member" id="id_member_hidden" value="">

            <div class="row">
                <div class="col-lg-8">
                    <!-- Info Kasir & Member -->
                    <div class="row animate-fade-in">
                        <div class="col-md-4">
                            <div class="info-box bg-gradient-light">
                                <span class="info-box-icon bg-info d-flex align-items-center justify-content-center">
                                    <i class="far fa-calendar-alt"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tanggal Transaksi</span>
                                    <span class="info-box-number"><?= $tanggal_saat_ini ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-gradient-light">
                                <span class="info-box-icon bg-warning d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user-alt"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Kasir</span>
                                    <span class="info-box-number"><?= $nama_kasir ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-gradient-light">
                                <span class="info-box-icon bg-primary d-flex align-items-center justify-content-center">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Member</span>
                                    <div class="input-group input-group-sm mt-1">
                                        <input type="text" class="form-control" id="nama_member_display" value="Umum" readonly>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCariMember">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pilih Paket -->
                    <div class="card mt-4 animate-fade-in" style="animation-delay: 0.1s;">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title"><i class="fas fa-box-open mr-2"></i> Pilih Paket</h3>
                            <span class="badge bg-light text-primary">Langkah 2</span>
                        </div>
                        <div class="card-body">
  <div class="row align-items-end">
    <div class="col-md-5 form-group">
      <label for="id_paket" class="font-weight-bold mb-1 text-primary">Pilih Paket</label>
      <select class="select2 form-control" id="id_paket" required style="width: 100%; height: 42px;">
        <option value="">-- Pilih Paket --</option>
        <?php foreach ($pakets as $p): ?>
          <option value="<?= $p['id_paket'] ?>"
            data-harga="<?= $p['harga'] ?>"
            data-nama="<?= htmlspecialchars($p['nama_paket']) ?>">
            <?= htmlspecialchars($p['nama_paket']) ?> (Rp <?= number_format($p['harga'], 0, ',', '.') ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-2 form-group floating-label">
      <input type="number" class="form-control" id="qty" min="1" value="1" placeholder=" ">
      <label for="qty">Qty</label>
    </div>

    <div class="col-md-3 form-group floating-label">
      <input type="number" class="form-control" id="diskon_item" min="0" value="0" step="0.01" placeholder=" ">
      <label for="diskon_item">Diskon Item (Rp)</label>
    </div>

    <div class="col-md-2 d-flex align-items-end">
      <button type="button" class="btn btn-info btn-block" id="btnHitung">
        <i class="fas fa-calculator mr-1"></i> Hitung
      </button>
    </div>
  </div>
</div>


                    </div>

                    <!-- Info Paket Dipilih -->
                    <div class="card mt-3 bg-gradient-success text-white" id="cardPaketInfo" style="display:none;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> Detail Paket Dipilih</h3>
                            <span class="badge bg-light text-success">Detail</span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Paket</label>
                                    <input type="text" class="form-control bg-white text-dark" id="info_nama" readonly>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Harga</label>
                                    <input type="text" class="form-control bg-white text-dark text-right" id="info_harga" readonly>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Qty</label>
                                    <input type="text" class="form-control bg-white text-dark text-right" id="info_qty" readonly>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Diskon Item</label>
                                    <input type="text" class="form-control bg-white text-dark text-right" id="info_diskon" readonly>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Sub Total</label>
                                    <input type="text" class="form-control bg-white text-dark text-right font-weight-bold" id="info_subtotal" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pembayaran -->
                    <div class="card mt-4 animate-fade-in" style="animation-delay: 0.2s;">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title"><i class="fas fa-credit-card mr-2"></i> Pembayaran</h3>
                            <span class="badge bg-light text-info">Langkah 3</span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 form-group floating-label">
                                    <select class="form-control" name="metode_pembayaran" id="metode_pembayaran" required>
                                        <option value="">-- Pilih Metode --</option>
                                        <option value="Tunai">Tunai</option>
                                        <option value="Transfer">Transfer</option>
                                        <option value="QRIS">QRIS</option>
                                    </select>
                                    <label>Metode Pembayaran</label>
                                </div>
                                <div class="col-md-4">
                                    <div id="tunai_section" style="display:none;">
                                        <div class="form-group floating-label">
                                            <input type="number" class="form-control" name="jumlah_dibayar_tunai" id="jumlah_dibayar_tunai" min="0" placeholder=" ">
                                            <label>Jumlah Dibayar</label>
                                        </div>
                                        <div class="alert alert-success py-2">
                                            <small><i class="fas fa-coins mr-1"></i> Kembalian: Rp <span id="kembalian">0</span></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 form-group floating-label">
                                    <textarea class="form-control" name="keterangan" rows="2" placeholder=" "></textarea>
                                    <label>Catatan (Opsional)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="col-lg-4">
                    <div class="card total-card bg-gradient-primary text-white shadow-xl pulse">
                        <div class="card-body text-center">
                            <h5 class="mb-2">No. Nota</h5>
                            <h4 class="mb-3 font-weight-bold"><?= $no_transaksi ?></h4>
                            <hr class="bg-white my-3">
                            <h3 class="mb-3">TOTAL BAYAR</h3>
                            <h1 class="mb-0">Rp <span id="grand_total_display" class="highlight">0</span></h1>
                            <input type="hidden" id="grand_total_hidden" name="total_bayar" value="0">
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="row">
                                <div class="col-6">
                                    <button type="button" class="btn btn-light btn-block" id="btnBatal">
                                        <i class="fas fa-times mr-1"></i> Batal
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="submit" class="btn btn-success btn-block" id="btnBayar">
                                        <i class="fas fa-check mr-1"></i> Bayar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Perhitungan -->
                    <div class="card bg-gradient-light mt-3">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title"><i class="fas fa-calculator mr-2"></i> Detail Perhitungan</h3>
                        </div>
                        <div class="card-body small">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sub Total</span>
                                <span id="sub_total_display" class="font-weight-bold">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Diskon Item</span>
                                <span id="diskon_item_display" class="text-danger">- Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Diskon Global</span>
                                <input type="number" class="form-control form-control-sm text-right w-50" id="diskon_global" value="0" min="0">
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between text-success font-weight-bold">
                                <span>Grand Total</span>
                                <span id="grand_total_display2">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal Cari Member -->
    <div class="modal fade" id="modalCariMember">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-search mr-2"></i> Cari Member</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-striped table-hover" id="tblMember">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $m): ?>
                                <tr>
                                    <td><?= $m['id_member'] ?></td>
                                    <td><?= htmlspecialchars($m['nama']) ?></td>
                                    <td><?= htmlspecialchars($m['email']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info btn-pilih-member" data-id="<?= $m['id_member'] ?>" data-nama="<?= htmlspecialchars($m['nama']) ?>">
                                            <i class="fas fa-check mr-1"></i> Pilih
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../../../view/master/footer.php'; ?>

<!-- Tambahkan library untuk tampilan modern -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

<script>
    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function hitungTotal() {
        const harga = parseFloat($('#id_paket').find(':selected').data('harga')) || 0;
        const qty = parseInt($('#qty').val()) || 1;
        const diskon_item = parseFloat($('#diskon_item').val()) || 0;
        const diskon_global = parseFloat($('#diskon_global').val()) || 0;

        const subtotal = harga * qty;
        const total_setelah_diskon_item = subtotal - diskon_item;
        const grand_total = total_setelah_diskon_item - diskon_global;
        const final_total = grand_total < 0 ? 0 : grand_total;

        $('#info_nama').val($('#id_paket').find(':selected').data('nama') || '-');
        $('#info_harga').val('Rp ' + formatRupiah(harga));
        $('#info_qty').val(qty);
        $('#info_diskon').val('Rp ' + formatRupiah(diskon_item));
        $('#info_subtotal').val('Rp ' + formatRupiah(subtotal));

        $('#sub_total_display').text('Rp ' + formatRupiah(subtotal));
        $('#diskon_item_display').text('- Rp ' + formatRupiah(diskon_item));
        $('#grand_total_display').text(formatRupiah(final_total));
        $('#grand_total_display2').text('Rp ' + formatRupiah(final_total));
        $('#grand_total_hidden').val(final_total.toFixed(2));

        // Update kembalian
        if ($('#metode_pembayaran').val() === 'Tunai') {
            const dibayar = parseFloat($('#jumlah_dibayar_tunai').val()) || 0;
            const kembalian = dibayar - final_total;
            $('#kembalian').text(formatRupiah(kembalian > 0 ? kembalian : 0));
        }

        // Update step indicator
        if ($('#id_paket').val()) {
            $('.step').eq(1).addClass('active');
        } else {
            $('.step').eq(1).removeClass('active');
        }

        if ($('#metode_pembayaran').val()) {
            $('.step').eq(2).addClass('active');
        } else {
            $('.step').eq(2).removeClass('active');
        }
    }

    $(document).ready(function() {



        // Inisialisasi DataTables untuk modal member
        $('#tblMember').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": false,
            "lengthChange": false,
            "pageLength": 5,
            "language": {
                "search": "Cari:",
                "paginate": {
                    "previous": "<i class='fas fa-chevron-left'></i>",
                    "next": "<i class='fas fa-chevron-right'></i>"
                }
            }
        });

        // Pilih member
        $(document).on('click', '.btn-pilih-member', function() {
            $('#id_member_hidden').val($(this).data('id'));
            $('#nama_member_display').val($(this).data('nama'));
            $('#modalCariMember').modal('hide');

            // Update step indicator
            $('.step').eq(0).addClass('active');
        });

        // Reset member
        $('#nama_member_display').click(function() {
            if ($('#id_member_hidden').val()) {
                Swal.fire({
                    title: 'Reset ke Umum?',
                    text: 'Data member yang dipilih akan dihapus',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4361ee',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Reset',
                    cancelButtonText: 'Batal'
                }).then(r => {
                    if (r.isConfirmed) {
                        $('#id_member_hidden').val('');
                        $('#nama_member_display').val('Umum');
                        $('.step').eq(0).removeClass('active');
                    }
                });
            }
        });

        // Hitung total otomatis
        $('#id_paket, #qty, #diskon_item, #diskon_global').on('change input', function() {
            if ($('#id_paket').val()) {
                $('#cardPaketInfo').fadeIn();
                hitungTotal();
            } else {
                $('#cardPaketInfo').fadeOut();
                $('#info_nama, #info_harga, #info_qty, #info_diskon, #info_subtotal').val('');
                $('#sub_total_display').text('Rp 0');
                $('#diskon_item_display').text('- Rp 0');
                $('#grand_total_display').text('0');
                $('#grand_total_display2').text('Rp 0');
                $('#grand_total_hidden').val(0);
                $('#kembalian').text('0');
                $('.step').eq(1).removeClass('active');
            }
        });

        // Btn Hitung manual
        $('#btnHitung').click(hitungTotal);

        // Metode pembayaran
        $('#metode_pembayaran').change(function() {
            const total = parseFloat($('#grand_total_hidden').val()) || 0;
            if ($(this).val() === 'Tunai') {
                $('#tunai_section').slideDown();
                $('#jumlah_dibayar_tunai').val(total.toFixed(2));
            } else {
                $('#tunai_section').slideUp();
                $('#jumlah_dibayar_tunai').val(0);
            }
            hitungTotal();
        });

        $('#jumlah_dibayar_tunai').on('input', hitungTotal);

        // Submit form
        $('#formTransaksi').submit(function(e) {
            e.preventDefault();

            if (!$('#id_paket').val()) {
                return Swal.fire({
                    title: 'Pilih paket!',
                    text: 'Silakan pilih paket terlebih dahulu',
                    icon: 'warning',
                    confirmButtonColor: '#4361ee'
                });
            }
            if (!$('#metode_pembayaran').val()) {
                return Swal.fire({
                    title: 'Pilih metode pembayaran!',
                    text: 'Silakan pilih metode pembayaran terlebih dahulu',
                    icon: 'warning',
                    confirmButtonColor: '#4361ee'
                });
            }
            if ($('#metode_pembayaran').val() === 'Tunai') {
                const dibayar = parseFloat($('#jumlah_dibayar_tunai').val()) || 0;
                const total = parseFloat($('#grand_total_hidden').val()) || 0;
                if (dibayar < total) {
                    return Swal.fire({
                        title: 'Uang kurang!',
                        text: 'Jumlah dibayar kurang dari total.',
                        icon: 'warning',
                        confirmButtonColor: '#4361ee'
                    });
                }
            }

            const formData = new FormData(this);
            formData.append('action', 'create_single_item');
            formData.append('id_paket', $('#id_paket').val());
            formData.append('harga_paket', $('#id_paket').find(':selected').data('harga'));
            formData.append('qty', $('#qty').val());
            formData.append('potongan_diskon_item', $('#diskon_item').val());
            formData.append('potongan_diskon', $('#diskon_global').val());

            $.ajax({
                url: 'proses_transaksi.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: () => {
                    $('#btnBayar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
                    $('.step').eq(3).addClass('active');
                },
                success: res => {
                    if (res.success) {
                        Swal.fire({
                            title: 'Transaksi Berhasil!',
                            html: `Transaksi <strong>${res.id}</strong> berhasil diproses`,
                            icon: 'success',
                            confirmButtonColor: '#4361ee'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            title: 'Transaksi Gagal',
                            text: res.error,
                            icon: 'error',
                            confirmButtonColor: '#4361ee'
                        });
                        $('.step').eq(3).removeClass('active');
                    }
                },
                error: xhr => {
                    Swal.fire({
                        title: 'Error',
                        text: xhr.responseText.substring(0, 200),
                        icon: 'error',
                        confirmButtonColor: '#4361ee'
                    });
                    $('.step').eq(3).removeClass('active');
                },
                complete: () => $('#btnBayar').prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Bayar')
            });
        });

        $('#btnBatal').click(() => {
            Swal.fire({
                title: 'Batalkan Transaksi?',
                text: 'Semua data yang telah diinput akan hilang',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4361ee',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Lanjutkan'
            }).then(r => {
                if (r.isConfirmed) location.reload();
            });
        });

        // Floating label functionality
        $('.floating-label .form-control').on('focus', function() {
            $(this).siblings('label').addClass('active');
        }).on('blur', function() {
            if (!$(this).val()) {
                $(this).siblings('label').removeClass('active');
            }
        });

        // Initialize floating labels for pre-filled values
        $('.floating-label .form-control').each(function() {
            if ($(this).val()) {
                $(this).siblings('label').addClass('active');
            }
        });
    });
</script>