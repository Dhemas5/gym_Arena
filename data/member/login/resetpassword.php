<?php
ob_start();
session_start();
require "../../../setting/koneksi.php";

$error = "";
$success = "";
$showForm = false;

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// cek token dari URL
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);

    // Debug: cek token di database
    $debug = $con->prepare("SELECT email, reset_token_expiry FROM tbl_member WHERE reset_token = ?");
    $debug->bind_param("s", $token);
    $debug->execute();
    $debugResult = $debug->get_result();
    
    if ($debugResult->num_rows === 0) {
        $error = "Token tidak ditemukan di database.";
    } else {
        $debugData = $debugResult->fetch_assoc();
        $expiry = $debugData['reset_token_expiry'];
        $now = date('Y-m-d H:i:s');
        
        // Cek apakah token sudah expired
        if (strtotime($expiry) < strtotime($now)) {
            $error = "Token sudah kadaluarsa. Silakan request reset password lagi.";
        } else {
            // Token valid
            $showForm = true;
            $user = $debugData;
        }
    }
}

// jika submit password baru
if (isset($_POST['resetpassword'])) {
    $token = trim($_POST['token']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if (empty($password) || empty($confirm)) {
        $error = "Password tidak boleh kosong!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $confirm) {
        $error = "Password dan konfirmasi tidak sama!";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $update = $con->prepare("UPDATE tbl_member SET password=?, reset_token=NULL, reset_token_expiry=NULL WHERE reset_token=?");
        $update->bind_param("ss", $hashed, $token);
        
        if ($update->execute() && $update->affected_rows > 0) {
            $success = "Password berhasil direset. Silakan login kembali.";
            $showForm = false;
        } else {
            $error = "Gagal update password atau token tidak valid.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset Password | Gym Arena</title>
  <link rel="stylesheet" href="../../../assets/assets_admin/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Logo" class="img-fluid" style="max-height:60px;">
    </div>
    <div class="card-body">

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <div class="text-center mt-3">
          <a href="forgotpassword.php" class="btn btn-secondary">Request Reset Lagi</a>
        </div>
      <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <div class="text-center mt-3">
          <a href="login.php" class="btn btn-primary btn-block">Kembali ke Login</a>
        </div>
      <?php endif; ?>

      <?php if ($showForm): ?>
        <p class="login-box-msg">Masukkan password baru Anda</p>
        <form method="POST">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
          <div class="input-group mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password Baru (min 6 karakter)" required>
            <div class="input-group-append">
              <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" name="confirm" class="form-control" placeholder="Konfirmasi Password" required>
            <div class="input-group-append">
              <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <button type="submit" name="resetpassword" class="btn btn-primary btn-block">Reset Password</button>
            </div>
          </div>
        </form>
      <?php endif; ?>

      <p class="mb-0 mt-3 text-center">
        <a href="login.php">Kembali ke Login</a>
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