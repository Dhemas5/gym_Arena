<?php
$body_class = 'sidebar-collapse';
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";

date_default_timezone_set('Asia/Jakarta');

$queryPaket = $con->query("SELECT id_paket, nama_paket, harga, durasi_hari FROM tbl_paket WHERE tipe_penjualan IN ('offline','keduanya')");
$queryMember = $con->query("SELECT id_member, nama, email FROM vw_member_status WHERE (is_active = 0 OR is_active IS NULL) ORDER BY nama ASC");

$pakets = $queryPaket ? $queryPaket->fetch_all(MYSQLI_ASSOC) : [];
$members = $queryMember ? $queryMember->fetch_all(MYSQLI_ASSOC) : [];

$nama_kasir = htmlspecialchars($_SESSION['username'] ?? 'Kasir');
$id_kasir = $_SESSION['id_user'] ?? 1;
$tanggal_saat_ini = date('d/m/Y');

include '../../../view/master/header.php';
include '../../../view/master/sidebar.php';
?>

<section class="content-header">
    <div class="container-fluid">
        <h1>Transaksi Penjualan Paket</h1>
        <p class="text-muted">QRIS DANA: <strong>085719630447</strong> | Tunai: Hitung Kembalian</p>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <form id="formTransaksi">
            <input type="hidden" name="id_user_kasir" value="<?= $id_kasir ?>">
            <input type="hidden" name="id_member" id="id_member_hidden" value="">
            <input type="hidden" name="qty" value="1">
            <input type="hidden" name="harga_paket" id="harga_paket_hidden" value="0">
            <input type="hidden" id="grand_total_hidden" value="0">

            <div class="row">
                <div class="col-lg-8">
                    <!-- STEP 1 -->
                    <div class="card card-outline card-primary" id="step1">
                        <div class="card-header">
                            <h3 class="card-title">Pilih Member (Opsional)</h3>
                        </div>
                        <div class="card-body">
                            <select id="pilih_member" class="form-control">
                                <option value="">-- Pelanggan Umum --</option>
                                <?php foreach ($members as $mem): ?>
                                    <option value="<?= $mem['id_member'] ?>"><?= htmlspecialchars($mem['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="card-footer text-right">
                            <button type="button" class="btn btn-primary" id="btnLanjutStep1">Lanjut</button>
                        </div>
                    </div>

                    <!-- STEP 2 -->
                    <div class="card card-outline card-primary d-none" id="step2">
                        <div class="card-header">
                            <h3 class="card-title">Pilih Paket</h3>
                        </div>
                        <div class="card-body">
                            <select id="id_paket" class="form-control" name="id_paket">
                                <option value="">-- Pilih Paket --</option>
                                <?php foreach ($pakets as $pkt): ?>
                                    <option value="<?= $pkt['id_paket'] ?>" data-harga="<?= $pkt['harga'] ?>">
                                        <?= $pkt['nama_paket'] ?> (Rp <?= number_format($pkt['harga'], 0, ',', '.') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="card-footer text-right">
                            <button type="button" class="btn btn-secondary" id="btnKembaliStep2">Kembali</button>
                            <button type="button" class="btn btn-primary" id="btnLanjutStep2">Lanjut</button>
                        </div>
                    </div>

                    <!-- STEP 3 -->
                    <div class="card card-outline card-primary d-none" id="step3">
                        <div class="card-header">
                            <h3 class="card-title">Pembayaran</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Metode</label>
                                        <select id="metode_pembayaran" class="form-control" name="metode_pembayaran">
                                            <option value="TUNAI">Tunai</option>
                                            <option value="QRIS">QRIS (DANA 085719630447)</option>
                                        </select>
                                    </div>

                                    <div class="form-group" id="tunai_group">
                                        <label>Jumlah Dibayar (Tunai)</label>
                                        <input type="number" class="form-control" id="jumlah_dibayar_tunai">
                                        <small class="text-success" id="kembalian_info" style="display:none;">Kembalian: <strong id="kembalian">Rp 0</strong></small>
                                    </div>

                                    <div id="qris_info" class="alert alert-warning d-none">
                                        <h5>QRIS DANA</h5>
                                        <p><strong>Nomor: 085719630447</strong></p>
                                        <p><em>Scan QR di meja kasir → Transfer → Masukkan nominal → Klik "Sukses"</em></p>
                                    </div>
                                    <div id="qris_input" class="d-none">
                                        <div class="form-group">
                                            <label>Nominal Transfer (Rp)</label>
                                            <input type="number" class="form-control" id="nominal_qris">
                                        </div>
                                        <button type="button" class="btn btn-success btn-block" id="btnSuksesQRIS">
                                            <i class="fas fa-check"></i> Sukses (Bayar Diterima)
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Diskon Item</label><input type="number" class="form-control" id="diskon_item" value="0"></div>
                                    <div class="form-group"><label>Diskon Global</label><input type="number" class="form-control" id="diskon_global" value="0"></div>
                                    <div class="form-group"><label>Keterangan</label><textarea class="form-control" id="keterangan" rows="2"></textarea></div>
                                </div>
                            </div>
                            <hr>
                            <div class="text-right">
                                <h4>Grand Total: <strong id="preview_grand_total">Rp 0</strong></h4>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="button" class="btn btn-secondary" id="btnKembaliStep3">Kembali</button>
                            <button type="submit" class="btn btn-success" id="btnBayar"><i class="fas fa-check mr-1"></i> Bayar</button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Preview</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Kasir:</strong> <span id="preview_kasir"><?= $nama_kasir ?></span></p>
                            <p><strong>Tanggal:</strong> <span id="preview_tanggal"><?= $tanggal_saat_ini ?></span></p>
                            <p><strong>Member:</strong> <span id="preview_member">Umum</span></p>
                            <p><strong>Paket:</strong> <span id="preview_paket">-</span></p>
                            <p><strong>Metode:</strong> <span id="preview_metode">TUNAI</span></p>
                            <p><strong>Keterangan:</strong> <span id="preview_keterangan">-</span></p>
                            <hr>
                            <p class="text-right"><strong>Total: <span id="preview_total">Rp 0</span></strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const formatIDR = (num) => 'Rp ' + parseInt(num).toLocaleString('id-ID');
    let hargaPaket = 0;

    const updatePreview = () => {
        const diskonItem = parseFloat($('#diskon_item').val()) || 0;
        const diskonGlobal = parseFloat($('#diskon_global').val()) || 0;
        const total = Math.max(0, hargaPaket - diskonItem - diskonGlobal);
        $('#grand_total_hidden').val(total);
        $('#preview_total').text(formatIDR(total));
        $('#preview_grand_total').text(formatIDR(total));
    };

    $('#btnLanjutStep1').click(() => {
        const mem = $('#pilih_member').val();
        $('#id_member_hidden').val(mem);
        $('#preview_member').text(mem ? $('#pilih_member option:selected').text() : 'Umum');
        $('#step1').addClass('d-none');
        $('#step2').removeClass('d-none');
    });

    $('#btnKembaliStep2').click(() => {
        $('#step2').addClass('d-none');
        $('#step1').removeClass('d-none');
    });
    $('#btnLanjutStep2').click(() => {
        if (!$('#id_paket').val()) return Swal.fire('Pilih Paket', 'Silakan pilih paket.', 'warning');
        const opt = $('#id_paket option:selected');
        hargaPaket = parseFloat(opt.data('harga')) || 0;
        $('#harga_paket_hidden').val(hargaPaket);
        $('#preview_paket').text(opt.text());
        $('#step2').addClass('d-none');
        $('#step3').removeClass('d-none');
        updatePreview();
    });
    $('#btnKembaliStep3').click(() => {
        $('#step3').addClass('d-none');
        $('#step2').removeClass('d-none');
    });

    $('#metode_pembayaran').change(function() {
        const val = $(this).val();
        $('#preview_metode').text(val);
        if (val === 'TUNAI') {
            $('#tunai_group').removeClass('d-none');
            $('#qris_info, #qris_input').addClass('d-none');
        } else {
            $('#tunai_group').addClass('d-none');
            $('#qris_info, #qris_input').removeClass('d-none');
        }
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

    $('#btnSuksesQRIS').click(function() {
        const nominal = parseFloat($('#nominal_qris').val()) || 0;
        const grandTotal = parseFloat($('#grand_total_hidden').val()) || 0;
        if (nominal < grandTotal) return Swal.fire('Kurang', 'Nominal transfer kurang.', 'warning');

        const data = {
            id_user_kasir: $('[name=id_user_kasir]').val(),
            id_member: $('#id_member_hidden').val(),
            id_paket: $('#id_paket').val(),
            harga_paket: $('#harga_paket_hidden').val(),
            qty: 1,
            potongan_diskon_item: $('#diskon_item').val(),
            potongan_diskon: $('#diskon_global').val(),
            metode_pembayaran: 'QRIS',
            jumlah_dibayar_tunai: nominal,
            keterangan: $('#keterangan').val()
        };

        $.post('proses_transaksi.php', data, function(res) {
            if (res.success) {
                Swal.fire('Sukses!', `Transaksi ${res.id_transaksi} via QRIS berhasil!`, 'success')
                    .then(() => window.location.href = 'transaksi.php');
            } else {
                Swal.fire('Gagal', res.error, 'error');
            }
        }, 'json');
    });

    $('#formTransaksi').submit(function(e) {
        e.preventDefault();
        const grandTotal = parseFloat($('#grand_total_hidden').val()) || 0;
        if (grandTotal <= 0) return Swal.fire('Error', 'Total tidak boleh 0', 'error');

        const metode = $('#metode_pembayaran').val();
        if (metode === 'TUNAI') {
            const dibayar = parseFloat($('#jumlah_dibayar_tunai').val()) || 0;
            if (dibayar < grandTotal) return Swal.fire('Uang Kurang', 'Jumlah tunai kurang.', 'warning');
        }

        const data = {
            id_user_kasir: $('[name=id_user_kasir]').val(),
            id_member: $('#id_member_hidden').val(),
            id_paket: $('#id_paket').val(),
            harga_paket: $('#harga_paket_hidden').val(),
            qty: 1,
            potongan_diskon_item: $('#diskon_item').val(),
            potongan_diskon: $('#diskon_global').val(),
            metode_pembayaran: metode,
            jumlah_dibayar_tunai: metode === 'TUNAI' ? $('#jumlah_dibayar_tunai').val() : '',
            keterangan: $('#keterangan').val()
        };

        $.post('proses_transaksi.php', data, function(res) {
            if (res.success) {
                let pesan = `Transaksi berhasil! No: <strong>${res.id_transaksi}</strong>`;
                if (res.kembalian > 0) pesan += `<br>Kembalian: <strong>${formatIDR(res.kembalian)}</strong>`;
                Swal.fire({
                        icon: 'success',
                        title: 'Sukses!',
                        html: pesan
                    })
                    .then(() => window.location.href = 'transaksi.php');
            } else {
                Swal.fire('Gagal', res.error, 'error');
            }
        }, 'json');
    });

    updatePreview();
</script>

<?php include '../../../view/master/footer.php'; ?>