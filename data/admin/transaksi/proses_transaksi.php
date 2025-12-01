<?php
require "../../../setting/session.php";
require "../../../setting/koneksi.php";

header('Content-Type: application/json');

// Validasi input dengan handling nilai kosong
$required_fields = ['id_user_kasir', 'id_paket', 'harga_paket', 'metode_pembayaran', 'jumlah_dibayar', 'durasi_hari'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field])) {
        echo json_encode(['success' => false, 'error' => 'Data tidak lengkap: ' . $field]);
        exit;
    }
}

// Ambil data dari POST dengan default value untuk diskon
$id_user_kasir = intval($_POST['id_user_kasir']);
$id_member = isset($_POST['id_member']) && $_POST['id_member'] !== '' ? intval($_POST['id_member']) : null;
$id_paket = intval($_POST['id_paket']);
$harga_paket = floatval($_POST['harga_paket']);
$diskon = isset($_POST['diskon']) ? floatval($_POST['diskon']) : 0; // Default 0 jika tidak ada
$metode_pembayaran = $con->real_escape_string($_POST['metode_pembayaran']);
$jumlah_dibayar = floatval($_POST['jumlah_dibayar']);
$durasi_hari = intval($_POST['durasi_hari']);

// HITUNG GRAND TOTAL DAN KEMBALIAN - INI YANG TAMBAH
$grand_total = $harga_paket - $diskon;
if ($grand_total < 0) {
    $grand_total = 0;
}

$kembalian = $jumlah_dibayar - $grand_total;

// Debug log (hapus di production)
error_log("Data received - harga: $harga_paket, diskon: $diskon, total: $grand_total, bayar: $jumlah_dibayar, kembali: $kembalian");

// Validasi nominal
if ($harga_paket < 0 || $diskon < 0 || $jumlah_dibayar < 0) {
    echo json_encode(['success' => false, 'error' => 'Nominal tidak valid']);
    exit;
}

if ($jumlah_dibayar < $grand_total) {
    echo json_encode(['success' => false, 'error' => 'Jumlah pembayaran kurang. Kurang: Rp ' . number_format($grand_total - $jumlah_dibayar, 0, ',', '.')]);
    exit;
}

// Validasi metode pembayaran
$valid_metode = ['tunai', 'qris', 'transfer', 'debit'];
if (!in_array(strtolower($metode_pembayaran), $valid_metode)) {
    echo json_encode(['success' => false, 'error' => 'Metode pembayaran tidak valid']);
    exit;
}

