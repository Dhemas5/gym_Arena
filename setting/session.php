<?php
ob_start();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi cek session + role
 * $required_role = "admin" atau "member"
 */
if (!function_exists('checkSession')) {
    function checkSession($required_role)
    {
        // Jika belum login arahkan ke landing page utama
        if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
            session_unset();
            session_destroy();
            header('Location: ../../../index.php'); // landing page utama
            exit;
        }

        // Kalau role tidak sesuai
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
            redirectByRole(); // panggil fungsi redirect otomatis
            exit;
        }
    }
}

/**
 * Fungsi blokir halaman login jika sudah login
 * Jadi admin yg sudah login ga bisa buka login member
 * Begitu juga sebaliknya
 */
if (!function_exists('blockLoginPageIfLoggedIn')) {
    function blockLoginPageIfLoggedIn()
    {
        if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
            redirectByRole();
            exit;
        }
    }
}

/**
 * Fungsi redirect otomatis sesuai role
 */
if (!function_exists('redirectByRole')) {
    function redirectByRole()
    {
        if ($_SESSION['role'] === 'admin') {
            header('Location: ../../../data/admin/dashboard/index.php');
        } elseif ($_SESSION['role'] === 'member') {
            header('Location: ../../../data/member/beranda/index.php');
        } else {
            session_unset();
            session_destroy();
            header('Location: ../../../index.php'); // fallback
        }
        exit;
    }
}
