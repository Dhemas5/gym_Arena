<?php
// setting/session.php
// Safe session handler with function guards to avoid "Cannot redeclare" errors.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk cek login & role
if (!function_exists('checkSession')) {
    function checkSession($required_role)
    {
        // Jika belum login arahkan ke Landing Page utama
        if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
            session_unset();
            session_destroy();
            header('Location: ../../../data/member/beranda/index.php'); // landing page utama
            exit;
        }

        // Kalau role tidak sesuai
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
            if ($_SESSION['role'] === 'admin') {
                header('Location: ../../../data/admin/dashboard/index.php');
            } elseif ($_SESSION['role'] === 'member') {
                header('Location: ../../../data/member/beranda/index.php'); // tetap ke beranda member
            } else {
                session_unset();
                session_destroy();
                header('Location: ../../../data/member/beranda/index.php'); // fallback ke landing utama
            }
            exit;
        }
    }
}

/**
 * Fungsi untuk redirect user berdasarkan role
 */
if (!function_exists('redirectByRole')) {
    function redirectByRole()
    {
        if (!isset($_SESSION['role'])) {
            header('Location: ../../../data/member/beranda/index.php');
            exit;
        }

        if ($_SESSION['role'] === 'admin') {
            header('Location: ../../../data/admin/dashboard/index.php');
        } elseif ($_SESSION['role'] === 'member') {
            header('Location: ../../../data/member/beranda/index.php');
        } else {
            header('Location: ../../../data/member/beranda/index.php'); // fallback
        }
        exit;
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

// Fungsi untuk proteksi halaman yang butuh login
if (!function_exists('requireLogin')) {
    function requireLogin()
    {
        if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
            header('Location: ../../../data/autentikasi/login/login.php');
            exit;
        }
    }
}

// Fungsi checkSession untuk handle halaman register dan halaman yang butuh login
if (!function_exists('checkSession')) {
    function checkSession($userType = 'member')
    {
        // Deteksi halaman register atau halaman publik
        $current_page = basename($_SERVER['PHP_SELF']);
        $request_uri = $_SERVER['REQUEST_URI'];
        
        // List halaman publik yang tidak butuh login
        $public_pages = ['register', 'login', 'forgot'];
        
        $is_public = false;
        foreach ($public_pages as $page) {
            if (strpos($request_uri, $page) !== false) {
                $is_public = true;
                break;
            }
        }
        
        if ($is_public) {
            // Untuk halaman publik (register/login)
            // Jika sudah login, redirect ke dashboard
            if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
                header('Location: ../../../data/member/beranda/index.php');
                exit;
            }
            // Jika belum login, biarkan akses
            return;
        }
        
        // Untuk halaman yang butuh login (dashboard, profile, dll)
        if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
            header('Location: ../../../data/autentikasi/login/login.php');
            exit;
        }
    }
}
?>