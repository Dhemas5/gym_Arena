<?php
require "../../../setting/session.php";
checkSession("admin"); // hanya admin boleh masuk
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>
<?php
require "../../../setting/koneksi.php";

// ====== PROSES TAMBAH MEMBER ======
if (isset($_POST['simpan'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $password = md5($_POST['password']); // Enkripsi MD5
    $no_hp = htmlspecialchars($_POST['no_hp']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $foto = "default.jpg"; // Default foto

    // Cek email sudah ada
    $cek = mysqli_prepare($con, "SELECT * FROM tbl_member WHERE email = ?");
    mysqli_stmt_bind_param($cek, "s", $email);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Email sudah digunakan!');</script>";
    } else {
        $insert = mysqli_prepare($con, "INSERT INTO tbl_member(nama, email, password, no_hp, alamat, foto) VALUES(?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($insert, "ssssss", $nama, $email, $password, $no_hp, $alamat, $foto);

        if (mysqli_stmt_execute($insert)) {
            echo "<script>
                alert('Member berhasil ditambahkan!');
                window.location='member.php';
            </script>";
        } else {
            echo "<script>
                alert('Gagal menambahkan member! Error: " . mysqli_error($con) . "');
            </script>";
        }
        mysqli_stmt_close($insert);
    }
    mysqli_stmt_close($cek);
}

// ====== PROSES UPDATE MEMBER ======
if (isset($_POST['update'])) {
    $id = $_POST['id_member'];
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $no_hp = htmlspecialchars($_POST['no_hp']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $password = $_POST['password'];

    // Cek email (tidak boleh duplikat)
    $cek = mysqli_prepare($con, "SELECT * FROM tbl_member WHERE email = ? AND id_member != ?");
    mysqli_stmt_bind_param($cek, "si", $email, $id);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>
            alert('Email sudah digunakan!');
            window.location='member.php';
        </script>";
    } else {
        if (!empty($password)) {
            // Jika password diisi, update dengan MD5 baru
            $password_md5 = md5($password);
            $update = mysqli_prepare($con, "UPDATE tbl_member SET nama=?, email=?, password=?, no_hp=?, alamat=? WHERE id_member=?");
            mysqli_stmt_bind_param($update, "sssssi", $nama, $email, $password_md5, $no_hp, $alamat, $id);
        } else {
            // Jika password kosong, tidak diubah
            $update = mysqli_prepare($con, "UPDATE tbl_member SET nama=?, email=?, no_hp=?, alamat=? WHERE id_member=?");
            mysqli_stmt_bind_param($update, "ssssi", $nama, $email, $no_hp, $alamat, $id);
        }

        if (mysqli_stmt_execute($update)) {
            echo "<script>
                alert('Member berhasil diupdate!');
                window.location='member.php';
            </script>";
        } else {
            echo "<script>alert('Gagal update member! Error: " . mysqli_error($con) . "');</script>";
        }
        mysqli_stmt_close($update);
    }
    mysqli_stmt_close($cek);
}

// ====== PROSES HAPUS MEMBER ======
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $delete = mysqli_prepare($con, "DELETE FROM tbl_member WHERE id_member = ?");
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

// ====== TAMPILKAN DATA MEMBER ======
$queryMember = mysqli_query($con, "SELECT * FROM tbl_member ORDER BY id_member ASC");
$jumlahMember = mysqli_num_rows($queryMember);
?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Manajemen Member</h1>
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

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Data Member</h3>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-outline-primary mb-3" data-toggle="modal" data-target="#modalTambah">
                            <i class="fas fa-plus-circle"></i> Tambah Member
                        </button>
                        <div class="table-responsive rounded">
                            <table id="tabelMember" class="table table-bordered table-striped">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 20%;">Nama</th>
                                        <th style="width: 20%;">Email</th>
                                        <th style="width: 15%;">No. HP</th>
                                        <th style="width: 20%;">Alamat</th>
                                        <th style="width: 10%;">Tanggal Daftar</th>
                                        <th style="width: 10%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($jumlahMember == 0) {
                                        echo "<tr><td colspan='7' class='text-center text-muted'>Tidak ada data member</td></tr>";
                                    } else {
                                        $no = 1;
                                        while ($member = mysqli_fetch_array($queryMember)) {
                                    ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= htmlspecialchars($member['nama']); ?></td>
                                                <td><?= htmlspecialchars($member['email']); ?></td>
                                                <td><?= htmlspecialchars($member['no_hp'] ?: '-'); ?></td>
                                                <td><?= htmlspecialchars($member['alamat'] ?: '-'); ?></td>
                                                <td><?= date('d M Y', strtotime($member['tanggal_daftar'])); ?></td>
                                                <td class="text-center align-middle">
                                                    <div class="btn-group" style="gap:8px;">
                                                        <button class="btn btn-warning btn-sm px-3 py-2" data-toggle="modal" data-target="#modalEdit<?= $member['id_member']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="member.php?hapus=<?= $member['id_member']; ?>" onclick="return confirm('Yakin hapus member ini?')" class="btn btn-danger btn-sm px-3 py-2">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Modal Edit -->
                                            <div class="modal fade" id="modalEdit<?= $member['id_member']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <form method="POST">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-warning text-dark">
                                                                <h5 class="modal-title">Edit Member</h5>
                                                                <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id_member" value="<?= $member['id_member']; ?>">
                                                                <div class="form-group">
                                                                    <label>Nama</label>
                                                                    <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($member['nama']); ?>" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Email</label>
                                                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($member['email']); ?>" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>No. HP</label>
                                                                    <input type="text" class="form-control" name="no_hp" value="<?= htmlspecialchars($member['no_hp']); ?>" placeholder="Masukkan nomor HP">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Alamat</label>
                                                                    <textarea class="form-control" name="alamat" rows="3" placeholder="Masukkan alamat"><?= htmlspecialchars($member['alamat']); ?></textarea>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Password Baru</label>
                                                                    <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                                                                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                    <i class="fas fa-times"></i> Batal
                                                                </button>
                                                                <button type="submit" name="update" class="btn btn-warning">
                                                                    <i class="fas fa-edit"></i> Perbarui
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
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Member</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" class="form-control" name="nama" required placeholder="Masukkan nama lengkap">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" required placeholder="Masukkan email">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required placeholder="Masukkan password">
                    </div>
                    <div class="form-group">
                        <label>No. HP</label>
                        <input type="text" class="form-control" name="no_hp" placeholder="Masukkan nomor HP">
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea class="form-control" name="alamat" rows="3" placeholder="Masukkan alamat"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Batal</button>
                    <button type="submit" name="simpan" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../../../view/master/footer.php'; ?>

<!-- Script DataTables dengan tombol Export (Sudah disesuaikan dengan footer.php) -->
<script>
$(function() {
    // Jalankan hanya jika tabel ada dan belum pernah diinisialisasi
    if ($("#tabelMember").length && !$.fn.DataTable.isDataTable("#tabelMember")) {
        var table = $("#tabelMember").DataTable({
            paging: true,
            lengthChange: true,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "Semua"]
            ],
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            responsive: true,
            buttons: ["copy", "csv", "excel", "pdf", "print"],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                },
                zeroRecords: "Data tidak ditemukan"
            }
        });

        // Tambahkan tombol export di posisi atas tabel
        table.buttons().container().appendTo('#tabelMember_wrapper .col-md-6:eq(0)');
    }
});
</script>