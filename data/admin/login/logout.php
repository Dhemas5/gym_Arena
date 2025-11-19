<?php
// logout.php - Admin logout redirect to member login
session_start();

// Hapus semua data session
$_SESSION = array();

// Hapus session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke halaman login terpadu di member area
header("Location: http://localhost/gym_Arena/data/member/login/login.php");
exit;
?>