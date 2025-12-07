<?php
session_start();
require "../../../setting/koneksi.php";

if (!isset($_SESSION['login']) || $_SESSION['user_type'] !== 'member') {
  header("Location: ../login/login.php");
  exit;
}

$nama_member = $_SESSION['nama'];
$id_member   = $_SESSION['id_member'];

// Ambil data foto member
$foto_member = 'default.jpg';
$stmt_foto = $con->prepare("SELECT foto FROM tbl_member WHERE id_member = ?");
$stmt_foto->bind_param("i", $id_member);
$stmt_foto->execute();
$result_foto = $stmt_foto->get_result();
if ($row_foto = $result_foto->fetch_assoc()) {
    $foto_member = $row_foto['foto'];
}
$stmt_foto->close();

// Fungsi untuk mendapatkan path foto yang benar
function getFotoPathMember($foto_filename, $id_member) {
    // Cek di beberapa lokasi yang mungkin
    $possible_paths = [
        '../../../uploads/member/' . $foto_filename,
        '../../uploads/member/' . $foto_filename,
        '../uploads/member/' . $foto_filename,
        'uploads/member/' . $foto_filename
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path) && !is_dir($path)) {
            return $path;
        }
    }
    
    // Jika tidak ditemukan, return path default
    return '../../../uploads/member/' . $foto_filename;
}

// Cek apakah foto ada
$foto_path = getFotoPathMember($foto_member, $id_member);
$has_custom_foto = ($foto_member !== 'default.jpg' && file_exists($foto_path));

// Cek status mahasiswa
$is_mahasiswa = 0;
$stmt = $con->prepare("SELECT is_mahasiswa FROM tbl_member WHERE id_member = ?");
$stmt->bind_param("i", $id_member);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
  $is_mahasiswa = $row['is_mahasiswa'];
}
$stmt->close();

// === CEK MEMBERSHIP AKTIF ===
$membership_aktif = false;
$paket_aktif = null;
$berakhir = null;
$total_sesi_gym = 0; // TAMBAHAN BARU

$stmt = $con->prepare("
    SELECT p.nama_paket, m.tgl_berakhir, p.durasi_hari 
    FROM tbl_membership m 
    JOIN tbl_paket p ON m.id_paket = p.id_paket 
    WHERE m.id_member = ? AND m.tgl_berakhir >= NOW() 
    ORDER BY m.tgl_berakhir DESC LIMIT 1
");
$stmt->bind_param("i", $id_member);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
  $row = $res->fetch_assoc();
  $membership_aktif = true;
  $paket_aktif = $row['nama_paket'];
  $berakhir = date('d M Y', strtotime($row['tgl_berakhir']));
  
  // TAMBAHAN BARU: Hitung total sesi gym berdasarkan durasi paket AKTIF SAAT INI
  $total_sesi_gym = (int)$row['durasi_hari'];
}
$stmt->close();

// === HITUNG TOTAL SELURUH SESI GYM DARI SEMUA PAKET YANG PERNAH DIBELI ===
// Kode ini akan menghitung total semua durasi paket yang pernah dimiliki member
$stmt_total_sesi = $con->prepare("
    SELECT SUM(p.durasi_hari) as total_seluruh_sesi
    FROM tbl_membership m 
    JOIN tbl_paket p ON m.id_paket = p.id_paket 
    WHERE m.id_member = ?
");
$stmt_total_sesi->bind_param("i", $id_member);
$stmt_total_sesi->execute();
$result_total_sesi = $stmt_total_sesi->get_result();
if ($row_total = $result_total_sesi->fetch_assoc()) {
    // Gunakan total seluruh sesi dari semua paket yang pernah dibeli
    $total_sesi_gym = (int)$row_total['total_seluruh_sesi'];
}
$stmt_total_sesi->close();


// TAMBAHAN BARU: Hitung sisa hari
$sisa_hari = 0;
if ($membership_aktif && $tgl_berakhir_raw) {
    $tgl_sekarang = new DateTime();
    $tgl_berakhir_obj = new DateTime($tgl_berakhir_raw);
    $selisih = $tgl_sekarang->diff($tgl_berakhir_obj);
    $sisa_hari = $selisih->days;
}

// === AMBIL JADWAL DARI DATABASE ===
// Ambil jadwal untuk 7 hari ke depan
$tanggal_sekarang = date('Y-m-d');
$tanggal_maksimal = date('Y-m-d', strtotime('+6 days'));
$current_time = date('H:i:s'); // Waktu saat ini untuk filter

$query_jadwal = "
    SELECT 
        jk.id_jadwal,
        jk.tanggal,
        jk.studio,
        jk.jam_mulai,
        jk.jam_selesai,
        k.nama_kategori as nama_kelas,
        i.nama_instruktur
    FROM tbl_jadwal_kelas jk
    INNER JOIN tbl_kategori k ON jk.id_kategori = k.id_kategori
    LEFT JOIN tbl_instruktur i ON jk.id_instruktur = i.id_instruktur
    WHERE jk.tanggal BETWEEN ? AND ?
    ORDER BY jk.tanggal, jk.jam_mulai
";

$stmt = $con->prepare($query_jadwal);
$stmt->bind_param("ss", $tanggal_sekarang, $tanggal_maksimal);
$stmt->execute();
$result_jadwal = $stmt->get_result();
$jadwal_dari_db = [];

while ($row = $result_jadwal->fetch_assoc()) {
    $kelas_date = $row['tanggal'];
    $jadwal_selesai_time = $row['jam_selesai'];
    
    // Tentukan apakah kelas sudah lewat
    $is_past_class = false;
    
    // Jika tanggal sudah lewat (kemarin atau sebelumnya)
    if ($kelas_date < $tanggal_sekarang) {
        $is_past_class = true;
    }
    
    // Jika tanggal sama dengan hari ini, cek apakah waktu selesai sudah lewat
    if ($kelas_date == $tanggal_sekarang) {
        if ($jadwal_selesai_time <= $current_time) {
            $is_past_class = true;
        }
    }
    
    $hari_indonesia = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu'
    ];
    
    $english_day = date('l', strtotime($kelas_date));
    $hari = $hari_indonesia[$english_day];
    
    if (!isset($jadwal_dari_db[$hari])) {
        $jadwal_dari_db[$hari] = [];
    }
    
    // Format nama instruktur
    $nama_instruktur = '';
    if (!empty($row['nama_instruktur'])) {
        $nama_kelas = $row['nama_kelas'];
        if (strpos($nama_kelas, 'ZUMBA') !== false) {
            $nama_instruktur = 'ZIN ' . $row['nama_instruktur'];
        } else if (strpos($nama_kelas, 'SEMAN BL') !== false || 
                   strpos($nama_kelas, 'BODY SHAPE') !== false ||
                   strpos($nama_kelas, 'KAPHA YOGA') !== false ||
                   strpos($nama_kelas, 'AERO BL') !== false ||
                   strpos($nama_kelas, 'TRAMPOLINE') !== false) {
            $nama_instruktur = 'COACH ' . $row['nama_instruktur'];
        } else {
            $nama_instruktur = $row['nama_instruktur'];
        }
    }
    
    // Format studio sesuai gambar
    $studio_display = $row['studio'];
    if ($studio_display == 'STUDIO1') {
        $studio_display = 'STUDIO 1';
    } else if ($studio_display == 'STUDIO2') {
        $studio_display = 'STUDIO 2';
    }
    
   $jadwal_dari_db[$hari][] = [
    'jam_mulai' => date('H:i', strtotime($row['jam_mulai'])),
    'jam_selesai' => date('H:i', strtotime($row['jam_selesai'])),
    'studio' => $studio_display,
    'nama_kelas' => $row['nama_kelas'],
    'nama_instruktur' => $nama_instruktur,
    'tanggal' => $kelas_date,
    'jam_selesai_full' => $row['jam_selesai'],
    'is_past_class' => $is_past_class  // TAMBAHKAN INI
];
}
$stmt->close();

