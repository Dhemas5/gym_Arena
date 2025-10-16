<?php
require "../../../setting/session.php";
checkSession("member"); // hanya member boleh masuk
?>
<?php include '../../../view/master_member/header.php'; ?>

<!-- konten.php -->
<div class="content-wrapper">
    <!-- Header Halaman -->
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Daftar Pengguna Gym</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Pengguna</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Konten utama -->
    <div class="content-wrapper">
        <section class="content pt-3">
            <div class="container-fluid">
                <div class="row justify-content-center">

                    <!-- Kartu Profil Utama -->
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="card card-primary card-outline text-center">
                            <div class="card-body box-profile">
                                <i class="fas fa-user fa-3x mb-2 text-primary"></i>

                                <h3 class="profile-username">Profile</h3>
                                <p class="text-muted">Member Aktif</p>
                                <a href="../../../data/member/profile/profile.php" class="btn btn-primary btn-block"><b>Lihat Profil</b></a>
                            </div>
                        </div>
                    </div>

                    <!-- Jadwal Kelas -->
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="card card-success card-outline text-center">
                            <div class="card-body box-profile">
                                <i class="fas fa-calendar-alt fa-3x mb-2 text-success"></i>
                                <h5 class="profile-username">Jadwal Kelas</h5>
                                <p class="text-muted">Lihat jadwal kelas gym Anda.</p>
                                <a href="jadwal.php" class="btn btn-success btn-block"><b>Lihat Jadwal</b></a>
                            </div>
                        </div>
                    </div>

                    <!-- Program Latihan -->
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="card card-danger card-outline text-center">
                            <div class="card-body box-profile">
                                <i class="fas fa-dumbbell fa-3x mb-2 text-danger"></i>
                                <h5 class="profile-username">Program Latihan</h5>
                                <p class="text-muted">Lihat dan ikuti program latihan Anda.</p>
                                <a href="program.php" class="btn btn-danger btn-block"><b>Lihat Program</b></a>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat -->
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="card card-warning card-outline text-center">
                            <div class="card-body box-profile">
                                <i class="fas fa-history fa-3x mb-2 text-warning"></i>
                                <h5 class="profile-username">Riwayat</h5>
                                <p class="text-muted">Lihat riwayat kelas dan booking Anda.</p>
                                <a href="riwayat.php" class="btn btn-warning btn-block text-white"><b>Lihat Riwayat</b></a>
                            </div>
                        </div>
                    </div>

                    <!-- Membership -->
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="card card-info card-outline text-center">
                            <div class="card-body box-profile">
                                <i class="fas fa-id-card fa-3x mb-2 text-info"></i>
                                <h5 class="profile-username">Membership</h5>
                                <p class="text-muted">Cek status dan perpanjangan membership Anda.</p>
                                <a href="membership.php" class="btn btn-info btn-block"><b>Cek Status</b></a>
                            </div>
                        </div>
                    </div>

                    <!-- Pengumuman -->
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="card card-secondary card-outline text-center">
                            <div class="card-body box-profile">
                                <i class="fas fa-bullhorn fa-3x mb-2 text-secondary"></i>
                                <h5 class="profile-username">Pengumuman</h5>
                                <p class="text-muted">Lihat promo dan berita terbaru dari gym.</p>
                                <a href="pengumuman.php" class="btn btn-secondary btn-block"><b>Lihat Pengumuman</b></a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>

</div>

<!-- Get In Touch Section End -->
<?php include '../../../view/master_member/footer.php'; ?>