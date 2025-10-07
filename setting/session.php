<?php
// setting/session.php
// Safe session handler with function guards to avoid "Cannot redeclare" errors.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect berdasarkan role yang tersimpan di session
 */
if (!function_exists('redirectByRole')) {
    function redirectByRole()
    {
        if (!isset($_SESSION['role'])) {
            session_unset();
            session_destroy();
            header('Location: ../../index.php');
            exit;
        }

        if ($_SESSION['role'] === 'admin') {
            header('Location: ../../data/admin/dashboard/index.php');
        } else {
            header('Location: ../../data/member/beranda/index.php');
        }
        exit;
    }
}

/**
 * Cek session untuk akses halaman tertentu (admin/member)
 */
if (!function_exists('checkSession')) {
    function checkSession($required_role)
    {
        if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
            // Belum login -> arahkan ke login yang sesuai
            if ($required_role === 'admin') {
                header('Location: ../../data/admin/login/login.php');
            } else {
                header('Location: ../../data/member/login/login.php');
            }
            exit;
        }

        // Sudah login, tapi role tidak sesuai -> redirect sesuai role
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
            redirectByRole();
            exit;
        }
    }
}

/**
 * Block halaman login jika user sudah login
 * (panggil di halaman login admin/member)
 */
if (!function_exists('blockLoginPageIfLoggedIn')) {
    function blockLoginPageIfLoggedIn($role = null)
    {
        if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
            redirectByRole();
            exit;
        }
    }
}

/**
 * Logout universal
 */
if (!function_exists('logout')) {
    function logout()
    {
        session_unset();
        session_destroy();
        header('Location: ../../index.php');
        exit;
    }
}

/**
 * Auto logout jika idle (opsional)
 */
if (!function_exists('autoLogout')) {
    function autoLogout($timeout = 600)
    {
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
            session_unset();
            session_destroy();
            header("Location: ../../index.php?timeout=1");
            exit;
        }
        $_SESSION['LAST_ACTIVITY'] = time();
    }
}