// Urutan hari dalam seminggu
$hari_dalam_minggu = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

// Dapatkan hari ini (0 = Minggu, 1 = Senin, ..., 6 = Sabtu)
$hari_ini_index = date('w');
// Konversi ke format kita (0 = Senin, 6 = Minggu)
$hari_ini_index = $hari_ini_index == 0 ? 6 : $hari_ini_index - 1;
$hari_ini = $hari_dalam_minggu[$hari_ini_index];

// Susun jadwal mulai dari hari ini
$jadwal_terurut = [];
for ($i = 0; $i < 7; $i++) {
    $index = ($hari_ini_index + $i) % 7;
    $hari = $hari_dalam_minggu[$index];
    
    // Hitung tanggal untuk hari ini
    $offset = $i;
    $tanggal_hari = date('Y-m-d', strtotime("+$offset days"));
    
    // Jika ada jadwal dari database untuk hari ini, filter yang belum selesai
   if (isset($jadwal_dari_db[$hari]) && !empty($jadwal_dari_db[$hari])) {
    // TAMPILKAN SEMUA JADWAL TANPA FILTER
    $jadwal_terurut[$hari] = $jadwal_dari_db[$hari];
} else {
    // Jika tidak ada jadwal, buat array kosong
    $jadwal_terurut[$hari] = [];
}
}

