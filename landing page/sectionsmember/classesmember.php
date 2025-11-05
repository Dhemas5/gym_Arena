<?php
// Include data classes dengan path yang benar
include 'config/data.php';
?>

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
      <button class="tab-btn active" onclick="filterClasses('all')">Semua Kelas</button>
      <button class="tab-btn" onclick="filterClasses('Mind & Body')">Mind & Body</button>
      <button class="tab-btn" onclick="filterClasses('Dance')">Dance</button>
      <button class="tab-btn" onclick="filterClasses('Strength')">Strength</button>
      <button class="tab-btn" onclick="filterClasses('HIIT')">HIIT</button>
    </div>

    <!-- Class Cards -->
    <div class="row g-4" id="classesContainer">

      <?php foreach ($classes as $class): ?>
        <div class="col-lg-4 col-md-6 class-card-item" data-category="<?php echo $class['category']; ?>">
          <div class="class-card">
            <div class="class-image">
              <img src="<?php echo $class['image']; ?>" alt="<?php echo $class['name']; ?>">
              <div class="class-overlay">
                <h3 class="class-name"><?php echo $class['name']; ?></h3>
                <span class="class-badge"><?php echo $class['category']; ?></span>
              </div>
            </div>
            <div class="class-body">
              <p class="class-desc"><?php echo $class['description']; ?></p>
              <div class="class-meta">
                <span class="class-duration">⏱️ <?php echo $class['duration']; ?></span>
                <span class="class-intensity">💪 <?php echo $class['intensity']; ?></span>
              </div>
              <p class="class-schedule">📅 <?php echo $class['schedule']; ?></p>
              <button class="btn-booking" onclick="openBooking('<?php echo $class['name']; ?>', <?php echo $class['id']; ?>)">Booking Kelas</button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

    </div>
  </div>
</section>

<script>
// Fungsi filter kelas
function filterClasses(category) {
  const classItems = document.querySelectorAll('.class-card-item');
  const tabButtons = document.querySelectorAll('.tab-btn');
  
  // Update active tab
  tabButtons.forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
  
  // Filter classes
  classItems.forEach(item => {
    if (category === 'all' || item.dataset.category === category) {
      item.style.display = 'block';
    } else {
      item.style.display = 'none';
    }
  });
}
</script>