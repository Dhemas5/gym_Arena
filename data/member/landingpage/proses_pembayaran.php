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

// Ambil data paket dari session
if (!isset($_SESSION['checkout_paket'])) {
    $_SESSION['error'] = "Data paket tidak valid!";
    header("Location: indexmemberr.php");
    exit;
}

$paket = $_SESSION['checkout_paket'];
$tipe_member = $_SESSION['tipe_member'] ?? 'umum';

// Proses upload bukti pembayaran dan simpan transaksi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['konfirmasi_pembayaran'])) {
    $catatan = $_POST['catatan'] ?? '';

    // Validasi file upload
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['bukti_pembayaran']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (!in_array(strtolower($filetype), $allowed)) {
            $_SESSION['error'] = "Format file tidak valid! Hanya JPG, JPEG, PNG, dan GIF yang diperbolehkan.";
        } else {
            $upload_dir = "../../../uploads/bukti_pembayaran/";

            // Buat direktori jika belum ada
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Generate ID Transaksi
            $id_transaksi = 'ONL' . date('YmdHis') . rand(100, 999);

            $new_filename = 'bukti_' . $id_transaksi . '_' . time() . '.' . $filetype;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $upload_path)) {

                // Handle paket custom vs paket dari database
                if (is_numeric($paket['id_paket'])) {
                    // Paket dari database - gunakan id_paket asli
                    $id_paket_for_db = $paket['id_paket'];
                } else {
                    // Paket custom - cari paket yang sesuai atau gunakan default
                    // Untuk sementara, kita gunakan paket harian gym (id=2) sebagai default
                    $id_paket_for_db = 2; // Default to Gym Harian

                    // Atau bisa juga mencari paket berdasarkan nama
                    $query_find_paket = "SELECT id_paket FROM tbl_paket WHERE nama_paket LIKE ? LIMIT 1";
                    $stmt_find = $con->prepare($query_find_paket);
                    $search_term = '%' . $paket['nama_paket'] . '%';
                    $stmt_find->bind_param("s", $search_term);
                    $stmt_find->execute();
                    $result_find = $stmt_find->get_result();

                    if ($result_find->num_rows > 0) {
                        $found_paket = $result_find->fetch_assoc();
                        $id_paket_for_db = $found_paket['id_paket'];
                    }
                    $stmt_find->close();
                }

                // Simpan transaksi ke tbl_transaksi_online
                $query_insert = "INSERT INTO tbl_transaksi_online 
                                (id_transaksi, tgl_transaksi, id_member, id_paket, total413, 
                                 bukti_pembayaran, status, verified_at, verified_by) 
                                VALUES (?, NOW(), ?, ?, ?, ?, 'pending', NULL, NULL)";

                $stmt_insert = $con->prepare($query_insert);

                $stmt_insert->bind_param(
                    "siids",
                    $id_transaksi,
                    $id_member,
                    $id_paket_for_db,
                    $paket['harga'],
                    $new_filename
                );

                if ($stmt_insert->execute()) {
                    $stmt_insert->close();

                    // Simpan data paket custom ke session untuk ditampilkan nanti
                    $_SESSION['last_transaction'] = [
                        'id_transaksi' => $id_transaksi,
                        'paket' => $paket,
                        'tipe_member' => $tipe_member
                    ];

                    // Hapus session checkout
                    unset($_SESSION['checkout_paket']);
                    unset($_SESSION['tipe_member']);

                    $_SESSION['success'] = "Bukti pembayaran berhasil diupload! Silakan tunggu verifikasi dari admin.";
                    header("Location: transaksi.php?id=" . $id_transaksi);
                    exit;
                } else {
                    $_SESSION['error'] = "Gagal menyimpan transaksi: " . $con->error;
                    // Hapus file yang sudah diupload
                    if (file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                }
            } else {
                $_SESSION['error'] = "Gagal mengupload file!";
            }
        }
    } else {
        $_SESSION['error'] = "Silakan pilih file bukti pembayaran!";
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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
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
            margin-bottom: 20px;
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
            margin-bottom: 10px;
        }

        .total-payment {
            background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 30px;
        }

        .total-payment h5 {
            color: #0d1b2a;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .total-payment h3 {
            color: #0d1b2a;
            font-weight: 700;
            margin: 0;
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

        .qris-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
        }

        .qris-section h5 {
            color: #0d1b2a;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .qris-image {
            max-width: 300px;
            margin: 0 auto;
            display: block;
            border: 3px solid #42a5f5;
            border-radius: 10px;
            padding: 10px;
            background: white;
        }

        .rekening-section {
            background: rgba(66, 165, 245, 0.1);
            border: 1px solid rgba(66, 165, 245, 0.3);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .rekening-section h5 {
            color: #42a5f5;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .rekening-item {
            background: rgba(13, 27, 42, 0.7);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border: 1px solid rgba(66, 165, 245, 0.2);
        }

        .rekening-item:last-child {
            margin-bottom: 0;
        }

        .rekening-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 5px;
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
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-copy:hover {
            background: #1976d2;
        }

        .upload-section {
            margin-top: 30px;
        }

        .upload-section h5 {
            color: #42a5f5;
            margin-bottom: 20px;
            font-weight: 700;
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
            margin-bottom: 10px;
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
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s;
            width: 100%;
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
            transition: all 0.3s;
            margin-top: 20px;
        }

        .btn-submit:hover {
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

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
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
    </style>
</head>

<body>
    <div class="payment-container">
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

        <div class="payment-card">
            <div class="payment-header">
                <h2><i class="fas fa-money-bill-wave"></i> Pembayaran</h2>
                <p class="text-muted mb-0">Silakan lakukan pembayaran dan upload bukti transfer</p>
            </div>

            <!-- Info Paket -->
            <div class="paket-info">
                <h5 class="text-primary mb-3">Detail Paket</h5>
                <div class="paket-detail">
                    <span>Nama Paket:</span>
                    <span class="fw-bold"><?php echo htmlspecialchars($paket['nama_paket']); ?></span>
                </div>
                <div class="paket-detail">
                    <span>Tipe Member:</span>
                    <span class="fw-bold text-uppercase"><?php echo $tipe_member; ?></span>
                </div>
                <div class="paket-detail">
                    <span>Durasi:</span>
                    <span class="fw-bold">
                        <?php
                        if (isset($paket['durasi_hari'])) {
                            echo $paket['durasi_hari'] . ' Hari';
                        } else {
                            echo '1 Sesi';
                        }
                        ?>
                    </span>
                </div>
            </div>

            <div class="total-payment">
                <h5>Total Pembayaran</h5>
                <h3>Rp <?php echo number_format($paket['harga'], 0, ',', '.'); ?></h3>
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

            <!-- QRIS Section -->
            <div class="qris-section">
                <h5><i class="fas fa-qrcode"></i> Scan QR Code untuk Pembayaran</h5>
                <img src="../../../assets/qris-placeholder.png" alt="QR Code" class="qris-image"
                    onerror="this.src='https://via.placeholder.com/300x300/42a5f5/ffffff?text=QR+CODE+PEMBAYARAN'">
                <p style="color: #666; margin-top: 15px; font-size: 0.9rem;">
                    Scan QR Code menggunakan aplikasi mobile banking Anda
                </p>
            </div>

            <!-- Rekening Transfer Section -->
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

            <!-- Upload Bukti Section -->
            <div class="upload-section">
                <form method="POST" enctype="multipart/form-data">
                    <h5><i class="fas fa-cloud-upload-alt"></i> Upload Bukti Pembayaran</h5>

                    <input type="hidden" name="id_paket" value="<?php echo $paket['id_paket']; ?>">
                    <input type="hidden" name="harga_paket" value="<?php echo $paket['harga']; ?>">
                    <input type="hidden" name="tipe_member" value="<?php echo $tipe_member; ?>">

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

                    <button type="submit" name="konfirmasi_pembayaran" class="btn-submit">
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
        // Display selected file name
        document.getElementById('bukti_pembayaran').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                document.getElementById('fileName').innerHTML = '<i class="fas fa-file-image"></i> ' + fileName;
            }
        });

        // Copy to clipboard function
        function copyToClipboard(elementId) {
            const text = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(text).then(function() {
                alert('Nomor rekening berhasil disalin: ' + text);
            }, function(err) {
                console.error('Gagal menyalin: ', err);
            });
        }
    </script>
</body>

</html>

<?php
$con->close();
?>