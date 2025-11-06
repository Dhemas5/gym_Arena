<?php
ob_start();
session_start();
require "../../../setting/koneksi.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/autoload.php';

date_default_timezone_set('Asia/Jakarta');

// Cek apakah ada email yang perlu diverifikasi
if (!isset($_SESSION['verify_email'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['verify_email'];
$error = "";
$success = "";

// Proses verifikasi
if (isset($_POST['verifybtn'])) {
    $code = trim(htmlspecialchars($_POST['verification_code']));

    if (empty($code)) {
        $error = "Kode verifikasi harus diisi!";
    } else {
        // Cek kode verifikasi dan expiry
        $stmt = $con->prepare("SELECT * FROM tbl_member WHERE email = ? AND verification_code = ? AND code_expiry > NOW()");
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Update is_verified menjadi 1
            $update = $con->prepare("UPDATE tbl_member SET is_verified = 1, verification_code = NULL, code_expiry = NULL WHERE email = ?");
            $update->bind_param("s", $email);
            
            if ($update->execute()) {
                $success = "Email berhasil diverifikasi! Lanjutkan ke pembayaran...";
                
                // Simpan email untuk pembayaran
                $_SESSION['payment_email'] = $email;
                unset($_SESSION['verify_email']);
                
                // Redirect ke halaman pembayaran setelah 2 detik
                header("refresh:2;url=payment.php");
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi!";
            }
        } else {
            $error = "Kode verifikasi salah atau sudah kadaluarsa!";
        }
    }
}

// Resend kode verifikasi
if (isset($_POST['resendbtn'])) {
    $verification_code = sprintf("%06d", mt_rand(1, 999999));
    $code_expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
    
    $update = $con->prepare("UPDATE tbl_member SET verification_code = ?, code_expiry = ? WHERE email = ?");
    $update->bind_param("sss", $verification_code, $code_expiry, $email);
    $update->execute();

    // Kirim email
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
        $mail->Body    = "Halo,<br><br>
                          Kode verifikasi baru Anda adalah: <h2>$verification_code</h2><br>
                          Kode ini berlaku selama 1 jam.";

        $mail->send();
        $success = "Kode verifikasi baru telah dikirim ke email Anda!";
    } catch (Exception $e) {
        $error = "Gagal mengirim email. Error: " . $mail->ErrorInfo;
    }
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
            position: relative;
            z-index: 1;
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

        .step-indicator {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(25, 118, 210, 0.2);
            border: 2px solid rgba(66, 165, 245, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 700;
            font-size: 0.9rem;
        }

        .step.active .step-circle {
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            border-color: #42a5f5;
            color: #fff;
            box-shadow: 0 0 15px rgba(66, 165, 245, 0.4);
        }

        .step.completed .step-circle {
            background: rgba(34, 197, 94, 0.2);
            border-color: #22c55e;
            color: #22c55e;
        }

        .step span {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.75rem;
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
            font-size: 1.2rem;
            margin: 0 5px;
        }

        .icon-wrapper {
            width: 100px;
            height: 100px;
            background: rgba(66, 165, 245, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            border: 2px solid rgba(66, 165, 245, 0.3);
        }

        .icon-wrapper::before {
            content: '✉️';
            font-size: 3.5rem;
        }

        .logo-section h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
        }

        .logo-section .text-primary {
            background: linear-gradient(135deg, #42a5f5 0%, #1976d2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .info-box {
            background: rgba(66, 165, 245, 0.1);
            border: 1px solid rgba(66, 165, 245, 0.3);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 25px;
            text-align: center;
        }

        .info-box p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            margin: 0;
            line-height: 1.6;
        }

        .info-box strong {
            color: #42a5f5;
            font-weight: 600;
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

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-label {
            display: block;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            background: rgba(25, 118, 210, 0.05);
            border: 1px solid rgba(66, 165, 245, 0.2);
            border-radius: 12px;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 3px;
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
            padding: 14px;
            background: rgba(66, 165, 245, 0.1);
            border: 1px solid rgba(66, 165, 245, 0.3);
            border-radius: 12px;
            color: #42a5f5;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-resend:hover {
            background: rgba(66, 165, 245, 0.2);
            border-color: #42a5f5;
        }

        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: rgba(66, 165, 245, 0.2);
        }

        .divider span {
            background: rgba(13, 27, 42, 0.9);
            padding: 0 15px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            position: relative;
            z-index: 1;
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
            font-size: 0.95rem;
        }

        .links a:hover {
            color: #64b5f6;
            text-decoration: underline;
        }

        .timer-info {
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            margin-top: 15px;
        }

        .timer-info .timer {
            color: #fbbf24;
            font-weight: 600;
        }

        @media (max-width: 576px) {
            .verify-container {
                padding: 40px 25px;
            }

            .logo-section h1 {
                font-size: 1.6rem;
            }

            .icon-wrapper {
                width: 80px;
                height: 80px;
            }

            .icon-wrapper::before {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="logo-section">
            <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Arena FIT Logo">

            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step completed">
                    <div class="step-circle">✓</div>
                    <span>Data Diri</span>
                </div>
                <span class="step-arrow">→</span>
                <div class="step active">
                    <div class="step-circle">2</div>
                    <span>Verifikasi</span>
                </div>
                <span class="step-arrow">→</span>
                <div class="step">
                    <div class="step-circle">3</div>
                    <span>Pembayaran</span>
                </div>
            </div>

            <div class="icon-wrapper"></div>
            <h1>Verifikasi <span class="text-primary">Email</span></h1>
        </div>

        <div class="info-box">
            <p>
                Kami telah mengirimkan kode verifikasi 6 digit ke<br>
                <strong><?= htmlspecialchars($email); ?></strong>
            </p>
        </div>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <div class="alert alert-success"><?= $success; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Kode Verifikasi</label>
                <input 
                    name="verification_code" 
                    type="text" 
                    class="form-control" 
                    placeholder="000000"
                    required 
                    maxlength="6" 
                    pattern="[0-9]{6}" 
                    title="Masukkan 6 digit angka"
                    autocomplete="off"
                    inputmode="numeric"
                >
            </div>

            <button type="submit" name="verifybtn" class="btn-verify">
                Verifikasi & Lanjut ke Pembayaran
            </button>
        </form>

        <div class="timer-info">
            <p>Kode berlaku selama <span class="timer">1 jam</span></p>
        </div>

        <div class="divider"><span>atau</span></div>

        <form method="POST">
            <button type="submit" name="resendbtn" class="btn-resend">
                <span>🔄</span> Kirim Ulang Kode
            </button>
        </form>

        <div class="links">
            <p><a href="register.php">← Kembali ke Pendaftaran</a></p>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            const input = document.querySelector('input[name="verification_code"]');
            if (input) {
                input.focus();
            }
        });

        document.querySelector('input[name="verification_code"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>