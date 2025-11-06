<?php
ob_start();
session_start();
require "../../../setting/session.php";
blockLoginPageIfLoggedIn(); // Jika sudah login, tidak bisa akses halaman ini lagi
require "../../../setting/koneksi.php";

// Pastikan koneksi aktif
if ($con->connect_error) {
    die("Koneksi gagal: " . $con->connect_error);
}

$error = "";
$success = "";

// Jika tombol login ditekan
if (isset($_POST['loginbtn'])) {
    $username = trim(htmlspecialchars($_POST['username']));
    $password = trim(htmlspecialchars($_POST['password']));
    $password_md5 = md5($password); // sesuaikan dengan hash di database

    // Query untuk cari user
    $sql = "SELECT * FROM tbl_user WHERE username = ? OR email = ? LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Jika ditemukan
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Cek password
        if ($password_md5 === $user['password']) {
            // Simpan ke session
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

            $success = "Login berhasil! Selamat datang, " . htmlspecialchars($user['nama_lengkap']) . "!";
        } else {
            $error = "Kata sandi salah! Pastikan benar.";
        }
    } else {
        $error = "Username atau email tidak ditemukan!";
    }

    $stmt->close();
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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .login-box {
            transition: all 0.4s ease;
        }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <!-- Card Login -->
        <div class="card card-outline shadow">
            <div class="card-header text-center">
                <a href="#">
                    <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Logo" class="img-fluid"
                        style="max-height:60px;">
                </a>
            </div>

            <div class="card-body">
                <p class="login-box-msg">Login Admin Dashboard</p>

                <!-- Form login -->
                <form action="" method="POST" id="loginForm">
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
                            <button type="submit" name="loginbtn" class="btn btn-primary btn-block">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
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

    <!-- SweetAlert Handler -->
    <?php if (!empty($error)) : ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: '<?= $error; ?>',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Coba Lagi',
                didOpen: () => {
                    document.querySelector('.login-box').style.transform = 'translateY(0)';
                }
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($success)) : ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Login',
                text: '<?= $success; ?>',
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true,
                didOpen: () => {
                    document.querySelector('.login-box').style.transform = 'translateY(0)';
                },
                didClose: () => {
                    window.location.href = "../../../data/admin/dashboard/index.php";
                }
            });
        </script>
    <?php endif; ?>
</body>

</html>

<?php ob_end_flush(); ?>