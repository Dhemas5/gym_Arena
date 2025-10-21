<?php
session_start();
// header page
include '../../../view/master_member/header.php';
// Check if user is logged in
if (!isset($_SESSION['id_member'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$host = 'localhost';
$db = 'db_gym';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get all categories
$stmt = $pdo->query("SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected category (default to first)
$selected_category = isset($_GET['kategori']) ? (int)$_GET['kategori'] : (count($categories) > 0 ? $categories[0]['id_kategori'] : null);

// Get schedules for selected category
$schedules = [];
$instructors = [];
if ($selected_category) {
    $stmt = $pdo->prepare("
        SELECT jk.*, k.nama_kategori, i.nama_instruktur, i.spesialisasi, i.foto
        FROM tbl_jadwal_kelas jk
        JOIN tbl_kategori k ON jk.id_kategori = k.id_kategori
        JOIN tbl_instruktur i ON jk.id_instruktur = i.id_instruktur
        WHERE jk.id_kategori = ?
        ORDER BY jk.tanggal ASC, jk.jam_mulai ASC
    ");
    $stmt->execute([$selected_category]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get member info
$stmt = $pdo->prepare("SELECT * FROM tbl_member WHERE id_member = ?");
$stmt->execute([$_SESSION['id_member']]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Latihan - Arena Gym Fit Club</title>
    <link rel="stylesheet" href="../../../assets/assets_member/css/custom-member.css">
    <link rel="stylesheet" href="../../../assets/assets_member/css/custom-program.css">
</head>
<body>
<div class="content-wrapper">
    <div class="main-content">
        <div class="page-header">
            <h1>Program Latihan</h1>
            <p>Lihat dan ikuti program latihan yang tersedia di gym kami</p>
        </div>
        <div class="category-filter">
            <h3>Pilih Program Latihan</h3>
            <div class="category-buttons">
                <?php foreach ($categories as $category): ?>
                    <a href="?kategori=<?php echo $category['id_kategori']; ?>" 
                       class="category-btn <?php echo $selected_category == $category['id_kategori'] ? 'active' : ''; ?>">
                        <span class="category-name"><?php echo htmlspecialchars($category['nama_kategori']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php if ($selected_category): ?>
            <div class="program-details">
                <?php 
                $category_name = '';
                $category_desc = '';
                foreach ($categories as $cat) {
                    if ($cat['id_kategori'] == $selected_category) {
                        $category_name = $cat['nama_kategori'];
                        $category_desc = $cat['deskripsi'];
                        break;
                    }
                }
                ?>
                <div class="program-header">
                    <h2><?php echo htmlspecialchars($category_name); ?></h2>
                    <p><?php echo htmlspecialchars($category_desc); ?></p>
                </div>
                <div class="schedules-container">
                    <h3>Jadwal Kelas</h3>
                    <?php if (count($schedules) > 0): ?>
                        <div class="schedules-grid">
                            <?php foreach ($schedules as $schedule): ?>
                                <div class="schedule-card">
                                    <div class="schedule-header">
                                        <h4><?php echo htmlspecialchars($schedule['nama_instruktur']); ?></h4>
                                        <span class="specialization"><?php echo htmlspecialchars($schedule['spesialisasi']); ?></span>
                                    </div>
                                    
                                    <div class="schedule-body">
                                        <div class="schedule-info">
                                            <div class="info-item">
                                                <span class="label">Tanggal</span>
                                                <span class="value"><?php echo date('d M Y', strtotime($schedule['tanggal'])); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span class="label">Jam Mulai</span>
                                                <span class="value"><?php echo date('H:i', strtotime($schedule['jam_mulai'])); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span class="label">Jam Selesai</span>
                                                <span class="value"><?php echo date('H:i', strtotime($schedule['jam_selesai'])); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="schedule-footer">
                                        <button class="btn-daftar">Daftar Kelas</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-schedule">
                            <p>Belum ada jadwal kelas untuk program ini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Arena Gym Fit Club. All rights reserved.</p>
    </footer>

    <script src="assets/assets_member/js/navbar.js"></script>
</body>
</html>
