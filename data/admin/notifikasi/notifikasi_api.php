<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action == 'get_notifications') {
    $limit = intval($_GET['limit'] ?? 10);
    
    // Ambil notifikasi terbaru
    $query = $con->prepare("
        SELECT * FROM tbl_notifikasi 
        ORDER BY dibuat_pada DESC 
        LIMIT ?
    ");
    $query->bind_param('i', $limit);
    $query->execute();
    $result = $query->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id_notifikasi'],
            'judul' => $row['judul'],
            'pesan' => $row['pesan'],
            'tipe' => $row['tipe'],
            'waktu' => waktu_lalu($row['dibuat_pada']),
            'dibaca' => (bool)$row['dibaca'],
            'icon' => get_icon_by_type($row['tipe'])
        ];
    }
    
    // Hitung jumlah notifikasi belum dibaca
    $count_query = $con->query("SELECT COUNT(*) as total FROM tbl_notifikasi WHERE dibaca = 0");
    $count_data = $count_query->fetch_assoc();
    $unread_count = $count_data['total'];
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unread_count
    ]);
    exit;
}

if ($action == 'mark_all_read') {
    $con->query("UPDATE tbl_notifikasi SET dibaca = 1 WHERE dibaca = 0");
    echo json_encode(['success' => true, 'message' => 'Semua notifikasi telah ditandai sudah dibaca']);
    exit;
}

if ($action == 'mark_read') {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $con->query("UPDATE tbl_notifikasi SET dibaca = 1 WHERE id_notifikasi = $id");
    }
    echo json_encode(['success' => true]);
    exit;
}

// Fungsi helper untuk format waktu
function waktu_lalu($timestamp) {
    $selisih = time() - strtotime($timestamp);
    
    if ($selisih < 60) return 'Baru saja';
    if ($selisih < 3600) return floor($selisih / 60) . ' menit lalu';
    if ($selisih < 86400) return floor($selisih / 3600) . ' jam lalu';
    if ($selisih < 2592000) return floor($selisih / 86400) . ' hari lalu';
    
    return date('d M Y', strtotime($timestamp));
}

// Fungsi helper untuk icon notifikasi
function get_icon_by_type($tipe) {
    switch ($tipe) {
        case 'member_baru': return 'fas fa-user-plus';
        case 'transaksi': return 'fas fa-shopping-cart';
        default: return 'fas fa-bell';
    }
}

echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
?>