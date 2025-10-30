<?php
// Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi untuk cek login sesuai tipe user
 * - $type: 'admin' atau 'member'
 * - Jika belum login → diarahkan ke halaman login masing-masing
 */
if (!function_exists('checkSession')) {
    function checkSession($type)
    {
        // Jika belum login
        if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
            session_unset();
            session_destroy();

            if ($type === 'admin') {
                header("Location: ../../../data/admin/login/login.php");
            } elseif ($type === 'member') {
                header("Location: ../../../data/member/login/login.php");
            } else {
                header("Location: ../../../index.php");
            }
            exit;
        }

        // Cek apakah login sesuai tipe
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== $type) {
            redirectByType($_SESSION['user_type']);
            exit;
        }
    }
}

/**
 * Fungsi redirect otomatis berdasarkan tipe user
 */
if (!function_exists('redirectByType')) {
    function redirectByType($type)
    {
        if ($type === 'admin') {
            header("Location: ../../../data/admin/dashboard/index.php");
        } elseif ($type === 'member') {
            header("Location: ../../../data/member/beranda/index.php");
        } else {
            session_unset();
            session_destroy();
            header("Location: ../../../index.php");
        }
        exit;
    }
}

/**
 * Fungsi untuk mencegah halaman login diakses ulang oleh user yang sudah login
 */
if (!function_exists('blockLoginPageIfLoggedIn')) {
    function blockLoginPageIfLoggedIn()
    {
        if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
            redirectByType($_SESSION['user_type']);
            exit;
        }
    }
}
