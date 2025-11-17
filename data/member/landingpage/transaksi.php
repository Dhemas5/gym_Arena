<?php
session_start();
require "../../../setting/koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['user_type'] !== 'member') {
    header("Location: ../login/login.php");
    exit;
}

$id_member = $_SESSION['id_member'];
$nama_member = $_SESSION['nama'];

// Cek apakah ada parameter ID untuk detail transaksi
$show_detail = false;
$transaction_detail = null;

if (isset($_GET['id'])) {
    $id_transaksi = $_GET['id'];

    // Query untuk mengambil detail transaksi dari tbl_transaksi_online
    $query_detail = "SELECT 
                t.*,
                p.nama_paket,
                p.deskripsi,
                p.durasi_hari,
                m.nama as nama_member,
                m.email
              FROM tbl_transaksi_online t
              LEFT JOIN tbl_paket p ON t.id_paket = p.id_paket
              LEFT JOIN tbl_member m ON t.id_member = m.id_member
              WHERE t.id_transaksi = ? AND t.id_member = ?";

    $stmt_detail = $con->prepare($query_detail);
    $stmt_detail->bind_param("si", $id_transaksi, $id_member);
    $stmt_detail->execute();
    $result_detail = $stmt_detail->get_result();

    if ($result_detail->num_rows > 0) {
        $transaction_detail = $result_detail->fetch_assoc();
        $show_detail = true;
    }
    $stmt_detail->close();
}

