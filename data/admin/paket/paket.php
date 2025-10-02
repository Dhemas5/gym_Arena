<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<?php
require "../../../setting/session.php";
require "../../../setting/koneksi.php";

// Proses tambah paket
if (isset($_POST['simpan'])) {
    $nama = htmlspecialchars($_POST['nama_paket']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $harga = htmlspecialchars($_POST['harga']);
    $durasi = htmlspecialchars($_POST['durasi_hari']);

    $cek = mysqli_query($con, "SELECT * FROM tbl_paket WHERE nama_paket='$nama'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Paket sudah ada!');</script>";
    } else {
        $insert = mysqli_query($con, "INSERT INTO tbl_paket(nama_paket, deskripsi, harga, durasi_hari) 
                                      VALUES('$nama','$deskripsi','$harga','$durasi')");
        if ($insert) {
            echo "<script>alert('Paket berhasil ditambahkan!');window.location='paket.php';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan paket!');</script>";
        }
    }
}

// Update paket
if (isset($_POST['update'])) {
    $id = $_POST['id_paket'];
    $nama = htmlspecialchars($_POST['nama_paket']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $harga = htmlspecialchars($_POST['harga']);
    $durasi = htmlspecialchars($_POST['durasi_hari']);

    // Cek apakah nama paket sudah ada (kecuali yang sedang diupdate)
    $cek = mysqli_query($con, "SELECT * FROM tbl_paket WHERE nama_paket='$nama' AND id_paket != '$id'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Nama paket sudah ada, gunakan nama lain!');window.location='paket.php';</script>";
    } else {
        $update = mysqli_query($con, "UPDATE tbl_paket 
                                      SET nama_paket='$nama', deskripsi='$deskripsi', harga='$harga', durasi_hari='$durasi' 
                                      WHERE id_paket='$id'");
        if ($update) {
            echo "<script>alert('Paket berhasil diupdate!');window.location='paket.php';</script>";
        } else {
            echo "<script>alert('Gagal update paket!');</script>";
        }
    }
}

// Hapus paket
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $delete = mysqli_query($con, "DELETE FROM tbl_paket WHERE id_paket='$id'");
    if ($delete) {
        echo "<script>alert('Paket berhasil dihapus!');window.location='paket.php';</script>";
    } else {
        echo "<script>alert('Gagal hapus paket!');</script>";
    }
}

$queryPaket = mysqli_query($con, "SELECT * FROM tbl_paket");
$jumlahPaket = mysqli_num_rows($queryPaket)
?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Menu Paket</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Paket</li>
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
                        <h3 class="card-title">Data Paket</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive rounded">
                            <button class="btn btn-outline-primary" data-toggle="modal" data-target="#modalTambah">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                            <table id="tabelPelatih" class="table table-bordered table-striped">
                                <thead class="bg-primary">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Paket</th>
                                        <th>Deskripsi</th>
                                        <th>Harga</th>
                                        <th>Durasi (hari)</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($jumlahPaket == 0) {
                                    ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada data paket</td>
                                        </tr>
                                        <?php
                                    } else {
                                        $no = 1;
                                        while ($data = mysqli_fetch_array($queryPaket)) {
                                        ?>
                                            <tr>
                                                <td><?= $no; ?></td>
                                                <td><?= $data['nama_paket']; ?></td>
                                                <td><?= $data['deskripsi']; ?></td>
                                                <td><?= number_format($data['harga'], 0, ',', '.'); ?></td>
                                                <td><?= $data['durasi_hari']; ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEdit<?= $data['id_paket']; ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <a href="paket.php?hapus=<?= $data['id_paket']; ?>" onclick="return confirm('Yakin hapus paket ini?')" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </a>
                                                </td>
                                            </tr>
                                            <!-- Modal Edit -->
                                            <div class="modal fade" id="modalEdit<?= $data['id_paket']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form method="POST">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-warning text-dark">
                                                                <h5 class="modal-title">Edit Paket</h5>
                                                                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body bg-dark text-white">
                                                                <input type="hidden" name="id_paket" value="<?= $data['id_paket']; ?>">
                                                                <div class="form-group">
                                                                    <label>Nama Paket</label>
                                                                    <input type="text" class="form-control" name="nama_paket" value="<?= $data['nama_paket']; ?>" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Deskripsi</label>
                                                                    <textarea class="form-control" name="deskripsi" required><?= $data['deskripsi']; ?></textarea>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Harga</label>
                                                                    <input type="number" class="form-control" name="harga" value="<?= $data['harga']; ?>" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Durasi (hari)</label>
                                                                    <input type="number" class="form-control" name="durasi_hari" value="<?= $data['durasi_hari']; ?>" required>
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
                                            $no++;
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

<!-- Modal Tambah Paket -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Paket</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-dark text-white">
                    <div class="form-group">
                        <label>Nama Paket</label>
                        <input type="text" class="form-control" name="nama_paket" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Harga</label>
                        <input type="number" class="form-control" name="harga" required>
                    </div>
                    <div class="form-group">
                        <label>Durasi (hari)</label>
                        <input type="number" class="form-control" name="durasi_hari" required>
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