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
          'image' => 'assets/images/gallery/gambar1.jpg',
          'title' => 'Modern Gym Facility'
        ],
        [
          'image' => 'assets/images/gallery/gambar2.jpg',
          'title' => 'Modern Gym Facility'
        ],
        [
          'image' => 'assets/images/gallery/gambar3.jpg',
          'title' => 'Modern Gym Facility'
        ],
        [
          'image' => 'assets/images/gallery/gambar4.jpg',
          'title' => 'Modern Gym Facility'
        ],
        [
          'image' => 'assets/images/gallery/gambar5.jpg',
          'title' => 'Modern Gym Facility'
        ],
        [
          'image' => 'assets/images/gallery/gambar6.jpg',
          'title' => 'Modern Gym Facility'
        ]
      ];

      foreach ($gallery_items as $item) {
        echo '
        <div class="col-lg-4 col-md-6">
          <div class="gallery-item">
            <img src="' . $item['image'] . '" alt="' . $item['title'] . '">
            <div class="gallery-overlay">
              <h5>' . $item['title'] . '</h5>
            </div>
          </div>
        </div>';
      }
      ?>

    </div>
  </div>
</section>