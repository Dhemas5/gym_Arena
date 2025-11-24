<?php
session_start();
require "../../../setting/koneksi.php";

if (!isset($_SESSION['login']) || $_SESSION['user_type'] !== 'member') {
    header("Location: ../login/login.php");
    exit;
}

if (!isset($_SESSION['checkout_paket'])) {
    $_SESSION['error'] = "Sesi checkout telah habis!";
    header("Location: indexmemberr.php");
    exit;
}

$id_member = $_SESSION['id_member'];
$paket = $_SESSION['checkout_paket'];
$tipe_member = $_SESSION['tipe_member'] ?? 'umum';

// Proses upload bukti pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi_pembayaran'])) {
    $catatan = trim($_POST['catatan'] ?? '');

    // Validasi file
    if (!isset($_FILES['bukti_pembayaran']) || $_FILES['bukti_pembayaran']['error'] !== 0) {
        $_SESSION['error'] = "Harap upload bukti pembayaran!";
        header("Location: proses_pembayaran.php");
        exit;
    }

    $file = $_FILES['bukti_pembayaran'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $_SESSION['error'] = "Format file tidak didukung. Gunakan JPG/PNG/GIF.";
        header("Location: proses_pembayaran.php");
        exit;
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5MB
        $_SESSION['error'] = "Ukuran file maksimal 5MB.";
        header("Location: proses_pembayaran.php");
        exit;
    }

    // Direktori upload
    $upload_dir = "../../../Uploads/bukti_pembayaran/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate ID Transaksi & nama file
    $id_transaksi = 'TRX' . date('YmdHis') . rand(100, 999);
    $new_filename = $id_transaksi . '_' . time() . '.' . $ext;
    $destination = $upload_dir . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $_SESSION['error'] = "Gagal mengupload bukti pembayaran.";
        header("Location: proses_pembayaran.php");
        exit;
    }

    // Tentukan id_paket untuk database
    $id_paket_db = is_numeric($paket['id_paket']) ? (int)$paket['id_paket'] : null;

    // Simpan ke tbl_transaksi_online
    $stmt = $con->prepare("
        INSERT INTO tbl_transaksi_online 
        (id_transaksi, tgl_transaksi, id_member, id_paket, total, bukti_pembayaran, catatan, status) 
        VALUES (?, NOW(), ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->bind_param("siisss", $id_transaksi, $id_member, $id_paket_db, $paket['harga'], $new_filename, $catatan);

    if ($stmt->execute()) {
        unset($_SESSION['checkout_paket']);
        unset($_SESSION['tipe_member']);
        $_SESSION['success'] = "Bukti pembayaran berhasil dikirim! Tunggu konfirmasi admin (maks 1×24 jam).";
        header("Location: transaksi.php?id=" . $id_transaksi);
        exit;
    } else {
        // Hapus file yang sudah diupload jika gagal simpan database
        if (file_exists($destination)) {
            unlink($destination);
        }
        $_SESSION['error'] = "Gagal menyimpan transaksi. Silakan coba lagi.";
        header("Location: proses_pembayaran.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Pembayaran - Arena FIT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%);
            min-height: 100vh;
            color: white;
            padding: 50px 0;
        }

        .payment-container {
            max-width: 700px;
            margin: 0 auto;
        }

        .payment-card {
            background: rgba(13, 27, 42, 0.95);
            border: 1px solid rgba(66, 165, 245, 0.3);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }

        .payment-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(66, 165, 245, 0.3);
        }

        .payment-header h2 {
            color: #42a5f5;
            font-weight: 700;
        }

        .paket-info {
            background: rgba(66, 165, 245, 0.1);
            border: 1px solid rgba(66, 165, 245, 0.3);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .paket-detail {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(66, 165, 245, 0.2);
        }

        .paket-detail:last-child {
            border-bottom: none;
        }

        .total-payment {
            background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 30px;
        }

        .total-payment h3 {
            color: #0d1b2a;
            font-weight: 700;
        }

        .info-box {
            background: rgba(33, 150, 243, 0.1);
            border-left: 4px solid #2196F3;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .qris-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
        }

        .qris-image {
            max-width: 300px;
            margin: 0 auto;
            border: 3px solid #42a5f5;
            border-radius: 10px;
            padding: 10px;
        }

        .rekening-section {
            background: rgba(66, 165, 245, 0.1);
            border: 1px solid rgba(66, 165, 245, 0.3);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .rekening-item {
            background: rgba(13, 27, 42, 0.7);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .rekening-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .rekening-value {
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-copy {
            background: #42a5f5;
            border: none;
            color: white;
            padding: 5px 15px;
            border-radius: 5px;
            font-size: 0.85rem;
        }

        .btn-copy:hover {
            background: #1976d2;
        }

        .upload-section {
            margin-top: 30px;
        }

        .form-control,
        .form-control:focus {
            background: rgba(13, 27, 42, 0.7);
            border: 1px solid rgba(66, 165, 245, 0.3);
            color: white;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .btn-file-upload {
            background: rgba(66, 165, 245, 0.2);
            border: 2px dashed rgba(66, 165, 245, 0.5);
            color: white;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            width: 100%;
            cursor: pointer;
        }

        .btn-file-upload:hover {
            background: rgba(66, 165, 245, 0.3);
            border-color: #42a5f5;
        }

        .file-upload-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-name {
            margin-top: 10px;
            color: #42a5f5;
            font-weight: 600;
        }

        .btn-submit {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            width: 100%;
        }

        .btn-back {
            background: rgba(108, 117, 125, 0.3);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            text-align: center;
            margin-top: 15px;
            display: block;
            text-decoration: none;
        }

        .btn-back:hover {
            background: rgba(108, 117, 125, 0.5);
            border-color: #42a5f5;
            color: white;
        }
    </style>
</head>

<body>
    <div class="payment-container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'];
                                                            unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="payment-card">
            <div class="payment-header">
                <h2><i class="fas fa-money-bill-wave"></i> Pembayaran</h2>
                <p class="text-muted mb-0">Silakan lakukan pembayaran dan upload bukti transfer</p>
            </div>

            <div class="paket-info">
                <h5 class="text-primary mb-3">Detail Paket</h5>
                <div class="paket-detail">
                    <span>Nama Paket:</span>
                    <span class="fw-bold"><?= htmlspecialchars($paket['nama_paket']) ?></span>
                </div>
                <div class="paket-detail">
                    <span>Tipe Member:</span>
                    <span class="fw-bold text-uppercase"><?= $tipe_member ?></span>
                </div>
                <div class="paket-detail">
                    <span>Durasi:</span>
                    <span class="fw-bold"><?= isset($paket['durasi_hari']) ? $paket['durasi_hari'] . ' Hari' : '1 Sesi' ?></span>
                </div>
            </div>

            <div class="total-payment">
                <h5>Total Pembayaran</h5>
                <h3>Rp <?= number_format($paket['harga'], 0, ',', '.') ?></h3>
            </div>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <strong>Petunjuk Pembayaran:</strong>
                <ol style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Scan QR Code atau transfer ke rekening yang tersedia</li>
                    <li>Upload bukti pembayaran Anda</li>
                    <li>Tunggu verifikasi dari admin (maksimal 1x24 jam)</li>
                </ol>
            </div>

            <div class="qris-section">
                <h5><i class="fas fa-qrcode"></i> Scan QR Code untuk Pembayaran</h5>
                <img src="../../../assets/qris-placeholder.png" alt="QR Code" class="qris-image"
                    onerror="this.src='https://via.placeholder.com/300x300/42a5f5/ffffff?text=QR+CODE+PEMBAYARAN'">
                <p style="color: #666; margin-top: 15px; font-size: 0.9rem;">
                    Scan QR Code menggunakan aplikasi mobile banking Anda
                </p>
            </div>

            <div class="rekening-section">
                <h5><i class="fas fa-university"></i> Atau Transfer ke Rekening Berikut</h5>
                <div class="rekening-item">
                    <div class="rekening-label">Bank BCA</div>
                    <div class="rekening-value">
                        <span id="bca">2009138999</span>
                        <button class="btn-copy" onclick="copyToClipboard('bca')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <div class="rekening-label mt-2">a.n. CV. ARENA MAJU BERSAMA</div>
                </div>
            </div>

            <div class="upload-section">
                <form method="POST" enctype="multipart/form-data">
                    <h5><i class="fas fa-cloud-upload-alt"></i> Upload Bukti Pembayaran</h5>
                    <input type="hidden" name="konfirmasi_pembayaran" value="1">
                    <div class="mb-3">
                        <label for="bukti_pembayaran" class="form-label">Bukti Transfer <span class="text-danger">*</span></label>
                        <div class="file-upload-wrapper">
                            <label class="btn-file-upload">
                                <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                                <p class="mb-0">Klik untuk memilih file</p>
                                <small>Format: JPG, JPEG, PNG, GIF (Max: 5MB)</small>
                                <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" accept="image/*" required>
                            </label>
                        </div>
                        <div class="file-name" id="fileName"></div>
                    </div>
                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" name="catatan" id="catatan" rows="3"
                            placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Konfirmasi Pembayaran
                    </button>
                </form>
                <a href="checkout_pembayaran.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('bukti_pembayaran').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                document.getElementById('fileName').innerHTML = '<i class="fas fa-file-image"></i> ' + fileName;
            }
        });

        function copyToClipboard(elementId) {
            const text = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert('Nomor rekening berhasil disalin: ' + text);
            }, (err) => {
                console.error('Gagal menyalin: ', err);
            });
        }
    </script>
</body>

</html>

<?php $con->close(); ?>