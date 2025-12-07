<?php
require "../../../setting/session.php";
require "../../../setting/koneksi.php";

header('Content-Type: application/json');

// Validasi input
$required_fields = ['id_user_kasir', 'id_paket', 'harga_paket', 'metode_pembayaran', 'jumlah_dibayar', 'durasi_hari'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field])) {
        echo json_encode(['success' => false, 'error' => 'Data tidak lengkap: ' . $field]);
        exit;
    }
}

// Ambil data dari POST
$id_user_kasir = intval($_POST['id_user_kasir']);
$id_member = isset($_POST['id_member']) && $_POST['id_member'] !== '' ? intval($_POST['id_member']) : null;
$id_paket = intval($_POST['id_paket']);
$harga_paket = floatval($_POST['harga_paket']);
$diskon = isset($_POST['diskon']) ? floatval($_POST['diskon']) : 0;
$metode_pembayaran = strtoupper($con->real_escape_string($_POST['metode_pembayaran']));
$jumlah_dibayar = floatval($_POST['jumlah_dibayar']);
$durasi_hari = intval($_POST['durasi_hari']);

// Hitung total
$total = max(0, $harga_paket - $diskon);
$kembalian = $jumlah_dibayar - $total;

// Validasi
if ($harga_paket < 0 || $diskon < 0 || $jumlah_dibayar < 0) {
    echo json_encode(['success' => false, 'error' => 'Nominal tidak valid']);
    exit;
}

if ($jumlah_dibayar < $total) {
    echo json_encode([
        'success' => false,
        'error' => 'Jumlah pembayaran kurang. Kurang: Rp ' . number_format($total - $jumlah_dibayar, 0, ',', '.')
    ]);
    exit;
}

// Validasi metode pembayaran
$valid_metode = ['TUNAI', 'QRIS', 'TRANSFER', 'DEBIT'];
if (!in_array($metode_pembayaran, $valid_metode)) {
    echo json_encode(['success' => false, 'error' => 'Metode pembayaran tidak valid']);
    exit;
}

// Validasi khusus: hanya paket harian (durasi 1) yang bisa dipilih
if ($durasi_hari != 1) {
    echo json_encode(['success' => false, 'error' => 'Hanya paket harian yang diperbolehkan']);
    exit;
}

