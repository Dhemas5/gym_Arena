<?php
session_start();
$isLogin = isset($_SESSION['login']) && $_SESSION['login'] === true;
?>

<!-- Offcanvas Menu Section Begin -->
<div class="offcanvas-menu-overlay"></div>
<div class="offcanvas-menu-wrapper">
    <div class="canvas-close">
        <i class="fa fa-close"></i>
    </div>
    <nav class="canvas-menu mobile-menu">
        <ul>
            <li><a href="index.php">Beranda</a></li>
            <?php if ($isLogin): ?>
                <li><a href="profil.php">Profil</a></li>
                <li><a href="../../../data/admin/login/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="../../../data/admin/login/login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
        <ul>
            <li><a href="./index.html">Beranda</a></li>
            <li><a href="./about-us.html">Tentang Kami</a></li>
            <li><a href="./classes.html">Classes</a></li>
            <li><a href="./services.html">Services</a></li>
            <li><a href="./team.html">Our Team</a></li>
            <li>
                <a href="#">Pages</a>
                <ul class="dropdown">
                    <li><a href="./class-timetable.html">Classes timetable</a></li>
                    <li><a href="./gallery.html">Gallery</a></li>
                    <li><a href="./contact.html">Contact</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="mobile-menu-wrap"></div>
</div>
<!-- Offcanvas Menu Section End -->

<!-- Header Section Begin -->
<header class="header-section">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3" style="text-align: center">
                <div class="logo">
                    <a href="./index.html">
                        <img
                            src="../../../assets/assets_member/img/logo.png"
                            alt="Logo"
                            style="width: 70px; height: auto" />
                    </a>
                </div>
            </div>

            <div class="col-lg-6">
                <nav class="nav-menu">
                    <ul>
                        <li><a href="index.php">Beranda</a></li>
                        <?php if ($isLogin): ?>
                            <li><a href="profil.php">Profil</a></li>
                            <li><a href="../../../data/admin/login/logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="../../../data/admin/login/login.php">Login</a></li>
                            <li><a href="register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                    <ul>
                        <li class="active"><a href="./index.php">Beranda</a></li>
                        <?php if ($isLogin): ?>
                            <li><a href="./about-us.html">Tentang Kami</a></li>
                            <li><a href="./class-details.html">Classes</a></li>
                            <li><a href="./services.html">Services</a></li>
                            <li><a href="./team.html">Our Team</a></li>
                            <li>
                                <a href="#">Pages</a>
                                <ul class="dropdown">
                                    <li><a href="./class-timetable.html">Classes timetable</a></li>
                                    <li><a href="./gallery.html">Gallery</a></li>
                                    <li><a href="./contact.html">Contact</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>

            <div class="col-lg-3 text-right d-none d-lg-block">
                <?php if (!$isLogin): ?>
                    <a href="../../../data/admin/login/login.php" class="btn btn-primary btn-sm mr-2">
                        <i class="fa fa-sign-in"></i> Login
                    </a>
                    <a href="register.php" class="btn btn-success btn-sm">
                        <i class="fa fa-user-plus"></i> Register
                    </a>
                <?php else: ?>
                    <a href="../../../data/admin/login/logout.php" class="btn btn-danger btn-sm">
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