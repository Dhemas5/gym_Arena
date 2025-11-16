<!-- CLASSES SECTION -->
<section id="classes" class="classes-section">
  <div class="container">
    <!-- Header -->
    <div class="text-center mb-5">
      <h1 class="section-title">Kelas & <span class="text-danger">Jadwal</span></h1>
      <p class="section-subtitle">Pilih kelas favorit Anda dan bergabunglah dengan kami</p>
    </div>

    <!-- Filter Tabs -->
    <div class="class-tabs text-center mb-5">
      <button class="tab-btn active">Kelas Populer</button>
      <button class="tab-btn">Semua Kelas</button>
    </div>

    <!-- Class Cards -->
    <div class="row g-4">

      <?php
      // Koneksi database
      require "../../../setting/koneksi.php";
      
      // Query untuk mengambil data kategori dari database
      $queryKategori = mysqli_query($con, "SELECT * FROM tbl_kategori ORDER BY id_kategori ASC");
      
      if (mysqli_num_rows($queryKategori) == 0) {
        echo '<div class="col-12 text-center"><p>Tidak ada data kategori</p></div>';
      } else {
        while ($kategori = mysqli_fetch_array($queryKategori)) {
          // Tentukan gambar default jika foto tidak ada
          $gambar = !empty($kategori['foto']) ? '../../../data/admin/img/' . $kategori['foto'] : 'https://images.unsplash.com/photo-1588286840104-8957b019727f?w=500&h=300&fit=crop';
          
          // Tentukan jadwal berdasarkan nama kategori atau gunakan default
          $jadwal = "Semua Level"; // Default schedule
          if (strpos(strtolower($kategori['nama_kategori']), 'pemula') !== false) {
            $jadwal = "Pemula";
          } elseif (strpos(strtolower($kategori['nama_kategori']), 'menengah') !== false) {
            $jadwal = "Menengah";
          } elseif (strpos(strtolower($kategori['nama_kategori']), 'lanjut') !== false) {
            $jadwal = "Lanjut";
          }
          
          echo '
          <div class="col-lg-4 col-md-6">
            <div class="class-card">
              <div class="class-image">
                <img src="' . $gambar . '" alt="' . htmlspecialchars($kategori['nama_kategori']) . '">
                <div class="class-overlay">
                  <h3 class="class-name">' . htmlspecialchars($kategori['nama_kategori']) . '</h3>
                </div>
              </div>
              <div class="class-body">
                <p class="class-desc">' . (!empty($kategori['deskripsi']) ? htmlspecialchars($kategori['deskripsi']) : 'Deskripsi kelas akan segera tersedia') . '</p>
                <p class="class-schedule">' . $jadwal . '</p>
                <button class="btn-booking">Booking Kelas</button>
              </div>
            </div>
          </div>';
        }
      }
      
      // Tutup koneksi
      mysqli_close($con);
      ?>

    </div>
  </div>
</section>