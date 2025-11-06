<?php
// Data user dari indexmember.php
global $user_data;
?>

<!-- WELCOME SECTION (Ganti Hero untuk Member) -->
<section id="home" class="member-welcome">
  <div class="container">
    <div class="welcome-card">
      <h1 class="welcome-title">Selamat Datang Kembali, <span class="text-danger"><?php echo explode(' ', $user_data['name'])[0]; ?>!</span></h1>
      <p class="welcome-subtitle">Siap untuk latihan hari ini? Booking kelas favoritmu sekarang!</p>
      
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number"><?php echo $user_data['stats']['classes_attended']; ?></div>
          <div class="stat-label">Kelas Diikuti</div>
        </div>
        <div class="stat-card">
          <div class="stat-number" id="activeBookings"><?php echo $user_data['stats']['active_bookings']; ?></div>
          <div class="stat-label">Booking Aktif</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?php echo $user_data['stats']['training_days']; ?></div>
          <div class="stat-label">Hari Berlatih</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?php echo $user_data['stats']['weight_loss']; ?></div>
          <div class="stat-label">Kg Turun</div>
        </div>
      </div>
    </div>
  </div>
</section>