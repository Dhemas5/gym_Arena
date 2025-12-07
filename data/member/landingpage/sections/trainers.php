<?php
// Koneksi ke database db_gym
$host = 'localhost';
$username = 'root'; // sesuaikan dengan username database Anda
$password = ''; // sesuaikan dengan password database Anda
$database = 'db_gym';

$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk mengambil data instruktur dari tabel tbl_instruktur
$sql = "SELECT * FROM tbl_instruktur ORDER BY id_instruktur DESC";
$result = $conn->query($sql);

$trainers = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Handle foto dari database
        $foto = '';
        
        if (!empty($row['foto'])) {
            // Cek apakah foto adalah nama file dan file ada di folder
            $foto_path = '../../../data/admin/img/' . $row['foto'];
            if (file_exists($foto_path)) {
                $foto = $foto_path;
            }
            // Jika tidak ditemukan di folder, coba gunakan path langsung
            elseif (file_exists($row['foto'])) {
                $foto = $row['foto'];
            }
        }
        
        // Jika foto tidak ditemukan, gunakan default
        if (empty($foto)) {
            $foto = 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=400&h=500&fit=crop';
        }
        
        // Ambil deskripsi dari kolom catatan
        $deskripsi = '';
        
        // Gunakan catatan sebagai deskripsi
        if (!empty($row['catatan'])) {
            $deskripsi = $row['catatan'];
        } else {
            $deskripsi = 'Instruktur profesional dengan pengalaman luas di bidang fitness dan kesehatan.';
        }
        
        // Potong deskripsi jika terlalu panjang
        if (strlen($deskripsi) > 150) {
            $deskripsi = substr($deskripsi, 0, 147) . '...';
        }
        
        // Parsing spesialisasi (jika ada koma, jadikan array)
        $specialties = [];
        if (!empty($row['spesialisasi'])) {
            $specialties = array_map('trim', explode(',', $row['spesialisasi']));
        } else {
            $specialties = ['Fitness'];
        }
        
        // Ambil data instagram
        $instagram = !empty($row['instagram']) ? $row['instagram'] : '';
        
        // Tentukan jadwal mengajar (default atau dari database lain)
        // NOTE: Anda mungkin perlu menyesuaikan ini dengan tabel jadwal
        $jadwal = 'Belum diatur';
        
        $trainers[] = [
            'id' => $row['id_instruktur'],
            'image' => $foto,
            'name' => $row['nama_instruktur'] ?? 'Instruktur',
            'specialties' => $specialties,
            'schedule' => $jadwal,
            'desc' => $deskripsi,
            'certifications' => !empty($row['spesialisasi']) ? [$row['spesialisasi']] : ['Certified Trainer'],
            'instagram' => $instagram,
            'catatan' => $row['catatan'] ?? ''
        ];
    }
} else {
    // Data default jika tidak ada data di database
    $trainers = [
        [
            'id' => 1,
            'image' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=400&h=500&fit=crop',
            'name' => 'Coach Fitri',
            'specialties' => ['Senam BL', 'Yoga'],
            'schedule' => 'Senin, Jumat',
            'desc' => '8 tahun pengalaman sebagai instruktur yoga bersertifikat internasional.',
            'certifications' => ['RYT-500', 'Pilates certified'],
            'instagram' => 'coach_fitri'
        ],
        [
            'id' => 2,
            'image' => 'https://images.unsplash.com/photo-1567598508481-65985588e295?w=400&h=500&fit=crop',
            'name' => 'Coach Mieke',
            'specialties' => ['Body Shape', 'Strength Training'],
            'schedule' => 'Rabu, Kamis',
            'desc' => '10 tahun di bidang strength & conditioning.',
            'certifications' => ['NSCA-CPT', 'CrossFit Level 2'],
            'instagram' => 'coach_mieke'
        ]
    ];
}

$conn->close();
?>

