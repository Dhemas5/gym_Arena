<?php
// Cek apakah user sudah login
$isLogin = isset($_SESSION['login']) && $_SESSION['login'] === true;

// Cek role untuk memastikan ini member, bukan admin
$isMember = isset($_SESSION['role']) && $_SESSION['role'] === 'member';

// Ambil nama file saat ini (tanpa path)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Offcanvas Menu Section Begin -->
<div class="offcanvas-menu-overlay"></div>
<div class="offcanvas-menu-wrapper">
    <div class="canvas-close">
        <i class="fa fa-close"></i>
    </div>
    <nav class="canvas-menu mobile-menu">
        <ul>
            <li class="<?= $current_page == 'index.php' ? 'active' : '' ?>"><a href="./index.php">Beranda</a></li>
            <li class="<?= $current_page == 'about-us.html' ? 'active' : '' ?>"><a href="./about-us.html">Tentang Kami</a></li>
            <li class="<?= $current_page == 'jadwal.php' ? 'active' : '' ?>"><a href="../../../data/member/jadwal/jadwal.php">Classes</a></li>
            <li class="<?= $current_page == 'services.html' ? 'active' : '' ?>"><a href="./services.html">Services</a></li>
            <li class="<?= $current_page == 'team.html' ? 'active' : '' ?>"><a href="./team.html">Our Team</a></li>
            <li>
                <a href="#">Pages</a>
                <ul class="dropdown">
                    <li><a href="./class-timetable.html">Classes timetable</a></li>
                    <li><a href="./gallery.html">Gallery</a></li>
                    <li><a href="./contact.html">Contact</a></li>
                </ul>
            </li>

            <?php if ($isLogin && $isMember): ?>
                <li class="<?= $current_page == 'profil.php' ? 'active' : '' ?>"><a href="./profil.php">Profil</a></li>
                <li><a href="../../../data/member/login/logout.php">Logout</a></li>
            <?php else: ?>
                <li class="<?= $current_page == 'login.php' ? 'active' : '' ?>"><a href="../../../data/member/login/login.php">Login</a></li>
                <li class="<?= $current_page == 'register.php' ? 'active' : '' ?>"><a href="./register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div id="mobile-menu-wrap"></div>
</div>
<!-- Offcanvas Menu Section End -->

<!-- Header Section Begin -->
<header class="header-section">
    <div class="container-fluid">
        <div class="row">
            <!-- Logo -->
            <div class="col-lg-3 text-center">
                <div class="logo">
                    <a href="./index.php">
                        <img src="../../../assets/assets_member/img/logo.png" alt="Logo" style="width: 70px; height: auto" />
                    </a>
                </div>
            </div>

            <!-- Menu -->
            <div class="col-lg-6">
                <nav class="nav-menu">
                    <ul>
                        <li class="<?= $current_page == 'index.php' ? 'active' : '' ?>"><a href="./index.php">Beranda</a></li>
                        <li class="<?= $current_page == 'about-us.html' ? 'active' : '' ?>"><a href="./about-us.html">Tentang Kami</a></li>
                        <li class="<?= $current_page == 'jadwal.php' ? 'active' : '' ?>"><a href="../../../data/member/jadwal/jadwal.php">Classes</a></li>
                        <li class="<?= $current_page == 'services.html' ? 'active' : '' ?>"><a href="./services.html">Services</a></li>
                        <li class="<?= $current_page == 'team.html' ? 'active' : '' ?>"><a href="./team.html">Our Team</a></li>
                        <li>
                            <a href="#">Pages</a>
                            <ul class="dropdown">
                                <li><a href="./class-timetable.html">Classes timetable</a></li>
                                <li><a href="./gallery.html">Gallery</a></li>
                                <li><a href="./contact.html">Contact</a></li>
                            </ul>
                        </li>

                        <?php if ($isLogin && $isMember): ?>
                            <li class="<?= $current_page == 'profil.php' ? 'active' : '' ?>"><a href="./profil.php">Profil</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>

            <!-- Button login/logout -->
            <div class="col-lg-3 text-right d-none d-lg-block">
                <?php if (!$isLogin || !$isMember): ?>
                    <a href="../../../data/member/login/login.php"
                        class="btn btn-primary btn-sm mr-2 <?= $current_page == 'login.php' ? 'active' : '' ?>">
                        <i class="fa fa-sign-in"></i> Login
                    </a>
                    <a href="./register.php"
                        class="btn btn-success btn-sm <?= $current_page == 'register.php' ? 'active' : '' ?>">
                        <i class="fa fa-user-plus"></i> Register
                    </a>
                <?php else: ?>
                    <a href="../../../data/member/login/logout.php"
                        class="btn btn-danger btn-sm <?= $current_page == 'logout.php' ? 'active' : '' ?>">
                        <i class="fa fa-sign-out"></i> Logout
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="canvas-open">
            <i class="fa fa-bars"></i>
        </div>
    </div>
</header>
<!-- Header End -->