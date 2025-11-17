<!-- TESTIMONIAL SECTION -->
<section id="testimonial" class="testimonial-section">
  <div class="container">
    <!-- Header -->
    <div class="text-center mb-5">
      <h1 class="section-title">Testimoni <span class="text-danger">Member</span></h1>
      <p class="section-subtitle">Dengar cerita sukses dari member kami</p>
    </div>

    <!-- Testimonial Carousel -->
    <div class="testimonial-carousel-container">
      <div class="testimonial-carousel">
        <?php
        // Koneksi ke database
        $host = "localhost";
        $username = "root"; // Ganti dengan username database Anda
        $password = ""; // Ganti dengan password database Anda
        $database = "db_gym";
        
        $conn = new mysqli($host, $username, $password, $database);
        
        // Cek koneksi
        if ($conn->connect_error) {
            die("Koneksi gagal: " . $conn->connect_error);
        }
        
        // Query untuk mengambil testimonial yang sudah dipublish
        // Berdasarkan data yang ada, kita ambil langsung dari tbl_testimoni
        $sql = "SELECT testimoni, rating, member_id, created_at 
                FROM tbl_testimoni 
                WHERE status = 'publish' 
                ORDER BY created_at DESC";
        
        $result = $conn->query($sql);
        $testimonials = [];
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Untuk nama member, kita bisa menggunakan member_id atau default name
                $member_name = "Member " . $row['member_id'];
                
                // Coba ambil nama member dari tbl_member jika ada
                $member_sql = "SELECT nama FROM tbl_member WHERE id_member = " . $row['member_id'];
                $member_result = $conn->query($member_sql);
                if ($member_result && $member_result->num_rows > 0) {
                    $member_data = $member_result->fetch_assoc();
                    $member_name = $member_data['nama'];
                }
                
                $testimonials[] = [
                    'name' => $member_name,
                    'role' => 'Member Aktif',
                    'image' => 'https://i.pravatar.cc/100?img=' . (($row['member_id'] % 70) + 1), // Avatar berdasarkan member_id
                    'quote' => $row['testimoni'],
                    'stars' => $row['rating']
                ];
            }
        }
        
        // Jika tidak ada testimonial dari database, gunakan data default
        if (empty($testimonials)) {
            $testimonials = [
              [
                'name' => 'Andi Wijaya',
                'role' => 'Member sejak 2023',
                'image' => 'https://i.pravatar.cc/100?img=12',
                'quote' => 'Arena FIT benar-benar mengubah hidup saya! Instruktur yang profesional dan fasilitas yang lengkap membuat saya termotivasi setiap hari. Dalam 6 bulan, saya berhasil menurunkan 15kg!',
                'stars' => 5
              ],
              [
                'name' => 'Sarah Putri',
                'role' => 'Member sejak 2022',
                'image' => 'https://i.pravatar.cc/100?img=5',
                'quote' => 'Program Body Shape dengan Coach Mieke sangat efektif! Saya tidak pernah merasa lebih percaya diri. Komunitas di sini juga sangat supportive.',
                'stars' => 5
              ]
            ];
        }
        
        foreach ($testimonials as $testimonial) {
          echo '
          <div class="testimonial-slide">
            <div class="testimonial-card">
              <!-- Stars -->
              <div class="testimonial-stars mb-3">';
          
          // Generate stars based on rating
          for ($i = 1; $i <= 5; $i++) {
            if ($i <= $testimonial['stars']) {
              echo '<i class="star-icon active">★</i>';
            } else {
              echo '<i class="star-icon">★</i>';
            }
          }
          
          echo '
              </div>

              <!-- Quote -->
              <p class="testimonial-quote">
                ' . htmlspecialchars($testimonial['quote']) . '
              </p>

              <!-- Author -->
              <div class="testimonial-author">
                <img src="' . $testimonial['image'] . '" alt="' . $testimonial['name'] . '" class="author-img">
                <div>
                  <h5 class="author-name">' . $testimonial['name'] . '</h5>
                  <p class="author-role">' . $testimonial['role'] . '</p>
                </div>
              </div>
            </div>
          </div>';
        }
        
        $conn->close();
        ?>
      </div>
      
      <!-- Carousel Navigation -->
      <button class="testimonial-nav testimonial-prev" aria-label="Previous testimonial">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <button class="testimonial-nav testimonial-next" aria-label="Next testimonial">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      
      <!-- Carousel Indicators -->
      <div class="testimonial-indicators">
        <?php
        for ($i = 0; $i < count($testimonials); $i++) {
          echo '<button class="testimonial-indicator' . ($i === 0 ? ' active' : '') . '" data-index="' . $i . '"></button>';
        }
        ?>
      </div>
    </div>
  </div>
