<?php
require "../../../setting/session.php";
require "../../../setting/koneksi.php";
checkSession("admin");

// Query notifikasi
$sql = "SELECT * FROM tbl_notifikasi ORDER BY id DESC LIMIT 10";

if (is_object($con)) {
    $queryNotif = $con->query($sql);
    $jumlahNotif = $queryNotif ? $queryNotif->num_rows : 0;
} else {
    $queryNotif = mysqli_query($con, $sql);
    $jumlahNotif = $queryNotif ? mysqli_num_rows($queryNotif) : 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Notifikasi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/adminlte.min.css">
    <script src="../../../assets/assets_admin/plugins/jquery/jquery.min.js"></script>
    <script src="../../../assets/assets_admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body style="padding: 20px;">
    <h2>Test Notifikasi - Total: <?= $jumlahNotif ?></h2>
    
    <div class="dropdown" style="display: inline-block;">
        <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
            <i class="fas fa-bell"></i> Notifikasi (<?= $jumlahNotif ?>)
        </button>
        <div class="dropdown-menu" style="width: 300px;">
            <h6 class="dropdown-header"><?= $jumlahNotif ?> Notifikasi</h6>
            <div class="dropdown-divider"></div>
            
            <?php if ($queryNotif && $jumlahNotif > 0): ?>
                <?php while ($notif = is_object($con) ? $queryNotif->fetch_assoc() : mysqli_fetch_assoc($queryNotif)): ?>
                    <a class="dropdown-item" href="#">
                        <strong><?= htmlspecialchars($notif['tipe']) ?></strong><br>
                        <small><?= htmlspecialchars($notif['pesan']) ?></small>
                    </a>
                    <div class="dropdown-divider"></div>
                <?php endwhile; ?>
            <?php else: ?>
                <a class="dropdown-item" href="#">Tidak ada notifikasi</a>
            <?php endif; ?>
        </div>
    </div>
    
    <hr>
    <h3>Data Notifikasi dari Database:</h3>
    <?php
    // Reset pointer
    if (is_object($con)) {
        $queryNotif2 = $con->query($sql);
    } else {
        $queryNotif2 = mysqli_query($con, $sql);
    }
    
    if ($queryNotif2 && (is_object($con) ? $queryNotif2->num_rows : mysqli_num_rows($queryNotif2)) > 0):
        while ($row = is_object($con) ? $queryNotif2->fetch_assoc() : mysqli_fetch_assoc($queryNotif2)):
    ?>
        <div style="border: 1px solid #ddd; padding: 10px; margin: 10px 0;">
            <strong>ID:</strong> <?= $row['id'] ?><br>
            <strong>Tipe:</strong> <?= $row['tipe'] ?><br>
            <strong>Pesan:</strong> <?= $row['pesan'] ?><br>
            <strong>Waktu:</strong> <?= $row['dibuat_pada'] ?>
        </div>
    <?php 
        endwhile;
    else:
    ?>
        <p>Tidak ada data di tbl_notifikasi</p>
    <?php endif; ?>
</body>
</html>