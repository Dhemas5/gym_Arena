<?php
// Data user dari indexmember.php
global $user_data;
?>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
  <div class="container">
    <a class="navbar-brand" href="#home">
      <span class="brand-box">AF</span>
      <div>
        <span class="brand-text">Arena FIT</span>
        <span class="brand-subtitle">Gym and Class</span>
      </div>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#classes">Kelas & Jadwal</a></li>
        <li class="nav-item"><a class="nav-link" href="#my-bookings">Booking Saya</a></li>
      </ul>
      <div class="user-info">
        <img src="<?php echo $user_data['avatar']; ?>" alt="User" class="user-avatar">
        <div>
          <p class="user-name"><?php echo $user_data['name']; ?></p>
          <p class="user-status"><?php echo $user_data['status']; ?></p>
        </div>
      </div>
    </div>
  </div>
</nav>