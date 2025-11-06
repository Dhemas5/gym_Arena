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
                                        <th style="width:20%">Nama Paket</th>
                                        <th style="width:15%">Kategori</th>
                                        <th style="width:15%">Tipe Paket</th>
                                        <th style="width:12%">Harga</th>
                                        <th style="width:18%">Deskripsi</th>
                                        <th style="width:15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "
                                        SELECT p.*, k.nama_kategori 
                                        FROM tbl_paket p 
                                        LEFT JOIN tbl_kategori k ON p.id_kategori = k.id_kategori 
                                        ORDER BY p.id_paket ASC
                                    ";
                                    $result = mysqli_query($con, $query);
                                    if (mysqli_num_rows($result) == 0) {
                                        echo '<tr><td colspan="7" class="text-center">Tidak ada data paket</td></tr>';
                                    } else {
                                        $no = 1;
                                        while ($data = mysqli_fetch_array($result)) {
                                            $durasi = $data['durasi_hari'];
                                            if ($durasi == 1) $tipe = '1 Hari';
                                            elseif ($durasi == 30) $tipe = '1 Bulan';
                                            elseif ($durasi == 90) $tipe = '3 Bulan';
                                            elseif ($durasi == 180) $tipe = '6 Bulan';
                                            elseif ($durasi == 365) $tipe = '1 Tahun';
                                            else $tipe = $durasi . ' hari';
                                    ?>
                                            <tr>
                                                <td><?= $no ?></td>
                                                <td><?= htmlspecialchars($data['nama_paket']) ?></td>
                                                <td><?= htmlspecialchars($data['nama_kategori'] ?? 'Tanpa Kategori') ?></td>
                                                <td><span class="badge badge-info"><?= $tipe ?></span></td>
                                                <td>Rp <?= number_format($data['harga'], 0, ',', '.') ?></td>
                                                <td><?= htmlspecialchars($data['deskripsi']) ?: '-' ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-warning btn-sm edit-btn mr-1"
                                                        data-id="<?= $data['id_paket'] ?>"
                                                        data-nama="<?= htmlspecialchars($data['nama_paket']) ?>"
                                                        data-kategori="<?= $data['id_kategori'] ?>"
                                                        data-deskripsi="<?= htmlspecialchars($data['deskripsi']) ?>"
                                                        data-harga="<?= $data['harga'] ?>"
                                                        data-durasi="<?= $data['durasi_hari'] ?>"
                                                        data-toggle="modal" data-target="#modalEdit"
                                                        title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm btn-hapus"
                                                        data-id="<?= $data['id_paket'] ?>"
                                                        data-nama="<?= htmlspecialchars($data['nama_paket']) ?>"
                                                        title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
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

<!-- Modal Tambah Paket -->
<div class="modal fade" id="modalTambah">
    <div class="modal-dialog">
        <form id="formTambah">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Paket Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nama Paket <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_paket" required
                            placeholder="Contoh: Paket Premium / Trial Harian">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-control" name="id_kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php
                            $kategori_query = mysqli_query($con, "SELECT * FROM tbl_kategori ORDER BY nama_kategori");
                            while ($k = mysqli_fetch_array($kategori_query)) {
                                echo "<option value='{$k['id_kategori']}'>" . htmlspecialchars($k['nama_kategori']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipe Paket <span class="text-danger">*</span></label>
                        <select class="form-control" name="tipe_paket" id="tipe_paket" required onchange="hitungDurasi()">
                            <option value="">-- Pilih Tipe Paket --</option>
                            <option value="1">1 Hari</option>
                            <option value="30">1 Bulan</option>
                            <option value="90">3 Bulan</option>
                            <option value="180">6 Bulan</option>
                            <option value="365">1 Tahun</option>
                        </select>
                        <input type="hidden" name="durasi_hari" id="durasi_hari">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="harga" required
                            placeholder="Contoh: 25000" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3"
                            placeholder="Opsional: Deskripsi singkat tentang paket ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Paket
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Paket -->
<div class="modal fade" id="modalEdit">
    <div class="modal-dialog">
        <form id="formEdit">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Edit Paket</h5>
                    <button type="button" class="close text-dark" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_paket" id="edit_id">
                    <div class="form-group">
                        <label class="form-label">Nama Paket <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_paket" id="edit_nama" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-control" name="id_kategori" id="edit_kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php
                            mysqli_data_seek($kategori_query, 0);
                            while ($k = mysqli_fetch_array($kategori_query)) {
                                echo "<option value='{$k['id_kategori']}'>" . htmlspecialchars($k['nama_kategori']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipe Paket <span class="text-danger">*</span></label>
                        <select class="form-control" name="tipe_paket" id="tipe_paket_edit" required onchange="setDurasiEdit(this.value)">
                            <option value="">-- Pilih Tipe Paket --</option>
                            <option value="1">1 Hari</option>
                            <option value="30">1 Bulan</option>
                            <option value="90">3 Bulan</option>
                            <option value="180">6 Bulan</option>
                            <option value="365">1 Tahun</option>
                        </select>
                        <input type="hidden" name="durasi_hari" id="durasi_hari_edit">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="harga" id="edit_harga" required min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Update Paket
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../../../view/master/footer.php'; ?>

<script>
    // Fungsi untuk hitung durasi otomatis (Tambah)
    function hitungDurasi() {
        const tipe = document.getElementById('tipe_paket').value;
        document.getElementById('durasi_hari').value = tipe || '';
    }

    // Fungsi untuk hitung durasi otomatis (Edit)
    function setDurasiEdit(tipe) {
        document.getElementById('durasi_hari_edit').value = tipe || '';
    }

    $(document).ready(function() {
        // Edit: Isi modal dengan data
        $(document).on('click', '.edit-btn', function() {
            const durasi = $(this).data('durasi');

            $('#edit_id').val($(this).data('id'));
            $('#edit_nama').val($(this).data('nama'));
            $('#edit_kategori').val($(this).data('kategori'));
            $('#edit_deskripsi').val($(this).data('deskripsi'));
            $('#edit_harga').val($(this).data('harga'));
            $('#tipe_paket_edit').val(durasi);
            $('#durasi_hari_edit').val(durasi);

            $('#modalEdit').modal('show');
        });

        // TAMBAH PAKET
        $('#formTambah').submit(function(e) {
            e.preventDefault();
            if (!$('#tipe_paket').val()) {
                Swal.fire('Error!', 'Pilih tipe paket terlebih dahulu!', 'error');
                return false;
            }
            $.ajax({
                url: 'proses_paket.php',
                type: 'POST',
                data: $(this).serialize() + '&action=simpan',
                dataType: 'json',
                beforeSend: function() {
                    $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                },
                success: function(res) {
                    if (res.status === 'success') {
                        Swal.fire('Berhasil!', res.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                complete: function() {
                    $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Paket');
                }
            });
        });

        // UPDATE PAKET
        $('#formEdit').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'proses_paket.php',
                type: 'POST',
                data: $(this).serialize() + '&action=update',
                dataType: 'json',
                beforeSend: function() {
                    $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memperbarui...');
                },
                success: function(res) {
                    if (res.status === 'success') {
                        Swal.fire('Berhasil!', res.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                complete: function() {
                    $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-edit"></i> Update Paket');
                }
            });
        });

        // HAPUS PAKET
        $(document).on('click', '.btn-hapus', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');

            Swal.fire({
                title: 'Konfirmasi Hapus!',
                text: `Paket "${nama}" akan dihapus permanen?`,
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
                            if (res.status === 'success') {
                                Swal.fire('Terhapus!', res.message, 'success').then(() => {
                                    location.reload();
                                });
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