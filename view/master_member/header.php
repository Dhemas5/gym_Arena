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
    <title>AdminLTE 3 | Pengguna Gym</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/adminlte.min.css">

    <!-- 🔹 Tambahkan CSS Kustom -->
    <style>
        /* 🔷 Warna Navbar Gradient */
        .custom-navbar {
            background: linear-gradient(90deg, #004e92, #000428);
            border-bottom: 2px solid #0d6efd;
            padding: 0.6rem 1rem;
        }

        /* 🔷 Logo Bundar dengan background hitam */
        .brand-logo-wrapper {
            background-color: #000;
            border-radius: 50%;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo-wrapper img.brand-image {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* 🔷 Hover lembut untuk menu */
        .nav-hover {
            transition: all 0.3s ease;
        }

        .nav-hover:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            transform: translateY(-1px);
        }

        /* 🔷 Efek hover untuk ikon profil dan notifikasi */
        .navbar-nav .nav-link i {
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover i {
            color: #00bfff;
        }

        /* 🔷 Dropdown menu shadow */
        .dropdown-menu {
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }

        /* === Styling Umum untuk Tabel Jadwal === */
        .table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        /* Header Tabel */
        .table thead {
            background: linear-gradient(90deg, #001f3f, #003366);
            color: #fff;
            text-transform: uppercase;
            font-weight: 600;
        }

        .table thead th {
            border: none !important;
            padding: 14px;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        /* Isi Tabel */
        .table tbody td {
            padding: 12px;
            vertical-align: middle;
            border-color: #dee2e6;
            font-size: 14px;
        }

        /* Efek Hover Baris */
        .table tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }

        /* Sel kosong */
        .table tbody td.bg-light {
            background-color: #f3f4f6 !important;
        }

        /* Judul Kategori */
        .table td h6 {
            font-weight: 600;
            margin-bottom: 3px;
        }

        /* Instruktur */
        .table td small {
            font-style: italic;
            color: #6c757d;
        }

        /* Efek Filter Aktif */
        .nav-pills .nav-link.active {
            background: linear-gradient(90deg, #0069d9, #00b894);
            color: #fff !important;
            border-radius: 50px;
            font-weight: 600;
        }

        .nav-pills .nav-link {
            color: #0069d9;
            transition: all 0.3s;
        }

        .nav-pills .nav-link:hover {
            background-color: #e9ecef;
        }

        /* Card styling */
        .card {
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        /* Header Tabel - Override thead-dark */
        .table thead.thead-dark th {
            background: linear-gradient(90deg, #002b5b, #00509e) !important;
            color: #fff !important;
            border: none !important;
            text-transform: uppercase;
            font-weight: 600;
            padding: 14px;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        /* Isi Tabel */
        .table tbody td {
            padding: 12px;
            vertical-align: middle;
            border-color: #dee2e6;
            font-size: 14px;
        }

        /* Efek Hover Baris */
        .table tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }

        /* Sel kosong */
        .table tbody td.bg-light {
            background-color: #f3f4f6 !important;
        }

        /* Judul Kategori */
        .table td h6 {
            font-weight: 600;
            margin-bottom: 3px;
        }

        /* Instruktur */
        .table td small {
            font-style: italic;
            color: #6c757d;
        }

        /* Efek Filter Aktif */
        .nav-pills .nav-link.active {
            background: linear-gradient(90deg, #002b5b, #00509e);
            color: #fff !important;
            border-radius: 50px;
            font-weight: 600;
        }

        .nav-pills .nav-link {
            color: #0069d9;
            transition: all 0.3s;
        }

        .nav-pills .nav-link:hover {
            background-color: #e9ecef;
        }

        /* Card styling */
        .card {
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        body {
            background: #f4f7fb;
            font-family: 'Poppins', sans-serif;
            color: #333;
        }

        /* ====== Header Halaman ====== */
        .content-header h1 {
            font-weight: 700;
            color: #002b5b;
        }

        .breadcrumb a {
            color: #007bff;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* ====== Judul Utama ====== */
        h4.text-center {
            color: #003b88;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* ====== Kartu Instruktur ====== */
        .card {
            border-radius: 16px;
            background: #ffffff;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 20px rgba(0, 64, 128, 0.15);
        }

        /* ====== Header Ribbon ====== */
        .ribbon {
            background: linear-gradient(90deg, #0069d9, #00b894) !important;
            font-weight: 600;
            font-size: 13px;
            padding: 5px 12px;
            border-radius: 0 0 8px 0;
            color: #fff;
        }

        /* ====== Foto Instruktur ====== */
        .card-body img {
            border-radius: 12px;
            border: 3px solid #e0e7ff;
            background-color: #f8f9fa;
            transition: transform 0.3s;
        }

        .card-body img:hover {
            transform: scale(1.05);
        }

        /* ====== Nama ====== */
        .card-body h5 {
            font-size: 18px;
            color: #002b5b;
            font-weight: 700;
        }

        /* ====== Catatan ====== */
        .card-body p {
            font-size: 13px;
            color: #6c757d;
            background: #f9fafb;
            border-radius: 8px;
            padding: 6px 10px;
            display: inline-block;
        }

        /* ====== Tombol Sosial ====== */
        .card-body a {
            transition: color 0.3s ease, transform 0.2s ease;
        }

        .card-body a:hover {
            transform: scale(1.2);
        }

        .text-success:hover {
            color: #00b894 !important;
        }

        .text-primary:hover {
            color: #0069d9 !important;
        }

        /* ====== Grid Responsif ====== */
        @media (max-width: 768px) {
            .card-body img {
                width: 120px;
                height: 120px;
            }

            h4.text-center {
                font-size: 18px;
            }
        }
    </style>
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