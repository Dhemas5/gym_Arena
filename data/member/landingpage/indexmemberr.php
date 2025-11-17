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