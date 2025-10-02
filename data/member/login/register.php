<?php
ob_start();
session_start();
require "../../../setting/koneksi.php";
require "../../../setting/session.php";
blockLoginPageIfLoggedIn(); // Kalau sudah login, tidak boleh buka register.php

// Cek koneksi database
if ($con->connect_error) {
    die("Koneksi gagal: " . $con->connect_error);
}

$error = "";
$success = "";

// Jika tombol register ditekan
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
            // Insert data ke database
            // Note: Disarankan menggunakan password_hash() untuk keamanan
            $insert_query = $con->prepare("INSERT INTO tbl_member (nama, email, password, no_hp) VALUES (?, ?, ?, ?)");
            $insert_query->bind_param("ssss", $nama, $email, $password, $no_hp);

            if ($insert_query->execute()) {
                $success = "Registrasi berhasil! Silakan login.";
                // Redirect ke login setelah 2 detik
                header("refresh:2;url=login.php");
            } else {
                $error = "Terjadi kesalahan saat registrasi. Silakan coba lagi!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>AdminLTE 3 | Register</title>

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
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/custom-regis.css" />
</head>

<body class="hold-transition register-page">
    <div class="register-box register-container">
        <div class="card card-outline card-primary register-card">
            <div class="card-header text-center">
                <a href="#">
                    <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Logo" class="img-fluid" style="max-height:60px;">
                </a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Register a new membership</p>

                <!-- tampilkan pesan error -->
                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger"><?= $error; ?></div>
                <?php endif; ?>

                <!-- tampilkan pesan sukses -->
                <?php if (!empty($success)) : ?>
                    <div class="alert alert-success"><?= $success; ?></div>
                <?php endif; ?>

                <!-- form registrasi -->
                <form action="" method="POST">
                    <div class="input-group mb-3">
                        <input name="nama" type="text" class="form-control" placeholder="Username" required 
                               value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>" />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input name="email" type="email" class="form-control" placeholder="Email" required 
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input name="no_hp" type="text" class="form-control" placeholder="No. HP" required 
                               value="<?= isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>" />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-phone"></span>
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
                    <div class="input-group mb-3">
                        <input name="confirm_password" type="password" class="form-control" placeholder="Confirm Password" required />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <!-- Tombol register submit -->
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="registerbtn" class="btn btn-primary btn-block">Register</button>
                        </div>
                    </div>
                </form>

                <p class="mb-0 mt-3">
                    <a href="login.php" class="text-center">I already have a membership</a>
                </p>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.register-box -->

    <!-- jQuery -->
    <script src="../../../assets/assets_admin/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../../../assets/assets_admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../../../assets/assets_admin/dist/js/adminlte.min.js"></script>
</body>

</html>

<?php ob_end_flush(); ?>