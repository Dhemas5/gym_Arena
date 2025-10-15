<?php
require "../../../setting/session.php";
checkSession("admin"); // hanya admin boleh masuk
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<?php
require "../../../setting/koneksi.php";

// ====== Fungsi untuk Generate ID otomatis (angka saja) ======
function generateID($con)
{
    $query = mysqli_query($con, "SELECT id_instruktur FROM tbl_instruktur ORDER BY id_instruktur DESC LIMIT 1");
    $lastID = mysqli_fetch_array($query);
    if ($lastID) {
        $num = (int)$lastID['id_instruktur'] + 1;
    } else {
        $num = 1;
    }
    return str_pad($num, 3, "0", STR_PAD_LEFT);
}

// ====== Tambah Data ======
if (isset($_POST['simpan'])) {
    $id = generateID($con);
    $nama = htmlspecialchars($_POST['nama_instruktur']);
    $spesialisasi = htmlspecialchars($_POST['spesialisasi']);
    $no_hp = htmlspecialchars($_POST['no_hp']);
    $email = htmlspecialchars($_POST['email']);
    $catatan = htmlspecialchars($_POST['catatan'] ?? '');

    // Upload Foto
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "../../../data/admin/img/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nama_file = "foto_" . $id . "_" . time() . "." . $ext;
        $target_file = $target_dir . $nama_file;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
            $foto = $nama_file;
        } else {
            echo "<script>alert('Gagal upload foto, periksa izin folder!');</script>";
        }
    }

    // Cek duplikat email
    $cek = mysqli_prepare($con, "SELECT * FROM tbl_instruktur WHERE email = ?");
    mysqli_stmt_bind_param($cek, "s", $email);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Email instruktur sudah terdaftar!');</script>";
    } else {
        $insert = mysqli_prepare($con, "INSERT INTO tbl_instruktur 
            (id_instruktur, nama_instruktur, spesialisasi, no_hp, email, foto, catatan)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($insert, "sssssss", $id, $nama, $spesialisasi, $no_hp, $email, $foto, $catatan);

        if (mysqli_stmt_execute($insert)) {
            echo "<script>
                alert('Instruktur berhasil ditambahkan!');
                window.location='instruktur.php';
            </script>";
        } else {
            echo "<script>alert('Gagal menambahkan instruktur!');</script>";
        }
        mysqli_stmt_close($insert);
    }
    mysqli_stmt_close($cek);
}

// ====== Update Data ======
if (isset($_POST['update'])) {
    $id = $_POST['id_instruktur'];
    $nama = htmlspecialchars($_POST['nama_instruktur']);
    $spesialisasi = htmlspecialchars($_POST['spesialisasi']);
    $no_hp = htmlspecialchars($_POST['no_hp']);
    $email = htmlspecialchars($_POST['email']);
    $catatan = htmlspecialchars($_POST['catatan'] ?? '');

    // Ambil data lama
    $old = mysqli_fetch_assoc(mysqli_query($con, "SELECT foto FROM tbl_instruktur WHERE id_instruktur='$id'"));
    $foto = $old['foto'];

    // Upload baru jika ada
    if (!empty($_FILES['foto']['name'])) {
        $target_dir = "../../../data/admin/img/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nama_file = "foto_" . $id . "_" . time() . "." . $ext;
        $target_file = $target_dir . $nama_file;

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                if ($foto && file_exists($target_dir . $foto)) {
                    unlink($target_dir . $foto);
                }
                $foto = $nama_file;
            }
        }
    }

    $update = mysqli_prepare($con, "UPDATE tbl_instruktur SET 
        nama_instruktur=?, spesialisasi=?, no_hp=?, email=?, foto=?, catatan=? 
        WHERE id_instruktur=?");
    mysqli_stmt_bind_param($update, "sssssss", $nama, $spesialisasi, $no_hp, $email, $foto, $catatan, $id);

    if (mysqli_stmt_execute($update)) {
        echo "<script>
            alert('Instruktur berhasil diupdate!');
            window.location='instruktur.php';
        </script>";
    } else {
        echo "<script>alert('Gagal update instruktur!');</script>";
    }
    mysqli_stmt_close($update);
}