// Ambil semua paket untuk ditampilkan (kecuali harian = 1 hari)
$paket_result = $con->query("
    SELECT id_paket, nama_paket, harga_umum, harga_mahasiswa, durasi_hari, deskripsi 
    FROM tbl_paket 
    WHERE durasi_hari != 1
    ORDER BY durasi_hari ASC
");

// Pisahkan paket membership dan trainer
$paket_membership = [];
$paket_trainer = [];

while ($p = $paket_result->fetch_assoc()) {
    $durasi = (int)$p['durasi_hari'];
    
    // Cek apakah ini paket trainer (biasanya berdasarkan nama atau deskripsi)
    $nama_paket = strtolower($p['nama_paket']);
    $deskripsi = strtolower($p['deskripsi']);
    
    $is_trainer = (
        strpos($nama_paket, 'trainer') !== false || 
        strpos($nama_paket, 'personal') !== false ||
        strpos($deskripsi, 'trainer') !== false ||
        strpos($deskripsi, 'personal') !== false ||
        strpos($deskripsi, 'privat') !== false
    );
    
    if ($is_trainer) {
        $paket_trainer[] = $p;
    } else {
        $paket_membership[] = $p;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Arena FIT - Member Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/stylemember.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Jadwal Section Styles */
    .schedule-section {
      background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%);
      padding: 80px 0;
      color: white;
    }

    .schedule-header {
      text-align: center;
      margin-bottom: 50px;
    }

    .schedule-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 15px;
      background: linear-gradient(135deg, #42a5f5 0%, #1976d2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .schedule-subtitle {
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.7);
      max-width: 600px;
      margin: 0 auto;
    }

    .day-schedule {
      background: rgba(13, 27, 42, 0.9);
      border-radius: 16px;
      padding: 25px;
      margin-bottom: 25px;
      border: 1px solid rgba(66, 165, 245, 0.2);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .day-schedule.today {
      border: 2px solid #42a5f5;
      box-shadow: 0 0 30px rgba(66, 165, 245, 0.4);
      transform: scale(1.02);
    }

    .day-schedule:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px rgba(66, 165, 245, 0.2);
    }

    .day-header {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 2px solid rgba(66, 165, 245, 0.3);
    }

    .day-name {
      font-size: 1.4rem;
      font-weight: 700;
      color: #42a5f5;
      margin-right: 15px;
    }

    .day-date {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.6);
      background: rgba(66, 165, 245, 0.1);
      padding: 4px 12px;
      border-radius: 20px;
    }

    .today-badge {
      background: linear-gradient(135deg, #42a5f5 0%, #1976d2 100%);
      color: white;
      font-weight: 600;
    }

    .class-table {
      width: 100%;
      border-collapse: collapse;
    }

    .class-table th {
      text-align: left;
      padding: 12px 15px;
      background: rgba(66, 165, 245, 0.1);
      color: #42a5f5;
      font-weight: 600;
      border-bottom: 1px solid rgba(66, 165, 245, 0.3);
    }

    .class-table td {
      padding: 15px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      color: rgba(255, 255, 255, 0.9);
    }

    .class-table tr:last-child td {
      border-bottom: none;
    }

    .class-table tr:hover td {
      background: rgba(66, 165, 245, 0.05);
    }

    .time-cell {
      font-weight: 600;
      color: #64b5f6;
      width: 150px;
    }

    .studio-cell {
      color: #81c784;
      font-weight: 500;
      width: 120px;
    }

    .class-cell {
      font-weight: 500;
    }

    .instructor-cell {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.9rem;
    }

    .time-range {
      font-size: 0.85rem;
      color: rgba(255, 255, 255, 0.6);
      margin-top: 4px;
    }

    .no-class {
      text-align: center;
      padding: 30px;
      color: rgba(255, 255, 255, 0.5);
      font-style: italic;
    }

    .instagram-section {
      text-align: center;
      margin-top: 60px;
      padding: 30px;
      background: rgba(13, 27, 42, 0.8);
      border-radius: 16px;
      border: 1px solid rgba(66, 165, 245, 0.2);
    }

    .instagram-handle {
      font-size: 1.3rem;
      font-weight: 700;
      color: #42a5f5;
      margin-bottom: 15px;
    }

    .instagram-cta {
      color: rgba(255, 255, 255, 0.8);
      margin-bottom: 20px;
    }

    .btn-instagram {
      background: linear-gradient(45deg, #405DE6, #5851DB, #833AB4, #C13584, #E1306C, #FD1D1D);
      border: none;
      color: white;
      padding: 12px 30px;
      border-radius: 25px;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .btn-instagram:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(225, 48, 108, 0.4);
      color: white;
    }

    .current-week {
      text-align: center;
      margin-bottom: 30px;
      color: rgba(255, 255, 255, 0.8);
      font-size: 1.1rem;
    }

    /* PRICE LIST SECTION - STYLE BARU */
    .pricelist-section {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
      padding: 80px 0;
      color: white;
    }

    .pricelist-header {
      text-align: center;
      margin-bottom: 50px;
    }

    .pricelist-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 15px;
      background: linear-gradient(135deg, #42a5f5 0%, #1976d2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .pricelist-subtitle {
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.7);
      max-width: 600px;
      margin: 0 auto;
    }

    .highlight {
      color: #42a5f5;
    }

    .price-category-title {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 30px;
      text-align: center;
      color: #42a5f5;
      position: relative;
      padding-bottom: 15px;
    }

    .price-category-title:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 3px;
      background: linear-gradient(90deg, #42a5f5, #1976d2);
      border-radius: 2px;
    }

    /* Grid untuk paket membership */
    .membership-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
      margin-bottom: 60px;
    }

    /* Grid untuk paket trainer */
    .trainer-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 30px;
    }

    /* Card untuk paket membership */
    .membership-card {
      background: rgba(13, 27, 42, 0.9);
      border-radius: 16px;
      padding: 30px;
      border: 1px solid rgba(66, 165, 245, 0.2);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .membership-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 40px rgba(66, 165, 245, 0.2);
      border-color: #42a5f5;
    }

    /* Card untuk paket trainer */
    .trainer-card {
      background: linear-gradient(135deg, rgba(66, 165, 245, 0.1), rgba(25, 118, 210, 0.1));
      border-radius: 16px;
      padding: 35px;
      border: 2px solid rgba(66, 165, 245, 0.3);
      box-shadow: 0 10px 35px rgba(0, 0, 0, 0.3);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .trainer-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 50px rgba(66, 165, 245, 0.3);
      border-color: #42a5f5;
    }

    .trainer-card:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #42a5f5, #1976d2);
    }

    /* Badge untuk paket populer */
    .popular-badge {
      position: absolute;
      top: 20px;
      right: -30px;
      background: linear-gradient(135deg, #ff416c, #ff4b2b);
      color: white;
      padding: 8px 40px;
      font-size: 0.9rem;
      font-weight: 600;
      transform: rotate(45deg);
      box-shadow: 0 4px 15px rgba(255, 65, 108, 0.3);
    }

    /* Nama paket */
    .package-name {
      font-size: 1.4rem;
      font-weight: 700;
      color: #42a5f5;
      margin-bottom: 10px;
      text-align: center;
    }

    .trainer-name {
      font-size: 1.6rem;
      font-weight: 700;
      color: #42a5f5;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
      text-align: center;
      justify-content: center;
    }

    /* Durasi paket */
    .package-duration {
      font-size: 1rem;
      color: rgba(255, 255, 255, 0.8);
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      text-align: center;
    }

    /* Deskripsi paket */
    .package-description {
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.95rem;
      line-height: 1.6;
      margin-bottom: 25px;
      min-height: 80px;
    }

    /* Container pilihan harga */
    .price-options-container {
      background: rgba(255, 255, 255, 0.05);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 25px;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .price-option {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 15px;
      margin-bottom: 10px;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.05);
      cursor: pointer;
      transition: all 0.3s ease;
      min-height: 120px;
      flex-direction: column;
      text-align: center;
    }

    .price-option:hover {
      background: rgba(66, 165, 245, 0.1);
      border-color: rgba(66, 165, 245, 0.3);
      transform: translateY(-2px);
    }

    .price-option.selected {
      background: rgba(66, 165, 245, 0.15);
      border-color: #42a5f5;
      box-shadow: 0 5px 15px rgba(66, 165, 245, 0.2);
    }

    .price-option.disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .price-option.disabled:hover {
      transform: none;
      background: rgba(255, 255, 255, 0.03);
      border-color: rgba(255, 255, 255, 0.05);
    }

    .price-option-info {
      flex: 1;
      text-align: center;
      width: 100%;
    }

    .price-option-label {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      font-size: 1rem;
      font-weight: 600;
      color: white !important;
      margin-bottom: 10px;
    }

    .price-option-note {
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.6);
      font-style: italic;
    }

    .price-option-value {
      font-size: 1.2rem;
      font-weight: 700;
      color: white !important;
      text-align: center;
      margin-top: 8px;
      width: 100%;
    }

    .price-option-value.student {
      color: white !important;
    }

    .price-option-badge {
      background: #FF5722;
      color: white;
      font-size: 0.7rem;
      font-weight: 600;
      padding: 3px 8px;
      border-radius: 12px;
      margin-left: 8px;
    }

    .price-option-badge.recommended {
      background: linear-gradient(135deg, #ff416c, #ff4b2b);
    }

    /* Tombol pilih */
    .btn-select-package {
      display: block;
      width: 100%;
      background: linear-gradient(135deg, #42a5f5 0%, #1976d2 100%);
      color: white;
      border: none;
      padding: 14px;
      border-radius: 10px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      text-decoration: none;
      margin-top: 10px;
    }

    .btn-select-package:hover {
      background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(66, 165, 245, 0.3);
      text-decoration: none;
      color: white;
    }

    .btn-select-package:disabled {
      background: #6c757d;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    /* Ikon khusus */
    .feature-icon {
      width: 60px;
      height: 60px;
      background: rgba(66, 165, 245, 0.1);
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 15px;
      color: #42a5f5;
      font-size: 1.5rem;
      margin: 0 auto 15px;
    }

    /* Info mahasiswa */
    .student-info {
      background: rgba(66, 165, 245, 0.1);
      border-left: 4px solid #42a5f5;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .student-info i {
      color: #42a5f5;
      font-size: 1.2rem;
    }

    .student-info-text {
      color: rgba(255, 255, 255, 0.9);
      font-size: 0.95rem;
    }

    /* Section divider */
    .section-divider {
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(66, 165, 245, 0.5), transparent);
      margin: 60px 0;
    }

    /* Modal untuk konfirmasi harga */
    .price-confirmation-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.8);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }

    .price-confirmation-content {
      background: rgba(13, 27, 42, 0.95);
      border-radius: 16px;
      padding: 30px;
      max-width: 500px;
      width: 90%;
      border: 2px solid #42a5f5;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .pricelist-section {
        padding: 50px 0;
      }

      .pricelist-title {
        font-size: 2rem;
      }

      .price-category-title {
        font-size: 1.5rem;
      }

      .membership-grid,
      .trainer-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .membership-card,
      .trainer-card {
        padding: 25px;
      }

      .trainer-name {
        font-size: 1.4rem;
      }

      .price-option-value {
        font-size: 1.2rem;
        min-width: 100px;
      }

      .price-confirmation-content {
        padding: 20px;
        width: 95%;
      }
    }

    /* Styling untuk kelas yang sudah lewat - TIDAK DIPAKAI LAGI karena sudah dihilangkan */
   /* Styling untuk kelas yang sudah lewat */
