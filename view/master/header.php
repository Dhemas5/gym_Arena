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
</head>

<body class="hold-transition light-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img src="../../../assets/assets_admin/dist/img/logo.jpg"
                class="rounded-circle elevation-2"
                alt="User Image"
                style="width: 50px; height: 50px; object-fit: cover;">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-light">
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
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-user"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">User Menu</span>
                        <div class="dropdown-divider"></div>
                        <a href="profil.php" class="dropdown-item">
                            <i class="fas fa-id-card mr-2"></i> Profil
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="setting.php" class="dropdown-item">
                            <i class="fas fa-cog mr-2"></i> Pengaturan
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="../login/logout.php" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                        <i class="fas fa-th-large"></i>
                    </a>
                </li>

            </ul>
        </nav>
        <!-- /.navbar -->