// ====== Hapus Data ======
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    // Hapus foto fisik
    $old = mysqli_fetch_assoc(mysqli_query($con, "SELECT foto FROM tbl_instruktur WHERE id_instruktur='$id'"));
    if ($old && $old['foto']) {
        $filePath = "../../../data/admin/img/" . $old['foto'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $delete = mysqli_prepare($con, "DELETE FROM tbl_instruktur WHERE id_instruktur=?");
    mysqli_stmt_bind_param($delete, "s", $id);

    if (mysqli_stmt_execute($delete)) {
        echo "<script>
            alert('Instruktur berhasil dihapus!');
            window.location='instruktur.php';
        </script>";
    } else {
        echo "<script>alert('Gagal menghapus instruktur!');</script>";
    }
    mysqli_stmt_close($delete);
}

// ====== Ambil Data ======
$queryInstruktur = mysqli_query($con, "SELECT * FROM tbl_instruktur ORDER BY id_instruktur DESC");
$jumlahInstruktur = mysqli_num_rows($queryInstruktur);
?>

<!-- ==== Tabel dan Modal seperti biasa ==== -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Data Instruktur</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Instruktur</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Daftar Instruktur</h3>
            </div>
            <div class="card-body">
                <button class="btn btn-outline-primary mb-3" data-toggle="modal" data-target="#modalTambah">
                    <i class="fas fa-plus"></i> Tambah Instruktur
                </button>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Spesialisasi</th>
                                <th>No. HP</th>
                                <th>Email</th>
                                <th>Foto</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($jumlahInstruktur == 0) {
                                echo "<tr><td colspan='9' class='text-center'>Belum ada data instruktur</td></tr>";
                            } else {
                                $no = 1;
                                while ($data = mysqli_fetch_array($queryInstruktur)) {
                            ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($data['id_instruktur']); ?></td>
                                        <td><?= htmlspecialchars($data['nama_instruktur']); ?></td>
                                        <td><?= htmlspecialchars($data['spesialisasi']); ?></td>
                                        <td><?= htmlspecialchars($data['no_hp']); ?></td>
                                        <td><?= htmlspecialchars($data['email']); ?></td>
                                        <td>
                                            <?php if (!empty($data['foto'])): ?>
                                                <img src="../../../data/admin/img/<?= htmlspecialchars($data['foto']); ?>" width="60" height="60" class="rounded-circle" style="object-fit:cover;">
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($data['catatan'] ?? '-'); ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEdit<?= $data['id_instruktur']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="instruktur.php?hapus=<?= $data['id_instruktur']; ?>"
                                                onclick="return confirm('Yakin hapus instruktur <?= htmlspecialchars($data['nama_instruktur']); ?>?')"
                                                class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </a>
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
</section>

<?php include '../../../view/master/footer.php'; ?>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTambahLabel">Tambah Instruktur</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Instruktur</label>
                        <input type="text" name="nama_instruktur" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Spesialisasi</label>
                        <input type="text" name="spesialisasi" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>No. HP</label>
                        <input type="text" name="no_hp" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Foto Instruktur</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3" placeholder="Opsional"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<?php
mysqli_data_seek($queryInstruktur, 0);
while ($data = mysqli_fetch_array($queryInstruktur)) {
?>
    <div class="modal fade" id="modalEdit<?= $data['id_instruktur']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Edit Instruktur</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_instruktur" value="<?= $data['id_instruktur']; ?>">
                        <div class="form-group">
                            <label>Nama Instruktur</label>
                            <input type="text" name="nama_instruktur" class="form-control" value="<?= htmlspecialchars($data['nama_instruktur']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Spesialisasi</label>
                            <input type="text" name="spesialisasi" class="form-control" value="<?= htmlspecialchars($data['spesialisasi']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>No. HP</label>
                            <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($data['no_hp']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Foto Instruktur</label><br>
                            <?php if (!empty($data['foto'])): ?>
                                <img src="../../../data/admin/img/<?= htmlspecialchars($data['foto']); ?>" width="80" class="mb-2 rounded">
                            <?php endif; ?>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah foto</small>
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="catatan" class="form-control" rows="3"><?= htmlspecialchars($data['catatan'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" name="update" class="btn btn-warning">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php } ?>

<?php include '../../../view/master/footer.php'; ?>