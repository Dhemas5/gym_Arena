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

// Ambil semua paket untuk ditampilkan
$pakets = [];
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
    'durasi_hari'     => (int)$p['durasi_hari'],
    'featured'        => $featured,
    'deskripsi'       => htmlspecialchars($p['deskripsi'] ?? '')
  ];
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
    .price-option {
      border: 2px solid #e9ecef;
      border-radius: 10px;
      padding: 15px;
      margin: 10px 0;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .price-option:hover {
      border-color: #42a5f5;
      background-color: rgba(66, 165, 245, 0.05);
    }

    .price-option.selected {
      border-color: #42a5f5;
      background-color: rgba(66, 165, 245, 0.1);
    }

    .price-option.disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .price-label {
      font-weight: 600;
      font-size: 1rem;
      margin-bottom: 5px;
    }

    .price-value {
      font-size: 1.2rem;
      font-weight: 700;
      color: #198754;
    }

    .price-value.mahasiswa {
      color: #0d6efd;
    }

    .badge-mahasiswa {
      background: linear-gradient(135deg, #ff6b6b, #ee5a24);
      color: white;
    }

    .badge-umum {
      background: linear-gradient(135deg, #48c78e, #00a76f);
      color: white;
    }

    .package-actions {
      margin-top: 20px;
    }

    .btn-beli {
      width: 100%;
      padding: 12px;
      font-weight: 600;
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
        <?php foreach ($pakets as $p): ?>
          <div class="gym-package-card <?= $p['featured'] ? 'featured' : '' ?>" data-package-id="<?= $p['id'] ?>">
            <?php if ($p['featured']): ?>
              <div class="ribbon"><span>Paling Laris</span></div>
            <?php endif; ?>

            <div class="gym-package-name"><?= $p['nama'] ?></div>
            <div class="gym-package-duration"><?= $p['durasi'] ?></div>

            <!-- Pilihan Harga Umum -->
            <div class="price-option" data-type="umum" data-price="<?= $p['harga_umum'] ?>">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div class="price-label">
                    <span class="badge badge-umum">UMUM</span>
                  </div>
                  <div class="price-value">Rp <?= number_format($p['harga_umum'], 0, ',', '.') ?></div>
                </div>
                <div class="form-check">
                  <input class="form-check-input price-radio" type="radio" name="price_option_<?= $p['id'] ?>"
                    value="umum" id="umum_<?= $p['id'] ?>" checked>
                </div>
              </div>
            </div>

            <!-- Pilihan Harga Mahasiswa -->
            <?php if ($p['harga_mahasiswa'] > 0 && $p['harga_mahasiswa'] < $p['harga_umum']): ?>
              <div class="price-option <?= $is_mahasiswa ? '' : 'disabled' ?>"
                data-type="mahasiswa"
                data-price="<?= $p['harga_mahasiswa'] ?>">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="price-label">
                      <span class="badge badge-mahasiswa">MAHASISWA</span>
                      <?php if (!$is_mahasiswa): ?>
                        <small class="text-muted d-block">*Hanya untuk mahasiswa terverifikasi</small>
                      <?php endif; ?>
                    </div>
                    <div class="price-value mahasiswa">Rp <?= number_format($p['harga_mahasiswa'], 0, ',', '.') ?></div>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input price-radio" type="radio" name="price_option_<?= $p['id'] ?>"
                      value="mahasiswa" id="mahasiswa_<?= $p['id'] ?>"
                      <?= $is_mahasiswa ? '' : 'disabled' ?>>
                  </div>
                </div>
              </div>
            <?php endif; ?>

            <!-- Tombol Beli -->
            <div class="package-actions">
              <form method="POST" action="checkout_pembayaran.php" class="package-form">
                <input type="hidden" name="id_paket" value="<?= $p['id'] ?>">
                <input type="hidden" name="nama_paket" value="<?= $p['nama'] ?>">
                <input type="hidden" name="durasi_hari" value="<?= $p['durasi_hari'] ?>">
                <input type="hidden" name="harga_paket" value="<?= $p['harga_umum'] ?>" class="harga-input">
                <input type="hidden" name="tipe_member" value="umum" class="tipe-input">
                <button type="submit" class="btn btn-primary btn-beli">
                  <i class="fas fa-shopping-cart"></i> Beli Sekarang -
                  <span class="selected-price">Rp <?= number_format($p['harga_umum'], 0, ',', '.') ?></span>
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

  <?php include 'sectionsmember/footer_member.php'; ?>
  <button class="scroll-to-top" onclick="scrollToTop()">Up</button>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Fungsi untuk mengatur pilihan harga
    document.addEventListener('DOMContentLoaded', function() {
      // Event listener untuk semua radio button harga
      document.querySelectorAll('.price-radio').forEach(radio => {
        radio.addEventListener('change', function() {
          const packageCard = this.closest('.gym-package-card');
          const priceOption = this.closest('.price-option');
          const packageId = packageCard.dataset.packageId;

          // Reset semua pilihan di package ini
          packageCard.querySelectorAll('.price-option').forEach(option => {
            option.classList.remove('selected');
          });

          // Tandai yang dipilih
          priceOption.classList.add('selected');

          // Update form data
          const form = packageCard.querySelector('.package-form');
          const hargaInput = form.querySelector('.harga-input');
          const tipeInput = form.querySelector('.tipe-input');
          const selectedPrice = form.querySelector('.selected-price');

          const selectedType = priceOption.dataset.type;
          const selectedPriceValue = priceOption.dataset.price;

          hargaInput.value = selectedPriceValue;
          tipeInput.value = selectedType;
          selectedPrice.textContent = 'Rp ' + parseInt(selectedPriceValue).toLocaleString('id-ID');
        });
      });

      // Set pilihan default untuk semua package
      document.querySelectorAll('.gym-package-card').forEach(card => {
        const defaultRadio = card.querySelector('.price-radio:checked');
        if (defaultRadio) {
          defaultRadio.closest('.price-option').classList.add('selected');
        }
      });

      // Event listener untuk klik pada price option
      document.querySelectorAll('.price-option:not(.disabled)').forEach(option => {
        option.addEventListener('click', function() {
          const radio = this.querySelector('.price-radio');
          if (radio && !radio.disabled) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
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

    window.addEventListener('scroll', () => {
      const scrollBtn = document.querySelector('.scroll-to-top');
      if (scrollBtn) {
        scrollBtn.classList.toggle('visible', window.pageYOffset > 300);
      }
    });
  </script>
</body>

</html>