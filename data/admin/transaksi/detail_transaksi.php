<?php
// detail_transaksi.php → API untuk ambil detail transaksi
require "../../../setting/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID transaksi tidak diberikan']);
    exit;
}

$id_transaksi = $_GET['id'];

// Ambil header
$stmt = $con->prepare("
    SELECT th.*, m.nama AS nama_member, u.username AS nama_kasir 
    FROM tbl_transaksi_header th
    LEFT JOIN tbl_member m ON th.id_member = m.id_member
    LEFT JOIN tbl_user u ON th.id_user_kasir = u.id_user
    WHERE th.id_transaksi = ?
");
$stmt->bind_param('s', $id_transaksi);
$stmt->execute();
$header = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$header) {
    echo json_encode(['success' => false, 'error' => 'Transaksi tidak ditemukan']);
    exit;
}

// Ambil detail
$stmt2 = $con->prepare("
    SELECT td.*, p.nama_paket 
    FROM tbl_transaksi_detail td
    JOIN tbl_paket p ON td.id_paket = p.id_paket
    WHERE td.id_transaksi = ?
");
$stmt2->bind_param('s', $id_transaksi);
$stmt2->execute();
$details = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

echo json_encode(['success' => true, 'header' => $header, 'details' => $details]);
exit;
