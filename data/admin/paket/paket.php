<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";

$kategori = $con->query("SELECT id_kategori, nama_kategori FROM tbl_kategori ORDER BY nama_kategori")->fetch_all(MYSQLI_ASSOC);
$pakets   = $con->query("SELECT p.*, k.nama_kategori FROM tbl_paket p LEFT JOIN tbl_kategori k ON p.id_kategori=k.id_kategori ORDER BY p.id_paket DESC")->fetch_all(MYSQLI_ASSOC);

include '../../../view/master/header.php';
include '../../../view/master/sidebar.php';
?>

<style>

    /* Baris tombol & search */
.datatable-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

/* Rapikan tombol export */
.datatable-actions .dt-buttons {
    margin-bottom: 0 !important;
}

/* Rapikan input search */
.datatable-actions .dataTables_filter {
    margin-bottom: 0 !important;
}

/* Custom styling untuk DataTables - HANYA WARNA */
.dataTables_wrapper .dataTables_length select {
    background-color: white !important;
    border: 1px solid #ced4da !important;
    border-radius: 4px !important;
    padding: 5px 30px 5px 10px !important;
    color: #495057 !important;
}

.dataTables_wrapper .dataTables_filter input {
    background-color: white !important;
    border: 1px solid #ced4da !important;
    border-radius: 4px !important;
    padding: 5px 10px !important;
    color: #495057 !important;
    margin-left: 5px !important;
}

/* Tombol Tambah Paket - biru solid */
.btn-primary {
    background-color: #007bff !important;
    border: 2px solid #007bff !important;
    color: white !important;
}

.btn-primary:hover {
    background-color: #0056b3 !important;
    border-color: #0056b3 !important;
    color: white !important;
}

/* Layout DataTables: Buttons di kiri, Search di kanan */
.dataTables_wrapper .dataTables_filter {
    float: right !important;
}

.dataTables_wrapper .dt-buttons {
    float: left !important;
    margin-bottom: 10px !important;
}

.dataTables_wrapper .dataTables_length {
    float: left !important;
    margin-bottom: 10px !important;
}

/* Clear float */
.dataTables_wrapper::after {
    content: "";
    display: table;
    clear: both;
}
</style>

