<?php
ob_start();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk cek login & role
if (!function_exists('checkSession')) {
    function checkSession($required_role)
    {
        // Jika belum login
        if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
            session_destroy();
            header('Location: ../data/admin/login/login.php');
            exit;
        }

        // Kalau role tidak sesuai
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
            // Arahkan user sesuai role yang sedang aktif
            if ($_SESSION['role'] === 'admin') {
                header('Location: ../data/admin/dashboard/index.php');
            } elseif ($_SESSION['role'] === 'member') {
                header('Location: ../data/member/beranda/index.php');
            } else {
                // Kalau role tidak jelas, paksa logout
                session_destroy();
                header('Location: ../data/admin/login/login.php');
            }
            exit;
        }
    }
}
