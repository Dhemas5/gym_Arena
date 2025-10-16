<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Menu Paket</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Beranda</a></li>
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
                        <button class="btn btn-outline-primary mb-3" data-toggle="modal" data-target="#modalTambah">
                            <i class="fas fa-plus"></i> Tambah Paket
                        </button>
                        <div class="table-responsive rounded">
                            <table class="table table-bordered table-striped" id="tabelPelatih">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width:5%">No</th>
                                        <th style="width:25%">Nama Paket</th>
                                        <th style="width:30%">Deskripsi</th>
                                        <th style="width:15%">Harga</th>
                                        <th style="width:10%">Durasi (hari)</th>
                                        <th style="width:15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $queryPaket = mysqli_query($con, "SELECT * FROM tbl_paket ORDER BY id_paket ASC");
                                    if (mysqli_num_rows($queryPaket) == 0) {
                                        echo '<tr><td colspan="6" class="text-center">Tidak ada data paket</td></tr>';
                                    } else {
                                        $no = 1;
                                        while ($data = mysqli_fetch_array($queryPaket)) {
                                    ?>
                                            <tr>
                                                <td><?= $no ?></td>
                                                <td><?= htmlspecialchars($data['nama_paket']) ?></td>
                                                <td><?= htmlspecialchars($data['deskripsi']) ?></td>
                                                <td><?= number_format($data['harga'], 0, ',', '.') ?></td>
                                                <td><?= $data['durasi_hari'] ?></td>
                                                <td class="text-center align-middle">
                                                    <div class="btn-group" style="gap:8px;">
                                                        <button class="btn btn-warning btn-sm px-3 py-2" data-toggle="modal" data-target="#modalEdit<?= $data['id_paket'] ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm px-3 py-2 btn-hapus" data-id="<?= $data['id_paket'] ?>" data-nama="<?= htmlspecialchars($data['nama_paket']) ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                    <?php $no++;
                                        }
                                    } ?>
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
        <form id="formTambah">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Paket</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Paket</label>
                        <input type="text" class="form-control" name="nama_paket" required placeholder="Masukkan nama paket">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3" placeholder="Opsional"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Harga</label>
                        <input type="number" class="form-control" name="harga" required placeholder="Masukkan harga">
                    </div>
                    <div class="form-group">
                        <label>Durasi (hari)</label>
                        <input type="number" class="form-control" name="durasi_hari" required placeholder="Durasi paket">
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

<!-- Modal Edit Paket -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formEdit">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Edit Paket</h5>
                    <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_paket" id="edit_id">
                    <div class="form-group">
                        <label>Nama Paket</label>
                        <input type="text" class="form-control" name="nama_paket" id="edit_nama" required placeholder="Masukkan nama paket">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3" placeholder="Opsional"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Harga</label>
                        <input type="number" class="form-control" name="harga" id="edit_harga" required placeholder="Masukkan harga">
                    </div>
                    <div class="form-group">
                        <label>Durasi (hari)</label>
                        <input type="number" class="form-control" name="durasi_hari" id="edit_durasi" required placeholder="Durasi paket">
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


<?php include '../../../view/master/footer.php'; ?>

<!-- Script AJAX + SweetAlert -->
<script>
    $(document).ready(function() {
        // Tambah paket
        $('#formTambah').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'proses_paket.php',
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

        // Edit paket
        $('.formEdit').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'proses_paket.php',
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

        // Hapus paket
        $('.btn-hapus').click(function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah yakin ingin menghapus paket "${nama}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'proses_paket.php',
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