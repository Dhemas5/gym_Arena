<?php
// File: /data/admin/notifikasi/index.php
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
                <h1>Notifikasi</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Notifikasi</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Notifikasi</h3>
                <div class="card-tools">
                    <a href="?action=mark_all_read" class="btn btn-sm btn-info">
                        <i class="fas fa-check-double mr-1"></i> Tandai Semua Dibaca
                    </a>
                    <a href="?action=clear_all" class="btn btn-sm btn-danger"
                        onclick="return confirm('Hapus semua notifikasi?')">
                        <i class="fas fa-trash mr-1"></i> Hapus Semua
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th width="60">Tipe</th>
                                <th>Judul</th>
                                <th>Pesan</th>
                                <th width="150">Tanggal</th>
                                <th width="100">Status</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query notifikasi
                            $query = "SELECT * FROM tbl_notifikasi ORDER BY dibuat_pada DESC";
                            $result = mysqli_query($con, $query);

                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)):
                                // Tentukan badge warna
                                $badgeColor = 'secondary';
                                if ($row['tipe'] == 'new_member') $badgeColor = 'success';
                                elseif ($row['tipe'] == 'new_membership') $badgeColor = 'primary';
                                elseif ($row['tipe'] == 'warning') $badgeColor = 'danger';
                            ?>
                                <tr class="<?php echo $row['dibaca'] == 0 ? 'table-info' : ''; ?>">
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $badgeColor; ?>">
                                            <?php echo $row['tipe']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                    <td><?php echo htmlspecialchars($row['pesan']); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($row['dibuat_pada'])); ?></td>
                                    <td>
                                        <?php if ($row['dibaca'] == 0): ?>
                                            <span class="badge badge-warning">Belum Dibaca</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Sudah Dibaca</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?action=mark_read&id=<?php echo $row['id_notifikasi']; ?>"
                                            class="btn btn-sm btn-info" title="Tandai Dibaca">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $row['id_notifikasi']; ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Hapus notifikasi ini?')" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../../../view/master/footer.php'; ?>