<?php
// Koneksi Database
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'db_gym';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Proses CRUD
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// CREATE - Tambah Data
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_instruktur'];
    $spesialisasi = $_POST['spesialisasi'];
    $no_hp = $_POST['no_hp'];
    $email = $_POST['email'];
    $catatan = $_POST['catatan'];
    
    // Upload foto
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "../../../uploads/instruktur/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $foto = basename($_FILES["foto"]["name"]);
        $target_file = $target_dir . time() . "_" . $foto;
        move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);
        $foto = time() . "_" . $foto;
    }
    
    $sql = "INSERT INTO tbl_instruktur (nama_instruktur, spesialisasi, foto, catatan, no_hp, email) 
            VALUES ('$nama', '$spesialisasi', '$foto', '$catatan', '$no_hp', '$email')";
    
    if ($conn->query($sql)) {
        header("Location: instruktur.php?msg=success");
        exit();
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

// UPDATE - Edit Data
if (isset($_POST['edit'])) {
    $id = $_POST['id_instruktur'];
    $nama = $_POST['nama_instruktur'];
    $spesialisasi = $_POST['spesialisasi'];
    $no_hp = $_POST['no_hp'];
    $email = $_POST['email'];
    $catatan = $_POST['catatan'];
    
    // Cek upload foto baru
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "../../../uploads/instruktur/";
        $foto = basename($_FILES["foto"]["name"]);
        $target_file = $target_dir . time() . "_" . $foto;
        move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);
        $foto = time() . "_" . $foto;
        
        $sql = "UPDATE tbl_instruktur SET 
                nama_instruktur='$nama', 
                spesialisasi='$spesialisasi', 
                foto='$foto',
                catatan='$catatan',
                no_hp='$no_hp',
                email='$email'
                WHERE id_instruktur=$id";
    } else {
        $sql = "UPDATE tbl_instruktur SET 
                nama_instruktur='$nama', 
                spesialisasi='$spesialisasi',
                catatan='$catatan',
                no_hp='$no_hp',
                email='$email'
                WHERE id_instruktur=$id";
    }
    
    if ($conn->query($sql)) {
        header("Location: instruktur.php?msg=updated");
        exit();
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

// DELETE - Hapus Data
if ($action == 'delete' && $id) {
    $sql = "DELETE FROM tbl_instruktur WHERE id_instruktur=$id";
    if ($conn->query($sql)) {
        header("Location: instruktur.php?msg=deleted");
        exit();
    }
}

// READ - Ambil data untuk edit
$edit_data = null;
if ($action == 'edit' && $id) {
    $result = $conn->query("SELECT * FROM tbl_instruktur WHERE id_instruktur=$id");
    $edit_data = $result->fetch_assoc();
}

// READ - Ambil semua data
$sql = "SELECT * FROM tbl_instruktur ORDER BY id_instruktur DESC";
$result = $conn->query($sql);
?>

<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Menu Pelatih</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Pelatih</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Alert Notifikasi -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['msg'] == 'success') echo " Data berhasil ditambahkan!";
                if ($_GET['msg'] == 'updated') echo " Data berhasil diupdate!";
                if ($_GET['msg'] == 'deleted') echo " Data berhasil dihapus!";
                ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <!-- Tombol Tambah -->
                <div class="mb-3">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
                        <i class="fas fa-plus"></i> Tambah Instruktur
                    </button>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Data Pelatih</h3>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive rounded">
                            <table id="tabelPelatih" class="table table-bordered table-striped">
                                <thead class="bg-primary">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 10%;">Foto</th>
                                        <th style="width: 15%;">Nama</th>
                                        <th>Bio</th>
                                        <th style="width: 12%;">No HP</th>
                                        <th style="width: 15%;">Email</th>
                                        <th style="width: 15%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while ($row = $result->fetch_assoc()): 
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($row['foto'])): ?>
                                                <img src="../../../uploads/instruktur/<?php echo $row['foto']; ?>" 
                                                     class="rounded-circle" 
                                                     style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #ddd;"
                                                     alt="Foto">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $row['nama_instruktur']; ?></td>
                                        <td><?php echo $row['spesialisasi']; ?></td>
                                        <td><?php echo $row['no_hp'] ?? '-'; ?></td>
                                        <td><?php echo $row['email'] ?? '-'; ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                    data-toggle="modal" 
                                                    data-target="#modalEdit<?php echo $row['id_instruktur']; ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <a href="?action=delete&id=<?php echo $row['id_instruktur']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="modalEdit<?php echo $row['id_instruktur']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-warning">
                                                    <h5 class="modal-title">Edit Data Instruktur</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <form method="POST" enctype="multipart/form-data">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id_instruktur" value="<?php echo $row['id_instruktur']; ?>">
                                                        
                                                        <div class="form-group">
                                                            <label>Nama Instruktur *</label>
                                                            <input type="text" name="nama_instruktur" class="form-control" 
                                                                   value="<?php echo $row['nama_instruktur']; ?>" required>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label>Spesialisasi / Bio *</label>
                                                            <input type="text" name="spesialisasi" class="form-control" 
                                                                   value="<?php echo $row['spesialisasi']; ?>" required>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>No HP</label>
                                                                    <input type="text" name="no_hp" class="form-control" 
                                                                           value="<?php echo $row['no_hp']; ?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>Email</label>
                                                                    <input type="email" name="email" class="form-control" 
                                                                           value="<?php echo $row['email']; ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label>Foto</label>
                                                            <input type="file" name="foto" class="form-control" accept="image/*">
                                                            <?php if (!empty($row['foto'])): ?>
                                                                <small class="text-muted">Foto saat ini: <?php echo $row['foto']; ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label>Catatan</label>
                                                            <textarea name="catatan" class="form-control" rows="3"><?php echo $row['catatan']; ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                        <button type="submit" name="edit" class="btn btn-warning">
                                                            <i class="fas fa-save"></i> Update Data
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>

                                    <?php if ($result->num_rows == 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada data instruktur</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Instruktur Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Instruktur *</label>
                        <input type="text" name="nama_instruktur" class="form-control" 
                               placeholder="Masukkan nama instruktur" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Spesialisasi / Bio *</label>
                        <input type="text" name="spesialisasi" class="form-control" 
                               placeholder="contoh: Pelatih yoga, Pelatih fitness" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>No HP</label>
                                <input type="text" name="no_hp" class="form-control" 
                                       placeholder="08123456789">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" 
                                       placeholder="email@example.com">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Foto</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG, JPEG (Max 2MB)</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3" 
                                  placeholder="Catatan tambahan (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tabelPelatih').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        },
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
    });
});
</script>

<?php include '../../../view/master/footer.php'; ?>
<?php $conn->close(); ?>