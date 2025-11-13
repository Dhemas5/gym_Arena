<?php
session_start();
require "../../../setting/koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['user_type'] !== 'member') {
    header("Location: ../login/login.php");
    exit;
}

// Ambil nama user dari session
$nama_member = $_SESSION['nama'];
$id_member = $_SESSION['id_member'];

// Ambil data paket dari tbl_paket
$paket_query = "SELECT * FROM tbl_paket ORDER BY harga ASC";
$paket_result = $con->query($paket_query);

// Ambil data kelas dari tbl_jadwal_kelas dengan join ke tbl_kategori dan tbl_instruktur
$kelas_query = "SELECT jk.*, k.nama_kategori, k.deskripsi as kategori_desc, i.nama_instruktur 
                FROM tbl_jadwal_kelas jk 
                LEFT JOIN tbl_kategori k ON jk.id_kategori = k.id_kategori 
                LEFT JOIN tbl_instruktur i ON jk.id_instruktur = i.id_instruktur
                ORDER BY jk.tanggal, jk.jam_mulai";
$kelas_result = $con->query($kelas_query);

// Data paket gym dari price list
$gym_packages = [
    [
        'id' => 'gym_harian',
        'nama' => 'Gym Harian',
        'badge' => 'GYM HARIAN',
        'harga_umum' => 60000,
        'harga_pelajar' => null,
        'durasi' => '1 Hari',
        'featured' => false
    ],
    [
        'id' => 'gym_1bulan',
        'nama' => 'Gym 1 Bulan',
        'badge' => 'GYM BULANAN',
        'harga_umum' => 285000,
        'harga_pelajar' => 200000,
        'durasi' => '30 Hari',
        'featured' => false
    ],
    [
        'id' => 'gym_3bulan',
        'nama' => 'Gym 3 Bulan',
        'badge' => 'GYM 3 BULAN',
        'harga_umum' => 675000,
        'harga_pelajar' => 550000,
        'durasi' => '90 Hari',
        'featured' => true
    ],
    [
        'id' => 'gym_6bulan',
        'nama' => 'Gym 6 Bulan',
        'badge' => 'GYM 6 BULAN',
        'harga_umum' => 1250000,
        'harga_pelajar' => 1000000,
        'durasi' => '180 Hari',
        'featured' => true
    ],
    [
        'id' => 'gym_1tahun',
        'nama' => 'Gym 1 Tahun',
        'badge' => 'GYM 1 TAHUN',
        'harga_umum' => 2300000,
        'harga_pelajar' => 1850000,
        'durasi' => '365 Hari',
        'featured' => true
    ]
];

