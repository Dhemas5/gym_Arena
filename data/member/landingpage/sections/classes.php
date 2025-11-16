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
      <button class="tab-btn active" data-tab="popular">Kelas Populer</button>
      <button class="tab-btn" data-tab="all">Semua Kelas</button>
    </div>

    <!-- Container untuk konten tab -->
    <div class="tab-container">
      <!-- Kelas Populer -->
      <div class="tab-content active" id="tab-popular">
        <div class="row g-4">
          <?php
          // Koneksi database
          require "../../../setting/koneksi.php";
          
          // Query untuk mengambil data kategori populer
          $queryKategori = mysqli_query($con, "SELECT * FROM tbl_kategori WHERE kelas_populer = 1 ORDER BY id_kategori ASC");
          
          if (mysqli_num_rows($queryKategori) == 0) {
            echo '<div class="col-12 text-center"><p class="text-muted">Tidak ada kelas populer saat ini</p></div>';
          } else {
            while ($kategori = mysqli_fetch_array($queryKategori)) {
              // Tentukan gambar default jika foto tidak ada
              $gambar = !empty($kategori['foto']) ? '../../../data/admin/img/' . $kategori['foto'] : '../../../data/admin/img/default.jpg';
              
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
                    <p class="class-schedule"><strong>Level:</strong> ' . $jadwal . '</p>
                    <button class="btn-booking">Booking Kelas</button>
                  </div>
                </div>
              </div>';
            }
          }
          ?>
        </div>
      </div>

      <!-- Semua Kelas -->
      <div class="tab-content" id="tab-all">
        <div class="row g-4">
          <?php
          // Query untuk mengambil semua data kategori
          $queryKategoriAll = mysqli_query($con, "SELECT * FROM tbl_kategori ORDER BY id_kategori ASC");
          
          if (mysqli_num_rows($queryKategoriAll) == 0) {
            echo '<div class="col-12 text-center"><p class="text-muted">Tidak ada data kategori</p></div>';
          } else {
            while ($kategori = mysqli_fetch_array($queryKategoriAll)) {
              // Tentukan gambar default jika foto tidak ada
              $gambar = !empty($kategori['foto']) ? '../../../data/admin/img/' . $kategori['foto'] : '../../../data/admin/img/default.jpg';
              
              // Tentukan jadwal berdasarkan nama kategori atau gunakan default
              $jadwal = "Semua Level"; // Default schedule
              if (strpos(strtolower($kategori['nama_kategori']), 'pemula') !== false) {
                $jadwal = "Pemula";
              } elseif (strpos(strtolower($kategori['nama_kategori']), 'menengah') !== false) {
                $jadwal = "Menengah";
              } elseif (strpos(strtolower($kategori['nama_kategori']), 'lanjut') !== false) {
                $jadwal = "Lanjut";
              }
              
              // Tampilkan badge untuk kelas populer
              $populerBadge = $kategori['kelas_populer'] == 1 ? '<span class="badge-populer">⭐ Populer</span>' : '';
              
              echo '
              <div class="col-lg-4 col-md-6">
                <div class="class-card">
                  <div class="class-image">
                    ' . $populerBadge . '
                    <img src="' . $gambar . '" alt="' . htmlspecialchars($kategori['nama_kategori']) . '">
                    <div class="class-overlay">
                      <h3 class="class-name">' . htmlspecialchars($kategori['nama_kategori']) . '</h3>
                    </div>
                  </div>
                  <div class="class-body">
                    <p class="class-desc">' . (!empty($kategori['deskripsi']) ? htmlspecialchars($kategori['deskripsi']) : 'Deskripsi kelas akan segera tersedia') . '</p>
                    <p class="class-schedule"><strong>Level:</strong> ' . $jadwal . '</p>
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
    </div>
  </div>
</section>

<style>
/* Style untuk badge populer */
.badge-populer {
  position: absolute;
  top: 10px;
  right: 10px;
  background: linear-gradient(45deg, #FFD700, #FFA500);
  color: #000;
  padding: 5px 10px;
  border-radius: 15px;
  font-size: 0.8rem;
  font-weight: 600;
  z-index: 2;
  box-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
}

/* Style untuk tab container */
.tab-container {
  position: relative;
}

.tab-content {
  display: none;
  animation: fadeIn 0.5s ease-in;
}

.tab-content.active {
  display: block;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Pastikan class-card memiliki position relative untuk badge */
.class-card {
  position: relative;
}

.class-image {
  position: relative;
}
</style>

<!-- JavaScript untuk tab functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const tabBtns = document.querySelectorAll('.tab-btn');
  const tabContents = document.querySelectorAll('.tab-content');
  
  // Fungsi untuk switch tab
  function switchTab(tabName) {
    // Remove active class from all buttons and contents
    tabBtns.forEach(b => b.classList.remove('active'));
    tabContents.forEach(c => c.classList.remove('active'));
    
    // Add active class to clicked button and corresponding content
    const activeBtn = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
    const activeContent = document.getElementById(`tab-${tabName}`);
    
    if (activeBtn && activeContent) {
      activeBtn.classList.add('active');
      activeContent.classList.add('active');
    }
  }
  
  // Event listener untuk tab buttons
  tabBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const tabId = this.getAttribute('data-tab');
      switchTab(tabId);
    });
  });
  
  // Inisialisasi tab pertama sebagai aktif
  switchTab('popular');
});
</script>