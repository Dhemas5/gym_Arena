<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";

header('Content-Type: application/json');
$action = $_REQUEST['action'] ?? '';

if ($action === 'simpan') {
    // Sanitasi input
    $nama = mysqli_real_escape_string($con, trim($_POST['nama_paket']));
    $kategori = mysqli_real_escape_string($con, $_POST['id_kategori']);
    $deskripsi = mysqli_real_escape_string($con, trim($_POST['deskripsi']));
    $harga = (int)$_POST['harga'];
    $durasi = (int)$_POST['durasi_hari'];

    // Validasi
    if (empty($nama) || empty($kategori) || $harga <= 0 || !$durasi) {
        echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi dengan benar!']);
        exit;
    }

    // Validasi durasi yang diizinkan
    if (!in_array($durasi, [1, 30, 90, 180, 365])) {
        echo json_encode(['status' => 'error', 'message' => 'Tipe durasi tidak valid!']);
        exit;
    }

    // Cek duplikat nama paket
    $cek = mysqli_query($con, "SELECT id_paket FROM tbl_paket WHERE nama_paket = '$nama'");
    if (mysqli_num_rows($cek) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nama paket sudah digunakan!']);
        exit;
    }

    // Insert
    $sql = "INSERT INTO tbl_paket (nama_paket, id_kategori, deskripsi, harga, durasi_hari) 
            VALUES ('$nama', '$kategori', '$deskripsi', $harga, $durasi)";

    if (mysqli_query($con, $sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Paket berhasil ditambahkan!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan: ' . mysqli_error($con)]);
    }
} elseif ($action === 'update') {
    // Sanitasi input
    $id = (int)$_POST['id_paket'];
    $nama = mysqli_real_escape_string($con, trim($_POST['nama_paket']));
    $kategori = mysqli_real_escape_string($con, $_POST['id_kategori']);
    $deskripsi = mysqli_real_escape_string($con, trim($_POST['deskripsi']));
    $harga = (int)$_POST['harga'];
    $durasi = (int)$_POST['durasi_hari'];

    // Validasi
    if (empty($nama) || empty($kategori) || $harga <= 0 || !$durasi || !$id) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap!']);
        exit;
    }

    if (!in_array($durasi, [1, 30, 90, 180, 365])) {
        echo json_encode(['status' => 'error', 'message' => 'Tipe durasi tidak valid!']);
        exit;
    }

    // Cek duplikat nama (kecuali dirinya sendiri)
    $cek = mysqli_query($con, "SELECT id_paket FROM tbl_paket WHERE nama_paket = '$nama' AND id_paket != $id");
    if (mysqli_num_rows($cek) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nama paket sudah digunakan!']);
        exit;
    }

    // Update
    $sql = "UPDATE tbl_paket SET 
            nama_paket = '$nama',
            id_kategori = '$kategori',
            deskripsi = '$deskripsi',
            harga = $harga,
            durasi_hari = $durasi
            WHERE id_paket = $id";

    if (mysqli_query($con, $sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Paket berhasil diperbarui!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui: ' . mysqli_error($con)]);
    }
} elseif ($action === 'hapus') {
    $id = (int)$_GET['id'];

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak valid!']);
        exit;
    }

    $sql = "DELETE FROM tbl_paket WHERE id_paket = $id";
    if (mysqli_query($con, $sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Paket berhasil dihapus!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus: ' . mysqli_error($con)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid!']);
}
