<?php
// get_data_nota.php
require "../../../setting/session.php";
require "../../../setting/koneksi.php";

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID Transaksi tidak valid']);
    exit;
}

$id_transaksi = $_GET['id'];

// Query yang benar: ambil data transaksi offline
$sql = "SELECT 
    th.id_transaksi,
    DATE_FORMAT(th.tgl_transaksi, '%d/%m/%Y %H:%i') as tgl_transaksi,
    th.total,
    th.metode_pembayaran,
    th.jumlah_bayar,
    th.kembalian,
    COALESCE(m.nama, 'Pelanggan Umum') as nama_member,
    u.username as nama_kasir
FROM tbl_transaksi_offline th
LEFT JOIN tbl_member m ON th.id_member = m.id_member
LEFT JOIN tbl_user u ON th.id_kasir = u.id_user
WHERE th.id_transaksi = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param("s", $id_transaksi);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'error' => 'Transaksi tidak ditemukan']);
}

$stmt->close();
$con->close();