<!-- TRAINERS SECTION -->
<section id="trainers" class="trainers-section">
  <div class="container">
    <!-- Header -->
    <div class="text-center mb-5">
      <h1 class="section-title">Instruktur <span class="text-danger">Profesional</span></h1>
      <p class="section-subtitle">Tim instruktur bersertifikat dan berpengalaman</p>
    </div>

    <!-- Trainers Carousel -->
    <div class="trainers-carousel-container">
      <div class="trainers-carousel">
        <?php
        foreach ($trainers as $trainer) {
          echo '
          <div class="trainer-slide">
            <div class="trainer-card">
              <div class="trainer-image">
                <img src="' . htmlspecialchars($trainer['image']) . '" alt="' . htmlspecialchars($trainer['name']) . '" onerror="this.src=\'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=400&h=500&fit=crop\'">
                <div class="image-overlay"></div>';
          
          // Tampilkan instagram badge jika ada
          if (!empty($trainer['instagram'])) {
            echo '<div class="trainer-instagram">
                    <a href="https://instagram.com/' . htmlspecialchars($trainer['instagram']) . '" target="_blank" class="instagram-link">
                      <span class="ig-icon">📱</span>
                      <span class="ig-username">@' . htmlspecialchars($trainer['instagram']) . '</span>
                    </a>
                  </div>';
          }
          
          echo '
              </div>
              <div class="trainer-info">
                <h3 class="trainer-name">' . htmlspecialchars($trainer['name']) . '</h3>
                
                <div class="trainer-badges">';
          
          foreach ($trainer['specialties'] as $specialty) {
            echo '<span class="badge-specialty">' . trim(htmlspecialchars($specialty)) . '</span>';
          }
          
          echo '
                </div>
                <p class="trainer-title">Spesialisasi</p>
                <p class="trainer-specialty">' . htmlspecialchars($trainer['specialties'][0] ?? 'Fitness Trainer') . '</p>
                
                <p class="trainer-desc">' . htmlspecialchars($trainer['desc']) . '</p>
                
                <div class="trainer-certs">
                  <p class="cert-title">Keahlian:</p>';
          
          foreach ($trainer['certifications'] as $cert) {
            echo '<span class="cert-badge">' . trim(htmlspecialchars($cert)) . '</span>';
          }
          
          echo '
                </div>';
          
          // Tampilkan instagram link di footer card jika ada
          if (!empty($trainer['instagram'])) {
            echo '<div class="trainer-footer">
                    <a href="https://instagram.com/' . htmlspecialchars($trainer['instagram']) . '" target="_blank" class="ig-profile-link">
                      <i class="fab fa-instagram"></i> Follow @' . htmlspecialchars($trainer['instagram']) . '
                    </a>
                  </div>';
          }
          
          echo '
              </div>
            </div>
          </div>';
        }
        ?>
      </div>
      
      <!-- Carousel Navigation -->
      <button class="carousel-nav carousel-prev" aria-label="Previous trainer">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <button class="carousel-nav carousel-next" aria-label="Next trainer">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      
      <!-- Carousel Indicators -->
      <div class="carousel-indicators">
        <?php
        for ($i = 0; $i < count($trainers); $i++) {
          echo '<button class="carousel-indicator' . ($i === 0 ? ' active' : '') . '" data-index="' . $i . '"></button>';
        }
        ?>
      </div>
    </div>
  </div>
</section>

<style>
/* ===================================
   TRAINERS SECTION
   =================================== */
.trainers-section {
  padding: 10px 0;
  background: #0d1b2a;
}

/* Carousel Container */
.trainers-carousel-container {
  position: relative;
  overflow: hidden;
  padding: 0 20px;
  margin-bottom: 50px;
}

.trainers-carousel {
  display: flex;
  transition: transform 0.5s ease;
  gap: 30px;
}

.trainer-slide {
  flex: 0 0 calc(25% - 22.5px);
  min-width: 0;
}

/* Trainer Card Styles */
.trainer-card {
  background: rgba(25, 118, 210, 0.05);
  border: 1px solid rgba(66, 165, 245, 0.2);
  border-radius: 15px;
  overflow: hidden;
  transition: all 0.3s;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.trainer-card:hover {
  transform: translateY(-5px);
  border-color: rgba(66, 165, 245, 0.5);
  box-shadow: 0 8px 25px rgba(25, 118, 210, 0.15);
}

.trainer-image {
  position: relative;
  height: 320px;
  overflow: hidden;
}

.trainer-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s;
}

.trainer-card:hover .trainer-image img {
  transform: scale(1.1);
}

.trainer-instagram {
  position: absolute;
  top: 15px;
  right: 15px;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(5px);
  padding: 8px 12px;
  border-radius: 20px;
  z-index: 2;
}

.instagram-link {
  display: flex;
  align-items: center;
  gap: 6px;
  color: white;
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: 600;
}

.instagram-link:hover {
  color: #e1306c;
}

.ig-icon {
  font-size: 0.9rem;
}

.trainer-info {
  padding: 25px;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.trainer-name {
  font-size: 1.4rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 12px;
}

.trainer-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 15px;
}

.badge-specialty {
  background: #1976d2;
  color: #fff;
  padding: 5px 15px;
  border-radius: 15px;
  font-size: 0.8rem;
  font-weight: 600;
}

.trainer-title {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.85rem;
  font-weight: 600;
  margin-bottom: 5px;
  margin-top: 10px;
}

.trainer-specialty {
  color: #fff;
  font-size: 0.95rem;
  font-weight: 600;
  margin-bottom: 15px;
}

.trainer-desc {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  line-height: 1.6;
  margin-bottom: 15px;
  flex-grow: 1;
}

.trainer-certs {
  padding-top: 15px;
  border-top: 1px solid rgba(66, 165, 245, 0.2);
  margin-bottom: 15px;
}

