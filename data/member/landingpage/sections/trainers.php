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
        $trainers = [
          [
            'image' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=400&h=500&fit=crop',
            'name' => 'Coach Fitri',
            'specialties' => ['Senam BL'],
            'schedule' => 'Sen, Jum',
            'desc' => '8 tahun pengalaman sebagai instruktur yoga bersertifikat internasional. Spesialisasi dalam Vinyasa dan Hatha Yoga.',
            'certifications' => ['RYT-500', 'Pilates certified']
          ],
          [
            'image' => 'https://images.unsplash.com/photo-1567598508481-65985588e295?w=400&h=500&fit=crop',
            'name' => 'Coach Mieke',
            'specialties' => ['Body Shape'],
            'schedule' => 'Rab, kam',
            'desc' => '10 tahun di bidang strength & conditioning. Mantan atlet angkat besi nasional.',
            'certifications' => ['NSCA-CPT', 'CrossFit Level 2']
          ],
          [
            'image' => 'https://images.unsplash.com/photo-1594381898411-846e7d193883?w=400&h=500&fit=crop',
            'name' => 'Coach Nana',
            'specialties' => ['Kapha Yoga', 'Trampoline'],
            'schedule' => 'Rab, Jum, Ming',
            'desc' => '6 tahun pengalaman mengajar Zumba dan dance fitness. Energik dan memotivasi!',
            'certifications' => ['Zumba B1 & B2', 'AFAA Certified']
          ],
          [
            'image' => 'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?w=400&h=500&fit=crop',
            'name' => 'Coach Wiwik',
            'specialties' => ['Aero BL'],
            'schedule' => 'Kam, Ming',
            'desc' => '7 tahun melatih CrossFit dan functional training. Fokus pada teknik yang tepat.',
            'certifications' => ['CrossFit L-2', 'ACE Personal Trainer']
          ],
          [
            'image' => 'https://images.unsplash.com/photo-1594381898411-846e7d193883?w=400&h=500&fit=crop',
            'name' => 'Coach Nana',
            'specialties' => ['Kapha Yoga', 'Trampoline'],
            'schedule' => 'Rab, Jum, Ming',
            'desc' => '6 tahun pengalaman mengajar Zumba dan dance fitness. Energik dan memotivasi!',
            'certifications' => ['Zumba B1 & B2', 'AFAA Certified']
          ],
          [
            'image' => 'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?w=400&h=500&fit=crop',
            'name' => 'Coach Wiwik',
            'specialties' => ['Aero BL'],
            'schedule' => 'Kam, Ming',
            'desc' => '7 tahun melatih CrossFit dan functional training. Fokus pada teknik yang tepat.',
            'certifications' => ['CrossFit L-2', 'ACE Personal Trainer']
          ],
          [
            'image' => 'https://images.unsplash.com/photo-1594381898411-846e7d193883?w=400&h=500&fit=crop',
            'name' => 'Coach Nana',
            'specialties' => ['Kapha Yoga', 'Trampoline'],
            'schedule' => 'Rab, Jum, Ming',
            'desc' => '6 tahun pengalaman mengajar Zumba dan dance fitness. Energik dan memotivasi!',
            'certifications' => ['Zumba B1 & B2', 'AFAA Certified']
          ],
          [
            'image' => 'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?w=400&h=500&fit=crop',
            'name' => 'Coach Wiwik',
            'specialties' => ['Aero BL'],
            'schedule' => 'Kam, Ming',
            'desc' => '7 tahun melatih CrossFit dan functional training. Fokus pada teknik yang tepat.',
            'certifications' => ['CrossFit L-2', 'ACE Personal Trainer']
          ]
        ];

        foreach ($trainers as $trainer) {
          echo '
          <div class="trainer-slide">
            <div class="trainer-card">
              <div class="trainer-image">
                <img src="' . $trainer['image'] . '" alt="' . $trainer['name'] . '">
              </div>
              <div class="trainer-info">
                <h3 class="trainer-name">' . $trainer['name'] . '</h3>
                <div class="trainer-badges">';
          
          foreach ($trainer['specialties'] as $specialty) {
            echo '<span class="badge-specialty">' . $specialty . '</span>';
          }
          
          echo '
                </div>
                <p class="trainer-title">Jadwal Mengajar</p>
                <p class="trainer-schedule">' . $trainer['schedule'] . '</p>
                <p class="trainer-desc">' . $trainer['desc'] . '</p>
                <div class="trainer-certs">
                  <p class="cert-title">Sertifikasi:</p>';
          
          foreach ($trainer['certifications'] as $cert) {
            echo '<span class="cert-badge">' . $cert . '</span>';
          }
          
          echo '
                </div>
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
  overflow: hidden; /* Kembalikan ke hidden */
  padding: 0 20px;
  margin-bottom: 50px; /* Beri margin bottom untuk indicators */
}

.trainers-carousel {
  display: flex;
  transition: transform 0.5s ease;
  gap: 30px;
}

.trainer-slide {
  flex: 0 0 calc(25% - 22.5px); /* 4 cards per view with gap */
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

.trainer-info {
  padding: 25px;
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
  margin-top: 15px;
}

.trainer-schedule {
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
}

.trainer-certs {
  padding-top: 15px;
  border-top: 1px solid rgba(66, 165, 245, 0.2);
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
  position: relative; /* Ubah dari absolute ke relative */
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
    flex: 0 0 calc(33.333% - 20px); /* 3 cards per view on medium screens */
  }
}

@media (max-width: 992px) {
  .trainer-slide {
    flex: 0 0 calc(50% - 15px); /* 2 cards per view on tablets */
  }
}

@media (max-width: 768px) {
  .trainers-carousel-container {
    padding: 0 40px;
  }
  
  .trainer-slide {
    flex: 0 0 100%; /* 1 card per view on mobile */
  }
  
  .carousel-nav {
    width: 40px;
    height: 40px;
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
    const slideWidth = slides[0].offsetWidth + 30; // width + gap
    carousel.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
    
    // Update indicators
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle('active', index === currentIndex);
    });
  }
  
  function nextSlide() {
    const maxIndex = slides.length - slidesPerView;
    currentIndex = currentIndex < maxIndex ? currentIndex + 1 : 0;
    updateCarousel();
  }
  
  function prevSlide() {
    const maxIndex = slides.length - slidesPerView;
    currentIndex = currentIndex > 0 ? currentIndex - 1 : maxIndex;
    updateCarousel();
  }
  
  // Event listeners
  prevBtn.addEventListener('click', prevSlide);
  nextBtn.addEventListener('click', nextSlide);
  
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
      // Reset to first slide on view change
      currentIndex = 0;
      updateCarousel();
    }
  });
  
  // Initialize carousel
  updateCarousel();
});
</script>