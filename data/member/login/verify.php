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

// Cek apakah user sudah di halaman verifikasi dengan session yang valid
if (!isset($_SESSION['verify_email']) || empty($_SESSION['verify_email'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['verify_email'];
$error = "";
$success = "";

// Validasi email session
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    session_destroy();
    header("Location: register.php");
    exit();
}

// Proses verifikasi kode
if (isset($_POST['verifybtn'])) {
    $input_code = trim(htmlspecialchars($_POST['verification_code']));
    
    // Validasi input kode
    if (empty($input_code) || strlen($input_code) != 6 || !is_numeric($input_code)) {
        $error = "❌ Kode verifikasi harus 6 digit angka!";
    } else {
        // Query verifikasi dengan prepared statement
        $query = $con->prepare("SELECT * FROM tbl_member WHERE email = ? AND verification_code = ? AND is_verified = 0");
        $query->bind_param("ss", $email, $input_code);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows === 1) {
            $member = $result->fetch_assoc();
            $expiry_time = strtotime($member['code_expiry']);
            $current_time = time();
            
            // Check expiry time
            if ($expiry_time > $current_time) {
                // Kode valid, lanjutkan verifikasi
                $update = $con->prepare("UPDATE tbl_member SET is_verified = 1, verification_code = NULL, code_expiry = NULL, verified_at = NOW(), status_akun = 'aktif' WHERE email = ?");
                $update->bind_param("s", $email);
                
                if ($update->execute()) {
                    // HAPUS BAGIAN NOTIFIKASI MANUAL - Biarkan trigger yang menangani
                    // atau buat notifikasi sederhana tanpa kolom judul
                    try {
                        $notifikasi_query = $con->prepare("INSERT INTO tbl_notifikasi (pesan, tipe, id_referensi, jenis_referensi, dibuat_pada) VALUES (?, 'member_baru', ?, 'member', NOW())");
                        $pesan_notifikasi = "Member " . $member['nama'] . " (" . $email . ") telah berhasil verifikasi email dan aktif.";
                        $notifikasi_query->bind_param("si", $pesan_notifikasi, $member['id_member']);
                        $notifikasi_query->execute();
                    } catch (Exception $e) {
                        // Skip error notifikasi jika masih bermasalah
                        error_log("Error notifikasi: " . $e->getMessage());
                    }
                    
                    // Hapus session verifikasi
                    unset($_SESSION['verify_email']);
                    unset($_SESSION['registration_step']);
                    
                    // Set session success
                    $_SESSION['verification_success'] = true;
                    $_SESSION['verified_email'] = $email;
                    
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "❌ Gagal memperbarui status verifikasi! Silakan coba lagi.";
                }
            } else {
                $error = "❌ Kode verifikasi sudah kadaluarsa! Silakan minta kode baru.";
            }
        } else {
            $error = "❌ Kode verifikasi salah! Periksa kembali kode Anda.";
        }
    }
}

// Kirim ulang kode verifikasi
if (isset($_POST['resendbtn'])) {
    // Generate kode baru
    $new_code = sprintf("%06d", random_int(1, 999999));
    $new_expiry = date("Y-m-d H:i:s", time() + 3600); // 1 jam dari sekarang
    
    // Update kode di database
    $update = $con->prepare("UPDATE tbl_member SET verification_code = ?, code_expiry = ? WHERE email = ?");
    $update->bind_param("sss", $new_code, $new_expiry, $email);
    
    if ($update->execute()) {
        // Ambil data user
        $user_query = $con->prepare("SELECT nama FROM tbl_member WHERE email = ?");
        $user_query->bind_param("s", $email);
        $user_query->execute();
        $user_result = $user_query->get_result();
        
        if ($user_result->num_rows === 1) {
            $user = $user_result->fetch_assoc();
            $nama = $user['nama'];
            
            // Kirim email
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
                $mail->Subject = 'Kode Verifikasi Baru - Arena FIT';
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
                                <p>Berikut adalah kode verifikasi baru untuk akun Arena FIT Anda:</p>
                                <div class='code'>$new_code</div>
                                <p>Kode ini berlaku hingga: <strong>" . date('d M Y H:i:s', strtotime($new_expiry)) . " WIB</strong></p>
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
                $mail->AltBody = "Halo $nama,\n\nKode verifikasi baru Anda: $new_code\n\nKode berlaku hingga: " . date('d M Y H:i:s', strtotime($new_expiry)) . " WIB\n\nSilakan masukkan kode pada halaman verifikasi.";

                if ($mail->send()) {
                    $success = "✅ Kode verifikasi baru telah dikirim ke email Anda! Berlaku hingga " . date('H:i', strtotime($new_expiry)) . " WIB";
                } else {
                    $error = "❌ Gagal mengirim email. Silakan coba lagi.";
                }
            } catch (Exception $e) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
                $error = "❌ Terjadi kesalahan saat mengirim email. Silakan coba lagi nanti.";
            }
        } else {
            $error = "❌ Data user tidak ditemukan!";
        }
    } else {
        $error = "❌ Gagal memperbarui kode verifikasi! Silakan coba lagi.";
    }
}

// Cek status kode verifikasi saat ini
$status_query = $con->prepare("SELECT code_expiry FROM tbl_member WHERE email = ? AND is_verified = 0");
$status_query->bind_param("s", $email);
$status_query->execute();
$status_result = $status_query->get_result();

