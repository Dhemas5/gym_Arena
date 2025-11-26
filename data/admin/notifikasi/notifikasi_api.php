<?php
session_start();
require "../../setting/koneksi.php";

header('Content-Type: application/json');

// Cek apakah user adalah admin/staff
if (!isset($_SESSION['user_level']) || ($_SESSION['user_level'] != 'admin' && $_SESSION['user_level'] != 'staff')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_notifications':
        getNotifications();
        break;
    case 'mark_read':
        markAsRead();
        break;
    case 'mark_all_read':
        markAllAsRead();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
}

function getNotifications() {
    global $con;
    
    $limit = intval($_GET['limit'] ?? 5);
    
    // Query untuk mendapatkan notifikasi
    $query = "SELECT * FROM tbl_notifikasi 
              ORDER BY dibuat_pada DESC 
              LIMIT ?";
    
    if ($stmt = $con->prepare($query)) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        $unread_count = 0;
        
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'id' => $row['id'],
                'judul' => $row['judul'],
                'pesan' => $row['pesan'],
                'tipe' => $row['tipe'],
                'dibaca' => (bool)$row['dibaca'],
                'icon' => getIconByType($row['tipe']),
                'waktu' => formatWaktu($row['dibuat_pada'])
            ];
            
            if (!$row['dibaca']) {
                $unread_count++;
            }
        }
        
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unread_count
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function markAsRead() {
    global $con;
    
    $id = $_POST['id'] ?? 0;
    
    if ($id) {
        $query = "UPDATE tbl_notifikasi SET dibaca = 1, dibaca_pada = NOW() WHERE id = ?";
        if ($stmt = $con->prepare($query)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    echo json_encode(['success' => true]);
}

function markAllAsRead() {
    global $con;
    
    $query = "UPDATE tbl_notifikasi SET dibaca = 1, dibaca_pada = NOW() WHERE dibaca = 0";
    if ($stmt = $con->prepare($query)) {
        $stmt->execute();
        $stmt->close();
    }
    
    echo json_encode(['success' => true, 'message' => 'Semua notifikasi telah ditandai sudah dibaca']);
}

function getIconByType($tipe) {
    switch ($tipe) {
        case 'member_baru': return 'fas fa-user-plus';
        case 'transaksi': return 'fas fa-shopping-cart';
        default: return 'fas fa-bell';
    }
}

function formatWaktu($timestamp) {
    $now = new DateTime();
    $waktu = new DateTime($timestamp);
    $diff = $now->diff($waktu);
    
    if ($diff->y > 0) return $diff->y . ' tahun lalu';
    if ($diff->m > 0) return $diff->m . ' bulan lalu';
    if ($diff->d > 0) return $diff->d . ' hari lalu';
    if ($diff->h > 0) return $diff->h . ' jam lalu';
    if ($diff->i > 0) return $diff->i . ' menit lalu';
    return 'Baru saja';
}
?>