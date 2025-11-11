<?php
session_start();
require "../../../setting/koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['user_type'] !== 'member') {
    header("Location: ../login/login.php");
    exit;
}

// Cek apakah form sudah disubmit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: indexmemberr.php");
    exit;
}

// Ambil data dari form
$id_member = $_POST['id_member'];
$package_id = $_POST['package_id'];
$package_name = $_POST['package_name'];
$package_type = $_POST['package_type'];
$member_type = $_POST['member_type'];
$harga = $_POST['harga'];
$durasi = $_POST['durasi'];
$kategori = $_POST['kategori'];
$catatan = isset($_POST['catatan']) ? $_POST['catatan'] : '';

// Validasi input
if (empty($id_member) || empty($harga) || empty($durasi)) {
    $_SESSION['error'] = "Data pembayaran tidak lengkap!";
    header("Location: indexmemberr.php");
    exit;
}

// Handle upload bukti pembayaran
$upload_dir = "../../../uploads/bukti_pembayaran/";

// Buat folder jika belum ada
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$bukti_pembayaran = '';
$kartu_pelajar = '';

// Upload bukti pembayaran
if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
    $file = $_FILES['bukti_pembayaran'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validasi ekstensi file
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    if (!in_array($file_ext, $allowed_ext)) {
        $_SESSION['error'] = "Format file bukti pembayaran tidak valid! Hanya JPG, JPEG, dan PNG yang diperbolehkan.";
        header("Location: checkout.php?package_id=$package_id&type=$package_type&member_type=$member_type");
        exit;
    }
    
    // Validasi ukuran file (max 5MB)
    if ($file_size > 5 * 1024 * 1024) {
        $_SESSION['error'] = "Ukuran file bukti pembayaran terlalu besar! Maksimal 5MB.";
        header("Location: checkout.php?package_id=$package_id&type=$package_type&member_type=$member_type");
        exit;
    }
    
    // Generate nama file unik
    $new_file_name = 'bukti_' . $id_member . '_' . time() . '.' . $file_ext;
    $upload_path = $upload_dir . $new_file_name;
    
    // Upload file
    if (move_uploaded_file($file_tmp, $upload_path)) {
        $bukti_pembayaran = $new_file_name;
    } else {
        $_SESSION['error'] = "Gagal mengupload bukti pembayaran!";
        header("Location: checkout.php?package_id=$package_id&type=$package_type&member_type=$member_type");
        exit;
    }
} else {
    $_SESSION['error'] = "Bukti pembayaran harus diupload!";
    header("Location: checkout.php?package_id=$package_id&type=$package_type&member_type=$member_type");
    exit;
}

// Upload kartu pelajar jika member type adalah pelajar
if ($member_type === 'pelajar') {
    if (isset($_FILES['kartu_pelajar']) && $_FILES['kartu_pelajar']['error'] == 0) {
        $file = $_FILES['kartu_pelajar'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validasi ekstensi file
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        if (!in_array($file_ext, $allowed_ext)) {
            // Hapus bukti pembayaran yang sudah diupload
            if (!empty($bukti_pembayaran) && file_exists($upload_dir . $bukti_pembayaran)) {
                unlink($upload_dir . $bukti_pembayaran);
            }
            $_SESSION['error'] = "Format file kartu pelajar tidak valid! Hanya JPG, JPEG, dan PNG yang diperbolehkan.";
            header("Location: checkout.php?package_id=$package_id&type=$package_type&member_type=$member_type");
            exit;
        }
        
        // Validasi ukuran file (max 5MB)
        if ($file_size > 5 * 1024 * 1024) {
            // Hapus bukti pembayaran yang sudah diupload
            if (!empty($bukti_pembayaran) && file_exists($upload_dir . $bukti_pembayaran)) {
                unlink($upload_dir . $bukti_pembayaran);
            }
            $_SESSION['error'] = "Ukuran file kartu pelajar terlalu besar! Maksimal 5MB.";
            header("Location: checkout.php?package_id=$package_id&type=$package_type&member_type=$member_type");
            exit;
        }
        
        // Generate nama file unik
        $new_file_name = 'kartu_' . $id_member . '_' . time() . '.' . $file_ext;
        $upload_path = $upload_dir . $new_file_name;
        
        // Upload file
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $kartu_pelajar = $new_file_name;
        } else {
            // Hapus bukti pembayaran yang sudah diupload
            if (!empty($bukti_pembayaran) && file_exists($upload_dir . $bukti_pembayaran)) {
                unlink($upload_dir . $bukti_pembayaran);
            }
            $_SESSION['error'] = "Gagal mengupload kartu pelajar!";
            header("Location: checkout.php?package_id=$package_id&type=$package_type&member_type=$member_type");
            exit;
        }
    } else {
        // Hapus bukti pembayaran yang sudah diupload
        if (!empty($bukti_pembayaran) && file_exists($upload_dir . $bukti_pembayaran)) {
            unlink($upload_dir . $bukti_pembayaran);
        }
        $_SESSION['error'] = "Kartu pelajar/KTM harus diupload untuk mendapatkan harga pelajar!";
        header("Location: checkout.php?package_id=$package_id&type=$package_type&member_type=$member_type");
        exit;
    }
}

