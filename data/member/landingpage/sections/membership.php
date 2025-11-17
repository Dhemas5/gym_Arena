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
          'price' => '60.000',
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
          'title' => '1 Bulan',
          'price' => '285.000',
          'period' => 'per bulan',
          'features' => [
            'Fasilitas gym lengkap',
            'Full acces gym dan studio kelas',
            'Full AC',
            'Kamar mandi dan ruang ganti',
            'Loker'
          ],
          'popular' => false
        ],
        [
          'title' => '3 Bulan',
          'price' => '675.000',
          'period' => 'per 3 bulan',
          'features' => [
            'Fasilitas gym lengkap',
            'Full acces gym dan studio kelas',
            'Full AC',
            'Kamar mandi dan ruang ganti',
            'Loker',
            'Free Payung'
          ],
          'popular' => true
        ],
        [
          'title' => '6 Bulan',
          'price' => '1.250.000',
          'period' => 'per 6 bulan',
          'features' => [
            'Fasilitas gym lengkap',
            'Full acces gym dan studio kelas',
            'Full AC',
            'Kamar mandi dan ruang ganti',
            'Loker',
            'Free Kaos'
          ],
          'popular' => false
        ],
        [
          'title' => '12 Bulan',
          'price' => '2.300.000',
          'period' => 'per tahun',
          'features' => [
            'Fasilitas gym lengkap',
            'Full acces gym dan studio kelas',
            'Full AC',
            'Kamar mandi dan ruang ganti',
            'Loker',
            'Free Tas Gym'
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