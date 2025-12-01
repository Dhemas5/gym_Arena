<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Arena | Fit Club</title>
    <link rel="icon" type="image/png" href="../../../assets/assets_admin/dist/img/logoadmin.png">

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/fontawesome-free/css/all.min.css">

    <!-- Overlay Scrollbars -->
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">

    <!-- AdminLTE -->
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/adminlte.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/admin-styles.css">
    
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Pure CSS Notification Dropdown */
        .notif-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .notif-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #fff;
            min-width: 350px;
            max-width: 400px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            z-index: 9999;
            border-radius: 4px;
            margin-top: 8px;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .notif-dropdown:hover .notif-dropdown-content {
            display: block;
        }
        
        .notif-header {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            background-color: #f8f9fa;
            border-radius: 4px 4px 0 0;
            font-size: 0.95rem;
        }
        
        .notif-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f4f4f4;
            transition: background-color 0.3s;
            display: block;
            text-decoration: none;
            color: #333;
            border-left: 3px solid transparent;
        }
        
        .notif-item:hover {
            background-color: #f8f9fa;
            text-decoration: none;
            color: #333;
        }
        
        .notif-item.unread {
            background-color: #e3f2fd;
            border-left-color: #2196F3;
        }
        
        .notif-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            flex-shrink: 0;
            float: left;
        }
        
        .notif-text {
            font-size: 0.9rem;
            margin-bottom: 5px;
            line-height: 1.4;
            color: #333;
            font-weight: 500;
        }
        
        .notif-desc {
            font-size: 0.85rem;
            color: #6c757d;
            line-height: 1.3;
            margin-bottom: 5px;
        }
        
        .notif-time {
            font-size: 0.75rem;
            color: #6c757d;
            display: block;
            clear: both;
        }
        
        .notif-empty {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .notif-footer {
            padding: 10px 15px;
            text-align: center;
            border-top: 1px solid #dee2e6;
            background-color: #f8f9fa;
            border-radius: 0 0 4px 4px;
        }
        
        .notif-footer a {
            color: #007bff;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .notif-footer a:hover {
            text-decoration: underline;
        }
        
        .notif-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ffc107;
            color: #000;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 0.75rem;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
        }
    </style>
</head>

<body class="hold-transition light-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed <?= $body_class ?? '' ?>">
    <div class="wrapper">

<?php
// Query notifikasi langsung dari PHP
// $sql = "SELECT * FROM tbl_notifikasi ORDER BY id DESC LIMIT 10";

// if (is_object($con)) {
//     $queryNotif = $con->query($sql);
//     $jumlahNotif = $queryNotif ? $queryNotif->num_rows : 0;
// } else {
//     $queryNotif = mysqli_query($con, $sql);
//     $jumlahNotif = $queryNotif ? mysqli_num_rows($queryNotif) : 0;
// }

// Fungsi helper
if (!function_exists('getNotifIcon')) {
    function getNotifIcon($tipe) {
        $tipe = strtolower($tipe ?? '');
        if (strpos($tipe, 'member') !== false) return 'user-plus';
        if (strpos($tipe, 'transaksi') !== false) return 'money-bill-wave';
        return 'bell';
    }
}

if (!function_exists('getNotifColor')) {
    function getNotifColor($tipe) {
        $tipe = strtolower($tipe ?? '');
        if (strpos($tipe, 'member') !== false) return 'success';
        if (strpos($tipe, 'transaksi') !== false) return 'info';
        return 'secondary';
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        if (empty($datetime)) return 'Baru saja';
        $timestamp = strtotime($datetime);
        if (!$timestamp) return date('d M Y');
        $difference = time() - $timestamp;
        
        if ($difference < 60) return 'Baru saja';
        elseif ($difference < 3600) return floor($difference / 60) . ' menit lalu';
        elseif ($difference < 86400) return floor($difference / 3600) . ' jam lalu';
        elseif ($difference < 2592000) return floor($difference / 86400) . ' hari lalu';
        else return date('d M Y', $timestamp);
    }
}
?>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Toggle navigation">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="../../../data/admin/dashboard/index.php" class="nav-link">
                        <i class="fas fa-home mr-1"></i> Home
                    </a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Notifications - Pure CSS Dropdown -->
                <li class="nav-item">
                    <div class="notif-dropdown">
                        <a class="nav-link" href="#" style="position: relative;">
                            <i class="far fa-bell"></i>
                            <?php if ($jumlahNotif > 0): ?>
                                <span class="notif-badge"><?= $jumlahNotif ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <div class="notif-dropdown-content">
                            <div class="notif-header">
                                <?= $jumlahNotif ?> Notifikasi Baru
                            </div>
                            
                            <?php if ($queryNotif && $jumlahNotif > 0): ?>
                                <?php while ($notif = is_object($con) ? $queryNotif->fetch_assoc() : mysqli_fetch_assoc($queryNotif)): 
                                    $pesan = htmlspecialchars($notif['pesan'] ?? 'Notifikasi baru');
                                    $tipe = $notif['tipe'] ?? 'lainnya';
                                    $waktu = $notif['dibuat_pada'] ?? date('Y-m-d H:i:s');
                                ?>
                                    <a href="#" class="notif-item unread">
                                        <div class="notif-icon bg-<?= getNotifColor($tipe) ?>">
                                            <i class="fas fa-<?= getNotifIcon($tipe) ?> text-white"></i>
                                        </div>
                                        <div style="margin-left: 52px;">
                                            <div class="notif-text">
                                                <?= ucfirst(str_replace('_', ' ', $tipe)) ?>
                                            </div>
                                            <div class="notif-desc">
                                                <?= $pesan ?>
                                            </div>
                                            <span class="notif-time">
                                                <i class="far fa-clock"></i> <?= timeAgo($waktu) ?>
                                            </span>
                                        </div>
                                        <div style="clear: both;"></div>
                                    </a>
                                <?php endwhile; ?>
                                
                                <div class="notif-footer">
                                    <a href="#">Lihat Semua Notifikasi</a>
                                </div>
                            <?php else: ?>
                                <div class="notif-empty">
                                    <i class="fas fa-inbox" style="font-size: 2.5rem; opacity: 0.3;"></i>
                                    <p style="margin: 10px 0 0 0;">Tidak ada notifikasi</p>
                                    <small>Notifikasi akan muncul di sini</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button" aria-label="Fullscreen">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" aria-label="User menu">
                        <i class="far fa-user"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">User Menu</span>
                        <div class="dropdown-divider"></div>
                        <a href="../login/logout.php" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->