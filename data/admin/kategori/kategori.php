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

$queryKategori = mysqli_query($con, "SELECT * FROM tbl_kategori");
$jumlahKategori = mysqli_num_rows($queryKategori)
?>
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Data Kategori</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Kategori</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow rounded-3 overflow-hidden">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">Data Kategori</h3>
                    </div>
                    <div class="card-body bg-dark p-3">
                        <div class="table-responsive rounded">
                            <button class="btn btn-light btn-sm text-primary" data-toggle="modal" data-target="#modalTambah">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                            <table id="tabelPelatih" class="table table-bordered table-hover text-white mb-0">
                                <thead class="bg-primary">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 20%;">Nama</th>
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
                <div class="modal-body bg-dark text-white">
                    <div class="form-group">
                        <label for="nama_kategori">Nama Kategori</label>
                        <input type="text" class="form-control" name="nama_kategori" id="nama_kategori" required>
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