<?php
// proses_transaksi.php
// Versi final: tanpa sistem perpanjangan membership
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'db_gym';

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
date_default_timezone_set('Asia/Jakarta'); // <<< tambahkan ini biar waktu sesuai WIB

// Set default kasir untuk development
if (!isset($_SESSION['id_user'])) {
    $_SESSION['id_user'] = 1;
    $_SESSION['username'] = 'admin';
}

$con = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($con->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Koneksi database gagal: ' . $con->connect_error]);
    exit;
}
$con->set_charset('utf8mb4');

function genTransactionId($con)
{
    $date = date('Ymd');
    $prefix = 'TRX' . $date;
    $like = $prefix . '%';
    $stmt = $con->prepare("SELECT COUNT(*) AS cnt FROM tbl_transaksi_header WHERE id_transaksi LIKE ?");
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $n = intval($res['cnt']) + 1;
    return $prefix . str_pad($n, 3, '0', STR_PAD_LEFT);
}

// Hanya menerima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Metode tidak diizinkan']);
    exit;
}

try {
    $con->autocommit(false);
    $con->begin_transaction();

    $id_trx = genTransactionId($con);
    $tgl = date('Y-m-d H:i:s');
    $jenis_transaksi = 'offline';

    $id_user_kasir = intval($_POST['id_user_kasir'] ?? $_SESSION['id_user']);
    $id_paket = intval($_POST['id_paket'] ?? 0);
    $harga_satuan = floatval($_POST['harga_paket'] ?? 0);
    $qty = intval($_POST['qty'] ?? 1);
    if ($qty <= 0) $qty = 1;
    $potongan_item = floatval($_POST['potongan_diskon_item'] ?? 0);
    $potongan_global = floatval($_POST['potongan_diskon'] ?? 0);
    $metode_pembayaran = trim($_POST['metode_pembayaran'] ?? 'Tunai');
    $keterangan = trim($_POST['keterangan'] ?? '');

    if ($id_paket <= 0) throw new Exception("Paket tidak valid.");
    if ($harga_satuan <= 0) throw new Exception("Harga paket tidak valid.");

    $sub_total = $harga_satuan * $qty;
    $total_item = max(0, $sub_total - $potongan_item);
    $grand_total = max(0, $total_item - $potongan_global);

    $jumlah_dibayar_tunai = null;
    $jumlah_kembalian = null;
    if (strtolower($metode_pembayaran) === 'tunai') {
        $jumlah_dibayar_tunai = floatval($_POST['jumlah_dibayar_tunai'] ?? 0);
        if ($jumlah_dibayar_tunai < $grand_total) {
            throw new Exception("Uang tunai kurang. Total harus dibayar Rp " . number_format($grand_total, 0, ',', '.'));
        }
        $jumlah_kembalian = $jumlah_dibayar_tunai - $grand_total;
    }

    // Validasi member jika ada
    $id_member = !empty($_POST['id_member']) ? intval($_POST['id_member']) : null;
    if ($id_member !== null) {
        $cek = $con->prepare("SELECT id_member FROM tbl_member WHERE id_member = ?");
        $cek->bind_param('i', $id_member);
        $cek->execute();
        $rs = $cek->get_result();
        if ($rs->num_rows === 0) {
            throw new Exception("Member tidak ditemukan.");
        }
        $cek->close();
    }

    // === INSERT HEADER ===
    $sql_header = "INSERT INTO tbl_transaksi_header 
        (id_transaksi, jenis_transaksi, tgl_transaksi, id_member, id_user_kasir, sub_total, potongan_diskon_global, grand_total, metode_pembayaran, jumlah_dibayar_tunai, jumlah_kembalian, keterangan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql_header);
    $stmt->bind_param(
        'ssssssssssss',
        $id_trx,
        $jenis_transaksi,
        $tgl,
        $id_member,
        $id_user_kasir,
        $sub_total,
        $potongan_global,
        $grand_total,
        $metode_pembayaran,
        $jumlah_dibayar_tunai,
        $jumlah_kembalian,
        $keterangan
    );
    if (!$stmt->execute()) throw new Exception("Insert header gagal: " . $stmt->error);
    $stmt->close();

    // === INSERT DETAIL ===
    $sql_detail = "INSERT INTO tbl_transaksi_detail (id_transaksi, id_paket, harga_satuan, qty, potongan_diskon_item, total_item)
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql_detail);
    $stmt->bind_param('ssssss', $id_trx, $id_paket, $harga_satuan, $qty, $potongan_item, $total_item);
    if (!$stmt->execute()) throw new Exception("Insert detail gagal: " . $stmt->error);
    $stmt->close();

    // === AKTIFKAN MEMBERSHIP TANPA PERPANJANGAN ===
    if ($id_member !== null) {
        $stmt = $con->prepare("SELECT durasi_hari FROM tbl_paket WHERE id_paket = ?");
        $stmt->bind_param('i', $id_paket);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) throw new Exception("Paket tidak ditemukan.");
        $paket = $res->fetch_assoc();
        $stmt->close();

        $durasi_hari = intval($paket['durasi_hari']);
        if ($durasi_hari <= 0) throw new Exception("Durasi paket tidak valid.");

        $tgl_mulai = date('Y-m-d H:i:s');
        $tgl_berakhir = date('Y-m-d H:i:s', strtotime("+$durasi_hari days"));

        $sql_mem = "INSERT INTO tbl_membership (id_member, id_transaksi, id_paket, tgl_mulai, tgl_berakhir)
                    VALUES (?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sql_mem);
        $stmt->bind_param('isiss', $id_member, $id_trx, $id_paket, $tgl_mulai, $tgl_berakhir);
        if (!$stmt->execute()) throw new Exception("Gagal simpan membership: " . $stmt->error);
        $stmt->close();
    }

    $con->commit();
    $con->autocommit(true);

    echo json_encode(['success' => true, 'id_transaksi' => $id_trx]);
    exit;
} catch (Exception $e) {
    $con->rollback();
    $con->autocommit(true);
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
