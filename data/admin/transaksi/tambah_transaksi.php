<?php
$body_class = 'sidebar-collapse';
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";

// Ambil semua paket + harga_umum dan harga_mahasiswa
$pakets = $con->query("
    SELECT id_paket, nama_paket, harga_umum, harga_mahasiswa, durasi_hari 
    FROM tbl_paket 
    ORDER BY nama_paket
")->fetch_all(MYSQLI_ASSOC);

// Ambil member aktif yang membershipnya belum aktif atau sudah expired
$members = $con->query("
    SELECT m.id_member, m.nama, m.is_mahasiswa
    FROM tbl_member m
    WHERE m.status_akun = 'aktif'
      AND (m.membership_status != 'aktif' OR m.membership_status IS NULL)
    ORDER BY m.nama
")->fetch_all(MYSQLI_ASSOC);

include '../../../view/master/header.php';
include '../../../view/master/sidebar.php';
?>

<section class="content-header">
    <div class="container-fluid">
        <h1><i class="fas fa-cash-register"></i> Transaksi Baru (Offline)</h1>
        <p class="text-muted">Pilih member → Pilih paket → Harga otomatis sesuai status mahasiswa</p>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <form id="formTransaksi">
                    <input type="hidden" name="id_user_kasir" value="<?= $_SESSION['id_user'] ?? 1 ?>">

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3><i class="fas fa-user"></i> Pilih Member (Opsional)</h3>
                        </div>
                        <div class="card-body">
                            <select id="id_member" class="form-control select2">
                                <option value="">-- Pelanggan Umum (Non-Member) --</option>
                                <?php foreach ($members as $m): ?>
                                    <option value="<?= $m['id_member'] ?>"
                                        data-mahasiswa="<?= $m['is_mahasiswa'] ?>">
                                        <?= htmlspecialchars($m['nama']) ?>
                                        <?= $m['is_mahasiswa'] ? ' (Mahasiswa)' : ' (Umum)' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Jika member dipilih → harga & membership otomatis sesuai status</small>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header bg-success text-white">
                            <h3><i class="fas fa-dumbbell"></i> Pilih Paket Membership</h3>
                        </div>
                        <div class="card-body">
                            <select name="id_paket" id="id_paket" class="form-control select2" required>
                                <option value="">-- Pilih Paket --</option>
                                <?php foreach ($pakets as $p): ?>
                                    <option value="<?= $p['id_paket'] ?>"
                                        data-harga-umum="<?= $p['harga_umum'] ?>"
                                        data-harga-mahasiswa="<?= $p['harga_mahasiswa'] ?>"
                                        data-durasi="<?= $p['durasi_hari'] ?>">
                                        <?= htmlspecialchars($p['nama_paket']) ?>
                                        → Umum: Rp <?= number_format($p['harga_umum'], 0, ',', '.') ?>
                                        | Mahasiswa: Rp <?= number_format($p['harga_mahasiswa'], 0, ',', '.') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="mt-2">
                                <strong>Harga yang akan dibayar: <span id="harga_terpilih" class="text-success">Rp 0</span></strong>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header bg-warning text-dark">
                            <h3><i class="fas fa-coins"></i> Pembayaran</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Diskon (Rp)</label>
                                    <input type="number" id="diskon" class="form-control" value="0" min="0">
                                </div>
                                <div class="col-md-6">
                                    <label>Metode Pembayaran</label>
                                    <select id="metode" class="form-control">
                                        <option value="TUNAI">Tunai</option>
                                        <option value="QRIS">QRIS DANA (085719630447)</option>
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group">
                                <label>Jumlah Dibayar</label>
                                <input type="number" id="dibayar" class="form-control" placeholder="Masukkan nominal" required>
                                <small id="info_kembalian" class="text-success font-weight-bold"></small>
                            </div>

                            <div class="alert alert-primary text-center">
                                <h3>Total Bayar: <strong id="total_bayar">Rp 0</strong></h3>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-lg btn-success">
                                <i class="fas fa-check-circle"></i> Selesai Transaksi
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-dark text-white text-center">
                        <h4>QRIS DANA</h4>
                    </div>
                    <div class="card-body text-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=085719630447" class="img-fluid">
                        <h5 class="mt-3">085719630447</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../../../view/master/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const formatRupiah = (num) => 'Rp ' + parseInt(num || 0).toLocaleString('id-ID');

    let hargaPaket = 0;
    let durasiHari = 0;
    let isMahasiswa = false;

    // Update harga saat pilih member
    $('#id_member').change(function() {
        isMahasiswa = $(this).find(':selected').data('mahasiswa') == 1;
        updateHargaTerpilih();
    });

    // Update saat pilih paket
    $('#id_paket').change(function() {
        const opt = $(this).find(':selected');
        const hargaUmum = parseFloat(opt.data('harga-umum')) || 0;
        const hargaMhs = parseFloat(opt.data('harga-mahasiswa')) || 0;
        durasiHari = parseInt(opt.data('durasi')) || 30;

        hargaPaket = isMahasiswa ? hargaMhs : hargaUmum;
        updateHargaTerpilih();
    });

    function updateHargaTerpilih() {
        $('#harga_terpilih').text(formatRupiah(hargaPaket));
        hitungTotal();
    }

    $('#diskon, #dibayar').on('input', hitungTotal);

    function hitungTotal() {
        const diskon = parseFloat($('#diskon').val()) || 0;
        const total = Math.max(0, hargaPaket - diskon);
        $('#total_bayar').text(formatRupiah(total));

        const dibayar = parseFloat($('#dibayar').val()) || 0;
        if (dibayar >= total && total > 0) {
            $('#info_kembalian').text('Kembalian: ' + formatRupiah(dibayar - total));
        } else {
            $('#info_kembalian').text('');
        }
    }

    $('#formTransaksi').submit(function(e) {
        e.preventDefault();

        if (!$('#id_paket').val()) {
            Swal.fire('Error', 'Pilih paket terlebih dahulu', 'error');
            return;
        }

        const diskon = parseFloat($('#diskon').val()) || 0;
        const total = Math.max(0, hargaPaket - diskon);
        const dibayar = parseFloat($('#dibayar').val()) || 0;

        if (dibayar < total) {
            Swal.fire('Kurang Bayar', 'Nominal yang dibayar kurang dari total tagihan', 'warning');
            return;
        }

        $.post('proses_transaksi.php', {
            id_user_kasir: <?= $_SESSION['id_user'] ?? 1 ?>,
            id_member: $('#id_member').val() || null,
            id_paket: $('#id_paket').val(),
            harga_paket: hargaPaket,
            diskon: diskon,
            metode_pembayaran: $('#metode').val(),
            jumlah_dibayar: dibayar,
            durasi_hari: durasiHari
        }, function(res) {
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Transaksi Berhasil!',
                    html: `
                        <strong>No. Transaksi:</strong> ${res.id_transaksi}<br>
                        <strong>Total Bayar:</strong> ${formatRupiah(res.grand_total)}<br>
                        <strong>Kembalian:</strong> ${formatRupiah(res.kembalian)}<br>
                        ${res.member_aktif ? '<br><span class="text-success">Membership telah diaktifkan!</span>' : ''}
                    `,
                    timer: 6000
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Gagal', res.error || 'Terjadi kesalahan', 'error');
            }
        }, 'json');
    });
</script>