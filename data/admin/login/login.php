<?php
ob_start();
session_start();
require "../../../setting/koneksi.php";
require "../../../setting/session.php";
blockLoginPageIfLoggedIn(); // kalau sudah login, otomatis redirectByRole()


// Cek koneksi database
if ($con->connect_error) {
    die("Koneksi gagal: " . $con->connect_error);
}

$error = "";

// Jika tombol login ditekan
if (isset($_POST['loginbtn'])) {
    $username = trim(htmlspecialchars($_POST['username']));
    $password = trim(htmlspecialchars($_POST['password']));
    $password_md5 = md5($password); // hashing md5

    // Cek user dengan role admin
    $query = $con->prepare("SELECT * FROM tbl_user WHERE (username = ? OR email = ?) AND role = 'admin' LIMIT 1");
    $query->bind_param("ss", $username, $username);
    $query->execute();
    $result = $query->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Cek password
        if ($password_md5 === $user['password']) {
            // Simpan data ke sesi
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Arahkan ke dashboard admin
            header("Location: ../dashboard/index.php");
            exit;
        } else {
            $error = "Kata sandi salah! Pastikan kata sandi sesuai.";
        }
    } else {
        $error = "User admin tidak ditemukan!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>AdminLTE 3 | Log in (v2)</title>

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
        <!-- /.login-logo -->
        <div class="card card-outline card-primary">
            <div class="card-header text-center bg-dark">
                <a href="#">
                    <img src="../../../assets/assets_admin/dist/img/logoadmin.png" alt="Logo" class="img-fluid" style="max-height:100px;">
                </a>
            </div>
            <div class="card-body bg-dark">
                <p class="login-box-msg">Silahkan Login Terlebih Dahulu</p>

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