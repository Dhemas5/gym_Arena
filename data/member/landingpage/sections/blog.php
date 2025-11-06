<!-- BLOG SECTION -->
<section id="blog" class="blog-section">
  <div class="container">
    <!-- Header -->
    <div class="text-center mb-5">
      <h1 class="section-title">Blog & <span class="text-danger">Artikel</span></h1>
      <p class="section-subtitle">Tips, trik, dan informasi seputar fitness dan kesehatan</p>
    </div>

    <!-- Blog Grid -->
    <div class="row g-4">

      <?php
      $blog_posts = [
        [
          'image' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=400&h=250&fit=crop',
          'date' => '20 Oktober 2025',
          'duration' => '5 min',
          'title' => 'Tips Diet Sehat untuk Pemula',
          'excerpt' => 'Mulai perjalanan diet Anda dengan tips praktis yang mudah diterapkan dalam kehidupan sehari-hari'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=400&h=250&fit=crop',
          'date' => '18 Oktober 2025',
          'duration' => '7 min',
          'title' => 'Pola Latihan Efektif untuk Pembentukan Otot',
          'excerpt' => 'Panduan lengkap tentang program latihan yang efektif untuk meningkatkan massa otot dengan cepat dan aman'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=250&fit=crop',
          'date' => '15 Oktober 2025',
          'duration' => '4 min',
          'title' => 'Motivasi Kebugaran: Tetap Konsisten',
          'excerpt' => 'Strategi mental dan tips praktis untuk menjaga motivasi kebugaran Anda tetap tinggi setiap hari'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=400&h=250&fit=crop',
          'date' => '12 Oktober 2025',
          'duration' => '6 min',
          'title' => 'Nutrisi untuk Otot: Protein dan Suplemen',
          'excerpt' => 'Pelajari tentang kebutuhan protein harian dan jenis suplemen terbaik untuk mendukung pertumbuhan otot'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1588286840104-8957b019727f?w=400&h=250&fit=crop',
          'date' => '10 Oktober 2025',
          'duration' => '5 min',
          'title' => 'Yoga untuk Pemulihan dan Fleksibilitas',
          'excerpt' => 'Manfaat yoga untuk pemulihan otot setelah latihan dan meningkatkan fleksibilitas tubuh'
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1549476464-37392f717541?w=400&h=250&fit=crop',
          'date' => '8 Oktober 2025',
          'duration' => '8 min',
          'title' => 'CrossFit: Panduan untuk Pemula',
          'excerpt' => 'Memulai CrossFit dengan panduan lengkap untuk pemula, dari teknik dasar hingga tips keselamatan'
        ]
      ];

      foreach ($blog_posts as $post) {
        echo '
        <div class="col-lg-4 col-md-6">
          <div class="blog-card">
            <div class="blog-image">
              <img src="' . $post['image'] . '" alt="' . $post['title'] . '">
            </div>
            <div class="blog-content">
              <div class="blog-meta">
                <span class="blog-date">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                  </svg>
                  ' . $post['date'] . '
                </span>
                <span class="blog-duration">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                  </svg>
                  ' . $post['duration'] . '
                </span>
              </div>
              <h3 class="blog-title">' . $post['title'] . '</h3>
              <p class="blog-excerpt">' . $post['excerpt'] . '</p>
              <a href="#" class="blog-link">Baca Selengkapnya →</a>
            </div>
          </div>
        </div>';
      }
      ?>

    </div>
  </div>
</section>