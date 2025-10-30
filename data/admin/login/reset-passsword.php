<?php
ob_start();
session_start();
require "../../../setting/koneksi.php";

$error = "";
$success = "";
$token_valid = false;
$token = "";

// Cek token dari URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Validasi token
    $stmt = $con->prepare("SELECT * FROM tbl_user WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $token_valid = true;
    } else {
        $error = "Token tidak valid atau sudah kadaluarsa!";
    }
}

// Proses reset password
if (isset($_POST['reset'])) {
    $token = $_POST['token'];
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        $password_md5 = md5($password);

        // Update password dan hapus token
        $update = $con->prepare("UPDATE tbl_user SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
        $update->bind_param("ss", $password_md5, $token);

        if ($update->execute()) {
            $success = "Password berhasil direset! Silakan login dengan password baru.";
            $token_valid = false;
        } else {
            $error = "Gagal reset password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reset Password Admin | Gym Arena</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" />
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/fontawesome-free/css/all.min.css" />
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css" />
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/adminlte.min.css" />
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/custom-login.css" />
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
                <p class="login-box-msg"><i class="fas fa-lock mr-2"></i>Reset Password Admin</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-ban mr-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-check mr-2"></i><?= htmlspecialchars($success) ?>
                    </div>
                    <a href="login.php" class="btn btn-primary btn-block"><i class="fas fa-sign-in-alt mr-2"></i>Login Sekarang</a>
                <?php elseif ($token_valid): ?>
                    <form method="POST">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        <div class="input-group mb-3">
                            <input name="password" type="password" class="form-control" placeholder="Password Baru" required minlength="6">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-lock"></span></div>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <input name="confirm_password" type="password" class="form-control" placeholder="Konfirmasi Password" required minlength="6">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-lock"></span></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" name="reset" class="btn btn-primary btn-block">
                                    <i class="fas fa-key mr-2"></i>Reset Password
                                </button>
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="text-danger">Token tidak valid!</p>
                    <a href="forgot-password.php" class="btn btn-secondary btn-block">Minta Link Baru</a>
                <?php endif; ?>

                <p class="mb-1 mt-3">
                    <a href="login.php"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Login</a>
                </p>
            </div>
        </div>
    </div>

    <script src="../../../assets/assets_admin/plugins/jquery/jquery.min.js"></script>
    <script src="../../../assets/assets_admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../../assets/assets_admin/dist/js/adminlte.min.js"></script>
</body>

</html>
<?php ob_end_flush(); ?>