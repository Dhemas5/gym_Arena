<?php
require "../../../setting/session.php";
checkSession("member");
require "../../../setting/koneksi.php";

// Ambil data member yang sedang login
$id_member = $_SESSION['id_member'];
$query = $con->prepare("SELECT * FROM tbl_member WHERE id_member = ?");
$query->bind_param("s", $id_member);
$query->execute();
$result = $query->get_result();
$member = $result->fetch_assoc();

if (isset($_POST['update_profile'])) {
    $nama   = htmlspecialchars($_POST['nama']);
    $email  = htmlspecialchars($_POST['email']);
    $no_hp  = htmlspecialchars($_POST['no_hp']);
    $alamat = htmlspecialchars($_POST['alamat']);

    // === Folder tujuan upload ===
    $target_dir = "../../../data/member/img/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Ambil foto lama (kalau ada)
    $foto_lama = !empty($member['foto']) ? $member['foto'] : null;
    $foto_baru = $foto_lama; // default: tidak diganti

    // === Proses Upload Foto Baru ===
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed)) {
            $nama_file = "member_" . $id_member . "_" . time() . "." . $ext;
            $target_file = $target_dir . $nama_file;

            // Upload file ke folder
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                // Jika ada foto lama, hapus (tapi hanya kalau memang ada)
                if (!empty($foto_lama) && file_exists($target_dir . $foto_lama)) {
                    unlink($target_dir . $foto_lama);
                }
                // Ganti nama foto baru di variabel
                $foto_baru = $nama_file;
            } else {
                echo "<script>alert('❌ Gagal upload foto. Periksa izin folder!');</script>";
            }
        } else {
            echo "<script>alert('❌ Format file tidak valid! Hanya JPG, JPEG, PNG, GIF.');</script>";
        }
    }

    // === Update data ke database ===
    $update = $con->prepare("
        UPDATE tbl_member 
        SET nama = ?, email = ?, no_hp = ?, alamat = ?, foto = ?
        WHERE id_member = ?
    ");
    $update->bind_param("ssssss", $nama, $email, $no_hp, $alamat, $foto_baru, $id_member);

    if ($update->execute()) {
        // Update session agar data langsung berubah
        $_SESSION['nama']  = $nama;
        $_SESSION['email'] = $email;
        $_SESSION['no_hp'] = $no_hp;

        echo "<script>
            alert('✅ Profil berhasil diperbarui!');
            window.location.href = 'profile.php';
        </script>";
        exit;
    } else {
        echo "<script>alert('❌ Gagal update data: " . addslashes($update->error) . "');</script>";
    }
}
?>

<?php include '../../../view/master_member/header.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary"><i class="fas fa-user-circle"></i> Edit Profil Member</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                        <li class="breadcrumb-item active">Edit Profil</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img src="../../../data/member/img/<?= !empty($member['foto']) ? htmlspecialchars($member['foto']) : 'default.png'; ?>"
                                    class="img-fluid rounded-circle mb-3 shadow border"
                                    style="max-width: 150px; height: 150px; object-fit: cover;">
                                <input type="file" name="foto" class="form-control mt-2" accept="image/*">
                            </div>

                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($member['nama']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($member['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>No. HP</label>
                                    <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($member['no_hp']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Alamat</label>
                                    <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($member['alamat']); ?></textarea>
                                </div>

                                <div class="form-group text-right">
                                    <a href="profile.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Batal</a>
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../../../view/master_member/footer.php'; ?>
</div>

<!-- Script -->
<script src="../../../assets/assets_admin/plugins/jquery/jquery.min.js"></script>
<script src="../../../assets/assets_admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../../assets/assets_admin/dist/js/adminlte.min.js"></script>
</body>

</html>