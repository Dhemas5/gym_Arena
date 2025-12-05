<!-- CONTACT SECTION -->
<section id="contact" class="contact-section">
  <div class="container">
    <!-- Header -->
    <div class="text-center mb-5">
      <h1 class="section-title">Hubungi <span class="text-danger">Kami</span></h1>
      <p class="section-subtitle">Kami siap membantu Anda memulai perjalanan fitness</p>
    </div>

    <!-- Contact Info Grid -->
    <div class="row g-4 justify-content-center">

      <?php
      $contact_info = [
      [
          'icon' => 'location',
          'title' => 'Alamat',
          'content' => 'Jl. KH Shiddiq No.19-21 TalangSari, Jember',
          'type' => 'location',
          'link' => 'https://www.google.com/maps/place/arena+fit+club+jember/data=!4m2!3m1!1s0x2dd6956c17efe3cd:0x238723fc52e5054e?sa=X&ved=1t:242&ictx=111'
        ],
        [
          'icon' => 'phone',
          'title' => 'Telepon',
          'content' => '+62 821-4308-0510',
          'type' => 'phone',
          'link' => 'wa.me/message/7TCOAF5OPHLYO1'
        ],
        [
          'icon' => 'email',
          'title' => 'Email',
          'content' => 'arenafitclubjbr22@gmail.com',
          'type' => 'email',
          'link' => 'arenafitclubjbr22@gmail.com'
        ],
        [
          'icon' => 'time',
          'title' => 'Jam Buka',
          'content' => 'Senin - Sabtu<br>07:00 - 22:00<br>Minggu<br>07:00 - 18:00',
          'type' => 'text',
          'link' => ''
        ]
      ];

      foreach ($contact_info as $info) {
        echo '
        <div class="col-lg-3 col-md-6">
          <div class="contact-card">';
        
        // Buat seluruh card bisa diklik untuk tipe tertentu
        if ($info['type'] === 'location' || $info['type'] === 'phone' || $info['type'] === 'email') {
          echo '<a href="' . $info['link'] . '" class="contact-card-link" target="_blank" style="text-decoration: none; color: inherit; display: block;">';
        }
        
        echo '
            <div class="contact-icon">
              ' . get_contact_icon($info['icon']) . '
            </div>
            <h4 class="contact-title">' . $info['title'] . '</h4>';
        
        if ($info['type'] === 'phone') {
          echo '<p class="contact-text contact-link">' . $info['content'] . '</p>';
        } elseif ($info['type'] === 'email') {
          echo '<p class="contact-text contact-link">' . $info['content'] . '</p>';
        } elseif ($info['type'] === 'location') {
          echo '<p class="contact-text contact-link">' . $info['content'] . '</p>';
        } else {
          echo '<p class="contact-text">' . $info['content'] . '</p>';
        }
        
        // Tutup tag link jika card bisa diklik
        if ($info['type'] === 'location' || $info['type'] === 'phone' || $info['type'] === 'email') {
          echo '</a>';
        }
        
        echo '
          </div>
        </div>';
      }

      function get_contact_icon($type) {
        $icons = [
          'location' => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>',
          'phone' => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
          </svg>',
          'email' => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>',
          'time' => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>'
        ];
        
        return $icons[$type] ?? '';
      }
      ?>

    </div>

    <!-- Instagram CTA -->
    <div class="instagram-cta">
      <h3 class="instagram-title">Ikuti Kami di Instagram</h3>
      <p class="instagram-subtitle">Lihat update terbaru, tips fitness, dan cerita inspiratif dari member kami</p>
      <a href="https://www.instagram.com/arenafitclubjember?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank" class="btn-instagram">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
        </svg>
        @arenafitclubjember
      </a>
    </div>
<!-- Map -->
<div class="contact-map">
  <iframe 
    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3951.456239372435!2d113.6907272!3d-8.1781471!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd6956c17efe3cd%3A0x238723fc52e5054e!2sARENA%20FIT%20CLUB!5e0!3m2!1sen!2sid!4v1733320512203!5m2!1sen!2sid"
    width="100%" 
    height="400" 
    style="border:0; border-radius: 15px;" 
    allowfullscreen="" 
    loading="lazy"
    referrerpolicy="no-referrer-when-downgrade"
    title="Peta Lokasi Arena FIT Club Jember">
  </iframe>
</div>