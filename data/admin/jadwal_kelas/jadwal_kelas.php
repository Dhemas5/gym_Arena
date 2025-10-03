<?php 
require "../../../setting/session.php";
checkSession("admin"); 
include '../../../view/master/header.php'; 
include '../../../view/master/sidebar.php'; 
require "../../../setting/koneksi.php"; 




// TAMBAH JADWAL 
if (isset($_POST['simpan'])) {
    $kategori   = $_POST['id_kategori'];
    $instruktur = $_POST['id_instruktur'];
    $tanggal    = $_POST['tanggal'];
    $mulai      = $_POST['jam_mulai'];
    $selesai    = $_POST['jam_selesai'];

    $insert = mysqli_query($con, "INSERT INTO tbl_jadwal_kelas (id_kategori,id_instruktur,tanggal,jam_mulai,jam_selesai) 
        VALUES('$kategori','$instruktur','$tanggal','$mulai','$selesai')");

    if ($insert) {
        echo "<script>alert('Jadwal berhasil ditambahkan!');window.location='jadwal.php';</script>";
    } else {
        die("Insert Error: " . mysqli_error($con));
    }
}

// UPDATE JADWAL 
if (isset($_POST['update'])) {
    $id        = $_POST['id_jadwal'];
    $kategori  = $_POST['id_kategori'];
    $instruktur= $_POST['id_instruktur'];
    $tanggal   = $_POST['tanggal'];
    $mulai     = $_POST['jam_mulai'];
    $selesai   = $_POST['jam_selesai'];

    $update = mysqli_query($con, "UPDATE tbl_jadwal_kelas 
        SET id_kategori='$kategori', id_instruktur='$instruktur', tanggal='$tanggal', jam_mulai='$mulai', jam_selesai='$selesai' 
        WHERE id_jadwal='$id'");

    if ($update) {
        echo "<script>alert('Jadwal berhasil diupdate!');window.location='jadwal.php';</script>";
    } else {
        die("Update Error: " . mysqli_error($con));
    }
}

// HAPUS JADWAL 
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $delete = mysqli_query($con, "DELETE FROM tbl_jadwal_kelas WHERE id_jadwal='$id'");
    if ($delete) {
        echo "<script>alert('Jadwal berhasil dihapus!');window.location='jadwal.php';</script>";
    } else {
        die("Delete Error: " . mysqli_error($con));
    }
}

// QUERY DATA 
$query = mysqli_query($con, "
    SELECT j.*, k.nama_kategori, i.nama_instruktur 
    FROM tbl_jadwal_kelas j
    JOIN tbl_kategori k ON j.id_kategori=k.id_kategori
    JOIN tbl_instruktur i ON j.id_instruktur=i.id_instruktur
    ORDER BY j.tanggal ASC, j.jam_mulai ASC
") or die("Select Error: " . mysqli_error($con));
?>

<style>
/* Tombol gradasi */
.btn-gradient {
    border: none;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: 0.3s;
}
.btn-edit {
    background: linear-gradient(45deg, #ff5f9e, #ff8fb1);
}
.btn-hapus {
    background: linear-gradient(45deg, #ff9966, #ffcc66);
}
.btn-gradient:hover {
    opacity: 0.85;
}
/* Ratakan tombol di tengah */
.td-aksi {
    display: flex;
    justify-content: center;
    gap: 10px;
}
</style>

<section class="content-header">
    <h1>Jadwal Kelas</h1>
</section>

<section class="content">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">Data Jadwal Kelas</h3>
        </div>
        <div class="card-body">
            <button class="btn btn-outline-primary" data-toggle="modal" data-target="#modalTambah">
                <i class="fas fa-plus"></i> Tambah
            </button>
            <table class="table table-bordered table-striped mt-2">
                <thead class="bg-primary">
                    <tr>
                        <th>No</th>
                        <th>Kategori</th>
                        <th>Instruktur</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no=1;
                while ($row=mysqli_fetch_assoc($query)) {
                ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= $row['nama_kategori']; ?></td>
                        <td><?= $row['nama_instruktur']; ?></td>
                        <td><?= $row['tanggal']; ?></td>
                        <td><?= $row['jam_mulai']." - ".$row['jam_selesai']; ?></td>
                        <td class="td-aksi">
                            <!-- Tombol Edit -->
                            <button class="btn-gradient btn-edit btn-sm btnEdit"
                                data-id="<?= $row['id_jadwal']; ?>"
                                data-kategori="<?= $row['id_kategori']; ?>"
                                data-instruktur="<?= $row['id_instruktur']; ?>"
                                data-tanggal="<?= $row['tanggal']; ?>"
                                data-mulai="<?= $row['jam_mulai']; ?>"
                                data-selesai="<?= $row['jam_selesai']; ?>"
                                data-toggle="modal" data-target="#modalEdit">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <!-- Tombol Hapus -->
                            <a href="jadwal.php?hapus=<?= $row['id_jadwal']; ?>" class="btn-gradient btn-hapus btn-sm" onclick="return confirm('Hapus data ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah">
  <div class="modal-dialog">
    <form method="POST">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Tambah Jadwal Kelas</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body bg-dark text-white">
          <div class="form-group">
            <label>Kategori</label>
            <select name="id_kategori" class="form-control" required>
              <?php
              $kat=mysqli_query($con,"SELECT * FROM tbl_kategori");
              while($k=mysqli_fetch_assoc($kat)){
                  echo "<option value='".$k['id_kategori']."'>".$k['nama_kategori']."</option>";
              }
              ?>
            </select>
          </div>
          <div class="form-group">
            <label>Instruktur</label>
            <select name="id_instruktur" class="form-control" required>
              <?php
              $ins=mysqli_query($con,"SELECT * FROM tbl_instruktur");
              while($i=mysqli_fetch_assoc($ins)){
                  echo "<option value='".$i['id_instruktur']."'>".$i['nama_instruktur']."</option>";
              }
              ?>
            </select>
          </div>
          <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Jam Mulai</label>
            <input type="time" name="jam_mulai" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Jam Selesai</label>
            <input type="time" name="jam_selesai" class="form-control" required>
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

<!-- Modal Edit (hanya 1) -->
<div class="modal fade" id="modalEdit">
  <div class="modal-dialog">
    <form method="POST">
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">Edit Jadwal</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body bg-dark text-white">
          <input type="hidden" name="id_jadwal" id="edit_id">

          <div class="form-group">
            <label>Kategori</label>
            <select name="id_kategori" id="edit_kategori" class="form-control" required>
              <?php
              $kat=mysqli_query($con,"SELECT * FROM tbl_kategori");
              while($k=mysqli_fetch_assoc($kat)){
                  echo "<option value='".$k['id_kategori']."'>".$k['nama_kategori']."</option>";
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>Instruktur</label>
            <select name="id_instruktur" id="edit_instruktur" class="form-control" required>
              <?php
              $ins=mysqli_query($con,"SELECT * FROM tbl_instruktur");
              while($i=mysqli_fetch_assoc($ins)){
                  echo "<option value='".$i['id_instruktur']."'>".$i['nama_instruktur']."</option>";
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" id="edit_tanggal" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Jam Mulai</label>
            <input type="time" name="jam_mulai" id="edit_mulai" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Jam Selesai</label>
            <input type="time" name="jam_selesai" id="edit_selesai" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer bg-dark">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" name="update" class="btn btn-warning">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const editButtons = document.querySelectorAll(".btnEdit");
  editButtons.forEach(btn => {
    btn.addEventListener("click", function() {
      document.getElementById("edit_id").value = this.dataset.id;
      document.getElementById("edit_kategori").value = this.dataset.kategori;
      document.getElementById("edit_instruktur").value = this.dataset.instruktur;
      document.getElementById("edit_tanggal").value = this.dataset.tanggal;
      document.getElementById("edit_mulai").value = this.dataset.mulai;
      document.getElementById("edit_selesai").value = this.dataset.selesai;
    });
  });
});
</script>

<?php include '../../../view/master/footer.php'; ?>
