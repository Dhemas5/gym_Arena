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
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
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
                <div class="col-md-8">
                    <!-- Kartu Profil -->
                    <div class="card card-outline card-primary shadow-lg" style="border-radius: 15px;">
                        <div class="card-body p-4">
                            <div class="row">
                                <!-- Foto Profil -->
                                <div class="col-md-4 text-center mb-3">
                                    <img src="<?= htmlspecialchars($foto_path); ?>" alt="Foto Profil"
                                        class="img-fluid rounded-circle mb-3 shadow border"
                                        style="width: 150px; height: 150px; object-fit: cover; background-color: #f8f9fa;">

                                    <h5 class="text-primary font-weight-bold">
                                        <?= htmlspecialchars($data['nama']); ?>
                                    </h5>
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-id-badge"></i> <?= htmlspecialchars($data['id_member']); ?>
                                    </p>

                                    <a href="edit_profil.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i> Edit Profil
                                    </a>
                                    <a href="ubah_password.php" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-key"></i> Ubah Password
                                    </a>
                                </div>

                                <!-- Detail Informasi -->
                                <div class="col-md-8">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="35%">Email</th>
                                            <td>: <?= htmlspecialchars($data['email']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>No. HP</th>
                                            <td>: <?= htmlspecialchars($data['no_hp']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Alamat</th>
                                            <td>: <?= htmlspecialchars($data['alamat']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Daftar</th>
                                            <td>: <?= htmlspecialchars($data['tanggal_daftar']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>: <span class="badge badge-success">Aktif</span></td>
                                        </tr>
                                    </table>

                                    <hr>
                                    <p class="text-muted">
                                        <i class="fas fa-quote-left text-primary"></i>
                                        Tetap semangat berlatih dan jaga kesehatan tubuh Anda!
                                    </p>
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

<!-- Tambahan CSS -->
<style>
    .card {
        background: #333;
        border: none;
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 10px 20px rgba(0, 64, 128, 0.15);
    }

    th {
        color: #f8f9fa;
        font-weight: 600;
    }

    td {
        color: #f8f9fa;
    }

    .badge-success {
        background: linear-gradient(90deg, #00b894, #1dd1a1);
    }

    .btn-outline-primary:hover {
        background: #00509e;
        color: white;
        border-color: #00509e;
    }

    .btn-outline-danger:hover {
        background: #dc3545;
        color: white;
    }
</style>