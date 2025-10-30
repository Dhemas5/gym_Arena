<?php
ob_start();
session_start();
require "../../../setting/koneksi.php";

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
    $error = "Database connection failed.";
  } else {
    // Cek apakah email terdaftar di tbl_user (admin)
    $stmt = $con->prepare("SELECT * FROM tbl_user WHERE email = ?");
    if ($stmt) {
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result && $result->num_rows === 1) {
        // Generate token & expiry
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Update ke database tbl_user
        $update = $con->prepare("UPDATE tbl_user SET reset_token=?, reset_token_expiry=? WHERE email=?");
        if ($update) {
          $update->bind_param("sss", $token, $expiry, $email);
          $update->execute();

          // Buat link reset password admin
          $resetLink = "http://localhost/gym_arena/data/admin/login/reset-password.php?token=" . $token;

          // Kirim email via PHPMailer
          $mail = new PHPMailer(true);
          try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'valhidayat01@gmail.com';
            $mail->Password   = 'ecbnikaaznxaujbk';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Pengirim & penerima
            $mail->setFrom('valhidayat01@gmail.com', 'Gym Arena Admin');
            $mail->addAddress($email);

            // Konten email
            $mail->isHTML(true);
            $mail->Subject = 'Reset Password Admin - Gym Arena';
            $mail->Body    = "
                            <p>Hello Admin,</p>
                            <p>We received a password reset request for your admin account.</p>
                            <p>Please click the following link to reset your password:</p>
                            <p><a href='$resetLink'>$resetLink</a></p>
                            <p><b>This link is valid for 1 hour.</b></p>
                            <hr>
                            <p>If you did not request a password reset, please ignore this email. Your account remains secure.</p>
                        ";

            $mail->send();
            $success = "Password reset link has been sent to your email.";
          } catch (Exception $e) {
            $error = "Failed to send email. Error: " . $mail->ErrorInfo;
          }
        } else {
          $error = "Failed to update reset token.";
        }
      } else {
        $error = "Admin email not found!";
      }

      $stmt->close();
    } else {
      $error = "An error occurred in the query.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin - Forgot Password | Gym Arena</title>

  <!-- Google Font: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .login-container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      overflow: hidden;
      width: 100%;
      max-width: 420px;
    }

    .logo-section {
      background: linear-gradient(135deg, #0f2557 0%, #1a3a6b 100%);
      padding: 40px 20px;
      text-align: center;
    }

    .logo-section img {
      max-width: 80px;
      height: auto;
      margin-bottom: 10px;
    }

    .logo-section h3 {
      color: #ffd700;
      font-size: 16px;
      font-weight: 500;
      letter-spacing: 1px;
    }

    .form-section {
      padding: 40px 35px;
    }

    .form-section h2 {
      color: #333;
      font-size: 20px;
      font-weight: 600;
      text-align: center;
      margin-bottom: 10px;
    }

    .form-section p {
      color: #666;
      font-size: 14px;
      text-align: center;
      margin-bottom: 30px;
    }

    .alert {
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      text-align: center;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .input-group {
      position: relative;
      margin-bottom: 25px;
    }

    .input-group input {
      width: 100%;
      padding: 15px 45px 15px 15px;
      border: 1px solid #e0e0e0;
      border-radius: 10px;
      font-size: 14px;
      font-family: 'Poppins', sans-serif;
      transition: all 0.3s;
      background: #f8f9fa;
    }

    .input-group input:focus {
      outline: none;
      border-color: #667eea;
      background: white;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .input-group i {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #999;
      font-size: 16px;
    }

    .btn-login {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, #0f2557 0%, #1a3a6b 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      margin-bottom: 20px;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(15, 37, 87, 0.4);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    .forgot-link {
      text-align: center;
      color: #667eea;
      font-size: 14px;
      text-decoration: none;
      display: block;
      transition: color 0.3s;
    }

    .forgot-link:hover {
      color: #764ba2;
      text-decoration: underline;
    }

    @media (max-width: 480px) {
      .form-section {
        padding: 30px 25px;
      }
    }
  </style>
</head>

<body>
  <div class="login-container">
    <div class="logo-section">
      <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Logo Gym Arena">
      <h3>GYM CLUB</h3>
    </div>

    <div class="form-section">
      <h2>Reset Your Password</h2>
      <p>Enter your email to receive reset link</p>

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="input-group">
          <input type="email" name="email" placeholder="Email / Username" required>
          <i class="fas fa-envelope"></i>
        </div>

        <button type="submit" name="resetbtn" class="btn-login">Send Reset Link</button>
      </form>

      <a href="login.php" class="forgot-link">
        <i class="fas fa-arrow-left"></i> Back to Login
      </a>
    </div>
  </div>

  <script src="../../../assets/assets_admin/plugins/jquery/jquery.min.js"></script>
</body>

</html>
<?php ob_end_flush(); ?>