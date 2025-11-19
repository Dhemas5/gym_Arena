<?php
ob_start();
session_start();
require "../../../setting/session.php";
require "../../../setting/koneksi.php";
blockLoginPageIfLoggedIn(); // Kalau sudah login, tidak boleh buka login.php

$error = "";

// Jika tombol login ditekan
if (isset($_POST['loginbtn'])) {
    $username = trim(htmlspecialchars($_POST['username']));
    $password = trim(htmlspecialchars($_POST['password']));

    // Coba login sebagai ADMIN terlebih dahulu
    $password_md5 = md5($password);
    $sql_admin = "SELECT * FROM tbl_user WHERE username = ? OR email = ? LIMIT 1";
    $stmt_admin = $con->prepare($sql_admin);
    $stmt_admin->bind_param("ss", $username, $username);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($result_admin && $result_admin->num_rows === 1) {
        $admin = $result_admin->fetch_assoc();
        
        // Cek password admin
        if ($password_md5 === $admin['password']) {
            // Login admin berhasil
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $admin['id_user'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['username'] = $admin['username'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['nama_lengkap'] = $admin['nama_lengkap'];

            header("Location: ../../../data/admin/dashboard/index.php");
            exit;
        }
    }
    $stmt_admin->close();

    // Jika bukan admin, coba login sebagai MEMBER
    $query_member = $con->prepare("SELECT * FROM tbl_member WHERE (nama = ? OR email = ?) LIMIT 1");
    $query_member->bind_param("ss", $username, $username);
    $query_member->execute();
    $result_member = $query_member->get_result();

    if ($result_member->num_rows === 1) {
        $member = $result_member->fetch_assoc();

        // Cek verifikasi email member
        if ($member['is_verified'] == 0) {
            $error = "Email Anda belum diverifikasi! Silakan cek email Anda.";
        } elseif (password_verify($password, $member['password'])) {
            // Login member berhasil
            $_SESSION['login'] = true;
            $_SESSION['user_type'] = 'member';
            $_SESSION['id_member'] = $member['id_member'];
            $_SESSION['nama'] = $member['nama'];
            $_SESSION['email'] = $member['email'];
            $_SESSION['no_hp'] = $member['no_hp'];

            header("Location: ../landingpage/indexmemberr.php");
            exit;
        } else {
            $error = "❌ Kata sandi salah!";
        }
    } else {
        $error = "⚠️ Username atau email tidak ditemukan!";
    }
    
    // Jika sampai di sini berarti login gagal untuk kedua tipe user
    if (empty($error)) {
        $error = "Username/email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Arena FIT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    
    <!-- Link ke CSS -->
    <link rel="stylesheet" href="../landingpage/assets/css/login_member.css">
    
    <!-- Font Awesome untuk ikon yang lebih modern -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .login-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .login-info h6 {
            margin-bottom: 10px;
            color: #007bff;
        }
        
        .login-info ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .login-info li {
            margin-bottom: 5px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="../../../data/member/landingpage" class="btn-back-home">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>

        <div class="logo-section">
            <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Arena FIT Logo">
            <h1>Login <span class="text-primary">Arena FIT</span></h1>
            <p>Masuk ke akun Anda</p>
        </div>

        <?php if (!empty($error)) : ?>
            <div class="alert">
                <i class="fas fa-exclamation-triangle"></i> <?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
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