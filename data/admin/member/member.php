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
                <h1>Menu Member</h1>
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
                <h3 class="card-title">Data Member</h3>
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
                            // Ambil semua data sekaligus ke array agar bisa dipakai ulang di modal
                            $members = [];
                            $query = $con->query("SELECT * FROM vw_member_dashboard ORDER BY id_member ASC");
                            if ($query && $query->num_rows > 0) {
                                while ($row = $query->fetch_assoc()) {
                                    $members[] = $row;
                                }
                            }

                            if (empty($members)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data member</td>
                                </tr>
                                <?php else:
                                $no = 1;
                                foreach ($members as $m): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($m['nama'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($m['email'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($m['no_hp'] ?? '-') ?></td>
                                        <td>
                                            <?= $m['status_akun'] === 'aktif'
                                                ? '<span class="badge badge-success">Aktif</span>'
                                                : '<span class="badge badge-secondary">Nonaktif</span>' ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            if ($m['membership_status'] === 'aktif' && !empty($m['tgl_berakhir'])) {
                                                $end = new DateTime($m['tgl_berakhir']);
                                                $now = new DateTime();
                                                $interval = $now->diff($end);
                                                $sisa = $interval->days > 0 ? $interval->days . ' hari' : ($interval->h + ($interval->i > 0 ? 1 : 0)) . ' jam';
                                                echo '<span class="badge badge-success">Aktif</span><br>';
                                                echo '<small><strong>' . htmlspecialchars($m['nama_paket'] ?? '-') . '</strong><br>';
                                                echo "Sisa: $sisa<br>";
                                                echo 'Sampai: ' . date('d/m/Y H:i', strtotime($m['tgl_berakhir'])) . '</small>';
                                            } elseif ($m['membership_status'] === 'expired') {
                                                echo '<span class="badge badge-danger">Expired</span>';
                                            } else {
                                                echo '<span class="badge badge-light">Belum Aktif</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?= date('d M Y', strtotime($m['tanggal_daftar'])) ?></td>
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

<!-- ========================= MODAL DETAIL ========================= -->
<?php foreach ($members as $m): ?>
    <div class="modal fade" id="modalDetail<?= $m['id_member'] ?>">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Detail Member: <?= htmlspecialchars($m['nama']) ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
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
                                    <td><?= htmlspecialchars($m['nama']) ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><?= htmlspecialchars($m['email']) ?></td>
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
                                    <td><?= $m['status_akun'] === 'aktif'
                                            ? '<span class="badge badge-success">Aktif</span>'
                                            : '<span class="badge badge-secondary">Nonaktif</span>' ?></td>
                                </tr>
                                <tr>
                                    <th>Membership</th>
                                    <td>
                                        <?php if (!empty($m['tgl_berakhir'])):
                                            $end = new DateTime($m['tgl_berakhir']);
                                            $now = new DateTime();
                                            if ($now < $end):
                                                $interval = $now->diff($end);
                                                $jam_total = $interval->days * 24 + $interval->h;
                                                echo '<span class="badge badge-success">Aktif</span><br>';
                                                echo '<strong>' . htmlspecialchars($m['nama_paket'] ?? '-') . '</strong><br>';
                                                echo $interval->days > 0 ? "Sisa: {$interval->days} hari" : "Sisa: {$jam_total} jam";
                                                echo '<br>Sampai: ' . date('d/m/Y H:i', strtotime($m['tgl_berakhir']));
                                            else:
                                                echo '<span class="badge badge-danger">Expired</span>';
                                            endif;
                                        else: ?>
                                            <span class="badge badge-light">Belum Aktif</span>
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

<!-- ========================= MODAL EDIT ========================= -->
<?php foreach ($members as $m): ?>
    <div class="modal fade" id="modalEdit<?= $m['id_member'] ?>">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">Edit Member: <?= htmlspecialchars($m['nama']) ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form id="formEdit<?= $m['id_member'] ?>" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_member" value="<?= $m['id_member'] ?>">
                        <input type="hidden" name="action" value="update">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama *</label>
                                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($m['nama']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($m['email']) ?>" required>
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
                                        <option value="aktif" <?= $m['status_akun'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="nonaktif" <?= $m['status_akun'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Tanggal Daftar</label>
                                    <input type="text" class="form-control" value="<?= date('d M Y H:i', strtotime($m['tanggal_daftar'])) ?>" readonly>
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

<?php include '../../../view/master/footer.php'; ?>

<!-- SweetAlert2 (hanya 1x) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Edit Member
        $('form[id^="formEdit"]').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            Swal.fire({
                title: 'Update Data?',
                text: 'Yakin ingin memperbarui data member?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Update!',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    $.post('proses_member.php', formData, function(res) {
                        let data;
                        try {
                            data = JSON.parse(res);
                        } catch (e) {
                            data = {
                                status: 'error',
                                message: 'Server error'
                            };
                        }
                        Swal.fire({
                            title: data.status === 'success' ? 'Sukses!' : 'Error!',
                            text: data.message,
                            icon: data.status === 'success' ? 'success' : 'error'
                        }).then(() => {
                            if (data.status === 'success') location.reload();
                        });
                    }).fail(() => {
                        Swal.fire('Error', 'Gagal koneksi ke server', 'error');
                    });
                }
            });
        });

        // Hapus Member
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
                        id: id
                    }, function(res) {
                        let data;
                        try {
                            data = JSON.parse(res);
                        } catch (e) {
                            data = {
                                status: 'error',
                                message: 'Server error'
                            };
                        }
                        Swal.fire({
                            title: data.status === 'success' ? 'Sukses!' : 'Gagal!',
                            text: data.message,
                            icon: data.status === 'success' ? 'success' : 'error'
                        }).then(() => {
                            if (data.status === 'success') location.reload();
                        });
                    }).fail(() => Swal.fire('Error', 'Gagal koneksi ke server', 'error'));
                }
            });
        });
    });
</script>