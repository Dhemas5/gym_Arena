<?php
require "../../../setting/session.php";
checkSession("admin");
include '../../../view/master/header.php';
include '../../../view/master/sidebar.php';
require "../../../setting/koneksi.php";

// ========== PROSES TAMBAH ==========
if (isset($_POST['simpan'])) {
  $kategori   = $_POST['id_kategori'];
  $instruktur = $_POST['id_instruktur'];
  $tanggal    = $_POST['tanggal'];
  $mulai      = $_POST['jam_mulai'];
  $selesai    = $_POST['jam_selesai'];

  $insert = mysqli_query($con, "INSERT INTO tbl_jadwal_kelas (id_kategori,id_instruktur,tanggal,jam_mulai,jam_selesai) 
        VALUES('$kategori','$instruktur','$tanggal','$mulai','$selesai')");

  if ($insert) {
    echo "<script>
            alert('Jadwal berhasil ditambahkan!');
            window.location='jadwal.php';
        </script>";
  } else {
    echo "<script>alert('Gagal menambahkan jadwal! Error: " . mysqli_error($con) . "');</script>";
  }
}

// ========== PROSES UPDATE ==========
if (isset($_POST['update'])) {
  $id         = $_POST['id_jadwal'];
  $kategori   = $_POST['id_kategori'];
  $instruktur = $_POST['id_instruktur'];
  $tanggal    = $_POST['tanggal'];
  $mulai      = $_POST['jam_mulai'];
  $selesai    = $_POST['jam_selesai'];

  $update = mysqli_query($con, "UPDATE tbl_jadwal_kelas 
        SET id_kategori='$kategori', id_instruktur='$instruktur', tanggal='$tanggal', jam_mulai='$mulai', jam_selesai='$selesai' 
        WHERE id_jadwal='$id'");

  if ($update) {
    echo "<script>
            alert('Jadwal berhasil diupdate!');
            window.location='jadwal.php';
        </script>";
  } else {
    echo "<script>alert('Gagal update jadwal! Error: " . mysqli_error($con) . "');</script>";
  }
}

// ========== PROSES HAPUS ==========
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  $delete = mysqli_query($con, "DELETE FROM tbl_jadwal_kelas WHERE id_jadwal='$id'");
  if ($delete) {
    echo "<script>
            alert('Jadwal berhasil dihapus!');
            window.location='jadwal.php';
        </script>";
  } else {
    echo "<script>alert('Gagal hapus jadwal! Error: " . mysqli_error($con) . "');</script>";
  }
}

// ========== QUERY DATA ==========
$query = mysqli_query($con, "
    SELECT j.*, k.nama_kategori, i.nama_instruktur 
    FROM tbl_jadwal_kelas j
    JOIN tbl_kategori k ON j.id_kategori=k.id_kategori
    JOIN tbl_instruktur i ON j.id_instruktur=i.id_instruktur
    ORDER BY j.tanggal ASC, j.jam_mulai ASC
");
$jumlah = mysqli_num_rows($query);
?>

<!-- Content Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Jadwal Kelas</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item active">Jadwal Kelas</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main Content -->
<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h3 class="card-title">Data Jadwal Kelas</h3>
      </div>
      <div class="card-body">
        <button class="btn btn-outline-primary mb-3" data-toggle="modal" data-target="#modalTambah">
          <i class="fas fa-plus"></i> Tambah Jadwal
        </button>

        <div class="table-responsive rounded">
          <table id="tabelPelatih" class="table table-bordered table-striped">
            <thead class="bg-primary text-white">
              <tr>
                <th style="width:5%;">No</th>
                <th style="width:20%;">Kategori</th>
                <th style="width:20%;">Instruktur</th>
                <th style="width:20%;">Tanggal</th>
                <th style="width:20%;">Jam</th>
                <th style="width:15%;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($jumlah == 0) { ?>
                <tr>
                  <td colspan="6" class="text-center">Tidak ada data jadwal</td>
                </tr>
                <?php } else {
                $no = 1;
                while ($row = mysqli_fetch_assoc($query)) { ?>
                  <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['nama_kategori']); ?></td>
                    <td><?= htmlspecialchars($row['nama_instruktur']); ?></td>
                    <td><?= htmlspecialchars($row['tanggal']); ?></td>
                    <td><?= htmlspecialchars($row['jam_mulai'] . ' - ' . $row['jam_selesai']); ?></td>
                    <td class="text-center align-middle">
                      <div class="btn-group" role="group" style="gap:8px;">
                        <button class="btn btn-warning btn-sm px-3 py-2 btnEdit"
                          data-toggle="modal"
                          data-target="#modalEdit"
                          data-id="<?= $row['id_jadwal']; ?>"
                          data-kategori="<?= $row['id_kategori']; ?>"
                          data-instruktur="<?= $row['id_instruktur']; ?>"
                          data-tanggal="<?= $row['tanggal']; ?>"
                          data-mulai="<?= $row['jam_mulai']; ?>"
                          data-selesai="<?= $row['jam_selesai']; ?>">
                          <i class="fas fa-edit"></i>
                        </button>
                        <a href="jadwal.php?hapus=<?= $row['id_jadwal']; ?>"
                          onclick="return confirm('Yakin hapus jadwal ini?')"
                          class="btn btn-danger btn-sm px-3 py-2">
                          <i class="fas fa-trash"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
              <?php }
              } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Modal Tambah Jadwal -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalTambahLabel">Tambah Jadwal Kelas</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Kategori</label>
            <select name="id_kategori" class="form-control" required>
              <option value="">-- Pilih Kategori --</option>
              <?php
              $kat = mysqli_query($con, "SELECT * FROM tbl_kategori");
              while ($k = mysqli_fetch_assoc($kat)) {
                echo "<option value='{$k['id_kategori']}'>{$k['nama_kategori']}</option>";
              }
              ?>
            </select>
          </div>
          <div class="form-group">
            <label>Instruktur</label>
            <select name="id_instruktur" class="form-control" required>
              <option value="">-- Pilih Instruktur --</option>
              <?php
              $ins = mysqli_query($con, "SELECT * FROM tbl_instruktur");
              while ($i = mysqli_fetch_assoc($ins)) {
                echo "<option value='{$i['id_instruktur']}'>{$i['nama_instruktur']}</option>";
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

<!-- Modal Edit Jadwal -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST">
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="modalEditLabel">Edit Jadwal</h5>
          <button type="button" class="close text-dark" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_jadwal" id="edit_id">
          <div class="form-group">
            <label>Kategori</label>
            <select name="id_kategori" id="edit_kategori" class="form-control" required>
              <?php
              $kat = mysqli_query($con, "SELECT * FROM tbl_kategori");
              while ($k = mysqli_fetch_assoc($kat)) {
                echo "<option value='{$k['id_kategori']}'>{$k['nama_kategori']}</option>";
              }
              ?>
            </select>
          </div>
          <div class="form-group">
            <label>Instruktur</label>
            <select name="id_instruktur" id="edit_instruktur" class="form-control" required>
              <?php
              $ins = mysqli_query($con, "SELECT * FROM tbl_instruktur");
              while ($i = mysqli_fetch_assoc($ins)) {
                echo "<option value='{$i['id_instruktur']}'>{$i['nama_instruktur']}</option>";
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