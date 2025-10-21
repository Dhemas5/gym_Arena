<?php
require "../../../setting/session.php";
checkSession("member"); // hanya member boleh masuk
require "../../../setting/koneksi.php";
include '../../../view/master_member/header.php';

// Ambil data member berdasarkan session login
if (isset($_SESSION['id_member'])) {
    $id_member = $_SESSION['id_member'];

    $query = mysqli_query($con, "SELECT * FROM tbl_member WHERE id_member='$id_member'");
    $data = mysqli_fetch_assoc($query);

    if (!$data) {
        die("<div style='padding:20px; color:red;'>❌ Data member tidak ditemukan di database.</div>");
    }
} else {
    die("<div style='padding:20px; color:red;'>⚠️ Session tidak ditemukan. Silakan login ulang.</div>");
}

// --- Path folder foto ---
$folder_foto = "../../../data/member/img/";

// --- Jika kolom foto kosong atau file tidak ada, gunakan default.png ---
$foto_path = $folder_foto . (!empty($data['foto']) ? $data['foto'] : "default.png");
if (!file_exists($foto_path) || empty($data['foto'])) {
    $foto_path = $folder_foto . "default.png";
}
?>

<!-- Tambahan CSS -->
<link rel="stylesheet" href="../../../assets/assets_member/css/custom-program.css">

<!-- Konten -->
<div class="content-wrapper">
    <!-- Header Halaman -->
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><i class="fas fa-user-circle"></i> Profil Member</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../../../data/member/beranda/index.php">Home</a></li>
                        <li class="breadcrumb-item active">Profil</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Isi Konten -->
    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8">
                    <!-- Kartu Profil -->
                    <div class="card profile-card fade-in">
                        <div class="card-body p-0">
                            <div class="row no-gutters">
                                <!-- Sidebar dengan Foto Profil -->
                                <div class="col-md-4 profile-sidebar">
                                    <div class="profile-image-container">
                                        <img src="<?= htmlspecialchars($foto_path); ?>" alt="Foto Profil" class="profile-image rounded-circle">
                                        <div class="profile-badge">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </div>
                                    
                                    <h3 class="profile-name"><?= htmlspecialchars($data['nama']); ?></h3>
                                    <p class="profile-id">
                                        <i class="fas fa-id-badge"></i> <?= htmlspecialchars($data['id_member']); ?>
                                    </p>
                                    
                                    <div class="profile-actions">
                                        <a href="edit_profil.php" class="btn btn-outline-light mb-2">
                                            <i class="fas fa-edit"></i> Edit Profil
                                        </a>
                                        <a href="ubah_password.php" class="btn btn-outline-warning">
                                            <i class="fas fa-key"></i> Ubah Password
                                        </a>
                                    </div>
                                </div>

                                <!-- Konten Detail Informasi -->
                                <div class="col-md-8 profile-content">
                                    <h4 class="text-light mb-4"><i class="fas fa-info-circle text-primary me-2"></i>Informasi Profil</h4>
                                    
                                    <table class="detail-table">
                                        <tr>
                                            <th>Email</th>
                                            <td><?= htmlspecialchars($data['email']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>No. HP</th>
                                            <td><?= htmlspecialchars($data['no_hp']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Alamat</th>
                                            <td><?= htmlspecialchars($data['alamat']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Daftar</th>
                                            <td><?= htmlspecialchars($data['tanggal_daftar']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td><span class="status-badge">Aktif</span></td>
                                        </tr>
                                    </table>

                                    <div class="inspiration-quote">
                                        <p class="mb-0">
                                            <i class="fas fa-quote-left text-primary me-2"></i>
                                            Tetap semangat berlatih dan jaga kesehatan tubuh Anda!
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Card -->
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../../../view/master_member/footer.php'; ?>