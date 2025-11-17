<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Data Member</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Beranda</a></li>
                    <li class="breadcrumb-item active">Member</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Daftar Member</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabelPelatih" class="table table-bordered table-striped">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. HP</th>
                                <th>Status Akun</th>
                                <th>Membership</th>
                                <th>Tgl Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $members = [];
                            $query = $con->query("SELECT * FROM vw_member_status ORDER BY id_member ASC");
                            if ($query && $query->num_rows > 0) {
                                while ($row = $query->fetch_assoc()) {
                                    $members[] = $row;
                                }
                            }

                            if (empty($members)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data member</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1;
                                foreach ($members as $m): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($m['nama'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($m['email'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($m['no_hp'] ?? '-') ?></td>
                                        <td>
                                            <?= ($m['status_akun'] ?? 'nonaktif') === 'aktif'
                                                ? '<span class="badge badge-success">Aktif</span>'
                                                : '<span class="badge badge-secondary">Nonaktif</span>'
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $sisa_teks = "-";
                                            $badge = '<span class="badge badge-light">Tidak Aktif</span>';

                                            if (!empty($m['tgl_berakhir'])) {
                                                $end = new DateTime($m['tgl_berakhir']);
                                                $now = new DateTime();
                                                $interval = $now->diff($end);
                                                if ($now < $end) {
                                                    // hitung sisa jam dan hari
                                                    $sisa_hari = $interval->days;
                                                    $sisa_jam = $interval->h + ($sisa_hari * 24);
                                                    if ($sisa_hari > 0) {
                                                        $sisa_teks = "{$sisa_hari} hari";
                                                    } else {
                                                        $sisa_teks = "{$sisa_jam} jam";
                                                    }
                                                    $badge = '<span class="badge badge-success">Aktif</span>';
                                                } else {
                                                    $sisa_teks = "Expired";
                                                    $badge = '<span class="badge badge-danger">Expired</span>';
                                                }
                                            }
                                            ?>
                                            <?= $badge ?><br>
                                            <small>
                                                <strong><?= htmlspecialchars($m['nama_paket'] ?? '-') ?></strong><br>
                                                <?= $sisa_teks ?><br>
                                                <?php if (!empty($m['tgl_berakhir'])): ?>
                                                    Sampai: <?= date('d/m/Y H:i', strtotime($m['tgl_berakhir'])) ?>
                                                <?php endif; ?>
                                            </small>
                                        </td>

                                        <td><?= !empty($m['tanggal_daftar']) ? date('d M Y', strtotime($m['tanggal_daftar'])) : '-' ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEdit<?= $m['id_member'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm btn-hapus" data-id="<?= $m['id_member'] ?>" data-nama="<?= htmlspecialchars($m['nama']) ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalDetail<?= $m['id_member'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================
     MODAL DETAIL
========================= -->
<?php if (!empty($members)): ?>
    <?php foreach ($members as $m): ?>
        <!-- MODAL DETAIL -->
        <div class="modal fade" id="modalDetail<?= $m['id_member'] ?>">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Detail Member: <?= htmlspecialchars($m['nama'] ?? '—') ?></h5>
                        <button type="button" class="close text-white" data-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <th>ID</th>
                                        <td><?= $m['id_member'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Nama</th>
                                        <td><?= htmlspecialchars($m['nama'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td><?= htmlspecialchars($m['email'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th>No. HP</th>
                                        <td><?= htmlspecialchars($m['no_hp'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Alamat</th>
                                        <td><?= nl2br(htmlspecialchars($m['alamat'] ?? '-')) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Tgl Daftar</th>
                                        <td><?= date('d M Y H:i', strtotime($m['tanggal_daftar'])) ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <th>Status Akun</th>
                                        <td><?= ($m['status_akun'] ?? 'nonaktif') === 'aktif'
                                                ? '<span class="badge badge-success">Aktif</span>'
                                                : '<span class="badge badge-secondary">Nonaktif</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Membership</th>
                                        <td>
                                            <?php if (!empty($m['tgl_berakhir'])): ?>
                                                <?php
                                                $end = new DateTime($m['tgl_berakhir']);
                                                $now = new DateTime();
                                                if ($now < $end) {
                                                    $interval = $now->diff($end);
                                                    $jam_total = $interval->days * 24 + $interval->h;
                                                    echo '<span class="badge badge-success">Aktif</span><br>';
                                                    echo "<strong>{$m['nama_paket']}</strong><br>";
                                                    if ($interval->days > 0) {
                                                        echo "Sisa: {$interval->days} hari";
                                                    } else {
                                                        echo "Sisa: {$jam_total} jam";
                                                    }
                                                    echo "<br>Sampai: " . date('d/m/Y H:i', strtotime($m['tgl_berakhir']));
                                                } else {
                                                    echo '<span class="badge badge-danger">Expired</span>';
                                                }
                                                ?>
                                            <?php else: ?>
                                                <span class="badge badge-light">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- =========================
     MODAL EDIT
========================= -->
<?php if (!empty($members)): ?>
    <?php foreach ($members as $m): ?>
        <!-- MODAL EDIT -->
        <div class="modal fade" id="modalEdit<?= $m['id_member'] ?>">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title">Edit Member: <?= htmlspecialchars($m['nama'] ?? '—') ?></h5>
                        <button type="button" class="close text-white" data-dismiss="modal">×</button>
                    </div>
                    <form id="formEdit<?= $m['id_member'] ?>" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="id_member" value="<?= $m['id_member'] ?>">
                            <input type="hidden" name="action" value="update">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama *</label>
                                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($m['nama'] ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email *</label>
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($m['email'] ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>No. HP</label>
                                        <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($m['no_hp'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Alamat</label>
                                        <textarea name="alamat" class="form-control" rows="4"><?= htmlspecialchars($m['alamat'] ?? '') ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Status Akun</label>
                                        <select name="status_akun" class="form-control" required>
                                            <option value="aktif" <?= ($m['status_akun'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                            <option value="nonaktif" <?= ($m['status_akun'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Tanggal Daftar</label>
                                        <input type="text" class="form-control" value="<?= !empty($m['tanggal_daftar']) ? date('d M Y H:i', strtotime($m['tanggal_daftar'])) : '-' ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../../../view/master/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Handle form edit dengan AJAX
    $('form[id^="formEdit"]').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const formData = form.serialize();
        
        Swal.fire({
            title: 'Update Data?',
            text: 'Yakin ingin memperbarui data member?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Update!',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'proses_member.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Sukses!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Tutup modal dan reload halaman
                                $('.modal').modal('hide');
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat mengirim data.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });

    // Handler untuk tombol hapus
    $('.btn-hapus').on('click', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        Swal.fire({
            title: 'Hapus Member?',
            text: `Yakin ingin menghapus "${nama}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                $.get('proses_member.php', {
                    action: 'hapus',
                    id
                }, function(res) {
                    const data = JSON.parse(res);
                    Swal.fire({
                        title: data.status === 'success' ? 'Sukses!' : 'Gagal!',
                        text: data.message,
                        icon: data.status === 'success' ? 'success' : 'error'
                    }).then(() => {
                        if (data.status === 'success') location.reload();
                    });
                }).fail(() => Swal.fire('Error', 'Gagal menghubungi server.', 'error'));
            }
        });
    });
});
</script>