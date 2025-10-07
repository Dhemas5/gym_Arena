<?php require "../../../setting/session.php";
checkSession("admin"); // hanya admin boleh masuk 
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Menu Member</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Member</li>
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
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Data Member</h3>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive rounded">
                            <table id="tabelPelatih" class="table table-bordered table-striped">
                                <thead class="bg-primary">
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