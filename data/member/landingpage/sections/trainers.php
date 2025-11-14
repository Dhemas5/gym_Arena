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
          'image' => 'http://localhost/gym_Arena/img/alfian.jpg',
          'name' => 'Coach Alfian',
          'specialties' => ['GYM'],
          'schedule' => '-',
          'desc' => '8 tahun pengalaman sebagai instruktur yoga bersertifikat internasional. Spesialisasi dalam Vinyasa dan Hatha Yoga.',
          'certifications' => ['RYT-500', 'Pilates certified']
        ],
        [
          'image' => 'http://localhost/gym_Arena/img/ameliya.jpg',
          'name' => 'Coach Ameylia',
          'specialties' => ['GYM'],
          'schedule' => '-',
          'desc' => '10 tahun di bidang strength & conditioning. Mantan atlet angkat besi nasional.',
          'certifications' => ['NSCA-CPT', 'CrossFit Level 2']
        ],
        [
          'image' => 'http://localhost/gym_Arena/img/niken.jpg',
          'name' => 'Coach Niken',
          'specialties' => ['GYM'],
          'schedule' => '-',
          'desc' => '6 tahun pengalaman mengajar Zumba dan dance fitness. Energik dan memotivasi!',
          'certifications' => ['Zumba B1 & B2', 'AFAA Certified']
        ],
        [
          'image' => 'http://localhost/gym_Arena/img/ade.jpg',
          'name' => 'Coach Ade',
          'specialties' => ['GYM'],
          'schedule' => '-',
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