.past-class {
  opacity: 0.7;
}
.past-class td {
  color: rgba(255, 255, 255, 0.5) !important;
}
.past-class .time-cell {
  color: #888 !important;
}
.past-class .studio-cell {
  color: #777 !important;
}
.past-class .class-cell {
  color: #999 !important;
}
.past-class .instructor-cell {
  color: rgba(255, 255, 255, 0.4) !important;
}
.past-class:hover td {
  background: rgba(100, 100, 100, 0.05) !important;
}
    /* Styles untuk member avatar di navbar */
    .member-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      font-weight: bold;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 10px;
      overflow: hidden;
      border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .member-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .member-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .welcome-text {
      color: rgba(255, 255, 255, 0.8);
    }

    .member-name {
      font-weight: 600;
      color: white;
    }

    @media (max-width: 768px) {
      .schedule-section {
        padding: 50px 0;
      }

      .schedule-title {
        font-size: 2rem;
      }

      .day-schedule {
        padding: 20px;
      }

      .class-table {
        font-size: 0.9rem;
      }

      .class-table th,
      .class-table td {
        padding: 10px 8px;
      }

      .time-cell {
        width: 120px;
      }
      
      .member-info {
        flex-wrap: wrap;
        justify-content: center;
      }
    }
  </style>
</head>

<body>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="indexmemberr.php">
        <span class="brand-box">AF</span>
        <div><span style="font-size: 1.2rem;">Arena FIT</span></div>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="indexmemberr.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="transaksi.php">Transaksi</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
        </ul>
        <div class="member-info ms-3">
          <div class="member-avatar">
            <?php if ($has_custom_foto): ?>
              <img src="<?= $foto_path . '?v=' . time() ?>" alt="Foto Profil <?= htmlspecialchars($nama_member) ?>">
            <?php else: ?>
              <?= strtoupper(substr($nama_member, 0, 1)) ?>
            <?php endif; ?>
          </div>
          <span class="welcome-text">
            <span class="member-name"><?= htmlspecialchars($nama_member) ?></span>
          </span>
          <a href="../login/logout.php" class="btn-logout">Logout</a>
        </div>
      </div>
    </div>
  </nav>

