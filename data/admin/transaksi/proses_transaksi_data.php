<?php
// proses_transaksi.php
date_default_timezone_set('Asia/Jakarta');

$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'db_gym';

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['id_user'])) {
    $_SESSION['id_user'] = 1;
}

$con = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($con->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Koneksi gagal: ' . $con->connect_error]);
    exit;
}
$con->set_charset('utf8mb4');

// === GENERATE ID TRANSAKSI ===
function genTransactionId($con)
{
    $date = date('Ymd');
    $prefix = 'TRX' . $date;
    $like = $prefix . '%';
    $sql = "SELECT COUNT(*) AS cnt FROM tbl_transaksi_header WHERE id_transaksi LIKE ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $n = intval($res['cnt']) + 1;
    $stmt->close();
    return $prefix . str_pad($n, 3, '0', STR_PAD_LEFT);
}

// === MAIN PROCESS ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_single_item') {
    $con->begin_transaction();

    try {
        $id_trx = genTransactionId($con);
        $jenis_transaksi = 'offline';

        // === DATA UTAMA ===
        $id_user_kasir = intval($_POST['id_user_kasir'] ?? $_SESSION['id_user']);
        $id_paket = intval($_POST['id_paket'] ?? 0);
        $harga_satuan = floatval($_POST['harga_paket'] ?? 0);
        $qty = intval($_POST['qty'] ?? 1);
        $potongan_item = floatval($_POST['potongan_diskon_item'] ?? 0);
        $potongan_global = floatval($_POST['potongan_diskon'] ?? 0);
        $metode_pembayaran = $_POST['metode_pembayaran'] ?? 'Tunai';
        $keterangan = $_POST['keterangan'] ?? null;

        if ($id_paket <= 0 || $harga_satuan <= 0) {
            throw new Exception("Paket tidak valid.");
        }

        // === HITUNG TOTAL ===
        $sub_total = $harga_satuan * $qty;
        $total_setelah_diskon_item = $sub_total - $potongan_item;
        $grand_total = max(0, $total_setelah_diskon_item - $potongan_global);

        // === VALIDASI TUNAI ===
        $jumlah_dibayar_tunai = null;
        $jumlah_kembalian = null;
        if ($metode_pembayaran === 'Tunai') {
            $jumlah_dibayar_tunai = floatval($_POST['jumlah_dibayar_tunai'] ?? 0);
            $jumlah_kembalian = $jumlah_dibayar_tunai - $grand_total;
            if ($jumlah_dibayar_tunai < $grand_total) {
                throw new Exception("Uang tunai kurang dari total bayar.");
            }
        }

        // === VALIDASI MEMBER (jika ada) ===
        $id_member = null;
        if (!empty($_POST['id_member'])) {
            $id_member = intval($_POST['id_member']);
            $check = $con->prepare("SELECT id_member FROM tbl_member WHERE id_member = ?");
            $check->bind_param('i', $id_member);
            $check->execute();
            $result = $check->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Member ID $id_member tidak ditemukan.");
            }
            $check->close();
        }

        // === INSERT HEADER (tgl_transaksi pakai NOW()) ===
        $sql_header = "INSERT INTO tbl_transaksi_header 
            (id_transaksi, jenis_transaksi, tgl_transaksi, id_member, id_user_kasir, 
             sub_total, potongan_diskon_global, grand_total, metode_pembayaran, 
             jumlah_dibayar_tunai, jumlah_kembalian, keterangan)
            VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_header = $con->prepare($sql_header);
        $dibayar_bind = $jumlah_dibayar_tunai ?? 0.00;
        $kembalian_bind = $jumlah_kembalian ?? 0.00;

        $stmt_header->bind_param(
            'sssiidddsds',
            $id_trx,
            $jenis_transaksi,
            $id_member,           // NULL jika umum
            $id_user_kasir,
            $sub_total,
            $potongan_global,
            $grand_total,
            $metode_pembayaran,
            $dibayar_bind,
            $kembalian_bind,
            $keterangan
        );

        if (!$stmt_header->execute()) {
            throw new Exception("Gagal simpan header: " . $stmt_header->error);
        }
        $stmt_header->close();

        // === INSERT DETAIL ===
        $sql_detail = "INSERT INTO tbl_transaksi_detail 
            (id_transaksi, id_paket, harga_satuan, qty, potongan_diskon_item, total_item)
            VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_detail = $con->prepare($sql_detail);
        $total_item = $sub_total - $potongan_item;
        $stmt_detail->bind_param('sididd', $id_trx, $id_paket, $harga_satuan, $qty, $potongan_item, $total_item);
        if (!$stmt_detail->execute()) {
            throw new Exception("Gagal simpan detail: " . $stmt_detail->error);
        }
        $stmt_detail->close();

        // === AKTIFKAN MEMBERSHIP (hanya jika ada member) ===
        if ($id_member !== null) {
            $stmt_paket = $con->prepare("SELECT durasi_hari FROM tbl_paket WHERE id_paket = ?");
            $stmt_paket->bind_param('i', $id_paket);
            $stmt_paket->execute();
            $res_paket = $stmt_paket->get_result();
            if ($res_paket->num_rows === 0) {
                $stmt_paket->close();
                throw new Exception("Paket tidak ditemukan.");
            }
            $paket = $res_paket->fetch_assoc();
            $durasi_hari = $paket['durasi_hari'];
            $stmt_paket->close();

            if ($durasi_hari <= 0) {
                throw new Exception("Durasi paket tidak valid.");
            }

            $tgl_mulai = date('Y-m-d');
            $tgl_berakhir = date('Y-m-d', strtotime("+$durasi_hari days"));

            $jenis_aktivasi = 'Baru';
            $cek_last = $con->prepare("SELECT tgl_berakhir FROM tbl_aktivasi_membership WHERE id_member = ? ORDER BY tgl_dicatat DESC LIMIT 1");
            $cek_last->bind_param('i', $id_member);
            $cek_last->execute();
            $res_last = $cek_last->get_result();
            if ($res_last->num_rows > 0) {
                $last = $res_last->fetch_assoc();
                if ($last['tgl_berakhir'] >= $tgl_mulai) {
                    $jenis_aktivasi = 'Perpanjangan';
                }
            }
            $cek_last->close();

            $sql_aktivasi = "INSERT INTO tbl_aktivasi_membership 
                (id_member, id_transaksi, id_paket, tgl_mulai, tgl_berakhir, jenis_aktivasi)
                VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_aktivasi = $con->prepare($sql_aktivasi);
            $stmt_aktivasi->bind_param('isisss', $id_member, $id_trx, $id_paket, $tgl_mulai, $tgl_berakhir, $jenis_aktivasi);
            if (!$stmt_aktivasi->execute()) {
                throw new Exception("Gagal simpan aktivasi: " . $stmt_aktivasi->error);
            }
            $stmt_aktivasi->close();

            $sql_update = "UPDATE tbl_member SET status_membership = 'Aktif', id_paket_aktif = ?, tgl_mulai_aktif = ?, tgl_kedaluwarsa = ? WHERE id_member = ?";
            $stmt_update = $con->prepare($sql_update);
            $stmt_update->bind_param('issi', $id_paket, $tgl_mulai, $tgl_berakhir, $id_member);
            if (!$stmt_update->execute()) {
                throw new Exception("Gagal update member: " . $stmt_update->error);
            }
            $stmt_update->close();
        }

        $con->commit();
        echo json_encode(['success' => true, 'id' => $id_trx, 'message' => 'Transaksi berhasil.']);
    } catch (Exception $e) {
        $con->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

    $con->close();
    exit;
}

echo json_encode(['success' => false, 'error' => 'Akses tidak valid.']);
$con->close();
