<?php
ob_start();
session_start();
require "../../../setting/koneksi.php";

// Tambah PHPMailer di atas
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/autoload.php';

date_default_timezone_set('Asia/Jakarta');

// Cek apakah ada email yang perlu diverifikasi
if (!isset($_SESSION['verify_email'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['verify_email'];
$error = "";
$success = "";

// Proses verifikasi
if (isset($_POST['verifybtn'])) {
    $code = trim(htmlspecialchars($_POST['verification_code']));

    if (empty($code)) {
        $error = "Kode verifikasi harus diisi!";
    } else {
        // Cek kode verifikasi dan expiry
        $stmt = $con->prepare("SELECT * FROM tbl_member WHERE email = ? AND verification_code = ? AND code_expiry > NOW()");
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Update is_verified menjadi 1
            $update = $con->prepare("UPDATE tbl_member SET is_verified = 1, verification_code = NULL, code_expiry = NULL WHERE email = ?");
            $update->bind_param("s", $email);
            
            if ($update->execute()) {
                $success = "Email berhasil diverifikasi! Silakan login.";
                unset($_SESSION['verify_email']);
                header("refresh:2;url=login.php");
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi!";
            }
        } else {
            $error = "Kode verifikasi salah atau sudah kadaluarsa!";
        }
    }
}

// Resend kode verifikasi
if (isset($_POST['resendbtn'])) {
    // Generate kode baru
    $verification_code = sprintf("%06d", mt_rand(1, 999999));
    $code_expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
    
    $update = $con->prepare("UPDATE tbl_member SET verification_code = ?, code_expiry = ? WHERE email = ?");
    $update->bind_param("sss", $verification_code, $code_expiry, $email);
    $update->execute();

    // Kirim email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'valhidayat01@gmail.com';
        $mail->Password   = 'ecbnikaaznxaujbk';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('valhidayat01@gmail.com', 'Gym Arena');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Kode Verifikasi Registrasi Gym Arena';
        $mail->Body    = "Halo,<br><br>
                          Kode verifikasi baru Anda adalah: <h2>$verification_code</h2><br>
                          Kode ini berlaku selama 1 jam.";

        $mail->send();
        $success = "Kode verifikasi baru telah dikirim ke email Anda!";
    } catch (Exception $e) {
        $error = "Gagal mengirim email. Error: " . $mail->ErrorInfo;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>AdminLTE 3 | Verifikasi Email</title>

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
                <p class="login-box-msg">Masukkan kode verifikasi yang telah dikirim ke email <strong><?= htmlspecialchars($email); ?></strong></p>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php elseif ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-group mb-3">
                        <input name="verification_code" type="text" class="form-control" placeholder="Kode Verifikasi (6 digit)" 
                               required maxlength="6" pattern="[0-9]{6}" title="Masukkan 6 digit angka">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-key"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="verifybtn" class="btn btn-primary btn-block">Verifikasi</button>
                        </div>
                    </div>
                </form>

                <form method="POST" class="mt-3">
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="resendbtn" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-redo"></i> Kirim Ulang Kode
                            </button>
                        </div>
                    </div>
                </form>

                <p class="mb-0 mt-3">
                    <a href="login.php">Kembali ke Login</a>
                </p>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="../../../assets/assets_admin/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../../../assets/assets_admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../../../assets/assets_admin/dist/js/adminlte.min.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>