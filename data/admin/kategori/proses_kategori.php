<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";
header('Content-Type: application/json');

// Path untuk upload foto
$uploadDir = '../../../data/admin/img/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$action = $_REQUEST['action'] ?? '';

function uploadFoto($file, $uploadDir, $fotoLama = '') {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 5 * 1024 * 1024;
    
    if ($file['error'] != UPLOAD_ERR_OK) {
        if ($file['error'] == UPLOAD_ERR_NO_FILE) {
            return ['status' => 'no_file', 'filename' => $fotoLama];
        }
        return ['status' => 'error', 'message' => 'Error upload file: ' . $file['error']];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['status' => 'error', 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau JPEG.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['status' => 'error', 'message' => 'Ukuran file maksimal 5MB.'];
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'kategori_' . time() . '_' . uniqid() . '.' . $ext;
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Hapus foto lama jika ada
        if (!empty($fotoLama) && file_exists($uploadDir . $fotoLama) && $fotoLama != 'default.jpg') {
            unlink($uploadDir . $fotoLama);
        }
        return ['status' => 'success', 'filename' => $filename];
    } else {
        return ['status' => 'error', 'message' => 'Gagal mengupload file.'];
    }
}

if ($action == 'simpan') {
    $nama = htmlspecialchars($_POST['nama_kategori']);
    $deskripsi = htmlspecialchars($_POST['deskripsi'] ?? '');
    
    // Cek duplikat nama
    $cek = mysqli_prepare($con, "SELECT * FROM tbl_kategori WHERE nama_kategori=?");
    mysqli_stmt_bind_param($cek, "s", $nama);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);
    
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nama kategori sudah ada!']);
    } else {
        // Upload foto
        $foto = 'default.jpg';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] != UPLOAD_ERR_NO_FILE) {
            $uploadResult = uploadFoto($_FILES['foto'], $uploadDir);
            if ($uploadResult['status'] == 'error') {
                echo json_encode(['status' => 'error', 'message' => $uploadResult['message']]);
                exit;
            }
            $foto = $uploadResult['filename'];
        }
        
        // Insert data
        $insert = mysqli_prepare($con, "INSERT INTO tbl_kategori (nama_kategori, deskripsi, foto) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($insert, "sss", $nama, $deskripsi, $foto);
        
        if (mysqli_stmt_execute($insert)) {
            echo json_encode(['status' => 'success', 'message' => 'Kategori berhasil ditambahkan.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan kategori: ' . mysqli_error($con)]);
        }
        mysqli_stmt_close($insert);
    }
    mysqli_stmt_close($cek);
}

if ($action == 'update') {
    $id = $_POST['id_kategori'];
    $nama = htmlspecialchars($_POST['nama_kategori']);
    $deskripsi = htmlspecialchars($_POST['deskripsi'] ?? '');
    $fotoLama = $_POST['foto_lama'] ?? '';
    
    // Cek duplikat nama
    $cek = mysqli_prepare($con, "SELECT * FROM tbl_kategori WHERE nama_kategori=? AND id_kategori!=?");
    mysqli_stmt_bind_param($cek, "si", $nama, $id);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);
    
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nama kategori sudah ada!']);
    } else {
        // Upload foto baru jika ada
        $foto = $fotoLama;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] != UPLOAD_ERR_NO_FILE) {
            $uploadResult = uploadFoto($_FILES['foto'], $uploadDir, $fotoLama);
            if ($uploadResult['status'] == 'error') {
                echo json_encode(['status' => 'error', 'message' => $uploadResult['message']]);
                exit;
            }
            $foto = $uploadResult['filename'];
        }
        
        // Update data
        $update = mysqli_prepare($con, "UPDATE tbl_kategori SET nama_kategori=?, deskripsi=?, foto=? WHERE id_kategori=?");
        mysqli_stmt_bind_param($update, "sssi", $nama, $deskripsi, $foto, $id);
        
        if (mysqli_stmt_execute($update)) {
            echo json_encode(['status' => 'success', 'message' => 'Kategori berhasil diperbarui.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui kategori: ' . mysqli_error($con)]);
        }
        mysqli_stmt_close($update);
    }
    mysqli_stmt_close($cek);
}

if ($action == 'hapus') {
    $id = $_GET['id'];
    
    // Ambil data foto sebelum hapus
    $query = mysqli_prepare($con, "SELECT foto FROM tbl_kategori WHERE id_kategori=?");
    mysqli_stmt_bind_param($query, "i", $id);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($query);
    
    // Hapus data dari database
    $delete = mysqli_prepare($con, "DELETE FROM tbl_kategori WHERE id_kategori=?");
    mysqli_stmt_bind_param($delete, "i", $id);
    
    if (mysqli_stmt_execute($delete)) {
        // Hapus file foto jika bukan default
        if (!empty($data['foto']) && $data['foto'] != 'default.jpg' && file_exists($uploadDir . $data['foto'])) {
            unlink($uploadDir . $data['foto']);
        }
        echo json_encode(['status' => 'success', 'message' => 'Kategori berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus kategori: ' . mysqli_error($con)]);
    }
    mysqli_stmt_close($delete);
}