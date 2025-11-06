<!-- Step 4: Pembayaran -->
<div id="step4" class="section-card hidden">
  <h2 class="section-title">Pembayaran</h2>
  
  <div class="payment-instructions">
    <h3>📋 Instruksi Pembayaran</h3>
    <ul>
      <li>Total pembayaran: <strong id="paymentTotal">Rp 0</strong></li>
      <li>Pilih salah satu metode pembayaran di bawah</li>
      <li>Setelah pembayaran, upload bukti transfer</li>
      <li>Klik "Konfirmasi Pembayaran" untuk menyelesaikan pendaftaran</li>
    </ul>
  </div>

  <div class="payment-options">
    <h3>💳 QRIS</h3>
    <p>Scan kode QR berikut menggunakan aplikasi banking atau e-wallet Anda:</p>
    <img src="https://via.placeholder.com/200x200?text=QRIS+Arena+FIT" alt="QRIS Arena FIT" style="width: 200px; height: 200px; border-radius: 10px; margin: 15px 0;">
    <p><strong>Jumlah: <span id="qrisAmount">Rp 0</span></strong></p>

    <h3 style="margin-top: 30px;">🏦 Transfer Bank</h3>
    <div style="background: rgba(25, 118, 210, 0.05); padding: 20px; border-radius: 10px; margin: 15px 0;">
      <p style="margin: 8px 0;"><strong>Bank:</strong> BCA</p>
      <p style="margin: 8px 0;"><strong>Nomor Rekening:</strong> 1234567890</p>
      <p style="margin: 8px 0;"><strong>Atas Nama:</strong> Arena FIT</p>
    </div>
  </div>

  <!-- Upload Bukti Pembayaran -->
  <div class="upload-section" id="uploadSection">
    <h3 style="color: #fff; margin-bottom: 15px;">📎 Upload Bukti Pembayaran</h3>
    <p style="color: rgba(255, 255, 255, 0.7); margin-bottom: 20px;">Upload screenshot atau foto bukti transfer Anda</p>
    
    <div class="file-input-wrapper">
      <label for="paymentProof" class="btn-upload">
        📁 Pilih File
      </label>
      <input type="file" id="paymentProof" accept="image/*" onchange="handleFileSelect(event)">
    </div>
    
    <div id="filePreview" class="file-preview" style="display: none;">
      ✅ <span id="fileName"></span>
    </div>
  </div>

  <div style="display: flex; gap: 15px; margin-top: 30px;">
    <button class="btn btn-primary" onclick="nextStep(3)" style="background: rgba(66, 165, 245, 0.2);">← Kembali</button>
    <button class="btn btn-primary" onclick="confirmPayment()" id="btnConfirmPayment" disabled>Konfirmasi Pembayaran</button>
  </div>
</div>