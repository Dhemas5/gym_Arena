<?php
require "../../../setting/session.php";
checkSession("admin"); // hanya admin boleh masuk
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<?php
require "../../../setting/koneksi.php";

// Proses tambah member
if (isset($_POST['simpan'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $no_hp = htmlspecialchars($_POST['no_hp']);
    $alamat = htmlspecialchars($_POST['alamat']);

    // Cek apakah email sudah terdaftar
    $cek = mysqli_prepare($con, "SELECT * FROM tbl_member WHERE email = ?");
    mysqli_stmt_bind_param($cek, "s", $email);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Email sudah terdaftar!');</script>";
    } else {
        $insert = mysqli_prepare($con, "INSERT INTO tbl_member (nama, email, password, no_hp, alamat) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($insert, "sssss", $nama, $email, $password, $no_hp, $alamat);
        if (mysqli_stmt_execute($insert)) {
            echo "<script>
                alert('Member berhasil ditambahkan!');
                window.location='member.php';
            </script>";
        } else {
            echo "<script>alert('Gagal menambahkan member! Error: " . mysqli_error($con) . "');</script>";
        }
        mysqli_stmt_close($insert);
    }
    mysqli_stmt_close($cek);
}

// Proses update member
if (isset($_POST['update'])) {
    $id = $_POST['id_member'];
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $no_hp = htmlspecialchars($_POST['no_hp']);
    $alamat = htmlspecialchars($_POST['alamat']);

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $update = mysqli_prepare($con, "UPDATE tbl_member SET nama=?, email=?, password=?, no_hp=?, alamat=? WHERE id_member=?");
        mysqli_stmt_bind_param($update, "sssssi", $nama, $email, $password, $no_hp, $alamat, $id);
    } else {
        $update = mysqli_prepare($con, "UPDATE tbl_member SET nama=?, email=?, no_hp=?, alamat=? WHERE id_member=?");
        mysqli_stmt_bind_param($update, "ssssi", $nama, $email, $no_hp, $alamat, $id);
    }

    if (mysqli_stmt_execute($update)) {
        echo "<script>
            alert('Data member berhasil diupdate!');
            window.location='member.php';
        </script>";
    } else {
        echo "<script>alert('Gagal update member! Error: " . mysqli_error($con) . "');</script>";
    }
    mysqli_stmt_close($update);
}

// Proses hapus member
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $delete = mysqli_prepare($con, "DELETE FROM tbl_member WHERE id_member=?");
    mysqli_stmt_bind_param($delete, "i", $id);
    if (mysqli_stmt_execute($delete)) {
        echo "<script>
            alert('Member berhasil dihapus!');
            window.location='member.php';
        </script>";
    } else {
        echo "<script>alert('Gagal hapus member! Error: " . mysqli_error($con) . "');</script>";
    }
    mysqli_stmt_close($delete);
}

$queryMember = mysqli_query($con, "SELECT * FROM tbl_member ORDER BY id_member DESC");
$jumlahMember = mysqli_num_rows($queryMember);
?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Menu Member</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Member</li>
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
                        <h3 class="card-title">Data Member</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive rounded">
                            <button class="btn btn-outline-primary mb-3" data-toggle="modal" data-target="#modalTambah">
                                <i class="fas fa-plus"></i> Tambah Member
                            </button>
                            <table id="tabelMember" class="table table-bordered table-striped">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>No HP</th>
                                        <th>Alamat</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($jumlahMember == 0) {
                                        echo '<tr><td colspan="7" class="text-center">Tidak ada data member</td></tr>';
                                    } else {
                                        $no = 1;
                                        while ($data = mysqli_fetch_array($queryMember)) {
                                    ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= htmlspecialchars($data['nama']); ?></td>
                                                <td><?= htmlspecialchars($data['email']); ?></td>
                                                <td><?= htmlspecialchars($data['no_hp']); ?></td>
                                                <td><?= htmlspecialchars($data['alamat']); ?></td>
                                                <td><?= htmlspecialchars($data['tanggal_daftar']); ?></td>
                                                <td class="text-center align-middle">
                                                    <div class="btn-group" role="group" style="gap: 6px;">
                                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEdit<?= $data['id_member']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="member.php?hapus=<?= $data['id_member']; ?>" onclick="return confirm('Yakin ingin hapus member <?= htmlspecialchars($data['nama']); ?>?')" class="btn btn-danger btn-sm">
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

<!-- Modal Tambah Member -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Member</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" class="form-control" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" autocomplete="new-password" required>
                    </div>
                    <div class="form-group">
                        <label>No HP</label>
                        <input type="text" class="form-control" name="no_hp">
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea class="form-control" name="alamat" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" name="simpan" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Member -->
<?php
mysqli_data_seek($queryMember, 0);
while ($data = mysqli_fetch_array($queryMember)) {
?>
<div class="modal fade" id="modalEdit<?= $data['id_member']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Edit Member</h5>
                    <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_member" value="<?= $data['id_member']; ?>">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($data['nama']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($data['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Password (Kosongkan jika tidak diubah)</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="form-group">
                        <label>No HP</label>
                        <input type="text" class="form-control" name="no_hp" value="<?= htmlspecialchars($data['no_hp']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea class="form-control" name="alamat" rows="3"><?= htmlspecialchars($data['alamat']); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" name="update" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Update
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php } ?>

<?php include '../../../view/master/footer.php'; ?>

<!-- Tambahkan script DataTables + Buttons -->
<script>
$(document).ready(function () {
    $('#tabelMember').DataTable({
        dom:
            "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
            "<'row'<'col-sm-12'B>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        language: {
            "sProcessing":   "Sedang memproses...",
            "sLengthMenu":   "Tampilkan _MENU_ data per halaman",
            "sZeroRecords":  "Tidak ada data yang sesuai",
            "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 data",
            "sInfoFiltered": "(disaring dari total _MAX_ data)",
            "sSearch":       "Cari:",
            "oPaginate": {
                "sFirst":    "Pertama",
                "sPrevious": "Sebelumnya",
                "sNext":     "Selanjutnya",
                "sLast":     "Terakhir"
            },
            "buttons": {
                "copy": "Copy",
                "csv": "CSV",
                "excel": "Excel",
                "pdf": "PDF",
                "print": "Print"
            }
        },
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]] // 🔥 Tambahkan ini
    });
});
</script>