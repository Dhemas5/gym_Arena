<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

if ($action == 'simpan') {
    $nama = htmlspecialchars($_POST['nama_kategori']);
    $deskripsi = htmlspecialchars($_POST['deskripsi'] ?? '');
    $cek = mysqli_prepare($con, "SELECT * FROM tbl_kategori WHERE nama_kategori=?");
    mysqli_stmt_bind_param($cek, "s", $nama);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nama kategori sudah ada!']);
    } else {
        $insert = mysqli_prepare($con, "INSERT INTO tbl_kategori (nama_kategori,deskripsi) VALUES (?,?)");
        mysqli_stmt_bind_param($insert, "ss", $nama, $deskripsi);
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
    $cek = mysqli_prepare($con, "SELECT * FROM tbl_kategori WHERE nama_kategori=? AND id_kategori!=?");
    mysqli_stmt_bind_param($cek, "si", $nama, $id);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nama kategori sudah ada!']);
    } else {
        $update = mysqli_prepare($con, "UPDATE tbl_kategori SET nama_kategori=?, deskripsi=? WHERE id_kategori=?");
        mysqli_stmt_bind_param($update, "ssi", $nama, $deskripsi, $id);
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
    $delete = mysqli_prepare($con, "DELETE FROM tbl_kategori WHERE id_kategori=?");
    mysqli_stmt_bind_param($delete, "i", $id);
    if (mysqli_stmt_execute($delete)) {
        echo json_encode(['status' => 'success', 'message' => 'Kategori berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus kategori: ' . mysqli_error($con)]);
    }
    mysqli_stmt_close($delete);
}
