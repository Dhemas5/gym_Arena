<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link">
        <img src="../../../assets/assets_admin/dist/img/logoadmin.png"
            alt="AdminLTE Logo"
            class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">Arena Gym Fit Club</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="../../../assets/assets_admin/dist/img/user2-160x160.jpg"
                    class="img-circle elevation-2"
                    alt="User Image"
                    style="width:40px; height:40px; object-fit:cover;">
            </div>
            <div class="info">
                <a href="#" class="d-block">
                    <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?>
                </a>
            </div>
        </div>


        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column">
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
                        <p>Member</p>
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
            </ul>
        </nav>

    </div>
</aside>


<!-- Content Wrapper -->
<div class="content-wrapper">