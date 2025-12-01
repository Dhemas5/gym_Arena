<?php
session_start();
require "../../../setting/koneksi.php";

if (!isset($_SESSION['login']) || $_SESSION['user_type'] !== 'member') {
    header("Location: ../login/login.php");
    exit;
}

$nama_member = $_SESSION['nama'];
$id_member = $_SESSION['id_member'];

// Query untuk mengambil data member
$query = "SELECT * FROM tbl_member WHERE id_member = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $id_member);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

if (!$member) {
    die("Data member tidak ditemukan");
}

// Format tanggal bergabung
$tanggal_daftar = date('d F Y', strtotime($member['tanggal_daftar']));

// Cek status membership
$membership_aktif = false;
$paket_aktif = null;
$berakhir = null;

$stmt_membership = $con->prepare("
    SELECT p.nama_paket, m.tgl_berakhir 
    FROM tbl_membership m 
    JOIN tbl_paket p ON m.id_paket = p.id_paket 
    WHERE m.id_member = ? AND m.tgl_berakhir >= NOW() 
    ORDER BY m.tgl_berakhir DESC LIMIT 1
");
$stmt_membership->bind_param("i", $id_member);
$stmt_membership->execute();
$res_membership = $stmt_membership->get_result();
if ($res_membership->num_rows > 0) {
    $row = $res_membership->fetch_assoc();
    $membership_aktif = true;
    $paket_aktif = $row['nama_paket'];
    $berakhir = date('d M Y', strtotime($row['tgl_berakhir']));
}
$stmt_membership->close();

// Tentukan status member untuk badge
$status_member = $membership_aktif ? "Aktif" : "Tidak Aktif";
$status_class = $membership_aktif ? "aktif" : "tidak-aktif";

// Proses update profil jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $nama = $con->real_escape_string($_POST['nama']);
    $no_hp = $con->real_escape_string($_POST['no_hp']);
    $alamat = $con->real_escape_string($_POST['alamat']);
    
    // Update data member
    $update_query = "UPDATE tbl_member SET nama = ?, no_hp = ?, alamat = ? WHERE id_member = ?";
    $stmt_update = $con->prepare($update_query);
    $stmt_update->bind_param("sssi", $nama, $no_hp, $alamat, $id_member);
    
    if ($stmt_update->execute()) {
        $_SESSION['nama'] = $nama;
        $success_message = "Profil berhasil diperbarui!";
        // Refresh data
        header("Location: profile.php");
        exit;
    } else {
        $error_message = "Gagal memperbarui profil. Silakan coba lagi.";
    }
    $stmt_update->close();
}

// Proses update password jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    // Verifikasi password lama
    if (password_verify($password_lama, $member['password'])) {
        if ($password_baru === $konfirmasi_password) {
            // Update password
            $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
            $update_password_query = "UPDATE tbl_member SET password = ? WHERE id_member = ?";
            $stmt_password = $con->prepare($update_password_query);
            $stmt_password->bind_param("si", $password_hash, $id_member);
            
            if ($stmt_password->execute()) {
                $success_password = "Password berhasil diubah!";
            } else {
                $error_password = "Gagal mengubah password. Silakan coba lagi.";
            }
            $stmt_password->close();
        } else {
            $error_password = "Password baru dan konfirmasi password tidak cocok.";
        }
    } else {
        $error_password = "Password lama tidak sesuai.";
    }
}

