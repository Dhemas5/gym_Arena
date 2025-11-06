<?php
ob_start();
session_start();
require "../../../setting/koneksi.php";

$error = "";
$success = "";
$showForm = false;

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// cek token dari URL
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);

    // Debug: cek token di database
    $debug = $con->prepare("SELECT email, reset_token_expiry FROM tbl_member WHERE reset_token = ?");
    $debug->bind_param("s", $token);
    $debug->execute();
    $debugResult = $debug->get_result();
    
    if ($debugResult->num_rows === 0) {
        $error = "Token tidak ditemukan di database.";
    } else {
        $debugData = $debugResult->fetch_assoc();
        $expiry = $debugData['reset_token_expiry'];
        $now = date('Y-m-d H:i:s');
        
        // Cek apakah token sudah expired
        if (strtotime($expiry) < strtotime($now)) {
            $error = "Token sudah kadaluarsa. Silakan request reset password lagi.";
        } else {
            // Token valid
            $showForm = true;
            $user = $debugData;
        }
    }
}

// jika submit password baru
if (isset($_POST['resetpassword'])) {
    $token = trim($_POST['token']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if (empty($password) || empty($confirm)) {
        $error = "Password tidak boleh kosong!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $confirm) {
        $error = "Password dan konfirmasi tidak sama!";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $update = $con->prepare("UPDATE tbl_member SET password=?, reset_token=NULL, reset_token_expiry=NULL WHERE reset_token=?");
        $update->bind_param("ss", $hashed, $token);
        
        if ($update->execute() && $update->affected_rows > 0) {
            $success = "Password berhasil direset. Silakan login kembali.";
            $showForm = false;
        } else {
            $error = "Gagal update password atau token tidak valid.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Arena FIT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px 40px;
            border: 1px solid rgba(66, 165, 245, 0.2);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
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

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            text-decoration: none;
            margin-bottom: 30px;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #42a5f5;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-container img {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            border: 2px solid rgba(66, 165, 245, 0.3);
            margin-bottom: 20px;
        }

        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }

        .text-primary {
            background: linear-gradient(135deg, #42a5f5 0%, #1976d2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.95rem;
            margin-bottom: 32px;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
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

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(66, 165, 245, 0.6);
            font-size: 1.1rem;
        }

        .form-input {
            width: 100%;
            padding: 14px 18px 14px 50px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(66, 165, 245, 0.2);
            border-radius: 12px;
            color: #fff;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-input:focus {
            border-color: #42a5f5;
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 3px rgba(66, 165, 245, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.3s;
        }

        .toggle-password:hover {
            color: #42a5f5;
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
            margin-top: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 118, 210, 0.4);
        }

        .btn-secondary {
            width: 100%;
            padding: 15px;
            background: rgba(71, 85, 105, 0.3);
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-secondary:hover {
            background: rgba(71, 85, 105, 0.5);
            border-color: rgba(148, 163, 184, 0.5);
        }

        .divider {
            text-align: center;
            margin: 24px 0;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.85rem;
        }

        .footer-links {
            text-align: center;
            margin-top: 24px;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .footer-link:hover {
            color: #42a5f5;
        }

        .success-container {
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(34, 197, 94, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: #22c55e;
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

        .password-strength {
            margin-top: 8px;
            font-size: 0.85rem;
        }

        .strength-bar {
            height: 4px;
            background: rgba(66, 165, 245, 0.2);
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .strength-bar-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
            border-radius: 2px;
        }

        .strength-weak { 
            background: #ef4444; 
            width: 33%; 
        }

        .strength-medium { 
            background: #f59e0b; 
            width: 66%; 
        }

        .strength-strong { 
            background: #22c55e; 
            width: 100%; 
        }

        .strength-text {
            color: rgba(255, 255, 255, 0.6);
            margin-top: 6px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 32px 24px;
            }

            .login-header h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="login.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>

        <div class="logo-container">
            <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Arena FIT Logo">
        </div>

        <div class="login-header">
            <h1>Reset <span class="text-primary">Password</span></h1>
            <p class="login-subtitle">Buat password baru untuk akun Arena FIT Anda</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <a href="forgotpassword.php" class="btn-secondary">Request Reset Lagi</a>
        <?php elseif ($success): ?>
            <div class="success-container">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
                <a href="login.php" class="btn-primary">Kembali ke Login</a>
            </div>
        <?php endif; ?>

        <?php if ($showForm): ?>
            <form method="POST" id="resetForm">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            class="form-input" 
                            placeholder="Masukkan password baru (min 6 karakter)" 
                            required
                            minlength="6"
                            oninput="checkPasswordStrength()">
                        <button type="button" class="toggle-password" onclick="togglePassword('password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="strengthIndicator" style="display: none;">
                        <div class="strength-bar">
                            <div class="strength-bar-fill" id="strengthBar"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input 
                            type="password" 
                            name="confirm" 
                            id="confirm"
                            class="form-input" 
                            placeholder="Ketik ulang password baru" 
                            required
                            oninput="checkPasswordMatch()">
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="strength-text" id="matchText" style="margin-top: 8px;"></div>
                </div>

                <button type="submit" name="resetpassword" class="btn-primary">
                    <i class="fas fa-key"></i> Reset Password
                </button>
            </form>
        <?php endif; ?>

        <div class="footer-links">
            <a href="login.php" class="footer-link">Kembali ke Login</a>
        </div>
    </div>

    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const indicator = document.getElementById('strengthIndicator');
            const bar = document.getElementById('strengthBar');
            const text = document.getElementById('strengthText');

            if (password.length === 0) {
                indicator.style.display = 'none';
                return;
            }

            indicator.style.display = 'block';

            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;

            bar.className = 'strength-bar-fill';
            
            if (strength <= 2) {
                bar.classList.add('strength-weak');
                text.textContent = 'Lemah - Tambahkan huruf besar, angka atau simbol';
                text.style.color = '#ef4444';
            } else if (strength <= 3) {
                bar.classList.add('strength-medium');
                text.textContent = 'Sedang - Password cukup kuat';
                text.style.color = '#f59e0b';
            } else {
                bar.classList.add('strength-strong');
                text.textContent = 'Kuat - Password sangat aman';
                text.style.color = '#22c55e';
            }

            checkPasswordMatch();
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm').value;
            const matchText = document.getElementById('matchText');

            if (confirm.length === 0) {
                matchText.textContent = '';
                return;
            }

            if (password === confirm) {
                matchText.textContent = '✓ Password cocok';
                matchText.style.color = '#22c55e';
            } else {
                matchText.textContent = '✗ Password tidak cocok';
                matchText.style.color = '#ef4444';
            }
        }
    </script>
</body>
</html>
<?php ob_end_flush(); ?>