<?php
// proses_member.php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

// === UPDATE MEMBER ===
if ($action == 'update') {
    $id = intval($_POST['id_member'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak valid.']);
        exit;
    }

    $nama = htmlspecialchars($_POST['nama'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $no_hp = htmlspecialchars($_POST['no_hp'] ?? '');
    $alamat = htmlspecialchars($_POST['alamat'] ?? '');
    $status = in_array($_POST['status_akun'], ['aktif', 'nonaktif']) ? $_POST['status_akun'] : 'nonaktif';
    $password = $_POST['password'] ?? '';

    if (empty($nama) || empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Nama dan email wajib diisi.']);
        exit;
    }

    // Cek email duplikat (kecuali diri sendiri)
    $cek = $con->prepare("SELECT id_member FROM tbl_member WHERE email = ? AND id_member != ?");
    $cek->bind_param('si', $email, $id);
    $cek->execute();
    $result = $cek->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email sudah digunakan member lain.']);
        $cek->close();
        exit;
    }
    $cek->close();

    // Update
    if (!empty($password)) {
        $pass_hash = md5($password);
        $sql = "UPDATE tbl_member SET nama=?, email=?, password=?, no_hp=?, alamat=?, status_akun=? WHERE id_member=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('ssssssi', $nama, $email, $pass_hash, $no_hp, $alamat, $status, $id);
    } else {
        $sql = "UPDATE tbl_member SET nama=?, email=?, no_hp=?, alamat=?, status_akun=? WHERE id_member=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('sssssi', $nama, $email, $no_hp, $alamat, $status, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Member berhasil diperbarui.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update: ' . $stmt->error]);
    }
    $stmt->close();
    exit;
}

// === HAPUS MEMBER ===
if ($action == 'hapus') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak valid.']);
        exit;
    }

    // Cek apakah member punya transaksi
    $cek = $con->prepare("SELECT id_transaksi FROM tbl_transaksi_header WHERE id_member = ? LIMIT 1");
    $cek->bind_param('i', $id);
    $cek->execute();
    $result = $cek->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Member memiliki riwayat transaksi. Tidak bisa dihapus.']);
        $cek->close();
        exit;
    }
    $cek->close();

    $stmt = $con->prepare("DELETE FROM tbl_member WHERE id_member = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Member berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal hapus: ' . $stmt->error]);
    }
    $stmt->close();
    exit;
}

// === AKSES TIDAK VALID ===
echo json_encode(['status' => 'error', 'message' => 'Aksi tidak diizinkan.']);
exit;
