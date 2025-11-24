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
    SELECT m.id_member, m.nama, m.is_mahasiswa, m.email, m.no_hp
    FROM tbl_member m
    WHERE m.status_akun = 'aktif'
      AND (m.membership_status != 'aktif' OR m.membership_status IS NULL)
    ORDER BY m.nama
")->fetch_all(MYSQLI_ASSOC);

include '../../../view/master/header.php';
include '../../../view/master/sidebar.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary: #4361ee;
        --success: #06d6a0;
        --warning: #ffd166;
        --danger: #ef476f;
        --dark: #1a1a2e;
        --light: #f8f9fa;
    }

    .transaction-card {
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        border: none;
        margin-bottom: 20px;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .transaction-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    }

    .card-header {
        border-radius: 15px 15px 0 0 !important;
        padding: 15px 20px;
        font-weight: 600;
    }

    .price-display {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--success);
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
    }

    .member-card {
        border-left: 4px solid var(--primary);
        background: linear-gradient(to right, #f8f9ff, white);
        padding: 15px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s;
        margin-bottom: 10px;
    }

    .member-card:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(67, 97, 238, 0.15);
    }

    .member-card.selected {
        background: linear-gradient(to right, #eef2ff, #e0e7ff);
        border-left: 4px solid var(--success);
    }

    .package-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .package-card:hover {
        border-color: var(--primary);
        transform: scale(1.02);
    }

    .package-card.selected {
        border-color: var(--success);
        background-color: rgba(6, 214, 160, 0.05);
    }

    .payment-summary {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .btn-modern {
        border-radius: 10px;
        font-weight: 600;
        padding: 12px 25px;
        transition: all 0.3s;
        border: none;
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success), #05a87e);
        box-shadow: 0 4px 15px rgba(6, 214, 160, 0.3);
    }

    .btn-success:hover {
        transform: translateY(-3px);
        box-shadow: 0 7px 20px rgba(6, 214, 160, 0.4);
    }

    .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        transition: all 0.3s;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
    }

    .qris-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        text-align: center;
    }

    .section-title {
        font-weight: 700;
        margin-bottom: 20px;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: var(--primary);
    }

    .badge-mahasiswa {
        background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
        color: #7c2d12;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
    }

    .badge-umum {
        background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
        color: #1e3a8a;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
    }

    .search-box {
        position: relative;
        margin-bottom: 20px;
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    .search-box input {
        padding-left: 40px;
    }

    .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        border-radius: 15px 15px 0 0;
        background: linear-gradient(135deg, var(--primary), #3a56d4);
        color: white;
    }

    .modal-footer {
        border-radius: 0 0 15px 15px;
    }

    .no-members {
        text-align: center;
        padding: 30px;
        color: #6c757d;
    }

    .no-members i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #dee2e6;
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <h1><i class="fas fa-cash-register"></i> Transaksi Baru</h1>
                <p class="text-muted">Pilih member → Pilih paket → Selesaikan pembayaran</p>
            </div>
            <div class="col-md-6 text-right">
                <div class="bg-light p-3 rounded d-inline-block">
                    <small class="text-muted">Kasir:</small>
                    <strong><?= $_SESSION['nama'] ?? 'Admin' ?></strong>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Kolom Kiri -->
            <div class="col-lg-8">
                <form id="formTransaksi">
                    <input type="hidden" name="id_user_kasir" value="<?= $_SESSION['id_user'] ?? 1 ?>">

                    <!-- Pilih Member -->
                    <div class="card transaction-card">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-user"></i> Pilih Member (Opsional)
                            </div>
                            <div id="selectedMemberInfo" class="d-none">
                                <span id="selectedMemberName" class="badge badge-light"></span>
                                <span id="selectedMemberType" class="badge badge-info ml-1"></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <button type="button" class="btn btn-outline-primary btn-modern" data-toggle="modal" data-target="#memberModal">
                                        <i class="fas fa-search"></i> Cari & Pilih Member
                                    </button>
                                </div>
                                <div>
                                    <button type="button" id="clearMember" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times"></i> Hapus Pilihan
                                    </button>
                                </div>
                            </div>

                            <div id="noMemberSelected" class="text-center p-4 border rounded bg-light">
                                <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Belum ada member yang dipilih<br>
                                    <small>Transaksi akan dianggap sebagai pelanggan umum</small>
                                </p>
                            </div>

                            <div id="memberSelected" class="d-none">
                                <div class="member-card selected">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1" id="displayMemberName">-</h5>
                                            <p class="mb-1 text-muted" id="displayMemberContact">-</p>
                                        </div>
                                        <div>
                                            <span class="badge-mahasiswa d-none" id="displayMemberBadge">Mahasiswa</span>
                                            <span class="badge-umum d-none" id="displayMemberBadgeUmum">Umum</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle"></i> Jika member dipilih, harga akan otomatis disesuaikan dengan status member
                            </small>
                        </div>
                    </div>

                    <!-- Pilih Paket -->
                    <div class="card transaction-card">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-dumbbell"></i> Pilih Paket Membership
                        </div>
                        <div class="card-body">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchPackage" class="form-control" placeholder="Cari paket...">
                            </div>

                            <div class="row" id="packageContainer">
                                <?php foreach ($pakets as $p): ?>
                                    <div class="col-md-6 package-item"
                                        data-name="<?= htmlspecialchars(strtolower($p['nama_paket'])) ?>"
                                        data-price-umum="<?= $p['harga_umum'] ?>"
                                        data-price-mahasiswa="<?= $p['harga_mahasiswa'] ?>">
                                        <div class="package-card"
                                            data-id="<?= $p['id_paket'] ?>"
                                            data-harga-umum="<?= $p['harga_umum'] ?>"
                                            data-harga-mahasiswa="<?= $p['harga_mahasiswa'] ?>"
                                            data-durasi="<?= $p['durasi_hari'] ?>">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h5 class="mb-1"><?= htmlspecialchars($p['nama_paket']) ?></h5>
                                                    <p class="mb-1 text-muted">Durasi: <?= $p['durasi_hari'] ?> hari</p>
                                                </div>
                                                <div class="text-right">
                                                    <div class="price-umum">
                                                        <small class="text-muted">Umum</small>
                                                        <div class="font-weight-bold">Rp <?= number_format($p['harga_umum'], 0, ',', '.') ?></div>
                                                    </div>
                                                    <div class="price-mahasiswa mt-1">
                                                        <small class="text-muted">Mahasiswa</small>
                                                        <div class="font-weight-bold">Rp <?= number_format($p['harga_mahasiswa'], 0, ',', '.') ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-3 p-3 border rounded bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Paket Terpilih:</strong>
                                        <span id="selectedPackageName" class="text-success">-</span>
                                    </div>
                                    <div class="price-display" id="harga_terpilih">Rp 0</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pembayaran -->
                    <div class="card transaction-card">
                        <div class="card-header bg-warning text-dark">
                            <i class="fas fa-coins"></i> Pembayaran
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Diskon (Rp)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" id="diskon" class="form-control" value="0" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label>Metode Pembayaran</label>
                                    <select id="metode" class="form-control">
                                        <option value="TUNAI">Tunai</option>
                                        <option value="QRIS">QRIS DANA (085719630447)</option>
                                        <option value="TRANSFER">Transfer Bank</option>
                                        <option value="DEBIT">Kartu Debit</option>
                                    </select>
                                </div>
                            </div>

                            <hr>

                            <div class="form-group">
                                <label>Jumlah Dibayar</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" id="dibayar" class="form-control" placeholder="Masukkan nominal" required>
                                </div>
                                <small id="info_kembalian" class="text-success font-weight-bold mt-2 d-block"></small>
                            </div>

                            <div class="payment-summary text-center">
                                <h4 class="mb-2">Total Bayar</h4>
                                <h1 class="price-display" id="total_bayar">Rp 0</h1>
                                <p class="mb-0 mt-2">Terima kasih atas transaksinya!</p>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-lg btn-success btn-modern">
                                <i class="fas fa-check-circle"></i> Selesaikan Transaksi
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Kolom Kanan -->
            <div class="col-lg-4">
                <div class="card transaction-card">
                    <div class="card-header bg-dark text-white text-center">
                        <h4><i class="fas fa-qrcode"></i> QRIS DANA</h4>
                    </div>
                    <div class="card-body">
                        <div class="qris-container">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=085719630447" class="img-fluid rounded">
                            <h5 class="mt-3">085719630447</h5>
                            <p class="text-muted">Scan kode QR untuk pembayaran via DANA</p>
                        </div>
                    </div>
                </div>

                <div class="card transaction-card">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-receipt"></i> Ringkasan Transaksi
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Member:</span>
                            <span id="summaryMember">Pelanggan Umum</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Paket:</span>
                            <span id="summaryPackage">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Harga Normal:</span>
                            <span id="summaryHargaNormal">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Status Harga:</span>
                            <span id="summaryStatusHarga" class="badge badge-secondary">Umum</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Diskon:</span>
                            <span id="summaryDiskon">Rp 0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between font-weight-bold">
                            <span>Total:</span>
                            <span id="summaryTotal">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Pilih Member -->
<div class="modal fade" id="memberModal" tabindex="-1" role="dialog" aria-labelledby="memberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="memberModalLabel"><i class="fas fa-users"></i> Pilih Member</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="search-box mb-4">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchMember" class="form-control" placeholder="Cari member berdasarkan nama, email, atau telepon...">
                </div>

                <div id="memberList">
                    <?php if (count($members) > 0): ?>
                        <?php foreach ($members as $m): ?>
                            <div class="member-card member-item"
                                data-id="<?= $m['id_member'] ?>"
                                data-nama="<?= htmlspecialchars($m['nama']) ?>"
                                data-mahasiswa="<?= $m['is_mahasiswa'] ?>"
                                data-email="<?= htmlspecialchars($m['email']) ?>"
                                data-telepon="<?= htmlspecialchars($m['no_hp']) ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1"><?= htmlspecialchars($m['nama']) ?></h5>
                                        <p class="mb-1 text-muted">
                                            <?= htmlspecialchars($m['email']) ?> |
                                            <?= htmlspecialchars($m['no_hp']) ?>
                                        </p>
                                    </div>
                                    <div>
                                        <?php if ($m['is_mahasiswa']): ?>
                                            <span class="badge-mahasiswa">Mahasiswa</span>
                                        <?php else: ?>
                                            <span class="badge-umum">Umum</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-members">
                            <i class="fas fa-user-times"></i>
                            <h5>Tidak ada member tersedia</h5>
                            <p class="text-muted">Semua member sudah memiliki membership aktif</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php include '../../../view/master/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        const formatRupiah = (num) => 'Rp ' + parseInt(num || 0).toLocaleString('id-ID');

        let hargaPaket = 0;
        let durasiHari = 0;
        let isMahasiswa = false;
        let selectedMember = null;
        let selectedPackage = null;

        // Fungsi untuk update ringkasan transaksi
        function updateSummary() {
            $('#summaryMember').text(selectedMember ? selectedMember.nama : 'Pelanggan Umum');
            $('#summaryPackage').text(selectedPackage ? selectedPackage.nama : '-');
            $('#summaryHargaNormal').text(formatRupiah(hargaPaket));
            $('#summaryStatusHarga').text(isMahasiswa ? 'Mahasiswa' : 'Umum');
            $('#summaryStatusHarga').removeClass('badge-secondary badge-info');
            $('#summaryStatusHarga').addClass(isMahasiswa ? 'badge-info' : 'badge-secondary');
            $('#summaryDiskon').text(formatRupiah($('#diskon').val()));
            $('#summaryTotal').text($('#total_bayar').text());
        }

        // Pencarian member di modal
        $('#searchMember').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.member-item').each(function() {
                const nama = $(this).data('nama').toLowerCase();
                const email = $(this).data('email').toLowerCase();
                const telepon = $(this).data('telepon').toLowerCase();

                if (nama.includes(searchTerm) || email.includes(searchTerm) || telepon.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Pilih member dari modal
        $('.member-item').click(function() {
            selectedMember = {
                id: $(this).data('id'),
                nama: $(this).data('nama'),
                mahasiswa: $(this).data('mahasiswa'),
                email: $(this).data('email'),
                telepon: $(this).data('telepon')
            };

            isMahasiswa = selectedMember.mahasiswa == 1;

            // Update tampilan
            $('#noMemberSelected').addClass('d-none');
            $('#memberSelected').removeClass('d-none');
            $('#displayMemberName').text(selectedMember.nama);
            $('#displayMemberContact').text(`${selectedMember.email} | ${selectedMember.telepon}`);

            if (isMahasiswa) {
                $('#displayMemberBadge').removeClass('d-none');
                $('#displayMemberBadgeUmum').addClass('d-none');
            } else {
                $('#displayMemberBadge').addClass('d-none');
                $('#displayMemberBadgeUmum').removeClass('d-none');
            }

            $('#selectedMemberInfo').removeClass('d-none');
            $('#selectedMemberName').text(selectedMember.nama);
            $('#selectedMemberType').text(isMahasiswa ? 'Mahasiswa' : 'Umum');

            // Tutup modal
            $('#memberModal').modal('hide');

            // Update harga jika sudah ada paket terpilih
            if (selectedPackage) {
                updateHargaTerpilih();
            }

            updateSummary();
        });

        // Hapus pilihan member
        $('#clearMember').click(function() {
            selectedMember = null;
            isMahasiswa = false;

            $('#noMemberSelected').removeClass('d-none');
            $('#memberSelected').addClass('d-none');
            $('#selectedMemberInfo').addClass('d-none');

            if (selectedPackage) {
                updateHargaTerpilih();
            }

            updateSummary();
        });

        // Pencarian paket
        $('#searchPackage').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.package-item').each(function() {
                const name = $(this).data('name');
                if (name.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Pilih paket
        $('.package-card').click(function() {
            $('.package-card').removeClass('selected');
            $(this).addClass('selected');

            selectedPackage = {
                id: $(this).data('id'),
                nama: $(this).find('h5').text(),
                hargaUmum: parseFloat($(this).data('harga-umum')) || 0,
                hargaMhs: parseFloat($(this).data('harga-mahasiswa')) || 0,
                durasi: parseInt($(this).data('durasi')) || 30
            };

            durasiHari = selectedPackage.durasi;
            hargaPaket = isMahasiswa ? selectedPackage.hargaMhs : selectedPackage.hargaUmum;

            $('#selectedPackageName').text(selectedPackage.nama);
            updateHargaTerpilih();
            updateSummary();
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
                $('#info_kembalian').html('<i class="fas fa-exchange-alt"></i> Kembalian: ' + formatRupiah(dibayar - total));
            } else {
                $('#info_kembalian').text('');
            }

            updateSummary();
        }

        $('#formTransaksi').submit(function(e) {
            e.preventDefault();

            if (!selectedPackage) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pilih Paket',
                    text: 'Silakan pilih paket membership terlebih dahulu',
                    confirmButtonColor: '#4361ee'
                });
                return;
            }

            const diskon = parseFloat($('#diskon').val()) || 0;
            const total = Math.max(0, hargaPaket - diskon);
            const dibayar = parseFloat($('#dibayar').val()) || 0;

            if (dibayar < total) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kurang Bayar',
                    html: `Nominal yang dibayar <strong>${formatRupiah(dibayar)}</strong> kurang dari total tagihan <strong>${formatRupiah(total)}</strong>`,
                    confirmButtonColor: '#4361ee'
                });
                return;
            }

            // Tampilkan konfirmasi transaksi
            Swal.fire({
                title: 'Konfirmasi Transaksi',
                html: `
                    <div class="text-left">
                        <p><strong>Member:</strong> ${selectedMember ? selectedMember.nama : 'Pelanggan Umum'}</p>
                        <p><strong>Paket:</strong> ${selectedPackage.nama}</p>
                        <p><strong>Total Bayar:</strong> ${formatRupiah(total)}</p>
                        <p><strong>Dibayar:</strong> ${formatRupiah(dibayar)}</p>
                        <p><strong>Kembalian:</strong> ${formatRupiah(dibayar - total)}</p>
                        <p><strong>Metode:</strong> ${$('#metode').find('option:selected').text()}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#06d6a0',
                cancelButtonColor: '#ef476f',
                confirmButtonText: 'Ya, Proses Transaksi',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proses transaksi
                    $.post('proses_transaksi.php', {
                        id_user_kasir: <?= $_SESSION['id_user'] ?? 1 ?>,
                        id_member: selectedMember ? selectedMember.id : null,
                        id_paket: selectedPackage.id,
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
                                    <div class="text-left">
                                        <p><strong>No. Transaksi:</strong> ${res.id_transaksi}</p>
                                        <p><strong>Total Bayar:</strong> ${formatRupiah(res.grand_total)}</p>
                                        <p><strong>Kembalian:</strong> ${formatRupiah(res.kembalian)}</p>
                                        ${res.member_aktif ? '<p class="text-success"><i class="fas fa-check-circle"></i> Membership telah diaktifkan!</p>' : ''}
                                    </div>
                                `,
                                confirmButtonColor: '#06d6a0',
                                timer: 6000
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.error || 'Terjadi kesalahan saat memproses transaksi',
                                confirmButtonColor: '#4361ee'
                            });
                        }
                    }, 'json').fail(function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan Jaringan',
                            text: 'Tidak dapat terhubung ke server',
                            confirmButtonColor: '#4361ee'
                        });
                    });
                }
            });
        });

        // Inisialisasi
        updateSummary();
    });
</script>