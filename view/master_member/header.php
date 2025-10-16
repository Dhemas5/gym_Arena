<!-- header.php (setelah penghapusan CSS) -->
<?php
require "../../../setting/session.php"; // Sesuaikan path ke session.php
checkSession('member');
?>
<!-- header.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Caca | Pengguna Gym</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/adminlte.min.css">

    <!-- 🔹 CSS Kustom yang dipisah -->
    <link rel="stylesheet" href="../../../assets/assets_member/css/custom-member.css">
</head>

<body class="hold-transition layout-top-nav">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand-md navbar-dark custom-navbar shadow-sm">
            <div class="container">

                <!-- 🔹 Logo dan Nama Brand -->
                <a href="../../../data/member/dashboard/index.php" class="navbar-brand d-flex align-items-center">
                    <div class="brand-logo-wrapper mr-2">
                        <img src="../../../assets/assets_admin/dist/img/logoadmin.png"
                            alt="Gym Logo"
                            class="brand-image">
                    </div>
                    <span class="brand-text font-weight-light text-white">Arena Gym Fit Club</span>
                </a>

                <!-- 🔹 Tombol Toggle Mobile -->
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
                    aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- 🔹 Menu Tengah -->
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav ml-3">
                        <li class="nav-item">
                            <a href="../../../data/member/beranda/index.php" class="nav-link text-white nav-hover">
                                <i class="fas fa-home mr-1"></i> Beranda
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../../data/member/jadwal/jadwal.php" class="nav-link text-white nav-hover">
                                <i class="fas fa-users mr-1"></i> Pengguna Gym
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../../data/member/instruktur/instruktur.php" class="nav-link text-white nav-hover">
                                <i class="fas fa-user-tie mr-1"></i> Instruktur
                            </a>
                        </li>
                    </ul>

                    <!-- 🔹 Search Bar -->
                    <form class="form-inline ml-auto d-none d-md-flex">
                        <div class="input-group input-group-sm">
                            <input class="form-control form-control-navbar" type="search" placeholder="Cari..." aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-navbar text-white" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- 🔹 Menu Kanan -->
                <ul class="navbar-nav ml-auto">
                    <!-- Notifikasi -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="fas fa-bell"></i>
                            <span class="badge badge-warning navbar-badge">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <span class="dropdown-header">3 Notifikasi Baru</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-dumbbell mr-2"></i> Jadwal baru tersedia
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-percent mr-2"></i> Promo membership baru!
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">Lihat semua notifikasi</a>
                        </div>
                    </li>

                    <!-- Profil -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="fas fa-user-circle"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="../../../data/member/profil/profil.php" class="dropdown-item">
                                <i class="fas fa-id-badge mr-2"></i> Profil Saya
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="../../../data/member/login/logout.php" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- /.navbar -->