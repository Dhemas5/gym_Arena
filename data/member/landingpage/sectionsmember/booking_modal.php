<!-- BOOKING MODAL -->
<div class="booking-modal" id="bookingModal">
  <div class="modal-content">
    <button class="modal-close" onclick="closeBookingModal()">×</button>
    <h2 class="modal-title" id="modalClassName">Booking Kelas</h2>
    
    <div class="form-group">
      <label class="form-label">Pilih Instruktur</label>
      <div class="trainer-grid" id="trainerGrid"></div>
    </div>

    <div class="form-group">
      <label class="form-label">Pilih Jadwal</label>
      <div class="schedule-grid" id="scheduleGrid"></div>
    </div>

    <div class="form-group">
      <label class="form-label">Catatan Tambahan (Opsional)</label>
      <textarea class="form-input" id="bookingNotes" rows="3" placeholder="Tambahkan catatan untuk instruktur..."></textarea>
    </div>

    <button class="btn-confirm" id="confirmBooking" disabled>Konfirmasi Booking</button>
    <div class="success-message" id="successMessage">
      ✓ Booking berhasil! Kelas Anda telah terdaftar.
    </div>
  </div>
</div>