<!-- WELCOME SECTION -->
<section class="member-welcome">
  <div class="container">
    <div class="welcome-card">
      <h1 class="welcome-title">Selamat Datang, <?= htmlspecialchars($nama_member) ?>!</h1>
      <p class="welcome-subtitle">Pilih paket membership terbaik dan mulai latihan sekarang!</p>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number"><?= $membership_aktif ? 'Aktif' : 'Nonaktif' ?></div>
          <div class="stat-label">Status Member</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?= $total_sesi_gym ?></div>
          <div class="stat-label">Sesi Gym</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">100%</div>
          <div class="stat-label">Semangat!</div>
        </div>
      </div>
    </div>
  </div>
</section>

  <!-- JADWAL KELAS SECTION -->
  <section class="schedule-section">
    <div class="container">
      <div class="schedule-header">
        <h2 class="schedule-title">JADWAL KELAS ARENA FIT</h2>
        <p class="schedule-subtitle">Ikuti kelas favorit Anda. Jadwal terbaru update setiap bulan.</p>
      </div>

      <div class="current-week">
        <i class="fas fa-calendar-alt me-2"></i>
        Jadwal Minggu Ini - <?= date('d F Y') ?>
      </div>

     <?php 
// Hitung apakah ada kelas tersedia
$any_class_available = false;
foreach($jadwal_terurut as $hari => $kelas): 
    if (!empty($kelas)) $any_class_available = true;
endforeach;

// Sekarang loop untuk menampilkan
foreach($jadwal_terurut as $hari => $kelas): 
    // Hitung tanggal untuk hari ini dan seterusnya
    $offset = array_search($hari, $hari_dalam_minggu) - $hari_ini_index;
    $tanggal_hari = date('d M', strtotime("+$offset days"));
    $tanggal_full = date('Y-m-d', strtotime("+$offset days"));
    $is_today = $hari === $hari_ini;
    
    // Skip hari jika tidak ada kelas yang tersedia
    if (empty($kelas)) {
        continue;
    }
?>
        
        <div class="day-schedule <?= $is_today ? 'today' : '' ?>">
          <div class="day-header">
            <div class="day-name"><?= strtoupper($hari) ?></div>
            <div class="day-date <?= $is_today ? 'today-badge' : '' ?>">
              <?= $is_today ? 'HARI INI' : $tanggal_hari ?>
            </div>
          </div>
          
          <table class="class-table">
            <thead>
              <tr>
                <th>WAKTU</th>
                <th>STUDIO</th>
                <th>KELAS</th>
              </tr>
            </thead>
           <tbody>
  <?php foreach($kelas as $kelas_item): ?>
    <tr class="<?= $kelas_item['is_past_class'] ? 'past-class' : '' ?>">
      <td class="time-cell">
        <div><?= $kelas_item['jam_mulai'] ?> - <?= $kelas_item['jam_selesai'] ?></div>
        <div class="time-range"><?= $kelas_item['jam_mulai'] ?> - <?= $kelas_item['jam_selesai'] ?></div>
      </td>
      <td class="studio-cell"><?= $kelas_item['studio'] ?></td>
      <td>
        <div class="class-cell">
          <?= $kelas_item['nama_kelas'] ?>
          <?php if($kelas_item['is_past_class']): ?>
            <span class="badge bg-secondary ms-2" style="font-size: 0.7rem;">SELESAI</span>
          <?php endif; ?>
        </div>
        <?php if(!empty($kelas_item['nama_instruktur'])): ?>
          <div class="instructor-cell"><?= $kelas_item['nama_instruktur'] ?></div>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</tbody>
          </table>
        </div>
      <?php endforeach; ?>
      
     <?php 
$any_class_available = false;
foreach($jadwal_terurut as $hari => $kelas): 
    if (!empty($kelas)) $any_class_available = true;
endforeach;

if (!$any_class_available): ?>
  <div class="day-schedule today">
    <div class="day-header">
      <div class="day-name"><?= strtoupper($hari_ini) ?></div>
      <div class="day-date today-badge">HARI INI</div>
    </div>
    <div class="no-class">
      <i class="fas fa-calendar-times fa-3x mb-3"></i>
      <h4>Tidak Ada Kelas Tersedia Minggu Ini</h4>
      <p>Tidak ada jadwal kelas untuk 7 hari ke depan. Silakan hubungi admin untuk informasi lebih lanjut.</p>
    </div>
  </div>
<?php endif; ?>

      <!-- Instagram Section -->
      <div class="instagram-section">
        <div class="instagram-handle">@arenafticlubjember</div>
        <p class="instagram-cta">Follow Instagram kami untuk update jadwal terbaru dan informasi promo!</p>
        <a href="https://www.instagram.com/arenafitclubjember?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank" class="btn-instagram">
          <i class="fab fa-instagram"></i>
          Follow Instagram
        </a>
        <div class="mt-3 text-muted">
          <small>Contact: 0821-4308-0510</small>
        </div>
      </div>
    </div>
  </section>

