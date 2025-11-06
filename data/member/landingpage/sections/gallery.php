<!-- GALLERY SECTION -->
<section id="gallery" class="gallery-section">
  <div class="container">
    <!-- Header -->
    <div class="text-center mb-5">
      <h1 class="section-title">Galeri <span class="text-danger">Foto</span></h1>
      <p class="section-subtitle">Lihat suasana dan fasilitas Arena FIT</p>
    </div>

    <!-- Gallery Grid -->
    <div class="row g-3">
      
      <?php
      $gallery_items = [
        [
          'image' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=500&h=350&fit=crop',
          'title' => 'Modern Gym Facility'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=500&h=350&fit=crop',
          'title' => 'Group Fitness Classes'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1588286840104-8957b019727f?w=500&h=350&fit=crop',
          'title' => 'Yoga Sessions'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=500&h=350&fit=crop',
          'title' => 'CrossFit Training'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1623874228601-f4193c7b1818?w=500&h=350&fit=crop',
          'title' => 'Strength Equipment'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?w=500&h=350&fit=crop',
          'title' => 'Dance Fitness'
        ]
      ];

      foreach ($gallery_items as $item) {
        echo '
        <div class="col-lg-4 col-md-6">
          <div class="gallery-item">
            <img src="' . $item['image'] . '" alt="' . $item['title'] . '">
            <div class="gallery-overlay">
              <h5>' . $item['title'] . '</h5>s
            </div>
          </div>
        </div>';
      }
      ?>

    </div>
  </div>
</section>