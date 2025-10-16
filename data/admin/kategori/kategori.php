<?php
require "../../../setting/session.php";
checkSession("admin"); // hanya admin
require "../../../setting/koneksi.php";
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Menu Kategori</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Beranda</a></li>
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
                        <button class="btn btn-outline-primary mb-3" data-toggle="modal" data-target="#modalTambah">
                            <i class="fas fa-plus"></i> Tambah Kategori
                        </button>
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
                                    $queryKategori = mysqli_query($con, "SELECT * FROM tbl_kategori ORDER BY id_kategori ASC");
                                    if (mysqli_num_rows($queryKategori) == 0) {
                                        echo '<tr><td colspan="4" class="text-center">Tidak ada data kategori</td></tr>';
                                    } else {
                                        $no = 1;
                                        while ($data = mysqli_fetch_array($queryKategori)) {
                                    ?>
                                            <tr>
                                                <td><?= $no ?></td>
                                                <td><?= htmlspecialchars($data['nama_kategori']) ?></td>
                                                <td><?= htmlspecialchars($data['deskripsi'] ?? '-') ?></td>
                                                <td class="text-center align-middle">
                                                    <div class="btn-group" style="gap:8px;">
                                                        <button class="btn btn-warning btn-sm px-3 py-2"
                                                            data-toggle="modal"
                                                            data-target="#modalEdit<?= $data['id_kategori'] ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm px-3 py-2 btn-hapus"
                                                            data-id="<?= $data['id_kategori'] ?>"
                                                            data-nama="<?= htmlspecialchars($data['nama_kategori']) ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formTambah">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Kategori</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Kategori</label>
                        <input type="text" class="form-control" name="nama_kategori" required placeholder="Masukkan nama kategori">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3" placeholder="Opsional"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<?php
mysqli_data_seek($queryKategori, 0);
while ($data = mysqli_fetch_array($queryKategori)) {
?>
    <div class="modal fade" id="modalEdit<?= $data['id_kategori'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="formEdit" data-id="<?= $data['id_kategori'] ?>">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">Edit Kategori</h5>
                        <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_kategori" value="<?= $data['id_kategori'] ?>">
                        <div class="form-group">
                            <label>Nama Kategori</label>
                            <input type="text" class="form-control" name="nama_kategori" value="<?= htmlspecialchars($data['nama_kategori']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3"><?= htmlspecialchars($data['deskripsi'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Batal</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-edit"></i> Perbarui</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php } ?>

<?php include '../../../view/master/footer.php'; ?>

<!-- Script AJAX + SweetAlert -->
<script>
    $(document).ready(function() {
        // Tambah kategori
        $('#formTambah').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'proses_kategori.php',
                type: 'POST',
                data: $(this).serialize() + '&action=simpan',
                dataType: 'json',
                success: function(res) {
                    if (res.status == 'success') {
                        Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                }
            });
        });

        // Edit kategori
        $('.formEdit').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'proses_kategori.php',
                type: 'POST',
                data: $(this).serialize() + '&action=update',
                dataType: 'json',
                success: function(res) {
                    if (res.status == 'success') {
                        Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                }
            });
        });

        // Hapus kategori
        $('.btn-hapus').click(function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah yakin ingin menghapus kategori "${nama}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'proses_kategori.php',
                        type: 'GET',
                        data: {
                            action: 'hapus',
                            id: id
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == 'success') {
                                Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                            }
                        }
                    });
                }
            });
        });
    });
</script>