try {
    // Mulai transaksi database
    $con->begin_transaction();

    // 1. Generate ID Transaksi
    $id_transaksi = 'TRX' . date('YmdHis') . rand(100, 999);

    // 2. Ambil nama paket
    $sql_paket = "SELECT nama_paket FROM tbl_paket WHERE id_paket = ?";
    $stmt = $con->prepare($sql_paket);
    $stmt->bind_param("i", $id_paket);
    $stmt->execute();
    $result = $stmt->get_result();
    $paket = $result->fetch_assoc();
    $stmt->close();

    if (!$paket) {
        throw new Exception("Paket tidak ditemukan");
    }

    $nama_paket = $paket['nama_paket'];

    // 3. Insert ke tabel transaksi_offline (sesuai struktur database Anda)
    $sql_transaksi = "INSERT INTO tbl_transaksi_offline (
        id_transaksi, id_member, id_kasir, id_paket, total, 
        metode_pembayaran, jumlah_bayar, kembalian, tgl_transaksi
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $con->prepare($sql_transaksi);

    // Bind parameters sesuai urutan
    $stmt->bind_param(
        "siiidddd",
        $id_transaksi,
        $id_member,
        $id_user_kasir,
        $id_paket,
        $grand_total,
        $metode_pembayaran,
        $jumlah_dibayar,
        $kembalian
    );

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan transaksi: " . $stmt->error);
    }

    $stmt->close();

    // 4. Insert ke tabel transaksi_offline_detail
    $sql_detail = "INSERT INTO tbl_transaksi_offline_detail (
        id_transaksi, id_paket, nama_paket, harga_satuan, qty, potongan_diskon_item, sub_total
    ) VALUES (?, ?, ?, ?, 1, ?, ?)";

    $stmt = $con->prepare($sql_detail);
    $stmt->bind_param("sisddd", $id_transaksi, $id_paket, $nama_paket, $harga_paket, $diskon, $grand_total);

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan detail transaksi: " . $stmt->error);
    }

    $stmt->close();

    // 5. Jika ada member, insert ke tabel membership dan update status
    $member_aktif = false;
    if ($id_member) {
        $tgl_mulai = date('Y-m-d H:i:s');
        $tgl_berakhir = date('Y-m-d H:i:s', strtotime("+$durasi_hari days"));

        // Set waktu berakhir ke akhir hari (23:59:59)
        $tgl_berakhir = date('Y-m-d', strtotime("+$durasi_hari days")) . " 23:59:59";

        // Insert ke tabel membership
        $sql_membership = "INSERT INTO tbl_membership (
            id_member, id_transaksi, id_paket, tgl_mulai, tgl_berakhir, sumber
        ) VALUES (?, ?, ?, ?, ?, 'offline')";

        $stmt = $con->prepare($sql_membership);
        $stmt->bind_param("isisss", $id_member, $id_transaksi, $id_paket, $tgl_mulai, $tgl_berakhir);

        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan membership: " . $stmt->error);
        }

        $stmt->close();

        // Update status member (trigger akan otomatis mengupdate membership_status)
        $sql_update_member = "UPDATE tbl_member SET membership_status = 'aktif' WHERE id_member = ?";
        $stmt = $con->prepare($sql_update_member);
        $stmt->bind_param("i", $id_member);

        if (!$stmt->execute()) {
            throw new Exception("Gagal mengupdate status member: " . $stmt->error);
        }

        $member_aktif = true;
        $stmt->close();
    }

    // Commit transaksi
    $con->commit();

    // Ambil nama member jika ada
    $nama_member = 'Pelanggan Umum';
    if ($id_member) {
        $sql_member = "SELECT nama FROM tbl_member WHERE id_member = ?";
        $stmt = $con->prepare($sql_member);
        $stmt->bind_param("i", $id_member);
        $stmt->execute();
        $result = $stmt->get_result();
        $member = $result->fetch_assoc();
        $nama_member = $member['nama'];
        $stmt->close();
    }

    // Ambil nama kasir
    $nama_kasir = 'Kasir';
    $sql_kasir = "SELECT username FROM tbl_user WHERE id_user = ?";
    $stmt = $con->prepare($sql_kasir);
    $stmt->bind_param("i", $id_user_kasir);
    $stmt->execute();
    $result = $stmt->get_result();
    $kasir = $result->fetch_assoc();
    if ($kasir) {
        $nama_kasir = $kasir['username'];
    }
    $stmt->close();

    // Response sukses
    echo json_encode([
        'success' => true,
        'id_transaksi' => $id_transaksi,
        'tanggal_transaksi' => date('d/m/Y H:i'),
        'nama_kasir' => $nama_kasir,
        'nama_member' => $nama_member,
        'nama_paket' => $nama_paket,
        'durasi_hari' => $durasi_hari,
        'harga_paket' => $harga_paket,
        'diskon' => $diskon,
        'grand_total' => $grand_total,
        'jumlah_dibayar' => $jumlah_dibayar,
        'kembalian' => $kembalian,
        'metode_pembayaran' => $metode_pembayaran,
        'member_aktif' => $member_aktif,
        'tgl_mulai' => isset($tgl_mulai) ? $tgl_mulai : '',
        'tgl_berakhir' => isset($tgl_berakhir) ? $tgl_berakhir : ''
    ]);
} catch (Exception $e) {
    // Rollback transaksi jika ada error
    $con->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$con->close();
