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

// Data untuk grafik pendaftaran member per bulan
$dataBulan = [];
$dataJumlah = [];
$queryGrafik = mysqli_query($con, "
    SELECT MONTH(tanggal_daftar) AS bulan, COUNT(*) AS jumlah 
    FROM tbl_member 
    WHERE YEAR(tanggal_daftar) = YEAR(CURDATE())
    GROUP BY MONTH(tanggal_daftar)
");

$bulanNama = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

while ($row = mysqli_fetch_assoc($queryGrafik)) {
  $dataBulan[] = $bulanNama[$row['bulan'] - 1];
  $dataJumlah[] = $row['jumlah'];
}

// Cek struktur tabel member untuk kolom yang tersedia
$queryCheckColumns = mysqli_query($con, "SHOW COLUMNS FROM tbl_member");
$availableColumns = [];
while ($col = mysqli_fetch_assoc($queryCheckColumns)) {
    $availableColumns[] = $col['Field'];
}

// Data untuk grafik Donut - menggunakan kolom yang tersedia
$statusLabels = [];
$statusData = [];
$statusColors = [];

// Pilih kolom yang mungkin ada untuk status
if (in_array('status', $availableColumns)) {
    $queryStatus = mysqli_query($con, "SELECT status, COUNT(*) AS total FROM tbl_member GROUP BY status");
} elseif (in_array('status_aktif', $availableColumns)) {
    $queryStatus = mysqli_query($con, "SELECT status_aktif, COUNT(*) AS total FROM tbl_member GROUP BY status_aktif");
} elseif (in_array('is_active', $availableColumns)) {
    $queryStatus = mysqli_query($con, "SELECT is_active, COUNT(*) AS total FROM tbl_member GROUP BY is_active");
} else {
    // Jika tidak ada kolom status, buat data dummy berdasarkan tanggal daftar
    $queryStatus = mysqli_query($con, "
        SELECT 
            CASE 
                WHEN tanggal_daftar >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 'Baru'
                ELSE 'Lama'
            END AS status_member,
            COUNT(*) AS total 
        FROM tbl_member 
        GROUP BY 
            CASE 
                WHEN tanggal_daftar >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 'Baru'
                ELSE 'Lama'
            END
    ");
}

if ($queryStatus) {
    while ($r = mysqli_fetch_assoc($queryStatus)) {
        $statusLabels[] = $r[array_keys($r)[0]]; // Ambil kolom pertama
        $statusData[] = $r['total'];
        
        // Tentukan warna berdasarkan status
        $statusValue = strtolower($r[array_keys($r)[0]]);
        if (strpos($statusValue, 'aktif') !== false || strpos($statusValue, 'active') !== false || $statusValue == 'baru') {
            $statusColors[] = '#28a745';
        } elseif (strpos($statusValue, 'nonaktif') !== false || strpos($statusValue, 'inactive') !== false || $statusValue == 'lama') {
            $statusColors[] = '#dc3545';
        } else {
            $statusColors[] = '#ffc107';
        }
    }
}

// Jika tidak ada data, beri nilai default
if (empty($statusLabels)) {
    $statusLabels = ['Aktif', 'Nonaktif'];
    $statusData = [$jumlahMember, 0];
    $statusColors = ['#28a745', '#dc3545'];
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
    <!-- Welcome Card -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card bg-gradient-primary">
          <div class="card-body">
            <div class="row">
              <div class="col-md-8">
                <h4 class="mb-1">Selamat Datang, <?php echo htmlspecialchars($username); ?>!</h4>
                <p class="mb-0">Ini adalah panel admin untuk mengelola sistem gym. Pantau statistik dan aktivitas terbaru di sini.</p>
              </div>
              <div class="col-md-4 text-right">
                <i class="fas fa-chart-line fa-3x opacity-50"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row">
      <!-- Box Statistik -->
      <div class="col-12 col-sm-6 col-md-4">
        <div class="info-box">
          <span class="info-box-icon bg-info elevation-1">
            <i class="fas fa-calendar-day"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Hari Ini</span>
            <span class="info-box-number"><?php echo date('d M Y'); ?></span>
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
    <div class="card mt-4">
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
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
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
        backgroundColor: <?= json_encode($statusColors) ?>
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  });
</script>

<?php include '../../../view/master/footer.php'; ?>