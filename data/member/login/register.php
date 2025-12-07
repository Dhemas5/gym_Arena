<?php
ob_start();
session_start();
require "../../../setting/koneksi.php";

// Import PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../../vendor/autoload.php';

// Set timezone ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

$error = "";
$success = "";

// Proses registrasi
if (isset($_POST['registerbtn'])) {
    $nama = trim(htmlspecialchars($_POST['nama']));
    $email = trim(htmlspecialchars($_POST['email']));
    $no_hp = trim(htmlspecialchars($_POST['no_hp']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $tipe_member = trim($_POST['tipe_member']);
    
    // Validasi input
    if (empty($nama) || empty($email) || empty($no_hp) || empty($password) || empty($tipe_member)) {
        $error = "Semua field wajib diisi!";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // Cek apakah email sudah terdaftar
        $check_email = $con->prepare("SELECT email FROM tbl_member WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $result = $check_email->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email sudah terdaftar! Silakan gunakan email lain.";
        } else {
            // Cek apakah nama sudah terdaftar
            $check_nama = $con->prepare("SELECT nama FROM tbl_member WHERE nama = ?");
            $check_nama->bind_param("s", $nama);
            $check_nama->execute();
            $result_nama = $check_nama->get_result();
            
            if ($result_nama->num_rows > 0) {
                $error = "Username sudah terdaftar! Silakan gunakan username lain.";
            } else {
                // Handle upload file KTM untuk mahasiswa
                $bukti_ktm = null;
                $is_mahasiswa = 0;
                $upload_success = true;
                
                if ($tipe_member == 'mahasiswa') {
                    $is_mahasiswa = 1;
                    if (!isset($_FILES['bukti_ktm']) || $_FILES['bukti_ktm']['error'] == UPLOAD_ERR_NO_FILE) {
                        $error = "Upload KTM/Kartu Pelajar wajib untuk Mahasiswa/Pelajar!";
                        $upload_success = false;
                    } else {
                        $file = $_FILES['bukti_ktm'];
                        $file_name = $file['name'];
                        $file_tmp = $file['tmp_name'];
                        $file_size = $file['size'];
                        $file_error = $file['error'];
                        
                        // Validasi file
                        $allowed_ext = array('jpg', 'jpeg', 'png', 'pdf');
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        
                        if ($file_error !== 0) {
                            $error = "Terjadi kesalahan saat upload file!";
                            $upload_success = false;
                        } elseif (!in_array($file_ext, $allowed_ext)) {
                            $error = "Format file tidak valid! Gunakan JPG, PNG, atau PDF.";
                            $upload_success = false;
                        } elseif ($file_size > 2097152) { // 2MB in bytes
                            $error = "Ukuran file terlalu besar! Maksimal 2MB.";
                            $upload_success = false;
                        } else {
                            // Create upload directory if not exists
                            $upload_dir = "../../../uploads/ktm/";
                            if (!file_exists($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }
                            
                            // Generate unique filename
                            $new_filename = uniqid('ktm_', true) . '_' . time() . '.' . $file_ext;
                            $upload_path = $upload_dir . $new_filename;
                            
                            if (move_uploaded_file($file_tmp, $upload_path)) {
                                $bukti_ktm = $new_filename;
                            } else {
                                $error = "Gagal mengupload file! Periksa permission folder.";
                                $upload_success = false;
                            }
                        }
                    }
                }
                
                // Jika tidak ada error, lanjutkan insert ke database
                // Jika tidak ada error, lanjutkan insert ke database
// Jika tidak ada error, lanjutkan insert ke database
if ($upload_success && empty($error)) {
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate kode verifikasi 6 digit
    $verification_code = sprintf("%06d", mt_rand(1, 999999));
    
    // Set expiry 1 jam dari sekarang
    $code_expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
    
    // CARA BARU: Insert manual tanpa prepare statement
    $nama_safe = mysqli_real_escape_string($con, $nama);
    $email_safe = mysqli_real_escape_string($con, $email);
    $no_hp_safe = mysqli_real_escape_string($con, $no_hp);
    $hashed_password_safe = mysqli_real_escape_string($con, $hashed_password);
    $bukti_ktm_safe = mysqli_real_escape_string($con, $bukti_ktm);
    $verification_code_safe = mysqli_real_escape_string($con, $verification_code);
    $code_expiry_safe = mysqli_real_escape_string($con, $code_expiry);
    
    $sql = "INSERT INTO tbl_member (nama, email, password, no_hp, is_mahasiswa, ktm_file, verification_code, code_expiry, is_verified, membership_status) 
            VALUES ('$nama_safe', '$email_safe', '$hashed_password_safe', '$no_hp_safe', $is_mahasiswa, '$bukti_ktm_safe', '$verification_code_safe', '$code_expiry_safe', 0, 'belum_aktif')";
    
    if ($con->query($sql)) {
        $member_id = $con->insert_id;
                        
                        // Kirim email verifikasi
                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'valhidayat01@gmail.com';
                            $mail->Password   = 'ecbnikaaznxaujbk';
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port       = 587;
                            $mail->CharSet    = 'UTF-8';

                            $mail->setFrom('valhidayat01@gmail.com', 'Arena FIT');
                            $mail->addAddress($email);
                            $mail->addReplyTo('valhidayat01@gmail.com', 'Arena FIT');

                            $mail->isHTML(true);
                            $mail->Subject = 'Kode Verifikasi Email - Arena FIT';
                            $mail->Body    = "
                                <!DOCTYPE html>
                                <html>
                                <head>
                                    <style>
                                        body { font-family: Arial, sans-serif; color: #333; }
                                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                        .header { background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                                        .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
                                        .code { font-size: 32px; font-weight: bold; color: #1976d2; text-align: center; margin: 20px 0; letter-spacing: 8px; }
                                        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                                    </style>
                                </head>
                                <body>
                                    <div class='container'>
                                        <div class='header'>
                                            <h1>Selamat Datang di Arena FIT!</h1>
                                            <p>Verifikasi Email Anda</p>
                                        </div>
                                        <div class='content'>
                                            <p>Halo <strong>$nama</strong>,</p>
                                            <p>Terima kasih telah mendaftar di Arena FIT. Untuk mengaktifkan akun Anda, silakan masukkan kode verifikasi berikut:</p>
                                            <div class='code'>$verification_code</div>
                                            <p>Kode ini berlaku hingga: <strong>" . date('d M Y H:i:s', strtotime($code_expiry)) . " WIB</strong></p>
                                            <p>Silakan masukkan kode ini pada halaman verifikasi untuk mengaktifkan akun Anda.</p>
                                            <p>Jika Anda tidak merasa mendaftar, abaikan email ini.</p>
                                        </div>
                                        <div class='footer'>
                                            <p>&copy; " . date('Y') . " Arena FIT. All rights reserved.</p>
                                        </div>
                                    </div>
                                </body>
                                </html>
                            ";
                            
                            $mail->AltBody = "Halo $nama,\n\nKode verifikasi Anda: $verification_code\n\nKode berlaku hingga: " . date('d M Y H:i:s', strtotime($code_expiry)) . " WIB\n\nSilakan masukkan kode pada halaman verifikasi.";

                            if ($mail->send()) {
                                // Set session untuk verifikasi
                                $_SESSION['verify_email'] = $email;
                                $_SESSION['registration_step'] = 2;
                                $_SESSION['verify_member_id'] = $member_id;
                                
                                // Redirect ke halaman verify
                                header("Location: verify.php");
                                exit();
                            } else {
                                $error = "✖ Pendaftaran berhasil, tetapi gagal mengirim email verifikasi. Silakan minta kirim ulang kode.";
                                // Tetap redirect ke verify meskipun email gagal
                                $_SESSION['verify_email'] = $email;
                                $_SESSION['registration_step'] = 2;
                                $_SESSION['verify_member_id'] = $member_id;
                                header("Location: verify.php?email_failed=1");
                                exit();
                            }
                        } catch (Exception $e) {
                            error_log("Mailer Error: " . $mail->ErrorInfo);
                            $error = "✖ Pendaftaran berhasil, tetapi terjadi kesalahan saat mengirim email.";
                            // Tetap redirect ke verify
                            $_SESSION['verify_email'] = $email;
                            $_SESSION['registration_step'] = 2;
                            $_SESSION['verify_member_id'] = $member_id;
                            header("Location: verify.php?email_failed=1");
                            exit();
                        }
                    } else {
                        $error = "✖ Gagal menyimpan data! Silakan coba lagi.";
                    }
                    $insert->close();
                }
            }
            $check_nama->close();
        }
        $check_email->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Member - Arena FIT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../landingpage/assets/css/registration.css">
</head>

<body>
    <div class="registration-container">
        <!-- Header -->
        <div class="header-section">
            <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Arena FIT Logo">
            <h1>Pendaftaran <span class="text-primary">Member</span></h1>
            <p>Daftar sekarang dan mulai perjalanan fitness Anda</p>
        </div>

        <!-- Step Indicator - Hanya 2 Step -->
        <div class="step-indicator">
            <div class="step <?= !isset($_SESSION['registration_step']) || $_SESSION['registration_step'] == 1 ? 'active' : '' ?>">
                <div class="step-circle">1</div>
                <span>Data Diri</span>
            </div>
            <span class="step-arrow">→</span>
            <div class="step <?= isset($_SESSION['registration_step']) && $_SESSION['registration_step'] == 2 ? 'active' : '' ?>">
                <div class="step-circle">2</div>
                <span>Verifikasi Email</span>
            </div>
        </div>

        <!-- Form Data Diri -->
        <div class="section-card">
            <h2 class="section-title">Data Diri</h2>

            <div class="alert alert-info">
                💡 Setelah mengisi data diri, kami akan mengirimkan kode verifikasi ke email Anda. Setelah verifikasi berhasil, Anda langsung dapat login.
            </div>

            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)) : ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="registrationForm" enctype="multipart/form-data">
                <!-- Tipe Member dengan Dropdown -->
                <div class="form-group">
                    <label class="form-label">Tipe Member *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👥</span>
                        <select name="tipe_member" id="tipe_member" class="form-control" required>
                            <option value="">-- Pilih Tipe Member --</option>
                            <option value="mahasiswa" <?= (isset($_POST['tipe_member']) && $_POST['tipe_member'] == 'mahasiswa') ? 'selected' : ''; ?>>
                                🎓 Mahasiswa/Pelajar
                            </option>
                            <option value="umum" <?= (isset($_POST['tipe_member']) && $_POST['tipe_member'] == 'umum') ? 'selected' : ''; ?>>
                                👤 Umum
                            </option>
                        </select>
                    </div>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Mahasiswa/Pelajar wajib upload KTM atau Kartu Pelajar
                    </small>
                </div>

                <!-- Upload KTM/Kartu Pelajar (Hidden by default) -->
                <div class="form-group" id="uploadKtmSection" style="display: none;">
                    <label class="form-label">Upload KTM / Kartu Pelajar *</label>
                    <div class="upload-area" id="uploadArea">
                        <input type="file" name="bukti_ktm" id="bukti_ktm" accept="image/*,.pdf" style="display: none;">
                        <div class="upload-content" id="uploadContent">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <p class="upload-text">Klik atau seret file ke sini</p>
                            <p class="upload-subtext">Format: JPG, PNG, PDF (Max: 2MB)</p>
                        </div>
                        <div class="upload-preview" id="uploadPreview" style="display: none;">
                            <img id="previewImage" src="" alt="Preview" style="display: none;">
                            <div id="previewPdf" style="display: none;">
                                <i class="fas fa-file-pdf" style="font-size: 3rem; color: #d32f2f;"></i>
                                <p id="pdfFileName" style="margin-top: 10px; font-weight: 500;"></p>
                            </div>
                            <button type="button" class="btn-remove-file" id="removeFile">
                                <i class="fas fa-times"></i> Hapus File
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted">
                        <i class="fas fa-exclamation-triangle"></i> Wajib untuk Mahasiswa/Pelajar. Pastikan foto/scan jelas dan terbaca.
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan username" required
                            value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">✉️</span>
                        <input type="email" name="email" class="form-control" placeholder="contoh@email.com" required
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">No. Telepon *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📱</span>
                        <input type="tel" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx" required
                            value="<?= isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Ulangi password" required>
                        <button type="button" class="password-toggle" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="passwordMatchMessage" style="margin-top: 8px; font-size: 0.9rem;"></div>
                </div>

                <button type="submit" name="registerbtn" class="btn-primary">
                    Daftar & Verifikasi Email
                </button>
            </form>

            <div class="links">
                <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const passwordMatchMessage = document.getElementById('passwordMatchMessage');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            const icon = this.querySelector('i');
            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);

            const icon = this.querySelector('i');
            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Password strength indicator
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            if (password.length >= 6) strength += 25;
            if (password.length >= 8) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^A-Za-z0-9]/.test(password)) strength += 10;

            passwordStrengthBar.style.width = strength + '%';

            if (strength < 50) {
                passwordStrengthBar.className = 'password-strength-bar strength-weak';
            } else if (strength < 80) {
                passwordStrengthBar.className = 'password-strength-bar strength-medium';
            } else {
                passwordStrengthBar.className = 'password-strength-bar strength-strong';
            }
        });

        // Password confirmation check
        confirmPasswordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirmPassword = this.value;

            if (confirmPassword === '') {
                passwordMatchMessage.textContent = '';
                passwordMatchMessage.style.color = '';
            } else if (password === confirmPassword) {
                passwordMatchMessage.textContent = '✓ Password cocok';
                passwordMatchMessage.style.color = '#4caf50';
            } else {
                passwordMatchMessage.textContent = '✗ Password tidak cocok';
                passwordMatchMessage.style.color = '#f44336';
            }
        });

        // Show/Hide KTM Upload Section
        const tipeMemberSelect = document.getElementById('tipe_member');
        const uploadKtmSection = document.getElementById('uploadKtmSection');
        const buktiKtmInput = document.getElementById('bukti_ktm');

        tipeMemberSelect.addEventListener('change', function() {
            if (this.value === 'mahasiswa') {
                uploadKtmSection.style.display = 'block';
                buktiKtmInput.setAttribute('required', 'required');
            } else {
                uploadKtmSection.style.display = 'none';
                buktiKtmInput.removeAttribute('required');
                buktiKtmInput.value = '';
                resetUploadArea();
            }
        });

        // File Upload Handling
        const uploadArea = document.getElementById('uploadArea');
        const uploadContent = document.getElementById('uploadContent');
        const uploadPreview = document.getElementById('uploadPreview');
        const previewImage = document.getElementById('previewImage');
        const previewPdf = document.getElementById('previewPdf');
        const pdfFileName = document.getElementById('pdfFileName');
        const removeFileBtn = document.getElementById('removeFile');

        // Click to upload
        uploadArea.addEventListener('click', function(e) {
            if (!e.target.closest('.btn-remove-file')) {
                buktiKtmInput.click();
            }
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#007bff';
            this.style.background = '#f0f8ff';
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#ddd';
            this.style.background = '#fafafa';
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#ddd';
            this.style.background = '#fafafa';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                buktiKtmInput.files = files;
                handleFileSelect(files[0]);
            }
        });

        // File input change
        buktiKtmInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                handleFileSelect(this.files[0]);
            }
        });

        // Handle file selection
        function handleFileSelect(file) {
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file terlalu besar! Maksimal 2MB.');
                buktiKtmInput.value = '';
                return;
            }

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
            if (!allowedTypes.includes(file.type)) {
                alert('Format file tidak valid! Gunakan JPG, PNG, atau PDF.');
                buktiKtmInput.value = '';
                return;
            }

            // Show preview
            uploadContent.style.display = 'none';
            uploadPreview.style.display = 'flex';

            if (file.type === 'application/pdf') {
                previewImage.style.display = 'none';
                previewPdf.style.display = 'flex';
                pdfFileName.textContent = file.name;
            } else {
                previewPdf.style.display = 'none';
                previewImage.style.display = 'block';
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        // Remove file
        removeFileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            buktiKtmInput.value = '';
            resetUploadArea();
        });

        function resetUploadArea() {
            uploadContent.style.display = 'flex';
            uploadPreview.style.display = 'none';
            previewImage.src = '';
        }

        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const tipeMember = tipeMemberSelect.value;

            if (!tipeMember) {
                e.preventDefault();
                alert('Silakan pilih tipe member terlebih dahulu!');
                tipeMemberSelect.focus();
                return;
            }

            if (tipeMember === 'mahasiswa' && !buktiKtmInput.files.length) {
                e.preventDefault();
                alert('Silakan upload KTM atau Kartu Pelajar untuk member Mahasiswa/Pelajar!');
                return;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                confirmPasswordInput.focus();
                return;
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (tipeMemberSelect.value === 'mahasiswa') {
                uploadKtmSection.style.display = 'block';
                buktiKtmInput.setAttribute('required', 'required');
            }
        });

        // Auto-format phone number
        const phoneInput = document.querySelector('input[name="no_hp"]');
        phoneInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
    
    <style>
        /* Styling untuk upload area */
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            background: #fafafa;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .upload-area:hover {
            border-color: #007bff;
            background: #f0f8ff;
        }

        .upload-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .upload-icon {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 15px;
        }

        .upload-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .upload-subtext {
            font-size: 0.9rem;
            color: #666;
        }

        .upload-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .upload-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        #previewPdf {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 15px;
        }

        .btn-remove-file {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-remove-file:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        .form-text {
            display: block;
            margin-top: 8px;
            font-size: 0.875rem;
        }

        .text-muted {
            color: #6c757d !important;
        }

        /* Style untuk select dropdown */
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
        }

        select.form-control option {
            padding: 10px;
        }
    </style>
</body>

</html>

<?php ob_end_flush(); ?>