<?php
ob_start();
session_start();
require "../../../setting/koneksi.php"; // pastikan variabel koneksi = $con

date_default_timezone_set('Asia/Jakarta');

// Tambah PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Pastikan autoload PHPMailer ada
require_once '../../../vendor/autoload.php';

$error = "";
$success = "";

if (isset($_POST['resetbtn'])) {
  $email = trim(htmlspecialchars($_POST['email']));

  // Cek apakah koneksi valid
  if (!$con) {
    $error = "Koneksi database gagal.";
  } else {
    // Cek apakah email terdaftar
    $stmt = $con->prepare("SELECT * FROM tbl_member WHERE email = ?");
    if ($stmt) {
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result && $result->num_rows === 1) {
        // Generate token & expiry
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Update ke database
        $update = $con->prepare("UPDATE tbl_member SET reset_token=?, reset_token_expiry=? WHERE email=?");
        if ($update) {
          $update->bind_param("sss", $token, $expiry, $email);
          $update->execute();

          // Buat link reset password
          $resetLink = "http://localhost/gym_arena/data/member/login/resetpassword.php?token=" . $token;

          // Kirim email via PHPMailer
          $mail = new PHPMailer(true);
          try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'valhidayat01@gmail.com'; // ganti dengan email kamu
            $mail->Password   = 'ecbnikaaznxaujbk';       // ganti dengan app password Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Pengirim & penerima
            $mail->setFrom('valhidayat01@gmail.com', 'Gym Arena');
            $mail->addAddress($email);

            // Konten email
            $mail->isHTML(true);
            $mail->Subject = 'Reset Password Gym Arena';
            $mail->Body    = "
                            <p>Halo,</p>
                            <p>Kami menerima permintaan reset password Anda.</p>
                            <p>Silakan klik link berikut untuk reset password Anda:</p>
                            <p><a href='$resetLink'>$resetLink</a></p>
                            <p><b>Link ini berlaku selama 1 jam.</b></p>
                            <hr>
                            <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
                        ";

            $mail->send();
            $success = "Link reset password sudah dikirim ke email Anda.";
          } catch (Exception $e) {
            $error = "Gagal mengirim email. Error: " . $mail->ErrorInfo;
          }
        } else {
          $error = "Gagal memperbarui token reset.";
        }
      } else {
        $error = "Email tidak ditemukan!";
      }

      $stmt->close();
    } else {
      $error = "Terjadi kesalahan pada query.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gym Arena | Forgot Password</title>

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
        <p class="login-box-msg">Lupa password Anda? Masukkan email untuk reset.</p>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
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

        <p class="mb-1 mt-3"><a href="login.php">Kembali ke Login</a></p>
        <p class="mb-0"><a href="register.php" class="text-center">Daftar Akun Baru</a></p>
      </div>
    </div>
  </div>

  <script src="../../../assets/assets_admin/plugins/jquery/jquery.min.js"></script>
  <script src="../../../assets/assets_admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../../../assets/assets_admin/dist/js/adminlte.min.js"></script>
</body>

</html>
<?php ob_end_flush(); ?>