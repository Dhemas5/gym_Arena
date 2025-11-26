<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";

// Tandai semua notifikasi sebagai dibaca saat halaman dibuka
$con->query("UPDATE tbl_notifikasi SET dibaca = 1 WHERE dibaca = 0");

// Ambil semua notifikasi
$query = $con->prepare("
    SELECT * FROM tbl_notifikasi 
    ORDER BY dibuat_pada DESC
");
$query->execute();
$notifications = $query->get_result()->fetch_all(MYSQLI_ASSOC);

include '../../../view/master/header.php';
include '../../../view/master/sidebar.php';
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Notifikasi</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Beranda</a></li>
                    <li class="breadcrumb-item active">Notifikasi</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Semua Notifikasi</h3>
                <div class="card-tools">
                    <span class="badge badge-light"><?= count($notifications) ?> notifikasi</span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Tidak ada notifikasi</h4>
                        <p class="text-muted">Semua notifikasi akan muncul di sini</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $notif): ?>
                            <div class="list-group-item">
                                <div class="d-flex align-items-start">
                                    <div class="mr-3 mt-1">
                                        <i class="<?= 
                                            $notif['tipe'] == 'member_baru' ? 'fas fa-user-plus text-success' : 
                                            ($notif['tipe'] == 'transaksi' ? 'fas fa-shopping-cart text-info' : 
                                            'fas fa-bell text-primary') 
                                        ?> fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h6 class="mb-1"><?= htmlspecialchars($notif['judul']) ?></h6>
                                            <small class="text-muted">
                                                <?= date('d M Y H:i', strtotime($notif['dibuat_pada'])) ?>
                                            </small>
                                        </div>
                                        <p class="mb-1"><?= htmlspecialchars($notif['pesan']) ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <span class="badge badge-<?= 
                                                    $notif['tipe'] == 'member_baru' ? 'success' : 
                                                    ($notif['tipe'] == 'transaksi' ? 'info' : 'primary')
                                                ?>">
                                                    <?= ucfirst($notif['tipe']) ?>
                                                </span>
                                            </small>
                                            <small class="text-<?= $notif['dibaca'] ? 'muted' : 'success' ?>">
                                                <i class="fas fa-circle fa-xs"></i>
                                                <?= $notif['dibaca'] ? 'Dibaca' : 'Baru' ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include '../../../view/master/footer.php'; ?>