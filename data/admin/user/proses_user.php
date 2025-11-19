<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";
header('Content-Type: application/json'); // Mengembalikan respons dalam format JSON

// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display errors untuk production

$action = $_REQUEST['action'] ?? '';

// ====== PROSES UPDATE USER (action=update) ======
if ($action == 'update') {
    $id = $_POST['id_user'] ?? 0;
    $username = htmlspecialchars(trim($_POST['username']));
    $nama_lengkap = htmlspecialchars(trim($_POST['nama_lengkap'])); // DIPERBAIKI: nama_lengkap
    $email = htmlspecialchars(trim($_POST['email']));
    $role = $_POST['role'] ?? 'staff'; // DIPERBAIKI: tambah role
    $password_new = $_POST['password'] ?? ''; // Kosongkan jika tidak diubah

    // Validasi ID
    if (empty($id) || !is_numeric($id)) {
        echo json_encode(['status' => 'error', 'message' => 'ID user tidak valid!']);
        exit;
    }

    // Validasi input required
    if (empty($username) || empty($nama_lengkap) || empty($email) || empty($role)) {
        echo json_encode(['status' => 'error', 'message' => 'Username, nama lengkap, email, dan role wajib diisi!']);
        exit;
    }

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Format email tidak valid!']);
        exit;
    }

    // Validasi role
    if (!in_array($role, ['admin', 'staff'])) {
        echo json_encode(['status' => 'error', 'message' => 'Role tidak valid!']);
        exit;
    }

    // Validasi panjang password jika diisi
    if (!empty($password_new) && strlen($password_new) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Password minimal 6 karakter!']);
        exit;
    }

    // Cek duplikasi username (kecuali user itu sendiri)
    $cek = mysqli_prepare($con, "SELECT id_user FROM tbl_user WHERE username = ? AND id_user != ?");
    if (!$cek) {
        echo json_encode(['status' => 'error', 'message' => 'Error sistem: ' . mysqli_error($con)]);
        exit;
    }
    mysqli_stmt_bind_param($cek, "si", $username, $id);
    
    if (!mysqli_stmt_execute($cek)) {
        echo json_encode(['status' => 'error', 'message' => 'Error sistem: ' . mysqli_stmt_error($cek)]);
        mysqli_stmt_close($cek);
        exit;
    }
    
    $result = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan oleh user lain!']);
        mysqli_stmt_close($cek);
        exit;
    }
    mysqli_stmt_close($cek);

    // Cek duplikasi email (kecuali user itu sendiri)
    $cek_email = mysqli_prepare($con, "SELECT id_user FROM tbl_user WHERE email = ? AND id_user != ?");
    if (!$cek_email) {
        echo json_encode(['status' => 'error', 'message' => 'Error sistem: ' . mysqli_error($con)]);
        exit;
    }
    mysqli_stmt_bind_param($cek_email, "si", $email, $id);
    
    if (!mysqli_stmt_execute($cek_email)) {
        echo json_encode(['status' => 'error', 'message' => 'Error sistem: ' . mysqli_stmt_error($cek_email)]);
        mysqli_stmt_close($cek_email);
        exit;
    }
    
    $result_email = mysqli_stmt_get_result($cek_email);

    if (mysqli_num_rows($result_email) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email sudah digunakan oleh user lain!']);
        mysqli_stmt_close($cek_email);
        exit;
    }
    mysqli_stmt_close($cek_email);

    try {
        if (!empty($password_new)) {
            // Update dengan password baru - DIPERBAIKI: sesuaikan dengan struktur database
            $password_hashed = password_hash($password_new, PASSWORD_DEFAULT);
            $update = mysqli_prepare($con, "UPDATE tbl_user SET username = ?, password = ?, role = ?, nama_lengkap = ?, email = ? WHERE id_user = ?");
            
            if (!$update) {
                throw new Exception('Error prepare statement: ' . mysqli_error($con));
            }
            
            $bind_result = mysqli_stmt_bind_param($update, "sssssi", $username, $password_hashed, $role, $nama_lengkap, $email, $id);
            if (!$bind_result) {
                throw new Exception('Error bind param: ' . mysqli_stmt_error($update));
            }
        } else {
            // Update tanpa password baru - DIPERBAIKI: sesuaikan dengan struktur database
            $update = mysqli_prepare($con, "UPDATE tbl_user SET username = ?, role = ?, nama_lengkap = ?, email = ? WHERE id_user = ?");
            
            if (!$update) {
                throw new Exception('Error prepare statement: ' . mysqli_error($con));
            }
            
            $bind_result = mysqli_stmt_bind_param($update, "ssssi", $username, $role, $nama_lengkap, $email, $id);
            if (!$bind_result) {
                throw new Exception('Error bind param: ' . mysqli_stmt_error($update));
            }
        }

        $execute_result = mysqli_stmt_execute($update);
        if (!$execute_result) {
            throw new Exception('Error execute: ' . mysqli_stmt_error($update));
        }

        // Cek apakah data benar-benar terupdate
        if (mysqli_stmt_affected_rows($update) > 0) {
            echo json_encode(['status' => 'success', 'message' => 'User berhasil diupdate!']);
        } else {
            // Tidak ada perubahan data
            echo json_encode(['status' => 'success', 'message' => 'Tidak ada perubahan data.']);
        }
        
        mysqli_stmt_close($update);

    } catch (Exception $e) {
        error_log("Update User Error: " . $e->getMessage());
        
        // Close statement jika masih terbuka
        if (isset($update)) {
            mysqli_stmt_close($update);
        }
        
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
    }
}

// ====== PROSES HAPUS USER (action=hapus) ======
if ($action == 'hapus') {
    $id = $_GET['id'] ?? 0;
    
    // Validasi ID
    if (empty($id) || !is_numeric($id)) {
        echo json_encode(['status' => 'error', 'message' => 'ID user tidak valid!']);
        exit;
    }
    
    // Cek apakah user ada
    $cek = mysqli_prepare($con, "SELECT id_user, username FROM tbl_user WHERE id_user = ?");
    if (!$cek) {
        echo json_encode(['status' => 'error', 'message' => 'Error sistem: ' . mysqli_error($con)]);
        exit;
    }
    mysqli_stmt_bind_param($cek, "i", $id);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);
    
    if (mysqli_num_rows($result) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan!']);
        mysqli_stmt_close($cek);
        exit;
    }
    mysqli_stmt_close($cek);
    
    // Hapus user
    $delete = mysqli_prepare($con, "DELETE FROM tbl_user WHERE id_user = ?");
    if (!$delete) {
        echo json_encode(['status' => 'error', 'message' => 'Error sistem: ' . mysqli_error($con)]);
        exit;
    }
    mysqli_stmt_bind_param($delete, "i", $id);

    if (mysqli_stmt_execute($delete)) {
        echo json_encode(['status' => 'success', 'message' => 'User berhasil dihapus!']);
    } else {
        error_log("Gagal hapus user: " . mysqli_error($con));
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus user: ' . mysqli_stmt_error($delete)]);
    }
    mysqli_stmt_close($delete);
}

// Jika action tidak dikenali
if (!in_array($action, ['update', 'hapus'])) {
    echo json_encode(['status' => 'error', 'message' => 'Action tidak valid!']);
    exit;
}
?>