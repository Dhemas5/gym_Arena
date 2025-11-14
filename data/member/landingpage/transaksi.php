<?php
session_start();
require "../../../setting/koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['user_type'] !== 'member') {
    header("Location: ../login/login.php");
    exit;
}

$id_member = $_SESSION['id_member'];
$nama_member = $_SESSION['nama'];

// Query untuk mengambil semua transaksi member
$query = "SELECT * FROM tbl_transaksi 
          WHERE id_member = ? 
          ORDER BY tanggal_transaksi DESC";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $id_member);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Member - Arena FIT</title>
    <link rel="stylesheet" href="assets/css/stylemember.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%);
            min-height: 100vh;
            color: white;
        }
        
        .navbar {
            background: rgba(13, 27, 42, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(66, 165, 245, 0.2);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            font-weight: 700;
            text-decoration: none;
        }
        
        .brand-box {
            background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            color: white;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            color: #42a5f5 !important;
        }
        
        .member-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .container-main {
            padding: 100px 20px 50px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
        }
        
        .page-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
        }
        
        .transaction-card {
            background: rgba(13, 27, 42, 0.9);
            border: 1px solid rgba(66, 165, 245, 0.2);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .transaction-card:hover {
            transform: translateY(-3px);
            border-color: #42a5f5;
            box-shadow: 0 10px 30px rgba(66, 165, 245, 0.3);
        }
        
        .transaction-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(66, 165, 245, 0.2);
        }
        
        .transaction-id {
            font-size: 1.2rem;
            font-weight: 700;
            color: #42a5f5;
        }
        
        .status-badge {
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending { 
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
        }
        
        .status-approved { 
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid #4caf50;
        }
        
        .status-rejected { 
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 1px solid #f44336;
        }
        
        .transaction-body {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            color: white;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .transaction-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid rgba(66, 165, 245, 0.2);
        }
        
        .price-tag {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffc107;
        }
        
        .btn-detail {
            background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(66, 165, 245, 0.4);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        
        .empty-icon {
            font-size: 5rem;
            color: rgba(66, 165, 245, 0.3);
            margin-bottom: 20px;
        }
        
        .empty-text {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 30px;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(66, 165, 245, 0.4);
            color: white;
        }
        
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            .transaction-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .transaction-footer {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="indexmemberr.php">
        <span class="brand-box">AF</span>
        <div>
          <span style="font-size: 1.2rem;">Arena FIT</span>
        </div>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="indexmemberr.php">Home</a></li>
          <li class="nav-item"><a class="nav-link active" href="transaksi.php">Transaksi</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
        </ul>
        
        <div class="member-info ms-3">
          <div class="member-avatar">
            <?php echo strtoupper(substr($nama_member, 0, 1)); ?>
          </div>
          <span class="welcome-text">
            <span class="member-name"><?php echo htmlspecialchars($nama_member); ?></span>
          </span>
          <a href="../login/logout.php" class="btn-logout">
            <span>🚪</span> Logout
          </a>
        </div>
      </div>
    </div>
  </nav>
    <!-- MAIN CONTENT -->
    <div class="container container-main">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-receipt"></i> Riwayat <span style="color: #42a5f5;">Transaksi</span>
            </h1>
            <p class="page-subtitle">Lihat semua transaksi dan status pembayaran Anda</p>
        </div>

        <!-- Transaction List -->
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="transaction-card">
                    <div class="transaction-header">
                        <div class="transaction-id">
                            <i class="fas fa-file-invoice"></i> 
                            #TRX-<?php echo str_pad($row['id_transaksi'], 6, '0', STR_PAD_LEFT); ?>
                        </div>
                        <span class="status-badge status-<?php echo $row['status']; ?>">
                            <?php 
                            switch($row['status']) {
                                case 'pending':
                                    echo '<i class="fas fa-clock"></i> Pending';
                                    break;
                                case 'approved':
                                    echo '<i class="fas fa-check-circle"></i> Approved';
                                    break;
                                case 'rejected':
                                    echo '<i class="fas fa-times-circle"></i> Rejected';
                                    break;
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div class="transaction-body">
                        <div class="info-item">
                            <span class="info-label">Tanggal</span>
                            <span class="info-value">
                                <?php 
                                $date = new DateTime($row['tanggal_transaksi']);
                                echo $date->format('d M Y');
                                ?>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Paket</span>
                            <span class="info-value"><?php echo htmlspecialchars($row['nama_paket']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Kategori</span>
                            <span class="info-value text-capitalize"><?php echo htmlspecialchars($row['kategori']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Metode</span>
                            <span class="info-value text-capitalize"><?php echo htmlspecialchars($row['metode_pembayaran']); ?></span>
                        </div>
                    </div>
                    
                    <div class="transaction-footer">
                        <div class="price-tag">
                            <i class="fas fa-tag"></i> Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>
                        </div>
                        <a href="detail_transaksi.php?id=<?php echo $row['id_transaksi']; ?>" class="btn-detail">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <div class="empty-text">
                    Belum ada transaksi
                </div>
                <a href="indexmemberr.php" class="btn-primary-custom">
                    <i class="fas fa-shopping-cart"></i> Mulai member
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$con->close();
?>