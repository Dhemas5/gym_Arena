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
                        <!-- TARO DI SINI: TAMPILKAN TABEL DARI ARRAY -->
                        <tbody>
                            <?php
                            // === AMBIL DATA SEKALI, SIMPAN KE ARRAY ===
                            $members = [];
                            $query = $con->query("
                                SELECT m.*, p.nama_paket 
                                FROM tbl_member m 
                                LEFT JOIN tbl_paket p ON m.id_paket_aktif = p.id_paket 
                                ORDER BY m.id_member ASC
                            ");
                            if ($query->num_rows > 0) {
                                while ($row = $query->fetch_assoc()) {
                                    $members[] = $row;
                                }
                            }

                            if (empty($members)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1;
                                foreach ($members as $m): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($m['nama']) ?></td>
                                        <td><?= htmlspecialchars($m['email']) ?></td>
                                        <td><?= htmlspecialchars($m['no_hp'] ?? '-') ?></td>
                                        <td>
                                            <?= $m['status_akun'] == 'aktif'
                                                ? '<span class="badge badge-success">Aktif</span>'
                                                : '<span class="badge badge-secondary">Nonaktif</span>'
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $membership = $m['status_membership'] ?? 'Tidak Aktif';
                                            if ($membership == 'Aktif') {
                                                echo '<span class="badge badge-success">Aktif</span><br>
                                                      <small><strong>' . htmlspecialchars($m['nama_paket'] ?? '—') . '</strong><br>
                                                      ' . ($m['tgl_kedaluwarsa'] ? date('d/m/Y', strtotime($m['tgl_kedaluwarsa'])) : '-') . '</small>';
                                            } elseif ($membership == 'Expired') {
                                                echo '<span class="badge badge-danger">Expired</span>';
                                            } else {
                                                echo '<span class="badge badge-light">Tidak Aktif</span>';
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

<!-- TARO DI SINI: MODAL EDIT & DETAIL (SETELAH TABEL) -->
<?php foreach ($members as $m): ?>
    <!-- MODAL EDIT -->
    <div class="modal fade" id="modalEdit<?= $m['id_member'] ?>">
        <div class="modal-dialog">
            <form class="formEdit" data-id="<?= $m['id_member'] ?>">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Edit Member</h5>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_member" value="<?= $m['id_member'] ?>">
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($m['nama']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($m['email']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Password Baru</label>
                            <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak diubah">
                        </div>
                        <div class="form-group">
                            <label>No. HP</label>
                            <input type="text" class="form-control" name="no_hp" value="<?= htmlspecialchars($m['no_hp'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea class="form-control" name="alamat" rows="2"><?= htmlspecialchars($m['alamat'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Status Akun</label>
                            <select class="form-control" name="status_akun">
                                <option value="aktif" <?= $m['status_akun'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="nonaktif" <?= $m['status_akun'] == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DETAIL -->
    <div class="modal fade" id="modalDetail<?= $m['id_member'] ?>">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Detail Member: <?= htmlspecialchars($m['nama']) ?></h5>
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
                                    <td>
                                        <?= $m['status_akun'] == 'aktif'
                                            ? '<span class="badge badge-success">Aktif</span>'
                                            : '<span class="badge badge-secondary">Nonaktif</span>'
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Membership</th>
                                    <td>
                                        <?php
                                        if ($m['status_membership'] == 'Aktif') {
                                            echo '<span class="badge badge-success">Aktif</span><br>
                                              <strong>' . htmlspecialchars($m['nama_paket'] ?? '—') . '</strong><br>
                                              <small>Mulai: ' . ($m['tgl_mulai_aktif'] ? date('d/m/Y', strtotime($m['tgl_mulai_aktif'])) : '-') . '<br>
                                              Berakhir: ' . ($m['tgl_kedaluwarsa'] ? date('d/m/Y', strtotime($m['tgl_kedaluwarsa'])) : '-') . '</small>';
                                        } elseif ($m['status_membership'] == 'Expired') {
                                            echo '<span class="badge badge-danger">Expired</span>';
                                        } else {
                                            echo '<span class="badge badge-light">Tidak Aktif</span>';
                                        }
                                        ?>
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

<?php include '../../../view/master/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Edit
        $('.formEdit').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'proses_member.php',
                type: 'POST',
                data: $(this).serialize() + '&action=update',
                dataType: 'json',
                success: function(res) {
                    Swal.fire(res.status == 'success' ? 'Sukses!' : 'Gagal', res.message, res.status == 'success' ? 'success' : 'error')
                        .then(() => {
                            if (res.status == 'success') location.reload();
                        });
                }
            });
        });

        // Hapus
        $('.btn-hapus').click(function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            Swal.fire({
                title: 'Hapus Member?',
                text: `Yakin hapus "${nama}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(r => {
                if (r.isConfirmed) {
                    $.get('proses_member.php', {
                        action: 'hapus',
                        id: id
                    }, function(res) {
                        const data = JSON.parse(res);
                        Swal.fire(data.status == 'success' ? 'Sukses!' : 'Gagal', data.message, data.status == 'success' ? 'success' : 'error')
                            .then(() => {
                                if (data.status == 'success') location.reload();
                            });
                    });
                }
            });
        });
    });
</script>