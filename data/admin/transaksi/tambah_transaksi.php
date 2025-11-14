<?php
$body_class = 'sidebar-collapse';
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";

date_default_timezone_set('Asia/Jakarta');

// Ambil data paket (wajib sertakan durasi_hari)
$queryPaket = $con->query("
    SELECT id_paket, nama_paket, harga, durasi_hari 
    FROM tbl_paket 
    WHERE tipe_penjualan IN ('offline','keduanya')
");
$queryMember = $con->query("
    SELECT id_member, nama, email 
    FROM vw_member_status 
    WHERE (is_active = 0 OR is_active IS NULL)
    ORDER BY nama ASC
");

$pakets = $queryPaket ? $queryPaket->fetch_all(MYSQLI_ASSOC) : [];
$members = $queryMember ? $queryMember->fetch_all(MYSQLI_ASSOC) : [];

$nama_kasir = htmlspecialchars($_SESSION['username'] ?? 'Kasir');
$id_kasir = $_SESSION['id_user'] ?? 1;
$tanggal_saat_ini = date('d/m/Y');
$no_transaksi = 'TRX' . date('ymd') . 'XXXX';

include '../../../view/master/header.php';
include '../../../view/master/sidebar.php';
?>

<link rel="stylesheet" href="../../../assets/assets_admin/dist/css/custom-tambah_transaksi.css">

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Transaksi Penjualan Paket</h1>
                <p class="text-muted">Lakukan transaksi penjualan paket kepada member atau pelanggan umum</p>
            </div>
            <div class="col-sm-6">
                <a href="transaksi.php" class="btn btn-sm btn-outline-primary float-sm-right">
                    <i class="fas fa-list mr-1"></i> Daftar Transaksi
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <form id="formTransaksi">
            <input type="hidden" name="id_user_kasir" value="<?= $id_kasir ?>">
            <input type="hidden" name="id_member" id="id_member_hidden" value="">
            <input type="hidden" name="qty" value="1">
            <input type="hidden" name="harga_paket" id="harga_paket_hidden" value="0">
            <input type="hidden" id="grand_total_hidden" name="grand_total_hidden" value="0">

            <div class="row">
                <div class="col-lg-8">

                    <!-- STEP 1: PILIH MEMBER -->
                    <div class="card card-outline card-primary step-card" id="step1">
                        <div class="card-header">
                            <h3 class="card-title">Pilih Member (Opsional)</h3>
                        </div>
                        <div class="card-body">
                            <select id="pilih_member" class="form-control">
                                <option value="">-- Pelanggan Umum --</option>
                                <?php foreach ($members as $mem): ?>
                                    <option value="<?= $mem['id_member'] ?>" data-email="<?= htmlspecialchars($mem['email']) ?>">
                                        <?= htmlspecialchars($mem['nama']) ?> (<?= htmlspecialchars($mem['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="card-footer text-right">
                            <button type="button" class="btn btn-primary" id="btnLanjutStep1">Lanjut</button>
                        </div>
                    </div>

                    <!-- Pilih Paket -->
                    <div class="card mt-4 animate-fade-in" style="animation-delay: 0.1s;">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title"><i class="fas fa-box-open mr-2"></i> Pilih Paket</h3>
                    <!-- STEP 2: PILIH PAKET -->
                    <div class="card card-outline card-primary step-card d-none" id="step2">
                        <div class="card-header">
                            <h3 class="card-title">Pilih Paket</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="id_paket">Pilih Paket</label>
                                <select id="id_paket" class="form-control" name="id_paket">
                                    <option value="">-- Pilih Paket --</option>
                                    <?php foreach ($pakets as $pkt) { ?>
                                        <option value="<?= htmlspecialchars($pkt['id_paket']); ?>"
                                            data-harga="<?= htmlspecialchars($pkt['harga']); ?>"
                                            data-durasi="<?= htmlspecialchars($pkt['durasi_hari']); ?>">
                                            <?= htmlspecialchars($pkt['nama_paket']); ?>
                                            (Rp <?= number_format($pkt['harga'], 0, ',', '.'); ?>)
                                        </option>
                                    <?php } ?>
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
                            <div class="form-group">
                                <label>Diskon Item (Rp)</label>
                                <input type="number" class="form-control" id="diskon_item" name="potongan_diskon_item" value="0" min="0">
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="button" class="btn btn-secondary" id="btnKembaliStep2">Kembali</button>
                            <button type="button" class="btn btn-primary" id="btnLanjutStep2">Lanjut</button>
                        </div>
                    </div>

                    <!-- Pembayaran -->
                    <div class="card mt-4 animate-fade-in" style="animation-delay: 0.2s;">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title"><i class="fas fa-credit-card mr-2"></i> Pembayaran</h3>
                    <!-- STEP 3: PEMBAYARAN -->
                    <div class="card card-outline card-primary step-card d-none" id="step3">
                        <div class="card-header">
                            <h3 class="card-title">Pembayaran</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <th>Sub Total</th>
                                    <td id="sub_total" class="text-right">Rp 0</td>
                                </tr>
                                <tr>
                                    <th>Diskon Item</th>
                                    <td id="diskon_item_display" class="text-right">Rp 0</td>
                                </tr>
                                <tr>
                                    <th>Diskon Global</th>
                                    <td><input type="number" id="diskon_global" name="potongan_diskon" class="form-control form-control-sm text-right" value="0" min="0"></td>
                                </tr>
                                <tr class="table-primary">
                                    <th>Grand Total</th>
                                    <td id="grand_total" class="text-right">Rp 0</td>
                                </tr>
                            </table>

                            <div class="form-group">
                                <label>Metode Pembayaran</label>
                                <select id="metode_pembayaran" name="metode_pembayaran" class="form-control">
                                    <option value="Tunai">Tunai</option>
                                    <option value="Transfer">Transfer Bank</option>
                                    <option value="Kartu Kredit">Kartu Kredit</option>
                                </select>
                            </div>

                            <div class="form-group d-none" id="tunai_group">
                                <label>Jumlah Dibayar (Tunai)</label>
                                <input type="number" id="jumlah_dibayar_tunai" name="jumlah_dibayar_tunai" class="form-control" value="0" min="0">
                                <div class="mt-2 alert alert-info d-none" id="kembalian_info">
                                    <strong>Kembalian:</strong> Rp <span id="kembalian">0</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea id="keterangan" name="keterangan" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="button" class="btn btn-secondary" id="btnKembaliStep3">Kembali</button>
                            <button type="submit" class="btn btn-primary" id="btnBayar"><i class="fas fa-check mr-1"></i> Bayar</button>
                        </div>
                    </div>

                </div>

                <!-- SIDEBAR PREVIEW -->
                <div class="col-lg-4">
                    <div class="card card-outline card-secondary sticky-top">
                        <div class="card-header">
                            <h3 class="card-title">Preview Nota</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-receipt fa-3x text-muted"></i>
                                <h5 id="preview_no_transaksi" class="mt-2"><?= $no_transaksi ?></h5>
                            </div>
                            <hr>
                            <p><strong>Member:</strong> <span id="preview_member">Pelanggan Umum</span></p>
                            <p><strong>Paket:</strong> <span id="preview_paket">-</span></p>
                            <p><strong>Harga:</strong> Rp <span id="preview_harga">0</span></p>
                            <p><strong>Sub Total:</strong> Rp <span id="preview_sub_total">0</span></p>
                            <p><strong>Diskon Item:</strong> Rp <span id="preview_diskon_item">0</span></p>
                            <p><strong>Diskon Global:</strong> Rp <span id="preview_diskon_global">0</span></p>
                            <hr>
                            <p class="font-weight-bold"><strong>Grand Total:</strong> Rp <span id="preview_grand_total">0</span></p>
                            <p><strong>Metode:</strong> <span id="preview_metode">-</span></p>
                            <p><strong>Keterangan:</strong> <span id="preview_keterangan">-</span></p>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</section>

<?php include '../../../view/master/footer.php'; ?>

<script>
    $(function() {
        let hargaPaket = 0;
        const qty = 1;

        function formatIDR(n) {
            return (Number(n) || 0).toLocaleString('id-ID');
        }

        function updatePreview() {
            const diskonItem = parseFloat($('#diskon_item').val()) || 0;
            const diskonGlobal = parseFloat($('#diskon_global').val()) || 0;
            const subTotal = hargaPaket * qty;
            const afterItem = Math.max(0, subTotal - diskonItem);
            const grandTotal = Math.max(0, afterItem - diskonGlobal);

            $('#preview_harga').text(formatIDR(hargaPaket));
            $('#preview_sub_total').text(formatIDR(subTotal));
            $('#preview_diskon_item').text(formatIDR(diskonItem));
            $('#preview_diskon_global').text(formatIDR(diskonGlobal));
            $('#preview_grand_total').text(formatIDR(grandTotal));

            $('#sub_total').text('Rp ' + formatIDR(subTotal));
            $('#diskon_item_display').text('Rp ' + formatIDR(diskonItem));
            $('#grand_total').text('Rp ' + formatIDR(grandTotal));
            $('#grand_total_hidden').val(grandTotal);
            $('#harga_paket_hidden').val(hargaPaket);

            $('#preview_metode').text($('#metode_pembayaran').val());
            $('#preview_keterangan').text($('#keterangan').val() || '-');
        }

        $('#btnLanjutStep1').click(function() {
            const mem = $('#pilih_member').val();
            $('#id_member_hidden').val(mem);
            $('#preview_member').text(mem ? $('#pilih_member option:selected').text() : 'Pelanggan Umum');
            $('#step1').addClass('d-none');
            $('#step2').removeClass('d-none');
        });

        $('#btnKembaliStep2').click(() => {
            $('#step2').addClass('d-none');
            $('#step1').removeClass('d-none');
        });
        $('#btnLanjutStep2').click(function() {
            if (!$('#id_paket').val()) return Swal.fire('Pilih Paket', 'Silakan pilih paket terlebih dahulu.', 'warning');
            $('#preview_paket').text($('#id_paket option:selected').text());
            $('#step2').addClass('d-none');
            $('#step3').removeClass('d-none');
            updatePreview();
        });
        $('#btnKembaliStep3').click(() => {
            $('#step3').addClass('d-none');
            $('#step2').removeClass('d-none');
        });

        $('#id_paket').change(function() {
            const opt = $(this).find(':selected');
            hargaPaket = parseFloat(opt.data('harga')) || 0;
            updatePreview();
        });
        $('#diskon_item,#diskon_global,#keterangan').on('input', updatePreview);
        $('#metode_pembayaran').change(function() {
            const val = $(this).val();
            if (val === 'Tunai') {
                $('#tunai_group').removeClass('d-none');
            } else {
                $('#tunai_group').addClass('d-none');
                $('#kembalian_info').addClass('d-none');
            }
            updatePreview();
        });

        $('#jumlah_dibayar_tunai').on('input', function() {
            const dibayar = parseFloat($(this).val()) || 0;
            const grand = parseFloat($('#grand_total_hidden').val()) || 0;
            if (dibayar >= grand) {
                $('#kembalian').text(formatIDR(dibayar - grand));
                $('#kembalian_info').removeClass('d-none');
            } else {
                $('#kembalian_info').addClass('d-none');
            }
        });

        $('#formTransaksi').submit(function(e) {
            e.preventDefault();
            const grandTotal = parseFloat($('#grand_total_hidden').val()) || 0;
            if (grandTotal <= 0) return Swal.fire('Total 0', 'Grand total tidak boleh 0', 'warning');

            const metode = $('#metode_pembayaran').val();
            if (metode === 'Tunai') {
                const dibayar = parseFloat($('#jumlah_dibayar_tunai').val()) || 0;
                if (dibayar < grandTotal) return Swal.fire('Uang kurang', 'Jumlah tunai kurang dari total.', 'warning');
            }

            const data = {
                action: 'create_single_item',
                id_user_kasir: $('input[name=id_user_kasir]').val(),
                id_member: $('#id_member_hidden').val(),
                id_paket: $('#id_paket').val(),
                harga_paket: $('#harga_paket_hidden').val(),
                qty: 1,
                potongan_diskon_item: $('#diskon_item').val(),
                potongan_diskon: $('#diskon_global').val(),
                metode_pembayaran: metode,
                jumlah_dibayar_tunai: $('#jumlah_dibayar_tunai').val(),
                keterangan: $('#keterangan').val()
            };

            $('#btnBayar').prop('disabled', true).text('Proses...');
            $.post('proses_transaksi.php', data, function(res) {
                if (res.success) {
                    Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Transaksi berhasil. No: ' + res.id_transaksi
                        })
                        .then(() => window.location.href = 'transaksi.php');
                } else {
                    Swal.fire('Gagal', res.error || 'Terjadi kesalahan', 'error');
                }
            }, 'json').fail(function(xhr) {
                let msg = 'Terjadi error koneksi.';
                try {
                    const j = JSON.parse(xhr.responseText);
                    msg = j.error || msg;
                } catch (e) {}
                Swal.fire('Error', msg, 'error');
            }).always(() => $('#btnBayar').prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Bayar'));
        });

        updatePreview();
    });
</script>