// Proses upload foto profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_foto'])) {
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto_profil'];
        
        // Validasi file
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $error_foto = "Format file tidak didukung. Gunakan JPG, PNG, atau GIF.";
        } elseif ($file['size'] > $max_size) {
            $error_foto = "Ukuran file terlalu besar. Maksimal 2MB.";
        } else {
            // Generate nama file unik
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'member_' . $id_member . '_' . time() . '.' . $ext;
            $upload_path = '../../uploads/member/' . $new_filename;
            
            // Buat folder jika belum ada
            if (!is_dir('../../uploads/member/')) {
                mkdir('../../uploads/member/', 0777, true);
            }
            
            // Hapus foto lama jika ada
            if (!empty($member['foto']) && $member['foto'] !== 'default.jpg') {
                $old_file_path = '../../uploads/member/' . $member['foto'];
                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            }
            
            // Upload file baru
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Update database
                $update_foto_query = "UPDATE tbl_member SET foto = ? WHERE id_member = ?";
                $stmt_foto = $con->prepare($update_foto_query);
                $stmt_foto->bind_param("si", $new_filename, $id_member);
                
                if ($stmt_foto->execute()) {
                    $success_foto = "Foto profil berhasil diupload!";
                    // Refresh data
                    header("Location: profile.php");
                    exit;
                } else {
                    $error_foto = "Gagal menyimpan foto ke database.";
                }
                $stmt_foto->close();
            } else {
                $error_foto = "Gagal mengupload file.";
            }
        }
    } else {
        $error_foto = "Silakan pilih file foto.";
    }
}