<!-- PRICE LIST SECTION -->
<section class="pricelist-section">
  <div class="container">

    <?php if ($membership_aktif): ?>
      <!-- Tampilkan info membership aktif sebagai info saja, bukan hambatan -->
      <div class="alert alert-success" style="background: rgba(46, 204, 113, 0.1); border-color: #2ecc71; color: white;">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Membership Anda Aktif!</strong> Paket <strong><?= $paket_aktif ?></strong> berlaku hingga <strong><?= $berakhir ?></strong>. 
        Anda masih dapat membeli paket baru yang akan aktif setelah paket saat ini berakhir.
      </div>
    <?php endif; ?>

    <!-- Selalu tampilkan semua paket -->
    <div class="pricelist-header">

    <?php if ($is_mahasiswa && !$membership_aktif): ?>
      <div class="student-info">
        <i class="fas fa-graduation-cap"></i>
        <div class="student-info-text">
          <strong>Status Mahasiswa Terdeteksi!</strong> Anda dapat memilih antara harga umum atau harga khusus mahasiswa.
          Pilih opsi yang paling sesuai dengan kebutuhan Anda.
        </div>
      </div>
    <?php endif; ?>

    <!-- PAKET MEMBERSHIP -->
    <h3 class="price-category-title">
      <i class="fas fa-dumbbell me-2"></i>Paket Membership
    </h3>
    
    <div class="membership-grid">
      <?php foreach($paket_membership as $p): 
        $durasi_text = match ((int)$p['durasi_hari']) {
          30   => '1 Bulan',
          90   => '3 Bulan',
          180  => '6 Bulan',
          365  => '1 Tahun',
          default => $p['durasi_hari'] . ' Hari'
        };
        
        $icon_class = match ((int)$p['durasi_hari']) {
          30   => 'fas fa-calendar-alt',
          90   => 'fas fa-calendar-check',
          180  => 'fas fa-calendar-star',
          365  => 'fas fa-crown',
          default => 'fas fa-calendar'
        };
        
        // Hitung diskon jika ada
        $diskon_percent = 0;
        if ($is_mahasiswa && $p['harga_mahasiswa'] > 0) {
          $diskon_amount = $p['harga_umum'] - $p['harga_mahasiswa'];
          $diskon_percent = round(($diskon_amount / $p['harga_umum']) * 100);
        }
      ?>
        <div class="membership-card" data-paket-id="<?= $p['id_paket'] ?>">
          <div class="text-center mb-4">
            <div class="feature-icon">
              <i class="<?= $icon_class ?>"></i>
            </div>
            <!-- TAMBAHKAN NAMA PAKET DI SINI -->
            <div class="package-name">
              <?= htmlspecialchars($p['nama_paket']) ?>
            </div>
            <div class="package-duration mt-2">
              <strong style="color: rgba(255,255,255,0.9); font-size: 1rem;"><?= $durasi_text ?></strong>
            </div>
          </div>
          
          <div class="price-options-container">
            <!-- Opsi Harga Umum -->
            <div class="price-option selected" 
                 data-price-type="umum" 
                 data-price="<?= $p['harga_umum'] ?>"
                 onclick="selectPriceOption(this, <?= $p['id_paket'] ?>, 'umum', <?= $p['harga_umum'] ?>)">
              <div class="price-option-info">
                <div class="price-option-label">
                  <i class="fas fa-user-tie me-2"></i> Umum
                </div>
                <div class="price-option-value">
                  Rp <?= number_format($p['harga_umum'], 0, ',', '.') ?>
                </div>
              </div>
              <div class="text-center mt-3">
                <i class="fas fa-check-circle" style="color: #4CAF50; font-size: 1.2rem;"></i>
              </div>
            </div>
            
            <!-- Opsi Harga Mahasiswa (jika user adalah mahasiswa dan ada harga mahasiswa) -->
            <?php if ($is_mahasiswa && $p['harga_mahasiswa'] > 0): ?>
              <div class="price-option mt-3" 
                   data-price-type="mahasiswa" 
                   data-price="<?= $p['harga_mahasiswa'] ?>"
                   onclick="selectPriceOption(this, <?= $p['id_paket'] ?>, 'mahasiswa', <?= $p['harga_mahasiswa'] ?>)">
                <div class="price-option-info">
                  <div class="price-option-label">
                    <i class="fas fa-graduation-cap me-2"></i> Mahasiswa
                    <?php if ($diskon_percent > 0): ?>
                      <span class="price-option-badge">-<?= $diskon_percent ?>%</span>
                    <?php endif; ?>
                  </div>
                  <div class="price-option-value student">
                    Rp <?= number_format($p['harga_mahasiswa'], 0, ',', '.') ?>
                  </div>
                </div>
                <div class="text-center mt-3">
                  <i class="far fa-circle" style="color: rgba(255,255,255,0.5); font-size: 1.2rem;"></i>
                </div>
              </div>
            <?php elseif ($is_mahasiswa && $p['harga_mahasiswa'] == 0): ?>
              <!-- Jika mahasiswa tapi tidak ada harga khusus -->
              <div class="price-option disabled mt-3">
                <div class="price-option-info">
                  <div class="price-option-label">
                    <i class="fas fa-graduation-cap me-2"></i> Mahasiswa
                  </div>
                  <div class="price-option-value">
                    -
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
          
          <?php if (!$membership_aktif): ?>
            <a href="#" 
               class="btn-select-package" 
               id="btn-paket-<?= $p['id_paket'] ?>"
               onclick="confirmPurchase(<?= $p['id_paket'] ?>, 'umum', <?= $p['harga_umum'] ?>, '<?= htmlspecialchars($p['nama_paket']) ?>')">
              <i class="fas fa-shopping-cart me-2"></i>Pilih Paket (Umum)
            </a>
          <?php else: ?>
            <button class="btn-select-package" disabled>
              <i class="fas fa-check-circle me-2"></i>Membership Aktif
            </button>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    
  <!-- PAKET TRAINER -->
