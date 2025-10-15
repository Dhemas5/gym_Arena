<?php
ob_start();
session_start();
require "../../../setting/session.php";
blockLoginPageIfLoggedIn('member'); // atau 'member'
require "../../../setting/koneksi.php";
require "../../../setting/session.php";
blockLoginPageIfLoggedIn(); // Kalau sudah login, tidak boleh buka login.php

// Cek koneksi database
if ($con->connect_error) {
    die("Koneksi gagal: " . $con->connect_error);
}

$error = "";

// Jika tombol login ditekan
if (isset($_POST['loginbtn'])) {
    $username = trim(htmlspecialchars($_POST['username']));
    $password = trim(htmlspecialchars($_POST['password']));
    $password_md5 = md5($password); // 🔐 hashing MD5

    // Cek username/email di tabel member
    $query = $con->prepare("SELECT * FROM tbl_member WHERE nama = ? OR email = ? LIMIT 1");
    $query->bind_param("ss", $username, $username);
    $query->execute();
    $result = $query->get_result();
    
    if ($user['is_verified'] == 0) {
    $error = "Email Anda belum diverifikasi! Silakan cek email Anda.";
} else {
    // Proses login normal
}

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Bandingkan password langsung (disarankan hashing pakai password_hash)
        if ($password === $user['password']) {
            // Simpan data ke sesi
            $_SESSION['login'] = true;
            $_SESSION['role'] = 'member';
            $_SESSION['id_member'] = $user['id_member'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email']; 
             $_SESSION['password'] = $user['password']; 
              $_SESSION['no_hp'] = $user['no_hp']; 

            exit;
        } else {
            $error = "Kata sandi salah! Pastikan sesuai.";
        }
    } else {
        $error = "Username atau email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Member</title>

    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/fontawesome-free/css/all.min.css" />
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css" />
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/adminlte.min.css" />
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Logo" class="img-fluid" style="max-height:60px;">
            </div>
            <div class="card-body">
                <p class="login-box-msg">Login Member</p>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger"><?= $error; ?></div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="input-group mb-3">
                        <input name="username" type="text" class="form-control" placeholder="Email / Username" required />
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-user"></span></div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input name="password" type="password" class="form-control" placeholder="Password" required />
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-lock"></span></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="loginbtn" class="btn btn-primary btn-block">Login</button>
                        </div>
                    </div>
                </form>

                <p class="mb-1 mt-3"><a href="forgotpassword.php">Lupa password?</a></p>
                <p class="mb-0"><a href="register.php" class="text-center">Daftar Member Baru</a></p>
            </div>
        </div>
    </div>

    <script src="../../../assets/assets_admin/plugins/jquery/jquery.min.js"></script>
    <script src="../../../assets/assets_admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../../assets/assets_admin/dist/js/adminlte.min.js"></script>
</body>

</html>

<?php ob_end_flush(); ?>
