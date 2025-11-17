<?php
require "../../../setting/session.php";
checkSession("admin"); // hanya admin boleh masuk

require "../../../setting/koneksi.php"; // Menggunakan $con

// Cek koneksi database
if (!isset($con) || $con->connect_error) {
    $_SESSION['error'] = "Koneksi database gagal: " . $con->connect_error;
    header("Location: testimoni.php");
    exit();
}

// Cek aksi yang dilakukan
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if (empty($action) || empty($id)) {
    $_SESSION['error'] = "Aksi atau ID tidak valid!";
    header("Location: testimoni.php");
    exit();
}

// Validasi ID
$id = intval($id);

switch ($action) {
    case 'publish':
        $query = "UPDATE tbl_testimoni SET status = 'publish' WHERE id = ?";
        break;
        
    case 'pending':
        $query = "UPDATE tbl_testimoni SET status = 'pending' WHERE id = ?";
        break;
        
    case 'delete':
        $query = "DELETE FROM tbl_testimoni WHERE id = ?";
        break;
        
    default:
        $_SESSION['error'] = "Aksi tidak dikenali!";
        header("Location: testimoni.php");
        exit();
}

// Eksekusi query menggunakan prepared statement
$stmt = $con->prepare($query);
if ($stmt) {
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($action == 'publish') {
            $_SESSION['success'] = "Testimoni berhasil dipublish!";
        } elseif ($action == 'pending') {
            $_SESSION['success'] = "Testimoni berhasil diubah status menjadi pending!";
        } elseif ($action == 'delete') {
            $_SESSION['success'] = "Testimoni berhasil dihapus!";
        }
    } else {
        $_SESSION['error'] = "Gagal melakukan aksi: " . $con->error;
    }
    
    $stmt->close();
} else {
    $_SESSION['error'] = "Error prepare statement: " . $con->error;
}

$con->close();
header("Location: testimoni.php");
exit();
?>