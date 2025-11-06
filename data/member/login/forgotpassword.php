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
            $mail->setFrom('valhidayat01@gmail.com', 'Arena FIT');
            $mail->addAddress($email);

            // Konten email
            $mail->isHTML(true);
            $mail->Subject = 'Reset Password - Arena FIT';
            $mail->Body    = "
              <!DOCTYPE html>
              <html>
              <head>
                <style>
                  body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
                  .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 10px; overflow: hidden; }
                  .header { background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%); padding: 30px; text-align: center; }
                  .header h1 { color: #fff; margin: 0; }
                  .content { padding: 30px; }
                  .button { display: inline-block; padding: 12px 30px; background: #1976d2; color: #fff; text-decoration: none; border-radius: 8px; margin: 20px 0; }
                  .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; }
                </style>
              </head>
              <body>
                <div class='container'>
                  <div class='header'>
                    <h1>Reset Password</h1>
                  </div>
                  <div class='content'>
                    <p>Halo,</p>
                    <p>Kami menerima permintaan untuk mereset password akun Arena FIT Anda.</p>
                    <p>Klik tombol di bawah ini untuk membuat password baru:</p>
                    <p style='text-align: center;'>
                      <a href='$resetLink' class='button'>Reset Password</a>
                    </p>
                    <p>Atau salin link berikut ke browser Anda:</p>
                    <p style='word-break: break-all; color: #1976d2;'>$resetLink</p>
                    <p><strong>⚠️ Link ini berlaku selama 1 jam.</strong></p>
                    <hr style='margin: 20px 0; border: none; border-top: 1px solid #eee;'>
                    <p style='color: #666; font-size: 14px;'>Jika Anda tidak meminta reset password, abaikan email ini. Password Anda tidak akan berubah.</p>
                  </div>
                  <div class='footer'>
                    <p>&copy; 2025 Arena FIT. All rights reserved.</p>
                  </div>
                </div>
              </body>
              </html>
            ";

            $mail->send();
            $success = "Link reset password sudah dikirim ke email Anda. Silakan cek inbox atau folder spam.";
          } catch (Exception $e) {
            $error = "Gagal mengirim email. Error: " . $mail->ErrorInfo;
          }
        } else {
          $error = "Gagal memperbarui token reset.";
        }
      } else {
        $error = "Email tidak ditemukan! Pastikan email sudah terdaftar.";
      }

      $stmt->close();
    } else {
      $error = "Terjadi kesalahan pada query.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Arena FIT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px 40px;
            border: 1px solid rgba(66, 165, 245, 0.2);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            text-decoration: none;
            margin-bottom: 30px;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #42a5f5;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-container img {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            border: 2px solid rgba(66, 165, 245, 0.3);
            margin-bottom: 20px;
        }

        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }

        .text-primary {
            background: linear-gradient(135deg, #42a5f5 0%, #1976d2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.95rem;
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
            line-height: 1.5;
        }

        .alert i {
            margin-top: 2px;
            flex-shrink: 0;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(66, 165, 245, 0.6);
            font-size: 1.1rem;
        }

        .form-input {
            width: 100%;
            padding: 14px 18px 14px 50px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(66, 165, 245, 0.2);
            border-radius: 12px;
            color: #fff;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-input:focus {
            border-color: #42a5f5;
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 3px rgba(66, 165, 245, 0.1);
        }

        .btn-primary {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 118, 210, 0.4);
        }

        .divider {
            text-align: center;
            margin: 24px 0;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.85rem;
        }

        .footer-links {
            text-align: center;
            margin-top: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .footer-link:hover {
            color: #42a5f5;
        }

        .info-box {
            background: rgba(66, 165, 245, 0.1);
            border: 1px solid rgba(66, 165, 245, 0.2);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .info-box p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            line-height: 1.6;
            margin: 0;
        }

        .info-box i {
            color: #42a5f5;
            margin-right: 8px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 32px 24px;
            }

            .login-header h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="login.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Login
        </a>

        <div class="logo-container">
            <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Arena FIT Logo">
        </div>

        <div class="login-header">
            <h1>Lupa <span class="text-primary">Password?</span></h1>
            <p class="login-subtitle">Masukkan email Anda dan kami akan mengirimkan link untuk mereset password</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
            
            <div class="info-box">
                <p><i class="fas fa-info-circle"></i> Periksa folder <strong>Inbox</strong> atau <strong>Spam</strong> di email Anda</p>
            </div>
        <?php else: ?>
            <div class="info-box">
                <p><i class="fas fa-shield-alt"></i> Kami akan mengirimkan link reset password yang berlaku selama <strong>1 jam</strong></p>
            </div>

            <form method="POST" id="forgotForm">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input 
                            type="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="Masukkan email terdaftar Anda" 
                            required
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <button type="submit" name="resetbtn" class="btn-primary">
                    <i class="fas fa-paper-plane"></i>
                    Kirim Link Reset Password
                </button>
            </form>
        <?php endif; ?>

        <div class="divider">atau</div>

        <div class="footer-links">
            <a href="login.php" class="footer-link">
                <i class="fas fa-sign-in-alt"></i> Kembali ke Login
            </a>
            <a href="register.php" class="footer-link">
                <i class="fas fa-user-plus"></i> Belum punya akun? Daftar Member Baru
            </a>
        </div>
    </div>

    <script>
        // Auto focus ke input email saat halaman load
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.querySelector('input[name="email"]');
            if (emailInput) {
                emailInput.focus();
            }
        });

        // Form validation
        document.getElementById('forgotForm')?.addEventListener('submit', function(e) {
            const email = this.querySelector('input[name="email"]').value;
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Masukkan alamat email yang valid!');
                return false;
            }
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>