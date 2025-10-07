<?php
require "../../../setting/session.php";
checkSession("admin"); // hanya admin boleh masuk 
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<?php
require "../../../setting/koneksi.php";

// Proses tambah kategori
if (isset($_POST['simpan'])) {
    $nama = htmlspecialchars($_POST['nama_kategori']);
    $deskripsi = htmlspecialchars($_POST['deskripsi'] ?? ''); // Tambahkan deskripsi

    // Gunakan prepared statement untuk keamanan
    $cek = mysqli_prepare($con, "SELECT * FROM tbl_kategori WHERE nama_kategori = ?");
    mysqli_stmt_bind_param($cek, "s", $nama);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Kategori sudah ada!');</script>";
    } else {
        $insert = mysqli_prepare($con, "INSERT INTO tbl_kategori(nama_kategori, deskripsi) VALUES(?, ?)");
        mysqli_stmt_bind_param($insert, "ss", $nama, $deskripsi);

        if (mysqli_stmt_execute($insert)) {
            echo "<script>
                alert('Kategori berhasil ditambahkan!');
                window.location='kategori.php';
            </script>";
        } else {
            echo "<script>
                alert('Gagal menambahkan kategori! Error: " . mysqli_error($con) . "');
            </script>";
        }
        mysqli_stmt_close($insert);
    }
    mysqli_stmt_close($cek);
}

// Update kategori
if (isset($_POST['update'])) {
    $id = $_POST['id_kategori'];
    $nama = htmlspecialchars($_POST['nama_kategori']);
    $deskripsi = htmlspecialchars($_POST['deskripsi'] ?? ''); // Tambahkan deskripsi

    // Cek apakah nama kategori sudah ada (kecuali kategori yang sedang diupdate)
    $cek = mysqli_prepare($con, "SELECT * FROM tbl_kategori WHERE nama_kategori = ? AND id_kategori != ?");
    mysqli_stmt_bind_param($cek, "si", $nama, $id);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>
            alert('Nama kategori sudah ada, gunakan nama lain!');
            window.location='kategori.php';
        </script>";
    } else {
        $update = mysqli_prepare($con, "UPDATE tbl_kategori SET nama_kategori = ?, deskripsi = ? WHERE id_kategori = ?");
        mysqli_stmt_bind_param($update, "ssi", $nama, $deskripsi, $id);

        if (mysqli_stmt_execute($update)) {
            echo "<script>
                alert('Kategori berhasil diupdate!');
                window.location='kategori.php';
            </script>";
        } else {
            echo "<script>
                alert('Gagal update kategori! Error: " . mysqli_error($con) . "');
            </script>";
        }
        mysqli_stmt_close($update);
    }
    mysqli_stmt_close($cek);
}

// Hapus kategori
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    // Gunakan prepared statement untuk delete
    $delete = mysqli_prepare($con, "DELETE FROM tbl_kategori WHERE id_kategori = ?");
    mysqli_stmt_bind_param($delete, "i", $id);

    if (mysqli_stmt_execute($delete)) {
        echo "<script>
            alert('Kategori berhasil dihapus!');
            window.location='kategori.php';
        </script>";
    } else {
        echo "<script>
            alert('Gagal hapus kategori! Error: " . mysqli_error($con) . "');
        </script>";
    }
    mysqli_stmt_close($delete);
}

$queryKategori = mysqli_query($con, "SELECT * FROM tbl_kategori ORDER BY id_kategori ASC");
$jumlahKategori = mysqli_num_rows($queryKategori);
?>
<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Menu Kategori</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Kategori</li>
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
                <div class="mb-3">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
                            <i class="fas fa-plus"></i> Tambah Kategori
                        </button>
                    </div>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Data Kategori</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive rounded">
                            <table id="tabelPelatih" class="table table-bordered table-striped">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 40%;">Nama Kategori</th>
                                        <th style="width: 35%;">Deskripsi</th>
                                        <th style="width: 20%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($jumlahKategori == 0) {
                                    ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Tidak ada data kategori</td>
                                        </tr>
                                        <?php
                                    } else {
                                        $jumlah = 1;
                                        while ($data = mysqli_fetch_array($queryKategori)) {
                                        ?>
                                            <tr>
                                                <td><?php echo $jumlah; ?></td>
                                                <td><?php echo htmlspecialchars($data['nama_kategori']); ?></td>
                                                <td><?php echo htmlspecialchars($data['deskripsi'] ?? '-'); ?></td>
                                                <td class="text-center align-middle">
                                                    <div class="btn-group" role="group" style="gap: 8px;">
                                                        <button class="btn btn-warning btn-sm px-3 py-2"
                                                            data-toggle="modal"
                                                            data-target="#modalEdit<?php echo $data['id_kategori']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>

                                                        <a href="kategori.php?hapus=<?php echo $data['id_kategori']; ?>"
                                                            onclick="return confirm('Yakin hapus kategori <?php echo htmlspecialchars($data['nama_kategori']); ?>?')"
                                                            class="btn btn-danger btn-sm px-3 py-2">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>

                                            </tr>
                                    <?php
                                            $jumlah++;
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

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTambahLabel">Tambah Kategori</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama_kategori">Nama Kategori</label>
                        <input type="text" class="form-control" name="nama_kategori" id="nama_kategori" required
                            placeholder="Masukkan nama kategori">
                    </div>
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi"
                            placeholder="Masukkan deskripsi kategori (opsional)" rows="3"></textarea>
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

<!-- Modal Edit Kategori -->
<?php
// Reset pointer query untuk modal edit
mysqli_data_seek($queryKategori, 0);
while ($data = mysqli_fetch_array($queryKategori)) {
?>
    <div class="modal fade" id="modalEdit<?php echo $data['id_kategori']; ?>" tabindex="-1" aria-labelledby="modalEditLabel<?php echo $data['id_kategori']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="modalEditLabel<?php echo $data['id_kategori']; ?>">Edit Kategori</h5>
                        <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_kategori" value="<?php echo $data['id_kategori']; ?>">
                        <div class="form-group">
                            <label for="nama_kategori_edit<?php echo $data['id_kategori']; ?>">Nama Kategori</label>
                            <input type="text" class="form-control" name="nama_kategori"
                                id="nama_kategori_edit<?php echo $data['id_kategori']; ?>"
                                value="<?php echo htmlspecialchars($data['nama_kategori']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="deskripsi_edit<?php echo $data['id_kategori']; ?>">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi"
                                id="deskripsi_edit<?php echo $data['id_kategori']; ?>"
                                rows="3"><?php echo htmlspecialchars($data['deskripsi'] ?? ''); ?></textarea>
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