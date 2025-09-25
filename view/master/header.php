<?php
require "../../../setting/session.php"; // Sesuaikan path ke session.php
checkSession('admin');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Arena | Fit Club</title>
    <link rel="icon" type="image/png" href="../../../assets/assets_admin/dist/img/logoadmin.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/adminlte.min.css">

    <!-- DataTables -->
    <link
        rel="stylesheet"
        href="../../../assets/assets_admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
    <link
        rel="stylesheet"
        href="../../../assets/assets_admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
    <link
        rel="stylesheet"
        href="../../../assets/assets_admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css" />

    <style>
        /* Warna dasar tabel */
        #tabelPelatih tbody tr {
            background-color: #343a40;
            /* abu gelap */
            transition: background-color 0.3s;
        }

        /* Efek hover */
        #tabelPelatih tbody tr:hover {
            background-color: #495057;
            /* sedikit lebih terang */
            cursor: pointer;
        }

        /* Saat baris diklik */
        #tabelPelatih tbody tr.active {
            background-color: #ffffff !important;
            color: #000000 !important;
        }

        /* Tambahkan sudut membulat di tabel */
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
        }
    </style>
</head>

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">

        <!-- Preloader -->
        <!-- <div class="preloader flex-column justify-content-center align-items-center">
            <img src="../assets_admin/dist/img/logoadmin.png"
                class="rounded-circle elevation-2"
                alt="User Image"
                style="width: 50px; height: 50px; object-fit: cover;">

        </div> -->

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-dark">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="dashboard.php" class="nav-link">Home</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->