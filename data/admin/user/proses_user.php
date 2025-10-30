<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";
header('Content-Type: application/json'); // Mengembalikan respons dalam format JSON

$action = $_REQUEST['action'] ?? '';

// ====== PROSES TAMBAH USER (action=simpan) ======
if ($action == 'simpan') {
    $username = htmlspecialchars($_POST['username']);
    $password_plain = $_POST['password'];
    $nama_lengkap = htmlspecialchars($_POST['nama_lengkap']);
    $email = htmlspecialchars($_POST['email']);
    // Variabel $role dihapus

    if (empty($username) || empty($password_plain) || empty($nama_lengkap) || empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi!']);
        exit;
    }

    // Hash password menggunakan BCRYPT (lebih aman dari md5)
    $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

    // Cek username sudah ada
    $cek = mysqli_prepare($con, "SELECT id_user FROM tbl_user WHERE username = ?");
    mysqli_stmt_bind_param($cek, "s", $username);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan!']);
    } else {
        // Query disederhanakan tanpa 'role'
        $insert = mysqli_prepare($con, "INSERT INTO tbl_user(username, password, nama_lengkap, email) VALUES(?, ?, ?, ?)");
        // bind_param disesuaikan menjadi 4 string (ssss)
        mysqli_stmt_bind_param($insert, "ssss", $username, $password_hashed, $nama_lengkap, $email);

        if (mysqli_stmt_execute($insert)) {
            echo json_encode(['status' => 'success', 'message' => 'User berhasil ditambahkan!']);
        } else {
            error_log("Gagal insert user: " . mysqli_error($con));
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan user: ' . mysqli_error($con)]);
        }
        mysqli_stmt_close($insert);
    }
    mysqli_stmt_close($cek);
}

// ====== PROSES UPDATE USER (action=update) ======
if ($action == 'update') {
    $id = $_POST['id_user'];
    $username = htmlspecialchars($_POST['username']);
    $nama_lengkap = htmlspecialchars($_POST['nama_lengkap']);
    $email = htmlspecialchars($_POST['email']);
    $password_new = $_POST['password'] ?? ''; // Kosongkan jika tidak diubah
    // Variabel $role dihapus

    // Cek duplikasi username (kecuali user itu sendiri)
    $cek = mysqli_prepare($con, "SELECT id_user FROM tbl_user WHERE username = ? AND id_user != ?");
    mysqli_stmt_bind_param($cek, "si", $username, $id);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan oleh user lain!']);
    } else {
        if (!empty($password_new)) {
            // Update dengan password baru (tanpa role)
            $password_hashed = password_hash($password_new, PASSWORD_DEFAULT);
            $update = mysqli_prepare($con, "UPDATE tbl_user SET username=?, password=?, nama_lengkap=?, email=? WHERE id_user=?");
            // bind_param disesuaikan menjadi ssssi
            mysqli_stmt_bind_param($update, "ssssi", $username, $password_hashed, $nama_lengkap, $email, $id);
        } else {
            // Update tanpa password baru (tanpa role)
            $update = mysqli_prepare($con, "UPDATE tbl_user SET username=?, nama_lengkap=?, email=? WHERE id_user=?");
            // bind_param disesuaikan menjadi sssi
            mysqli_stmt_bind_param($update, "sssi", $username, $nama_lengkap, $email, $id);
        }

        if (mysqli_stmt_execute($update)) {
            echo json_encode(['status' => 'success', 'message' => 'User berhasil diupdate!']);
        } else {
            error_log("Gagal update user: " . mysqli_error($con));
            echo json_encode(['status' => 'error', 'message' => 'Gagal update user: ' . mysqli_error($con)]);
        }
        mysqli_stmt_close($update);
    }
    mysqli_stmt_close($cek);
}

// ====== PROSES HAPUS USER (action=hapus) ======
if ($action == 'hapus') {
    $id = $_GET['id'];
    $delete = mysqli_prepare($con, "DELETE FROM tbl_user WHERE id_user = ?");
    mysqli_stmt_bind_param($delete, "i", $id);

    if (mysqli_stmt_execute($delete)) {
        echo json_encode(['status' => 'success', 'message' => 'User berhasil dihapus!']);
    } else {
        error_log("Gagal hapus user: " . mysqli_error($con));
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus user: ' . mysqli_error($con)]);
    }
    mysqli_stmt_close($delete);
}
