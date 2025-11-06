<!-- Step 3: Data Diri & Ringkasan -->
<div id="step3" class="section-card hidden">
  <h2 class="section-title">Data Diri</h2>
  
  <form id="registrationForm">
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label class="form-label">Nama Lengkap *</label>
          <input type="text" class="form-control" id="fullname" required placeholder="Masukkan nama lengkap">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" id="email" placeholder="contoh@email.com">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label class="form-label">No. Telepon</label>
          <input type="tel" class="form-control" id="phone" placeholder="08xxxxxxxxxx">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label class="form-label">Tanggal Lahir</label>
          <input type="date" class="form-control" id="birthdate">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Alamat</label>
      <textarea class="form-control" id="address" rows="3" placeholder="Masukkan alamat lengkap"></textarea>
    </div>

    <h2 class="section-title" style="margin-top: 30px;">Ringkasan Pembayaran</h2>
    <div class="summary-card">
      <div class="summary-row">
        <span class="summary-label">Paket Membership:</span>
        <span class="summary-value" id="summaryMembership">-</span>
      </div>
      <div class="summary-row" id="summaryClassRow" style="display: none;">
        <span class="summary-label">Kelas Tambahan:</span>
        <span class="summary-value" id="summaryClasses">-</span>
      </div>
      <div class="summary-row">
        <span class="summary-label">Total Pembayaran:</span>
        <span class="summary-value total-value" id="summaryTotal">Rp 0</span>
      </div>
    </div>

    <div style="display: flex; gap: 15px;">
      <button type="button" class="btn btn-primary" onclick="nextStep(2)" style="background: rgba(66, 165, 245, 0.2);">← Kembali</button>
      <button type="submit" class="btn btn-primary">Lanjut ke Pembayaran</button>
    </div>
  </form>
</div>