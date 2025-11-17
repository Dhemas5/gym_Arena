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

// Ambil data paket dari POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_paket'])) {
    $id_paket = $_POST['id_paket'];
    $harga_paket = $_POST['harga_paket'] ?? 0;
    $tipe_member = $_POST['tipe_member'] ?? 'umum';
    
    // Cek apakah ini paket dari database atau paket custom
    if (is_numeric($id_paket)) {
        // Ambil detail paket dari database
        $query = "SELECT * FROM tbl_paket WHERE id_paket = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $id_paket);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $paket = $result->fetch_assoc();
        } else {
            $_SESSION['error'] = "Paket tidak ditemukan!";
            header("Location: indexmemberr.php");
            exit;
        }
        $stmt->close();
    } else {
        // Untuk paket custom (boxing_1bulan, program_trainer, dll)
        $paket = [
            'id_paket' => $id_paket,
            'nama_paket' => $_POST['nama_paket'] ?? 'Paket Custom',
            'durasi_hari' => getDurasiFromId($id_paket),
            'tipe_penjualan' => 'custom',
            'harga' => $harga_paket
        ];
    }
    
    // Simpan data paket ke session untuk proses selanjutnya
    $_SESSION['checkout_paket'] = $paket;
    $_SESSION['tipe_member'] = $tipe_member;
    
} else if (isset($_SESSION['checkout_paket'])) {
    // Ambil dari session jika sudah ada
    $paket = $_SESSION['checkout_paket'];
    $tipe_member = $_SESSION['tipe_member'] ?? 'umum';
} else {
    $_SESSION['error'] = "Data paket tidak valid!";
    header("Location: indexmemberr.php");
    exit;
}

// Fungsi helper untuk mendapatkan durasi dari ID paket custom
function getDurasiFromId($id_paket) {
    $durations = [
        'gym_harian' => 1,
        'gym_1bulan' => 30,
        'gym_3bulan' => 90,
        'gym_6bulan' => 180,
        'gym_1tahun' => 365,
        'kelas_20k' => 1,
        'kelas_25k' => 1,
        'kelas_30k' => 1,
        'boxing_1bulan' => 30,
        'program_trainer' => 30
    ];
    
    return $durations[$id_paket] ?? 1;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Pembayaran - Arena FIT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%);
            min-height: 100vh;
            color: white;
            padding: 50px 0;
        }
        
        .checkout-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .checkout-card {
            background: rgba(13, 27, 42, 0.95);
            border: 1px solid rgba(66, 165, 245, 0.3);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }
        
        .checkout-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(66, 165, 245, 0.3);
        }
        
        .checkout-header h2 {
            color: #42a5f5;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .paket-info {
            background: rgba(66, 165, 245, 0.1);
            border: 1px solid rgba(66, 165, 245, 0.3);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .paket-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #42a5f5;
            margin-bottom: 15px;
        }
        
        .paket-detail {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(66, 165, 245, 0.2);
        }
        
        .paket-detail:last-child {
            border-bottom: none;
        }
        
        .paket-label {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .paket-value {
            color: white;
            font-weight: 600;
        }
        
        .total-payment {
            background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .total-payment h5 {
            color: #0d1b2a;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .total-payment h3 {
            color: #0d1b2a;
            font-weight: 700;
            margin: 0;
            font-size: 2rem;
        }
        
        .btn-lanjut {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s;
            margin-top: 20px;
        }
        
        .btn-lanjut:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(76, 175, 80, 0.4);
        }
        
        .btn-back {
            background: rgba(108, 117, 125, 0.3);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: all 0.3s;
            margin-top: 15px;
        }
        
        .btn-back:hover {
            background: rgba(108, 117, 125, 0.5);
            border-color: #42a5f5;
            color: white;
        }
        
        .info-box {
            background: rgba(33, 150, 243, 0.1);
            border-left: 4px solid #2196F3;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        
        .info-box i {
            color: #2196F3;
            margin-right: 10px;
        }

        .tipe-member-badge {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
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
        
        <div class="checkout-card">
            <div class="checkout-header">
                <h2><i class="fas fa-shopping-cart"></i> Checkout Pembayaran</h2>
                <p class="text-muted mb-0">Periksa detail pesanan Anda</p>
            </div>
            
            <div class="paket-info">
                <div class="paket-name">
                    <?php echo htmlspecialchars($paket['nama_paket']); ?>
                    <span class="tipe-member-badge">
                        <?php echo strtoupper($tipe_member); ?>
                    </span>
                </div>
                
                <div class="paket-detail">
                    <span class="paket-label">Durasi</span>
                    <span class="paket-value">
                        <?php 
                        if (isset($paket['durasi_hari'])) {
                            echo htmlspecialchars($paket['durasi_hari']) . ' Hari';
                        } else {
                            echo '1 Sesi';
                        }
                        ?>
                    </span>
                </div>
                
                <?php if (isset($paket['tipe_penjualan'])): ?>
                <div class="paket-detail">
                    <span class="paket-label">Tipe</span>
                    <span class="paket-value text-capitalize"><?php echo htmlspecialchars($paket['tipe_penjualan']); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="paket-detail">
                    <span class="paket-label">Harga</span>
                    <span class="paket-value">Rp <?php echo number_format($paket['harga'], 0, ',', '.'); ?></span>
                </div>
            </div>
            
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <strong>Informasi:</strong>
                <p class="mb-0 mt-2">Setelah melanjutkan, Anda akan diarahkan ke halaman pembayaran untuk menyelesaikan transaksi.</p>
            </div>
            
            <div class="total-payment">
                <h5>Total Pembayaran</h5>
                <h3>Rp <?php echo number_format($paket['harga'], 0, ',', '.'); ?></h3>
            </div>
            
            <form method="POST" action="proses_pembayaran.php">
                <input type="hidden" name="id_paket" value="<?php echo $paket['id_paket']; ?>">
                <input type="hidden" name="harga_paket" value="<?php echo $paket['harga']; ?>">
                <input type="hidden" name="tipe_member" value="<?php echo $tipe_member; ?>">
                <button type="submit" class="btn-lanjut">
                    <i class="fas fa-arrow-right"></i> Lanjut ke Pembayaran
                </button>
            </form>
            
            <a href="indexmemberr.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$con->close();
?>