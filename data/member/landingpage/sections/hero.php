<!-- HERO SECTION -->
<section id="home" class="hero">
  <div class="hero-content">
    <h1>
      <span class="text-white">Stay</span> <span class="text-danger">Strong</span>
      <br>
      <span class="text-white">Stay</span> <span class="text-danger">Healthy</span>
    </h1>
    <p>with Arena FIT </p>
    <a href="login.php" class="btn btn-danger btn-lg">Join Now</a>
  </div>
</section>

<style>
/* ===================================
   HERO SECTION MODERN
   =================================== */
.hero {
  min-height: 100vh;
  background: linear-gradient(135deg, rgba(25, 118, 210, 0.15) 0%, rgba(10, 25, 41, 0.9) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  position: relative;
  padding-top: 80px;
  overflow: hidden;
}

/* Background image responsive */
.hero::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data/member/landingpage/assets/images/gallery/bg.jpg') center/cover no-repeat;
  z-index: -1;
}

/* Efek shadow hitam redup */
.hero::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.7) 100%);
  z-index: -1;
}

.hero-content {
  position: relative;
  z-index: 2;
  animation: fadeInUp 1.2s ease-out forwards;
  max-width: 800px;
  padding: 0 20px;
}

.hero h1 {
  font-size: 4.5rem;
  font-weight: 700;
  line-height: 1.2;
  margin-bottom: 1rem;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
}

.hero .text-white {
  color: #fff !important;
}

.hero .text-danger {
  color: #42a5f5 !important;
}

.hero p {
  font-size: 1.3rem;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 2rem;
  text-shadow: 0 1px 5px rgba(0, 0, 0, 0.5);
}

.hero .btn-lg {
  padding: 15px 40px;
  font-size: 1.1rem;
  border-radius: 50px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

/* ===================================
   ANIMATIONS
   =================================== */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ===================================
   RESPONSIVE BACKGROUND IMAGE
   =================================== */
@media (max-width: 1200px) {
  .hero::before {
    background-size: cover;
    background-position: center;
  }
}

@media (max-width: 992px) {
  .hero::before {
    background-size: cover;
    background-position: center;
  }
  
  .hero h1 {
    font-size: 3.5rem;
  }
}

@media (max-width: 768px) {
  .hero::before {
    background-size: cover;
    background-position: center;
  }
  
  .hero h1 {
    font-size: 2.8rem;
  }

  .hero p {
    font-size: 1.1rem;
  }
  
  .hero .btn-lg {
    padding: 12px 35px;
    font-size: 1rem;
  }
}

@media (max-width: 576px) {
  .hero::before {
    background-size: cover;
    background-position: center;
  }
  
  .hero h1 {
    font-size: 2.2rem;
  }

  .hero p {
    font-size: 1rem;
  }

  .hero .btn-lg {
    padding: 12px 30px;
    font-size: 0.95rem;
  }
}

/* Untuk layar sangat kecil (mobile portrait) */
@media (max-width: 400px) {
  .hero::before {
    background-size: cover;
    background-position: center;
  }
  
  .hero h1 {
    font-size: 1.8rem;
  }
  
  .hero-content {
    padding: 0 15px;
  }
}

/* Untuk layar sangat lebar (desktop besar) */
@media (min-width: 1600px) {
  .hero::before {
    background-size: cover;
    background-position: center;
  }
}

/* Optimasi untuk landscape mobile */
@media (max-height: 500px) and (orientation: landscape) {
  .hero {
    min-height: 120vh;
    padding-top: 60px;
  }
  
  .hero::before {
    background-size: cover;
    background-position: center;
  }
  
  .hero h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
  }
  
  .hero p {
    font-size: 1rem;
    margin-bottom: 1rem;
  }
}
</style>