// Mulai transaksi database
$con->begin_transaction();

try {
    // 1. Insert ke tbl_payments
    $stmt_payment = $con->prepare("INSERT INTO tbl_payments (id_member, membership_type, amount, payment_proof, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
    $stmt_payment->bind_param("isds", $id_member, $kategori, $harga, $bukti_pembayaran);
    
    if (!$stmt_payment->execute()) {
        throw new Exception("Gagal menyimpan data pembayaran: " . $stmt_payment->error);
    }
    
    $id_payment = $con->insert_id;
    $stmt_payment->close();
    
    // 2. Insert ke tbl_transaksi
    $notes_data = $catatan;
    if ($member_type === 'pelajar' && !empty($kartu_pelajar)) {
        $notes_data .= ($catatan ? "\n\n" : "") . "Tipe Member: Pelajar/Mahasiswa\nKartu Pelajar: " . $kartu_pelajar;
    }
    
    $stmt_transaksi = $con->prepare("INSERT INTO tbl_transaksi (id_member, id_paket, nama_paket, kategori, harga, tanggal_transaksi, metode_pembayaran, status, bukti_pembayaran, notes) VALUES (?, ?, ?, ?, ?, NOW(), 'online', 'pending', ?, ?)");
    
    // Jika package_type adalah 'paket', gunakan id_paket dari database, jika tidak set NULL
    $id_paket_db = ($package_type === 'paket') ? $package_id : null;
    
    $stmt_transaksi->bind_param("iissdss", $id_member, $id_paket_db, $package_name, $kategori, $harga, $bukti_pembayaran, $notes_data);
    
    if (!$stmt_transaksi->execute()) {
        throw new Exception("Gagal menyimpan transaksi: " . $stmt_transaksi->error);
    }
    
    $id_transaksi = $con->insert_id;
    $stmt_transaksi->close();
    
    // 3. Update tbl_member status jika diperlukan
    // Status akan diupdate oleh admin setelah verifikasi pembayaran
    
    // Commit transaksi
    $con->commit();
    
    // Set success message
    $_SESSION['success'] = "Pembayaran berhasil diproses! Menunggu verifikasi dari admin.";
    $_SESSION['payment_id'] = $id_payment;
    $_SESSION['transaksi_id'] = $id_transaksi;
    
    // Redirect ke halaman sukses
    header("Location: payment_success.php?id=" . $id_transaksi);
    exit;
    
} catch (Exception $e) {
    // Rollback jika ada error
    $con->rollback();
    
    // Hapus file yang sudah diupload jika ada error
    if (!empty($bukti_pembayaran) && file_exists($upload_dir . $bukti_pembayaran)) {
        unlink($upload_dir . $bukti_pembayaran);
    }
    if (!empty($kartu_pelajar) && file_exists($upload_dir . $kartu_pelajar)) {
        unlink($upload_dir . $kartu_pelajar);
    }
    
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    header("Location: checkout.php?package_id=$package_id&type=$package_type&member_type=$member_type");
    exit;
}

$con->close();
?>