<section class="content-header">
    <div class="container-fluid">
        <h1>Menu Paket</h1>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Daftar Paket</h3>
            </div>
            <div class="card-body">
                <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalTambah">
                    <i class="fas fa-plus"></i> Tambah Paket
                </button>
                <table class="table table-bordered table-hover" id="tabelPaket">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>No</th>
                            <th>Nama Paket</th>
                            <th>Kategori</th>
                            <th>Tipe</th>
                            <th>Harga Umum</th>
                            <th>Harga Mahasiswa</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pakets as $i => $p):
                            $tipe = match ($p['durasi_hari']) {
                                1 => 'Harian',
                                30 => 'Bulanan',
                                90 => '3 Bulan',
                                180 => '6 Bulan',
                                365 => 'Tahunan',
                                default => $p['durasi_hari'] . ' Hari'
                            };
                        ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($p['nama_paket']) ?></strong></td>
                                <td><?= htmlspecialchars($p['nama_kategori'] ?? '-') ?></td>
                                <td><span class="badge badge-info"><?= $tipe ?></span></td>
                                <td>Rp <?= number_format($p['harga_umum'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($p['harga_mahasiswa'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($p['deskripsi'] ?: '-') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn"
                                        data-id="<?= $p['id_paket'] ?>"
                                        data-nama="<?= htmlspecialchars($p['nama_paket']) ?>"
                                        data-kategori="<?= $p['id_kategori'] ?>"
                                        data-deskripsi="<?= htmlspecialchars($p['deskripsi']) ?>"
                                        data-harga_umum="<?= $p['harga_umum'] ?>"
                                        data-harga_mahasiswa="<?= $p['harga_mahasiswa'] ?>"
                                        data-durasi="<?= $p['durasi_hari'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-hapus"
                                        data-id="<?= $p['id_paket'] ?>"
                                        data-nama="<?= htmlspecialchars($p['nama_paket']) ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
                        <label>Nama Paket <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_paket" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori <span class="text-danger">*</span></label>
                        <select class="form-control" name="id_kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategori as $kat): ?>
                                <option value="<?= $kat['id_kategori'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tipe Paket <span class="text-danger">*</span></label>
                        <select class="form-control" name="tipe_paket" id="tipe_paket" required onchange="hitungDurasi()">
                            <option value="">-- Pilih Tipe --</option>
                            <option value="1">1 Hari</option>
                            <option value="30">1 Bulan</option>
                            <option value="90">3 Bulan</option>
                            <option value="180">6 Bulan</option>
                            <option value="365">1 Tahun</option>
                        </select>
                        <input type="hidden" name="durasi_hari" id="durasi_hari">
                    </div>
                    <div class="form-group">
                        <label>Harga Umum (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="harga_umum" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Harga Mahasiswa (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="harga_mahasiswa" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Paket</button>
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
                        <label>Nama Paket <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_paket" id="edit_nama" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori <span class="text-danger">*</span></label>
                        <select class="form-control" name="id_kategori" id="edit_kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategori as $kat): ?>
                                <option value="<?= $kat['id_kategori'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tipe Paket <span class="text-danger">*</span></label>
                        <select class="form-control" name="tipe_paket" id="tipe_paket_edit" required onchange="setDurasiEdit(this.value)">
                            <option value="">-- Pilih Tipe --</option>
                            <option value="1">1 Hari</option>
                            <option value="30">1 Bulan</option>
                            <option value="90">3 Bulan</option>
                            <option value="180">6 Bulan</option>
                            <option value="365">1 Tahun</option>
                        </select>
                        <input type="hidden" name="durasi_hari" id="durasi_hari_edit">
                    </div>
                    <div class="form-group">
                        <label>Harga Umum (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="harga_umum" id="edit_harga_umum" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Harga Mahasiswa (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="harga_mahasiswa" id="edit_harga_mahasiswa" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update Paket</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../../../view/master/footer.php'; ?>

<!-- DataTables Buttons CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function hitungDurasi() {
        document.getElementById('durasi_hari').value = document.getElementById('tipe_paket').value;
    }

    function setDurasiEdit(val) {
        document.getElementById('durasi_hari_edit').value = val;
    }

    $(document).ready(function () {
    $('#tabelPaket').DataTable({
        language: {
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan _PAGE_ dari _PAGES_ halaman",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            search: "Cari:",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "›",
                previous: "‹"
            }
        },

        pageLength: 10,
        order: [[0, "asc"]],

        dom:
            "<'row mb-3'<'col-md-6 d-flex align-items-center'l><'col-md-6'f>>" +
            "<'row mb-3'<'col-md-12'B>>" +
            "rt" +
            "<'row'<'col-md-5'i><'col-md-7'p>>",

        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });

        // Edit
        $(document).on('click', '.edit-btn', function() {
            $('#edit_id').val($(this).data('id'));
            $('#edit_nama').val($(this).data('nama'));
            $('#edit_kategori').val($(this).data('kategori'));
            $('#edit_deskripsi').val($(this).data('deskripsi'));
            $('#edit_harga_umum').val($(this).data('harga_umum'));
            $('#edit_harga_mahasiswa').val($(this).data('harga_mahasiswa'));
            $('#tipe_paket_edit').val($(this).data('durasi'));
            $('#durasi_hari_edit').val($(this).data('durasi'));
            $('#modalEdit').modal('show');
        });

        // Tambah
        $('#formTambah').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'proses_paket.php',
                type: 'POST',
                data: $(this).serialize() + '&action=simpan',
                dataType: 'json',
                beforeSend: () => $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...'),
                success: res => {
                    if (res.status === 'success') {
                        Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                complete: () => $('button[type="submit"]').prop('disabled', false).html('Simpan Paket')
            });
        });

        // Update
        $('#formEdit').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'proses_paket.php',
                type: 'POST',
                data: $(this).serialize() + '&action=update',
                dataType: 'json',
                beforeSend: () => $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...'),
                success: res => {
                    if (res.status === 'success') {
                        Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                complete: () => $('button[type="submit"]').prop('disabled', false).html('Update Paket')
            });
        });

        // Hapus
        $(document).on('click', '.btn-hapus', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            Swal.fire({
                title: 'Hapus Paket?',
                text: `"${nama}" akan dihapus permanen!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    $.get('proses_paket.php', {
                        action: 'hapus',
                        id: id
                    }, res => {
                        res = JSON.parse(res);
                        Swal.fire(
                            res.status === 'success' ? 'Terhapus!' : 'Gagal!', 
                            res.message, 
                            res.status === 'success' ? 'success' : 'error'
                        ).then(() => {
                            if (res.status === 'success') location.reload();
                        });
                    });
                }
            });
        });
    });
</script>