// Proses hapus foto profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hapus_foto'])) {
    if (!empty($member['foto']) && $member['foto'] !== 'default.jpg') {
        $file_path = '../../uploads/member/' . $member['foto'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Update database ke default
        $update_foto_query = "UPDATE tbl_member SET foto = 'default.jpg' WHERE id_member = ?";
        $stmt_foto = $con->prepare($update_foto_query);
        $stmt_foto->bind_param("i", $id_member);
        
        if ($stmt_foto->execute()) {
            $success_foto = "Foto profil berhasil dihapus!";
            // Refresh data
            header("Location: profile.php");
            exit;
        }
        $stmt_foto->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Member - Arena FIT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/stylemember.css">
    <link rel="stylesheet" href="assets/css/styleprofilemember.css">
    <style>
        .ktm-preview {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .ktm-preview:hover {
            transform: scale(1.02);
        }
        .modal-body.ktm-modal {
            padding: 20px;
            text-align: center;
        }
        .btn-view-ktm {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .btn-view-ktm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .btn-view-ktm i {
            font-size: 1.1rem;
        }
        
        /* Fullscreen Image Modal */
        .fullscreen-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            padding-top: 50px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.95);
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .fullscreen-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 85vh;
            object-fit: contain;
            animation: zoomIn 0.3s ease;
        }
        
        @keyframes zoomIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .close-fullscreen {
            position: absolute;
            top: 20px;
            right: 40px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            z-index: 10000;
        }
        
        .close-fullscreen:hover,
        .close-fullscreen:focus {
            color: #bbb;
        }
        
        .fullscreen-caption {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            text-align: center;
            color: #ccc;
            padding: 10px 0;
            font-size: 16px;
        }
        
        .zoom-hint {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: #fff;
            background: rgba(0,0,0,0.7);
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="indexmemberr.php">
                <span class="brand-box">AF</span>
                <div><span style="font-size: 1.2rem;">Arena FIT</span></div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="indexmemberr.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="transaksi.php">Transaksi</a></li>
                    <li class="nav-item"><a class="nav-link active" href="profile.php">Profile</a></li>
                </ul>
                <div class="member-info ms-3">
                    <div class="member-avatar">
                        <?php if (!empty($member['foto']) && $member['foto'] !== 'default.jpg'): ?>
                            <img src="../../uploads/member/<?= $member['foto'] ?>" alt="Foto Profil" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <?= strtoupper(substr($nama_member, 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <span class="welcome-text">
                        <span class="member-name"><?= htmlspecialchars($nama_member) ?></span>
                    </span>
                    <a href="../login/logout.php" class="btn-logout">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Profil Section -->
    <section class="profile-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 mb-0">Profil Member</h1>
                    </div>
                    
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $success_message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $error_message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success_foto)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $success_foto ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_foto)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $error_foto ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar" data-bs-toggle="modal" data-bs-target="#uploadFotoModal">
                                <?php if (!empty($member['foto']) && $member['foto'] !== 'default.jpg'): ?>
                                    <img src="../../uploads/member/<?= $member['foto'] ?>" alt="Foto Profil">
                                <?php else: ?>
                                    <?= strtoupper(substr($member['nama'], 0, 1)) ?>
                                <?php endif; ?>
                                <div class="avatar-overlay">
                                    <i class="fas fa-camera"></i>
                                </div>
                            </div>
                            <h2 class="profile-name"><?= htmlspecialchars($member['nama']) ?></h2>
                            <p class="profile-email"><?= htmlspecialchars($member['email']) ?></p>
                            <small class="text-muted">Klik foto untuk mengubah</small>
                        </div>
                        
                        <div class="profile-info">
                            <div class="info-item">
                                <span class="info-label">Nama Lengkap</span>
                                <span class="info-value"><?= htmlspecialchars($member['nama']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email</span>
                                <span class="info-value"><?= htmlspecialchars($member['email']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Tipe Member</span>
                                <span class="info-value">
                                    <span class="status-badge <?= $member['is_mahasiswa'] ? 'status-info' : 'status-primary' ?>">
                                        <?= $member['is_mahasiswa'] ? '🎓 Pelajar/Mahasiswa' : '👤 Umum' ?>
                                    </span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Nomor Telepon</span>
                                <span class="info-value"><?= $member['no_hp'] ? htmlspecialchars($member['no_hp']) : '-' ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Alamat</span>
                                <span class="info-value"><?= $member['alamat'] ? htmlspecialchars($member['alamat']) : '-' ?></span>
                            </div>
                            
                            <!-- Tampilkan KTM jika mahasiswa -->
                            <?php if ($member['is_mahasiswa'] && !empty($member['ktm_file'])): ?>
                            <div class="info-item">
                                <span class="info-label">Kartu Mahasiswa</span>
                                <span class="info-value">
                                    <button type="button" class="btn-view-ktm" data-bs-toggle="modal" data-bs-target="#ktmModal">
                                        <i class="fas fa-id-card"></i> Lihat KTM
                                    </button>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-item">
                                <span class="info-label">Tanggal Bergabung</span>
                                <span class="info-value"><?= $tanggal_daftar ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status Member</span>
                                <span class="info-value">
                                    <span class="status-badge status-<?= $status_class ?>"><?= $status_member ?></span>
                                </span>
                            </div>
                            <?php if ($membership_aktif): ?>
                            <div class="info-item">
                                <span class="info-label">Paket Aktif</span>
                                <span class="info-value text-success"><?= htmlspecialchars($paket_aktif) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Berlaku Hingga</span>
                                <span class="info-value text-warning"><?= $berakhir ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="button" class="btn btn-edit" data-bs-toggle="modal" data-bs-target="#editProfilModal">
                            <i class="fas fa-edit me-2"></i> Edit Profil
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Lihat KTM -->
    <?php if ($member['is_mahasiswa'] && !empty($member['ktm_file'])): ?>
    <div class="modal fade" id="ktmModal" tabindex="-1" aria-labelledby="ktmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ktmModalLabel">
                        <i class="fas fa-id-card me-2"></i>Kartu Tanda Mahasiswa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body ktm-modal">
                    <img src="../../uploads/ktm/<?= $member['ktm_file'] ?>" alt="KTM" class="ktm-preview" id="ktmImage" onclick="openFullscreen()">
                    <div class="mt-3">
                        <small class="text-muted d-block mb-2">
                            <i class="fas fa-info-circle me-1"></i>Klik gambar untuk melihat ukuran penuh
                        </small>
                        <a href="../../uploads/ktm/<?= $member['ktm_file'] ?>" download class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Download KTM
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Fullscreen Image Viewer -->
    <div id="fullscreenModal" class="fullscreen-modal" onclick="closeFullscreen()">
        <span class="close-fullscreen" onclick="closeFullscreen()">&times;</span>
        <img class="fullscreen-content" id="fullscreenImage">
        <div class="fullscreen-caption" id="caption">Kartu Tanda Mahasiswa</div>
        <div class="zoom-hint">
            <i class="fas fa-search-plus me-2"></i>Klik untuk menutup
        </div>
    </div>

    <!-- Modal Edit Profil -->
    <div class="modal fade" id="editProfilModal" tabindex="-1" aria-labelledby="editProfilModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfilModalLabel">Edit Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-4" id="profilTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="data-diri-tab" data-bs-toggle="tab" data-bs-target="#data-diri" type="button" role="tab" aria-controls="data-diri" aria-selected="true">Data Diri</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ubah-password-tab" data-bs-toggle="tab" data-bs-target="#ubah-password" type="button" role="tab" aria-controls="ubah-password" aria-selected="false">Ubah Password</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="profilTabsContent">
                        <!-- Tab Data Diri -->
                        <div class="tab-pane fade show active" id="data-diri" role="tabpanel" aria-labelledby="data-diri-tab">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($member['nama']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($member['email']) ?>" readonly>
                                    <div class="form-text text-muted">Email tidak dapat diubah.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="no_hp" class="form-label">Nomor Telepon</label>
                                    <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?= $member['no_hp'] ? htmlspecialchars($member['no_hp']) : '' ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= $member['alamat'] ? htmlspecialchars($member['alamat']) : '' ?></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="update_profil" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Tab Ubah Password -->
                        <div class="tab-pane fade" id="ubah-password" role="tabpanel" aria-labelledby="ubah-password-tab">
                            <?php if (isset($success_password)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?= $success_password ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($error_password)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= $error_password ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="password_lama" class="form-label">Password Lama</label>
                                    <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password_baru" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="password_baru" name="password_baru" required>
                                </div>
                                <div class="mb-3">
                                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="update_password" class="btn btn-primary">Ubah Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Upload Foto -->
    <div class="modal fade" id="uploadFotoModal" tabindex="-1" aria-labelledby="uploadFotoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadFotoModalLabel">Ubah Foto Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <?php if (!empty($member['foto']) && $member['foto'] !== 'default.jpg'): ?>
                        <img src="../../uploads/member/<?= $member['foto'] ?>" alt="Foto Profil Saat Ini" class="foto-preview">
                    <?php else: ?>
                        <div class="foto-preview bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="font-size: 3rem;">
                            <?= strtoupper(substr($member['nama'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="foto_profil" class="form-label">Pilih Foto Baru</label>
                            <input type="file" class="form-control" id="foto_profil" name="foto_profil" accept="image/*" required>
                            <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB.</div>
                        </div>
                        
                        <div class="d-flex justify-content-center gap-2">
                            <?php if (!empty($member['foto']) && $member['foto'] !== 'default.jpg'): ?>
                                <button type="submit" name="hapus_foto" class="btn btn-hapus-foto">
                                    <i class="fas fa-trash me-1"></i> Hapus Foto
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="upload_foto" class="btn btn-primary">Upload Foto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fullscreen Image Viewer Functions
        function openFullscreen() {
            var modal = document.getElementById("fullscreenModal");
            var img = document.getElementById("ktmImage");
            var modalImg = document.getElementById("fullscreenImage");
            
            modal.style.display = "block";
            modalImg.src = img.src;
            
            // Prevent body scroll when modal is open
            document.body.style.overflow = 'hidden';
        }
        
        function closeFullscreen() {
            var modal = document.getElementById("fullscreenModal");
            modal.style.display = "none";
            
            // Restore body scroll
            document.body.style.overflow = 'auto';
        }
        
        // Close fullscreen on ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeFullscreen();
            }
        });
        
        // Inisialisasi tab jika ada error pada tab ubah password
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($error_password) || isset($success_password)): ?>
                var triggerTab = document.getElementById('ubah-password-tab');
                if (triggerTab) {
                    var tab = new bootstrap.Tab(triggerTab);
                    tab.show();
                }
            <?php endif; ?>
            
            <?php if (isset($error_foto) || isset($success_foto)): ?>
                var fotoModal = new bootstrap.Modal(document.getElementById('uploadFotoModal'));
                fotoModal.show();
            <?php endif; ?>
        });

        // Preview foto sebelum upload
        document.getElementById('foto_profil').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.foto-preview');
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        // Jika sebelumnya adalah placeholder, ganti dengan img
                        const newPreview = document.createElement('img');
                        newPreview.src = e.target.result;
                        newPreview.className = 'foto-preview';
                        newPreview.alt = 'Preview Foto';
                        preview.parentNode.replaceChild(newPreview, preview);
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 