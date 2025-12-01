<?php
// File: functions/notifikasi_functions.php
// Fungsi-fungsi untuk manajemen notifikasi

/**
 * Buat notifikasi baru
 * @param mysqli $con Koneksi database
 * @param string $judul Judul notifikasi
 * @param string $pesan Isi pesan notifikasi
 * @param string $tipe Tipe notifikasi (new_member, new_membership, system, warning)
 * @param string|null $link Link aksi (opsional)
 * @param int|null $id_referensi ID referensi (opsional)
 * @return bool true jika berhasil, false jika gagal
 */
function buatNotifikasi($con, $judul, $pesan, $tipe = 'system', $link = null, $id_referensi = null)
{
    // Validasi input
    if (empty($judul) || empty($pesan)) {
        error_log("Judul atau pesan notifikasi kosong");
        return false;
    }

    // Pastikan pesan tidak null
    $pesan = $pesan ?: "Notifikasi sistem";

    // Cek apakah tabel notifikasi ada
    $check = $con->query("SHOW TABLES LIKE 'tbl_notifikasi'");
    if ($check && $check->num_rows == 0) {
        error_log("Tabel tbl_notifikasi tidak ditemukan");
        return false; // Tabel tidak ada
    }

    try {
        // Escape string untuk keamanan
        $judul = $con->real_escape_string($judul);
        $pesan = $con->real_escape_string($pesan);
        $tipe = $con->real_escape_string($tipe);
        $link = $link ? $con->real_escape_string($link) : null;

        // Query insert
        $sql = "INSERT INTO tbl_notifikasi (judul, pesan, tipe, link, id_referensi) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $con->prepare($sql);
        if (!$stmt) {
            error_log("Gagal prepare statement notifikasi: " . $con->error);
            return false;
        }

        $stmt->bind_param("ssssi", $judul, $pesan, $tipe, $link, $id_referensi);
        $result = $stmt->execute();

        if (!$result) {
            error_log("Gagal eksekusi notifikasi: " . $stmt->error);
            $stmt->close();
            return false;
        }

        $stmt->close();
        return true;
    } catch (Exception $e) {
        error_log("Error buatNotifikasi: " . $e->getMessage());
        return false;
    }
}

/**
 * Buat notifikasi untuk transaksi yang disetujui
 * @param mysqli $con Koneksi database
 * @param string $id_transaksi ID transaksi
 * @param int $id_member ID member
 * @param float $total Total transaksi
 * @param string $nama_member Nama member
 * @param string $nama_paket Nama paket
 * @return bool true jika berhasil
 */
function buatNotifikasiTransaksiApprove($con, $id_transaksi, $id_member, $total, $nama_member, $nama_paket)
{
    $judul = 'Transaksi Online Disetujui';
    $pesan = sprintf(
        "Transaksi #%s dari member \"%s\" untuk paket \"%s\" telah disetujui. Total: Rp %s",
        $id_transaksi,
        $nama_member,
        $nama_paket,
        number_format($total, 0, ',', '.')
    );
    $link = "/data/admin/transaksi_online/detail.php?id=" . urlencode($id_transaksi);

    return buatNotifikasi($con, $judul, $pesan, 'new_membership', $link, $id_member);
}

/**
 * Buat notifikasi untuk transaksi yang ditolak
 * @param mysqli $con Koneksi database
 * @param string $id_transaksi ID transaksi
 * @param string $nama_member Nama member
 * @return bool true jika berhasil
 */
function buatNotifikasiTransaksiReject($con, $id_transaksi, $nama_member)
{
    $judul = 'Transaksi Online Ditolak';
    $pesan = sprintf(
        "Transaksi #%s dari member \"%s\" telah ditolak.",
        $id_transaksi,
        $nama_member
    );
    $link = "/data/admin/transaksi_online/detail.php?id=" . urlencode($id_transaksi);

    return buatNotifikasi($con, $judul, $pesan, 'warning', $link, 0);
}

/**
 * Buat notifikasi untuk member baru
 * @param mysqli $con Koneksi database
 * @param int $id_member ID member
 * @param string $nama_member Nama member
 * @param string $email Email member
 * @return bool true jika berhasil
 */
function buatNotifikasiMemberBaru($con, $id_member, $nama_member, $email)
{
    $judul = 'Member Baru Mendaftar';
    $pesan = sprintf(
        "Member baru dengan nama \"%s\" telah mendaftar. Email: %s",
        $nama_member,
        $email
    );
    $link = "/data/admin/member/detail.php?id=" . $id_member;

    return buatNotifikasi($con, $judul, $pesan, 'new_member', $link, $id_member);
}

/**
 * Buat notifikasi untuk pembelian membership offline
 * @param mysqli $con Koneksi database
 * @param string $id_transaksi ID transaksi
 * @param string $nama_member Nama member
 * @param string $nama_paket Nama paket
 * @param float $total Total transaksi
 * @return bool true jika berhasil
 */
function buatNotifikasiMembershipOffline($con, $id_transaksi, $nama_member, $nama_paket, $total)
{
    $judul = 'Pembelian Membership Offline';
    $pesan = sprintf(
        "Member \"%s\" telah membeli paket \"%s\" secara offline. Total: Rp %s",
        $nama_member,
        $nama_paket,
        number_format($total, 0, ',', '.')
    );
    $link = "/data/admin/transaksi/detail.php?id=" . urlencode($id_transaksi);

    return buatNotifikasi($con, $judul, $pesan, 'new_membership', $link, 0);
}

/**
 * Hitung jumlah notifikasi yang belum dibaca
 * @param mysqli $con Koneksi database
 * @return int Jumlah notifikasi belum dibaca
 */
function hitungNotifikasiBelumDibaca($con)
{
    $sql = "SELECT COUNT(*) as total FROM tbl_notifikasi WHERE dibaca = 0";
    $result = $con->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return (int) $row['total'];
    }

    return 0;
}

/**
 * Ambil notifikasi terbaru
 * @param mysqli $con Koneksi database
 * @param int $limit Jumlah maksimal notifikasi
 * @return array List notifikasi
 */
function ambilNotifikasiTerbaru($con, $limit = 10)
{
    $sql = "SELECT * FROM tbl_notifikasi 
            ORDER BY dibuat_pada DESC 
            LIMIT " . (int) $limit;

    $result = $con->query($sql);
    $notifications = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
    }

    return $notifications;
}

/**
 * Tandai notifikasi sebagai sudah dibaca
 * @param mysqli $con Koneksi database
 * @param int $id_notifikasi ID notifikasi
 * @return bool true jika berhasil
 */
function tandaiNotifikasiDibaca($con, $id_notifikasi)
{
    $sql = "UPDATE tbl_notifikasi SET dibaca = 1 WHERE id_notifikasi = ?";
    $stmt = $con->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $id_notifikasi);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}
