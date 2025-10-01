<?php
ob_start();
session_start();
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

    // Cek username/email
    $query = $con->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $query->bind_param("ss", $username, $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Bandingkan password langsung (disarankan hashing pakai password_hash)
        if ($password === $user['password']) {
            // Simpan data ke sesi
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Arahkan sesuai role
            if ($user['role'] === 'admin') {
                header("Location: ../dashboard/index.php");
            } else {
                header("Location: ../../member/beranda/index.php");
            }
            exit;
        } else {
            $error = "Kata sandi salah! Pastikan kata sandi sesuai.";
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
    <title>Log in</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/fontawesome-free/css/all.min.css" />
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css" />
    <!-- Theme style -->
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/adminlte.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/custom-login.css" />
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <!-- /.login-logo -->
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="#">
                    <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Logo" class="img-fluid" style="max-height:60px;">
                </a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Sign in to start your session</p>

                <!-- tampilkan pesan error -->
                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger"><?= $error; ?></div>
                <?php endif; ?>

                <!-- perbaikan form -->
                <form action="" method="POST">
                    <div class="input-group mb-3">
                        <input name="username" type="text" class="form-control" placeholder="Email / Username" required />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input name="password" type="password" class="form-control" placeholder="Password" required />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <!-- Tombol login submit -->
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="loginbtn" class="btn btn-primary btn-block">Login</button>
                        </div>
                    </div>
                </form>

                <p class="mb-1 mt-3">
                    <a href="forgot-password.html">I forgot my password</a>
                </p>
                <p class="mb-0">
                    <a href="register.html" class="text-center">Register a new membership</a>
                </p>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.login-box -->

    <!-- jQuery -->
    <script src="../../../assets/assets_admin/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../../../assets/assets_admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../../../assets/assets_admin/dist/js/adminlte.min.js"></script>
    <script>
        // Efek loading pada tombol login
        $(document).ready(function() {
        $('form').on('submit', function() {
            $('button[name="loginbtn"]').addClass('btn-loading');
        });
        
        // Efek fokus pada input
        $('.form-control').on('focus', function() {
            $(this).parent().css('box-shadow', '0 0 0 0.2rem rgba(74, 108, 247, 0.15)');
        });
        
        $('.form-control').on('blur', function() {
            $(this).parent().css('box-shadow', 'none');
        });
        });
    </script>
</body>

</html>

<?php ob_end_flush(); ?>