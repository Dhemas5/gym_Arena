<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

if ($action === 'simpan') {
    $nama           = trim($_POST['nama_paket'] ?? '');
    $kategori       = $_POST['id_kategori'] ?? '';
    $deskripsi      = trim($_POST['deskripsi'] ?? '');
    $harga_umum     = intval($_POST['harga_umum'] ?? 0);
    $harga_mahasiswa = intval($_POST['harga_mahasiswa'] ?? 0);
    $durasi         = intval($_POST['durasi_hari'] ?? 0);

    if (empty($nama) || empty($kategori) || $harga_umum <= 0 || $harga_mahasiswa <= 0 || !in_array($durasi, [1, 30, 90, 180, 365])) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap atau tidak valid!']);
        exit;
    }

    // Cek duplikat nama
    $cek = $con->prepare("SELECT id_paket FROM tbl_paket WHERE nama_paket = ?");
    $cek->bind_param('s', $nama);
    $cek->execute();
    if ($cek->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nama paket sudah ada!']);
        exit;
    }

    $stmt = $con->prepare("INSERT INTO tbl_paket (nama_paket, id_kategori, deskripsi, harga_umum, harga_mahasiswa, durasi_hari) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param('sisiii', $nama, $kategori, $deskripsi, $harga_umum, $harga_mahasiswa, $durasi);

    echo $stmt->execute()
        ? json_encode(['status' => 'success', 'message' => 'Paket berhasil ditambahkan!'])
        : json_encode(['status' => 'error', 'message' => 'Gagal menyimpan paket']);
    $stmt->close();
} elseif ($action === 'update') {
    $id             = intval($_POST['id_paket'] ?? 0);
    $nama           = trim($_POST['nama_paket'] ?? '');
    $kategori       = $_POST['id_kategori'] ?? '';
    $deskripsi      = trim($_POST['deskripsi'] ?? '');
    $harga_umum     = intval($_POST['harga_umum'] ?? 0);
    $harga_mahasiswa = intval($_POST['harga_mahasiswa'] ?? 0);
    $durasi         = intval($_POST['durasi_hari'] ?? 0);

    if (!$id || empty($nama) || $harga_umum <= 0 || $harga_mahasiswa <= 0 || !in_array($durasi, [1, 30, 90, 180, 365])) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak valid!']);
        exit;
    }

    $stmt = $con->prepare("UPDATE tbl_paket SET nama_paket=?, id_kategori=?, deskripsi=?, harga_umum=?, harga_mahasiswa=?, durasi_hari=? WHERE id_paket=?");
    $stmt->bind_param('sisiiii', $nama, $kategori, $deskripsi, $harga_umum, $harga_mahasiswa, $durasi, $id);

    echo $stmt->execute()
        ? json_encode(['status' => 'success', 'message' => 'Paket berhasil diperbarui!'])
        : json_encode(['status' => 'error', 'message' => 'Gagal update paket']);
} elseif ($action === 'hapus') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $con->prepare("DELETE FROM tbl_paket WHERE id_paket = ?");
    $stmt->bind_param('i', $id);
    echo $stmt->execute()
        ? json_encode(['status' => 'success', 'message' => 'Paket dihapus!'])
        : json_encode(['status' => 'error', 'message' => 'Gagal hapus (paket mungkin sudah digunakan)']);
}
