<?php
// File: setting/get_notifications.php
session_start();
require "koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die(json_encode([]));
}

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$query = "SELECT * FROM tbl_notifikasi WHERE dibaca = 0 ORDER BY dibuat_pada DESC LIMIT $limit";
$result = mysqli_query($con, $query);

$notifications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = [
        'id' => $row['id_notifikasi'],
        'title' => $row['judul'],
        'message' => $row['pesan'],
        'type' => $row['tipe'],
        'time' => date('H:i', strtotime($row['dibuat_pada'])),
        'link' => $row['link']
    ];
}

header('Content-Type: application/json');
echo json_encode($notifications);
