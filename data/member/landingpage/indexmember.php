<?php
// Include konfigurasi data
include 'config/data.php';

// Data user (dapat dari session/database)
$user_data = [
    'name' => 'Andi Setiawan',
    'status' => 'Member Premium',
    'avatar' => 'https://i.pravatar.cc/100?img=33',
    'stats' => [
        'classes_attended' => 12,
        'active_bookings' => 0,
        'training_days' => 45,
        'weight_loss' => 8.5
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Arena FIT - Dashboard Member</title>

  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

  <!-- Custom CSS dari file awal -->
  <link rel="stylesheet" href="assets/css/style.css">
  
  <!-- Member Dashboard CSS - File Terpisah -->
  <link rel="stylesheet" href="assets/css/stylemember.css">
</head>
<body>

  <?php include 'headermember.php'; ?>

  <main>
    <?php
    // Include semua section dari folder sectionsmember
    include 'sectionsmember/welcome.php';
    include 'sectionsmember/classesmember.php';
    include 'sectionsmember/bookings.php';
    ?>
  </main>

  <?php include 'sectionsmember/booking_modal.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Member Dashboard JavaScript -->
  <script>
    // Data Instruktur
    const trainers = <?php echo json_encode($trainers); ?>;
    
    // Data Jadwal
    const schedules = <?php echo json_encode($schedules); ?>;

    let myBookings = [];
    let currentClass = { name: '', id: null };
    let selectedTrainer = null;
    let selectedSchedule = null;

    // Fungsi membuka modal booking
    function openBooking(className, classId) {
      currentClass = { name: className, id: classId };
      document.getElementById('modalClassName').textContent = `Booking ${className}`;
      document.getElementById('bookingModal').classList.add('active');
      
      // Load trainers
      const trainerGrid = document.getElementById('trainerGrid');
      trainerGrid.innerHTML = trainers.map(t => `
        <div class="trainer-option" data-trainer-id="${t.id}">
          <img src="${t.image}" alt="${t.name}" class="trainer-avatar-modal">
          <div class="trainer-name-modal">${t.name}</div>
          <div class="trainer-specialty-modal">${t.specialty}</div>
        </div>
      `).join('');
      
      // Load schedules
      const scheduleGrid = document.getElementById('scheduleGrid');
      scheduleGrid.innerHTML = schedules.map(s => `
        <div class="schedule-option" data-schedule-id="${s.id}">
          <div class="schedule-day">${s.day}</div>
          <div class="schedule-time">${s.time}</div>
        </div>
      `).join('');
      
      // Event listeners untuk trainer selection
      document.querySelectorAll('.trainer-option').forEach(el => {
        el.addEventListener('click', function() {
          document.querySelectorAll('.trainer-option').forEach(e => e.classList.remove('selected'));
          this.classList.add('selected');
          selectedTrainer = trainers.find(t => t.id == this.dataset.trainerId);
          updateConfirmButton();
        });
      });
      
      // Event listeners untuk schedule selection
      document.querySelectorAll('.schedule-option').forEach(el => {
        el.addEventListener('click', function() {
          document.querySelectorAll('.schedule-option').forEach(e => e.classList.remove('selected'));
          this.classList.add('selected');
          selectedSchedule = schedules.find(s => s.id == this.dataset.scheduleId);
          updateConfirmButton();
        });
      });
      
      selectedTrainer = null;
      selectedSchedule = null;
      document.getElementById('bookingNotes').value = '';
      document.getElementById('successMessage').classList.remove('show');
      updateConfirmButton();
    }

    // Fungsi menutup modal
    function closeBookingModal() {
      document.getElementById('bookingModal').classList.remove('active');
    }

    // Update status tombol confirm
    function updateConfirmButton() {
      const btn = document.getElementById('confirmBooking');
      btn.disabled = !(selectedTrainer && selectedSchedule);
    }

    // Konfirmasi booking
    document.getElementById('confirmBooking').addEventListener('click', function() {
      const notes = document.getElementById('bookingNotes').value;
      const booking = {
        id: Date.now(),
        class: currentClass,
        trainer: selectedTrainer,
        schedule: selectedSchedule,
        notes: notes,
        date: new Date().toLocaleDateString('id-ID')
      };
      
      myBookings.push(booking);
      loadMyBookings();
      updateActiveBookings();
      
      // Tampilkan pesan sukses
      document.getElementById('successMessage').classList.add('show');
      this.disabled = true;
      
      setTimeout(() => {
        closeBookingModal();
      }, 2000);
    });

    // Load daftar booking
    function loadMyBookings() {
      const list = document.getElementById('bookingList');
      
      if (myBookings.length === 0) {
        list.innerHTML = `
          <div class="empty-state">
            <div class="empty-icon">📅</div>
            <h3>Belum Ada Booking</h3>
            <p>Mulai booking kelas favorit Anda sekarang!</p>
          </div>
        `;
        return;
      }
      
      list.innerHTML = myBookings.map(booking => `
        <div class="booking-item">
          <div class="booking-icon">🏋️</div>
          <div class="booking-details">
            <h4>${booking.class.name}</h4>
            <p>👨‍🏫 Instruktur: ${booking.trainer.name}</p>
            <p>📅 ${booking.schedule.day}, ${booking.schedule.time}</p>
            <p>📝 Terdaftar: ${booking.date}</p>
            ${booking.notes ? `<p>💬 ${booking.notes}</p>` : ''}
          </div>
          <button class="btn-cancel" onclick="cancelBooking(${booking.id})">Batal</button>
        </div>
      `).join('');
    }

    // Batalkan booking
    function cancelBooking(bookingId) {
      if (confirm('Apakah Anda yakin ingin membatalkan booking ini?')) {
        myBookings = myBookings.filter(b => b.id !== bookingId);
        loadMyBookings();
        updateActiveBookings();
      }
    }

    // Update jumlah booking aktif
    function updateActiveBookings() {
      document.getElementById('activeBookings').textContent = myBookings.length;
    }

    // Initialize
    loadMyBookings();
    updateActiveBookings();
  </script>
</body>
</html>