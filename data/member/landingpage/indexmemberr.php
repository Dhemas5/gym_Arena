<?php
session_start();
require "../../../setting/koneksi.php";

if (!isset($_SESSION['login']) || $_SESSION['user_type'] !== 'member') {
  header("Location: ../login/login.php");
  exit;
}

$nama_member = $_SESSION['nama'];
$id_member   = $_SESSION['id_member'];

// Cek status mahasiswa
$is_mahasiswa = 0;
$stmt = $con->prepare("SELECT is_mahasiswa FROM tbl_member WHERE id_member = ?");
$stmt->bind_param("i", $id_member);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
  $is_mahasiswa = $row['is_mahasiswa'];
}
$stmt->close();

// === CEK MEMBERSHIP AKTIF ===
$membership_aktif = false;
$paket_aktif = null;
$berakhir = null;

$stmt = $con->prepare("
    SELECT p.nama_paket, m.tgl_berakhir 
    FROM tbl_membership m 
    JOIN tbl_paket p ON m.id_paket = p.id_paket 
    WHERE m.id_member = ? AND m.tgl_berakhir >= NOW() 
    ORDER BY m.tgl_berakhir DESC LIMIT 1
");
$stmt->bind_param("i", $id_member);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
  $row = $res->fetch_assoc();
  $membership_aktif = true;
  $paket_aktif = $row['nama_paket'];
  $berakhir = date('d M Y', strtotime($row['tgl_berakhir']));
}
$stmt->close();

// Data jadwal sesuai gambar
$jadwal_kelas = [
    'Senin' => [
        ['07:00', 'STUDIO 1', 'SEMAN BL', 'COACH FITRI'],
        ['08:00', '-', 'BOXING', ''],
        ['08:30', 'STUDIO 1', 'ZUMBA', 'ZIN IRA'],
        ['08:30', 'STUDIO 2', 'BODY SHAPE', 'COACH NIEKE']
    ],
    'Selasa' => [
        ['08:30', 'STUDIO 1', 'ZUMBA', 'ZIN NILA'],
        ['08:15', 'STUDIO 2', 'CID ROCKER', 'SISKA'],
        ['18:15', 'STUDIO 1', 'ZUMBA', 'ZIN INA'],
        ['13:00', 'STUDIO 1', 'STROKU KATOI', 'SYNCHOVA']
    ],
    'Rabu' => [
        ['08:00', '-', 'BOXING', ''],
        ['08:30', 'STUDIO 1', 'ZUMBA', 'ZIN IRA'],
        ['18:00', 'STUDIO 1', 'BODY SHAPE', 'COACH NIEKE'],
        ['18:30', 'STUDIO 1', 'KAPHA YOGA', 'COACH NANA']
    ],
    'Kamis' => [
        ['08:00', '-', 'BOXING', ''],
        ['08:30', 'STUDIO 1', 'BODY SHAPE', 'COACH NIEKE'],
        ['18:00', 'STUDIO 1', 'ZUMBA', 'ZIN INA'],
        ['16:00', 'STUDIO 2', 'AERO BL', 'COACH WIVVIK']
    ],
    'Jumat' => [
        ['07:00', 'STUDIO 1', 'SEMAN BL', 'COACH FITRI'],
        ['07:45', 'STUDIO 2', 'POUNDFIT', 'BERNI'],
        ['18:00', 'STUDIO 1', 'KAPHA YOGA', 'COACH NANA'],
        ['16:00', 'STUDIO 2', 'POUNDFIT', 'PPNILA']
    ],
    'Sabtu' => [
        ['08:00', '-', 'BOXING', ''],
        ['08:30', 'STUDIO 1', 'ZUMBA', 'ZIN INA'],
        ['16:00', 'STUDIO 2', 'ZUMBA', 'ZIN SARI'],
        ['16:15', 'STUDIO 1', 'STROKU KATOI', 'SYNCHOVA']
    ],
    'Minggu' => [
        ['07:30', 'STUDIO 2', 'TRAMPOLINE', 'COACH NANA'],
        ['08:00', 'STUDIO 1', 'ZUMBA', 'ZIN INA'],
        ['15:30', 'STUDIO 1', 'AERO BL', 'COACH WIVVIK']
    ]
];

// Urutan hari dalam seminggu
$hari_dalam_minggu = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

// Dapatkan hari ini (0 = Minggu, 1 = Senin, ..., 6 = Sabtu)
$hari_ini_index = date('w');
// Konversi ke format kita (0 = Senin, 6 = Minggu)
$hari_ini_index = $hari_ini_index == 0 ? 6 : $hari_ini_index - 1;
$hari_ini = $hari_dalam_minggu[$hari_ini_index];