<?php if (!empty($paket_trainer)): ?>
  <div class="section-divider"></div>
  
  <h3 class="price-category-title">
    <i class="fas fa-user-tie me-2"></i>Paket Personal Trainer
  </h3>
  
  <!-- TAMBAHKAN INFORMASI INI DI SINI -->
  <div class="text-center mb-4">
    <div class="alert alert-info" style="background: rgba(66, 165, 245, 0.1); border-color: #42a5f5; color: white;">
      <i class="fas fa-info-circle me-2"></i>
      <strong>PROGRAM TRAINER:</strong> 10x Pertemuan + GYM 1 Bulan + Boxing 4x
    </div>
  </div>
  
  <div class="trainer-grid">
    <?php foreach($paket_trainer as $p): 
      $durasi_text = match ((int)$p['durasi_hari']) {
        30   => '1 Bulan',
        90   => '3 Bulan',
        180  => '6 Bulan',
        365  => '1 Tahun',
        default => $p['durasi_hari'] . ' Hari'
      };
      
      // Tentukan apakah ini paket trainer dengan harga 1.5 juta
      $is_trainer_special = ($p['harga_umum'] == 1500000);
    ?>
      <div class="trainer-card" data-paket-id="<?= $p['id_paket'] ?>">
        <div class="text-center mb-4">
          <div class="feature-icon">
            <i class="fas fa-dumbbell"></i>
          </div>
          <!-- TAMBAHKAN NAMA PAKET TRAINER DI SINI -->
          <div class="trainer-name">
            <?= htmlspecialchars($p['nama_paket']) ?>
          </div>
          <div class="package-duration mt-2">
            <strong style="color: rgba(255,255,255,0.9); font-size: 1rem;"><?= $durasi_text ?></strong>
          </div>
          
          <!-- TAMBAHKAN DESKRIPSI TRAINER DI SINI -->
          <?php if ($is_trainer_special): ?>
            <div class="mt-3" style="color: rgba(255,255,255,0.8); font-size: 0.9rem;">
              <i class="fas fa-check-circle me-1" style="color: #4CAF50;"></i> 10x Pertemuan<br>
              <i class="fas fa-check-circle me-1" style="color: #4CAF50;"></i> GYM 1 Bulan<br>
              <i class="fas fa-check-circle me-1" style="color: #4CAF50;"></i> Boxing 4x
            </div>
          <?php endif; ?>
        </div>
        
        <div class="price-options-container">
          <!-- Opsi Harga Umum -->
          <div class="price-option selected" 
               data-price-type="umum" 
               data-price="<?= $p['harga_umum'] ?>"
               onclick="selectPriceOption(this, <?= $p['id_paket'] ?>, 'umum', <?= $p['harga_umum'] ?>)">
            <div class="price-option-info">
              <div class="price-option-label">
                <i class="fas fa-user-tie me-2"></i> Umum
              </div>
              <div class="price-option-value">
                Rp <?= number_format($p['harga_umum'], 0, ',', '.') ?>
              </div>
            </div>
            <div class="text-center mt-3">
              <i class="fas fa-check-circle" style="color: #4CAF50; font-size: 1.2rem;"></i>
            </div>
          </div>
          
          <!-- Opsi Harga Mahasiswa -->
          <?php if ($is_mahasiswa && $p['harga_mahasiswa'] > 0): 
            $diskon_amount = $p['harga_umum'] - $p['harga_mahasiswa'];
            $diskon_percent = round(($diskon_amount / $p['harga_umum']) * 100);
          ?>
            <div class="price-option mt-3" 
                 data-price-type="mahasiswa" 
                 data-price="<?= $p['harga_mahasiswa'] ?>"
                 onclick="selectPriceOption(this, <?= $p['id_paket'] ?>, 'mahasiswa', <?= $p['harga_mahasiswa'] ?>)">
              <div class="price-option-info">
                <div class="price-option-label">
                  <i class="fas fa-graduation-cap me-2"></i> Mahasiswa
                  <?php if ($diskon_percent > 0): ?>
                    <span class="price-option-badge">-<?= $diskon_percent ?>%</span>
                  <?php endif; ?>
                </div>
                <div class="price-option-value student">
                  Rp <?= number_format($p['harga_mahasiswa'], 0, ',', '.') ?>
                </div>
              </div>
              <div class="text-center mt-3">
                <i class="far fa-circle" style="color: rgba(255,255,255,0.5); font-size: 1.2rem;"></i>
              </div>
            </div>
          <?php elseif ($is_mahasiswa && $p['harga_mahasiswa'] == 0): ?>
            <div class="price-option disabled mt-3">
              <div class="price-option-info">
                <div class="price-option-label">
                  <i class="fas fa-graduation-cap me-2"></i> Mahasiswa
                </div>
                <div class="price-option-value">
                  -
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
        
        <?php if (!$membership_aktif): ?>
          <a href="#" 
             class="btn-select-package" 
             id="btn-paket-<?= $p['id_paket'] ?>"
             onclick="confirmPurchase(<?= $p['id_paket'] ?>, 'umum', <?= $p['harga_umum'] ?>, '<?= htmlspecialchars($p['nama_paket']) ?>')">
            <i class="fas fa-user-check me-2"></i>Pilih Paket (Umum)
          </a>
        <?php else: ?>
          <button class="btn-select-package" disabled>
            <i class="fas fa-check-circle me-2"></i>Membership Aktif
          </button>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
    
    <!-- Modal Konfirmasi Pembelian -->
    <div class="price-confirmation-modal" id="priceConfirmationModal">
      <div class="price-confirmation-content">
        <h3 class="text-center mb-4">
          <i class="fas fa-shopping-cart text-primary me-2"></i>
          Konfirmasi Pembelian
        </h3>
        
        <div class="mb-4">
          <p class="mb-2"><strong>Paket:</strong> <span id="modal-paket-name"></span></p>
          <p class="mb-2"><strong>Tipe Harga:</strong> <span id="modal-price-type"></span></p>
          <p class="mb-3"><strong>Total:</strong> <span id="modal-total-price" class="text-success fw-bold"></span></p>
          
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <small>
              <?php if ($is_mahasiswa): ?>
                Untuk harga mahasiswa, pastikan Anda membawa Kartu Tanda Mahasiswa (KTM) asli saat verifikasi di gym.
              <?php else: ?>
                Pembelian akan diproses setelah pembayaran dikonfirmasi.
              <?php endif; ?>
            </small>
          </div>
        </div>
        
        <div class="d-flex gap-3">
          <button class="btn btn-secondary flex-fill" onclick="closeModal()">
            <i class="fas fa-times me-2"></i>Batal
          </button>
          <a href="#" class="btn btn-primary flex-fill" id="modal-confirm-link">
            <i class="fas fa-check me-2"></i>Konfirmasi
          </a>
        </div>
      </div>
    </div>
    
    <!-- Catatan -->
    <div class="text-center mt-5">
      <p class="text-muted">
        <small>
          <i class="fas fa-info-circle me-1"></i>
          Semua paket sudah termasuk akses ke semua fasilitas gym dan kelas reguler.
          Harga dapat berubah tanpa pemberitahuan sebelumnya.
        </small>
      </p>
    </div>

  </div>
