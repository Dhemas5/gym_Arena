<?php
ob_start();
session_start();
require "../../../setting/koneksi.php";
require "../../../setting/session.php";
blockLoginPageIfLoggedIn(); // Kalau sudah login, tidak boleh buka forgot-password.php

// Cek koneksi database
if ($con->connect_error) {
    die("Koneksi gagal: " . $con->connect_error);
}

$error = "";
$success = "";

// Jika tombol reset ditekan
if (isset($_POST['resetbtn'])) {
    $email = trim(htmlspecialchars($_POST['email']));

    // Validasi email
    if (empty($email)) {
        $error = "Email harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        // Cek apakah email terdaftar
        $query = $con->prepare("SELECT * FROM tbl_member WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Tampilkan password langsung (untuk development/testing)
            // CATATAN: Ini tidak aman untuk production!
            $success = "Password Anda adalah: <strong>" . $user['password'] . "</strong><br>Silakan login kembali.";
            
            // Atau bisa redirect ke halaman reset password dengan token
            // header("refresh:3;url=login.php");
        } else {
            $error = "Email tidak terdaftar dalam sistem!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>AdminLTE 3 | Forgot Password</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/fontawesome-free/css/all.min.css" />
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css" />
    <!-- Theme style -->
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/adminlte.min.css" />
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="#">
                    <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Logo" class="img-fluid" style="max-height:60px;">
                </a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">You forgot your password? Here you can retrieve your password.</p>

                <!-- tampilkan pesan error -->
                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger"><?= $error; ?></div>
                <?php endif; ?>

                <!-- tampilkan pesan sukses -->
                <?php if (!empty($success)) : ?>
                    <div class="alert alert-success"><?= $success; ?></div>
                <?php endif; ?>

                <!-- form forgot password -->
                <form action="" method="POST">
                    <div class="input-group mb-3">
                        <input name="email" type="email" class="form-control" placeholder="Email" required 
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <!-- Tombol reset submit -->
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="resetbtn" class="btn btn-primary btn-block">Request Password</button>
                        </div>
                    </div>
                </form>

                <p class="mt-3 mb-1">
                    <a href="login.php">Back to Login</a>
                </p>
                <p class="mb-0">
                    <a href="register.php" class="text-center">Register a new membership</a>
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
</body>

</html>

<?php ob_end_flush(); ?>