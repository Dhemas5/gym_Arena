<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<?php
require "../../../setting/session.php";
require "../../../setting/koneksi.php";

$queryUser = mysqli_query($con, "SELECT * FROM users");
$jumlahUser = mysqli_num_rows($queryUser)
?>
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Data User</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">User</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow rounded-3 overflow-hidden">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">Data User</h3>
                    </div>

                    <div class="card-body bg-dark p-3">
                        <div class="table-responsive rounded">
                            <table id="tabelPelatih" class="table table-bordered table-hover text-white mb-0">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 20%;">Username</th>
                                        <th style="width: 15%;">Password</th>
                                        <th style="width: 20%;">Email</th>
                                        <th style="width: 20%;">Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($jumlahUser == 0) {
                                    ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Tidak ada data user</td>
                                        </tr>
                                        <?php
                                    } else {
                                        $jumlah = 1;
                                        while ($data = mysqli_fetch_array($queryUser)) {
                                        ?>
                                            <tr>
                                                <td><?php echo $jumlah; ?></td>
                                                <td><?php echo $data['username']; ?></td>
                                                <td><?php echo $data['password']; ?></td>
                                                <td><?php echo $data['email']; ?></td>
                                                <td><?php echo $data['role']; ?></td>
                                            </tr>
                                    <?php
                                            $jumlah++;
                                        }
                                    }
                                    ?>
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