</section>
  
  <?php include 'sectionsmember/footer_member.php'; ?>
  <button class="scroll-to-top" onclick="scrollToTop()">Up</button>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Fungsi untuk memilih opsi harga
    function selectPriceOption(element, paketId, priceType, price) {
      // Cari card parent
      const card = element.closest('.membership-card, .trainer-card');
      
      // Reset semua opsi dalam card ini
      const allOptions = card.querySelectorAll('.price-option');
      allOptions.forEach(opt => {
        opt.classList.remove('selected');
        const icon = opt.querySelector('.fa-check-circle, .fa-circle, .far.fa-circle');
        if (icon) {
          if (icon.classList.contains('fa-check-circle')) {
            icon.className = 'far fa-circle';
            icon.style.color = 'rgba(255,255,255,0.5)';
          }
        }
      });
      
      // Pilih opsi yang diklik
      element.classList.add('selected');
      
      // Update icon
      const selectedIcon = element.querySelector('.fa-circle, .far.fa-circle');
      if (selectedIcon) {
        selectedIcon.className = 'fas fa-check-circle';
        selectedIcon.style.color = '#4CAF50';
      }
      
      // Update tombol
      const button = card.querySelector('.btn-select-package');
      if (button && !button.disabled) {
        const typeText = priceType === 'mahasiswa' ? 'Mahasiswa' : 'Umum';
        button.innerHTML = `<i class="fas fa-shopping-cart me-2"></i>Pilih Paket (${typeText})`;
        button.setAttribute('onclick', `confirmPurchase(${paketId}, '${priceType}', ${price})`);
      }
    }
    
    // Fungsi untuk konfirmasi pembelian
    function confirmPurchase(paketId, priceType, price, paketName = '') {
      // Jika paketName tidak diberikan, cari dari DOM
      if (!paketName) {
        const card = document.querySelector(`[data-paket-id="${paketId}"]`);
        if (card) {
          const nameElement = card.querySelector('.package-name, .trainer-name');
          if (nameElement) {
            paketName = nameElement.textContent;
          }
        }
      }
      
      const priceFormatted = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
      }).format(price);
      
      const typeText = priceType === 'mahasiswa' ? 'Mahasiswa' : 'Umum';
      
      // Set modal content
      document.getElementById('modal-paket-name').textContent = paketName;
      document.getElementById('modal-price-type').textContent = typeText;
      document.getElementById('modal-total-price').textContent = priceFormatted;
      
      // Set link konfirmasi
      const confirmLink = document.getElementById('modal-confirm-link');
      confirmLink.href = `checkout_pembayaran.php?id_paket=${paketId}&harga=${price}&tipe=${priceType}`;
      
      // Tampilkan modal
      document.getElementById('priceConfirmationModal').style.display = 'flex';
    }
    
    // Fungsi untuk menutup modal
    function closeModal() {
      document.getElementById('priceConfirmationModal').style.display = 'none';
    }
    
    // Tutup modal saat klik di luar
    document.getElementById('priceConfirmationModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeModal();
      }
    });
    
    // Inisialisasi opsi harga untuk semua paket
    document.addEventListener('DOMContentLoaded', function() {
      const cards = document.querySelectorAll('.membership-card, .trainer-card');
      cards.forEach(card => {
        const defaultOption = card.querySelector('.price-option.selected');
        if (defaultOption) {
          const paketId = card.getAttribute('data-paket-id');
          const priceType = defaultOption.getAttribute('data-price-type');
          const price = defaultOption.getAttribute('data-price');
          
          const button = card.querySelector('.btn-select-package:not([disabled])');
          if (button) {
            const typeText = priceType === 'mahasiswa' ? 'Mahasiswa' : 'Umum';
            button.innerHTML = `<i class="fas fa-shopping-cart me-2"></i>Pilih Paket (${typeText})`;
            button.setAttribute('onclick', `confirmPurchase(${paketId}, '${priceType}', ${price})`);
          }
        }
      });
    });
    
    function scrollToTop() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }
    window.addEventListener('scroll', () => {
      document.querySelector('.scroll-to-top').classList.toggle('visible', window.pageYOffset > 300);
    });
    
    // Auto-refresh jadwal setiap 5 menit
    setInterval(() => {
      location.reload();
    }, 300000); // 300000 ms = 5 menit
    
    // Refresh avatar foto setiap 30 detik untuk update real-time
    setInterval(() => {
      const avatarImg = document.querySelector('.member-avatar img');
      if (avatarImg) {
        // Tambahkan timestamp untuk mencegah cache
        const currentSrc = avatarImg.src;
        const separator = currentSrc.includes('?') ? '&' : '?';
        avatarImg.src = currentSrc.split('?')[0] + separator + 'refresh=' + Date.now();
      }
    }, 30000); // 30 detik
  </script>
</body>

</html>