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
                                        <th style="width: 15%;">Foto</th>
                                        <th style="width: 25%;">Nama Kategori</th>
                                        <th style="width: 25%;">Deskripsi</th>
                                        <th style="width: 15%;">Status</th>
                                        <th style="width: 20%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $queryKategori = mysqli_query($con, "SELECT * FROM tbl_kategori ORDER BY id_kategori ASC");
                                    if (mysqli_num_rows($queryKategori) == 0) {
                                        echo '<tr><td colspan="6" class="text-center">Tidak ada data kategori</td></tr>';
                                    } else {
                                        $no = 1;
                                        while ($data = mysqli_fetch_array($queryKategori)) {
                                    ?>
                                            <tr>
                                                <td><?= $no ?></td>
                                                <td class="text-center">
                                                    <?php if (!empty($data['foto'])): ?>
                                                        <img src="../../../data/admin/img/<?= $data['foto'] ?>"
                                                            alt="Foto Kategori"
                                                            style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                                                    <?php else: ?>
                                                        <img src="../../../data/admin/img/default.jpg"
                                                            alt="Default"
                                                            style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($data['nama_kategori']) ?></td>
                                                <td><?= htmlspecialchars($data['deskripsi'] ?? '-') ?></td>
                                                <td class="text-center align-middle">
                                                    <button class="btn btn-sm btn-favorite <?= $data['kelas_populer'] ? 'active' : '' ?>"
                                                        data-id="<?= $data['id_kategori'] ?>"
                                                        data-status="<?= $data['kelas_populer'] ?>">
                                                        <i class="fas <?= $data['kelas_populer'] ? 'fa-star text-warning' : 'fa-star text-secondary' ?>"></i>
                                                        <span class="favorite-text"><?= $data['kelas_populer'] ? 'Populer' : 'Biasa' ?></span>
                                                    </button>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <div class="btn-group" style="gap:8px;">
                                                        <button class="btn btn-warning btn-sm px-3 py-2 btn-edit"
                                                            data-id="<?= $data['id_kategori'] ?>"
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
        <form id="formTambah" enctype="multipart/form-data">
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
                    <div class="form-group">
                        <label>Foto Kategori</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="fotoTambah" name="foto" accept="image/*">
                            <label class="custom-file-label" for="fotoTambah">Pilih file foto...</label>
                        </div>
                        <small class="form-text text-muted">Format: JPG, PNG, JPEG. Maksimal 5MB</small>
                    </div>
                    <div class="form-group text-center">
                        <img id="previewTambah" src="../../../data/admin/img/default.jpg"
                            alt="Preview Foto"
                            style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 5px; margin-top: 10px;">
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
// Query terpisah untuk modal edit agar semua data bisa diakses
$queryModal = mysqli_query($con, "SELECT * FROM tbl_kategori ORDER BY id_kategori ASC");
while ($data = mysqli_fetch_array($queryModal)) {
?>
    <div class="modal fade" id="modalEdit<?= $data['id_kategori'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="formEdit" data-id="<?= $data['id_kategori'] ?>" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">Edit Kategori</h5>
                        <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_kategori" value="<?= $data['id_kategori'] ?>">
                        <input type="hidden" name="foto_lama" value="<?= $data['foto'] ?>">
                        <div class="form-group">
                            <label>Nama Kategori</label>
                            <input type="text" class="form-control" name="nama_kategori" value="<?= htmlspecialchars($data['nama_kategori']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3"><?= htmlspecialchars($data['deskripsi'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Foto Kategori</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="fotoEdit<?= $data['id_kategori'] ?>" name="foto" accept="image/*">
                                <label class="custom-file-label" for="fotoEdit<?= $data['id_kategori'] ?>">
                                    <?= !empty($data['foto']) ? $data['foto'] : 'Pilih file foto...' ?>
                                </label>
                            </div>
                            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah foto</small>
                        </div>
                        <div class="form-group text-center">
                            <img id="previewEdit<?= $data['id_kategori'] ?>"
                                src="<?= !empty($data['foto']) ? '../../../data/admin/img/' . $data['foto'] : '../../../data/admin/img/default.jpg' ?>"
                                alt="Preview Foto"
                                style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 5px; margin-top: 10px;">
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

<!-- CSS untuk tombol favorit -->
<style>
    .btn-favorite {
        border: 1px solid #ddd;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 20px;
        width: 100px;
        cursor: pointer;
    }

    .btn-favorite:hover {
        background-color: #e9ecef;
        transform: scale(1.05);
    }

    .btn-favorite.active {
        background-color: #fff3cd;
        border-color: #ffc107;
    }

    .btn-favorite .favorite-text {
        font-size: 0.8rem;
        font-weight: 500;
    }

    /* DataTables Customization */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5em 1em !important;
        margin-left: 2px !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.25rem !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #007bff !important;
        color: white !important;
        border-color: #007bff !important;
    }
</style>

<!-- Script AJAX + SweetAlert + DataTables -->
<script>
    $(document).ready(function() {
        // Inisialisasi DataTables
        $('#tabelKategori').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            },
            "responsive": true,
            "autoWidth": false
        });

        // Preview image untuk tambah
        $('#fotoTambah').change(function(e) {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewTambah').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
                $('.custom-file-label', $(this).parent()).text(file.name);
            }
        });

        // Preview image untuk edit
        $(document).on('change', 'input[id^="fotoEdit"]', function(e) {
            const file = this.files[0];
            const modalId = $(this).attr('id').replace('fotoEdit', '');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewEdit' + modalId).attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
                $('.custom-file-label', $(this).parent()).text(file.name);
            }
        });

        // Tambah kategori dengan FormData
        $('#formTambah').submit(function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'simpan');

            $.ajax({
                url: 'proses_kategori.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    if (res.status == 'success') {
                        Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error!', 'Terjadi kesalahan: ' + error, 'error');
                }
            });
        });

        // Edit kategori dengan FormData
        $('.formEdit').submit(function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update');

            $.ajax({
                url: 'proses_kategori.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    if (res.status == 'success') {
                        Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error!', 'Terjadi kesalahan: ' + error, 'error');
                }
            });
        });

        // Hapus kategori
        $(document).on('click', '.btn-hapus', function(e) {
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

        // Toggle status favorit
        $(document).on('click', '.btn-favorite', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const currentStatus = $(this).data('status');
            const newStatus = currentStatus == 1 ? 0 : 1;

            $.ajax({
                url: 'proses_kategori.php',
                type: 'POST',
                data: {
                    action: 'toggle_favorite',
                    id: id,
                    status: newStatus
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status == 'success') {
                        // Update tampilan tombol
                        const $btn = $('.btn-favorite[data-id="' + id + '"]');
                        $btn.data('status', newStatus);

                        if (newStatus == 1) {
                            $btn.addClass('active');
                            $btn.find('i').removeClass('text-secondary').addClass('text-warning');
                            $btn.find('.favorite-text').text('Populer');
                        } else {
                            $btn.removeClass('active');
                            $btn.find('i').removeClass('text-warning').addClass('text-secondary');
                            $btn.find('.favorite-text').text('Biasa');
                        }

                        Swal.fire('Berhasil!', res.message, 'success');
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error!', 'Terjadi kesalahan: ' + error, 'error');
                }
            });
        });
    });
</script>