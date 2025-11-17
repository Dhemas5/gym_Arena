<?php
session_start();
require "../../../setting/koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['user_type'] !== 'member') {
    header("Location: ../login/login.php");
    exit;
}

// Ambil data dari parameter GET
if (!isset($_GET['package_id']) || !isset($_GET['type'])) {
    header("Location: indexmemberr.php");
    exit;
}

$nama_member = $_SESSION['nama'];
$id_member = $_SESSION['id_member'];
$package_id = $_GET['package_id'];
$package_type = $_GET['type'];
$member_type = isset($_GET['member_type']) ? $_GET['member_type'] : 'umum';

// Data paket berdasarkan type
$package_data = null;

if ($package_type === 'paket') {
    // Ambil dari database tbl_paket
    $query = "SELECT * FROM tbl_paket WHERE id_paket = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $package_data = $result->fetch_assoc();
} else {
    // Data custom untuk kelas dan program
    $custom_packages = [
        'gym_harian' => ['nama' => 'Gym Harian', 'harga' => 60000, 'durasi' => 1, 'kategori' => 'Gym Harian'],
        'gym_1bulan' => ['nama' => 'Gym 1 Bulan', 'harga_umum' => 285000, 'harga_pelajar' => 200000, 'durasi' => 30, 'kategori' => 'Gym Bulanan'],
        'gym_3bulan' => ['nama' => 'Gym 3 Bulan', 'harga_umum' => 675000, 'harga_pelajar' => 550000, 'durasi' => 90, 'kategori' => 'Gym 3 Bulan'],
        'gym_6bulan' => ['nama' => 'Gym 6 Bulan', 'harga_umum' => 1250000, 'harga_pelajar' => 1000000, 'durasi' => 180, 'kategori' => 'Gym 6 Bulan'],
        'gym_1tahun' => ['nama' => 'Gym 1 Tahun', 'harga_umum' => 2300000, 'harga_pelajar' => 1850000, 'durasi' => 365, 'kategori' => 'Gym 1 Tahun'],
        'kelas_20k' => ['nama' => 'Kelas Zumba/Aero BL/Strong Nation', 'harga' => 20000, 'durasi' => 1, 'kategori' => 'Kelas'],
        'kelas_25k' => ['nama' => 'Kelas CID/Body Shape/Senam BL', 'harga' => 25000, 'durasi' => 1, 'kategori' => 'Kelas'],
        'kelas_30k' => ['nama' => 'Kelas Boxing/Kapha Yoga', 'harga' => 30000, 'durasi' => 1, 'kategori' => 'Kelas'],
        'boxing_1bulan' => ['nama' => 'Boxing 1 Bulan', 'harga' => 300000, 'durasi' => 30, 'kategori' => 'Boxing'],
        'program_trainer' => ['nama' => 'Program Trainer', 'harga' => 1500000, 'durasi' => 30, 'kategori' => 'Personal Training']
    ];
    
    if (isset($custom_packages[$package_id])) {
        $package_data = $custom_packages[$package_id];
    }
}

if (!$package_data) {
    header("Location: indexmemberr.php");
    exit;
}

// Hitung harga berdasarkan tipe member
if (isset($package_data['harga_umum']) && isset($package_data['harga_pelajar'])) {
    $harga = $member_type === 'pelajar' ? $package_data['harga_pelajar'] : $package_data['harga_umum'];
} else {
    $harga = $package_data['harga'];
}

