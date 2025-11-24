<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";

// Set default tanggal
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$status_filter = $_GET['status'] ?? 'all';
$export = $_GET['export'] ?? '';

// Validasi status
$valid_statuses = ['all', 'pending', 'approved', 'rejected'];
$status_filter = in_array($status_filter, $valid_statuses) ? $status_filter : 'all';

// Build WHERE clause
$where_conditions = ["ton.tgl_transaksi BETWEEN '$start_date' AND '$end_date 23:59:59'"];
if ($status_filter !== 'all') {
    $where_conditions[] = "ton.status = '$status_filter'";
}
$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Query untuk statistik
$stats_sql = "SELECT 
    status,
    COUNT(*) as count,
    SUM(total) as total_amount
FROM tbl_transaksi_online ton
WHERE ton.tgl_transaksi BETWEEN '$start_date' AND '$end_date 23:59:59'
GROUP BY status";

$stats_result = $con->query($stats_sql);
$stats = [
    'pending' => ['count' => 0, 'total' => 0],
    'approved' => ['count' => 0, 'total' => 0],
    'rejected' => ['count' => 0, 'total' => 0],
    'all' => ['count' => 0, 'total' => 0]
];

while ($row = $stats_result->fetch_assoc()) {
    $stats[$row['status']] = [
        'count' => $row['count'],
        'total' => $row['total_amount']
    ];
    $stats['all']['count'] += $row['count'];
    $stats['all']['total'] += $row['total_amount'];
}

// Query data transaksi
$sql = "SELECT 
            ton.*, 
            m.nama AS nama_member, 
            m.email,
            m.no_hp,
            p.nama_paket, 
            p.durasi_hari,
            p.harga_umum,
            p.harga_mahasiswa,
            u.username AS admin_verifikator,
            DATE(ton.tgl_transaksi) as tgl_transaksi_only,
            DATE(ton.tgl_verifikasi) as tgl_verifikasi_only
        FROM tbl_transaksi_online ton
        JOIN tbl_member m ON ton.id_member = m.id_member
        JOIN tbl_paket p ON ton.id_paket = p.id_paket
        LEFT JOIN tbl_user u ON ton.admin_verifikasi = u.id_user
        $where_clause
        ORDER BY ton.tgl_transaksi DESC";

$transaksi = $con->query($sql);

// Export to Excel
if ($export === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="laporan_transaksi_online_' . date('Y-m-d') . '.xls"');

    echo "<table border='1'>";
    echo "<tr><th colspan='10' style='background:#f8f9fa; font-size:16px;'>LAPORAN TRANSAKSI ONLINE</th></tr>";
    echo "<tr><th colspan='10'>Periode: " . date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date)) . "</th></tr>";
    echo "<tr><th colspan='10'>Status: " . ($status_filter == 'all' ? 'Semua' : ucfirst($status_filter)) . "</th></tr>";
    echo "<tr style='background:#343a40; color:white;'>
            <th>NO</th>
            <th>ID TRANSAKSI</th>
            <th>MEMBER</th>
            <th>EMAIL</th>
            <th>TELEPON</th>
            <th>PAKET</th>
            <th>TOTAL</th>
            <th>TGL TRANSAKSI</th>
            <th>STATUS</th>
            <th>VERIFIKATOR</th>
          </tr>";

    $no = 1;
    $total_all = 0;
    while ($row = $transaksi->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . $row['id_transaksi'] . "</td>";
        echo "<td>" . $row['nama_member'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['no_hp'] . "</td>";
        echo "<td>" . $row['nama_paket'] . " (" . $row['durasi_hari'] . " hari)</td>";
        echo "<td>Rp " . number_format($row['total'], 0, ',', '.') . "</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($row['tgl_transaksi'])) . "</td>";
        echo "<td>" . strtoupper($row['status']) . "</td>";
        echo "<td>" . ($row['admin_verifikator'] ?: '-') . "</td>";
        echo "</tr>";
        $total_all += $row['total'];
    }

    echo "<tr style='background:#f8f9fa; font-weight:bold;'>";
    echo "<td colspan='6'>TOTAL</td>";
    echo "<td>Rp " . number_format($total_all, 0, ',', '.') . "</td>";
    echo "<td colspan='3'>" . ($no - 1) . " Transaksi</td>";
    echo "</tr>";
    echo "</table>";
    exit;
}

// Export to PDF
// if ($export === 'pdf') {
//     require_once '../../../lib/tcpdf/tcpdf.php';

//     $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//     $pdf->SetCreator('Sistem Membership');
//     $pdf->SetAuthor('Admin');
//     $pdf->SetTitle('Laporan Transaksi Online');
//     $pdf->SetHeaderData('', 0, 'LAPORAN TRANSAKSI ONLINE', 'Periode: ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)));

//     $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
//     $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
//     $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
//     $pdf->SetMargins(15, 25, 15);
//     $pdf->SetHeaderMargin(10);
//     $pdf->SetFooterMargin(10);
//     $pdf->SetAutoPageBreak(TRUE, 15);
//     $pdf->SetFont('helvetica', '', 9);
//     $pdf->AddPage();