// Susun jadwal mulai dari hari ini
$jadwal_terurut = [];
for ($i = 0; $i < 7; $i++) {
    $index = ($hari_ini_index + $i) % 7;
    $hari = $hari_dalam_minggu[$index];
    $jadwal_terurut[$hari] = $jadwal_kelas[$hari];
}

// Ambil paket hanya jika belum aktif
$pakets = [];
if (!$membership_aktif) {
  $paket_result = $con->query("
        SELECT id_paket, nama_paket, harga_umum, harga_mahasiswa, durasi_hari, deskripsi 
        FROM tbl_paket 
        ORDER BY durasi_hari ASC
    ");

  while ($p = $paket_result->fetch_assoc()) {
    $durasi_text = match ((int)$p['durasi_hari']) {
      1    => '1 Hari',
      30   => '1 Bulan',
      90   => '3 Bulan',
      180  => '6 Bulan',
      365  => '1 Tahun',
      default => $p['durasi_hari'] . ' Hari'
    };

    $featured = in_array($p['durasi_hari'], [90, 180, 365]);

    $pakets[] = [
      'id'              => $p['id_paket'],
      'nama'            => htmlspecialchars($p['nama_paket']),
      'harga_umum'      => (int)$p['harga_umum'],
      'harga_mahasiswa' => (int)$p['harga_mahasiswa'],
      'durasi'          => $durasi_text,
      'featured'        => $featured,
      'deskripsi'       => htmlspecialchars($p['deskripsi'] ?? '')
    ];
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Arena FIT - Member Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/stylemember.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Jadwal Section Styles */
    .schedule-section {
      background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%);
      padding: 80px 0;
      color: white;
    }

    .schedule-header {
      text-align: center;
      margin-bottom: 50px;
    }

    .schedule-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 15px;
      background: linear-gradient(135deg, #42a5f5 0%, #1976d2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .schedule-subtitle {
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.7);
      max-width: 600px;
      margin: 0 auto;
    }

    .day-schedule {
      background: rgba(13, 27, 42, 0.9);
      border-radius: 16px;
      padding: 25px;
      margin-bottom: 25px;
      border: 1px solid rgba(66, 165, 245, 0.2);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .day-schedule.today {
      border: 2px solid #42a5f5;
      box-shadow: 0 0 30px rgba(66, 165, 245, 0.4);
      transform: scale(1.02);
    }

    .day-schedule:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px rgba(66, 165, 245, 0.2);
    }

    .day-header {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 2px solid rgba(66, 165, 245, 0.3);
    }

    .day-name {
      font-size: 1.4rem;
      font-weight: 700;
      color: #42a5f5;
      margin-right: 15px;
    }

    .day-date {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.6);
      background: rgba(66, 165, 245, 0.1);
      padding: 4px 12px;
      border-radius: 20px;
    }

    .today-badge {
      background: linear-gradient(135deg, #42a5f5 0%, #1976d2 100%);
      color: white;
      font-weight: 600;
    }

    .class-table {
      width: 100%;
      border-collapse: collapse;
    }

    .class-table th {
      text-align: left;
      padding: 12px 15px;
      background: rgba(66, 165, 245, 0.1);
      color: #42a5f5;
      font-weight: 600;
      border-bottom: 1px solid rgba(66, 165, 245, 0.3);
    }

    .class-table td {
      padding: 15px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      color: rgba(255, 255, 255, 0.9);
    }

    .class-table tr:last-child td {
      border-bottom: none;
    }

    .class-table tr:hover td {
      background: rgba(66, 165, 245, 0.05);
    }

    .time-cell {
      font-weight: 600;
      color: #64b5f6;
      width: 100px;
    }

    .studio-cell {
      color: #81c784;
      font-weight: 500;
      width: 120px;
    }

    .class-cell {
      font-weight: 500;
    }

    .instructor-cell {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.9rem;
    }

    .class-badge {
      display: inline-block;
      padding: 4px 12px;
      background: rgba(66, 165, 245, 0.2);
      color: #42a5f5;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      margin-right: 8px;
      margin-bottom: 5px;
    }

    .instagram-section {
      text-align: center;
      margin-top: 60px;
      padding: 30px;
      background: rgba(13, 27, 42, 0.8);
      border-radius: 16px;
      border: 1px solid rgba(66, 165, 245, 0.2);
    }

    .instagram-handle {
      font-size: 1.3rem;
      font-weight: 700;
      color: #42a5f5;
      margin-bottom: 15px;
    }

    .instagram-cta {
      color: rgba(255, 255, 255, 0.8);
      margin-bottom: 20px;
    }

    .btn-instagram {
      background: linear-gradient(45deg, #405DE6, #5851DB, #833AB4, #C13584, #E1306C, #FD1D1D);
      border: none;
      color: white;
      padding: 12px 30px;
      border-radius: 25px;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .btn-instagram:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(225, 48, 108, 0.4);
      color: white;
    }

    .current-week {
      text-align: center;
      margin-bottom: 30px;
      color: rgba(255, 255, 255, 0.8);
      font-size: 1.1rem;
    }

    /* Price Row dengan Tombol */
    .price-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
      padding: 8px 0;
    }

    .price-row.with-button {
      background: rgba(255, 255, 255, 0.05);
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 8px;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .price-row.with-button:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    .price-label {
      font-weight: 600;
      color: rgba(255, 255, 255, 0.9);
    }

    .price-value {
      font-weight: 700;
      color: #4CAF50;
    }

    .btn-price-option {
      background: linear-gradient(135deg, #42a5f5 0%, #1976d2 100%);
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-price-option:hover {
      background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
      color: white;
      transform: translateY(-1px);
      text-decoration: none;
    }

    @media (max-width: 768px) {
      .schedule-section {
        padding: 50px 0;
      }

      .schedule-title {
        font-size: 2rem;
      }

      .day-schedule {
        padding: 20px;
      }

      .class-table {
        font-size: 0.9rem;
      }

      .class-table th,
      .class-table td {
        padding: 10px 8px;
      }

      .price-row.with-button {
        padding: 8px;
      }

      .btn-price-option {
        padding: 4px 8px;
        font-size: 0.75rem;
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
        <div><span style="font-size: 1.2rem;">Arena FIT</span></div>
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
          <div class="member-avatar"><?= strtoupper(substr($nama_member, 0, 1)) ?></div>
          <span class="welcome-text">
            <span class="member-name"><?= htmlspecialchars($nama_member) ?></span>
          </span>
          <a href="../login/logout.php" class="btn-logout">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- WELCOME SECTION -->
  <section class="member-welcome">
    <div class="container">
      <div class="welcome-card">
        <h1 class="welcome-title">Selamat Datang, <?= htmlspecialchars($nama_member) ?>!</h1>
        <p class="welcome-subtitle">Pilih paket membership terbaik dan mulai latihan sekarang!</p>
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-number"><?= $membership_aktif ? 'Aktif' : 'Nonaktif' ?></div>
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
            <div class="stat-label">Semangat!</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- JADWAL KELAS SECTION -->
  <section class="schedule-section">
    <div class="container">
      <div class="schedule-header">
        <h2 class="schedule-title">JADWAL KELAS ARENA FIT</h2>
        <p class="schedule-subtitle">Ikuti kelas favorit Anda dengan instruktur profesional. Jadwal terbaru update setiap bulan.</p>
      </div>

      <div class="current-week">
        <i class="fas fa-calendar-alt me-2"></i>
        Jadwal Minggu Ini - <?= date('d F Y') ?>
      </div>

      <?php foreach($jadwal_terurut as $hari => $kelas): ?>
        <?php 
        // Hitung tanggal untuk hari ini dan seterusnya
        $offset = array_search($hari, $hari_dalam_minggu) - $hari_ini_index;
        $tanggal_hari = date('d M', strtotime("+$offset days"));
        $is_today = $hari === $hari_ini;
        ?>
        
        <div class="day-schedule <?= $is_today ? 'today' : '' ?>">
          <div class="day-header">
            <div class="day-name"><?= strtoupper($hari) ?></div>
            <div class="day-date <?= $is_today ? 'today-badge' : '' ?>">
              <?= $is_today ? 'HARI INI' : $tanggal_hari ?>
            </div>
          </div>
          <table class="class-table">
            <thead>
              <tr>
                <th>WAKTU</th>
                <th>STUDIO</th>
                <th>KELAS & INSTRUKTUR</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($kelas as $kelas_item): ?>
                <tr>
                  <td class="time-cell"><?= $kelas_item[0] ?></td>
                  <td class="studio-cell"><?= $kelas_item[1] ?></td>
                  <td>
                    <div class="class-cell"><?= $kelas_item[2] ?></div>
                    <?php if(!empty($kelas_item[3])): ?>
                      <div class="instructor-cell"><?= $kelas_item[3] ?></div>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endforeach; ?>

      <!-- Instagram Section -->
      <div class="instagram-section">
        <div class="instagram-handle">@arenafticlub2022</div>
        <p class="instagram-cta">Follow Instagram kami untuk update jadwal terbaru dan informasi promo!</p>
        <a href="https://instagram.com/arenafticlub2022" target="_blank" class="btn-instagram">
          <i class="fab fa-instagram"></i>
          Follow Instagram
        </a>
        <div class="mt-3 text-muted">
          <small>Contact: 0821-4308-0510</small>
        </div>
      </div>
    </div>
  </section>

 <!-- PRICE LIST SECTION -->
<section class="pricelist-section">
  <div class="container">

    <?php if ($membership_aktif): ?>
      <div class="text-center py-5 my-5">
        <i class="fas fa-check-circle fa-6x text-success mb-4"></i>
        <h2 class="text-success fw-bold">Membership Anda Sudah Aktif!</h2>
        <h4 class="mt-3">Paket: <strong class="text-primary"><?= $paket_aktif ?></strong></h4>
        <p class="lead">Berlaku hingga: <strong class="text-warning"><?= $berakhir ?></strong></p>
        <a href="transaksi.php" class="btn btn-lg btn-primary mt-4">
          <i class="fas fa-history"></i> Lihat Riwayat Transaksi
        </a>
      </div>
    <?php endif; ?>

    <!-- Selalu tampilkan semua paket -->
    <div class="pricelist-header <?= $membership_aktif ? 'mt-5' : '' ?>">
      <h2 class="pricelist-title">GYM <span class="highlight">PRICE LIST</span></h2>
      <p class="pricelist-subtitle">Pilih Paket Membership Sesuai Kebutuhan Anda</p>
    </div>

    <h3 class="price-category-title">Paket Membership Gym</h3>
    <div class="gym-packages-grid">
      <?php 
      // Ambil semua paket (selalu, tidak peduli aktif atau tidak)
      $paket_result = $con->query("
          SELECT id_paket, nama_paket, harga_umum, harga_mahasiswa, durasi_hari, deskripsi 
          FROM tbl_paket 
          ORDER BY durasi_hari ASC
      ");

      while ($p = $paket_result->fetch_assoc()): 
        $durasi_text = match ((int)$p['durasi_hari']) {
          1    => '1 Hari',
          30   => '1 Bulan',
          90   => '3 Bulan',
          180  => '6 Bulan',
          365  => '1 Tahun',
          default => $p['durasi_hari'] . ' Hari'
        };

        $featured = in_array($p['durasi_hari'], [90, 180, 365]);
      ?>
        <div class="gym-package-card <?= $featured ? 'featured' : '' ?>">
          <?php if ($featured): ?>
            <div class="ribbon"><span>Paling Laris</span></div>
          <?php endif; ?>

          <div class="gym-package-name"><?= htmlspecialchars($p['nama_paket']) ?></div>
          <div class="gym-package-duration"><?= $durasi_text ?></div>

          <!-- Untuk SEMUA member: Tampilkan harga umum -->
          <div class="price-row with-button">
            <div>
              <div class="price-label">UMUM</div>
              <div class="price-value">Rp <?= number_format($p['harga_umum'], 0, ',', '.') ?></div>
            </div>
            <?php if (!$membership_aktif): ?>
              <a href="checkout_pembayaran.php?id_paket=<?= $p['id_paket'] ?>&harga=<?= $p['harga_umum'] ?>&tipe=umum" 
                 class="btn-price-option">
                Pilih
              </a>
            <?php else: ?>
              <button class="btn-price-option" disabled style="background:#6c757d; cursor:not-allowed;">
                Aktif
              </button>
            <?php endif; ?>
          </div>

          <!-- Hanya untuk MAHASISWA: Tampilkan harga mahasiswa -->
          <?php if ($is_mahasiswa && $p['harga_mahasiswa'] > 0): ?>
            <div class="price-row with-button">
              <div>
                <div class="price-label">MAHASISWA</div>
                <div class="price-value text-success">Rp <?= number_format($p['harga_mahasiswa'], 0, ',', '.') ?></div>
              </div>
              <?php if (!$membership_aktif): ?>
                <a href="checkout_pembayaran.php?id_paket=<?= $p['id_paket'] ?>&harga=<?= $p['harga_mahasiswa'] ?>&tipe=mahasiswa" 
                   class="btn-price-option">
                  Pilih
                </a>
              <?php else: ?>
                <button class="btn-price-option" disabled style="background:#6c757d; cursor:not-allowed;">
                  Aktif
                </button>
              <?php endif; ?>
            </div>
          <?php endif; ?>

        </div>
      <?php endwhile; ?>
    </div>

  </div>
</section>
  <?php include 'sectionsmember/footer_member.php'; ?>
  <button class="scroll-to-top" onclick="scrollToTop()">Up</button>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function scrollToTop() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }
    window.addEventListener('scroll', () => {
      document.querySelector('.scroll-to-top').classList.toggle('visible', window.pageYOffset > 300);
    });
  </script>
</body>

</html>