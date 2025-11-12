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
    <link rel="stylesheet" href="../landingpage/assets/css/login_member.css">
    
    <!-- Font Awesome untuk ikon yang lebih modern -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <a href="../../../landingpage/index.php" class="btn-back-home">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>

        <div class="logo-section">
            <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Arena FIT Logo">
            <h1>Login <span class="text-primary">Member</span></h1>
            <p>Masuk ke akun member Arena FIT Anda</p>
        </div>

        <?php if (!empty($error)) : ?>
            <div class="alert">
                <i class="fas fa-exclamation-triangle"></i> <?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Username / Email</label>
                <div class="input-wrapper">
                    <span class="input-icon"><i class="fas fa-user"></i></span>
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
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <input 
                        name="password" 
                        type="password" 
                        class="form-control" 
                        id="passwordInput"
                        placeholder="Masukkan password" 
                        required
                    >
                    <span class="password-toggle" id="passwordToggle">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <button type="submit" name="loginbtn" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>

        <div class="links">
            <p><a href="forgotpassword.php" class="text-warning"><i class="fas fa-key"></i> Lupa password?</a></p>
            <div class="divider"><span>atau</span></div>
            <p>Belum punya akun? <a href="register.php"><i class="fas fa-user-plus"></i> Daftar Member Baru</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Fungsi untuk toggle lihat password
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('passwordInput');
            const passwordToggle = document.getElementById('passwordToggle');
            const eyeIcon = passwordToggle.querySelector('i');
            
            passwordToggle.addEventListener('click', function() {
                // Toggle type password/text
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle icon
                if (type === 'text') {
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                    passwordToggle.classList.add('active');
                } else {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                    passwordToggle.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>

<?php ob_end_flush(); ?>