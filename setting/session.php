<?php
ob_start();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk mencegah akses login.php kalau sudah login
if (!function_exists('blockLoginPageIfLoggedIn')) {
    function blockLoginPageIfLoggedIn()
    {
        if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
            header('Location: ../../../data/member/beranda/index.php'); // landing utama
            exit;
        }
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