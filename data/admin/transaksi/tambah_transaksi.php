<?php
$body_class = 'sidebar-collapse';
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";

// Query paket - HANYA PAKET HARIAN (durasi_hari = 1)
$pakets = $con->query("
    SELECT id_paket, nama_paket, harga_umum, harga_mahasiswa, durasi_hari 
    FROM tbl_paket 
    WHERE durasi_hari = 1  -- HANYA PAKET HARIAN
    ORDER BY nama_paket
")->fetch_all(MYSQLI_ASSOC);

// Query member - HANYA MEMBER YANG STATUS MEMBERSHIP EXPIRED atau BELUM AKTIF
$members = $con->query("
    SELECT 
        m.id_member, 
        m.nama, 
        m.is_mahasiswa, 
        m.email, 
        m.no_hp,
        m.membership_status,
        ms.tgl_berakhir as tgl_berakhir_terakhir,
        p.nama_paket as paket_terakhir,
        p.durasi_hari as durasi_paket_terakhir
    FROM tbl_member m
    LEFT JOIN (
        SELECT id_member, MAX(tgl_berakhir) as max_tgl_berakhir 
        FROM tbl_membership 
        GROUP BY id_member
    ) ms_max ON m.id_member = ms_max.id_member
    LEFT JOIN tbl_membership ms ON m.id_member = ms.id_member AND ms.tgl_berakhir = ms_max.max_tgl_berakhir
    LEFT JOIN tbl_paket p ON ms.id_paket = p.id_paket
    WHERE m.status_akun = 'aktif'
    AND (
        -- Member belum pernah punya membership sama sekali
        ms_max.max_tgl_berakhir IS NULL 
        -- ATAU Membership sudah expired
        OR ms_max.max_tgl_berakhir < NOW()
        -- ATAU Status membership adalah 'expired'
        OR m.membership_status = 'expired'
        -- ATAU Status membership bukan 'aktif'
        OR m.membership_status != 'aktif'
    )
    GROUP BY m.id_member
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

    .nota-container {
        display: none;
        font-family: 'Courier New', monospace;
        max-width: 300px;
        margin: 0 auto;
        padding: 15px;
        border: 1px dashed #ccc;
        background: white;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .nota-container,
        .nota-container * {
            visibility: visible;
        }

        .nota-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
            box-shadow: none;
        }

        .no-print {
            display: none !important;
        }
    }

    .disabled-member {
        opacity: 0.6;
        cursor: not-allowed !important;
    }

    .disabled-member:hover {
        transform: none !important;
        box-shadow: none !important;
    }

    .disabled-badge {
        background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
        color: #666;
    }

    .badge-harian {
        background: linear-gradient(135deg, #06d6a0, #05a87e);
        color: white;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 0.7rem;
    }

    .no-packages {
        text-align: center;
        padding: 40px 20px;
    }

    .no-packages i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    .payment-method-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.3s;
        background: white;
    }

    .payment-method-card:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .payment-method-card.selected {
        border-color: var(--success);
        background-color: rgba(6, 214, 160, 0.05);
    }

    .payment-icon {
        font-size: 2rem;
        margin-bottom: 10px;
        color: var(--primary);
    }

    .modal-header .close span {
        color: white !important;
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <h1><i class="fas fa-cash-register"></i> Transaksi Paket Harian</h1>
                <p class="text-muted">Pilih member yang membership expired → Pilih paket harian → Selesaikan pembayaran</p>
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
                    <input type="hidden" name="metode_pembayaran" id="hidden_metode_pembayaran" value="TUNAI">
                    <input type="hidden" name="durasi_hari" id="hidden_durasi_hari" value="1">

                    <!-- Pilih Member -->
                    <div class="card transaction-card">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-user"></i> Pilih Member (Yang Membership Expired)
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
                                        <i class="fas fa-search"></i> Cari Member Expired
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
                                <i class="fas fa-info-circle"></i> Hanya menampilkan member yang membershipnya sudah expired
                            </small>
                        </div>
                    </div>

                    <!-- Pilih Paket -->
                    <div class="card transaction-card">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-dumbbell"></i> Pilih Paket Harian
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Hanya paket harian (durasi 1 hari) yang tersedia untuk transaksi
                            </div>

                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchPackage" class="form-control" placeholder="Cari paket harian...">
                            </div>

                            <div class="row" id="packageContainer">
                                <?php if (count($pakets) > 0): ?>
                                    <?php foreach ($pakets as $p): ?>
                                        <div class="col-md-6 package-item"
                                            data-name="<?= htmlspecialchars(strtolower($p['nama_paket'])) ?>"
                                            data-price-umum="<?= $p['harga_umum'] ?>"
                                            data-price-mahasiswa="<?= $p['harga_mahasiswa'] ?>">
                                            <div class="package-card"
                                                data-id="<?= $p['id_paket'] ?>"
                                                data-nama="<?= htmlspecialchars($p['nama_paket']) ?>"
                                                data-harga-umum="<?= $p['harga_umum'] ?>"
                                                data-harga-mahasiswa="<?= $p['harga_mahasiswa'] ?>"
                                                data-durasi="<?= $p['durasi_hari'] ?>">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h5 class="mb-1"><?= htmlspecialchars($p['nama_paket']) ?></h5>
                                                        <p class="mb-1 text-muted">
                                                            <span class="badge-harian">Harian</span>
                                                            Durasi: <?= $p['durasi_hari'] ?> hari
                                                        </p>
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
                                <?php else: ?>
                                    <div class="col-12 no-packages">
                                        <i class="fas fa-box-open"></i>
                                        <h5>Tidak ada paket harian tersedia</h5>
                                        <p class="text-muted">Silakan tambahkan paket harian terlebih dahulu di menu Paket</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-3 p-3 border rounded bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Paket Harian Terpilih:</strong>
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
                                        <input type="number" id="diskon" name="diskon" class="form-control" value="0" min="0" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Metode Pembayaran -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <label class="font-weight-bold">Pilih Metode Pembayaran</label>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="payment-method-card text-center" data-method="TUNAI">
                                                <div class="payment-icon">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </div>
                                                <h5>Tunai</h5>
                                                <p class="text-muted mb-0">Bayar dengan uang tunai</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="payment-method-card text-center" data-method="DEBIT">
                                                <div class="payment-icon">
                                                    <i class="fas fa-credit-card"></i>
                                                </div>
                                                <h5>Kartu Debit</h5>
                                                <p class="text-muted mb-0">Bayar dengan kartu debit</p>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="selectedPaymentMethod" value="TUNAI">
                                </div>
                            </div>

                            <hr>

                            <div class="form-group">
                                <label>Jumlah Dibayar</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" id="dibayar" name="jumlah_dibayar" class="form-control" placeholder="Masukkan nominal" required>
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
                            <button type="submit" class="btn btn-lg btn-success btn-modern" id="btnSubmit">
                                <i class="fas fa-check-circle"></i> Selesaikan Transaksi
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Kolom Kanan -->
            <div class="col-lg-4">
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
                        <div class="d-flex justify-content-between mb-2">
                            <span>Metode Pembayaran:</span>
                            <span id="summaryMetode" class="badge badge-primary">Tunai</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between font-weight-bold">
                            <span>Total:</span>
                            <span id="summaryTotal">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Informasi Transaksi -->
                <div class="card transaction-card">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-info-circle"></i> Informasi Transaksi
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb"></i> <strong>Catatan Penting:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Hanya paket harian yang tersedia</li>
                                <li>Durasi paket: 1 hari (hingga 23:59 hari ini)</li>
                                <li>Hanya menerima pembayaran tunai atau kartu debit</li>
                                <li>Nota akan dicetak otomatis setelah transaksi berhasil</li>
                            </ul>
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
                <h5 class="modal-title" id="memberModalLabel"><i class="fas fa-users"></i> Pilih Member (Expired/Belum Aktif)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-white">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Hanya menampilkan member yang membershipnya sudah expired atau belum pernah aktif. Member dengan membership aktif tidak ditampilkan.
                </div>

                <div class="search-box mb-4">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchMember" class="form-control" placeholder="Cari member berdasarkan nama...">
                </div>

                <div id="memberList">
                    <?php if (count($members) > 0): ?>
                        <?php foreach ($members as $m):
                            $status_membership = $m['membership_status'] ?? 'belum_aktif';
                            $expired_date = $m['tgl_berakhir_terakhir'] ?? null;
                            $is_expired = false;
                            $nama_paket = $m['paket_terakhir'] ?? 'Belum ada';
                            $durasi_paket = $m['durasi_paket_terakhir'] ?? 0;

                            // Cek apakah sudah expired
                            if ($expired_date) {
                                $is_expired = strtotime($expired_date) < time();
                            }

                            // Tentukan status untuk ditampilkan
                            if ($status_membership == 'aktif' && !$is_expired) {
                                // Tidak ditampilkan - skip
                                continue;
                            } elseif ($status_membership == 'aktif' && $is_expired) {
                                $status_badge = 'warning';
                                $status_text = 'Expired';
                                $status_icon = 'exclamation-triangle';
                            } elseif ($status_membership == 'expired') {
                                $status_badge = 'danger';
                                $status_text = 'Expired';
                                $status_icon = 'exclamation-triangle';
                            } else {
                                $status_badge = 'secondary';
                                $status_text = 'Belum Aktif';
                                $status_icon = 'clock';
                            }
                        ?>
                            <div class="member-card member-item"
                                data-id="<?= $m['id_member'] ?>"
                                data-nama="<?= htmlspecialchars($m['nama']) ?>"
                                data-mahasiswa="<?= $m['is_mahasiswa'] ?>"
                                data-email="<?= htmlspecialchars($m['email']) ?>"
                                data-telepon="<?= htmlspecialchars($m['no_hp']) ?>"
                                data-paket-terakhir="<?= htmlspecialchars($nama_paket) ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1"><?= htmlspecialchars($m['nama']) ?></h5>
                                        <p class="mb-1 text-muted">
                                            <?= htmlspecialchars($m['email']) ?> | <?= htmlspecialchars($m['no_hp']) ?>
                                        </p>
                                        <div class="d-flex align-items-center mt-1">
                                            <span class="badge badge-<?= $status_badge ?> mr-2">
                                                <i class="fas fa-<?= $status_icon ?>"></i> <?= $status_text ?>
                                                <?php if ($expired_date): ?>
                                                    (<?= date('d/m/Y', strtotime($expired_date)) ?>)
                                                <?php endif; ?>
                                            </span>

                                            <?php if ($m['is_mahasiswa']): ?>
                                                <span class="badge-mahasiswa mr-2">Mahasiswa</span>
                                            <?php else: ?>
                                                <span class="badge-umum mr-2">Umum</span>
                                            <?php endif; ?>

                                            <?php if ($nama_paket != 'Belum ada'): ?>
                                                <span class="badge badge-light">
                                                    <i class="fas fa-history"></i> <?= htmlspecialchars($nama_paket) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (count($members) == 0): ?>
                            <div class="no-members">
                                <i class="fas fa-user-check"></i>
                                <h5>Semua member sudah aktif</h5>
                                <p class="text-muted">Tidak ada member dengan status expired atau belum aktif</p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-members">
                            <i class="fas fa-user-times"></i>
                            <h5>Tidak ada member tersedia</h5>
                            <p class="text-muted">Tidak ada member dengan status akun aktif</p>
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
        let selectedMemberPaketTerakhir = '';

        // Inisialisasi nilai diskon ke 0
        $('#diskon').val(0);

        // Pilih metode pembayaran
        $('.payment-method-card').click(function() {
            $('.payment-method-card').removeClass('selected');
            $(this).addClass('selected');

            const method = $(this).data('method');
            $('#selectedPaymentMethod').val(method);
            $('#hidden_metode_pembayaran').val(method);

            // Update ringkasan
            $('#summaryMetode').text(method === 'TUNAI' ? 'Tunai' : 'Kartu Debit');

            console.log('Metode pembayaran dipilih:', method);
        });

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
        $(document).on('click', '.member-item', function() {
            selectedMember = {
                id: $(this).data('id'),
                nama: $(this).data('nama'),
                mahasiswa: $(this).data('mahasiswa'),
                email: $(this).data('email'),
                telepon: $(this).data('telepon')
            };

            selectedMemberPaketTerakhir = $(this).data('paket-terakhir') || '';

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
            selectedMemberPaketTerakhir = '';
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
        $(document).on('click', '.package-card', function() {
            // Validasi: paket harus harian (durasi 1)
            const packageDurasi = $(this).data('durasi');
            if (packageDurasi != 1) {
                Swal.fire({
                    icon: 'error',
                    title: 'Paket Tidak Valid',
                    text: 'Hanya paket harian yang diperbolehkan untuk transaksi ini',
                    confirmButtonColor: '#4361ee'
                });
                return;
            }

            $('.package-card').removeClass('selected');
            $(this).addClass('selected');

            selectedPackage = {
                id: $(this).data('id'),
                nama: $(this).data('nama'),
                hargaUmum: parseFloat($(this).data('harga-umum')) || 0,
                hargaMhs: parseFloat($(this).data('harga-mahasiswa')) || 0,
                durasi: parseInt($(this).data('durasi')) || 1
            };

            durasiHari = selectedPackage.durasi;
            $('#hidden_durasi_hari').val(durasiHari);
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

        // Fungsi untuk cetak nota langsung
        function cetakNotaLangsung(data) {
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
                        .btn-print {
                            display: block;
                            width: 100%;
                            background: #4361ee;
                            color: white;
                            border: none;
                            padding: 10px;
                            border-radius: 5px;
                            font-size: 14px;
                            font-weight: bold;
                            cursor: pointer;
                            margin-top: 15px;
                            text-align: center;
                            text-decoration: none;
                        }
                        .btn-print:hover {
                            background: #3a56d4;
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
                            <span class="info-value">${data.tanggal_transaksi}</span>
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
                            <span class="info-label">Paket:</span>
                            <span class="info-value">${data.nama_paket}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Durasi:</span>
                            <span class="info-value">${data.durasi_hari} hari (Harian)</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Berlaku sampai:</span>
                            <span class="info-value">${data.tgl_berakhir ? data.tgl_berakhir.split(' ')[0] : 'Hari ini'} 23:59</span>
                        </div>
                    </div>
                    
                    <hr class="divider">
                    
                    <div class="total-section">
                        <div class="info-row">
                            <span>Harga:</span>
                            <span>Rp ${parseInt(data.harga_paket).toLocaleString('id-ID')}</span>
                        </div>
                        <div class="info-row">
                            <span>Diskon:</span>
                            <span>- Rp ${parseInt(data.diskon).toLocaleString('id-ID')}</span>
                        </div>
                        <div class="info-row grand-total">
                            <span>Total:</span>
                            <span>Rp ${parseInt(data.grand_total).toLocaleString('id-ID')}</span>
                        </div>
                        <div class="info-row">
                            <span>Dibayar:</span>
                            <span>Rp ${parseInt(data.jumlah_dibayar).toLocaleString('id-ID')}</span>
                        </div>
                        <div class="info-row">
                            <span>Kembalian:</span>
                            <span>Rp ${parseInt(data.kembalian).toLocaleString('id-ID')}</span>
                        </div>
                        <div class="info-row">
                            <span>Metode:</span>
                            <span>${data.metode_pembayaran}</span>
                        </div>
                    </div>
                    
                    <div class="footer">
                        <p>Terima kasih atas kunjungan Anda</p>
                        <p>*** Semoga Sehat Selalu ***</p>
                    </div>
                    
                    <button class="btn-print no-print" onclick="window.print()">
                        <i class="fas fa-print"></i> CETAK NOTA
                    </button>
                    <button class="btn-print no-print" style="background: #6c757d; margin-top: 10px;" onclick="window.close()">
                        <i class="fas fa-times"></i> TUTUP
                    </button>
                    
                    <script>
                        // Auto print setelah 500ms
                        setTimeout(() => {
                            window.print();
                        }, 500);
                        
                        // Auto close setelah print (jika browser mendukung)
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
        }

        // Form submit handler menggunakan FormData
        $('#formTransaksi').submit(function(e) {
            e.preventDefault();

            if (!selectedPackage) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pilih Paket',
                    text: 'Silakan pilih paket harian terlebih dahulu',
                    confirmButtonColor: '#4361ee'
                });
                return;
            }

            // Pastikan metode pembayaran terpilih
            const metodePembayaran = $('#selectedPaymentMethod').val();
            if (!metodePembayaran) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pilih Metode Pembayaran',
                    text: 'Silakan pilih metode pembayaran (Tunai atau Kartu Debit)',
                    confirmButtonColor: '#4361ee'
                });
                return;
            }

            // Update hidden field metode pembayaran
            $('#hidden_metode_pembayaran').val(metodePembayaran);
            $('#hidden_durasi_hari').val(durasiHari);

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

            // Validasi durasi paket harus 1 (harian)
            if (durasiHari !== 1) {
                Swal.fire({
                    icon: 'error',
                    title: 'Paket Tidak Valid',
                    text: 'Hanya paket harian (durasi 1 hari) yang diperbolehkan untuk transaksi ini',
                    confirmButtonColor: '#4361ee'
                });
                return;
            }

            // Tampilkan konfirmasi transaksi
            Swal.fire({
                title: 'Konfirmasi Transaksi Paket Harian',
                html: `
                    <div class="text-left">
                        <p><strong>Member:</strong> ${selectedMember ? selectedMember.nama : 'Pelanggan Umum'}</p>
                        <p><strong>Paket:</strong> ${selectedPackage.nama}</p>
                        <p><strong>Durasi:</strong> ${durasiHari} hari (Harian)</p>
                        <p><strong>Berlaku sampai:</strong> Hari ini 23:59</p>
                        <p><strong>Metode Pembayaran:</strong> ${metodePembayaran === 'TUNAI' ? 'Tunai' : 'Kartu Debit'}</p>
                        <p><strong>Total Bayar:</strong> ${formatRupiah(total)}</p>
                        <p><strong>Dibayar:</strong> ${formatRupiah(dibayar)}</p>
                        <p><strong>Kembalian:</strong> ${formatRupiah(dibayar - total)}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#06d6a0',
                cancelButtonColor: '#ef476f',
                confirmButtonText: 'Ya, Proses Transaksi',
                cancelButtonText: 'Batal',
                width: '500px'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan loading
                    Swal.fire({
                        title: 'Memproses Transaksi...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Buat FormData dari form
                    const formData = new FormData(document.getElementById('formTransaksi'));

                    // Tambahkan data yang tidak ada di form
                    formData.append('id_paket', selectedPackage.id);
                    formData.append('harga_paket', hargaPaket);
                    formData.append('id_member', selectedMember ? selectedMember.id : '');

                    console.log('Data yang dikirim ke server:');
                    for (let [key, value] of formData.entries()) {
                        console.log(key + ': ' + value);
                    }

                    // Proses transaksi menggunakan FormData
                    $.ajax({
                        url: 'proses_transaksi.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                Swal.close();

                                // Tampilkan pilihan setelah transaksi berhasil
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Transaksi Berhasil!',
                                    html: `
                                        <div class="text-left">
                                            <p><strong>No. Transaksi:</strong> ${res.id_transaksi}</p>
                                            <p><strong>Metode Pembayaran:</strong> ${res.metode_pembayaran}</p>
                                            <p><strong>Total Bayar:</strong> ${formatRupiah(res.grand_total)}</p>
                                            <p><strong>Kembalian:</strong> ${formatRupiah(res.kembalian)}</p>
                                            <p><strong>Berlaku sampai:</strong> Hari ini 23:59</p>
                                            ${res.member_aktif ? '<p class="text-success"><i class="fas fa-check-circle"></i> Membership harian telah diaktifkan!</p>' : ''}
                                        </div>
                                    `,
                                    showCancelButton: true,
                                    confirmButtonText: '<i class="fas fa-print"></i> Cetak Nota',
                                    cancelButtonText: 'Tutup & Reset Form',
                                    confirmButtonColor: '#06d6a0',
                                    cancelButtonColor: '#ef476f',
                                    allowOutsideClick: false,
                                    width: '500px'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Cetak nota langsung
                                        cetakNotaLangsung(res);
                                    }
                                    // Reset form setelah beberapa detik
                                    setTimeout(() => {
                                        location.reload();
                                    }, 1000);
                                });

                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res.error || 'Terjadi kesalahan saat memproses transaksi',
                                    confirmButtonColor: '#4361ee'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Kesalahan Jaringan',
                                text: 'Tidak dapat terhubung ke server: ' + error,
                                confirmButtonColor: '#4361ee'
                            });
                        }
                    });
                }
            });
        });

        // Inisialisasi
        updateSummary();

        // Pilih metode tunai secara default
        $('.payment-method-card[data-method="TUNAI"]').click();

    });
</script>