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

// Data hari dalam bahasa Indonesia
$days = [
    1 => 'Senin',
    2 => 'Selasa', 
    3 => 'Rabu',
    4 => 'Kamis',
    5 => 'Jumat',
    6 => 'Sabtu',
    7 => 'Minggu'
];

$current_day = date('N');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Arena FIT - Member Dashboard</title>

  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/stylemember.css">
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
          <li class="nav-item"><a class="nav-link" href="riwayat_transaksi.php">Transaksi</a></li>
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

  <!-- MEMBER WELCOME SECTION -->
  <section class="member-welcome">
    <div class="container">
      <div class="welcome-card">
        <h1 class="welcome-title">Selamat Datang, <?php echo htmlspecialchars($nama_member); ?>!</h1>
        <p class="welcome-subtitle">Mulai perjalanan fitness Anda dengan kami dan raih tujuan kesehatan Anda</p>
        
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-number">Active</div>
            <div class="stat-label">Status Member</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">0</div>
            <div class="stat-label">Kelas Diikuti</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">0</div>
            <div class="stat-label">Sesi Gym</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">100%</div>
            <div class="stat-label">Progress</div>
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
          <?php endif; ?>
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
          
          <button class="btn-buy-package" onclick="openPaymentModal('<?php echo $package['id']; ?>', '<?php echo $package['nama']; ?>', <?php echo $package['harga_umum']; ?>, 'umum', '<?php echo $package['durasi']; ?>')">
            Beli Paket Ini
          </button>
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
      
      <!-- Schedule Filters -->
      <div class="schedule-filters">
        <button class="filter-btn active" data-filter="all">Semua Kelas</button>
        <button class="filter-btn" data-filter="studio1">Studio 1</button>
        <button class="filter-btn" data-filter="studio2">Studio 2</button>
        <button class="filter-btn" data-filter="boxing">Boxing</button>
      </div>
      
      <!-- Jadwal per Hari -->
      <div class="schedule-container">
        
        <!-- SENIN -->
        <div class="day-schedule <?php echo $current_day == 1 ? 'current-day' : ''; ?>" data-day="senin">
          <div class="day-header">
            <h3 class="day-title">SENIN</h3>
            <span class="day-date">Hari Pertama Semangat</span>
          </div>
          <div class="schedule-grid">
            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">07:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">SENAM BL</span>
                <span class="class-type">Low Impact</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH FITRI</span>
                <span class="studio-badge"><span class="studio-icon">🎯</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item boxing" data-studio="boxing">
              <div class="time-slot">
                <span class="time">08:00</span>
                <span class="duration">90 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">BOXING</span>
                <span class="class-type">High Intensity</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH PROFESIONAL</span>
                <span class="studio-badge"><span class="studio-icon">🥊</span> AREA BOXING</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">08:30</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">ZUMBA</span>
                <span class="class-type">Dance Fitness</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">ZIN IRA</span>
                <span class="studio-badge"><span class="studio-icon">💃</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item studio2" data-studio="studio2">
              <div class="time-slot">
                <span class="time">08:30</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">BODY SHAPE</span>
                <span class="class-type">Strength Training</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH MIEKE</span>
                <span class="studio-badge"><span class="studio-icon">💪</span> STUDIO 2</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">16:15</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">ZUMBA</span>
                <span class="class-type">Dance Fitness</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">ZIN SARI</span>
                <span class="studio-badge"><span class="studio-icon">💃</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">19:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">ZUMBA</span>
                <span class="class-type">Dance Fitness</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">ZIN INA</span>
                <span class="studio-badge"><span class="studio-icon">💃</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item boxing" data-studio="boxing">
              <div class="time-slot">
                <span class="time">19:00</span>
                <span class="duration">90 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">BOXING</span>
                <span class="class-type">High Intensity</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH PROFESIONAL</span>
                <span class="studio-badge"><span class="studio-icon">🥊</span> AREA BOXING</span>
              </div>
            </div>
          </div>
        </div>

        <!-- SELASA -->
        <div class="day-schedule <?php echo $current_day == 2 ? 'current-day' : ''; ?>" data-day="selasa">
          <div class="day-header">
            <h3 class="day-title">SELASA</h3>
            <span class="day-date">Burn Fat Day</span>
          </div>
          <div class="schedule-grid">
            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">08:30</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">ZUMBA</span>
                <span class="class-type">Dance Fitness</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">ZIN NILA</span>
                <span class="studio-badge"><span class="studio-icon">💃</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item studio2" data-studio="studio2">
              <div class="time-slot">
                <span class="time">08:15</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">CID ROCKER</span>
                <span class="class-type">Cardio Intensity</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">SISKA</span>
                <span class="studio-badge"><span class="studio-icon">⚡</span> STUDIO 2</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">16:15</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">ZUMBA</span>
                <span class="class-type">Dance Fitness</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">ZIN INA</span>
                <span class="studio-badge"><span class="studio-icon">💃</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">19:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">STRONG NATION</span>
                <span class="class-type">High Intensity</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">SYNC NOVA</span>
                <span class="studio-badge"><span class="studio-icon">🔥</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item studio2" data-studio="studio2">
              <div class="time-slot">
                <span class="time">19:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">ZUMBA</span>
                <span class="class-type">Dance Fitness</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">ZIN SACTA</span>
                <span class="studio-badge"><span class="studio-icon">💃</span> STUDIO 2</span>
              </div>
            </div>

            <div class="schedule-item boxing" data-studio="boxing">
              <div class="time-slot">
                <span class="time">19:00</span>
                <span class="duration">90 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">BOXING</span>
                <span class="class-type">High Intensity</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH PROFESIONAL</span>
                <span class="studio-badge"><span class="studio-icon">🥊</span> AREA BOXING</span>
              </div>
            </div>
          </div>
        </div>

        <!-- RABU -->
        <div class="day-schedule <?php echo $current_day == 3 ? 'current-day' : ''; ?>" data-day="rabu">
          <div class="day-header">
            <h3 class="day-title">RABU</h3>
            <span class="day-date">Midweek Energy Boost</span>
          </div>
          <div class="schedule-grid">
            <div class="schedule-item boxing" data-studio="boxing">
              <div class="time-slot">
                <span class="time">08:00</span>
                <span class="duration">90 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">BOXING</span>
                <span class="class-type">High Intensity</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH PROFESIONAL</span>
                <span class="studio-badge"><span class="studio-icon">🥊</span> AREA BOXING</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">08:30</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">ZUMBA</span>
                <span class="class-type">Dance Fitness</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">ZIN IRA</span>
                <span class="studio-badge"><span class="studio-icon">💃</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">16:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">BODY SHAPE</span>
                <span class="class-type">Strength Training</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH MIEKE</span>
                <span class="studio-badge"><span class="studio-icon">💪</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">18:30</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">KAPHA YOGA</span>
                <span class="class-type">Mind & Body</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH NANA</span>
                <span class="studio-badge"><span class="studio-icon">🧘</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item studio2" data-studio="studio2">
              <div class="time-slot">
                <span class="time">19:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">CID ROCKER</span>
                <span class="class-type">Cardio Intensity</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">SISKA</span>
                <span class="studio-badge"><span class="studio-icon">⚡</span> STUDIO 2</span>
              </div>
            </div>
          </div>
        </div>

        <!-- KAMIS -->
        <div class="day-schedule <?php echo $current_day == 4 ? 'current-day' : ''; ?>" data-day="kamis">
          <div class="day-header">
            <h3 class="day-title">KAMIS</h3>
            <span class="day-date">Full Body Workout</span>
          </div>
          <div class="schedule-grid">
            <div class="schedule-item boxing" data-studio="boxing">
              <div class="time-slot">
                <span class="time">08:00</span>
                <span class="duration">90 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">BOXING</span>
                <span class="class-type">High Intensity</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH PROFESIONAL</span>
                <span class="studio-badge"><span class="studio-icon">🥊</span> AREA BOXING</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">08:30</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">BODY SHAPE</span>
                <span class="class-type">Strength Training</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH MIEKE</span>
                <span class="studio-badge"><span class="studio-icon">💪</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">16:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">ZUMBA</span>
                <span class="class-type">Dance Fitness</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">ZIN INA</span>
                <span class="studio-badge"><span class="studio-icon">💃</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item studio2" data-studio="studio2">
              <div class="time-slot">
                <span class="time">16:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">AERO BL</span>
                <span class="class-type">Cardio Blast</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH WIWIK</span>
                <span class="studio-badge"><span class="studio-icon">💨</span> STUDIO 2</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">19:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">ZUMBA</span>
                <span class="class-type">Dance Fitness</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">ZIN SACTA</span>
                <span class="studio-badge"><span class="studio-icon">💃</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item boxing" data-studio="boxing">
              <div class="time-slot">
                <span class="time">19:00</span>
                <span class="duration">90 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">BOXING</span>
                <span class="class-type">High Intensity</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH PROFESIONAL</span>
                <span class="studio-badge"><span class="studio-icon">🥊</span> AREA BOXING</span>
              </div>
            </div>
          </div>
        </div>

        <!-- JUMAT -->
        <div class="day-schedule <?php echo $current_day == 5 ? 'current-day' : ''; ?>" data-day="jumat">
          <div class="day-header">
            <h3 class="day-title">JUMAT</h3>
            <span class="day-date">Weekend Warm-up</span>
          </div>
          <div class="schedule-grid">
            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">07:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">SENAM BL</span>
                <span class="class-type">Low Impact</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH FITRI</span>
                <span class="studio-badge"><span class="studio-icon">🎯</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item studio2" data-studio="studio2">
              <div class="time-slot">
                <span class="time">07:45</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">POUNDFIT</span>
                <span class="class-type">Rhythm Workout</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">BERNI</span>
                <span class="studio-badge"><span class="studio-icon">🥁</span> STUDIO 2</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">16:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">KAPHA YOGA</span>
                <span class="class-type">Mind & Body</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH NANA</span>
                <span class="studio-badge"><span class="studio-icon">🧘</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item studio2" data-studio="studio2">
              <div class="time-slot">
                <span class="time">16:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">POUNDFIT PP</span>
                <span class="class-type">Rhythm Workout</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">NILA</span>
                <span class="studio-badge"><span class="studio-icon">🥁</span> STUDIO 2</span>
              </div>
            </div>

            <div class="schedule-item studio2" data-studio="studio2">
              <div class="time-slot">
                <span class="time">18:30</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">TRAMPOLINE</span>
                <span class="class-type">Fun Cardio</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH NANA</span>
                <span class="studio-badge"><span class="studio-icon">🤸</span> STUDIO 2</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">19:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">ZUMBA</span>
                <span class="class-type">Dance Fitness</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">ZIN INA</span>
                <span class="studio-badge"><span class="studio-icon">💃</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item boxing" data-studio="boxing">
              <div class="time-slot">
                <span class="time">19:00</span>
                <span class="duration">90 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">BOXING</span>
                <span class="class-type">High Intensity</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH PROFESIONAL</span>
                <span class="studio-badge"><span class="studio-icon">🥊</span> AREA BOXING</span>
              </div>
            </div>
          </div>
        </div>

        <!-- SABTU -->
        <div class="day-schedule <?php echo $current_day == 6 ? 'current-day' : ''; ?>" data-day="sabtu">
          <div class="day-header">
            <h3 class="day-title">SABTU</h3>
            <span class="day-date">Weekend Energy</span>
          </div>
          <div class="schedule-grid">
            <div class="schedule-item boxing" data-studio="boxing">
              <div class="time-slot">
                <span class="time">08:00</span>
                <span class="duration">90 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">BOXING</span>
                <span class="class-type">High Intensity</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH PROFESIONAL</span>
                <span class="studio-badge"><span class="studio-icon">🥊</span> AREA BOXING</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">08:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">ZUMBA</span>
                <span class="class-type">Dance Fitness</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">ZIN INA</span>
                <span class="studio-badge"><span class="studio-icon">💃</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item studio2" data-studio="studio2">
              <div class="time-slot">
                <span class="time">16:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">ZUMBA</span>
                <span class="class-type">Dance Fitness</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">ZIN SARI</span>
                <span class="studio-badge"><span class="studio-icon">💃</span> STUDIO 2</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">16:15</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">STRONG NATION</span>
                <span class="class-type">High Intensity</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">SYNC NOVA</span>
                <span class="studio-badge"><span class="studio-icon">🔥</span> STUDIO 1</span>
              </div>
            </div>
          </div>
        </div>

        <!-- MINGGU -->
        <div class="day-schedule <?php echo $current_day == 7 ? 'current-day' : ''; ?>" data-day="minggu">
          <div class="day-header">
            <h3 class="day-title">MINGGU</h3>
            <span class="day-date">Sunday Funday</span>
          </div>
          <div class="schedule-grid">
            <div class="schedule-item studio2" data-studio="studio2">
              <div class="time-slot">
                <span class="time">07:30</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">TRAMPOLINE</span>
                <span class="class-type">Fun Cardio</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH NANA</span>
                <span class="studio-badge"><span class="studio-icon">🤸</span> STUDIO 2</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">08:00</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">ZUMBA</span>
                <span class="class-type">Dance Fitness</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">ZIN INA</span>
                <span class="studio-badge"><span class="studio-icon">💃</span> STUDIO 1</span>
              </div>
            </div>

            <div class="schedule-item studio1" data-studio="studio1">
              <div class="time-slot">
                <span class="time">15:30</span>
                <span class="duration">60 min</span>
              </div>
              <div class="class-info">
                <span class="class-name">AERO BL</span>
                <span class="class-type">Cardio Blast</span>
              </div>
              <div class="instructor-info">
                <span class="instructor">COACH WIWIK</span>
                <span class="studio-badge"><span class="studio-icon">💨</span> STUDIO 1</span>
              </div>
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

  <!-- Scroll to Top Button -->
  <button class="scroll-to-top" onclick="scrollToTop()">
    ↑
  </button>

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

    // Enhanced JavaScript for modern features
    document.addEventListener('DOMContentLoaded', function() {
      // Schedule Filtering
      const filterButtons = document.querySelectorAll('.filter-btn');
      const scheduleItems = document.querySelectorAll('.schedule-item');
      
      filterButtons.forEach(button => {
        button.addEventListener('click', function() {
          const filter = this.getAttribute('data-filter');
          
          // Update active button
          filterButtons.forEach(btn => btn.classList.remove('active'));
          this.classList.add('active');
          
          // Filter schedule items
          scheduleItems.forEach(item => {
            const studio = item.getAttribute('data-studio');
            
            if (filter === 'all' || studio === filter) {
              item.style.display = 'grid';
              setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
              }, 50);
            } else {
              item.style.opacity = '0';
              item.style.transform = 'translateY(20px)';
              setTimeout(() => {
                item.style.display = 'none';
              }, 300);
            }
          });
        });
      });
      
      // Scroll to Top functionality
      const scrollToTopBtn = document.querySelector('.scroll-to-top');
      
      window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
          scrollToTopBtn.classList.add('visible');
        } else {
          scrollToTopBtn.classList.remove('visible');
        }
        
        // Parallax effect for welcome section
        const scrolled = window.pageYOffset;
        const parallax = document.querySelector('.member-welcome');
        if (parallax) {
          parallax.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
      });
      
      // Enhanced scroll animations
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };
      
      const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
          }
        });
      }, observerOptions);
      
      // Observe all schedule items and days
      document.querySelectorAll('.day-schedule, .schedule-item, .gym-package-card, .class-price-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
      });
      
      // Smooth scrolling for anchor links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            target.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        });
      });
    });

    function scrollToTop() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }
  </script>
</body>
</html>