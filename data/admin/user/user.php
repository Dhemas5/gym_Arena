<?php
require "../../../setting/session.php";
checkSession("admin"); // hanya admin boleh masuk
require "../../../setting/koneksi.php";
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<?php
// Query untuk menampilkan data user - DIPERBAIKI: nama_lengkap menjadi nama_lengkap
$queryUser = mysqli_query($con, "SELECT * FROM tbl_user ORDER BY id_user ASC");
?>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
.btn-group {
    display: flex;
    flex-wrap: nowrap;
}

.btn-hapus:hover {
    transform: scale(1.05);
    transition: transform 0.2s;
}

.table-responsive {
    border-radius: 8px;
    overflow: hidden;
}

.card-header {
    border-bottom: 0;
}

.breadcrumb {
    background: transparent;
    margin-bottom: 0;
}
</style>

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
                        <div class="table-responsive rounded">
                            <table id="tabelUser" class="table table-bordered table-striped table-hover">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Tanggal Buat</th>
                                        <th style="width: 15%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $jumlahUser = mysqli_num_rows($queryUser);
                                    if ($jumlahUser == 0) {
                                        echo "<tr><td colspan='7' class='text-center text-muted'><i class='fas fa-inbox fa-3x mb-3 d-block'></i>Tidak ada data user</td></tr>";
                                    } else {
                                        $no = 1;
                                        mysqli_data_seek($queryUser, 0);
                                        while ($user = mysqli_fetch_array($queryUser)) {
                                    ?>
                                            <tr>
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td><i class="fas fa-user mr-2 text-primary"></i><?= htmlspecialchars($user['username']); ?></td>
                                                <td><?= htmlspecialchars($user['nama_lengkap']); ?></td> <!-- DIPERBAIKI: nama_lengkap -->
                                                <td><i class="fas fa-envelope mr-2 text-muted"></i><?= htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $user['role'] == 'admin' ? 'danger' : 'info'; ?>">
                                                        <?= strtoupper($user['role']); ?>
                                                    </span>
                                                </td>
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
                                <input type="text" class="form-control" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required> <!-- DIPERBAIKI: nama_lengkap -->
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-envelope mr-2"></i>Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-user-tag mr-2"></i>Role</label>
                                <select class="form-control" name="role" required>
                                    <option value="staff" <?= $user['role'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
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

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
$(document).ready(function() {
    // SweetAlert untuk konfirmasi hapus
    $('.btn-hapus').on('click', function(e) {
        e.preventDefault();
        
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        
        Swal.fire({
            title: 'Hapus User?',
            html: `Anda yakin ingin menghapus user <strong>"${nama}"</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            backdrop: true,
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                hapusUser(id);
            }
        });
    });
    
    // Fungsi hapus user via AJAX
    function hapusUser(id) {
        $.ajax({
            url: 'proses_user.php',
            type: 'GET',
            data: {
                action: 'hapus',
                id: id
            },
            dataType: 'json',
            beforeSend: function() {
                // Tampilkan loading
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Sedang menghapus user',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                Swal.close();
                if (response.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK',
                        timer: 2000,
                        timerProgressBar: true
                    }).then((result) => {
                        // Refresh halaman setelah berhasil hapus
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.close();
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menghapus user',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }
    
    // Handle form edit
    $('.formEdit').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const modalId = form.closest('.modal').attr('id');
        const formData = new FormData(this);
        formData.append('action', 'update');
        
        $.ajax({
            url: 'proses_user.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                // Tampilkan loading
                $('#' + modalId).modal('hide');
                Swal.fire({
                    title: 'Memperbarui...',
                    text: 'Sedang mengupdate data user',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                Swal.close();
                if (response.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK',
                        timer: 2000,
                        timerProgressBar: true
                    }).then((result) => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        // Tampilkan kembali modal edit jika error
                        $('#' + modalId).modal('show');
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.close();
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error Sistem!',
                    html: `Terjadi kesalahan saat mengupdate user.<br><small>${error}</small>`,
                    icon: 'error',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    // Tampilkan kembali modal edit jika error
                    $('#' + modalId).modal('show');
                });
            }
        });
    });
});
</script>