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
                                <th>Foto</th>
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
                                    <td colspan="9" class="text-center">Tidak ada data member</td>
                                </tr>
                                <?php else:
                                $no = 1;
                                foreach ($members as $m): 
                                    // Ambil data foto dari tbl_member
                                    $query_foto = $con->prepare("SELECT foto FROM tbl_member WHERE id_member = ?");
                                    $query_foto->bind_param("i", $m['id_member']);
                                    $query_foto->execute();
                                    $result_foto = $query_foto->get_result();
                                    $foto_data = $result_foto->fetch_assoc();
                                    $foto = $foto_data['foto'] ?? '';
                                    $query_foto->close();
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td class="text-center">
                                            <div class="member-photo-container">
                                                <?php if (!empty($foto) && $foto !== 'default.jpg'): ?>
                                                    <img src="../../uploads/member/<?= $foto ?>" 
                                                         alt="Foto <?= htmlspecialchars($m['nama']) ?>" 
                                                         class="member-photo rounded-circle"
                                                         style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #dee2e6;"
                                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAiIGhlaWdodD0iNTAiIHZpZXdCb3g9IjAgMCA1MCA1MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjUiIGN5PSIyNSIgcj0iMjUiIGZpbGw9IiNlMmUyZTIiLz4KPHN2ZyB4PSIxMiIgeT0iMTIiIHdpZHRoPSIyNiIgaGVpZ2h0PSIyNiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM5OTk5OTkiIHN0cm9rZS13aWR0aD0iMiI+CjxwYXRoIGQ9Ik0yMCAyMXYtMmE0IDQgMCAwIDAtNC00SDhhNCA0IDAgMCAwLTQgNHYyIi8+CjxjaXJjbGUgY3g9IjEyIiBjeT0iNyIgcj0iNCIvPgo8L3N2Zz4KPC9zdmc+'">
                                                <?php else: ?>
                                                    <div class="member-initial rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                                         style="width: 50px; height: 50px; background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%); font-size: 1.2rem;">
                                                        <?= strtoupper(substr($m['nama'], 0, 1)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
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
<?php foreach ($members as $m): 
    // Ambil data foto untuk modal detail
    $query_foto_detail = $con->prepare("SELECT foto FROM tbl_member WHERE id_member = ?");
    $query_foto_detail->bind_param("i", $m['id_member']);
    $query_foto_detail->execute();
    $result_foto_detail = $query_foto_detail->get_result();
    $foto_detail = $result_foto_detail->fetch_assoc();
    $foto_member = $foto_detail['foto'] ?? '';
    $query_foto_detail->close();
?>
    <div class="modal fade" id="modalDetail<?= $m['id_member'] ?>">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Detail Member: <?= htmlspecialchars($m['nama']) ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="member-photo-large mb-3">
                                <?php if (!empty($foto_member) && $foto_member !== 'default.jpg'): ?>
                                    <img src="../../uploads/member/<?= $foto_member ?>" 
                                         alt="Foto <?= htmlspecialchars($m['nama']) ?>" 
                                         class="rounded-circle img-fluid"
                                         style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #dee2e6;"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgdmlld0JveD0iMCAwIDE1MCAxNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxjaXJjbGUgY3g9Ijc1IiBjeT0iNzUiIHI9Ijc1IiBmaWxsPSIjZTJlMmUyIi8+Cjxzdmcgd2lkdGg9IjE1MCIgaGVpZ2h0PSIxNTAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjOTk5OTk5IiBzdHJva2Utd2lkdGg9IjIiPgo8cGF0aCBkPSJNMjAgMjF2LTJhNCA0IDAgMCAwLTQtNEg4YTQgNCAwIDAgMC00IDR2MiIvPgo8Y2lyY2xlIGN4PSIxMiIgY3k9IjciIHI9IjQiLz4KPC9zdmc+Cjwvc3ZnPg=='">
                                <?php else: ?>
                                    <div class="member-initial-large rounded-circle d-flex align-items-center justify-content-center text-white fw-bold mx-auto"
                                         style="width: 150px; height: 150px; background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%); font-size: 3rem;">
                                        <?= strtoupper(substr($m['nama'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h5 class="mb-1"><?= htmlspecialchars($m['nama']) ?></h5>
                            <p class="text-muted mb-0"><?= htmlspecialchars($m['email']) ?></p>
                        </div>
                        <div class="col-md-9">
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
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-bordered">
                                        <tr>
                                            <th>Alamat</th>
                                            <td><?= nl2br(htmlspecialchars($m['alamat'] ?? '-')) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tgl Daftar</th>
                                            <td><?= date('d M Y H:i', strtotime($m['tanggal_daftar'])) ?></td>
                                        </tr>
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
        </div>
    </div>
<?php endforeach; ?>

<!-- ========================= MODAL EDIT ========================= -->
<?php foreach ($members as $m): 
    // Ambil data foto untuk modal edit
    $query_foto_edit = $con->prepare("SELECT foto FROM tbl_member WHERE id_member = ?");
    $query_foto_edit->bind_param("i", $m['id_member']);
    $query_foto_edit->execute();
    $result_foto_edit = $query_foto_edit->get_result();
    $foto_edit = $result_foto_edit->fetch_assoc();
    $foto_member_edit = $foto_edit['foto'] ?? '';
    $query_foto_edit->close();
?>
    <div class="modal fade" id="modalEdit<?= $m['id_member'] ?>">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">Edit Member: <?= htmlspecialchars($m['nama']) ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form id="formEdit<?= $m['id_member'] ?>" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id_member" value="<?= $m['id_member'] ?>">
                        <input type="hidden" name="action" value="update">

                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="member-photo-edit mb-3">
                                    <?php if (!empty($foto_member_edit) && $foto_member_edit !== 'default.jpg'): ?>
                                        <img src="../../uploads/member/<?= $foto_member_edit ?>" 
                                             alt="Foto <?= htmlspecialchars($m['nama']) ?>" 
                                             class="rounded-circle img-fluid mb-2"
                                             style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #dee2e6;"
                                             id="fotoPreview<?= $m['id_member'] ?>"
                                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEyMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxjaXJjbGUgY3g9IjYwIiBjeT0iNjAiIHI9IjYwIiBmaWxsPSIjZTJlMmUyIi8+Cjxzdmcgd2lkdGg9IjEyMCIgaGVpZ2h0PSIxMjAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjOTk5OTk5IiBzdHJva2Utd2lkdGg9IjIiPgo8cGF0aCBkPSJNMjAgMjF2LTJhNCA0IDAgMCAwLTQtNEg4YTQgNCAwIDAgMC00IDR2MiIvPgo8Y2lyY2xlIGN4PSIxMiIgY3k9IjciIHI9IjQiLz4KPC9zdmc+Cjwvc3ZnPg=='">
                                    <?php else: ?>
                                        <div class="member-initial-edit rounded-circle d-flex align-items-center justify-content-center text-white fw-bold mx-auto mb-2"
                                             style="width: 120px; height: 120px; background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%); font-size: 2.5rem;"
                                             id="fotoPreview<?= $m['id_member'] ?>">
                                            <?= strtoupper(substr($m['nama'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label for="foto<?= $m['id_member'] ?>" class="btn btn-outline-primary btn-sm btn-block">
                                        <i class="fas fa-camera"></i> Ganti Foto
                                    </label>
                                    <input type="file" name="foto" id="foto<?= $m['id_member'] ?>" class="d-none" accept="image/*">
                                    <small class="form-text text-muted">Max 2MB, JPG/PNG</small>
                                </div>
                            </div>
                            <div class="col-md-9">
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
        // Preview foto sebelum upload di modal edit
        $('input[type="file"][name="foto"]').on('change', function(e) {
            const file = e.target.files[0];
            const memberId = $(this).attr('id').replace('foto', '');
            const preview = $('#fotoPreview' + memberId);
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (preview.hasClass('member-initial-edit')) {
                        // Ganti div dengan img
                        const newPreview = $('<img>', {
                            src: e.target.result,
                            class: 'rounded-circle img-fluid',
                            style: 'width: 120px; height: 120px; object-fit: cover; border: 3px solid #dee2e6;'
                        });
                        preview.replaceWith(newPreview);
                    } else {
                        // Update src img yang sudah ada
                        preview.attr('src', e.target.result);
                    }
                }
                reader.readAsDataURL(file);
            }
        });

        // Edit Member dengan form data (untuk upload file)
        $('form[id^="formEdit"]').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

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
                        processData: false,
                        contentType: false,
                        success: function(res) {
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
                        },
                        error: function() {
                            Swal.fire('Error', 'Gagal koneksi ke server', 'error');
                        }
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