//     $html = '<h3 style="text-align:center;">LAPORAN TRANSAKSI ONLINE</h3>';
//     $html .= '<p style="text-align:center;">Periode: ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)) . ' | Status: ' . ($status_filter == 'all' ? 'Semua' : ucfirst($status_filter)) . '</p>';

//     $html .= '<table border="1" cellpadding="4" style="border-collapse:collapse;">';
//     $html .= '<tr style="background-color:#343a40; color:white;">
//                 <th width="5%">NO</th>
//                 <th width="12%">ID TRANSAKSI</th>
//                 <th width="15%">MEMBER</th>
//                 <th width="15%">EMAIL</th>
//                 <th width="15%">PAKET</th>
//                 <th width="10%">TOTAL</th>
//                 <th width="12%">TGL TRANSAKSI</th>
//                 <th width="8%">STATUS</th>
//                 <th width="8%">VERIFIKATOR</th>
//               </tr>';

//     $no = 1;
//     $total_all = 0;
//     $transaksi_data = $con->query($sql); // Re-query untuk PDF
//     while ($row = $transaksi_data->fetch_assoc()) {
//         $html .= '<tr>';
//         $html .= '<td>' . $no++ . '</td>';
//         $html .= '<td>' . $row['id_transaksi'] . '</td>';
//         $html .= '<td>' . $row['nama_member'] . '</td>';
//         $html .= '<td>' . $row['email'] . '</td>';
//         $html .= '<td>' . $row['nama_paket'] . ' (' . $row['durasi_hari'] . ' hari)</td>';
//         $html .= '<td>Rp ' . number_format($row['total'], 0, ',', '.') . '</td>';
//         $html .= '<td>' . date('d/m/Y H:i', strtotime($row['tgl_transaksi'])) . '</td>';
//         $html .= '<td>' . strtoupper($row['status']) . '</td>';
//         $html .= '<td>' . ($row['admin_verifikator'] ?: '-') . '</td>';
//         $html .= '</tr>';
//         $total_all += $row['total'];
//     }

//     $html .= '<tr style="background-color:#f8f9fa; font-weight:bold;">';
//     $html .= '<td colspan="5">TOTAL</td>';
//     $html .= '<td>Rp ' . number_format($total_all, 0, ',', '.') . '</td>';
//     $html .= '<td colspan="3">' . ($no - 1) . ' Transaksi</td>';
//     $html .= '</tr>';
//     $html .= '</table>';

//     $pdf->writeHTML($html, true, false, true, false, '');
//     $pdf->Output('laporan_transaksi_online_' . date('Y-m-d') . '.pdf', 'D');
//     exit;
// }
?>

