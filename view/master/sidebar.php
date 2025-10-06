<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Enhanced brand link with better styling -->
    <a href="../../../data/admin/dashboard/index.php" class="brand-link text-center">
        <img src="../../../assets/assets_admin/dist/img/logoadmin.png"
            alt="Gym Logo"
            class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">Arena Gym Fit Club</span>
    </a>

    <div class="sidebar">
        <!-- Enhanced user panel with status indicator -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image position-relative">
                <img src="../../../assets/assets_admin/dist/img/user2-160x160.jpg"
                    class="img-circle elevation-2"
                    alt="User Image"
                    style="width:40px; height:40px; object-fit:cover;">
                <span class="badge badge-success position-absolute" style="bottom: 0; right: 0; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #343a40;"></span>
            </div>
            <div class="info">
                <a href="#" class="d-block">
                    <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?>
                </a>
                <small class="text-muted">
                    <i class="fas fa-circle text-success" style="font-size: 8px;"></i> Online
                </small>
            </div>
        </div>

        <!-- Added search form in sidebar -->
        <div class="form-inline mb-3">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Cari menu..." aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Enhanced menu items with better icons and badges -->
                <li class="nav-item">
                    <a href="../../../data/admin/dashboard/index.php" class="nav-link <?php if ($current_page == 'index.php') {
                                                                                            echo 'active';
                                                                                        } ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-header">MASTER DATA</li>

                <li class="nav-item">
                    <a href="../../../data/admin/user/user.php" class="nav-link <?php if ($current_page == 'user.php') {
                                                                                    echo 'active';
                                                                                } ?>">
                        <i class="nav-icon fas fa-user-shield"></i>
                        <p>User</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="../../../data/admin/member/member.php" class="nav-link <?php if ($current_page == 'member.php') {
                                                                                        echo 'active';
                                                                                    } ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Member
                            <span class="badge badge-info right">New</span>
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="../../../data/admin/kategori/kategori.php" class="nav-link <?php if ($current_page == 'kategori.php') {
                                                                                            echo 'active';
                                                                                        } ?>">
                        <i class="nav-icon fas fa-layer-group"></i>
                        <p>Kategori</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="../../../data/admin/instruktur/instruktur.php" class="nav-link <?php if ($current_page == 'pelatih.php') {
                                                                                                echo 'active';
                                                                                            } ?>">
                        <i class="nav-icon fas fa-chalkboard-teacher"></i>
                        <p>Instruktur</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="../../../data/admin/jadwal/jadwal.php" class="nav-link <?php if ($current_page == 'jadwal.php') {
                                                                                        echo 'active';
                                                                                    } ?>">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Jadwal Kelas</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../../../data/admin/paket/paket.php" class="nav-link <?php if ($current_page == 'paket.php') {
                                                                                        echo 'active';
                                                                                    } ?>">
                        <i class="nav-icon fas fa-tags"></i>
                        <p>Paket</p>
                    </a>
                </li>

                <!-- Added Reports section -->
                <li class="nav-header">LAPORAN</li>
                
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>
                            Laporan
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Laporan Member</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Laporan Keuangan</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</aside>

<!-- Content Wrapper -->
<div class="content-wrapper">
