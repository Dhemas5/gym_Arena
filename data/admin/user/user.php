<?php
require "../../../setting/session.php";
checkSession("admin"); // hanya admin boleh masuk
require "../../../setting/koneksi.php";
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<?php
// Query untuk menampilkan data user
$queryUser = mysqli_query($con, "SELECT * FROM tbl_user ORDER BY id_user ASC");
?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Menu User</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Beranda</a></li>
                    <li class="breadcrumb-item active">User</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Data User</h3>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-outline-primary mb-3" data-toggle="modal" data-target="#modalTambah">
                            <i class="fas fa-plus"></i> Tambah User
                        </button>
                        <div class="table-responsive rounded">
                            <table id="tabelPelatih" class="table table-bordered table-striped table-hover">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>Tanggal Buat</th>
                                        <th style="width: 15%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $jumlahUser = mysqli_num_rows($queryUser);
                                    if ($jumlahUser == 0) {
                                        // colspan diatur menjadi 6
                                        echo "<tr><td colspan='6' class='text-center text-muted'><i class='fas fa-inbox fa-3x mb-3 d-block'></i>Tidak ada data user</td></tr>";
                                    } else {
                                        $no = 1;
                                        mysqli_data_seek($queryUser, 0);
                                        while ($user = mysqli_fetch_array($queryUser)) {
                                    ?>
                                            <tr>
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td><i class="fas fa-user mr-2 text-primary"></i><?= htmlspecialchars($user['username']); ?></td>
                                                <td><?= htmlspecialchars($user['nama_lengkap']); ?></td>
                                                <td><i class="fas fa-envelope mr-2 text-muted"></i><?= htmlspecialchars($user['email']); ?></td>
                                                <td><i class="far fa-clock mr-2 text-muted"></i><?= date('d M Y', strtotime($user['created_at'] ?? 'now')); ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group" style="gap:5px;">
                                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEdit<?= $user['id_user']; ?>" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm btn-hapus" data-id="<?= $user['id_user']; ?>" data-nama="<?= htmlspecialchars($user['username']); ?>" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
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

<!-- Modal Tambah User -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formTambah" method="POST">
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
                    <!-- Field Role dihapus dari sini -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-2"></i>Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit (Looping untuk setiap data user) -->
<?php
// Reset pointer query lagi untuk loop modal edit
if (mysqli_num_rows($queryUser) > 0) {
    mysqli_data_seek($queryUser, 0);
    while ($user = mysqli_fetch_array($queryUser)) {
?>
        <div class="modal fade" id="modalEdit<?= $user['id_user']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="formEdit">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit User</h5>
                            <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_user" value="<?= $user['id_user']; ?>">
                            <div class="form-group">
                                <label><i class="fas fa-user mr-2"></i>Username</label>
                                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-id-card mr-2"></i>Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-envelope mr-2"></i>Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <!-- Field Role dihapus dari sini -->
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
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save mr-2"></i>Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
<?php
    }
}
?>

<?php include '../../../view/master/footer.php'; ?>