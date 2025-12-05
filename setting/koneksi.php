<?php
$host = "localhost";
$user = "root";
$pass = "12345";
$db = "db_gym";
$con = new mysqli($host, $user, $pass, $db);
if ($con->connect_error) {
    die("Koneksi gagal: " . $con->connect_error);
}
