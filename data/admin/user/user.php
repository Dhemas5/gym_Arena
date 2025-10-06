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
                <h1><i class="fas fa-user-shield mr-2"></i>Manajemen User</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
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
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-table mr-2"></i>Data User</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
                                <i class="fas fa-plus-circle mr-2"></i>Tambah User
                            </button>
                            <span class="ml-3 text-muted">
                                <i class="fas fa-users mr-1"></i>Total: <strong><?= $jumlahUser ?></strong> user
                            </span>
                        </div>
                        
                        <div class="table-responsive">
                            <table id="tabelPelatih" class="table table-bordered table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Tanggal Buat</th>
                                        <th style="width: 150px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($jumlahUser == 0) {
                                        echo "<tr><td colspan='7' class='text-center text-muted'><i class='fas fa-inbox fa-3x mb-3 d-block'></i>Tidak ada data user</td></tr>";
                                    } else {
                                        $no = 1;
                                        while ($user = mysqli_fetch_array($queryUser)) {
                                    ?>
                                            <tr>
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td><i class="fas fa-user mr-2 text-primary"></i><?= $user['username']; ?></td>
                                                <td><?= $user['nama_lengkap']; ?></td>
                                                <td><i class="fas fa-envelope mr-2 text-muted"></i><?= $user['email']; ?></td>
                                                <td>
                                                    <?php if($user['role'] == 'admin'): ?>
                                                        <span class="badge badge-danger"><i class="fas fa-crown mr-1"></i>Admin</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-info"><i class="fas fa-user mr-1"></i>Staff</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><i class="far fa-clock mr-2 text-muted"></i><?= date('d M Y', strtotime($user['created_at'])); ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-warning btn-sm edit-btn" 
                                                                data-toggle="modal" 
                                                                data-target="#modalEdit" 
                                                                data-id="<?= $user['id_user']; ?>"
                                                                data-username="<?= $user['username']; ?>"
                                                                data-nama="<?= $user['nama_lengkap']; ?>"
                                                                data-email="<?= $user['email']; ?>"
                                                                data-role="<?= $user['role']; ?>"
                                                                title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="user.php?hapus=<?= $user['id_user']; ?>" onclick="return confirm('Yakin hapus user ini?')" class="btn btn-danger btn-sm" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
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

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="formEdit">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit User</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_user" id="edit_id_user">
                    <div class="form-group">
                        <label><i class="fas fa-user mr-2"></i>Username</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-id-card mr-2"></i>Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" id="edit_nama_lengkap" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope mr-2"></i>Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user-tag mr-2"></i>Role</label>
                        <select name="role" class="form-control" id="edit_role" required>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock mr-2"></i>Password Baru</label>
                        <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit" name="update" class="btn btn-warning">
                        <i class="fas fa-save mr-2"></i>Update
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="formTambah">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Tambah User Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-user mr-2"></i>Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" required placeholder="Masukkan username">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-id-card mr-2"></i>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_lengkap" required placeholder="Masukkan nama lengkap">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope mr-2"></i>Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required placeholder="contoh@email.com">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user-tag mr-2"></i>Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-control" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock mr-2"></i>Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required placeholder="Minimal 6 karakter" minlength="6">
                        <small class="form-text text-muted">Password minimal 6 karakter</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit" name="simpan" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript untuk mengisi data modal edit -->
<script>
$(document).ready(function() {
    // Event handler untuk tombol edit
    $('.edit-btn').on('click', function() {
        // Ambil data dari atribut data-*
        var id = $(this).data('id');
        var username = $(this).data('username');
        var nama = $(this).data('nama');
        var email = $(this).data('email');
        var role = $(this).data('role');
        
        // Isi data ke dalam form modal edit
        $('#edit_id_user').val(id);
        $('#edit_username').val(username);
        $('#edit_nama_lengkap').val(nama);
        $('#edit_email').val(email);
        $('#edit_role').val(role);
    });
    
    // Reset form edit ketika modal ditutup
    $('#modalEdit').on('hidden.bs.modal', function () {
        $('#formEdit')[0].reset();
    });
});
</script>

<?php include '../../../view/master/footer.php'; ?>