// Data kelas per kunjungan
$class_prices = [
    [
        'nama' => 'ZUMBA, AERO BL, STRONG NATION',
        'harga' => 20000,
        'id' => 'kelas_20k'
    ],
    [
        'nama' => 'CID, BODY SHAPE, SENAM BL',
        'harga' => 25000,
        'id' => 'kelas_25k'
    ],
    [
        'nama' => 'BOXING, KAPHA YOGA',
        'harga' => 30000,
        'id' => 'kelas_30k'
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Arena FIT - Member Dashboard</title>

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
    }

    /* Navbar Styles */
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
    
    .welcome-text .member-name {
      font-weight: 700;
      color: #42a5f5;
    }

    /* Hero Section */
    .hero {
      min-height: 70vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 100px 20px 50px;
    }

    .hero h1 {
      font-size: 3.5rem;
      font-weight: 700;
      margin-bottom: 20px;
      color: white;
    }

    .hero p {
      font-size: 1.3rem;
      color: rgba(255, 255, 255, 0.7);
      margin-bottom: 40px;
    }

    .text-danger {
      color: #42a5f5;
    }

    /* Features/Stats Section */
    .features {
      padding: 80px 0;
      background: rgba(13, 27, 42, 0.5);
    }

    .feature-box {
      background: rgba(13, 27, 42, 0.9);
      border: 1px solid rgba(66, 165, 245, 0.2);
      border-radius: 20px;
      padding: 40px 30px;
      text-align: center;
      transition: all 0.3s;
      height: 100%;
    }

    .feature-box:hover {
      transform: translateY(-10px);
      border-color: #42a5f5;
      box-shadow: 0 15px 40px rgba(66, 165, 245, 0.3);
    }

    .feature-icon {
      width: 70px;
      height: 70px;
      margin: 0 auto 25px;
      background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
    }

    .feature-box h3 {
      font-size: 2.5rem;
      font-weight: 700;
      color: #42a5f5;
      margin-bottom: 10px;
    }

    .feature-box p {
      color: rgba(255, 255, 255, 0.7);
      font-size: 1rem;
      margin: 0;
    }

    /* Price List Section */
    .pricelist-section {
      padding: 80px 0;
      background: rgba(13, 27, 42, 0.5);
    }

    .pricelist-header {
      text-align: center;
      margin-bottom: 60px;
    }

    .pricelist-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: white;
      margin-bottom: 15px;
    }

    .pricelist-title .highlight {
      color: #ffc107;
    }

    .pricelist-subtitle {
      color: rgba(255, 255, 255, 0.7);
      font-size: 1.1rem;
    }

    .price-category-title {
      font-size: 2rem;
      font-weight: 700;
      color: white;
      margin-bottom: 30px;
      padding-left: 20px;
      border-left: 5px solid #ffc107;
    }

    /* Gym Packages Grid */
    .gym-packages-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
      margin-bottom: 60px;
    }

    .gym-package-card {
      background: linear-gradient(135deg, rgba(139, 69, 19, 0.3) 0%, rgba(101, 67, 33, 0.4) 100%);
      border: 2px solid rgba(255, 193, 7, 0.3);
      border-radius: 20px;
      padding: 30px;
      position: relative;
      transition: all 0.3s ease;
    }

    .gym-package-card:hover {
      transform: translateY(-5px);
      border-color: #ffc107;
      box-shadow: 0 15px 40px rgba(255, 193, 7, 0.3);
    }

    .gym-package-card.featured {
      border-color: #ffc107;
      box-shadow: 0 10px 30px rgba(255, 193, 7, 0.2);
    }

    .package-badge {
      position: absolute;
      top: 20px;
      right: 20px;
      background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
      color: #1b263b;
      padding: 8px 20px;
      border-radius: 20px;
      font-weight: 700;
      font-size: 0.85rem;
      text-transform: uppercase;
    }

    .gift-icon {
      position: absolute;
      top: 15px;
      left: 15px;
      font-size: 2rem;
    }

    .gym-package-name {
      font-size: 1.4rem;
      font-weight: 700;
      color: #ffc107;
      margin-bottom: 20px;
      text-transform: uppercase;
    }

    .price-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
      padding: 12px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .price-row:hover {
      background: rgba(255, 193, 7, 0.1);
      transform: translateX(5px);
    }

    .price-label {
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.9rem;
    }

    .price-value {
      color: white;
      font-weight: 700;
      font-size: 1.1rem;
    }

    .btn-buy-package {
      background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
      border: none;
      color: #1b263b;
      padding: 12px 25px;
      border-radius: 10px;
      font-weight: 700;
      text-decoration: none;
      display: block;
      text-align: center;
      transition: all 0.3s;
      width: 100%;
      margin-top: 15px;
      cursor: pointer;
    }

    .btn-buy-package:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(255, 193, 7, 0.4);
      color: #1b263b;
    }

    /* Class Prices Grid */
    .class-prices-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }

    .class-price-card {
      background: linear-gradient(135deg, rgba(139, 69, 19, 0.25) 0%, rgba(101, 67, 33, 0.35) 100%);
      border: 2px solid rgba(255, 193, 7, 0.25);
      border-radius: 15px;
      padding: 25px;
      text-align: center;
      transition: all 0.3s ease;
    }

    .class-price-card:hover {
      transform: translateY(-3px);
      border-color: #ffc107;
      box-shadow: 0 10px 25px rgba(255, 193, 7, 0.2);
    }

    .class-price-name {
      font-size: 1.1rem;
      font-weight: 700;
      color: white;
      margin-bottom: 15px;
    }

    .class-price-amount {
      font-size: 1.8rem;
      font-weight: 700;
      color: #ffc107;
    }

    .class-price-label {
      font-size: 0.85rem;
      color: rgba(255, 255, 255, 0.6);
      margin-top: 5px;
      margin-bottom: 15px;
    }

    /* Monthly Class Package */
    .monthly-class-box {
      background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 152, 0, 0.15) 100%);
      border: 2px solid #ffc107;
      border-radius: 20px;
      padding: 30px;
      margin-bottom: 60px;
      text-align: center;
    }

    .monthly-class-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: #ffc107;
      margin-bottom: 10px;
      text-transform: uppercase;
    }

    .monthly-class-subtitle {
      color: rgba(255, 255, 255, 0.7);
      margin-bottom: 20px;
    }

    .monthly-class-price {
      font-size: 2.5rem;
      font-weight: 700;
      color: white;
      margin-bottom: 10px;
    }

    /* Trainer Program Box */
    .trainer-program-box {
      background: linear-gradient(135deg, rgba(244, 67, 54, 0.2) 0%, rgba(211, 47, 47, 0.25) 100%);
      border: 3px solid #f44336;
      border-radius: 25px;
      padding: 40px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .trainer-program-box::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(244, 67, 54, 0.1) 0%, transparent 70%);
      animation: pulse 3s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 0.5; }
      50% { transform: scale(1.1); opacity: 0.8; }
    }

    .trainer-badge {
      background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
      color: white;
      padding: 10px 25px;
      border-radius: 25px;
      font-weight: 700;
      font-size: 1.2rem;
      display: inline-block;
      margin-bottom: 20px;
      text-transform: uppercase;
      position: relative;
      z-index: 1;
    }

    .trainer-program-box h3 {
      font-size: 2rem;
      font-weight: 700;
      color: white;
      margin-bottom: 15px;
      position: relative;
      z-index: 1;
    }

    .trainer-price {
      font-size: 3rem;
      font-weight: 700;
      color: #ffc107;
      margin-bottom: 15px;
      position: relative;
      z-index: 1;
    }

    .trainer-details {
      color: rgba(255, 255, 255, 0.9);
      font-size: 1.1rem;
      margin-bottom: 25px;
      position: relative;
      z-index: 1;
    }

    .note-box {
      background: rgba(255, 193, 7, 0.15);
      border-left: 4px solid #ffc107;
      padding: 20px;
      border-radius: 10px;
      text-align: left;
      position: relative;
      z-index: 1;
      margin-bottom: 20px;
    }

    .note-title {
      font-weight: 700;
      color: #ffc107;
      margin-bottom: 10px;
      font-size: 1.1rem;
    }

    .note-text {
      color: rgba(255, 255, 255, 0.8);
      line-height: 1.6;
    }

    .contact-box {
      text-align: center;
      padding: 30px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 15px;
      margin-top: 40px;
    }

    .contact-box h3 {
      color: #ffc107;
      margin-bottom: 15px;
      font-size: 1.5rem;
    }

    .contact-box p {
      color: rgba(255, 255, 255, 0.8);
      margin-bottom: 10px;
      line-height: 1.8;
    }

    /* Kelas Section */
    .kelas-section {
      padding: 80px 0;
      background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%);
    }

    .section-title {
      text-align: center;
      font-size: 2.5rem;
      font-weight: 700;
      color: white;
      margin-bottom: 50px;
    }

    .section-title .text-primary {
      color: #42a5f5;
    }

    /* Schedule Styles */
    .schedule-container {
      display: flex;
      flex-direction: column;
      gap: 40px;
    }

    .day-schedule {
      background: rgba(13, 27, 42, 0.8);
      border: 2px solid rgba(66, 165, 245, 0.2);
      border-radius: 20px;
      padding: 30px;
      transition: all 0.3s;
    }

    .day-schedule:hover {
      border-color: #42a5f5;
      box-shadow: 0 10px 30px rgba(66, 165, 245, 0.2);
    }

    .day-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: #ffc107;
      margin-bottom: 25px;
      text-align: center;
      text-transform: uppercase;
      letter-spacing: 2px;
    }

    .schedule-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 15px;
    }

    .schedule-item {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(66, 165, 245, 0.2);
      border-radius: 12px;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      transition: all 0.3s;
    }

    .schedule-item:hover {
      transform: translateY(-3px);
      border-color: #42a5f5;
      background: rgba(66, 165, 245, 0.1);
      box-shadow: 0 5px 15px rgba(66, 165, 245, 0.2);
    }

    .schedule-item.studio1 {
      border-left: 4px solid #ffc107;
    }

    .schedule-item.studio2 {
      border-left: 4px solid #42a5f5;
    }

    .schedule-item .time {
      font-size: 1.3rem;
      font-weight: 700;
      color: #42a5f5;
    }

    .schedule-item .studio-badge {
      display: inline-block;
      background: rgba(255, 193, 7, 0.2);
      color: #ffc107;
      padding: 4px 12px;
      border-radius: 15px;
      font-size: 0.75rem;
      font-weight: 600;
      width: fit-content;
      text-transform: uppercase;
    }

    .schedule-item.studio2 .studio-badge {
      background: rgba(66, 165, 245, 0.2);
      color: #42a5f5;
    }

    .schedule-item .class-name {
      font-size: 1.1rem;
      font-weight: 700;
      color: white;
      text-transform: uppercase;
    }

    .schedule-item .instructor {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.6);
      font-style: italic;
    }

    /* Modal Styles */
    .modal-content {
      background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%);
      border: 2px solid rgba(66, 165, 245, 0.3);
      border-radius: 20px;
    }

    .modal-header {
      border-bottom: 1px solid rgba(66, 165, 245, 0.2);
    }

    .modal-title {
      color: #ffc107;
      font-weight: 700;
    }

    .modal-body {
      color: rgba(255, 255, 255, 0.9);
    }

    .form-label {
      color: rgba(255, 255, 255, 0.8);
      font-weight: 600;
    }

    .form-control, .form-select {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(66, 165, 245, 0.3);
      color: white;
      border-radius: 10px;
    }

    .form-control:focus, .form-select:focus {
      background: rgba(255, 255, 255, 0.15);
      border-color: #42a5f5;
      color: white;
      box-shadow: 0 0 0 0.25rem rgba(66, 165, 245, 0.25);
    }

    .form-select option {
      background: #1b263b;
      color: white;
    }

    .btn-close {
      filter: invert(1);
    }

    .payment-summary {
      background: rgba(255, 193, 7, 0.1);
      border: 2px solid rgba(255, 193, 7, 0.3);
      border-radius: 15px;
      padding: 20px;
      margin: 20px 0;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      padding-bottom: 10px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .summary-row:last-child {
      border-bottom: none;
      font-size: 1.3rem;
      font-weight: 700;
      color: #ffc107;
    }

    .btn-primary {
      background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
      border: none;
      padding: 12px 30px;
      font-weight: 600;
      border-radius: 10px;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(66, 165, 245, 0.4);
    }

    .btn-secondary {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: white;
      padding: 12px 30px;
      font-weight: 600;
      border-radius: 10px;
    }

    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2rem;
      }
      .member-info {
        margin-top: 15px;
      }
      .pricelist-title {
        font-size: 1.8rem;
      }
      .price-category-title {
        font-size: 1.5rem;
      }
      .trainer-price {
        font-size: 2.5rem;
      }
      .gym-packages-grid,
      .class-prices-grid {
        grid-template-columns: 1fr;
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
          <li class="nav-item"><a class="nav-link active" href="indexmemberr.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="transaksi.php">Transaksi</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
        </ul>
        
        <div class="member-info ms-3">
          <div class="member-avatar">
            <?php echo strtoupper(substr($nama_member, 0, 1)); ?>
          </div>
          <span class="welcome-text">
            <span class="member-name"><?php echo htmlspecialchars($nama_member); ?></span>
          </span>
          <a href="../login/logout.php" class="btn btn-danger ms-2">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- HERO SECTION -->
  <section id="home" class="hero">
    <div class="hero-content">
      <h1>
        <span style="color: white;">Selamat Datang,</span>
        <br>
        <span class="text-danger"><?php echo htmlspecialchars($nama_member); ?>!</span>
      </h1>
      <p>Mulai perjalanan fitness Anda dengan kami</p>
    </div>
  </section>

  <!-- DASHBOARD STATS -->
  <section class="features">
    <div class="container">
      <div class="row g-4 justify-content-center">
        <div class="col-lg-4 col-md-6">
          <div class="feature-box">
            <div class="feature-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
              </svg>
            </div>
            <h3>Active</h3>
            <p>Status Member</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- PRICE LIST SECTION -->
  <section class="pricelist-section">
    <div class="container">
      <!-- Header -->
      <div class="pricelist-header">
        <h2 class="pricelist-title">GYM <span class="highlight">PRICE LIST</span></h2>
        <p class="pricelist-subtitle">Pilih Paket Terbaik untuk Perjalanan Fitness Anda</p>
      </div>

      <!-- Gym Packages -->
      <h3 class="price-category-title">📅 Paket Membership Gym</h3>
      <div class="gym-packages-grid">
        <?php foreach($gym_packages as $package): ?>
        <div class="gym-package-card <?php echo $package['featured'] ? 'featured' : ''; ?>">
          <?php if($package['featured']): ?>
          <div class="gift-icon">🎁</div>
          <?php endif; ?>
          <div class="package-badge"><?php echo $package['badge']; ?></div>
          <div class="gym-package-name"><?php echo $package['nama']; ?></div>
          
          <?php if($package['harga_pelajar']): ?>
            <div class="price-row" onclick="openPaymentModal('<?php echo $package['id']; ?>', '<?php echo $package['nama']; ?>', <?php echo $package['harga_umum']; ?>, 'umum', '<?php echo $package['durasi']; ?>')">
              <span class="price-label">UMUM</span>
              <span class="price-value">Rp <?php echo number_format($package['harga_umum'], 0, ',', '.'); ?></span>
            </div>
            <div class="price-row" onclick="openPaymentModal('<?php echo $package['id']; ?>', '<?php echo $package['nama']; ?>', <?php echo $package['harga_pelajar']; ?>, 'pelajar', '<?php echo $package['durasi']; ?>')">
              <span class="price-label">PELAJAR / MAHASISWA</span>
              <span class="price-value">Rp <?php echo number_format($package['harga_pelajar'], 0, ',', '.'); ?></span>
            </div>
          <?php else: ?>
            <div class="price-row" onclick="openPaymentModal('<?php echo $package['id']; ?>', '<?php echo $package['nama']; ?>', <?php echo $package['harga_umum']; ?>, 'umum', '<?php echo $package['durasi']; ?>')">
              <span class="price-label">Harga</span>
              <span class="price-value">Rp <?php echo number_format($package['harga_umum'], 0, ',', '.'); ?></span>
            </div>
          <?php endif; ?>
          
          <a href="checkout.php?package_id=<?php echo $package['id']; ?>&type=custom&member_type=umum" class="btn-buy-package">
            Beli Paket Ini
          </a>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Class Prices Per Visit -->
      <h3 class="price-category-title">💪 Harga Kelas Per Datang</h3>
      <div class="class-prices-grid">
        <?php foreach($class_prices as $class): ?>
        <div class="class-price-card">
          <div class="class-price-name"><?php echo $class['nama']; ?></div>
          <div class="class-price-amount">Rp <?php echo number_format($class['harga'], 0, ',', '.'); ?></div>
          <div class="class-price-label">per sesi</div>
          <button class="btn-buy-package" onclick="openPaymentModal('<?php echo $class['id']; ?>', '<?php echo $class['nama']; ?>', <?php echo $class['harga']; ?>, 'umum', '1 Sesi')">
            Beli Sekarang
          </button>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Monthly Class Package -->
      <div class="monthly-class-box">
        <div class="monthly-class-title">🥊 Kelas 1 Bulan</div>
        <div class="monthly-class-subtitle">Paket Khusus Boxing</div>
        <div class="monthly-class-price">Rp 300.000</div>
        <div class="class-price-label">Unlimited Boxing untuk 1 bulan</div>
        <button class="btn-buy-package" style="max-width: 300px; margin: 20px auto 0;" onclick="openPaymentModal('boxing_1bulan', 'Boxing 1 Bulan', 300000, 'umum', '30 Hari')">
          Beli Paket Boxing
        </button>
      </div>

      <!-- Trainer Program -->
      <h3 class="price-category-title">🏋️ Program Premium</h3>
      <div class="trainer-program-box">
        <div class="trainer-badge">PROGRAM TRAINER</div>
        <h3>Personal Training Program</h3>
        <div class="trainer-price">Rp 1.500.000</div>
        <div class="trainer-details">
          (10X PERTEMUAN + GYM 1 BULAN + BOXING 4X)
        </div>
        <div class="note-box">
          <div class="note-title">📌 NOTED</div>
          <div class="note-text">
            <strong>Khusus Pelajar / Mahasiswa</strong><br>
            Wajib menunjukkan Kartu Pendukung (KTM/Kartu Pelajar) saat registrasi untuk mendapatkan harga spesial.
          </div>
        </div>
        <button class="btn-buy-package" style="max-width: 400px; margin: 0 auto; position: relative; z-index: 1;" onclick="openPaymentModal('program_trainer', 'Program Trainer', 1500000, 'umum', 'Paket Lengkap')">
          Daftar Program Trainer
        </button>
      </div>

      <!-- Contact Info -->
      <div class="contact-box">
        <h3>📍 Hubungi Kami</h3>
        <p>
          <strong>BCA:</strong> 2009138999<br>
          <strong>AN:</strong> CV. ARENA MAJU BERSAMA
        </p>
        <p>
          <strong>WhatsApp:</strong> 0821-4308-0510
        </p>
        <p>
          <strong>Instagram:</strong> @arenafitclub2022
        </p>
      </div>
    </div>
  </section>

  <!-- JADWAL KELAS MINGGUAN SECTION -->
  <section class="kelas-section">
    <div class="container">
      <h2 class="section-title">Jadwal <span class="text-primary">Kelas Mingguan</span></h2>
      
      <!-- Jadwal per Hari -->
      <div class="schedule-container">
        
        <!-- SENIN -->
        <div class="day-schedule">
          <h3 class="day-title">SENIN</h3>
          <div class="schedule-grid">
            <div class="schedule-item studio1">
              <span class="time">07:00</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">SENAM BL</span>
              <span class="instructor">COACH FITRI</span>
            </div>
            <div class="schedule-item">
              <span class="time">08:00</span>
              <span class="class-name">BOXING</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">08:30</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">ZUMBA</span>
              <span class="instructor">ZIN IRA</span>
            </div>
            <div class="schedule-item studio2">
              <span class="time">08:30</span>
              <span class="studio-badge">STUDIO 2</span>
              <span class="class-name">BODY SHAPE</span>
              <span class="instructor">COACH MIEKE</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">16:15</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">ZUMBA</span>
              <span class="instructor">ZIN SARI</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">19:00</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">ZUMBA</span>
              <span class="instructor">ZIN INA</span>
            </div>
            <div class="schedule-item">
              <span class="time">19:00</span>
              <span class="class-name">BOXING</span>
            </div>
          </div>
        </div>

        <!-- SELASA -->
        <div class="day-schedule">
          <h3 class="day-title">SELASA</h3>
          <div class="schedule-grid">
            <div class="schedule-item studio1">
              <span class="time">08:30</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">ZUMBA</span>
              <span class="instructor">ZIN NILA</span>
            </div>
            <div class="schedule-item studio2">
              <span class="time">08:15</span>
              <span class="studio-badge">STUDIO 2</span>
              <span class="class-name">CID ROCKER</span>
              <span class="instructor">SISKA</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">16:15</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">ZUMBA</span>
              <span class="instructor">ZIN INA</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">19:00</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">STRONG NATION</span>
              <span class="instructor">SYNC NOVA</span>
            </div>
            <div class="schedule-item studio2">
              <span class="time">19:00</span>
              <span class="studio-badge">STUDIO 2</span>
              <span class="class-name">ZUMBA</span>
              <span class="instructor">ZIN SACTA</span>
            </div>
            <div class="schedule-item">
              <span class="time">19:00</span>
              <span class="class-name">BOXING</span>
            </div>
          </div>
        </div>

        <!-- RABU -->
        <div class="day-schedule">
          <h3 class="day-title">RABU</h3>
          <div class="schedule-grid">
            <div class="schedule-item">
              <span class="time">08:00</span>
              <span class="class-name">BOXING</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">08:30</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">ZUMBA</span>
              <span class="instructor">ZIN IRA</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">16:00</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">BODY SHAPE</span>
              <span class="instructor">COACH MIEKE</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">18:30</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">KAPHA YOGA</span>
              <span class="instructor">COACH NANA</span>
            </div>
            <div class="schedule-item studio2">
              <span class="time">19:00</span>
              <span class="studio-badge">STUDIO 2</span>
              <span class="class-name">CID ROCKER</span>
              <span class="instructor">SISKA</span>
            </div>
          </div>
        </div>

        <!-- KAMIS -->
        <div class="day-schedule">
          <h3 class="day-title">KAMIS</h3>
          <div class="schedule-grid">
            <div class="schedule-item">
              <span class="time">08:00</span>
              <span class="class-name">BOXING</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">08:30</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">BODY SHAPE</span>
              <span class="instructor">COACH MIEKE</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">16:00</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">ZUMBA</span>
              <span class="instructor">ZIN INA</span>
            </div>
            <div class="schedule-item studio2">
              <span class="time">16:00</span>
              <span class="studio-badge">STUDIO 2</span>
              <span class="class-name">AERO BL</span>
              <span class="instructor">COACH WIWIK</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">19:00</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">ZUMBA</span>
              <span class="instructor">ZIN SACTA</span>
            </div>
            <div class="schedule-item">
              <span class="time">19:00</span>
              <span class="class-name">BOXING</span>
            </div>
            <div class="schedule-item studio2">
              <span class="time">16:00</span>
              <span class="studio-badge">STUDIO 2</span>
              <span class="class-name">POUNDFIT PP</span>
              <span class="instructor">NILA</span>
            </div>
          </div>
        </div>

        <!-- JUM'AT -->
        <div class="day-schedule">
          <h3 class="day-title">JUM'AT</h3>
          <div class="schedule-grid">
            <div class="schedule-item studio1">
              <span class="time">07:00</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">SENAM BL</span>
              <span class="instructor">COACH FITRI</span>
            </div>
            <div class="schedule-item studio2">
              <span class="time">07:45</span>
              <span class="studio-badge">STUDIO 2</span>
              <span class="class-name">POUNDFIT</span>
              <span class="instructor">BERNI</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">16:00</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">KAPHA YOGA</span>
              <span class="instructor">COACH NANA</span>
            </div>
            <div class="schedule-item studio2">
              <span class="time">16:00</span>
              <span class="studio-badge">STUDIO 2</span>
              <span class="class-name">POUNDFIT PP</span>
              <span class="instructor">NILA</span>
            </div>
            <div class="schedule-item studio2">
              <span class="time">18:30</span>
              <span class="studio-badge">STUDIO 2</span>
              <span class="class-name">TRAMPOLINE</span>
              <span class="instructor">COACH NANA</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">19:00</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">ZUMBA</span>
              <span class="instructor">ZIN INA</span>
            </div>
            <div class="schedule-item">
              <span class="time">19:00</span>
              <span class="class-name">BOXING</span>
            </div>
          </div>
        </div>

        <!-- SABTU -->
        <div class="day-schedule">
          <h3 class="day-title">SABTU</h3>
          <div class="schedule-grid">
            <div class="schedule-item">
              <span class="time">08:00</span>
              <span class="class-name">BOXING</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">08:00</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">ZUMBA</span>
              <span class="instructor">ZIN INA</span>
            </div>
            <div class="schedule-item studio2">
              <span class="time">16:00</span>
              <span class="studio-badge">STUDIO 2</span>
              <span class="class-name">ZUMBA</span>
              <span class="instructor">ZIN SARI</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">16:15</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">STRONG NATION</span>
              <span class="instructor">SYNC NOVA</span>
            </div>
          </div>
        </div>

        <!-- MINGGU -->
        <div class="day-schedule">
          <h3 class="day-title">MINGGU</h3>
          <div class="schedule-grid">
            <div class="schedule-item studio2">
              <span class="time">07:30</span>
              <span class="studio-badge">STUDIO 2</span>
              <span class="class-name">TRAMPOLINE</span>
              <span class="instructor">COACH NANA</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">08:00</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">ZUMBA</span>
              <span class="instructor">ZIN INA</span>
            </div>
            <div class="schedule-item studio1">
              <span class="time">15:30</span>
              <span class="studio-badge">STUDIO 1</span>
              <span class="class-name">AERO BL</span>
              <span class="instructor">COACH WIWIK</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- Payment Modal -->
  <div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Checkout Pembayaran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="proses_pembayaran.php" method="POST" enctype="multipart/form-data">
          <div class="modal-body">
            <input type="hidden" name="id_member" value="<?php echo $id_member; ?>">
            <input type="hidden" name="package_id" id="package_id">
            <input type="hidden" name="package_type" id="package_type">
            <input type="hidden" name="total_harga" id="total_harga_input">
            
            <div class="mb-3">
              <label class="form-label">Nama Paket</label>
              <input type="text" class="form-control" id="package_name_display" name="nama_paket" readonly>
            </div>

            <div class="mb-3" id="member_type_section">
              <label class="form-label">Tipe Member</label>
              <select class="form-select" id="member_type" name="tipe_member">
                <option value="umum">Umum</option>
                <option value="pelajar">Pelajar / Mahasiswa</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Durasi</label>
              <input type="text" class="form-control" id="duration_display" name="durasi" readonly>
            </div>

            <div class="payment-summary">
              <div class="summary-row">
                <span>Paket</span>
                <span id="summary_package">-</span>
              </div>
              <div class="summary-row">
                <span>Tipe</span>
                <span id="summary_type">-</span>
              </div>
              <div class="summary-row">
                <span>Total Pembayaran</span>
                <span id="total_price_display">Rp 0</span>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Upload Bukti Pembayaran</label>
              <input type="file" class="form-control" name="bukti_pembayaran" accept="image/*" required>
              <small style="color: rgba(255,255,255,0.6);">Transfer ke: BCA 2009138999 AN. CV. ARENA MAJU BERSAMA</small>
            </div>

            <div class="mb-3">
              <label class="form-label">Catatan (Opsional)</label>
              <textarea class="form-control" name="catatan" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    const packages = <?php echo json_encode($gym_packages); ?>;
    const classPackages = <?php echo json_encode($class_prices); ?>;
    
    // Additional packages
    const additionalPackages = {
      'boxing_1bulan': {
        nama: 'Boxing 1 Bulan',
        harga_umum: 300000,
        durasi: '30 Hari'
      },
      'program_trainer': {
        nama: 'Program Trainer',
        harga_umum: 1500000,
        durasi: 'Paket Lengkap'
      }
    };

    function openPackageModal(packageId, packageName, hargaUmum, hargaPelajar, durasi) {
      const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
      
      document.getElementById('package_id').value = packageId;
      document.getElementById('package_type').value = 'gym';
      document.getElementById('package_name_display').value = packageName;
      document.getElementById('duration_display').value = durasi;
      document.getElementById('summary_package').textContent = packageName;
      
      const memberTypeSelect = document.getElementById('member_type');
      const memberTypeSection = document.getElementById('member_type_section');
      
      if(hargaPelajar > 0) {
        memberTypeSection.style.display = 'block';
        memberTypeSelect.onchange = function() {
          updatePrice(this.value === 'umum' ? hargaUmum : hargaPelajar, this.value);
        };
        updatePrice(hargaUmum, 'umum');
      } else {
        memberTypeSection.style.display = 'none';
        updatePrice(hargaUmum, 'umum');
      }
      
      modal.show();
    }

    function openPaymentModal(packageId, packageName, harga, type, durasi) {
      const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
      
      document.getElementById('package_id').value = packageId;
      document.getElementById('package_type').value = 'single';
      document.getElementById('package_name_display').value = packageName;
      document.getElementById('duration_display').value = durasi;
      document.getElementById('summary_package').textContent = packageName;
      document.getElementById('member_type_section').style.display = 'none';
      
      updatePrice(harga, type);
      modal.show();
    }

    function updatePrice(price, type) {
      document.getElementById('total_harga_input').value = price;
      document.getElementById('total_price_display').textContent = 'Rp ' + price.toLocaleString('id-ID');
      document.getElementById('summary_type').textContent = type === 'umum' ? 'Umum' : 'Pelajar/Mahasiswa';
    }
  </script>
</body>
</html>