<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-chart-bar"></i> Laporan Transaksi Online</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Admin</a></li>
                    <li class="breadcrumb-item active">Laporan Transaksi</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Filter Card -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filter Laporan</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="form-horizontal">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tanggal Mulai:</label>
                                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tanggal Akhir:</label>
                                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status:</label>
                                <select name="status" class="form-control">
                                    <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Semua Status</option>
                                    <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="approved" <?= $status_filter == 'approved' ? 'selected' : '' ?>>Approved</option>
                                    <option value="rejected" <?= $status_filter == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group" style="margin-top: 32px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Tampilkan
                                </button>
                                <a href="?" class="btn btn-secondary">
                                    <i class="fas fa-sync"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistik Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="info-box bg-gradient-info">
                    <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Menunggu</span>
                        <span class="info-box-number"><?= $stats['pending']['count'] ?></span>
                        <span class="progress-description">
                            Rp <?= number_format($stats['pending']['total'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-gradient-success">
                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Disetujui</span>
                        <span class="info-box-number"><?= $stats['approved']['count'] ?></span>
                        <span class="progress-description">
                            Rp <?= number_format($stats['approved']['total'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-gradient-danger">
                    <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Ditolak</span>
                        <span class="info-box-number"><?= $stats['rejected']['count'] ?></span>
                        <span class="progress-description">
                            Rp <?= number_format($stats['rejected']['total'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-gradient-secondary">
                    <span class="info-box-icon"><i class="fas fa-list"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total</span>
                        <span class="info-box-number"><?= $stats['all']['count'] ?></span>
                        <span class="progress-description">
                            Rp <?= number_format($stats['all']['total'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="btn-group">
                    <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                    <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'pdf'])) ?>" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
                <div class="float-right">
                    <span class="badge badge-info badge-lg">
                        Periode: <?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?>
                    </span>
                    <span class="badge badge-secondary badge-lg ml-2">
                        Status: <?= $status_filter == 'all' ? 'Semua' : ucfirst($status_filter) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">
                    <i class="fas fa-table"></i>
                    Data Transaksi Online
                </h3>
                <div class="card-tools">
                    <span class="badge badge-light badge-lg"><?= $transaksi->num_rows ?> transaksi</span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if ($transaksi->num_rows == 0): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-5x text-muted mb-3"></i>
                        <h4 class="text-muted">Tidak ada data transaksi</h4>
                        <p class="text-muted">Tidak ditemukan transaksi pada periode yang dipilih</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tabelLaporan" style="font-size: 0.9rem;">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">NO</th>
                                    <th width="12%">ID TRANSAKSI</th>
                                    <th width="15%">MEMBER</th>
                                    <th width="15%">EMAIL</th>
                                    <th width="12%">TELEPON</th>
                                    <th width="13%">PAKET</th>
                                    <th width="8%">TOTAL</th>
                                    <th width="10%">TGL TRANSAKSI</th>
                                    <th width="8%">STATUS</th>
                                    <th width="12%">VERIFIKATOR</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $total_keseluruhan = 0;
                                while ($row = $transaksi->fetch_assoc()):
                                    $total_keseluruhan += $row['total'];
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <span class="badge bg-<?= $row['status'] == 'pending' ? 'warning' : ($row['status'] == 'approved' ? 'success' : 'danger') ?> text-dark">
                                                <?= htmlspecialchars($row['id_transaksi']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_member']) ?></strong>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                                        </td>
                                        <td>
                                            <small><?= $row['no_hp'] ?: '-' ?></small>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($row['nama_paket']) ?></strong>
                                                <small class="text-muted d-block">
                                                    <?= $row['durasi_hari'] ?> hari
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="text-success">Rp <?= number_format($row['total'], 0, ',', '.') ?></strong>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span><?= date('d/m/Y H:i', strtotime($row['tgl_transaksi'])) ?></span>
                                                <?php if ($row['tgl_verifikasi']): ?>
                                                    <small class="text-info">
                                                        Verif: <?= date('d/m/Y H:i', strtotime($row['tgl_verifikasi'])) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $row['status'] == 'pending' ? 'warning' : ($row['status'] == 'approved' ? 'success' : 'danger') ?>">
                                                <?= strtoupper($row['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($row['admin_verifikator']): ?>
                                                <span class="text-success fw-bold"><?= htmlspecialchars($row['admin_verifikator']) ?></span>
                                                <?php if ($row['tgl_verifikasi_only']): ?>
                                                    <small class="text-muted d-block">
                                                        <?= date('d/m/Y', strtotime($row['tgl_verifikasi_only'])) ?>
                                                    </small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot class="table-info">
                                <tr>
                                    <th colspan="6" class="text-right">TOTAL KESELURUHAN:</th>
                                    <th class="text-success">Rp <?= number_format($total_keseluruhan, 0, ',', '.') ?></th>
                                    <th colspan="3"><?= ($no - 1) ?> Transaksi</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Summary Card -->
        <?php if ($transaksi->num_rows > 0): ?>
            <div class="card mt-4">
                <div class="card-header bg-warning">
                    <h3 class="card-title"><i class="fas fa-chart-pie"></i> Ringkasan Laporan</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Statistik Berdasarkan Status:</h5>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Pending
                                    <span class="badge badge-warning badge-pill"><?= $stats['pending']['count'] ?> transaksi</span>
                                    <span class="text-muted">Rp <?= number_format($stats['pending']['total'], 0, ',', '.') ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Approved
                                    <span class="badge badge-success badge-pill"><?= $stats['approved']['count'] ?> transaksi</span>
                                    <span class="text-muted">Rp <?= number_format($stats['approved']['total'], 0, ',', '.') ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Rejected
                                    <span class="badge badge-danger badge-pill"><?= $stats['rejected']['count'] ?> transaksi</span>
                                    <span class="text-muted">Rp <?= number_format($stats['rejected']['total'], 0, ',', '.') ?></span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Informasi Periode:</h5>
                            <div class="alert alert-info">
                                <strong>Periode Laporan:</strong><br>
                                <?= date('d F Y', strtotime($start_date)) ?> - <?= date('d F Y', strtotime($end_date)) ?>
                                <hr>
                                <strong>Total Transaksi:</strong> <?= $stats['all']['count'] ?> transaksi<br>
                                <strong>Total Pendapatan:</strong> Rp <?= number_format($stats['all']['total'], 0, ',', '.') ?><br>
                                <strong>Rata-rata per Transaksi:</strong> Rp <?= number_format($stats['all']['count'] > 0 ? $stats['all']['total'] / $stats['all']['count'] : 0, 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../../view/master/footer.php'; ?>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tabelLaporan').DataTable({
            "pageLength": 25,
            "lengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "Semua"]
            ],
            "order": [
                [0, "asc"]
            ],
            "dom": 'Bfrtip',
            "buttons": [{
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Laporan Transaksi Online - <?= date('d/m/Y', strtotime($start_date)) ?> hingga <?= date('d/m/Y', strtotime($end_date)) ?>'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    title: 'Laporan Transaksi Online - <?= date('d/m/Y', strtotime($start_date)) ?> hingga <?= date('d/m/Y', strtotime($end_date)) ?>'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-info btn-sm',
                    title: 'Laporan Transaksi Online - <?= date('d/m/Y', strtotime($start_date)) ?> hingga <?= date('d/m/Y', strtotime($end_date)) ?>'
                }
            ],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            }
        });
    });
</script>