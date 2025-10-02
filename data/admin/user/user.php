<?php require "../../../setting/session.php";
checkSession("admin"); // hanya admin boleh masuk 
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<?php
require "../../../setting/session.php";
require "../../../setting/koneksi.php";
// ====== PROSES TAMBAH USER ======
if (isset($_POST['simpan'])) {
    $username = htmlspecialchars($_POST['username']);
    $nama = htmlspecialchars($_POST['nama_lengkap']);
    $email = htmlspecialchars($_POST['email']);
    $role = htmlspecialchars($_POST['role']);
    $password = md5($_POST['password']); // pakai MD5

    // Cek username/email apakah sudah ada
    $cek = mysqli_query($con, "SELECT * FROM tbl_user WHERE username='$username' OR email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username atau email sudah digunakan!');</script>";
    } else {
        $insert = mysqli_query($con, "INSERT INTO tbl_user(username, password, role, nama_lengkap, email, created_at) 
                                      VALUES('$username', '$password', '$role', '$nama', '$email', NOW())");
        if ($insert) {
            echo "<script>alert('User berhasil ditambahkan!');window.location='user.php';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan user!');</script>";
        }
    }
}

// ====== PROSES UPDATE USER ======
if (isset($_POST['update'])) {
    $id = $_POST['id_user'];
    $username = htmlspecialchars($_POST['username']);
    $nama = htmlspecialchars($_POST['nama_lengkap']);
    $email = htmlspecialchars($_POST['email']);
    $role = htmlspecialchars($_POST['role']);
    $passwordBaru = $_POST['password'];

    // cek username / email unik (kecuali id_user sekarang)
    $cek = mysqli_query($con, "SELECT * FROM tbl_user WHERE (username='$username' OR email='$email') AND id_user != '$id'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username/email sudah digunakan!');window.location='user.php';</script>";
    } else {
        if (!empty($passwordBaru)) {
            $password = md5($passwordBaru); // pakai MD5
            $update = mysqli_query($con, "UPDATE tbl_user 
                SET username='$username', nama_lengkap='$nama', email='$email', role='$role', password='$password' 
                WHERE id_user='$id'");
        } else {
            $update = mysqli_query($con, "UPDATE tbl_user 
                SET username='$username', nama_lengkap='$nama', email='$email', role='$role' 
                WHERE id_user='$id'");
        }

        if ($update) {
            echo "<script>alert('User berhasil diupdate!');window.location='user.php';</script>";
        } else {
            echo "<script>alert('Gagal update user!');</script>";
        }
    }
}

// ====== PROSES HAPUS USER ======
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $delete = mysqli_query($con, "DELETE FROM tbl_user WHERE id_user='$id'");
    if ($delete) {
        echo "<script>alert('User berhasil dihapus!');window.location='user.php';</script>";
    } else {
        echo "<script>alert('Gagal hapus user!');</script>";
    }
}

// Ambil semua user
$queryUser = mysqli_query($con, "SELECT * FROM tbl_user ORDER BY created_at DESC");
$jumlahUser = mysqli_num_rows($queryUser);
?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Manajemen User</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">User</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Data User</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive rounded">
                            <button class="btn btn-outline-primary" data-toggle="modal" data-target="#modalTambah">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                            <table id="tabelPelatih" class="table table-bordered table-striped mt-3">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>No</th>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Tanggal Buat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($jumlahUser == 0) {
                                        echo "<tr><td colspan='7' class='text-center'>Tidak ada data user</td></tr>";
                                    } else {
                                        $no = 1;
                                        while ($user = mysqli_fetch_array($queryUser)) {
                                    ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= $user['username']; ?></td>
                                                <td><?= $user['nama_lengkap']; ?></td>
                                                <td><?= $user['email']; ?></td>
                                                <td><?= ucfirst($user['role']); ?></td>
                                                <td><?= $user['created_at']; ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEdit<?= $user['id_user']; ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <a href="user.php?hapus=<?= $user['id_user']; ?>" onclick="return confirm('Yakin hapus user ini?')" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </a>
                                                </td>
                                            </tr>

                                            <!-- Modal Edit -->
                                            <div class="modal fade" id="modalEdit<?= $user['id_user']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form method="POST">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-warning">
                                                                <h5 class="modal-title">Edit User</h5>
                                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                            </div>
                                                            <div class="modal-body bg-dark text-white">
                                                                <input type="hidden" name="id_user" value="<?= $user['id_user']; ?>">
                                                                <div class="form-group">
                                                                    <label>Username</label>
                                                                    <input type="text" class="form-control" name="username" value="<?= $user['username']; ?>" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Nama Lengkap</label>
                                                                    <input type="text" class="form-control" name="nama_lengkap" value="<?= $user['nama_lengkap']; ?>" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Email</label>
                                                                    <input type="email" class="form-control" name="email" value="<?= $user['email']; ?>" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Role</label>
                                                                    <select name="role" class="form-control" required>
                                                                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                                        <option value="staff" <?= $user['role'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Password Baru (kosongkan jika tidak ganti)</label>
                                                                    <input type="password" class="form-control" name="password">
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-dark">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                                <button type="submit" name="update" class="btn btn-warning">Update</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                    <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah User</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body bg-dark text-white">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </div>
                <div class="modal-footer bg-dark">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../../../view/master/footer.php'; ?>