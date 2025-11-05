<?php
// Data untuk membership options
$membership_options = [
    ['value' => 'bulanan-umum', 'name' => 'Bulanan Umum', 'price' => 285000, 'period' => 'per bulan'],
    ['value' => 'bulanan-pelajar', 'name' => 'Bulanan Pelajar', 'price' => 200000, 'period' => 'per bulan'],
    ['value' => '3bulan-umum', 'name' => '3 Bulan Umum', 'price' => 675000, 'period' => '3 bulan'],
    ['value' => '3bulan-pelajar', 'name' => '3 Bulan Pelajar', 'price' => 550000, 'period' => '3 bulan'],
    ['value' => '6bulan-umum', 'name' => '6 Bulan Umum', 'price' => 1250000, 'period' => '6 bulan'],
    ['value' => '6bulan-pelajar', 'name' => '6 Bulan Pelajar', 'price' => 1000000, 'period' => '6 bulan'],
    ['value' => 'tahunan-umum', 'name' => '1 Tahun Umum', 'price' => 2300000, 'period' => 'per tahun'],
    ['value' => 'tahunan-pelajar', 'name' => '1 Tahun Pelajar', 'price' => 1850000, 'period' => 'per tahun']
];

// Data untuk class options
$class_options = [
    ['value' => 'zumba', 'name' => 'Zumba / Aero BL / Strong Nation', 'price' => 20000],
    ['value' => 'body-shape', 'name' => 'CID / Body Shape / Senam BL', 'price' => 25000],
    ['value' => 'boxing', 'name' => 'Boxing / Kapha Yoga', 'price' => 30000],
    ['value' => 'boxing-bulanan', 'name' => 'Boxing (Paket 1 Bulan)', 'price' => 300000],
    ['value' => 'trainer', 'name' => 'Program Trainer (10x Pertemuan + Gym 1 Bulan + Boxing 4x)', 'price' => 1500000]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pendaftaran - Arena FIT</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  
  <!-- Link ke CSS terpisah -->
  <link rel="stylesheet" href="assets/css/registration.css">
  <style>
    .membership-option.selected, .class-option.selected {
      background-color: rgba(66, 165, 245, 0.4);
      border: 2px solid #42a5f5;
    }

    /* Modal Konfirmasi Pembayaran */
    .payment-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.95);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    }

    .payment-modal.active {
      display: flex;
    }

    .payment-modal-content {
      background: #0d1b2a;
      border-radius: 20px;
      padding: 40px;
      max-width: 500px;
      width: 90%;
      border: 1px solid rgba(66, 165, 245, 0.3);
      text-align: center;
      animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .payment-modal-icon {
      width: 80px;
      height: 80px;
      background: rgba(34, 197, 94, 0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 3rem;
    }

    .payment-modal-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: #fff;
      margin-bottom: 15px;
    }

    .payment-modal-text {
      color: rgba(255, 255, 255, 0.7);
      font-size: 1rem;
      line-height: 1.6;
      margin-bottom: 25px;
    }

    .payment-modal-redirect {
      color: #22c55e;
      font-weight: 600;
      margin-top: 15px;
    }

    .btn-confirm-payment {
      background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
      border: none;
      padding: 12px 30px;
      border-radius: 10px;
      color: #fff;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-confirm-payment:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(25, 118, 210, 0.5);
    }

    .upload-section {
      background: rgba(25, 118, 210, 0.05);
      border: 2px dashed rgba(66, 165, 245, 0.3);
      border-radius: 15px;
      padding: 30px;
      margin: 20px 0;
      text-align: center;
    }

    .upload-section.dragover {
      border-color: #42a5f5;
      background: rgba(25, 118, 210, 0.1);
    }

    .file-input-wrapper {
      position: relative;
      overflow: hidden;
      display: inline-block;
    }

    .file-input-wrapper input[type=file] {
      position: absolute;
      left: -9999px;
    }

    .btn-upload {
      background: rgba(25, 118, 210, 0.2);
      border: 1px solid rgba(66, 165, 245, 0.3);
      padding: 10px 25px;
      border-radius: 8px;
      color: #42a5f5;
      cursor: pointer;
      transition: all 0.3s;
      display: inline-block;
    }

    .btn-upload:hover {
      background: rgba(25, 118, 210, 0.3);
      border-color: #42a5f5;
    }

    .file-preview {
      margin-top: 15px;
      color: #22c55e;
      font-weight: 600;
    }

    .payment-instructions {
      background: rgba(66, 165, 245, 0.1);
      border-left: 4px solid #42a5f5;
      padding: 20px;
      margin: 20px 0;
      border-radius: 8px;
    }

    .payment-instructions h3 {
      color: #42a5f5;
      font-size: 1.2rem;
      margin-bottom: 15px;
    }

    .payment-instructions ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .payment-instructions li {
      color: rgba(255, 255, 255, 0.8);
      padding: 8px 0;
      display: flex;
      align-items: start;
    }

    .payment-instructions li:before {
      content: "✓";
      color: #22c55e;
      font-weight: bold;
      margin-right: 10px;
    }
  </style>
</head>
<body>

  <div class="registration-container">
    <!-- Header -->
    <div class="header-section">
      <h1>Pendaftaran <span class="text-primary">Membership</span></h1>
      <p style="color: rgba(255, 255, 255, 0.7);">Lengkapi formulir berikut untuk bergabung dengan Arena FIT</p>
    </div>

    <!-- Step Indicator -->
    <div class="step-indicator">
      <div class="step active" id="step1-indicator">
        <div class="step-circle">1</div>
        <span>Paket</span>
      </div>
      <span class="step-arrow">→</span>
      <div class="step" id="step2-indicator">
        <div class="step-circle">2</div>
        <span>Kelas</span>
      </div>
      <span class="step-arrow">→</span>
      <div class="step" id="step3-indicator">
        <div class="step-circle">3</div>
        <span>Data Diri</span>
      </div>
      <span class="step-arrow">→</span>
      <div class="step" id="step4-indicator">
        <div class="step-circle">4</div>
        <span>Pembayaran</span>
      </div>
    </div>

    <!-- Include semua section -->
    <?php include 'sections/step1_membership.php'; ?>
    <?php include 'sections/step2_classes.php'; ?>
    <?php include 'sections/step3_personal_data.php'; ?>
    <?php include 'sections/step4_payment.php'; ?>

  </div>

  <!-- Include modal -->
  <?php include 'modal/payment_modal.php'; ?>

  <script>
    let selectedMembership = null;
    let selectedMembershipPrice = 0;
    let selectedClasses = [];
    let selectedClassesPrice = 0;
    let uploadedFile = null;

    function selectMembership(type, price) {
      selectedMembership = type;
      selectedMembershipPrice = price;
      document.querySelectorAll('.membership-option').forEach(option => {
        option.classList.remove('selected');
      });
      const selectedOption = document.querySelector(`input[value="${type}"]`).parentElement;
      selectedOption.classList.add('selected');
      document.querySelectorAll('.membership-option input[type="radio"]').forEach(radio => {
        radio.checked = radio.value === type;
      });
      document.getElementById('btnStep1').disabled = false;
      updateSummary();
    }

    function toggleClass(className, price) {
      const checkbox = document.querySelector(`input[value="${className}"]`);
      checkbox.checked = !checkbox.checked;
      const option = checkbox.parentElement;
      if (checkbox.checked) {
        option.classList.add('selected');
        selectedClasses.push({ name: className, price });
      } else {
        option.classList.remove('selected');
        selectedClasses = selectedClasses.filter(c => c.name !== className);
      }
      selectedClassesPrice = selectedClasses.reduce((total, c) => total + c.price, 0);
      updateSummary();
    }

    function nextStep(step) {
      document.querySelectorAll('.section-card').forEach(card => card.classList.add('hidden'));
      document.getElementById(`step${step}`).classList.remove('hidden');

      document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
      document.getElementById(`step${step}-indicator`).classList.add('active');
    }

    function updateSummary() {
      if (selectedMembership) {
        const membershipName = document.querySelector(`input[value="${selectedMembership}"]`).parentElement.querySelector('.membership-name').textContent;
        const membershipPrice = formatCurrency(selectedMembershipPrice);
        document.getElementById('summaryMembership').textContent = `${membershipName} - ${membershipPrice}`;
      }

      if (selectedClasses.length > 0) {
        const classesNames = selectedClasses.map(c => {
          return document.querySelector(`input[value="${c.name}"]`).parentElement.querySelector('.class-name').textContent;
        }).join(', ');
        const classesPrice = formatCurrency(selectedClassesPrice);
        document.getElementById('summaryClasses').textContent = `${classesNames} - ${classesPrice}`;
        document.getElementById('summaryClassRow').style.display = 'block';
      } else {
        document.getElementById('summaryClassRow').style.display = 'none';
      }

      const total = selectedMembershipPrice + selectedClassesPrice;
      document.getElementById('summaryTotal').textContent = formatCurrency(total);
    }

    function updatePayment() {
      const total = selectedMembershipPrice + selectedClassesPrice;
      document.getElementById('paymentTotal').textContent = formatCurrency(total);
      document.getElementById('qrisAmount').textContent = formatCurrency(total);
    }

    function formatCurrency(amount) {
      return `Rp ${amount.toLocaleString('id-ID')}`;
    }

    function handleFileSelect(event) {
      const file = event.target.files[0];
      if (file) {
        uploadedFile = file;
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('filePreview').style.display = 'block';
        document.getElementById('btnConfirmPayment').disabled = false;
      }
    }

    function confirmPayment() {
      // Simpan data pendaftaran
      const formData = {
        fullname: document.getElementById('fullname').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        birthdate: document.getElementById('birthdate').value,
        address: document.getElementById('address').value,
        membership: selectedMembership,
        classes: selectedClasses.map(c => c.name),
        total: selectedMembershipPrice + selectedClassesPrice,
        paymentProof: uploadedFile ? uploadedFile.name : null,
        timestamp: new Date().toISOString()
      };
      
      console.log('Data Pendaftaran:', formData);
      
      // Tampilkan modal konfirmasi
      document.getElementById('paymentModal').classList.add('active');
      
      // Countdown redirect
      let countdown = 5;
      const countdownElement = document.getElementById('countdown');
      const countdownInterval = setInterval(() => {
        countdown--;
        countdownElement.textContent = countdown;
        if (countdown <= 0) {
          clearInterval(countdownInterval);
          goToDashboard();
        }
      }, 1000);
    }

    function goToDashboard() {
      // Redirect ke member dashboard
      window.location.href = 'indexmember.html';
    }

    // Handle form submission
    document.getElementById('registrationForm').addEventListener('submit', function(e) {
      e.preventDefault();
      updatePayment();
      nextStep(4);
    });

    // Drag and drop support
    const uploadSection = document.getElementById('uploadSection');
    
    uploadSection.addEventListener('dragover', (e) => {
      e.preventDefault();
      uploadSection.classList.add('dragover');
    });
    
    uploadSection.addEventListener('dragleave', () => {
      uploadSection.classList.remove('dragover');
    });
    
    uploadSection.addEventListener('drop', (e) => {
      e.preventDefault();
      uploadSection.classList.remove('dragover');
      const file = e.dataTransfer.files[0];
      if (file && file.type.startsWith('image/')) {
        uploadedFile = file;
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('filePreview').style.display = 'block';
        document.getElementById('btnConfirmPayment').disabled = false;
      }
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>