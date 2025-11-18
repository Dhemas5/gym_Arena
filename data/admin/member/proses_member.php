<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

if ($action == 'update') {
    $id = intval($_POST['id_member'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $status_akun = trim($_POST['status_akun'] ?? 'aktif');

    if ($id <= 0 || empty($nama) || empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Nama dan email wajib diisi.']);
        exit;
    }

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Format email tidak valid.']);
        exit;
    }

    // Cek duplikasi email
    $cek = $con->prepare("SELECT id_member FROM tbl_member WHERE email=? AND id_member!=?");
    $cek->bind_param('si', $email, $id);
    $cek->execute();
    if ($cek->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email sudah digunakan.']);
        exit;
    }
    $cek->close();

    // Handle upload foto
    $foto_update = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        
        // Validasi file
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowed_types)) {
            echo json_encode(['status' => 'error', 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.']);
            exit;
        } elseif ($file['size'] > $max_size) {
            echo json_encode(['status' => 'error', 'message' => 'Ukuran file terlalu besar. Maksimal 5MB.']);
            exit;
        } else {
            // Generate nama file unik
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'member_' . $id . '_' . time() . '.' . $ext;
            $upload_path = '../../uploads/member/' . $new_filename;
            
            // Buat folder jika belum ada
            if (!is_dir('../../uploads/member/')) {
                mkdir('../../uploads/member/', 0777, true);
            }
            
            // Hapus foto lama jika ada
            $query_old_foto = $con->prepare("SELECT foto FROM tbl_member WHERE id_member = ?");
            $query_old_foto->bind_param("i", $id);
            $query_old_foto->execute();
            $result_old_foto = $query_old_foto->get_result();
            $old_foto_data = $result_old_foto->fetch_assoc();
            $old_foto = $old_foto_data['foto'] ?? '';
            
            if (!empty($old_foto) && $old_foto !== 'default.jpg') {
                $old_file_path = '../../uploads/member/' . $old_foto;
                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            }
            $query_old_foto->close();
            
            // Upload file baru
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $foto_update = ", foto = '$new_filename'";
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal mengupload file foto.']);
                exit;
            }
        }
    }

    // Update data member
    $sql = "UPDATE tbl_member SET nama=?, email=?, no_hp=?, alamat=?, status_akun? $foto_update WHERE id_member=?";
    $stmt = $con->prepare($sql);
    
    if ($foto_update) {
        $stmt->bind_param('sssssi', $nama, $email, $no_hp, $alamat, $status_akun, $id);
    } else {
        $stmt->bind_param('ssssi', $nama, $email, $no_hp, $alamat, $status_akun, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Data member berhasil diperbarui.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update: ' . $stmt->error]);
    }
    $stmt->close();
    exit;
}

if ($action == 'hapus') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak valid.']);
        exit;
    }

    // Cek apakah member memiliki riwayat transaksi
    $cek = $con->prepare("SELECT id_transaksi FROM tbl_transaksi_offline WHERE id_member=? LIMIT 1");
    $cek->bind_param('i', $id);
    $cek->execute();
    if ($cek->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Member memiliki riwayat transaksi, tidak bisa dihapus.']);
        exit;
    }
    $cek->close();

    // Hapus foto member jika ada
    $query_foto = $con->prepare("SELECT foto FROM tbl_member WHERE id_member = ?");
    $query_foto->bind_param("i", $id);
    $query_foto->execute();
    $result_foto = $query_foto->get_result();
    $foto_data = $result_foto->fetch_assoc();
    $foto = $foto_data['foto'] ?? '';
    
    if (!empty($foto) && $foto !== 'default.jpg') {
        $file_path = '../../uploads/member/' . $foto;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    $query_foto->close();

    // Hapus member
    $stmt = $con->prepare("DELETE FROM tbl_member WHERE id_member=?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Member berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal hapus: ' . $stmt->error]);
    }
    $stmt->close();
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Aksi tidak diizinkan.']);