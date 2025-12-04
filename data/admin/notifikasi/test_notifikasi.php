<?php
require "../../../setting/koneksi.php";

// Test 1: Cek tabel notifikasi
echo "<h2>1. Cek Tabel Notifikasi</h2>";
$result = $con->query("SHOW TABLES LIKE 'tbl_notifikasi'");
if ($result->num_rows > 0) {
    echo "✅ Tabel tbl_notifikasi ADA<br>";

    // Hitung jumlah notifikasi
    $count = $con->query("SELECT COUNT(*) as total FROM tbl_notifikasi")->fetch_assoc()['total'];
    echo "Jumlah notifikasi: " . $count . "<br>";

    // Tampilkan notifikasi terbaru
    $notif = $con->query("SELECT * FROM tbl_notifikasi ORDER BY dibuat_pada DESC LIMIT 5");
    echo "<h3>5 Notifikasi Terbaru:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Judul</th><th>Pesan</th><th>Tipe</th><th>Tanggal</th></tr>";
    while ($row = $notif->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id_notifikasi'] . "</td>";
        echo "<td>" . $row['judul'] . "</td>";
        echo "<td>" . $row['pesan'] . "</td>";
        echo "<td>" . $row['tipe'] . "</td>";
        echo "<td>" . $row['dibuat_pada'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Tabel tbl_notifikasi TIDAK ADA<br>";
}

// Test 2: Cek trigger
echo "<h2>2. Cek Trigger</h2>";
$triggers = $con->query("SHOW TRIGGERS");
if ($triggers->num_rows > 0) {
    echo "Trigger yang ada:<br>";
    echo "<table border='1'>";
    echo "<tr><th>Trigger</th><th>Event</th><th>Table</th></tr>";
    while ($row = $triggers->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Trigger'] . "</td>";
        echo "<td>" . $row['Event'] . "</td>";
        echo "<td>" . $row['Table'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Tidak ada trigger<br>";
}

// Test 3: Buat notifikasi manual
echo "<h2>3. Test Buat Notifikasi Manual</h2>";
$judul = "Test Notifikasi Manual";
$pesan = "Ini adalah test notifikasi manual pada " . date('Y-m-d H:i:s');
$tipe = "system";
$link = "/test.php";

$sql = "INSERT INTO tbl_notifikasi (judul, pesan, tipe, link) VALUES (?, ?, ?, ?)";
$stmt = $con->prepare($sql);
$stmt->bind_param("ssss", $judul, $pesan, $tipe, $link);

if ($stmt->execute()) {
    echo "✅ Notifikasi manual berhasil dibuat<br>";
} else {
    echo "❌ Gagal membuat notifikasi: " . $stmt->error . "<br>";
}
$stmt->close();

$con->close();
