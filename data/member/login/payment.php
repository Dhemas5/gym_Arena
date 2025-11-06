<?php
ob_start();
session_start();
require "../../../setting/koneksi.php";

date_default_timezone_set('Asia/Jakarta');

// Cek apakah sudah verifikasi email
if (!isset($_SESSION['payment_email'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['payment_email'];
$error = "";
$success = "";

// Ambil data member - PERBAIKAN: gunakan is_verified = 1
$stmt = $con->prepare("SELECT * FROM tbl_member WHERE email = ? AND is_verified = 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: register.php");
    exit();
}

$member = $result->fetch_assoc();
$member_id = $member['id_member']; // Primary key dari tbl_member

// Proses pembayaran
if (isset($_POST['payment_submit'])) {
    $membership_type = htmlspecialchars($_POST['membership_type']);
    $membership_price = floatval($_POST['membership_price']); // PERBAIKAN: gunakan float untuk decimal
    
    // Handle file upload
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['payment_proof'];
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $file['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed)) {
            $error = "Format file tidak valid! Gunakan JPG, JPEG, atau PNG.";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = "Ukuran file maksimal 2MB!";
        } else {
            // Generate unique filename
            $new_filename = "payment_" . $member_id . "_" . time() . "." . $file_ext;
            $upload_path = "../../../uploads/payments/" . $new_filename;
            
            // Create directory if not exists
            if (!file_exists("../../../uploads/payments/")) {
                mkdir("../../../uploads/payments/", 0777, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // PERBAIKAN: Simpan data pembayaran ke tbl_payments dengan struktur yang sesuai
                $insert = $con->prepare("INSERT INTO tbl_payments (id_member, membership_type, amount, payment_proof, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
                $insert->bind_param("isds", $member_id, $membership_type, $membership_price, $new_filename);
                
                if ($insert->execute()) {
                    // PERBAIKAN: Update tbl_member dengan kolom yang sesuai database
                    $update_member = $con->prepare("UPDATE tbl_member SET membership_type = ?, membership_price = ?, payment_proof = ?, payment_status = 'pending' WHERE id_member = ?");
                    $update_member->bind_param("sdsi", $membership_type, $membership_price, $new_filename, $member_id);
                    $update_member->execute();
                    
                    $success = "Pembayaran berhasil disubmit! Mengarahkan ke halaman member...";
                    
                    // Set session untuk auto-login
                    $_SESSION['id_member'] = $member['id_member'];
                    $_SESSION['nama'] = $member['nama'];
                    $_SESSION['email'] = $member['email'];
                    $_SESSION['level'] = 'member';
                    
                    // Hapus session pembayaran
                    unset($_SESSION['payment_email']);
                    
                    // Redirect ke index member
                    header("refresh:2;url=../landingpage/indexmember.html");
                } else {
                    $error = "Gagal menyimpan data pembayaran: " . $con->error;
                }
            } else {
                $error = "Gagal mengupload file!";
            }
        }
    } else {
        $error = "Bukti pembayaran harus diupload!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Membership - Arena FIT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .payment-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .header-section img {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            margin-bottom: 20px;
            border: 2px solid rgba(66, 165, 245, 0.3);
        }

        .header-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
        }

        .header-section .text-primary {
            background: linear-gradient(135deg, #42a5f5 0%, #1976d2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(25, 118, 210, 0.2);
            border: 2px solid rgba(66, 165, 245, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 700;
            transition: all 0.3s;
        }

        .step.active .step-circle {
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            border-color: #42a5f5;
            color: #fff;
            box-shadow: 0 0 20px rgba(66, 165, 245, 0.4);
        }

        .step.completed .step-circle {
            background: rgba(34, 197, 94, 0.2);
            border-color: #22c55e;
            color: #22c55e;
        }

        .step span {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .step.active span {
            color: #42a5f5;
        }

        .step.completed span {
            color: #22c55e;
        }

        .step-arrow {
            color: rgba(66, 165, 245, 0.3);
            font-size: 1.5rem;
        }

        .section-card {
            background: rgba(13, 27, 42, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            border: 1px solid rgba(66, 165, 245, 0.2);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            margin-bottom: 30px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 25px;
        }

        .alert {
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 0.9rem;
            margin-bottom: 25px;
            border: none;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }

        .success-modal {
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

        .success-modal.active {
            display: flex;
        }

        .success-modal-content {
            background: #0d1b2a;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            border: 1px solid rgba(66, 165, 245, 0.3);
            text-align: center;
            animation: slideIn 0.3s ease-out;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: rgba(34, 197, 94, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 4rem;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }

        .success-title {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 15px;
        }

        .success-text {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .loading-bar {
            width: 100%;
            height: 4px;
            background: rgba(66, 165, 245, 0.2);
            border-radius: 2px;
            overflow: hidden;
            margin-top: 20px;
        }

        .loading-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #42a5f5, #1976d2);
            width: 0%;
            animation: loadingBar 2s ease-out forwards;
        }

        @keyframes loadingBar {
            to {
                width: 100%;
            }
        }

        .alert-info {
            background: rgba(66, 165, 245, 0.15);
            border: 1px solid rgba(66, 165, 245, 0.3);
            color: #93c5fd;
        }

        .membership-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .membership-option {
            background: rgba(25, 118, 210, 0.05);
            border: 2px solid rgba(66, 165, 245, 0.2);
            border-radius: 16px;
            padding: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .membership-option:hover {
            border-color: #42a5f5;
            background: rgba(25, 118, 210, 0.1);
            transform: translateY(-5px);
        }

        .membership-option.selected {
            background: rgba(66, 165, 245, 0.2);
            border: 2px solid #42a5f5;
        }

        .membership-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .membership-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
        }

        .membership-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #42a5f5;
            margin-bottom: 5px;
        }

        .membership-period {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
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

        .payment-method {
            background: rgba(25, 118, 210, 0.05);
            padding: 25px;
            border-radius: 15px;
            margin: 20px 0;
            border: 1px solid rgba(66, 165, 245, 0.2);
        }

        .payment-method h3 {
            color: #42a5f5;
            margin-bottom: 20px;
        }

        .bank-info {
            background: rgba(13, 27, 42, 0.5);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid rgba(66, 165, 245, 0.2);
            margin-top: 15px;
        }

        .bank-info p {
            margin: 8px 0;
            color: rgba(255, 255, 255, 0.9);
        }

        .bank-info strong {
            color: #42a5f5;
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
            padding: 12px 30px;
            border-radius: 8px;
            color: #42a5f5;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-block;
            font-weight: 600;
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

        .btn-primary {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 118, 210, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .section-card {
                padding: 25px;
            }

            .header-section h1 {
                font-size: 2rem;
            }

            .membership-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <!-- Header -->
        <div class="header-section">
            <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Arena FIT Logo">
            <h1>Pembayaran <span class="text-primary">Membership</span></h1>
            <p style="color: rgba(255, 255, 255, 0.7);">Langkah terakhir untuk bergabung dengan Arena FIT</p>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step completed">
                <div class="step-circle">✓</div>
                <span>Data Diri</span>
            </div>
            <span class="step-arrow">→</span>
            <div class="step completed">
                <div class="step-circle">✓</div>
                <span>Verifikasi</span>
            </div>
            <span class="step-arrow">→</span>
            <div class="step active">
                <div class="step-circle">3</div>
                <span>Pembayaran</span>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="section-card">
            <h2 class="section-title">Pilih Paket & Lakukan Pembayaran</h2>

            <div class="alert alert-info">
                💡 Halo <strong><?= htmlspecialchars($member['nama']); ?></strong>! Pilih paket membership dan upload bukti pembayaran
            </div>

            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)) : ?>
                <script>
                    // Tampilkan modal sukses
                    window.addEventListener('load', function() {
                        document.getElementById('successModal').classList.add('active');
                    });
                </script>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="paymentForm">
                <!-- Membership Selection -->
                <h3 style="color: #fff; margin-bottom: 20px;">Pilih Paket Membership</h3>
                <div class="membership-grid">
                    <div class="membership-option" onclick="selectMembership('bulanan-umum', 285000)">
                        <input type="radio" name="membership_type" value="bulanan-umum" required>
                        <div class="membership-name">Bulanan Umum</div>
                        <div class="membership-price">Rp 285.000</div>
                        <div class="membership-period">per bulan</div>
                    </div>

                    <div class="membership-option" onclick="selectMembership('bulanan-pelajar', 200000)">
                        <input type="radio" name="membership_type" value="bulanan-pelajar">
                        <div class="membership-name">Bulanan Pelajar</div>
                        <div class="membership-price">Rp 200.000</div>
                        <div class="membership-period">per bulan</div>
                    </div>

                    <div class="membership-option" onclick="selectMembership('3bulan-umum', 675000)">
                        <input type="radio" name="membership_type" value="3bulan-umum">
                        <div class="membership-name">3 Bulan Umum</div>
                        <div class="membership-price">Rp 675.000</div>
                        <div class="membership-period">3 bulan</div>
                    </div>

                    <div class="membership-option" onclick="selectMembership('3bulan-pelajar', 550000)">
                        <input type="radio" name="membership_type" value="3bulan-pelajar">
                        <div class="membership-name">3 Bulan Pelajar</div>
                        <div class="membership-price">Rp 550.000</div>
                        <div class="membership-period">3 bulan</div>
                    </div>

                    <div class="membership-option" onclick="selectMembership('6bulan-umum', 1250000)">
                        <input type="radio" name="membership_type" value="6bulan-umum">
                        <div class="membership-name">6 Bulan Umum</div>
                        <div class="membership-price">Rp 1.250.000</div>
                        <div class="membership-period">6 bulan</div>
                    </div>

                    <div class="membership-option" onclick="selectMembership('6bulan-pelajar', 1000000)">
                        <input type="radio" name="membership_type" value="6bulan-pelajar">
                        <div class="membership-name">6 Bulan Pelajar</div>
                        <div class="membership-price">Rp 1.000.000</div>
                        <div class="membership-period">6 bulan</div>
                    </div>

                    <div class="membership-option" onclick="selectMembership('tahunan-umum', 2300000)">
                        <input type="radio" name="membership_type" value="tahunan-umum">
                        <div class="membership-name">1 Tahun Umum</div>
                        <div class="membership-price">Rp 2.300.000</div>
                        <div class="membership-period">per tahun</div>
                    </div>

                    <div class="membership-option" onclick="selectMembership('tahunan-pelajar', 1850000)">
                        <input type="radio" name="membership_type" value="tahunan-pelajar">
                        <div class="membership-name">1 Tahun Pelajar</div>
                        <div class="membership-price">Rp 1.850.000</div>
                        <div class="membership-period">per tahun</div>
                    </div>
                </div>

                <input type="hidden" name="membership_price" id="membership_price" value="0">

                <!-- Payment Instructions -->
                <div class="payment-instructions">
                    <h3>📋 Total Pembayaran</h3>
                    <ul>
                        <li style="font-size: 1.3rem;"><strong id="totalPayment">Rp 0</strong></li>
                    </ul>
                </div>

                <!-- Payment Methods -->
                <div class="payment-method">
                    <h3>💳 QRIS</h3>
                    <div style="text-align: center;">
                        <img src="https://via.placeholder.com/200x200?text=QRIS+Arena+FIT" alt="QRIS" 
                            style="width: 200px; height: 200px; border-radius: 10px; border: 2px solid rgba(66, 165, 245, 0.3);">
                        <p style="color: rgba(255, 255, 255, 0.8); margin-top: 10px;">Scan dengan aplikasi banking/e-wallet</p>
                    </div>

                    <h3 style="margin-top: 30px;">🏦 Transfer Bank</h3>
                    <div class="bank-info">
                        <p><strong>Bank:</strong> BCA</p>
                        <p><strong>No. Rekening:</strong> 1234567890</p>
                        <p><strong>Atas Nama:</strong> Arena FIT</p>
                    </div>
                </div>

                <!-- Upload Section -->
                <div class="upload-section" id="uploadSection">
                    <h3 style="color: #fff; margin-bottom: 15px;">📎 Upload Bukti Pembayaran</h3>
                    <p style="color: rgba(255, 255, 255, 0.7); margin-bottom: 20px;">
                        Upload screenshot/foto bukti transfer (JPG, PNG, max 2MB)
                    </p>
                    
                    <div class="file-input-wrapper">
                        <label for="payment_proof" class="btn-upload">
                            📁 Pilih File
                        </label>
                        <input type="file" id="payment_proof" name="payment_proof" accept="image/*" required onchange="handleFileSelect(event)">
                    </div>
                    
                    <div id="filePreview" class="file-preview" style="display: none;">
                        ✅ <span id="fileName"></span>
                    </div>
                </div>

                <button type="submit" name="payment_submit" class="btn-primary" id="btnSubmit" disabled>
                    Konfirmasi Pembayaran
                </button>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="success-modal" id="successModal">
        <div class="success-modal-content">
            <div class="success-icon">✓</div>
            <h2 class="success-title">Pembayaran Berhasil!</h2>
            <p class="success-text">
                Terima kasih telah melakukan pembayaran.<br>
                Data Anda sedang dalam proses verifikasi oleh admin.<br><br>
                <strong>Anda akan diarahkan ke halaman member...</strong>
            </p>
            <div class="loading-bar">
                <div class="loading-bar-fill"></div>
            </div>
        </div>
    </div>

    <script>
        let selectedPrice = 0;

        function selectMembership(type, price) {
            selectedPrice = price;
            
            document.querySelectorAll('.membership-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            const selectedOption = document.querySelector(`input[value="${type}"]`).parentElement;
            selectedOption.classList.add('selected');
            
            document.querySelectorAll('.membership-option input[type="radio"]').forEach(radio => {
                radio.checked = radio.value === type;
            });
            
            document.getElementById('membership_price').value = price;
            document.getElementById('totalPayment').textContent = formatCurrency(price);
            
            checkFormValid();
        }

        function formatCurrency(amount) {
            return `Rp ${amount.toLocaleString('id-ID')}`;
        }

        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                if (!file.type.startsWith('image/')) {
                    alert('File harus berupa gambar!');
                    event.target.value = '';
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal 2MB!');
                    event.target.value = '';
                    return;
                }

                document.getElementById('fileName').textContent = file.name;
                document.getElementById('filePreview').style.display = 'block';
                checkFormValid();
            }
        }

        function checkFormValid() {
            const membershipSelected = document.querySelector('input[name="membership_type"]:checked');
            const fileUploaded = document.getElementById('payment_proof').files.length > 0;
            
            document.getElementById('btnSubmit').disabled = !(membershipSelected && fileUploaded);
        }

        // Drag and drop
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
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal 2MB');
                    return;
                }
                
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                document.getElementById('payment_proof').files = dataTransfer.files;
                
                document.getElementById('fileName').textContent = file.name;
                document.getElementById('filePreview').style.display = 'block';
                checkFormValid();
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>