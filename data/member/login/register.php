<?php
ob_start();
session_start();
require "../../../setting/koneksi.php";
require "../../../setting/session.php";
blockLoginPageIfLoggedIn();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/autoload.php';

date_default_timezone_set('Asia/Jakarta');

if ($con->connect_error) {
    die("Koneksi gagal: " . $con->connect_error);
}

$error = "";
$success = "";

// Jika tombol register ditekan (hanya menyimpan data dan kirim verifikasi)
if (isset($_POST['registerbtn'])) {
    $nama = trim(htmlspecialchars($_POST['nama']));
    $email = trim(htmlspecialchars($_POST['email']));
    $password = trim(htmlspecialchars($_POST['password']));
    $confirm_password = trim(htmlspecialchars($_POST['confirm_password']));
    $no_hp = trim(htmlspecialchars($_POST['no_hp']));

    // Validasi input
    if (empty($nama) || empty($email) || empty($password) || empty($confirm_password) || empty($no_hp)) {
        $error = "Semua field harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // Cek apakah email atau username sudah terdaftar
        $check_query = $con->prepare("SELECT * FROM tbl_member WHERE email = ? OR nama = ?");
        $check_query->bind_param("ss", $email, $nama);
        $check_query->execute();
        $check_result = $check_query->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Username atau email sudah terdaftar!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Generate kode verifikasi 6 digit
            $verification_code = sprintf("%06d", mt_rand(1, 999999));
            $code_expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
            
            // Insert data ke database dengan is_verified = 0 (TANPA PAKET MEMBERSHIP DULU)
            $insert_query = $con->prepare("INSERT INTO tbl_member (nama, email, password, no_hp, verification_code, code_expiry, is_verified) VALUES (?, ?, ?, ?, ?, ?, 0)");
            $insert_query->bind_param("ssssss", $nama, $email, $hashed_password, $no_hp, $verification_code, $code_expiry);

            if ($insert_query->execute()) {
                // Kirim kode verifikasi via email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'valhidayat01@gmail.com';
                    $mail->Password   = 'ecbnikaaznxaujbk';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('valhidayat01@gmail.com', 'Gym Arena');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Kode Verifikasi Registrasi Gym Arena';
                    $mail->Body    = "Halo <strong>$nama</strong>,<br><br>
                                      Terima kasih telah mendaftar di Gym Arena!<br><br>
                                      Kode verifikasi Anda adalah: <h2>$verification_code</h2><br>
                                      Kode ini berlaku selama 1 jam.<br><br>
                                      Silakan masukkan kode ini pada halaman verifikasi.";

                    $mail->send();
                    
                    // Redirect ke halaman verifikasi
                    $_SESSION['verify_email'] = $email;
                    $_SESSION['registration_step'] = 'verify'; // Tandai bahwa user baru dari registrasi
                    header("Location: verify.php");
                    exit();
                    
                } catch (Exception $e) {
                    $error = "Registrasi berhasil, tapi gagal mengirim email verifikasi. Error: " . $mail->ErrorInfo;
                }
            } else {
                $error = "Terjadi kesalahan saat registrasi. Silakan coba lagi!";
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

        .registration-container {
            max-width: 600px;
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

        .step span {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .step.active span {
            color: #42a5f5;
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
            animation: shake 0.5s ease-in-out;
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

        .alert-info {
            background: rgba(66, 165, 245, 0.15);
            border: 1px solid rgba(66, 165, 245, 0.3);
            color: #93c5fd;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(66, 165, 245, 0.6);
            font-size: 1.1rem;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: rgba(25, 118, 210, 0.05);
            border: 1px solid rgba(66, 165, 245, 0.2);
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #42a5f5;
            background: rgba(25, 118, 210, 0.08);
            box-shadow: 0 0 0 3px rgba(66, 165, 245, 0.1);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
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

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 118, 210, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .links {
            text-align: center;
            margin-top: 25px;
        }

        .links a {
            color: #42a5f5;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: #64b5f6;
            text-decoration: underline;
        }

        .links p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .section-card {
                padding: 25px;
            }

            .header-section h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <!-- Header -->
        <div class="header-section">
            <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Arena FIT Logo">
            <h1>Pendaftaran <span class="text-primary">Member</span></h1>
            <p style="color: rgba(255, 255, 255, 0.7);">Daftar sekarang dan mulai perjalanan fitness Anda</p>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step active">
                <div class="step-circle">1</div>
                <span>Data Diri</span>
            </div>
            <span class="step-arrow">→</span>
            <div class="step">
                <div class="step-circle">2</div>
                <span>Verifikasi</span>
            </div>
            <span class="step-arrow">→</span>
            <div class="step">
                <div class="step-circle">3</div>
                <span>Pembayaran</span>
            </div>
        </div>

        <!-- Form Data Diri -->
        <div class="section-card">
            <h2 class="section-title">Data Diri</h2>

            <div class="alert alert-info">
                💡 Setelah mengisi data diri, kami akan mengirimkan kode verifikasi ke email Anda
            </div>

            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)) : ?>
                <div class="alert alert-success"><?= $success; ?></div>
            <?php endif; ?>

            <form method="POST">
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
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
                    </div>
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
</body>
</html>

<?php ob_end_flush(); ?>