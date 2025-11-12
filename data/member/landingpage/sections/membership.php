<!-- MEMBERSHIP SECTION -->
<section id="membership" class="membership-section">
  <div class="container">
    <!-- Header -->
    <div class="text-center mb-5">
      <h1 class="section-title">Paket <span class="text-danger">Membership</span></h1>
      <p class="section-subtitle">Pilih paket yang sesuai dengan kebutuhan Anda</p>
    </div>

    <!-- Pricing Cards -->
    <div class="row g-4 justify-content-center">

      <?php
      $membership_plans = [
        [
          'title' => 'Harian',
          'price' => '50.000',
          'period' => 'per hari',
          'features' => [
            'Akses gym 1 hari',
            'Akses semua peralatan',
            'Loker gratis',
            'Handuk gratis'
          ],
          'popular' => false
        ],
        [
          'title' => 'Bulanan',
          'price' => '500.000',
          'period' => 'per bulan',
          'features' => [
            'Akses gym unlimited',
            'Akses semua peralatan',
            '3 sesi personal trainer',
            'Loker pribadi',
            'Handuk gratis',
            'Free consultation'
          ],
          'popular' => true
        ],
        [
          'title' => 'Tahunan',
          'price' => '4.500.000',
          'period' => 'per tahun',
          'features' => [
            'Akses gym unlimited',
            'Akses semua peralatan',
            'Kelas unlimited',
            'Loker pribadi',
            'Handuk gratis',
            '2 sesi personal trainer',
            'Program nutrisi',
            'Diskon merchandise 20%'
          ],
          'popular' => false
        ]
      ];

      foreach ($membership_plans as $plan) {
        $popular_class = $plan['popular'] ? 'popular' : '';
        echo '
        <div class="col-lg-4 col-md-6">
          <div class="pricing-card ' . $popular_class . '">';
        
        if ($plan['popular']) {
          echo '<span class="popular-badge">Paling Populer</span>';
        }
        
        echo '
            <h3 class="pricing-title">' . $plan['title'] . '</h3>
            <div class="pricing-price">
              <span class="price-currency">Rp</span>
              <span class="price-amount">' . $plan['price'] . '</span>
              <span class="price-period">' . $plan['period'] . '</span>
            </div>

            <ul class="pricing-features">';
        
        foreach ($plan['features'] as $feature) {
          echo '<li><span class="check-icon">✓</span> ' . $feature . '</li>';
        }
        
        echo '
            </ul>
            <a href="../login/register.php" class="btn btn-danger">Daftar Sekarang</a>
          </div>
        </div>';
      }
      ?>

    </div>

    <!-- Footer Note -->
    <div class="text-center mt-5">
      <p class="pricing-note">Semua paket termasuk akses ke fasilitas shower, WiFi gratis, dan parkir</p>
    </div>
  </div>
</section>