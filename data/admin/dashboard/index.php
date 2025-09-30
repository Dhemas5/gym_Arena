<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<?php
require "../../../setting/koneksi.php";
require "../../../setting/session.php";

$queryKategori = mysqli_query($con, "SELECT *  FROM tbl_kategori");
$jumlahKategori = mysqli_num_rows($queryKategori);

$queryUser = mysqli_query($con, "SELECT *  FROM tbl_user");
$jumlahUser = mysqli_num_rows($queryUser);
?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Contoh info-box -->
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info elevation-1">
                        <i class="fas fa-tachometer-alt"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Dashboard</span>
                        <span class="info-box-number">-</span>
                    </div>
                </div>
            </div>

            <!-- User -->
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning elevation-1">
                        <i class="fas fa-user-shield"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">User</span>
                        <span class="info-box-number"><?php echo $jumlahUser ?></span>
                    </div>
                </div>
            </div>

            <!-- Member -->
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success elevation-1">
                        <i class="fas fa-users"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Member</span>
                        <span class="info-box-number"></span>
                    </div>
                </div>
            </div>

            <!-- Kategori -->
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-danger elevation-1">
                        <i class="fas fa-layer-group"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Kategori Gym</span>
                        <span class="info-box-number"><?php echo $jumlahKategori ?></span>
                    </div>
                </div>
            </div>

            <!-- Pelatih -->
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-primary elevation-1">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pelatih</span>
                        <span class="info-box-number"></span>
                    </div>
                </div>
            </div>

            <!-- Jadwal Kelas -->
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-secondary elevation-1">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Jadwal Kelas</span>
                        <span class="info-box-number"></span>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Data Pelatih</h3>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive rounded">
                            <table id="tabelPelatih" class="table table-bordered table-striped">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 20%;">Nama</th>
                                        <th>Bio</th>
                                        <th style="width: 15%;">No HP</th>
                                        <th style="width: 20%;">Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Andi</td>
                                        <td>Pelatih fitness</td>
                                        <td>08123456789</td>
                                        <td>andi@example.com</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Budi</td>
                                        <td>Pelatih yoga</td>
                                        <td>0822334455</td>
                                        <td>budi@example.com</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<?php include '../../../view/master/footer.php'; ?>