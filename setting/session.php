<?php
ob_start();

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
