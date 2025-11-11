<?php
session_start();
require "../../../setting/koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['user_type'] !== 'member') {
    header("Location: ../login/login.php");
    exit;
}

$nama_member = $_SESSION['nama'];
$id_transaksi = isset($_GET['id']) ? $_GET['id'] : 0;

// Ambil data transaksi
$transaksi_data = null;
if ($id_transaksi > 0) {
    $query = "SELECT t.*, m.nama as nama_member, m.email 
              FROM tbl_transaksi t 
              LEFT JOIN tbl_member m ON t.id_member = m.id_member 
              WHERE t.id_transaksi = ? AND t.id_member = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ii", $id_transaksi, $_SESSION['id_member']);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaksi_data = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pembayaran Berhasil - Arena FIT</title>

  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%);
      min-height: 100vh;
      padding-top: 80px;
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

    .success-container {
      max-width: 700px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .success-card {
      background: rgba(13, 27, 42, 0.9);
      border: 2px solid rgba(76, 175, 80, 0.3);
      border-radius: 20px;
      padding: 50px 40px;
      text-align: center;
    }

    .success-icon {
      width: 100px;
      height: 100px;
      margin: 0 auto 30px;
      background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: scaleIn 0.5s ease-out;
    }

    @keyframes scaleIn {
      0% {
        transform: scale(0);
      }
      50% {
        transform: scale(1.1);
      }
      100% {
        transform: scale(1);
      }
    }

    .success-icon svg {
      width: 50px;
      height: 50px;
      color: white;
    }

    .success-title {
      font-size: 2rem;
      font-weight: 700;
      color: white;
      margin-bottom: 15px;
    }

    .success-subtitle {
      color: rgba(255, 255, 255, 0.7);
      margin-bottom: 40px;
      font-size: 1.1rem;
    }

    .transaction-info {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(66, 165, 245, 0.2);
      border-radius: 15px;
      padding: 30px;
      margin-bottom: 30px;
      text-align: left;
    }

    .info-title {
      font-size: 1.2rem;
      font-weight: 700;
      color: #42a5f5;
      margin-bottom: 20px;
      text-align: center;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .info-row:last-child {
      border-bottom: none;
    }

    .info-label {
      color: rgba(255, 255, 255, 0.7);
      font-weight: 500;
    }

    .info-value {
      color: white;
      font-weight: 600;
      text-align: right;
    }

    .status-badge {
      display: inline-block;
      padding: 8px 20px;
      border-radius: 20px;
      font-weight: 700;
      font-size: 0.9rem;
      text-transform: uppercase;
    }

    .status-pending {
      background: rgba(255, 193, 7, 0.2);
      color: #ffc107;
      border: 1px solid #ffc107;
    }

    .alert-info {
      background: rgba(66, 165, 245, 0.1);
      border: 1px solid rgba(66, 165, 245, 0.3);
      color: rgba(255, 255, 255, 0.9);
      border-radius: 10px;
      padding: 20px;
      margin: 30px 0;
      text-align: left;
    }

    .alert-info strong {
      color: #42a5f5;
      display: block;
      margin-bottom: 10px;
    }

    .btn-primary {
      background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
      border: none;
      color: white;
      padding: 15px 40px;
      border-radius: 10px;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s;
      margin: 10px;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(66, 165, 245, 0.4);
      color: white;
    }

    .btn-secondary {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: white;
      padding: 15px 40px;
      border-radius: 10px;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s;
      margin: 10px;
    }

    .btn-secondary:hover {
      background: rgba(255, 255, 255, 0.2);
      color: white;
    }

    @media (max-width: 768px) {
      .success-card {
        padding: 30px 20px;
      }
      
      .success-title {
        font-size: 1.5rem;
      }
      
      .info-row {
        flex-direction: column;
        gap: 5px;
      }
      
      .info-value {
        text-align: left;
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
    </div>
  </nav>

  <!-- SUCCESS CONTENT -->
  <div class="success-container">
    <div class="success-card">
      <div class="success-icon">
        <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
          <path d="M5 13l4 4L19 7" />
        </svg>
      </div>
      
      <h1 class="success-title">Pembayaran Berhasil!</h1>
      <p class="success-subtitle">Terima kasih telah melakukan pembayaran</p>

      <?php if ($transaksi_data): ?>
      <div class="transaction-info">
        <div class="info-title">📋 Detail Transaksi</div>
        
        <div class="info-row">
          <span class="info-label">ID Transaksi</span>
          <span class="info-value">#<?php echo str_pad($transaksi_data['id_transaksi'], 6, '0', STR_PAD_LEFT); ?></span>
        </div>
        
        <div class="info-row">
          <span class="info-label">Tanggal</span>
          <span class="info-value"><?php echo date('d M Y, H:i', strtotime($transaksi_data['tanggal_transaksi'])); ?> WIB</span>
        </div>
        
        <div class="info-row">
          <span class="info-label">Nama Paket</span>
          <span class="info-value"><?php echo htmlspecialchars($transaksi_data['nama_paket']); ?></span>
        </div>
        
        <div class="info-row">
          <span class="info-label">Kategori</span>
          <span class="info-value"><?php echo htmlspecialchars($transaksi_data['kategori']); ?></span>
        </div>
        
        <?php if(strpos($transaksi_data['notes'], 'Pelajar/Mahasiswa') !== false): ?>
        <div class="info-row">
          <span class="info-label">Tipe Member</span>
          <span class="info-value" style="color: #4caf50;">🎓 Pelajar / Mahasiswa</span>
        </div>
        <?php endif; ?>
        
        <div class="info-row">
          <span class="info-label">Total Pembayaran</span>
          <span class="info-value" style="color: #ffc107; font-size: 1.2rem;">
            Rp <?php echo number_format($transaksi_data['harga'], 0, ',', '.'); ?>
          </span>
        </div>
        
        <div class="info-row">
          <span class="info-label">Status</span>
          <span class="info-value">
            <span class="status-badge status-pending">Menunggu Verifikasi</span>
          </span>
        </div>
      </div>
      <?php endif; ?>

      <div class="alert-info">
        <strong>ℹ️ Informasi Penting:</strong>
        <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
          <li>Pembayaran Anda sedang dalam proses verifikasi</li>
          <li>Admin akan memverifikasi pembayaran dalam 1x24 jam</li>
          <li>Anda akan menerima notifikasi setelah pembayaran diverifikasi</li>
          <li>Jika ada kendala, hubungi kami di WhatsApp: 0821-4308-0510</li>
        </ul>
      </div>

      <div>
        <a href="riwayat_transaksi.php" class="btn-primary">Lihat Riwayat Transaksi</a>
        <a href="indexmemberr.php" class="btn-secondary">Kembali ke Beranda</a>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>