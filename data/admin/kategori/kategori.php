<?php require "../../../setting/session.php";
checkSession("admin"); // hanya admin boleh masuk 
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<?php
require "../../../setting/session.php";
require "../../../setting/koneksi.php";

// Proses tambah kategori
if (isset($_POST['simpan'])) {
    $nama = htmlspecialchars($_POST['nama_kategori']);
    $cek = mysqli_query($con, "SELECT * FROM tbl_kategori WHERE nama_kategori='$nama'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Kategori sudah ada!');</script>";
    } else {
        $insert = mysqli_query($con, "INSERT INTO tbl_kategori(nama_kategori) VALUES('$nama')");
        if ($insert) {
            echo "<script>alert('Kategori berhasil ditambahkan!');window.location='kategori.php';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan kategori!');</script>";
        }
    }
}

// Update kategori
if (isset($_POST['update'])) {
    $id = $_POST['id_kategori'];
    $nama = htmlspecialchars($_POST['nama_kategori']);

    // Cek apakah nama kategori sudah ada (kecuali kategori yang sedang diupdate)
    $cek = mysqli_query($con, "SELECT * FROM tbl_kategori WHERE nama_kategori='$nama' AND id_kategori != '$id'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Nama kategori sudah ada, gunakan nama lain!');window.location='kategori.php';</script>";
    } else {
        $update = mysqli_query($con, "UPDATE tbl_kategori SET nama_kategori='$nama' WHERE id_kategori='$id'");
        if ($update) {
            echo "<script>alert('Kategori berhasil diupdate!');window.location='kategori.php';</script>";
        } else {
            echo "<script>alert('Gagal update kategori!');</script>";
        }
    }
}


// Hapus kategori
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $delete = mysqli_query($con, "DELETE FROM tbl_kategori WHERE id_kategori='$id'");
    if ($delete) {
        echo "<script>alert('Kategori berhasil dihapus!');window.location='kategori.php';</script>";
    } else {
        echo "<script>alert('Gagal hapus kategori!');</script>";
    }
}

$queryKategori = mysqli_query($con, "SELECT * FROM tbl_kategori");
$jumlahKategori = mysqli_num_rows($queryKategori)
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
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Data Kategori</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive rounded">
                            <button class="btn btn-outline-primary" data-toggle="modal" data-target="#modalTambah">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                            <table id="tabelPelatih" class="table table-bordered table-striped">
                                <thead class="bg-primary">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 50%;">Nama</th>
                                        <th style="width: 20%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($jumlahKategori == 0) {
                                    ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Tidak ada data kategori</td>
                                        </tr>
                                        <?php
                                    } else {
                                        $jumlah = 1;
                                        while ($data = mysqli_fetch_array($queryKategori)) {
                                        ?>
                                            <tr>
                                                <td><?php echo $jumlah; ?></td>
                                                <td><?php echo $data['nama_kategori']; ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEdit<?php echo $data['id_kategori']; ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <a href="kategori.php?hapus=<?php echo $data['id_kategori']; ?>" onclick="return confirm('Yakin hapus kategori ini?')" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </a>
                                                </td>
                                            </tr>
                                            <!-- Modal Edit -->
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
                                                            <div class="modal-body bg-dark text-white">
                                                                <input type="hidden" name="id_kategori" value="<?php echo $data['id_kategori']; ?>">
                                                                <div class="form-group">
                                                                    <label for="nama_kategori">Nama Kategori</label>
                                                                    <input type="text" class="form-control" name="nama_kategori" value="<?php echo $data['nama_kategori']; ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-dark">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Batal</button>
                                                                <button type="submit" name="update" class="btn btn-warning"><i class="fas fa-edit"></i> Update</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
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
                <div class="modal-body bg-dark text-white">
                    <div class="form-group">
                        <label for="nama_kategori">Nama Kategori</label>
                        <input type="text" class="form-control" name="nama_kategori" id="nama_kategori" required>
                    </div>
                </div>
                <div class="modal-footer bg-dark">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>

                    <button type="submit" name="simpan" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<?php include '../../../view/master/footer.php'; ?>