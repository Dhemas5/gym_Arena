<!-- TRAINERS SECTION -->
<section id="trainers" class="trainers-section">
  <div class="container">
    <!-- Header -->
    <div class="text-center mb-5">
      <h1 class="section-title">Instruktur <span class="text-danger">Profesional</span></h1>
      <p class="section-subtitle">Tim instruktur bersertifikat dan berpengalaman</p>
    </div>

    <!-- Trainers Grid -->
    <div class="row g-4">

      <?php
      $trainers = [
        [
          'image' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=400&h=500&fit=crop',
          'name' => 'Sarah Johnson',
          'specialties' => ['Yoga', 'Pilates'],
          'schedule' => 'Sen, Rab, Jum',
          'desc' => '8 tahun pengalaman sebagai instruktur yoga bersertifikat internasional. Spesialisasi dalam Vinyasa dan Hatha Yoga.',
          'certifications' => ['RYT-500', 'Pilates certified']
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1567598508481-65985588e295?w=400&h=500&fit=crop',
          'name' => 'Mike Anderson',
          'specialties' => ['Body Combat'],
          'schedule' => 'Sen, Rab, Sab',
          'desc' => '10 tahun di bidang strength & conditioning. Mantan atlet angkat besi nasional.',
          'certifications' => ['NSCA-CPT', 'CrossFit Level 2']
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1594381898411-846e7d193883?w=400&h=500&fit=crop',
          'name' => 'Lisa Martinez',
          'specialties' => ['Zumba', 'Dance Fitness'],
          'schedule' => 'Sel, Kam, Jum',
          'desc' => '6 tahun pengalaman mengajar Zumba dan dance fitness. Energik dan memotivasi!',
          'certifications' => ['Zumba B1 & B2', 'AFAA Certified']
        ],
        [
          'image' => 'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?w=400&h=500&fit=crop',
          'name' => 'David Chen',
          'specialties' => ['CrossFit', 'HIIT'],
          'schedule' => 'Sel, Kam, Sab',
          'desc' => '7 tahun melatih CrossFit dan functional training. Fokus pada teknik yang tepat.',
          'certifications' => ['CrossFit L-2', 'ACE Personal Trainer']
        ]
      ];

      foreach ($trainers as $trainer) {
        echo '
        <div class="col-lg-3 col-md-6">
          <div class="trainer-card">
            <div class="trainer-image">
              <img src="' . $trainer['image'] . '" alt="' . $trainer['name'] . '">
            </div>
            <div class="trainer-info">
              <h3 class="trainer-name">' . $trainer['name'] . '</h3>
              <div class="trainer-badges">';
        
        foreach ($trainer['specialties'] as $specialty) {
          echo '<span class="badge-specialty">' . $specialty . '</span>';
        }
        
        echo '
              </div>
              <p class="trainer-title">Jadwal Mengajar</p>
              <p class="trainer-schedule">' . $trainer['schedule'] . '</p>
              <p class="trainer-desc">' . $trainer['desc'] . '</p>
              <div class="trainer-certs">
                <p class="cert-title">Sertifikasi:</p>';
        
        foreach ($trainer['certifications'] as $cert) {
          echo '<span class="cert-badge">' . $cert . '</span>';
        }
        
        echo '
              </div>
            </div>
          </div>
        </div>';
      }
      ?>

    </div>
  </div>
</section>