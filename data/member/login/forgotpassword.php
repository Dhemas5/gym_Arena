<?php
ob_start();
session_start();
require "../../../setting/koneksi.php";

date_default_timezone_set('Asia/Jakarta');

// Tambah PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/autoload.php'; // pastikan sudah install composer require phpmailer/phpmailer

$error = "";
$success = "";

if (isset($_POST['resetbtn'])) {
    $email = trim(htmlspecialchars($_POST['email']));

    $stmt = $con->prepare("SELECT * FROM tbl_member WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // generate token
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $con->prepare("UPDATE tbl_member SET reset_token=?, reset_token_expiry=? WHERE email=?");
        $update->bind_param("sss", $token, $expiry, $email);
        $update->execute();

        // link reset password
        $resetLink = "http://localhost/gym_arena/data/member/login/resetpassword.php?token=" . $token;

        // Kirim email via PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // atau SMTP hosting kamu
            $mail->SMTPAuth   = true;
            $mail->Username   = 'valhidayat01@gmail.com'; // ganti email kamu
            $mail->Password   = 'ecbnikaaznxaujbk';   // ganti dengan app password Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            // Pengirim & penerima
            $mail->setFrom('valhidayat01@gmail.com', 'Gym Arena');
            $mail->addAddress($email);

            // Konten
            $mail->isHTML(true);
            $mail->Subject = 'Reset Password Gym Arena';
            $mail->Body    = "Halo,<br><br>Kami menerima permintaan reset password.<br>
                              Silakan klik link berikut untuk reset password Anda:<br>
                              <a href='$resetLink'>$resetLink</a><br><br>
                              Link berlaku selama 1 jam.";

            $mail->send();
            $success = "Link reset password sudah dikirim ke email Anda.";
        } catch (Exception $e) {
            $error = "Gagal mengirim email. Error: " . $mail->ErrorInfo;
        }

    } else {
        $error = "Email tidak ditemukan!";
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

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php elseif ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="input-group mb-3">
          <input name="email" type="email" class="form-control" placeholder="Email" required>
          <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-envelope"></span></div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" name="resetbtn" class="btn btn-primary btn-block">Request Password</button>
          </div>
        </div>
      </form>

      <p class="mb-1 mt-3"><a href="login.php">Back to Login</a></p>
      <p class="mb-0"><a href="register.php" class="text-center">Register a new membership</a></p>
    </div>
  </div>
</div>
<script src="../../../assets/assets_admin/plugins/jquery/jquery.min.js"></script>
<script src="../../../assets/assets_admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../../assets/assets_admin/dist/js/adminlte.min.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>
