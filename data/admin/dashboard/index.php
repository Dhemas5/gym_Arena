<?php
require "../../../setting/session.php";
checkSession("admin");

// PENTING: Load koneksi SEBELUM header!
require "../../../setting/koneksi.php";
?>

<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<?php
// Ambil data user yang login
$username = $_SESSION['username'] ?? 'User';

// Jumlah kategori gym
$queryKategori = mysqli_query($con, "SELECT * FROM tbl_kategori");
$jumlahKategori = mysqli_num_rows($queryKategori);

// Jumlah user (admin + staff)
$queryUser = mysqli_query($con, "SELECT * FROM tbl_user");
$jumlahUser = mysqli_num_rows($queryUser);

// Jumlah member
$queryMember = mysqli_query($con, "SELECT * FROM tbl_member");
$jumlahMember = mysqli_num_rows($queryMember);

// Jumlah member aktif (status_akun = 'aktif')
$queryMemberAktif = mysqli_query($con, "SELECT * FROM tbl_member WHERE status_akun = 'aktif'");
$jumlahMemberAktif = mysqli_num_rows($queryMemberAktif);

// Jumlah pelatih
$queryPelatih = mysqli_query($con, "SELECT * FROM tbl_instruktur");
$jumlahPelatih = mysqli_num_rows($queryPelatih);

// Jumlah jadwal kelas
$queryJadwal = mysqli_query($con, "SELECT * FROM tbl_jadwal_kelas");
$jumlahJadwal = mysqli_num_rows($queryJadwal);

// Jumlah member hari ini
$queryMemberHariIni = mysqli_query($con, "SELECT * FROM tbl_member WHERE DATE(tanggal_daftar) = CURDATE()");
$jumlahMemberHariIni = mysqli_num_rows($queryMemberHariIni);

// Jumlah transaksi online pending
$queryTransaksiPending = mysqli_query($con, "SELECT * FROM tbl_transaksi_online WHERE status = 'pending'");
$jumlahTransaksiPending = mysqli_num_rows($queryTransaksiPending);

// Data untuk grafik pendaftaran member per bulan (tahun ini)
$dataBulan = [];
$dataJumlah = [];
$bulanNama = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];

// Inisialisasi semua bulan dengan 0
for ($i = 1; $i <= 12; $i++) {
  $dataBulan[] = $bulanNama[$i - 1];
  $dataJumlah[] = 0;
}

