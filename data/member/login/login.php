<?php
ob_start();
session_start();
require"../../../setting/session.php";
require "../../../setting/koneksi.php";
blockLoginPageIfLoggedIn(); // Kalau sudah login, tidak boleh buka login.php

$error = "";

// Jika tombol login ditekan
if (isset($_POST['loginbtn'])) {
    $username = trim(htmlspecialchars($_POST['username']));
    $password = trim(htmlspecialchars($_POST['password']));

    // Cek username/email di tabel member
    $query = $con->prepare("SELECT * FROM tbl_member WHERE (nama = ? OR email = ?) LIMIT 1");
    $query->bind_param("ss", $username, $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Cek verifikasi email
        if ($user['is_verified'] == 0) {
            $error = "Email Anda belum diverifikasi! Silakan cek email Anda.";
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['user_type'] = 'member';
            $_SESSION['id_member'] = $user['id_member'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['no_hp'] = $user['no_hp'];

            header("Location: ../landingpage/indexmemberr.php");
            exit;
        } else {
            $error = "❌ Kata sandi salah!";
        }
    } else {
        $error = "⚠️ Username atau email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Member - Arena FIT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    
    <!-- Link ke CSS -->
    <link rel="stylesheet" href="../assets/css/stylemember.css">
    
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

        .login-container {
            background: rgba(13, 27, 42, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 50px 40px;
            width: 100%;
            max-width: 450px;
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

        .alert {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            padding: 14px 18px;
            color: #fca5a5;
            font-size: 0.9rem;
            margin-bottom: 25px;
            animation: shake 0.5s ease-in-out;
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

        .btn-login {
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
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 118, 210, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .divider {
            text-align: center;
            margin: 30px 0;
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

        .links p {
            color: rgba(255, 255, 255, 0.6);
            margin: 12px 0;
            font-size: 0.95rem;
        }

        .text-warning {
            color: #fbbf24 !important;
        }

        .text-warning:hover {
            color: #fcd34d !important;
        }

        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .btn-back-home:hover {
            color: #42a5f5;
            transform: translateX(-5px);
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 40px 25px;
            }

            .logo-section h1 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="../landingpage/index.php" class="btn-back-home">
            ← Kembali ke Beranda
        </a>

        <div class="logo-section">
            <img src="../../assets/assets_admin/dist/img/logo.jpg" alt="Arena FIT Logo">
            <h1>Login <span class="text-primary">Member</span></h1>
            <p>Masuk ke akun member Arena FIT Anda</p>
        </div>

        <?php if (!empty($error)) : ?>
            <div class="alert"><?= $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Username / Email</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input 
                        name="username" 
                        type="text" 
                        class="form-control" 
                        placeholder="Masukkan username atau email" 
                        required
                        value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input 
                        name="password" 
                        type="password" 
                        class="form-control" 
                        placeholder="Masukkan password" 
                        required
                    >
                </div>
            </div>

            <button type="submit" name="loginbtn" class="btn-login">
                Masuk
            </button>
        </form>

        <div class="links">
            <p><a href="forgotpassword.php" class="text-warning">Lupa password?</a></p>
            <div class="divider"><span>atau</span></div>
            <p>Belum punya akun? <a href="register.php">Daftar Member Baru</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>