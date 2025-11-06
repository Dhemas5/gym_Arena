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
      $classes = [
        [
          'image' => 'https://images.unsplash.com/photo-1588286840104-8957b019727f?w=500&h=300&fit=crop',
          'name' => 'Senam BL',
          'desc' => 'Meningkatkan fleksibilitas dan ketenangan pikiran',
          'schedule' => 'Semua Level'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=500&h=300&fit=crop',
          'name' => 'Zumba Zin Ira',
          'desc' => 'Membakar kalori dan meningkatkan energi dengan tarian ritmis',
          'schedule' => 'Pemula - Mahir'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?w=500&h=300&fit=crop',
          'name' => 'Boxing',
          'desc' => 'Meningkatkan kekuatan, ketahanan, dan refleks tubuh',
          'schedule' => 'Menengah - Lanjut'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=500&h=300&fit=crop',
          'name' => 'Body Shape',
          'desc' => 'Membentuk dan mengencangkan otot tubuh secara proporsional',
          'schedule' => 'Semua Level'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=500&h=300&fit=crop',
          'name' => 'Kapha Yoga',
          'desc' => 'Menyeimbangkan tubuh dan pikiran melalui gerakan serta pernapasan',
          'schedule' => 'Pemula - Menengah'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=500&h=300&fit=crop',
          'name' => 'Aero BL',
          'desc' => 'Melatih daya tahan dan koordinasi dengan gerakan dinamis',
          'schedule' => 'Lanjut'
        ]
      ];

      foreach ($classes as $class) {
        echo '
        <div class="col-lg-4 col-md-6">
          <div class="class-card">
            <div class="class-image">
              <img src="' . $class['image'] . '" alt="' . $class['name'] . '">
              <div class="class-overlay">
                <h3 class="class-name">' . $class['name'] . '</h3>
              </div>
            </div>
            <div class="class-body">
              <p class="class-desc">' . $class['desc'] . '</p>
              <p class="class-schedule">' . $class['schedule'] . '</p>
              <button class="btn-booking">Booking Kelas</button>
            </div>
          </div>
        </div>';
      }
      ?>

    </div>
  </div>
</section>