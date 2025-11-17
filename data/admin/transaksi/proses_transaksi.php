<?php
// proses_transaksi.php → FINAL: QRIS DANA + TUNAI
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'db_gym';

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['id_user'])) {
    $_SESSION['id_user'] = 1;
    $_SESSION['username'] = 'admin';
}

$con = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($con->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Koneksi gagal']);
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
    return $prefix . str_pad(intval($res['cnt']) + 1, 3, '0', STR_PAD_LEFT);
}

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
    $qty = 1;
    $potongan_item = floatval($_POST['potongan_diskon_item'] ?? 0);
    $potongan_global = floatval($_POST['potongan_diskon'] ?? 0);
    $metode_pembayaran = strtoupper(trim($_POST['metode_pembayaran'] ?? 'TUNAI'));
    $keterangan = trim($_POST['keterangan'] ?? '');

    if ($id_paket <= 0 || $harga_satuan <= 0) {
        throw new Exception("Paket atau harga tidak valid.");
    }

    $sub_total = $harga_satuan * $qty;
    $total_item = max(0, $sub_total - $potongan_item);
    $grand_total = max(0, $total_item - $potongan_global);
    $jumlah_dibayar_tunai = null;
    $jumlah_kembalian = null;

    if ($metode_pembayaran === 'TUNAI') {
        $jumlah_dibayar_tunai = floatval($_POST['jumlah_dibayar_tunai'] ?? 0);
        if ($jumlah_dibayar_tunai < $grand_total) {
            throw new Exception("Uang tunai kurang.");
        }
        $jumlah_kembalian = $jumlah_dibayar_tunai - $grand_total;
    } elseif ($metode_pembayaran === 'QRIS') {
        $jumlah_dibayar_tunai = floatval($_POST['jumlah_dibayar_tunai'] ?? 0);
        if ($jumlah_dibayar_tunai < $grand_total) {
            throw new Exception("Nominal transfer kurang.");
        }
        $jumlah_kembalian = $jumlah_dibayar_tunai - $grand_total;
        $keterangan .= ' [QRIS: DANA 085719630447]';
    } else {
        throw new Exception("Metode pembayaran tidak valid.");
    }

    $id_member = !empty($_POST['id_member']) ? intval($_POST['id_member']) : null;
    if ($id_member) {
        $cek = $con->prepare("SELECT id_member FROM tbl_member WHERE id_member = ?");
        $cek->bind_param('i', $id_member);
        $cek->execute();
        if ($cek->get_result()->num_rows === 0) {
            throw new Exception("Member tidak ditemukan.");
        }
        $cek->close();
    }

    $sql_header = "INSERT INTO tbl_transaksi_header 
        (id_transaksi, jenis_transaksi, tgl_transaksi, id_member, id_user_kasir, sub_total, potongan_diskon_global, grand_total, metode_pembayaran, jumlah_dibayar_tunai, jumlah_kembalian, keterangan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql_header);
    $stmt->bind_param('sssisiddddds', $id_trx, $jenis_transaksi, $tgl, $id_member, $id_user_kasir, $sub_total, $potongan_global, $grand_total, $metode_pembayaran, $jumlah_dibayar_tunai, $jumlah_kembalian, $keterangan);
    if (!$stmt->execute()) throw new Exception("Gagal simpan header: " . $stmt->error);
    $stmt->close();

    $sql_detail = "INSERT INTO tbl_transaksi_detail (id_transaksi, id_paket, qty, harga_satuan, potongan_diskon_item, total_item) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql_detail);
    $stmt->bind_param('siiddd', $id_trx, $id_paket, $qty, $harga_satuan, $potongan_item, $total_item);
    if (!$stmt->execute()) throw new Exception("Gagal simpan detail: " . $stmt->error);
    $stmt->close();

    if ($id_member) {
        $stmt = $con->prepare("SELECT durasi_hari FROM tbl_paket WHERE id_paket = ?");
        $stmt->bind_param('i', $id_paket);
        $stmt->execute();
        $paket = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $durasi_hari = intval($paket['durasi_hari']);
        $tgl_mulai = $tgl;
        $tgl_berakhir = date('Y-m-d H:i:s', strtotime($tgl . " + $durasi_hari days"));

        $sql_mem = "INSERT INTO tbl_membership (id_member, id_transaksi, id_paket, tgl_mulai, tgl_berakhir) VALUES (?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sql_mem);
        $stmt->bind_param('isiss', $id_member, $id_trx, $id_paket, $tgl_mulai, $tgl_berakhir);
        if (!$stmt->execute()) throw new Exception("Gagal aktifkan membership: " . $stmt->error);
        $stmt->close();
    }

    $con->commit();
    $con->autocommit(true);
    echo json_encode(['success' => true, 'id_transaksi' => $id_trx, 'metode' => $metode_pembayaran, 'kembalian' => $jumlah_kembalian]);
    exit;
} catch (Exception $e) {
    $con->rollback();
    $con->autocommit(true);
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
