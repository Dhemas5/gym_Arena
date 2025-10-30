<?php
ob_start();
session_start();
require "../../../setting/session.php";
blockLoginPageIfLoggedIn(); // Jika sudah login, tidak bisa akses halaman ini lagi
require "../../../setting/koneksi.php";

// Pastikan koneksi database aktif
if ($con->connect_error) {
    die("Koneksi gagal: " . $con->connect_error);
}

$error = "";

// Jika tombol login ditekan
if (isset($_POST['loginbtn'])) {
    $username = trim(htmlspecialchars($_POST['username']));
    $password = trim(htmlspecialchars($_POST['password']));
    $password_md5 = md5($password); // hashing md5 (disesuaikan dengan database)

    // Cek username/email di tabel admin
    $query = $con->prepare("SELECT * FROM tbl_user WHERE username = ? OR email = ? LIMIT 1");
    $query->bind_param("ss", $username, $username);
    $query->execute();
    $result = $query->get_result();

    if ($result && $result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        // Cek password
        if ($password_md5 === $admin['password']) {
            // Simpan data ke sesi
            $_SESSION['login'] = true;
            $_SESSION['user_type'] = 'admin';
            $_SESSION['id_admin'] = $admin['id_admin'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['nama'] = $admin['nama'];

            // Arahkan ke dashboard admin
            header("Location: ../dashboard/index.php");
            exit;
        } else {
            $error = "❌ Kata sandi salah! Pastikan benar.";
        }
    } else {
        $error = "⚠️ Username atau email admin tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Admin</title>

    <!-- Font & Style -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" />
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/fontawesome-free/css/all.min.css" />
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css" />
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/adminlte.min.css" />
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/custom-login.css" />
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <!-- Card Login -->
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="#">
                    <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Logo" class="img-fluid" style="max-height:60px;">
                </a>
            </div>

            <div class="card-body">
                <p class="login-box-msg">Login Admin Dashboard</p>

                <!-- Tampilkan pesan error jika ada -->
                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger text-center"><?= $error; ?></div>
                <?php endif; ?>

                <!-- Form login -->
                <form action="" method="POST">
                    <div class="input-group mb-3">
                        <input name="username" type="text" class="form-control" placeholder="Email / Username" required
                            value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
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

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="loginbtn" class="btn btn-primary btn-block">Login</button>
                        </div>
                    </div>
                </form>

                <p class="mt-3 text-center">
                    <a href="forgot-password.php" class="text-warning">Lupa password?</a>
                </p>
            </div>
        </div>
        <!-- /Card -->
    </div>

    <!-- Script -->
    <script src="../../../assets/assets_admin/plugins/jquery/jquery.min.js"></script>
    <script src="../../../assets/assets_admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../../assets/assets_admin/dist/js/adminlte.min.js"></script>

    <script>
        $(document).ready(function() {
            // Efek loading tombol
            $('form').on('submit', function() {
                $('button[name="loginbtn"]').addClass('btn-loading').text('Memproses...');
            });

            // Efek fokus input
            $('.form-control').on('focus', function() {
                $(this).parent().css('box-shadow', '0 0 0 0.2rem rgba(0, 123, 255, 0.25)');
            }).on('blur', function() {
                $(this).parent().css('box-shadow', 'none');
            });
        });
    </script>
</body>

</html>

<?php ob_end_flush(); ?>