.cert-title {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.85rem;
  font-weight: 600;
  margin-bottom: 8px;
}

.cert-badge {
  display: inline-block;
  background: rgba(25, 118, 210, 0.15);
  color: #42a5f5;
  padding: 5px 12px;
  border-radius: 5px;
  font-size: 0.75rem;
  font-weight: 600;
  margin-right: 8px;
  margin-bottom: 5px;
  border: 1px solid rgba(66, 165, 245, 0.3);
}

.trainer-footer {
  margin-top: auto;
  padding-top: 15px;
  border-top: 1px solid rgba(66, 165, 245, 0.2);
}

.ig-profile-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #e1306c;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 600;
  transition: color 0.3s;
}

.ig-profile-link:hover {
  color: #c13584;
  text-decoration: underline;
}

.ig-profile-link i {
  font-size: 1rem;
}

/* Carousel Navigation */
.carousel-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(25, 118, 210, 0.7);
  border: none;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  transition: all 0.3s;
  z-index: 10;
}

.carousel-nav:hover {
  background: rgba(25, 118, 210, 1);
  transform: translateY(-50%) scale(1.1);
}

.carousel-prev {
  left: 0;
}

.carousel-next {
  right: 0;
}

.carousel-nav svg {
  width: 24px;
  height: 24px;
}

/* Carousel Indicators */
.carousel-indicators {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-top: 30px;
  position: relative;
  width: 100%;
  bottom: auto;
  left: auto;
  right: 14%;
}

.carousel-indicator {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  border: none;
  cursor: pointer;
  transition: all 0.3s;
}

.carousel-indicator.active {
  background: #1976d2;
  transform: scale(1.2);
}

.carousel-indicator:hover {
  background: rgba(25, 118, 210, 0.7);
}

/* Responsive Design */
@media (max-width: 1200px) {
  .trainer-slide {
    flex: 0 0 calc(33.333% - 20px);
  }
}

@media (max-width: 992px) {
  .trainer-slide {
    flex: 0 0 calc(50% - 15px);
  }
}

@media (max-width: 768px) {
  .trainers-carousel-container {
    padding: 0 40px;
  }
  
  .trainer-slide {
    flex: 0 0 100%;
  }
  
  .carousel-nav {
    width: 40px;
    height: 40px;
  }
  
  .trainer-instagram {
    top: 10px;
    right: 10px;
    padding: 6px 10px;
  }
}

@media (max-width: 576px) {
  .trainers-carousel-container {
    padding: 0 30px;
  }
  
  .carousel-nav {
    width: 35px;
    height: 35px;
  }
  
  .carousel-nav svg {
    width: 18px;
    height: 18px;
  }
  
  .trainer-image {
    height: 280px;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const carousel = document.querySelector('.trainers-carousel');
  const slides = document.querySelectorAll('.trainer-slide');
  const prevBtn = document.querySelector('.carousel-prev');
  const nextBtn = document.querySelector('.carousel-next');
  const indicators = document.querySelectorAll('.carousel-indicator');
  
  let currentIndex = 0;
  const slidesPerView = getSlidesPerView();
  
  function getSlidesPerView() {
    if (window.innerWidth < 768) return 1;
    if (window.innerWidth < 992) return 2;
    if (window.innerWidth < 1200) return 3;
    return 4;
  }
  
  function updateCarousel() {
    if (slides.length === 0) return;
    
    const slideWidth = slides[0].offsetWidth + 30;
    carousel.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
    
    // Update indicators
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle('active', index === currentIndex);
    });
  }
  
  function nextSlide() {
    const maxIndex = Math.max(0, slides.length - slidesPerView);
    currentIndex = currentIndex < maxIndex ? currentIndex + 1 : 0;
    updateCarousel();
  }
  
  function prevSlide() {
    const maxIndex = Math.max(0, slides.length - slidesPerView);
    currentIndex = currentIndex > 0 ? currentIndex - 1 : maxIndex;
    updateCarousel();
  }
  
  // Event listeners
  if (prevBtn) prevBtn.addEventListener('click', prevSlide);
  if (nextBtn) nextBtn.addEventListener('click', nextSlide);
  
  indicators.forEach(indicator => {
    indicator.addEventListener('click', function() {
      currentIndex = parseInt(this.getAttribute('data-index'));
      updateCarousel();
    });
  });
  
  // Handle window resize
  window.addEventListener('resize', function() {
    const newSlidesPerView = getSlidesPerView();
    if (newSlidesPerView !== slidesPerView) {
      currentIndex = 0;
      updateCarousel();
    }
  });
  
  // Initialize carousel
  updateCarousel();
  
  // Auto slide jika ada lebih dari 4 instruktur
  if (slides.length > 4) {
    setInterval(nextSlide, 5000);
  }
});
</script>