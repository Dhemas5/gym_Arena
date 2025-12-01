<?php
// File: setting/mark_notification_read.php
session_start();
require "koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "UPDATE tbl_notifikasi SET dibaca = 1 WHERE id_notifikasi = $id";

    if (mysqli_query($con, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($con)]);
    }
}
