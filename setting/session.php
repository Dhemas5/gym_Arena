<?php
// Pastikan session aktif
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi untuk cek login & role user
 * - $required_role: "admin" atau "member"
 * - Jika belum login → diarahkan ke index utama
 * - Jika role salah → diarahkan ke halaman sesuai rolenya
 */
if (!function_exists('checkSession')) {
    function checkSession($required_role)
    {
        // Jika belum login
        if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
            session_unset();
            session_destroy();
            header('Location: ../../../index.php'); // ke landing page utama
            exit;
        }

        // Jika role tidak sesuai, arahkan ke halaman role masing-masing
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
            redirectByRole($_SESSION['role']);
            exit;
        }
    }
}

/**
 * Fungsi untuk redirect otomatis sesuai role
 * - Admin diarahkan ke dashboard admin
 * - Member diarahkan ke beranda member
 */
if (!function_exists('redirectByRole')) {
    function redirectByRole($role)
    {
        if ($role === 'admin') {
            header('Location: ../../../data/admin/dashboard/index.php');
        } elseif ($role === 'member') {
            header('Location: ../../../data/member/beranda/index.php');
        } else {
            // Role tidak dikenali, logout paksa
            session_unset();
            session_destroy();
            header('Location: ../../../index.php');
        }
        exit;
    }
}

/**
 * Fungsi untuk mencegah halaman login diakses ulang
 * - Jika sudah login, diarahkan ke halaman sesuai role
 * - Misal admin sudah login → tidak bisa buka login member
 */
if (!function_exists('blockLoginPageIfLoggedIn')) {
    function blockLoginPageIfLoggedIn()
    {
        if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
            redirectByRole($_SESSION['role']);
            exit;
        }
    }
}