// Query data aktual
$queryGrafik = mysqli_query($con, "
    SELECT MONTH(tanggal_daftar) AS bulan, COUNT(*) AS jumlah 
    FROM tbl_member 
    WHERE YEAR(tanggal_daftar) = YEAR(CURDATE())
    GROUP BY MONTH(tanggal_daftar)
    ORDER BY bulan ASC
");

// Update data yang ada
while ($row = mysqli_fetch_assoc($queryGrafik)) {
  $index = $row['bulan'] - 1;
  if (isset($dataJumlah[$index])) {
    $dataJumlah[$index] = $row['jumlah'];
  }
}

// Data untuk grafik Donut - status membership
$statusLabels = ['Aktif', 'Expired', 'Belum Aktif'];
$statusData = [0, 0, 0];
$statusColors = ['#28a745', '#dc3545', '#ffc107'];

$queryStatus = mysqli_query($con, "
    SELECT 
        CASE 
            WHEN membership_status = 'aktif' THEN 'Aktif'
            WHEN membership_status = 'expired' THEN 'Expired'
            WHEN membership_status = 'belum_aktif' THEN 'Belum Aktif'
            ELSE 'Belum Aktif'
        END as status_group,
        COUNT(*) as total
    FROM tbl_member 
    GROUP BY 
        CASE 
            WHEN membership_status = 'aktif' THEN 'Aktif'
            WHEN membership_status = 'expired' THEN 'Expired'
            WHEN membership_status = 'belum_aktif' THEN 'Belum Aktif'
            ELSE 'Belum Aktif'
        END
");

if ($queryStatus) {
  // Reset data
  $statusLabels = [];
  $statusData = [];
  $statusColors = [];

  while ($row = mysqli_fetch_assoc($queryStatus)) {
    $statusLabels[] = $row['status_group'];
    $statusData[] = $row['total'];

    // Tentukan warna
    if ($row['status_group'] == 'Aktif') {
      $statusColors[] = '#28a745';
    } elseif ($row['status_group'] == 'Expired') {
      $statusColors[] = '#dc3545';
    } else {
      $statusColors[] = '#ffc107';
    }
  }
}

// Ambil member terbaru (5 terakhir)
$queryMemberTerbaru = mysqli_query($con, "
    SELECT id_member, nama, email, tanggal_daftar, membership_status
    FROM tbl_member 
    ORDER BY tanggal_daftar DESC 
    LIMIT 5
");

// Ambil transaksi terbaru - PERBAIKAN DI SINI: ganti alias 'to' menjadi 't'
$queryTransaksiTerbaru = mysqli_query($con, "
    SELECT 
        t.id_transaksi,
        m.nama as nama_member,
        p.nama_paket,
        t.total,
        t.status,
        t.tgl_transaksi
    FROM tbl_transaksi_online t
    JOIN tbl_member m ON t.id_member = m.id_member
    LEFT JOIN tbl_paket p ON t.id_paket = p.id_paket
    ORDER BY t.tgl_transaksi DESC 
    LIMIT 5
");
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
      <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box bg-dark">
          <span class="info-box-icon bg-info elevation-1">
            <i class="fas fa-calendar-day"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Hari Ini</span>
            <span class="info-box-number"><?php echo date('d M Y'); ?></span>
          </div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box bg-dark">
          <span class="info-box-icon bg-success elevation-1">
            <i class="fas fa-users"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Total Member</span>
            <span class="info-box-number"><?php echo $jumlahMember; ?></span>
          </div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box bg-dark">
          <span class="info-box-icon bg-warning elevation-1">
            <i class="fas fa-shopping-cart"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Transaksi Pending</span>
            <span class="info-box-number"><?php echo $jumlahTransaksiPending; ?></span>
          </div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box bg-dark">
          <span class="info-box-icon bg-primary elevation-1">
            <i class="fas fa-chalkboard-teacher"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Instruktur</span>
            <span class="info-box-number"><?php echo $jumlahPelatih; ?></span>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Chart Section -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <i class="fas fa-chart-line mr-2"></i> Statistik Pendaftaran Member Tahun <?= date('Y') ?>
          </div>
          <div class="card-body" style="height: 350px;">
            <canvas id="areaChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Status Membership -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header bg-info text-white">
            <i class="fas fa-chart-pie mr-2"></i> Status Membership
          </div>
          <div class="card-body" style="height: 350px;">
            <canvas id="donutChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <!-- Member Terbaru -->
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-plus mr-2"></i> Member Terbaru</h3>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Tanggal Daftar</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($member = mysqli_fetch_assoc($queryMemberTerbaru)): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($member['nama']); ?></td>
                      <td><?php echo htmlspecialchars($member['email']); ?></td>
                      <td><?php echo date('d M Y', strtotime($member['tanggal_daftar'])); ?></td>
                      <td>
                        <?php if ($member['membership_status'] == 'aktif'): ?>
                          <span class="badge badge-success">Aktif</span>
                        <?php elseif ($member['membership_status'] == 'expired'): ?>
                          <span class="badge badge-danger">Expired</span>
                        <?php else: ?>
                          <span class="badge badge-warning">Belum Aktif</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                  <?php if (mysqli_num_rows($queryMemberTerbaru) == 0): ?>
                    <tr>
                      <td colspan="4" class="text-center">Tidak ada data member</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Transaksi Terbaru -->
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-exchange-alt mr-2"></i> Transaksi Terbaru</h3>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>ID Transaksi</th>
                    <th>Member</th>
                    <th>Paket</th>
                    <th>Total</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($transaksi = mysqli_fetch_assoc($queryTransaksiTerbaru)): ?>
                    <tr>
                      <td><?php echo substr($transaksi['id_transaksi'], 0, 10) . '...'; ?></td>
                      <td><?php echo htmlspecialchars($transaksi['nama_member']); ?></td>
                      <td><?php echo htmlspecialchars($transaksi['nama_paket']); ?></td>
                      <td>Rp <?php echo number_format($transaksi['total'], 0, ',', '.'); ?></td>
                      <td>
                        <?php if ($transaksi['status'] == 'approved'): ?>
                          <span class="badge badge-success">Disetujui</span>
                        <?php elseif ($transaksi['status'] == 'pending'): ?>
                          <span class="badge badge-warning">Pending</span>
                        <?php else: ?>
                          <span class="badge badge-danger">Ditolak</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                  <?php if (mysqli_num_rows($queryTransaksiTerbaru) == 0): ?>
                    <tr>
                      <td colspan="5" class="text-center">Tidak ada data transaksi</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- JavaScript untuk Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Data untuk area chart
    const bulanLabels = <?php echo json_encode($dataBulan); ?>;
    const jumlahData = <?php echo json_encode($dataJumlah); ?>;

    // Area Chart
    const areaCtx = document.getElementById('areaChart').getContext('2d');
    const areaChart = new Chart(areaCtx, {
      type: 'line',
      data: {
        labels: bulanLabels,
        datasets: [{
          label: 'Jumlah Pendaftaran',
          data: jumlahData,
          backgroundColor: 'rgba(40, 167, 69, 0.1)',
          borderColor: 'rgba(40, 167, 69, 1)',
          borderWidth: 2,
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });

    // Data untuk donut chart
    const statusLabels = <?php echo json_encode($statusLabels); ?>;
    const statusData = <?php echo json_encode($statusData); ?>;
    const statusColors = <?php echo json_encode($statusColors); ?>;

    // Donut Chart
    const donutCtx = document.getElementById('donutChart').getContext('2d');
    const donutChart = new Chart(donutCtx, {
      type: 'doughnut',
      data: {
        labels: statusLabels,
        datasets: [{
          data: statusData,
          backgroundColor: statusColors,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 20
            }
          }
        },
        cutout: '70%'
      }
    });
  });
</script>

<?php include '../../../view/master/footer.php'; ?>