</section>

<!-- CSS dan JavaScript tetap sama seperti sebelumnya -->
<style>
/* ===================================
   TESTIMONIAL SECTION - WARNA SAMA DENGAN TRAINERS
   =================================== */
.testimonial-section {
  padding: 100px 0;
  background: #0d1b2a; /* Background sama dengan trainers */
  position: relative;
  overflow: hidden;
}

.testimonial-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" opacity="0.02"><polygon fill="%2342a5f5" points="0,1000 1000,0 1000,1000"/></svg>');
  background-size: cover;
}

.testimonial-section .container {
  position: relative;
  z-index: 1;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.testimonial-carousel-container {
  position: relative;
  overflow: hidden;
  margin: 0 auto;
  padding: 0 20px;
  max-width: 800px; 
  width: 100%;
}

.testimonial-carousel {
  display: flex;
  transition: transform 0.5s ease;
  gap: 0;
}

.testimonial-slide {
  flex: 0 0 100%;
  min-width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Testimonial Card Styles */
.testimonial-card {
  background: rgba(25, 118, 210, 0.05);
  border: 1px solid rgba(66, 165, 245, 0.2);
  border-radius: 15px; 
  padding: 40px 35px;
  text-align: center;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  max-width: 600px;
  width: 100%;
  margin: 0 auto;
}

.testimonial-card::before {
  content: '"';
  position: absolute;
  top: 20px;
  left: 30px;
  font-size: 100px;
  color: rgba(66, 165, 245, 0.1); 
  font-family: serif;
  line-height: 1;
}

.testimonial-card:hover {
  transform: translateY(-5px);
  border-color: rgba(66, 165, 245, 0.5); 
  box-shadow: 0 15px 40px rgba(25, 118, 210, 0.3); 
}

/* Stars */
.testimonial-stars {
  display: flex;
  justify-content: center;
  gap: 5px;
  margin-bottom: 25px;
}

.star-icon {
  color: rgba(255, 255, 255, 0.3); 
  font-size: 1.4rem;
  transition: color 0.3s;
}

.star-icon.active {
  color: #ffc107;
}

/* Quote  */
.testimonial-quote {
  font-size: 1.1rem;
  line-height: 1.7;
  color: #fff; 
  margin-bottom: 30px;
  font-style: italic;
  position: relative;
  z-index: 1;
  font-weight: 400;
}

/* Author */
.testimonial-author {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
}

.author-img {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #1976d2; /* Border biru sama */
}

.author-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #fff; /* Text putih */
  margin: 0 0 5px 0;
  text-align: left;
}

.author-role {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7); /* Text abu-abu terang */
  margin: 0;
  text-align: left;
}

/* Carousel Navigation*/
.testimonial-nav {
  position: absolute;
  top: 33%;
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

.testimonial-nav:hover {
  background: rgba(25, 118, 210, 1); 
  transform: translateY(-50%) scale(1.1);
}

.testimonial-prev {
  left: 10px;
}

.testimonial-next {
  right: 10px;
}

.testimonial-nav svg {
  width: 24px;
  height: 24px;
}

/* Carousel Indicators - WARNA SAMA */
.testimonial-indicators {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-top: 40px;
  width: 100%;
}

.testimonial-indicator {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3); /* Background sama */
  border: none;
  cursor: pointer;
  transition: all 0.3s;
}

.testimonial-indicator.active {
  background: #1976d2; /* Biru aktif sama */
  transform: scale(1.2);
}

.testimonial-indicator:hover {
  background: rgba(25, 118, 210, 0.7); /* Hover biru sama */
}

/* Section Title Styling - WARNA SAMA */
.section-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #fff; /* Putih sama */
  margin-bottom: 1rem;
}

.section-subtitle {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.8); /* Abu-abu terang sama */
  margin-bottom: 0;
}

