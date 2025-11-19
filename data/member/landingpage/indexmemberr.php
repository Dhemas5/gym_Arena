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

      <!-- SENIN -->
      <div class="day-schedule">
        <div class="day-header">
          <div class="day-name">SENIN</div>
          <div class="day-date">Hari Ini</div>
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
            <tr>
              <td class="time-cell">07:00</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">SEMAN BL</div>
                <div class="instructor-cell">COACH FITRI</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">08:00</td>
              <td class="studio-cell">-</td>
              <td>
                <div class="class-cell">BOXING</div>
                <div class="instructor-cell"></div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">08:30</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">ZUMBA</div>
                <div class="instructor-cell">ZIN IRA</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">08:30</td>
              <td class="studio-cell">STUDIO 2</td>
              <td>
                <div class="class-cell">BODY SHAPE</div>
                <div class="instructor-cell">COACH NIEKE</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- SELASA -->
      <div class="day-schedule">
        <div class="day-header">
          <div class="day-name">SELASA</div>
          <div class="day-date">Besok</div>
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
            <tr>
              <td class="time-cell">08:30</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">ZUMBA</div>
                <div class="instructor-cell">ZIN NILA</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">08:15</td>
              <td class="studio-cell">STUDIO 2</td>
              <td>
                <div class="class-cell">CID ROCKER</div>
                <div class="instructor-cell">SISKA</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">18:15</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">ZUMBA</div>
                <div class="instructor-cell">ZIN INA</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">13:00</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">STROKU KATOI</div>
                <div class="instructor-cell">SYNCHOVA</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- RABU -->
      <div class="day-schedule">
        <div class="day-header">
          <div class="day-name">RABU</div>
          <div class="day-date"></div>
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
            <tr>
              <td class="time-cell">08:00</td>
              <td class="studio-cell">-</td>
              <td>
                <div class="class-cell">BOXING</div>
                <div class="instructor-cell"></div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">08:30</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">ZUMBA</div>
                <div class="instructor-cell">ZIN IRA</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">18:00</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">BODY SHAPE</div>
                <div class="instructor-cell">COACH NIEKE</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">18:30</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">KAPHA YOGA</div>
                <div class="instructor-cell">COACH NANA</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- KAMIS -->
      <div class="day-schedule">
        <div class="day-header">
          <div class="day-name">KAMIS</div>
          <div class="day-date"></div>
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
            <tr>
              <td class="time-cell">08:00</td>
              <td class="studio-cell">-</td>
              <td>
                <div class="class-cell">BOXING</div>
                <div class="instructor-cell"></div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">08:30</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">BODY SHAPE</div>
                <div class="instructor-cell">COACH NIEKE</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">18:00</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">ZUMBA</div>
                <div class="instructor-cell">ZIN INA</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">16:00</td>
              <td class="studio-cell">STUDIO 2</td>
              <td>
                <div class="class-cell">AERO BL</div>
                <div class="instructor-cell">COACH WIVVIK</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- JUMAT -->
      <div class="day-schedule">
        <div class="day-header">
          <div class="day-name">JUM'AT</div>
          <div class="day-date"></div>
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
            <tr>
              <td class="time-cell">07:00</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">SEMAN BL</div>
                <div class="instructor-cell">COACH FITRI</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">07:45</td>
              <td class="studio-cell">STUDIO 2</td>
              <td>
                <div class="class-cell">POUNDFIT</div>
                <div class="instructor-cell">BERNI</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">18:00</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">KAPHA YOGA</div>
                <div class="instructor-cell">COACH NANA</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">16:00</td>
              <td class="studio-cell">STUDIO 2</td>
              <td>
                <div class="class-cell">POUNDFIT</div>
                <div class="instructor-cell">PPNILA</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- SABTU -->
      <div class="day-schedule">
        <div class="day-header">
          <div class="day-name">SABTU</div>
          <div class="day-date"></div>
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
            <tr>
              <td class="time-cell">08:00</td>
              <td class="studio-cell">-</td>
              <td>
                <div class="class-cell">BOXING</div>
                <div class="instructor-cell"></div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">08:30</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">ZUMBA</div>
                <div class="instructor-cell">ZIN INA</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">16:00</td>
              <td class="studio-cell">STUDIO 2</td>
              <td>
                <div class="class-cell">ZUMBA</div>
                <div class="instructor-cell">ZIN SARI</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">16:15</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">STROKU KATOI</div>
                <div class="instructor-cell">SYNCHOVA</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- MINGGU -->
      <div class="day-schedule">
        <div class="day-header">
          <div class="day-name">MINGGU</div>
          <div class="day-date"></div>
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
            <tr>
              <td class="time-cell">07:30</td>
              <td class="studio-cell">STUDIO 2</td>
              <td>
                <div class="class-cell">TRAMPOLINE</div>
                <div class="instructor-cell">COACH NANA</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">08:00</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">ZUMBA</div>
                <div class="instructor-cell">ZIN INA</div>
              </td>
            </tr>
            <tr>
              <td class="time-cell">15:30</td>
              <td class="studio-cell">STUDIO 1</td>
              <td>
                <div class="class-cell">AERO BL</div>
                <div class="instructor-cell">COACH WIVVIK</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

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
          $harga_akhir = $is_mahasiswa && $p['harga_mahasiswa'] > 0 ? $p['harga_mahasiswa'] : $p['harga_umum'];
        ?>
          <div class="gym-package-card <?= $pkg['featured'] ?? $featured ? 'featured' : '' ?>">
            <?php if ($featured): ?>
              <div class="ribbon"><span>Paling Laris</span></div>
            <?php endif; ?>

            <div class="gym-package-name"><?= htmlspecialchars($p['nama_paket']) ?></div>
            <div class="gym-package-duration"><?= $durasi_text ?></div>

            <!-- Harga Umum -->
            <div class="price-row umum">
              <span class="price-label">UMUM</span>
              <span class="price-value">Rp <?= number_format($p['harga_umum'], 0, ',', '.') ?></span>
            </div>

            <!-- Harga Mahasiswa (jika ada diskon) -->
            <?php if ($p['harga_mahasiswa'] > 0 && $p['harga_mahasiswa'] < $p['harga_umum']): ?>
              <div class="price-row mahasiswa">
                <span class="price-label">MAHASISWA</span>
                <span class="price-value text-success">Rp <?= number_format($p['harga_mahasiswa'], 0, ',', '.') ?></span>
              </div>
            <?php endif; ?>

            <!-- Tombol sesuai status membership -->
            <?php if ($membership_aktif): ?>
              <button class="btn-choose-package" disabled style="background:#28a745; opacity:0.8; cursor:not-allowed;">
                <i class="fas fa-check"></i> Sudah Aktif
              </button>
            <?php else: ?>
              <button class="btn-choose-package"
                onclick="openPaymentModal('<?= $p['id_paket'] ?>', 'gym', '<?= htmlspecialchars($p['nama_paket']) ?>', 
                  <?= $harga_akhir ?>, <?= $p['harga_mahasiswa'] ?>, '<?= $durasi_text ?>')">
                Pilih Paket Ini
              </button>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      </div>

    </div>
  </section>

  <!-- Modal Pembayaran -->
  <div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="formPembayaran" enctype="multipart/form-data">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Konfirmasi Pembayaran</h5>
            <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id_paket" id="package_id">
            <input type="hidden" name="harga" id="total_harga_input">

            <div class="row">
              <div class="col-md-6">
                <div class="summary-card">
                  <h6>Ringkasan Pembelian</h6>
                  <hr>
                  <p><strong>Paket:</strong> <span id="summary_package"></span></p>
                  <p><strong>Durasi:</strong> <span id="duration_display"></span></p>
                  <p><strong>Tipe:</strong> <span id="summary_type">Umum</span></p>
                  <h4>Total: <strong id="total_price_display" class="text-primary">Rp 0</strong></h4>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Upload Bukti Transfer</label>
                  <input type="file" class="form-control" name="bukti_pembayaran" accept="image/*" required>
                  <small class="text-muted">Transfer ke: <strong>BCA 2009138999</strong> a.n. CV. ARENA MAJU BERSAMA</small>
                </div>
                <div class="mb-3">
                  <label class="form-label">Catatan (Opsional)</label>
                  <textarea class="form-control" name="catatan" rows="3" placeholder="Contoh: Bayar via m-BCA"></textarea>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-success">Kirim Bukti Pembayaran</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php include 'sectionsmember/footer_member.php'; ?>
  <button class="scroll-to-top" onclick="scrollToTop()">Up</button>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function openPaymentModal(id_paket, tipe, nama, harga, harga_mhs, durasi) {
      document.getElementById('package_id').value = id_paket;
      document.getElementById('total_harga_input').value = harga;
      document.getElementById('summary_package').textContent = nama;
      document.getElementById('duration_display').textContent = durasi;
      document.getElementById('total_price_display').textContent = 'Rp ' + harga.toLocaleString('id-ID');
      document.getElementById('summary_type').textContent = (harga === harga_mhs && harga_mhs > 0) ? 'Mahasiswa' : 'Umum';

      new bootstrap.Modal(document.getElementById('paymentModal')).show();
    }

    // SweetAlert Setelah Upload Bukti
    document.getElementById('formPembayaran').addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = this.querySelector('button[type="submit"]');
      const old = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

      const formData = new FormData(this);

      fetch('proses_bayar.php', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              html: '<strong>Bukti pembayaran telah dikirim!</strong><br>Mohon tunggu konfirmasi admin.',
              timer: 5000,
              timerProgressBar: true,
              showConfirmButton: true,
              confirmButtonText: 'OK'
            }).then(() => location.reload());
          } else {
            Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
          }
        })
        .catch(() => {
          Swal.fire('Error', 'Koneksi terputus, coba lagi nanti', 'error');
        })
        .finally(() => {
          btn.disabled = false;
          btn.innerHTML = old;
        });
    });

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