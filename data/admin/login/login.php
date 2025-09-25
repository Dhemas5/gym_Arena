<?php
ob_start();
session_start();
require "../../../setting/koneksi.php";

// Cek koneksi database
if ($con->connect_error) {
    die("Koneksi gagal: " . $con->connect_error);
}

// Jika tombol login ditekan
if (isset($_POST['loginbtn'])) {
    $username = trim(htmlspecialchars($_POST['username']));
    $password = trim(htmlspecialchars($_POST['password']));

    // Cek username/email
    $query = $con->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $query->bind_param("ss", $username, $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Bandingkan kata sandi langsung (tanpa hashing)
        if ($password === $user['password']) {
            // Simpan data ke sesi
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Arahkan sesuai role
            if ($user['role'] === 'admin') {
                header("Location: index.php");
            } else {
                header("Location: ../../member/index.php");
            }
            exit;
        } else {
            $error = "Kata sandi salah! Pastikan kata sandi sesuai.";
        }
    } else {
        $error = "Username atau email tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login — GymFit</title>
    <link rel="stylesheet" href="../../../assets/assets_member/css/login.css" />
</head>

<body>
    <div class="login-card">
        <div class="logo">GF</div>
        <h1>Login Member</h1>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email / Username</label>
                <input
                    name="username"
                    type="text"
                    id="email"
                    placeholder="contoh@email.com"
                    required />
            </div>
            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <input
                    name="password"
                    type="password"
                    id="password"
                    placeholder="••••••••"
                    required />
            </div>
            <button type="submit" class="btn" name="loginbtn">Masuk</button>
        </form>
        <?php if (!empty($error)) : ?>
            <p style="color: red;"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <a href="lupapassword.html" class="link">Lupa Password?</a>
        <a href="register.html" class="link">Belum punya akun? Daftar</a>
    </div>
</body>

</html>
<?php ob_end_flush(); ?>