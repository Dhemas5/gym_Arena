<?php
require "../../../setting/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID transaksi tidak diberikan']);
    exit;
}

$id_transaksi = $_GET['id'];

// Validasi ID transaksi
if (empty($id_transaksi)) {
    echo json_encode(['success' => false, 'error' => 'ID transaksi tidak valid']);
    exit;
}

// Cek apakah tabel transaksi_offline_detail ada
$checkTable = $con->query("SHOW TABLES LIKE 'tbl_transaksi_offline_detail'");
if ($checkTable->num_rows > 0) {
    // Jika ada tabel detail khusus untuk transaksi offline
    $stmt = $con->prepare("
        SELECT th.*, m.nama AS nama_member, u.username AS nama_kasir 
        FROM tbl_transaksi_offline th
        LEFT JOIN tbl_member m ON th.id_member = m.id_member
        LEFT JOIN tbl_user u ON th.id_user_kasir = u.id_user
        WHERE th.id_transaksi = ?
    ");

    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Error preparing statement: ' . $con->error]);
        exit;
    }

    $stmt->bind_param('s', $id_transaksi);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        echo json_encode(['success' => false, 'error' => 'Error executing query: ' . $stmt->error]);
        $stmt->close();
        exit;
    }

    $header = $result->fetch_assoc();
    $stmt->close();

    if (!$header) {
        echo json_encode(['success' => false, 'error' => 'Transaksi tidak ditemukan']);
        exit;
    }

    // Ambil detail dari tabel transaksi_offline_detail
    $stmt2 = $con->prepare("
        SELECT tod.*, p.nama_paket 
        FROM tbl_transaksi_offline_detail tod
        JOIN tbl_paket p ON tod.id_paket = p.id_paket
        WHERE tod.id_transaksi = ?
    ");

    if (!$stmt2) {
        echo json_encode(['success' => false, 'error' => 'Error preparing detail statement: ' . $con->error]);
        exit;
    }

    $stmt2->bind_param('s', $id_transaksi);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if (!$result2) {
        echo json_encode(['success' => false, 'error' => 'Error executing detail query: ' . $stmt2->error]);
        $stmt2->close();
        exit;
    }

    $details = $result2->fetch_all(MYSQLI_ASSOC);
    $stmt2->close();
} else {
    // Jika tidak ada tabel detail khusus, ambil dari header saja (transaksi offline biasanya 1 paket)
    $stmt = $con->prepare("
        SELECT th.*, m.nama AS nama_member, u.username AS nama_kasir, p.nama_paket,
               th.harga_paket as harga_satuan, 1 as qty, 
               th.diskon as potongan_diskon_item, th.harga_paket - th.diskon as sub_total
        FROM tbl_transaksi_offline th
        LEFT JOIN tbl_member m ON th.id_member = m.id_member
        LEFT JOIN tbl_user u ON th.id_user_kasir = u.id_user
        LEFT JOIN tbl_paket p ON th.id_paket = p.id_paket
        WHERE th.id_transaksi = ?
    ");

    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Error preparing statement: ' . $con->error]);
        exit;
    }

    $stmt->bind_param('s', $id_transaksi);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        echo json_encode(['success' => false, 'error' => 'Error executing query: ' . $stmt->error]);
        $stmt->close();
        exit;
    }

    $header = $result->fetch_assoc();
    $stmt->close();

    if (!$header) {
        echo json_encode(['success' => false, 'error' => 'Transaksi tidak ditemukan']);
        exit;
    }

    // Buat detail manual dari data header
    $details = [[
        'nama_paket' => $header['nama_paket'],
        'harga_satuan' => $header['harga_satuan'],
        'qty' => 1,
        'potongan_diskon_item' => $header['potongan_diskon_item'],
        'sub_total' => $header['sub_total']
    ]];

    // Hapus field yang tidak perlu dari header
    unset($header['nama_paket']);
    unset($header['harga_satuan']);
    unset($header['qty']);
    unset($header['potongan_diskon_item']);
    unset($header['sub_total']);
}

// Kirim response
echo json_encode([
    'success' => true,
    'header' => $header,
    'details' => $details
]);

exit;
