<?php
// Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi untuk cek login sesuai tipe user
 * - $type: 'admin', 'instruktur', atau 'member'
 * - Jika belum login → diarahkan ke halaman login
 */
if (!function_exists('checkSession')) {
    function checkSession($type)
    {
        // Jika belum login
        if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
            session_unset();
            session_destroy();
            header("Location: ../../../data/member/login/login.php");
            exit;
        }

        // Cek apakah login sesuai tipe
        if (!isset($_SESSION['user_type'])) {
            session_unset();
            session_destroy();
            header("Location: ../../../data/member/login/login.php");
            exit;
        }

        // Jika user_type tidak sesuai dengan yang diharapkan
        if ($_SESSION['user_type'] !== $type) {
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
            header("Location: ../../../data/member/landingpage/indexmemberr.php");
        } elseif ($type === 'instruktur') {
            header("Location: ../../../data/instruktur/dashboard/index.php");
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

/**
 * Fungsi untuk mendapatkan nama user berdasarkan tipe
 */
if (!function_exists('getUserDisplayName')) {
    function getUserDisplayName()
    {
        if (isset($_SESSION['user_type'])) {
            switch ($_SESSION['user_type']) {
                case 'admin':
                    return $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin';
                case 'member':
                    return $_SESSION['nama'] ?? 'Member';
                case 'instruktur':
                    return $_SESSION['nama_instruktur'] ?? 'Instruktur';
                default:
                    return 'User';
            }
        }
        return 'User';
    }
}

/**
 * Fungsi untuk logout
 */
if (!function_exists('logout')) {
    function logout()
    {
        session_unset();
        session_destroy();
        header("Location: ../data/member/login/login.php");
        exit;
    }
}
