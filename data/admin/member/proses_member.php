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

    // Update data member
    $stmt = $con->prepare("UPDATE tbl_member SET nama=?, email=?, no_hp=?, alamat=?, status_akun=? WHERE id_member=?");
    $stmt->bind_param('sssssi', $nama, $email, $no_hp, $alamat, $status_akun, $id);

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
    $cek = $con->prepare("SELECT id_transaksi FROM tbl_transaksi_header WHERE id_member=? LIMIT 1");
    $cek->bind_param('i', $id);
    $cek->execute();
    if ($cek->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Member memiliki riwayat transaksi, tidak bisa dihapus.']);
        exit;
    }
    $cek->close();

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