$current_code_expiry = null;
if ($status_result->num_rows === 1) {
    $status_data = $status_result->fetch_assoc();
    $current_code_expiry = $status_data['code_expiry'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Arena FIT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        /* CSS tetap sama seperti sebelumnya */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(66, 165, 245, 0.15) 0%, transparent 70%);
            top: -250px;
            right: -250px;
            border-radius: 50%;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(25, 118, 210, 0.1) 0%, transparent 70%);
            bottom: -200px;
            left: -200px;
            border-radius: 50%;
        }

        .verify-container {
            background: rgba(13, 27, 42, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 50px 40px;
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(66, 165, 245, 0.2);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: slideIn 0.5s ease-out;
            position: relative;
            z-index: 1;
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

        .logo-section {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-section img {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            margin-bottom: 20px;
            border: 2px solid rgba(66, 165, 245, 0.3);
        }

        .logo-section h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }

        .logo-section .text-primary {
            background: linear-gradient(135deg, #42a5f5 0%, #1976d2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo-section p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.95rem;
        }

        .email-info {
            background: rgba(66, 165, 245, 0.1);
            border: 1px solid rgba(66, 165, 245, 0.3);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            margin-bottom: 25px;
        }

        .email-info p {
            color: rgba(255, 255, 255, 0.7);
            margin: 0 0 5px 0;
            font-size: 0.9rem;
        }

        .email-info strong {
            color: #42a5f5;
            font-size: 1.05rem;
            display: block;
        }

        .alert {
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 0.9rem;
            margin-bottom: 25px;
            animation: shake 0.5s ease-in-out;
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
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 12px;
            text-align: center;
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
            letter-spacing: normal;
            font-size: 1rem;
        }

        .btn-verify {
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
            margin-bottom: 15px;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 118, 210, 0.4);
        }

        .btn-verify:active {
            transform: translateY(0);
        }

        .btn-resend {
            width: 100%;
            padding: 15px;
            background: rgba(66, 165, 245, 0.1);
            border: 1px solid rgba(66, 165, 245, 0.3);
            border-radius: 12px;
            color: #42a5f5;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-resend:hover {
            background: rgba(66, 165, 245, 0.2);
            transform: translateY(-1px);
        }

        .btn-resend:active {
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
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: #64b5f6;
            text-decoration: underline;
        }

        .links p {
            color: rgba(255, 255, 255, 0.6);
            margin: 12px 0;
            font-size: 0.95rem;
        }

        .info-box {
            background: rgba(66, 165, 245, 0.05);
            border: 1px solid rgba(66, 165, 245, 0.2);
            border-radius: 12px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
        }

        .info-box p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            margin: 0;
            line-height: 1.6;
        }

        .expiry-info {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            text-align: center;
        }

        .expiry-info p {
            color: #ffd54f;
            font-size: 0.8rem;
            margin: 0;
        }

        @media (max-width: 576px) {
            .verify-container {
                padding: 40px 25px;
            }

            .logo-section h1 {
                font-size: 1.6rem;
            }

            .form-control {
                font-size: 1.2rem;
                letter-spacing: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="logo-section">
            <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Arena FIT Logo">
            <h1>Verifikasi <span class="text-primary">Email</span></h1>
            <p>Masukkan kode 6 digit yang dikirim ke email Anda</p>
        </div>

        <div class="email-info">
            <p>Kode verifikasi dikirim ke:</p>
            <strong><?= htmlspecialchars($email); ?></strong>
            <?php if ($current_code_expiry): ?>
                <div class="expiry-info">
                    <p>⏰ Kode berlaku hingga: <?= date('H:i', strtotime($current_code_expiry)) ?> WIB</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <div class="alert alert-success"><?= $success; ?></div>
        <?php endif; ?>

        <form method="POST" id="verifyForm">
            <div class="form-group">
                <label class="form-label">Kode Verifikasi</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔐</span>
                    <input 
                        type="text" 
                        name="verification_code" 
                        class="form-control" 
                        placeholder="000000" 
                        maxlength="6"
                        pattern="[0-9]{6}"
                        required
                        autofocus
                        autocomplete="one-time-code"
                        value="<?= isset($_POST['verification_code']) ? htmlspecialchars($_POST['verification_code']) : '' ?>"
                    >
                </div>
            </div>

            <button type="submit" name="verifybtn" class="btn-verify">
                ✓ Verifikasi Email
            </button>
        </form>

        <form method="POST" style="margin-top: 15px;">
            <button type="submit" name="resendbtn" class="btn-resend">
                🔄 Kirim Ulang Kode
            </button>
        </form>

        <div class="info-box">
            <p>💡 Kode verifikasi berlaku selama 1 jam (WIB). Jika tidak menerima email, periksa folder spam atau klik tombol kirim ulang.</p>
        </div>

        <div class="links">
            <p><a href="register.php">← Kembali ke Registrasi</a></p>
        </div>
    </div>

    <script>
        // Auto format input kode verifikasi (hanya angka)
        const codeInput = document.querySelector('input[name="verification_code"]');
        
        codeInput.addEventListener('input', function(e) {
            // Hanya izinkan angka
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // HAPUS AUTO SUBMIT - Ini penyebab refresh otomatis
            // Hanya beri visual feedback ketika 6 digit terisi
            if (this.value.length === 6) {
                this.style.borderColor = '#4caf50';
                this.style.boxShadow = '0 0 0 3px rgba(76, 175, 80, 0.1)';
                
                // Opsional: Focus ke tombol verify
                document.querySelector('.btn-verify').focus();
            } else {
                this.style.borderColor = 'rgba(66, 165, 245, 0.2)';
                this.style.boxShadow = 'none';
            }
        });

        // Prevent form submission jika kode tidak valid
        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            const code = codeInput.value;
            if (code.length !== 6 || !/^\d+$/.test(code)) {
                e.preventDefault();
                alert('Kode verifikasi harus 6 digit angka!');
                codeInput.focus();
            }
        });

        // Auto focus ke input kode dan select semua text
        codeInput.focus();
        codeInput.select();

        // Tambahkan event listener untuk keypress (Enter)
        codeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('verifyForm').submit();
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>