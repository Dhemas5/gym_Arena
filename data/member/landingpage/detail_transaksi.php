<?php
session_start();
require "../../../setting/koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['user_type'] !== 'member') {
    header("Location: ../login/login.php");
    exit;
}

// Cek parameter ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID transaksi tidak valid!";
    header("Location: transaksi.php");
    exit;
}

$id_transaksi = $_GET['id'];
$id_member = $_SESSION['id_member'];

// Query untuk mengambil detail transaksi
$query = "SELECT 
            t.*,
            p.id_payment,
            p.verified_at,
            p.verified_by,
            m.nama as nama_member,
            m.email
          FROM tbl_transaksi t
          LEFT JOIN tbl_payments p ON t.id_member = p.id_member 
              AND t.bukti_pembayaran = p.payment_proof
          LEFT JOIN tbl_member m ON t.id_member = m.id_member
          WHERE t.id_transaksi = ? AND t.id_member = ?";

$stmt = $con->prepare($query);
$stmt->bind_param("ii", $id_transaksi, $id_member);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Transaksi tidak ditemukan!";
    header("Location: transaksi.php");
    exit;
}

$transaction = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi #<?php echo str_pad($transaction['id_transaksi'], 6, '0', STR_PAD_LEFT); ?> - Arena FIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 50px;
        }
        
        .container-main {
            padding: 30px 0;
        }
        
        .detail-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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
            background: #fff3cd;
            color: #856404;
        }
        
        .status-large.approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-large.rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .card-body-custom {
            padding: 30px;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            color: #1e3c72;
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            width: 200px;
            color: #6c757d;
            font-weight: 500;
        }
        
        .info-value {
            flex: 1;
            color: #333;
            font-weight: 600;
        }
        
        .bukti-pembayaran {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .bukti-pembayaran:hover {
            transform: scale(1.02);
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s ease;
        }
        
        .btn-back:hover {
            background: #5a6268;
            color: white;
        }
        
        .alert-info-custom {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container container-main">
        <div class="mb-3">
            <a href="transaksi.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Riwayat Transaksi
            </a>
        </div>

        <div class="detail-card">
            <div class="card-header-custom">
                <h3><i class="fas fa-receipt"></i> Detail Transaksi #TRX-<?php echo str_pad($transaction['id_transaksi'], 6, '0', STR_PAD_LEFT); ?></h3>
                <span class="status-large <?php echo $transaction['status']; ?>">
                    <?php 
                    switch($transaction['status']) {
                        case 'pending':
                            echo '<i class="fas fa-clock"></i> Menunggu Verifikasi';
                            break;
                        case 'approved':
                            echo '<i class="fas fa-check-circle"></i> Pembayaran Disetujui';
                            break;
                        case 'rejected':
                            echo '<i class="fas fa-times-circle"></i> Pembayaran Ditolak';
                            break;
                    }
                    ?>
                </span>
            </div>

            <div class="card-body-custom">
                <!-- Status Info -->
                <?php if ($transaction['status'] === 'pending'): ?>
                    <div class="alert-info-custom">
                        <i class="fas fa-info-circle"></i>
                        <strong>Pembayaran Anda sedang dalam proses verifikasi.</strong>
                        <p class="mb-0 mt-2">Tim kami akan memverifikasi pembayaran Anda dalam waktu 1x24 jam. Anda akan mendapatkan notifikasi setelah pembayaran diverifikasi.</p>
                    </div>
                <?php elseif ($transaction['status'] === 'rejected'): ?>
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
                        <span class="info-value">#TRX-<?php echo str_pad($transaction['id_transaksi'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Tanggal Transaksi</span>
                        <span class="info-value">
                            <?php 
                            $date = new DateTime($transaction['tanggal_transaksi']);
                            echo $date->format('d F Y, H:i') . ' WIB';
                            ?>
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Nama Paket</span>
                        <span class="info-value"><?php echo htmlspecialchars($transaction['nama_paket']); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Kategori</span>
                        <span class="info-value text-capitalize"><?php echo htmlspecialchars($transaction['kategori']); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Harga</span>
                        <span class="info-value text-success">Rp <?php echo number_format($transaction['harga'], 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Metode Pembayaran</span>
                        <span class="info-value text-capitalize"><?php echo htmlspecialchars($transaction['metode_pembayaran']); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge <?php echo $transaction['status'] === 'approved' ? 'bg-success' : ($transaction['status'] === 'rejected' ? 'bg-danger' : 'bg-warning text-dark'); ?>">
                                <?php echo strtoupper($transaction['status']); ?>
                            </span>
                        </span>
                    </div>
                </div>

                <!-- Informasi Member -->
                <div class="info-section">
                    <h4 class="section-title"><i class="fas fa-user"></i> Informasi Member</h4>
                    
                    <div class="info-row">
                        <span class="info-label">Nama</span>
                        <span class="info-value"><?php echo htmlspecialchars($transaction['nama_member']); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo htmlspecialchars($transaction['email']); ?></span>
                    </div>
                </div>

                <!-- Catatan -->
                <?php if ($transaction['notes']): ?>
                <div class="info-section">
                    <h4 class="section-title"><i class="fas fa-sticky-note"></i> Catatan</h4>
                    <div class="alert alert-light">
                        <?php echo nl2br(htmlspecialchars($transaction['notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Verifikasi Info -->
                <?php if ($transaction['verified_at']): ?>
                <div class="info-section">
                    <h4 class="section-title"><i class="fas fa-check-double"></i> Informasi Verifikasi</h4>
                    
                    <div class="info-row">
                        <span class="info-label">Diverifikasi Pada</span>
                        <span class="info-value">
                            <?php 
                            $verified_date = new DateTime($transaction['verified_at']);
                            echo $verified_date->format('d F Y, H:i') . ' WIB';
                            ?>
                        </span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Bukti Pembayaran -->
                <?php if ($transaction['bukti_pembayaran']): ?>
                <div class="info-section">
                    <h4 class="section-title"><i class="fas fa-image"></i> Bukti Pembayaran</h4>
                    <div class="text-center">
                        <a href="../../../uploads/bukti_pembayaran/<?php echo $transaction['bukti_pembayaran']; ?>" target="_blank">
                            <img src="../../../uploads/bukti_pembayaran/<?php echo $transaction['bukti_pembayaran']; ?>" 
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$con->close();
?>