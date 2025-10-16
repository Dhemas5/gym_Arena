<?php
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

if ($action == 'simpan') {
    $nama = htmlspecialchars($_POST['nama_paket']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $harga = htmlspecialchars($_POST['harga']);
    $durasi = htmlspecialchars($_POST['durasi_hari']);
    $cek = mysqli_query($con, "SELECT * FROM tbl_paket WHERE nama_paket='$nama'");
    if (mysqli_num_rows($cek) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Paket sudah ada!']);
    } else {
        $insert = mysqli_query($con, "INSERT INTO tbl_paket(nama_paket,deskripsi,harga,durasi_hari) VALUES('$nama','$deskripsi','$harga','$durasi')");
        if ($insert) {
            echo json_encode(['status' => 'success', 'message' => 'Paket berhasil ditambahkan.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan paket: ' . mysqli_error($con)]);
        }
    }
}

if ($action == 'update') {
    $id = $_POST['id_paket'];
    $nama = htmlspecialchars($_POST['nama_paket']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $harga = htmlspecialchars($_POST['harga']);
    $durasi = htmlspecialchars($_POST['durasi_hari']);
    $cek = mysqli_query($con, "SELECT * FROM tbl_paket WHERE nama_paket='$nama' AND id_paket!='$id'");
    if (mysqli_num_rows($cek) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nama paket sudah ada!']);
    } else {
        $update = mysqli_query($con, "UPDATE tbl_paket SET nama_paket='$nama', deskripsi='$deskripsi', harga='$harga', durasi_hari='$durasi' WHERE id_paket='$id'");
        if ($update) {
            echo json_encode(['status' => 'success', 'message' => 'Paket berhasil diupdate.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal update paket: ' . mysqli_error($con)]);
        }
    }
}

if ($action == 'hapus') {
    $id = $_GET['id'];
    $delete = mysqli_query($con, "DELETE FROM tbl_paket WHERE id_paket='$id'");
    if ($delete) {
        echo json_encode(['status' => 'success', 'message' => 'Paket berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal hapus paket: ' . mysqli_error($con)]);
    }
}
