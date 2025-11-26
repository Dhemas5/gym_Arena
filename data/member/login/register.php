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
    // Ambil dan sanitasi data
    $nama = trim(htmlspecialchars($_POST['nama']));
    $email = trim(htmlspecialchars($_POST['email']));
    $no_hp = trim(htmlspecialchars($_POST['no_hp']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (empty($nama) || empty($email) || empty($no_hp) || empty($password) || empty($confirm_password)) {
        $error = "❌ Semua field harus diisi!";
    } elseif ($password !== $confirm_password) {
        $error = "❌ Password dan konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "❌ Password minimal 6 karakter!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ Format email tidak valid!";
    } else {
        // Cek apakah email sudah terdaftar
        $check_email = $con->prepare("SELECT id_member FROM tbl_member WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $check_email->store_result();

        if ($check_email->num_rows > 0) {
            $error = "❌ Email sudah terdaftar! Silakan gunakan email lain.";
        } else {
            // Cek apakah username sudah terdaftar
            $check_username = $con->prepare("SELECT id_member FROM tbl_member WHERE nama = ?");
            $check_username->bind_param("s", $nama);
            $check_username->execute();
            $check_username->store_result();

            if ($check_username->num_rows > 0) {
                $error = "❌ Username sudah terdaftar! Silakan gunakan username lain.";
            } else {
                // Generate kode verifikasi
                $verification_code = sprintf("%06d", random_int(1, 999999));
                $code_expiry = date("Y-m-d H:i:s", time() + 3600); // 1 jam dari sekarang
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Generate ID member
                $id_member = "MEM" . date('YmdHis') . random_int(100, 999);

                // Cek struktur tabel dan sesuaikan query
                // Pertama, cek kolom apa saja yang ada di tabel
                $check_columns = $con->query("SHOW COLUMNS FROM tbl_member");
                $columns = [];
                while ($row = $check_columns->fetch_assoc()) {
                    $columns[] = $row['Field'];
                }

                // Buat query dinamis berdasarkan kolom yang ada
                if (in_array('created_at', $columns)) {
                    // Jika ada created_at
                    $insert = $con->prepare("INSERT INTO tbl_member (id_member, nama, email, no_hp, password, verification_code, code_expiry, is_verified, status_akun, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 'pending', NOW())");
                } else {
                    // Jika tidak ada created_at
                    $insert = $con->prepare("INSERT INTO tbl_member (id_member, nama, email, no_hp, password, verification_code, code_expiry, is_verified, status_akun) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 'pending')");
                }
                
                $insert->bind_param("sssssss", $id_member, $nama, $email, $no_hp, $hashed_password, $verification_code, $code_expiry);

                if ($insert->execute()) {
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
                        $mail->Subject = 'Verifikasi Email - Arena FIT';
                        $mail->Body    = "
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <style>
                                    body { font-family: Arial, sans-serif; color: #333; }
                                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                    .header { background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                                    .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
                                    .code { font-size: 32px; font-weight: bold; color: #1976d2; text-align: center; margin: 20px 0; }
                                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                                </style>
                            </head>
                            <body>
                                <div class='container'>
                                    <div class='header'>
                                        <h1>Arena FIT</h1>
                                        <p>Verifikasi Email Anda</p>
                                    </div>
                                    <div class='content'>
                                        <p>Halo <strong>$nama</strong>,</p>
                                        <p>Terima kasih telah mendaftar di Arena FIT. Berikut adalah kode verifikasi untuk mengaktifkan akun Anda:</p>
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
                        
                        // Tambahkan plain text version
                        $mail->AltBody = "Halo $nama,\n\nKode verifikasi Anda: $verification_code\n\nKode berlaku hingga: " . date('d M Y H:i:s', strtotime($code_expiry)) . " WIB\n\nSilakan masukkan kode pada halaman verifikasi.";

                        if ($mail->send()) {
                            // Set session untuk verifikasi
                            $_SESSION['verify_email'] = $email;
                            $_SESSION['registration_step'] = 2;
                            
                            // Redirect ke halaman verifikasi
                            header("Location: verify.php");
                            exit();
                        } else {
                            $error = "❌ Gagal mengirim email verifikasi. Silakan coba lagi.";
                            // Hapus data yang sudah tersimpan jika gagal kirim email
                            $delete = $con->prepare("DELETE FROM tbl_member WHERE email = ?");
                            $delete->bind_param("s", $email);
                            $delete->execute();
                        }
                    } catch (Exception $e) {
                        $error = "❌ Terjadi kesalahan saat mengirim email. Silakan coba lagi.";
                        // Hapus data yang sudah tersimpan jika gagal kirim email
                        $delete = $con->prepare("DELETE FROM tbl_member WHERE email = ?");
                        $delete->bind_param("s", $email);
                        $delete->execute();
                    }
                } else {
                    $error = "❌ Gagal melakukan registrasi! Silakan coba lagi. Error: " . $con->error;
                }
            }
        }
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
                💡 Setelah mengisi data diri, kami akan mengirimkan kode verifikasi ke email Anda. Setelah verifikasi berhasil, Anda langsung dapat mengakses dashboard member.
            </div>

            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)) : ?>
                <div class="alert alert-success"><?= $success; ?></div>
            <?php endif; ?>

            <form method="POST" id="registrationForm">
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

            // Toggle icon
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

            // Toggle icon
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

            // Check password length
            if (password.length >= 6) strength += 25;
            if (password.length >= 8) strength += 25;

            // Check for uppercase letters
            if (/[A-Z]/.test(password)) strength += 25;

            // Check for numbers and special characters
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^A-Za-z0-9]/.test(password)) strength += 10;

            // Update strength bar
            passwordStrengthBar.style.width = strength + '%';

            // Update color based on strength
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

        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                passwordInput.focus();
                return;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                confirmPasswordInput.focus();
            }
        });

        // Auto-format phone number
        const phoneInput = document.querySelector('input[name="no_hp"]');
        phoneInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>

</html>
<?php ob_end_flush(); ?>