/* Responsive Design */
@media (max-width: 992px) {
  .testimonial-carousel-container {
    padding: 0 50px;
    max-width: 700px;
  }
  
  .testimonial-card {
    padding: 35px 30px;
    max-width: 550px;
  }
  
  .testimonial-quote {
    font-size: 1.05rem;
  }
}

@media (max-width: 768px) {
  .testimonial-section {
    padding: 80px 0;
  }
  
  .testimonial-carousel-container {
    padding: 0 40px;
    max-width: 600px;
  }
  
  .testimonial-card {
    padding: 30px 25px;
    max-width: 500px;
  }
  
  .testimonial-card::before {
    font-size: 80px;
    top: 15px;
    left: 20px;
  }
  
  .testimonial-nav {
    width: 45px;
    height: 45px;
  }
  
  .testimonial-prev {
    left: 5px;
  }
  
  .testimonial-next {
    right: 5px;
  }
  
  .testimonial-quote {
    font-size: 1rem;
    line-height: 1.6;
  }
}

@media (max-width: 576px) {
  .testimonial-section .container {
    padding: 0 10px;
  }
  
  .testimonial-carousel-container {
    padding: 0 30px;
    max-width: 100%;
  }
  
  .testimonial-card {
    padding: 25px 20px;
    max-width: 100%;
  }
  
  .testimonial-author {
    flex-direction: column;
    text-align: center;
    gap: 10px;
  }
  
  .author-img {
    width: 55px;
    height: 55px;
  }
  
  .author-name,
  .author-role {
    text-align: center;
  }
  
  .author-name {
    font-size: 1rem;
  }
  
  .testimonial-nav {
    width: 40px;
    height: 40px;
  }
  
  .testimonial-nav svg {
    width: 20px;
    height: 20px;
  }
  
  .testimonial-prev {
    left: 2px;
  }
  
  .testimonial-next {
    right: 2px;
  }
  
  .testimonial-indicators {
    margin-top: 35px;
  }
  
  .testimonial-quote {
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 25px;
  }
  
  .section-title {
    font-size: 2rem;
  }
  
  .section-subtitle {
    font-size: 1rem;
  }
}

/* Animation for slide transition */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.testimonial-slide {
  animation: fadeIn 0.5s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const carousel = document.querySelector('.testimonial-carousel');
  const slides = document.querySelectorAll('.testimonial-slide');
  const prevBtn = document.querySelector('.testimonial-prev');
  const nextBtn = document.querySelector('.testimonial-next');
  const indicators = document.querySelectorAll('.testimonial-indicator');
  
  let currentIndex = 0;
  let autoPlayInterval;
  
  function updateCarousel() {
    carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
    
    // Update indicators
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle('active', index === currentIndex);
    });
  }
  
  function nextSlide() {
    currentIndex = (currentIndex + 1) % slides.length;
    updateCarousel();
  }
  
  function prevSlide() {
    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
    updateCarousel();
  }
  
  function goToSlide(index) {
    currentIndex = index;
    updateCarousel();
  }
  
  function startAutoPlay() {
    autoPlayInterval = setInterval(nextSlide, 5000);
  }
  
  function stopAutoPlay() {
    clearInterval(autoPlayInterval);
  }
  
  // Event listeners
  prevBtn.addEventListener('click', () => {
    stopAutoPlay();
    prevSlide();
    startAutoPlay();
  });
  
  nextBtn.addEventListener('click', () => {
    stopAutoPlay();
    nextSlide();
    startAutoPlay();
  });
  
  indicators.forEach(indicator => {
    indicator.addEventListener('click', function() {
      stopAutoPlay();
      const index = parseInt(this.getAttribute('data-index'));
      goToSlide(index);
      startAutoPlay();
    });
  });
  
  // Keyboard navigation
  document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') {
      stopAutoPlay();
      prevSlide();
      startAutoPlay();
    } else if (e.key === 'ArrowRight') {
      stopAutoPlay();
      nextSlide();
      startAutoPlay();
    }
  });
  
  // Pause autoplay on hover
  const carouselContainer = document.querySelector('.testimonial-carousel-container');
  carouselContainer.addEventListener('mouseenter', stopAutoPlay);
  carouselContainer.addEventListener('mouseleave', startAutoPlay);
  
  // Initialize carousel
  updateCarousel();
  startAutoPlay();
});
</script>