$nama_paket = $package_data['nama_paket'] ?? $package_data['nama'];
$durasi = $package_data['durasi_hari'] ?? $package_data['durasi'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Checkout - Arena FIT</title>

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

    .checkout-container {
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .checkout-card {
      background: rgba(13, 27, 42, 0.9);
      border: 2px solid rgba(66, 165, 245, 0.3);
      border-radius: 20px;
      padding: 40px;
      margin-bottom: 30px;
    }

    .page-title {
      font-size: 2rem;
      font-weight: 700;
      color: white;
      margin-bottom: 10px;
    }

    .page-subtitle {
      color: rgba(255, 255, 255, 0.6);
      margin-bottom: 30px;
    }

    .section-title {
      font-size: 1.3rem;
      font-weight: 700;
      color: #42a5f5;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid rgba(66, 165, 245, 0.3);
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      padding: 15px;
      margin-bottom: 10px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 10px;
    }

    .info-label {
      color: rgba(255, 255, 255, 0.7);
      font-weight: 500;
    }

    .info-value {
      color: white;
      font-weight: 600;
    }

    .price-highlight {
      font-size: 1.8rem;
      color: #ffc107;
      font-weight: 700;
    }

    .form-label {
      color: rgba(255, 255, 255, 0.8);
      font-weight: 600;
      margin-bottom: 8px;
    }

    .form-control, .form-select {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(66, 165, 245, 0.3);
      color: white;
      border-radius: 10px;
      padding: 12px 15px;
    }

    .form-control:focus, .form-select:focus {
      background: rgba(255, 255, 255, 0.15);
      border-color: #42a5f5;
      color: white;
      box-shadow: 0 0 0 0.25rem rgba(66, 165, 245, 0.25);
    }

    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.4);
    }

    textarea.form-control {
      resize: vertical;
      min-height: 100px;
    }

    .bank-info {
      background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 152, 0, 0.15) 100%);
      border: 2px solid rgba(255, 193, 7, 0.3);
      border-radius: 15px;
      padding: 20px;
      margin: 20px 0;
    }

    .bank-title {
      font-weight: 700;
      color: #ffc107;
      margin-bottom: 15px;
      font-size: 1.1rem;
    }

    .bank-detail {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
      color: rgba(255, 255, 255, 0.9);
    }

    .bank-detail strong {
      color: white;
      min-width: 120px;
    }

    .file-upload-wrapper {
      position: relative;
      overflow: hidden;
      display: inline-block;
      width: 100%;
    }

    .file-upload-label {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 40px;
      background: rgba(66, 165, 245, 0.1);
      border: 2px dashed rgba(66, 165, 245, 0.4);
      border-radius: 15px;
      cursor: pointer;
      transition: all 0.3s;
      text-align: center;
    }

    .file-upload-label:hover {
      background: rgba(66, 165, 245, 0.2);
      border-color: #42a5f5;
    }

    .file-upload-input {
      position: absolute;
      opacity: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;
    }

    .file-upload-text {
      color: rgba(255, 255, 255, 0.7);
    }

    .file-preview {
      margin-top: 15px;
      text-align: center;
    }

    .file-preview img {
      max-width: 100%;
      max-height: 300px;
      border-radius: 10px;
      border: 2px solid rgba(66, 165, 245, 0.3);
    }

    .btn-submit {
      background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
      border: none;
      color: white;
      padding: 15px 40px;
      border-radius: 10px;
      font-weight: 600;
      font-size: 1.1rem;
      width: 100%;
      transition: all 0.3s;
    }

    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(66, 165, 245, 0.4);
    }

    .btn-back {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: white;
      padding: 12px 30px;
      border-radius: 10px;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s;
    }

    .btn-back:hover {
      background: rgba(255, 255, 255, 0.2);
      color: white;
    }

    .alert-info {
      background: rgba(66, 165, 245, 0.1);
      border: 1px solid rgba(66, 165, 245, 0.3);
      color: rgba(255, 255, 255, 0.9);
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 20px;
    }

    @media (max-width: 768px) {
      .checkout-card {
        padding: 25px;
      }
      
      .page-title {
        font-size: 1.5rem;
      }
      
      .info-row {
        flex-direction: column;
        gap: 5px;
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

  <!-- CHECKOUT CONTENT -->
  <div class="checkout-container">
    <div class="checkout-card">
      <h1 class="page-title">Checkout Pembayaran</h1>
      <p class="page-subtitle">Lengkapi data pembayaran Anda</p>

      <!-- Package Information -->
      <div class="section-title">📦 Detail Paket</div>
      <div class="info-row">
        <span class="info-label">Nama Paket</span>
        <span class="info-value"><?php echo htmlspecialchars($nama_paket); ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Kategori</span>
        <span class="info-value"><?php echo htmlspecialchars($package_data['kategori'] ?? 'Membership'); ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Durasi</span>
        <span class="info-value"><?php echo $durasi; ?> Hari</span>
      </div>
      <div class="info-row">
        <span class="info-label">Tipe Member</span>
        <span class="info-value" style="text-transform: capitalize;">
          <?php 
            if($member_type === 'pelajar') {
              echo '<span style="color: #4caf50;">🎓 Pelajar / Mahasiswa</span>';
            } else {
              echo '<span style="color: #42a5f5;">👤 Umum</span>';
            }
          ?>
        </span>
      </div>
      <div class="info-row">
        <span class="info-label">Total Pembayaran</span>
        <span class="info-value price-highlight">Rp <?php echo number_format($harga, 0, ',', '.'); ?></span>
      </div>

      <!-- Bank Information -->
      <div class="bank-info">
        <div class="bank-title">💳 Informasi Transfer</div>
        <div class="bank-detail">
          <strong>Bank:</strong>
          <span>BCA</span>
        </div>
        <div class="bank-detail">
          <strong>No. Rekening:</strong>
          <span>2009138999</span>
        </div>
        <div class="bank-detail">
          <strong>Atas Nama:</strong>
          <span>CV. ARENA MAJU BERSAMA</span>
        </div>
        <div class="bank-detail">
          <strong>Jumlah Transfer:</strong>
          <span style="color: #ffc107; font-weight: 700; font-size: 1.1rem;">Rp <?php echo number_format($harga, 0, ',', '.'); ?></span>
        </div>
      </div>

      <div class="alert alert-info">
        <strong>ℹ️ Catatan:</strong> 
        <?php if($member_type === 'pelajar'): ?>
          <p style="margin: 10px 0 0 0;">Anda memilih paket untuk <strong>Pelajar/Mahasiswa</strong>. Silakan transfer sesuai nominal yang tertera, kemudian upload bukti pembayaran dan <strong style="color: #ffc107;">bukti kartu pelajar/KTM</strong> di bawah ini.</p>
        <?php else: ?>
          <p style="margin: 10px 0 0 0;">Silakan transfer sesuai nominal yang tertera, kemudian upload bukti pembayaran di bawah ini.</p>
        <?php endif; ?>
      </div>

      <?php if($member_type === 'pelajar'): ?>
      <div style="background: rgba(76, 175, 80, 0.1); border: 2px solid rgba(76, 175, 80, 0.3); border-radius: 15px; padding: 20px; margin: 20px 0;">
        <div style="font-weight: 700; color: #4caf50; margin-bottom: 10px; font-size: 1.1rem;">
          🎓 Khusus Pelajar / Mahasiswa
        </div>
        <div style="color: rgba(255, 255, 255, 0.9); line-height: 1.6;">
          Anda mendapatkan <strong style="color: #ffc107;">harga spesial</strong>! Pastikan untuk mengupload:<br>
          ✓ Bukti transfer pembayaran<br>
          ✓ Foto Kartu Pelajar / KTM yang masih berlaku
        </div>
      </div>
            <div class="alert alert-info">
        <strong>ℹ️ Catatan:</strong> 
        <?php if($member_type === 'pelajar'): ?>
          <p style="margin: 10px 0 0 0;">Anda memilih paket untuk <strong>Pelajar/Mahasiswa</strong>. Silakan transfer sesuai nominal yang tertera, kemudian upload bukti pembayaran dan <strong style="color: #ffc107;">bukti kartu pelajar/KTM</strong> di bawah ini.</p>
        <?php else: ?>
          <p style="margin: 10px 0 0 0;">Silakan transfer sesuai nominal yang tertera, kemudian upload bukti pembayaran di bawah ini.</p>
        <?php endif; ?>
      </div>

      <?php if($member_type === 'pelajar'): ?>
      <div style="background: rgba(76, 175, 80, 0.1); border: 2px solid rgba(76, 175, 80, 0.3); border-radius: 15px; padding: 20px; margin: 20px 0;">
        <div style="font-weight: 700; color: #4caf50; margin-bottom: 10px; font-size: 1.1rem;">
          🎓 Khusus Pelajar / Mahasiswa
        </div>
        <div style="color: rgba(255, 255, 255, 0.9); line-height: 1.6;">
          Anda mendapatkan <strong style="color: #ffc107;">harga spesial</strong>! Pastikan untuk mengupload:<br>
          ✓ Bukti transfer pembayaran<br>
          ✓ Foto Kartu Pelajar / KTM yang masih berlaku
        </div>
      </div> 

      <?php endif; ?>
      
      <?php endif; ?>

      <!-- Payment Form -->
      <form action="proses_pembayaran.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_member" value="<?php echo $id_member; ?>">
        <input type="hidden" name="package_id" value="<?php echo htmlspecialchars($package_id); ?>">
        <input type="hidden" name="package_name" value="<?php echo htmlspecialchars($nama_paket); ?>">
        <input type="hidden" name="package_type" value="<?php echo htmlspecialchars($package_type); ?>">
        <input type="hidden" name="member_type" value="<?php echo htmlspecialchars($member_type); ?>">
        <input type="hidden" name="harga" value="<?php echo $harga; ?>">
        <input type="hidden" name="durasi" value="<?php echo $durasi; ?>">
        <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($package_data['kategori'] ?? 'Membership'); ?>">

        <div class="section-title">📤 Upload Bukti Pembayaran</div>
        
        <div class="mb-4">
          <label class="form-label">Bukti Transfer <span style="color: #f44336;">*</span></label>
          <div class="file-upload-wrapper">
            <label for="bukti_pembayaran" class="file-upload-label">
              <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
              </svg>
              <div>
                <div class="file-upload-text" style="font-weight: 600; font-size: 1rem; color: white;">
                  Klik untuk upload bukti transfer
                </div>
                <div class="file-upload-text" style="font-size: 0.85rem;">
                  Format: JPG, PNG, JPEG (Max 5MB)
                </div>
              </div>
            </label>
            <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" class="file-upload-input" accept="image/*" required>
          </div>
          <div id="file-preview" class="file-preview"></div>
        </div>

        <?php if($member_type === 'pelajar'): ?>
        <div class="mb-4">
          <label class="form-label">Foto Kartu Pelajar / KTM <span style="color: #f44336;">*</span></label>
          <div class="file-upload-wrapper">
            <label for="kartu_pelajar" class="file-upload-label" style="border-color: rgba(76, 175, 80, 0.4); background: rgba(76, 175, 80, 0.1);">
              <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
              </svg>
              <div>
                <div class="file-upload-text" style="font-weight: 600; font-size: 1rem; color: white;">
                  Klik untuk upload kartu pelajar/KTM
                </div>
                <div class="file-upload-text" style="font-size: 0.85rem;">
                  Format: JPG, PNG, JPEG (Max 5MB)
                </div>
              </div>
            </label>
            <input type="file" id="kartu_pelajar" name="kartu_pelajar" class="file-upload-input" accept="image/*" required>
          </div>
          <div id="kartu-preview" class="file-preview"></div>
        </div>
        <?php endif; ?>

        <div class="mb-4">
          <label class="form-label">Catatan (Opsional)</label>
          <textarea class="form-control" name="catatan" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
        </div>

        <div class="d-flex gap-3 mt-4">
          <a href="indexmemberr.php" class="btn-back">← Kembali</a>
          <button type="submit" class="btn-submit">Konfirmasi Pembayaran</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Preview uploaded image - Bukti Pembayaran
    document.getElementById('bukti_pembayaran').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('file-preview');
      
      if (file) {
        // Check file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
          alert('Ukuran file terlalu besar! Maksimal 5MB');
          e.target.value = '';
          preview.innerHTML = '';
          return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
        };
        reader.readAsDataURL(file);
      } else {
        preview.innerHTML = '';
      }
    });

    // Preview uploaded image - Kartu Pelajar
    const kartuPelajarInput = document.getElementById('kartu_pelajar');
    if(kartuPelajarInput) {
      kartuPelajarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('kartu-preview');
        
        if (file) {
          // Check file size (5MB)
          if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 5MB');
            e.target.value = '';
            preview.innerHTML = '';
            return;
          }
          
          const reader = new FileReader();
          reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview Kartu">';
          };
          reader.readAsDataURL(file);
        } else {
          preview.innerHTML = '';
        }
      });
    }
  </script>
</body>
</html>