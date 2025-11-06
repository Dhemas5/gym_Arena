<?php
require "../../../setting/session.php";
checkSession("admin"); // hanya admin boleh masuk
?>
<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<?php
require "../../../setting/koneksi.php";
require "../../../setting/session.php";

// Ambil data user yang login
$username = $_SESSION['username'] ?? 'User';

// Jumlah kategori gym
$queryKategori = mysqli_query($con, "SELECT * FROM tbl_kategori");
$jumlahKategori = mysqli_num_rows($queryKategori);

// Jumlah user (admin + member)
$queryUser = mysqli_query($con, "SELECT * FROM tbl_user");
$jumlahUser = mysqli_num_rows($queryUser);

// Jumlah member
$queryMember = mysqli_query($con, "SELECT * FROM tbl_member");
$jumlahMember = mysqli_num_rows($queryMember);

// Jumlah pelatih
$queryPelatih = mysqli_query($con, "SELECT * FROM tbl_instruktur");
$jumlahPelatih = mysqli_num_rows($queryPelatih);

// Jumlah jadwal kelas
$queryJadwal = mysqli_query($con, "SELECT * FROM tbl_jadwal_kelas");
$jumlahJadwal = mysqli_num_rows($queryJadwal);

// notifikasi
$queryNotif = mysqli_query($con, "SELECT * FROM tbl_member WHERE status_notif = 'baru' ORDER BY tanggal_daftar DESC LIMIT 5");
$jumlahNotif = mysqli_num_rows($queryNotif);

// Data untuk grafik pendaftaran member per bulan
$dataBulan = [];
$dataJumlah = [];
$queryGrafik = mysqli_query($con, "
    SELECT MONTH(tanggal_daftar) AS bulan, COUNT(*) AS jumlah 
    FROM tbl_member 
    WHERE YEAR(tanggal_daftar) = YEAR(CURDATE())
    GROUP BY MONTH(tanggal_daftar)
");

$bulanNama = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];

while ($row = mysqli_fetch_assoc($queryGrafik)) {
    $dataBulan[] = $bulanNama[$row['bulan'] - 1];
    $dataJumlah[] = $row['jumlah'];
}

// Data untuk grafik Donut (status membership)
$queryStatus = mysqli_query($con, "SELECT status_membership, COUNT(*) AS total FROM tbl_member GROUP BY status_membership");
$statusLabels = [];
$statusData = [];
while ($r = mysqli_fetch_assoc($queryStatus)) {
    $statusLabels[] = $r['status_membership'];
    $statusData[] = $r['total'];
}
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
            <!-- Box Statistik -->
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

            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning elevation-1">
                        <i class="fas fa-user-shield"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">User</span>
                        <span class="info-box-number"><?php echo $jumlahUser; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success elevation-1">
                        <i class="fas fa-users"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Member</span>
                        <span class="info-box-number"><?php echo $jumlahMember; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-danger elevation-1">
                        <i class="fas fa-layer-group"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Kategori Gym</span>
                        <span class="info-box-number"><?php echo $jumlahKategori; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-primary elevation-1">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Instruktur</span>
                        <span class="info-box-number"><?php echo $jumlahPelatih; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-secondary elevation-1">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Jadwal Kelas</span>
                        <span class="info-box-number"><?php echo $jumlahJadwal; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Tabs (AdminLTE Style) -->
        <div class="card">          
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-chart-pie mr-1"></i>
              Statistik Member Tahun <?= date('Y') ?>
            </h3>
            <div class="card-tools">
              <ul class="nav nav-pills ml-auto">
                <li class="nav-item"><a class="nav-link active" href="#area-chart" data-toggle="tab">Pendaftaran</a></li>
                <li class="nav-item"><a class="nav-link" href="#donut-chart" data-toggle="tab">Status</a></li>
              </ul>
            </div>
          </div>
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="chart tab-pane active" id="area-chart" style="position: relative; height: 300px;">
                <canvas id="areaChart" height="300"></canvas>
              </div>
              <div class="chart tab-pane" id="donut-chart" style="position: relative; height: 300px;">
                <canvas id="donutChart" height="300"></canvas>
              </div>
            </div>
          </div>
        </div>

    </div>
</section>

<!-- ChartJS Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Grafik Area (Line)
  const ctxArea = document.getElementById('areaChart').getContext('2d');
  new Chart(ctxArea, {
    type: 'line',
    data: {
      labels: <?= json_encode($dataBulan) ?>,
      datasets: [{
        label: 'Jumlah Member',
        data: <?= json_encode($dataJumlah) ?>,
        borderColor: '#007bff',
        backgroundColor: 'rgba(0,123,255,0.25)',
        fill: true,
        tension: 0.3,
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });

  // Grafik Donut
  const ctxDonut = document.getElementById('donutChart').getContext('2d');
  new Chart(ctxDonut, {
    type: 'doughnut',
    data: {
      labels: <?= json_encode($statusLabels) ?>,
      datasets: [{
        data: <?= json_encode($statusData) ?>,
        backgroundColor: ['#28a745', '#ffc107', '#dc3545']
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom' } }
    }
  });
</script>

<?php include '../../../view/master/footer.php'; ?>
