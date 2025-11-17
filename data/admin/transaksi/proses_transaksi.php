<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require "../../../setting/koneksi.php";

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'error' => 'Login required']);
    exit;
}

function genId($con)
{
    $prefix = "OFF" . date('Ymd');
    $stmt = $con->prepare("SELECT id_transaksi FROM tbl_transaksi_offline WHERE id_transaksi LIKE ? ORDER BY id_transaksi DESC LIMIT 1");
    $like = $prefix . '%';
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $num = $res ? intval(substr($res['id_transaksi'], -3)) + 1 : 1;
    return $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

try {
    $con->autocommit(false);

    $id_transaksi = genId($con);
    $id_kasir     = $_SESSION['id_user'];
    $id_member    = !empty($_POST['id_member']) ? intval($_POST['id_member']) : null;
    $id_paket     = intval($_POST['id_paket']);
    $harga_paket  = floatval($_POST['harga_paket']);
    $diskon       = floatval($_POST['diskon'] ?? 0);
    $dibayar      = floatval($_POST['jumlah_dibayar']);
    $metode       = strtoupper($_POST['metode_pembayaran'] ?? 'TUNAI');
    $durasi_hari  = intval($_POST['durasi_hari'] ?? 30);

    if ($id_paket <= 0 || $harga_paket <= 0) {
        throw new Exception("Paket tidak valid");
    }

    $grand_total = max(0, $harga_paket - $diskon);
    if ($dibayar < $grand_total) {
        throw new Exception("Pembayaran kurang dari total");
    }
    $kembalian = $dibayar - $grand_total;

    // Simpan transaksi
    $stmt = $con->prepare("INSERT INTO tbl_transaksi_offline 
        (id_transaksi, tgl_transaksi, id_member, id_kasir, id_paket, total, metode_pembayaran, jumlah_bayar, kembalian)
        VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siiidddd", $id_transaksi, $id_member, $id_kasir, $id_paket, $grand_total, $metode, $dibayar, $kembalian);
    if (!$stmt->execute()) throw new Exception("Gagal simpan transaksi");
    $stmt->close();

    $member_aktif = false;
    if ($id_member) {
        $tgl_mulai = date('Y-m-d H:i:s');
        $tgl_berakhir = date('Y-m-d 23:59:59', strtotime("+$durasi_hari days"));

        $stmt = $con->prepare("INSERT INTO tbl_membership 
            (id_member, id_transaksi, id_paket, tgl_mulai, tgl_berakhir, sumber)
            VALUES (?, ?, ?, ?, ?, 'offline')");
        $stmt->bind_param("isiis", $id_member, $id_transaksi, $id_paket, $tgl_mulai, $tgl_berakhir);
        $stmt->execute();
        $stmt->close();

        $con->query("UPDATE tbl_member SET membership_status = 'aktif' WHERE id_member = $id_member");
        $member_aktif = true;
    }

    $con->commit();

    echo json_encode([
        'success' => true,
        'id_transaksi' => $id_transaksi,
        'grand_total' => $grand_total,
        'kembalian' => $kembalian,
        'member_aktif' => $member_aktif
    ]);
} catch (Exception $e) {
    $con->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