// Query untuk mengambil semua transaksi member dari tbl_transaksi_online
$query = "SELECT t.*, p.nama_paket, p.durasi_hari
          FROM tbl_transaksi_online t
          LEFT JOIN tbl_paket p ON t.id_paket = p.id_paket
          WHERE t.id_member = ? 
          ORDER BY t.tgl_transaksi DESC";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $id_member);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $show_detail ? 'Detail Transaksi ' . $transaction_detail['id_transaksi'] : 'Riwayat Transaksi'; ?> - Arena FIT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%);
            min-height: 100vh;
            color: white;
        }

        .navbar {
            background: rgba(13, 27, 42, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(66, 165, 245, 0.2);
            padding: 1rem 0;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            font-weight: 700;
            text-decoration: none;
        }

        .brand-box {
            background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            color: white;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #42a5f5 !important;
        }

        .member-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .container-main {
            padding: 100px 20px 50px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
        }

        .transaction-card {
            background: rgba(13, 27, 42, 0.9);
            border: 1px solid rgba(66, 165, 245, 0.2);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .transaction-card:hover {
            transform: translateY(-3px);
            border-color: #42a5f5;
            box-shadow: 0 10px 30px rgba(66, 165, 245, 0.3);
        }

        .transaction-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(66, 165, 245, 0.2);
        }

        .transaction-id {
            font-size: 1.2rem;
            font-weight: 700;
            color: #42a5f5;
        }

        .status-badge {
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
        }

        .status-approved,
        .status-paid {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid #4caf50;
        }

        .status-rejected,
        .status-expired {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 1px solid #f44336;
        }

        .transaction-body {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: white;
            font-weight: 600;
            font-size: 1rem;
        }

        .transaction-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid rgba(66, 165, 245, 0.2);
        }

        .price-tag {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffc107;
        }

        .btn-detail {
            background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(66, 165, 245, 0.4);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-icon {
            font-size: 5rem;
            color: rgba(66, 165, 245, 0.3);
            margin-bottom: 20px;
        }

        .empty-text {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 30px;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(66, 165, 245, 0.4);
            color: white;
        }

        /* Detail Transaksi Styles */
        .detail-card {
            background: rgba(13, 27, 42, 0.95);
            border: 1px solid rgba(66, 165, 245, 0.3);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
            color: white;
            padding: 25px;
        }

        .card-header-custom h3 {
            margin: 0;
            font-weight: 600;
        }

        .status-large {
            display: inline-block;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 10px;
        }

        .status-large.pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
        }

        .status-large.approved,
        .status-large.paid {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid #4caf50;
        }

        .status-large.rejected,
        .status-large.expired {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 1px solid #f44336;
        }

        .card-body-custom {
            padding: 30px;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .section-title {
            color: #42a5f5;
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(66, 165, 245, 0.3);
        }

        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid rgba(66, 165, 245, 0.1);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row .info-label {
            width: 200px;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 500;
        }

        .info-row .info-value {
            flex: 1;
            color: white;
            font-weight: 600;
        }

        .bukti-pembayaran {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .bukti-pembayaran:hover {
            transform: scale(1.02);
        }

        .btn-back {
            background: rgba(108, 117, 125, 0.3);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px 25px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        .btn-back:hover {
            background: rgba(108, 117, 125, 0.5);
            border-color: #42a5f5;
            color: white;
        }

        .alert-info-custom {
            background: rgba(33, 150, 243, 0.1);
            border-left: 4px solid #2196F3;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.9);
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            border-left: 4px solid #4caf50;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.9);
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }

            .transaction-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .transaction-footer {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .info-row {
                flex-direction: column;
                gap: 5px;
            }

            .info-row .info-label {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="indexmemberr.php">
                <span class="brand-box">AF</span>
                <div>
                    <span style="font-size: 1.2rem;">Arena FIT</span>
                </div>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="indexmemberr.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="transaksi.php">Transaksi</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                </ul>

                <div class="member-info ms-3">
                    <div class="member-avatar">
                        <?php echo strtoupper(substr($nama_member, 0, 1)); ?>
                    </div>
                    <span class="welcome-text">
                        <span style="color: #42a5f5; font-weight: 700;"><?php echo htmlspecialchars($nama_member); ?></span>
                    </span>
                    <a href="../login/logout.php" class="btn btn-danger ms-2">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="container container-main">

        <!-- Success Message -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i>
                <?php
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i>
                <?php
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($show_detail && $transaction_detail): ?>
            <!-- DETAIL TRANSAKSI -->
            <a href="transaksi.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Riwayat Transaksi
            </a>

            <div class="detail-card">
                <div class="card-header-custom">
                    <h3><i class="fas fa-receipt"></i> Detail Transaksi <?php echo htmlspecialchars($transaction_detail['id_transaksi']); ?></h3>
                    <span class="status-large <?php echo $transaction_detail['status']; ?>">
                        <?php
                        switch ($transaction_detail['status']) {
                            case 'pending':
                                echo '<i class="fas fa-clock"></i> Menunggu Verifikasi';
                                break;
                            case 'approved':
                            case 'paid':
                                echo '<i class="fas fa-check-circle"></i> Pembayaran Disetujui';
                                break;
                            case 'rejected':
                            case 'expired':
                                echo '<i class="fas fa-times-circle"></i> Pembayaran Ditolak';
                                break;
                        }
                        ?>
                    </span>
                </div>

                <div class="card-body-custom">
                    <!-- Status Info -->
                    <?php if ($transaction_detail['status'] === 'pending'): ?>
                        <div class="alert-info-custom">
                            <i class="fas fa-info-circle"></i>
                            <strong>Pembayaran Anda sedang dalam proses verifikasi.</strong>
                            <p class="mb-0 mt-2">Tim kami akan memverifikasi pembayaran Anda dalam waktu 1x24 jam. Anda akan mendapatkan notifikasi setelah pembayaran diverifikasi.</p>
                        </div>
                    <?php elseif ($transaction_detail['status'] === 'rejected' || $transaction_detail['status'] === 'expired'): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Pembayaran Anda ditolak.</strong>
                            <p class="mb-0 mt-2">Silakan hubungi admin untuk informasi lebih lanjut atau lakukan pembayaran ulang dengan bukti yang valid.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Informasi Transaksi -->
                    <div class="info-section">
                        <h4 class="section-title"><i class="fas fa-file-invoice"></i> Informasi Transaksi</h4>

                        <div class="info-row">
                            <span class="info-label">ID Transaksi</span>
                            <span class="info-value"><?php echo htmlspecialchars($transaction_detail['id_transaksi']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Tanggal Transaksi</span>
                            <span class="info-value">
                                <?php
                                $tgl_transaksi = new DateTime($transaction_detail['tgl_transaksi']);
                                echo $tgl_transaksi->format('d F Y, H:i') . ' WIB';
                                ?>
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Nama Paket</span>
                            <span class="info-value"><?php echo htmlspecialchars($transaction_detail['nama_paket']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Deskripsi</span>
                            <span class="info-value"><?php echo htmlspecialchars($transaction_detail['deskripsi']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Durasi</span>
                            <span class="info-value"><?php echo htmlspecialchars($transaction_detail['durasi_hari']); ?> Hari</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Total Pembayaran</span>
                            <span class="info-value text-success">Rp <?php echo number_format($transaction_detail['total413'], 0, ',', '.'); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Status</span>
                            <span class="info-value">
                                <span class="badge <?php
                                                    echo ($transaction_detail['status'] === 'approved' || $transaction_detail['status'] === 'paid') ? 'bg-success' : (($transaction_detail['status'] === 'rejected' || $transaction_detail['status'] === 'expired') ? 'bg-danger' : 'bg-warning text-dark');
                                                    ?>">
                                    <?php echo strtoupper($transaction_detail['status']); ?>
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Informasi Member -->
                    <div class="info-section">
                        <h4 class="section-title"><i class="fas fa-user"></i> Informasi Member</h4>

                        <div class="info-row">
                            <span class="info-label">Nama</span>
                            <span class="info-value"><?php echo htmlspecialchars($transaction_detail['nama_member']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($transaction_detail['email']); ?></span>
                        </div>
                    </div>

                    <!-- Verifikasi Info -->
                    <?php if ($transaction_detail['verified_at']): ?>
                        <div class="info-section">
                            <h4 class="section-title"><i class="fas fa-check-double"></i> Informasi Verifikasi</h4>

                            <div class="info-row">
                                <span class="info-label">Diverifikasi Pada</span>
                                <span class="info-value">
                                    <?php
                                    $verified_date = new DateTime($transaction_detail['verified_at']);
                                    echo $verified_date->format('d F Y, H:i') . ' WIB';
                                    ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Bukti Pembayaran -->
                    <?php if ($transaction_detail['bukti_pembayaran']): ?>
                        <div class="info-section">
                            <h4 class="section-title"><i class="fas fa-image"></i> Bukti Pembayaran</h4>
                            <div class="text-center">
                                <a href="../../../uploads/bukti_pembayaran/<?php echo $transaction_detail['bukti_pembayaran']; ?>" target="_blank">
                                    <img src="../../../uploads/bukti_pembayaran/<?php echo $transaction_detail['bukti_pembayaran']; ?>"
                                        alt="Bukti Pembayaran"
                                        class="bukti-pembayaran"
                                        style="max-width: 500px;">
                                </a>
                                <p class="text-muted mt-3">
                                    <i class="fas fa-info-circle"></i> Klik gambar untuk melihat ukuran penuh
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <a href="transaksi.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-list"></i> Lihat Semua Transaksi
                        </a>
                        <a href="indexmemberr.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-home"></i> Kembali ke Home
                        </a>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- RIWAYAT TRANSAKSI -->
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-receipt"></i> Riwayat <span style="color: #42a5f5;">Transaksi</span>
                </h1>
                <p class="page-subtitle">Lihat semua transaksi dan status pembayaran Anda</p>
            </div>

            <!-- Transaction List -->
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="transaction-card">
                        <div class="transaction-header">
                            <div class="transaction-id">
                                <i class="fas fa-file-invoice"></i>
                                <?php echo htmlspecialchars($row['id_transaksi']); ?>
                            </div>
                            <span class="status-badge status-<?php echo $row['status']; ?>">
                                <?php
                                switch ($row['status']) {
                                    case 'pending':
                                        echo '<i class="fas fa-clock"></i> Pending';
                                        break;
                                    case 'approved':
                                    case 'paid':
                                        echo '<i class="fas fa-check-circle"></i> Approved';
                                        break;
                                    case 'rejected':
                                    case 'expired':
                                        echo '<i class="fas fa-times-circle"></i> Rejected';
                                        break;
                                }
                                ?>
                            </span>
                        </div>

                        <div class="transaction-body">
                            <div class="info-item">
                                <span class="info-label">Tanggal</span>
                                <span class="info-value">
                                    <?php
                                    $tgl = new DateTime($row['tgl_transaksi']);
                                    echo $tgl->format('d M Y');
                                    ?>
                                </span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Paket</span>
                                <span class="info-value"><?php echo htmlspecialchars($row['nama_paket']); ?></span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Durasi</span>
                                <span class="info-value"><?php echo htmlspecialchars($row['durasi_hari']); ?> Hari</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Status</span>
                                <span class="info-value text-capitalize"><?php echo htmlspecialchars($row['status']); ?></span>
                            </div>
                        </div>

                        <div class="transaction-footer">
                            <div class="price-tag">
                                <i class="fas fa-tag"></i> Rp <?php echo number_format($row['total413'], 0, ',', '.'); ?>
                            </div>
                            <a href="transaksi.php?id=<?php echo $row['id_transaksi']; ?>" class="btn-detail">
                                <i class="fas fa-eye"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div class="empty-text">
                        Belum ada transaksi
                    </div>
                    <a href="indexmemberr.php" class="btn-primary-custom">
                        <i class="fas fa-shopping-cart"></i> Mulai Belanja
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$stmt->close();
$con->close();
?>