try {
    // Mulai transaksi
    $con->begin_transaction();

    // 1. Generate ID Transaksi
    $id_transaksi = 'TRX' . date('YmdHis') . rand(100, 999);

    // 2. Ambil nama paket dan durasi
    $sql_paket = "SELECT nama_paket, durasi_hari FROM tbl_paket WHERE id_paket = ?";
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
    $durasi_paket = $paket['durasi_hari'];

    // Validasi: pastikan hanya paket harian
    if ($durasi_paket != 1) {
        throw new Exception("Hanya paket harian yang diperbolehkan. Paket ini memiliki durasi " . $durasi_paket . " hari");
    }

    // 3. Validasi jika member sudah memiliki membership aktif
    if ($id_member) {
        // Cek apakah member sudah memiliki membership aktif
        $sql_check_active = "
            SELECT 
                m.membership_status,
                ms.tgl_berakhir,
                p.nama_paket
            FROM tbl_member m
            LEFT JOIN tbl_membership ms ON m.id_member = ms.id_member 
                AND ms.tgl_berakhir = (SELECT MAX(tgl_berakhir) FROM tbl_membership WHERE id_member = m.id_member)
            LEFT JOIN tbl_paket p ON ms.id_paket = p.id_paket
            WHERE m.id_member = ? 
                AND m.membership_status = 'aktif' 
                AND ms.tgl_berakhir >= NOW()
        ";

        $stmt = $con->prepare($sql_check_active);
        $stmt->bind_param("i", $id_member);
        $stmt->execute();
        $result = $stmt->get_result();
        $active_membership = $result->fetch_assoc();
        $stmt->close();

        if ($active_membership) {
            $expiry_date = date('d/m/Y H:i', strtotime($active_membership['tgl_berakhir']));
            throw new Exception("Member sudah memiliki membership aktif ({$active_membership['nama_paket']}) sampai $expiry_date");
        }
    }

    // 4. Insert ke tabel transaksi_offline
    $sql_transaksi = "INSERT INTO tbl_transaksi_offline (
        id_transaksi, 
        id_member, 
        id_kasir, 
        id_paket, 
        total, 
        metode_pembayaran, 
        jumlah_bayar, 
        kembalian
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $con->prepare($sql_transaksi);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $con->error);
    }

    $stmt->bind_param(
        "siiidddd",
        $id_transaksi,
        $id_member,
        $id_user_kasir,
        $id_paket,
        $total,
        $metode_pembayaran,
        $jumlah_dibayar,
        $kembalian
    );

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan transaksi: " . $stmt->error);
    }
    $stmt->close();

    // 5. Insert ke tabel transaksi_offline_detail
    $sql_detail = "INSERT INTO tbl_transaksi_offline_detail (
        id_transaksi, 
        id_paket, 
        nama_paket, 
        harga_satuan, 
        qty, 
        potongan_diskon_item, 
        sub_total
    ) VALUES (?, ?, ?, ?, 1, ?, ?)";

    $stmt = $con->prepare($sql_detail);
    if (!$stmt) {
        throw new Exception("Prepare detail failed: " . $con->error);
    }

    $stmt->bind_param(
        "sisddd",
        $id_transaksi,
        $id_paket,
        $nama_paket,
        $harga_paket,
        $diskon,
        $total
    );

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan detail: " . $stmt->error);
    }
    $stmt->close();

    // 6. Jika ada member, insert ke membership (hanya untuk paket harian)
    $member_aktif = false;
    $tgl_mulai = null;
    $tgl_berakhir = null;

    if ($id_member) {
        // Untuk paket harian: mulai sekarang, berakhir hari ini jam 23:59:59
        $tgl_mulai = date('Y-m-d H:i:s');
        $tgl_berakhir = date('Y-m-d 23:59:59');

        // Insert ke membership
        $sql_membership = "INSERT INTO tbl_membership (
            id_member, 
            id_transaksi, 
            id_paket, 
            tgl_mulai, 
            tgl_berakhir, 
            sumber
        ) VALUES (?, ?, ?, ?, ?, 'offline')";

        $stmt = $con->prepare($sql_membership);
        $stmt->bind_param(
            "iisss",
            $id_member,
            $id_transaksi,
            $id_paket,
            $tgl_mulai,
            $tgl_berakhir
        );

        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan membership: " . $stmt->error);
        }
        $stmt->close();

        // Update status membership di tabel member
        $sql_update_member = "UPDATE tbl_member SET membership_status = 'aktif' WHERE id_member = ?";
        $stmt = $con->prepare($sql_update_member);
        $stmt->bind_param("i", $id_member);
        $stmt->execute();
        $stmt->close();

        $member_aktif = true;

        // Tambahkan notifikasi
        $sql_notif = "INSERT INTO tbl_notifikasi (tipe, judul, pesan) 
                      VALUES ('membership_aktif', 'Membership Harian Diaktifkan', 
                              CONCAT('Membership harian ', ?, ' telah diaktifkan untuk ', (SELECT nama FROM tbl_member WHERE id_member = ?)))";
        $stmt = $con->prepare($sql_notif);
        $stmt->bind_param("si", $nama_paket, $id_member);
        $stmt->execute();
        $stmt->close();
    }

    // Commit transaksi
    $con->commit();

    // Ambil nama member
    $nama_member = 'Pelanggan Umum';
    if ($id_member) {
        $sql_member = "SELECT nama FROM tbl_member WHERE id_member = ?";
        $stmt = $con->prepare($sql_member);
        $stmt->bind_param("i", $id_member);
        $stmt->execute();
        $result = $stmt->get_result();
        $member = $result->fetch_assoc();
        if ($member) {
            $nama_member = $member['nama'];
        }
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
        'grand_total' => $total,
        'jumlah_dibayar' => $jumlah_dibayar,
        'kembalian' => $kembalian,
        'metode_pembayaran' => $metode_pembayaran,
        'member_aktif' => $member_aktif,
        'tgl_mulai' => $tgl_mulai ? date('d/m/Y H:i', strtotime($tgl_mulai)) : '',
        'tgl_berakhir' => $tgl_berakhir ? date('d/m/Y H:i', strtotime($tgl_berakhir)) : ''
    ]);
} catch (Exception $e) {
    $con->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$con->close();
