<!-- MEMBERSHIP SECTION -->
<section id="membership" class="membership-section">
  <div class="container">
    <!-- Header -->
    <div class="text-center mb-5">
      <h1 class="section-title">Paket <span class="text-danger">Membership</span></h1>
      <p class="section-subtitle">Pilih paket yang sesuai dengan kebutuhan Anda</p>
    </div>

    <!-- Pricing Cards -->
    <div class="row g-4 justify-content-center">

      <?php
      // Koneksi ke database
      require "../../../setting/koneksi.php";
      
      // Query untuk mengambil data paket dari database
      $query = "SELECT p.*, k.nama_kategori FROM tbl_paket p 
                LEFT JOIN tbl_kategori k ON p.id_kategori = k.id_kategori 
                WHERE p.durasi_hari IN (1, 30, 90, 180, 365) 
                ORDER BY p.durasi_hari ASC";
      
      $result = $con->query($query);
      
      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          // Tentukan tipe berdasarkan durasi_hari
          $tipe = match ($row['durasi_hari']) {
            1 => 'Harian',
            30 => '1 Bulan',
            90 => '3 Bulan',
            180 => '6 Bulan',
            365 => '12 Bulan',
            default => $row['durasi_hari'] . ' Hari'
          };
          
          // Tentukan period berdasarkan durasi
          $period = match ($row['durasi_hari']) {
            1 => 'per hari',
            30 => 'per bulan',
            90 => 'per 3 bulan',
            180 => 'per 6 bulan',
            365 => 'per tahun',
            default => 'per ' . $row['durasi_hari'] . ' hari'
          };
          
          // Format harga
          $harga_umum = number_format($row['harga_umum'], 0, ',', '.');
          $harga_mahasiswa = number_format($row['harga_mahasiswa'], 0, ',', '.');
          
          // Tandai paket 3 bulan sebagai paling populer
          $popular = ($row['durasi_hari'] == 90);
          $popular_class = $popular ? 'popular' : '';
          $popular_btn = $popular ? 'popular-btn' : '';
          
          // Features dari database atau default
          $features = [];
          if (!empty($row['deskripsi'])) {
            $features = array_filter(array_map('trim', explode(',', $row['deskripsi'])));
          }
          
          if (empty($features)) {
            $features = [
              'Fasilitas gym lengkap',
              'Full access gym dan studio kelas',
              'Full AC',
              'Kamar mandi dan ruang ganti',
              'Loker gratis'
            ];
            
            // Tambahkan bonus berdasarkan durasi
            if ($row['durasi_hari'] == 90) {
              $features[] = 'Free Payung';
            } elseif ($row['durasi_hari'] == 180) {
              $features[] = 'Free Kaos';
            } elseif ($row['durasi_hari'] == 365) {
              $features[] = 'Free Tas Gym';
            }
          }
          
          echo '
          <div class="col-lg-4 col-md-6">
            <div class="pricing-card ' . $popular_class . '">';
          
          if ($popular) {
            echo '<span class="popular-badge">Paling Populer</span>';
          }
          
          // Tampilkan kategori
          if (!empty($row['nama_kategori'])) {
            echo '<span class="category-badge">' . htmlspecialchars($row['nama_kategori']) . '</span>';
          }
          
          echo '
              <h3 class="pricing-title">' . $tipe . '</h3>
              <p class="pricing-subtitle">' . $period . '</p>
              
              <div class="pricing-price">
                <div class="price-container">
                  <span class="price-currency">Rp</span>
                  <span class="price-amount">' . $harga_umum . '</span>
                </div>
              </div>
              
              <div class="student-price">
                <div class="student-price-label">Mahasiswa</div>
                <div class="student-price-amount">
                  <span class="student-price-currency">Rp</span>
                  ' . $harga_mahasiswa . '
                </div>
              </div>

              <ul class="pricing-features">';
          
          foreach ($features as $feature) {
            echo '<li><span class="check-icon">✓</span> ' . htmlspecialchars($feature) . '</li>';
          }
          
          echo '
              </ul>
              <a href="../login/register.php?durasi=' . $row['durasi_hari'] . '" class="btn-pricing ' . $popular_btn . '">Daftar Sekarang</a>
            </div>
          </div>';
        }
      } else {
        // Fallback jika tidak ada data dari database
        echo '<div class="col-12 text-center">
                <p class="text-muted">Tidak ada paket membership tersedia saat ini.</p>
              </div>';
      }
      
      // Tutup koneksi
      $con->close();
      ?>

    </div>

    <!-- Footer Note -->
    <div class="text-center mt-5">
      <p class="pricing-note">Semua paket termasuk akses ke fasilitas shower, WiFi gratis, dan parkir</p>
    </div>
  </div>
</section>