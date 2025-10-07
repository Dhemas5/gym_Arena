<?php
require "../../../setting/session.php";
checkSession("admin"); // hanya admin boleh masuk
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>
<?php
require "../../../setting/koneksi.php";

// ====== PROSES TAMBAH USER ======
if (isset($_POST['simpan'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = md5($_POST['password']); // Enkripsi MD5
    $nama = htmlspecialchars($_POST['nama_lengkap']);
    $email = htmlspecialchars($_POST['email']);
    $role = htmlspecialchars($_POST['role']);

    // Cek username sudah ada
    $cek = mysqli_prepare($con, "SELECT * FROM tbl_user WHERE username = ?");
    mysqli_stmt_bind_param($cek, "s", $username);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Username sudah digunakan!');</script>";
    } else {
        $insert = mysqli_prepare($con, "INSERT INTO tbl_user(username, password, role, nama_lengkap, email) VALUES(?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($insert, "sssss", $username, $password, $role, $nama, $email);

        if (mysqli_stmt_execute($insert)) {
            echo "<script>
                alert('User berhasil ditambahkan!');
                window.location='user.php';
            </script>";
        } else {
            echo "<script>
                alert('Gagal menambahkan user! Error: " . mysqli_error($con) . "');
            </script>";
        }
        mysqli_stmt_close($insert);
    }
    mysqli_stmt_close($cek);
}

// ====== PROSES UPDATE USER ======
if (isset($_POST['update'])) {
    $id = $_POST['id_user'];
    $username = htmlspecialchars($_POST['username']);
    $nama = htmlspecialchars($_POST['nama_lengkap']);
    $email = htmlspecialchars($_POST['email']);
    $role = htmlspecialchars($_POST['role']);
    $password = $_POST['password'];

    // Cek username (tidak boleh duplikat)
    $cek = mysqli_prepare($con, "SELECT * FROM tbl_user WHERE username = ? AND id_user != ?");
    mysqli_stmt_bind_param($cek, "si", $username, $id);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>
            alert('Username sudah digunakan!');
            window.location='user.php';
        </script>";
    } else {
        if (!empty($password)) {
            // Jika password diisi, update dengan MD5 baru
            $password_md5 = md5($password);
            $update = mysqli_prepare($con, "UPDATE tbl_user SET username=?, password=?, role=?, nama_lengkap=?, email=? WHERE id_user=?");
            mysqli_stmt_bind_param($update, "sssssi", $username, $password_md5, $role, $nama, $email, $id);
        } else {
            // Jika password kosong, tidak diubah
            $update = mysqli_prepare($con, "UPDATE tbl_user SET username=?, role=?, nama_lengkap=?, email=? WHERE id_user=?");
            mysqli_stmt_bind_param($update, "ssssi", $username, $role, $nama, $email, $id);
        }

        if (mysqli_stmt_execute($update)) {
            echo "<script>
                alert('User berhasil diupdate!');
                window.location='user.php';
            </script>";
        } else {
            echo "<script>alert('Gagal update user! Error: " . mysqli_error($con) . "');</script>";
        }
        mysqli_stmt_close($update);
    }
    mysqli_stmt_close($cek);
}

// ====== PROSES HAPUS USER ======
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $delete = mysqli_prepare($con, "DELETE FROM tbl_user WHERE id_user = ?");
    mysqli_stmt_bind_param($delete, "i", $id);

    if (mysqli_stmt_execute($delete)) {
        echo "<script>
            alert('User berhasil dihapus!');
            window.location='user.php';
        </script>";
    } else {
        echo "<script>alert('Gagal hapus user! Error: " . mysqli_error($con) . "');</script>";
    }
    mysqli_stmt_close($delete);
}

// ====== TAMPILKAN DATA USER ======
$queryUser = mysqli_query($con, "SELECT * FROM tbl_user ORDER BY id_user DESC");
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

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Data User</h3>
            </div>
            <div class="card-body">
                <button class="btn btn-outline-primary mb-3" data-toggle="modal" data-target="#modalTambah">
                    <i class="fas fa-plus"></i> Tambah User
                </button>

                <div class="table-responsive rounded">
                    <table id="tabelPelatih" class="table table-bordered table-striped">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($jumlahUser == 0): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada data user</td>
                                </tr>
                                <?php else:
                                $no = 1;
                                while ($data = mysqli_fetch_assoc($queryUser)): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($data['username']); ?></td>
                                        <td><?= htmlspecialchars($data['nama_lengkap']); ?></td>
                                        <td><?= htmlspecialchars($data['email']); ?></td>
                                        <td><?= htmlspecialchars($data['role']); ?></td>
                                        <td><?= htmlspecialchars($data['created_at']); ?></td>
                                        <td class="text-center align-middle">
                                            <div class="btn-group" role="group" style="gap: 8px;">
                                                <button class="btn btn-warning btn-sm px-3 py-2"
                                                    data-toggle="modal"
                                                    data-target="#modalEdit<?= $data['id_user']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="user.php?hapus=<?= $data['id_user']; ?>"
                                                    onclick="return confirm('Yakin hapus user <?= htmlspecialchars($data['username']); ?>?')"
                                                    class="btn btn-danger btn-sm px-3 py-2">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                            <?php endwhile;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah User</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" required placeholder="Masukkan username">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required placeholder="Masukkan password">
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" required placeholder="Masukkan nama lengkap">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" required placeholder="Masukkan email">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="member">Member</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" name="simpan">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit User -->
<?php
mysqli_data_seek($queryUser, 0);
while ($data = mysqli_fetch_assoc($queryUser)) { ?>
    <div class="modal fade" id="modalEdit<?= $data['id_user']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">Edit User</h5>
                        <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_user" value="<?= $data['id_user']; ?>">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($data['username']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Password (kosongkan jika tidak ingin ubah)</label>
                            <input type="password" class="form-control" name="password" placeholder="Masukkan password baru">
                        </div>
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap" value="<?= htmlspecialchars($data['nama_lengkap']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($data['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" class="form-control" required>
                                <option value="admin" <?= ($data['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="member" <?= ($data['role'] == 'member') ? 'selected' : ''; ?>>Member</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button class="btn btn-warning" name="update">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php } ?>

<?php include '../../../view/master/footer.php'; ?>