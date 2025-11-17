<?php
require "../../../setting/session.php";
checkSession("admin"); // hanya admin boleh masuk

require "../../../setting/koneksi.php"; // Menggunakan $con

// Debug: Cek koneksi
if (!isset($con) || $con->connect_error) {
    die("KONEKSI DATABASE GAGAL: " . $con->connect_error);
}
?>

<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<?php
// Query untuk mengambil data testimoni beserta data member - PERBAIKAN: name menjadi nama
$query = "
    SELECT 
        t.id,
        t.testimoni,
        t.rating,
        t.status,
        t.created_at,
        m.nama AS nama_member,
        m.foto,
        YEAR(m.tanggal_daftar) AS member_sejak
    FROM tbl_testimoni t
    INNER JOIN tbl_member m ON t.member_id = m.id_member
    ORDER BY t.created_at DESC
";

$result = $con->query($query);

// Cek jika query gagal
if (!$result) {
    echo "<div class='alert alert-danger'>Error query: " . $con->error . "</div>";
    $result = []; // Set result kosong untuk menghindari error
}
?>

<!-- Content Wrapper. Contains page content -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Data Testimoni Member</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Beranda</a></li>
                        <li class="breadcrumb-item active">Kategori</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Daftar Testimoni dari Member</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <i class="icon fas fa-check"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <i class="icon fas fa-ban"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($result && $result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table id="example1" class="table table-bordered table-hover table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="20%">Member</th>
                                                <th width="30%">Testimoni</th>
                                                <th width="10%">Rating</th>
                                                <th width="10%">Status</th>
                                                <th width="15%">Tanggal</th>
                                                <th width="10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++; ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if (!empty($row['foto'])): ?>
                                                                <img src="../../../data/member/<?= $row['foto']; ?>" 
                                                                     class="img-circle elevation-2 mr-3" alt="Foto Member" 
                                                                     style="width: 45px; height: 45px; object-fit: cover;">
                                                            <?php else: ?>
                                                                <img src="../../../assets/img/default-avatar.png" 
                                                                     class="img-circle elevation-2 mr-3" alt="Default Avatar" 
                                                                     style="width: 45px; height: 45px; object-fit: cover;">
                                                            <?php endif; ?>
                                                            <div>
                                                                <strong><?= htmlspecialchars($row['nama_member']); ?></strong><br>
                                                                <small class="text-muted">Member sejak: <?= $row['member_sejak']; ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="testimoni-text" style="max-height: 60px; overflow: hidden; position: relative;">
                                                            <?= htmlspecialchars($row['testimoni']); ?>
                                                            <?php if (strlen($row['testimoni']) > 150): ?>
                                                                <span class="text-muted">...</span>
                                                                <button type="button" class="btn btn-xs btn-link p-0 ml-1 read-more-btn" data-toggle="tooltip" title="Baca selengkapnya">
                                                                    <small>selengkapnya</small>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        $rating = $row['rating'];
                                                        $stars = '';
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            if ($i <= $rating) {
                                                                $stars .= '<span class="text-warning" style="font-size: 1.2em;">★</span>';
                                                            } else {
                                                                $stars .= '<span class="text-muted" style="font-size: 1.2em;">★</span>';
                                                            }
                                                        }
                                                        echo '<div class="d-flex flex-column align-items-center">';
                                                        echo '<div>' . $stars . '</div>';
                                                        echo '<small class="text-muted mt-1">(' . $rating . '/5)</small>';
                                                        echo '</div>';
                                                        ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-<?= $row['status'] == 'publish' ? 'success' : 'warning'; ?> p-2">
                                                            <i class="fas fa-<?= $row['status'] == 'publish' ? 'check-circle' : 'clock'; ?> mr-1"></i>
                                                            <?= ucfirst($row['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <span><?= date('d M Y', strtotime($row['created_at'])); ?></span>
                                                            <small class="text-muted"><?= date('H:i', strtotime($row['created_at'])); ?></small>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group-vertical btn-group-sm">
                                                            <?php if ($row['status'] == 'pending'): ?>
                                                                <a href="proses_testimoni.php?action=publish&id=<?= $row['id']; ?>" 
                                                                   class="btn btn-success" 
                                                                   onclick="return confirm('Yakin ingin publish testimoni ini?')"
                                                                   data-toggle="tooltip" title="Publikasikan testimoni">
                                                                    <i class="fas fa-check"></i> Publish
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="proses_testimoni.php?action=pending&id=<?= $row['id']; ?>" 
                                                                   class="btn btn-warning" 
                                                                   onclick="return confirm('Yakin ubah status menjadi pending?')"
                                                                   data-toggle="tooltip" title="Tunda testimoni">
                                                                    <i class="fas fa-clock"></i> Pending
                                                                </a>
                                                            <?php endif; ?>
                                                            <a href="proses_testimoni.php?action=delete&id=<?= $row['id']; ?>" 
                                                               class="btn btn-danger" 
                                                               onclick="return confirm('Yakin ingin menghapus testimoni ini?')"
                                                               data-toggle="tooltip" title="Hapus testimoni">
                                                                <i class="fas fa-trash"></i> Hapus
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                    <h4 class="text-muted">Belum ada testimoni dari member</h4>
                                    <p class="text-muted">Testimoni dari member akan muncul di sini</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer clearfix">
                            <div class="float-right text-muted">
                                <small>Total: <?= $result ? $result->num_rows : 0; ?> testimoni</small>
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->

<!-- Modal untuk membaca testimoni lengkap -->
<div class="modal fade" id="testimoniModal" tabindex="-1" role="dialog" aria-labelledby="testimoniModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testimoniModalLabel">Testimoni Lengkap</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="fullTestimoni"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inisialisasi tooltip
    $('[data-toggle="tooltip"]').tooltip();
    
    // Fungsi untuk menampilkan testimoni lengkap
    $('.read-more-btn').click(function() {
        var testimoniText = $(this).closest('td').find('.testimoni-text').text().replace('...selengkapnya', '');
        $('#fullTestimoni').text(testimoniText);
        $('#testimoniModal').modal('show');
    });
    
    // DataTable initialization
    if ($('#example1').length) {
        $('#example1').DataTable({
            "responsive": true,
            "autoWidth": false,
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ testimoni per halaman",
                "zeroRecords": "Tidak ada testimoni yang ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada testimoni",
                "infoFiltered": "(disaring dari _MAX_ total testimoni)",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Berikutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    }
});
</script>

<style>
.card-primary.card-outline {
    border-top: 3px solid #007bff;
}

.testimoni-text {
    line-height: 1.5;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.btn-group-vertical .btn {
    margin-bottom: 2px;
    border-radius: 4px;
}

.read-more-btn {
    text-decoration: none;
    font-weight: 500;
}

.read-more-btn:hover {
    text-decoration: underline;
}

.badge {
    font-size: 0.85em;
}
</style>

<?php include '../../../view/master/footer.php'; ?>