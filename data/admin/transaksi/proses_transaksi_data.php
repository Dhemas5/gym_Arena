<?php
require "../../../setting/koneksi.php";
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'get_transaksi') {
    $tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-d');
    $tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

    $sql = "SELECT 
                h.id_transaksi,
                DATE_FORMAT(h.tgl_transaksi, '%d %b %Y %H:%i') as tanggal,
                u.username as kasir,
                m.nama as member,
                h.metode_pembayaran,
                h.sub_total,
                h.potongan_diskon_global,
                h.grand_total,
                h.jumlah_dibayar_tunai,
                h.jumlah_kembalian,
                h.keterangan
            FROM tbl_transaksi_header h
            LEFT JOIN tbl_user u ON h.id_user_kasir = u.id_user
            LEFT JOIN tbl_member m ON h.id_member = m.id_member
            WHERE DATE(h.tgl_transaksi) BETWEEN ? AND ?
            ORDER BY h.tgl_transaksi DESC";

    $stmt = $con->prepare($sql);
    $stmt->bind_param('ss', $tgl_awal, $tgl_akhir);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

if ($action === 'get_detail') {
    $id = $_GET['id'] ?? '';

    // Header
    $sql_header = "SELECT 
        h.*, u.username as kasir, m.nama as member,
        DATE_FORMAT(h.tgl_transaksi, '%d %b %Y %H:%i') as tanggal
        FROM tbl_transaksi_header h
        LEFT JOIN tbl_user u ON h.id_user_kasir = u.id_user
        LEFT JOIN tbl_member m ON h.id_member = m.id_member
        WHERE h.id_transaksi = ?";
    $stmt_h = $con->prepare($sql_header);
    $stmt_h->bind_param('s', $id);
    $stmt_h->execute();
    $header = $stmt_h->get_result()->fetch_assoc();

    // Detail
    $sql_detail = "SELECT 
        d.*, p.nama_paket
        FROM tbl_transaksi_detail d
        JOIN tbl_paket p ON d.id_paket = p.id_paket
        WHERE d.id_transaksi = ?";
    $stmt_d = $con->prepare($sql_detail);
    $stmt_d->bind_param('s', $id);
    $stmt_d->execute();
    $result_d = $stmt_d->get_result();
    $detail = [];
    while ($row = $result_d->fetch_assoc()) {
        $detail[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'header' => $header,
        'detail' => $detail
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid.']);
