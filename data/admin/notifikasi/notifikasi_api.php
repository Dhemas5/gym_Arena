<?php
session_start();
require "../../../setting/koneksi.php";

header('Content-Type: application/json');

// Cek apakah user adalah admin/staff
if (!isset($_SESSION['user_level']) || ($_SESSION['user_level'] != 'admin' && $_SESSION['user_level'] != 'staff')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Cek koneksi database
if (!$con) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
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
        case 'get_unread_count':
            getUnreadCount();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getNotifications() {
    global $con;
    
    $limit = intval($_GET['limit'] ?? 10);
    $limit = min($limit, 50); // Batasi maksimal 50
    
    // Query untuk mendapatkan notifikasi dengan status dibaca
    $query = "SELECT id, judul, pesan, tipe, dibaca, dibuat_pada 
              FROM tbl_notifikasi 
              ORDER BY dibuat_pada DESC 
              LIMIT ?";
    
    if ($stmt = $con->prepare($query)) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        $unread_count = 0;
        
        while ($row = $result->fetch_assoc()) {
            $notification = [
                'id' => $row['id'],
                'judul' => $row['judul'] ?? ucfirst(str_replace('_', ' ', $row['tipe'])),
                'pesan' => $row['pesan'],
                'tipe' => $row['tipe'],
                'dibaca' => (bool)$row['dibaca'],
                'icon' => getIconByType($row['tipe']),
                'icon_class' => getIconClassByType($row['tipe']),
                'waktu' => formatWaktu($row['dibuat_pada']),
                'timestamp' => $row['dibuat_pada']
            ];
            
            $notifications[] = $notification;
            
            if (!$row['dibaca']) {
                $unread_count++;
            }
        }
        
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unread_count,
            'total' => count($notifications)
        ]);
    } else {
        throw new Exception('Failed to prepare statement');
    }
}

function markAsRead() {
    global $con;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? $_POST['id'] ?? 0;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID notifikasi tidak valid']);
        return;
    }
    
    $query = "UPDATE tbl_notifikasi SET dibaca = 1, dibaca_pada = NOW() WHERE id = ?";
    
    if ($stmt = $con->prepare($query)) {
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        
        if ($success && $affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Notifikasi ditandai sebagai dibaca',
                'id' => $id
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Gagal menandai notifikasi sebagai dibaca'
            ]);
        }
    } else {
        throw new Exception('Failed to prepare statement');
    }
}

function markAllAsRead() {
    global $con;
    
    $query = "UPDATE tbl_notifikasi SET dibaca = 1, dibaca_pada = NOW() WHERE dibaca = 0";
    
    if ($stmt = $con->prepare($query)) {
        $success = $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        
        if ($success) {
            echo json_encode([
                'success' => true, 
                'message' => 'Semua notifikasi telah ditandai sudah dibaca',
                'affected_rows' => $affected_rows
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Gagal menandai semua notifikasi sebagai dibaca'
            ]);
        }
    } else {
        throw new Exception('Failed to prepare statement');
    }
}

function getUnreadCount() {
    global $con;
    
    $query = "SELECT COUNT(*) as unread_count FROM tbl_notifikasi WHERE dibaca = 0";
    
    if ($stmt = $con->prepare($query)) {
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'unread_count' => (int)$data['unread_count']
        ]);
    } else {
        throw new Exception('Failed to prepare statement');
    }
}

function getIconByType($tipe) {
    $tipe = strtolower($tipe ?? '');
    
    if (strpos($tipe, 'member') !== false) return 'user-plus';
    if (strpos($tipe, 'transaksi') !== false) return 'money-bill-wave';
    if (strpos($tipe, 'pembayaran') !== false) return 'credit-card';
    if (strpos($tipe, 'jadwal') !== false) return 'calendar';
    if (strpos($tipe, 'sistem') !== false) return 'cogs';
    
    return 'bell';
}

function getIconClassByType($tipe) {
    $tipe = strtolower($tipe ?? '');
    
    if (strpos($tipe, 'member') !== false) return 'member';
    if (strpos($tipe, 'transaksi') !== false) return 'transaksi';
    if (strpos($tipe, 'pembayaran') !== false) return 'transaksi';
    if (strpos($tipe, 'jadwal') !== false) return 'lainnya';
    if (strpos($tipe, 'sistem') !== false) return 'lainnya';
    
    return 'lainnya';
}

function formatWaktu($timestamp) {
    if (empty($timestamp)) return 'Baru saja';
    
    try {
        $now = new DateTime();
        $waktu = new DateTime($timestamp);
        $diff = $now->diff($waktu);
        
        if ($diff->y > 0) return $diff->y . ' tahun lalu';
        if ($diff->m > 0) return $diff->m . ' bulan lalu';
        if ($diff->d > 0) return $diff->d . ' hari lalu';
        if ($diff->h > 0) return $diff->h . ' jam lalu';
        if ($diff->i > 0) return $diff->i . ' menit lalu';
        
        return 'Baru saja';
    } catch (Exception $e) {
        return 'Waktu tidak valid';
    }
}

// Close database connection
if (is_object($con)) {
    $con->close();
}
?>