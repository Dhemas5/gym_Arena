<?php
$body_class = 'sidebar-collapse';
require "../../../setting/session.php";
checkSession("admin");
require "../../../setting/koneksi.php";

// ============================================
// LOAD PHPMailer
// ============================================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/autoload.php';

// ============================================
// FUNGSI UNTUK MENGIRIM EMAIL DENGAN PHPMailer
// ============================================
function kirimEmailPHPMailer($emailData)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'valhidayat01@gmail.com'; // Email Anda
        $mail->Password   = 'ecbnikaaznxaujbk'; // App password Anda
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Pengirim
        $mail->setFrom('valhidayat01@gmail.com', 'Admin Gym');
        $mail->addReplyTo('valhidayat01@gmail.com', 'Admin Gym');

        // Penerima
        $mail->addAddress($emailData['to_email'], $emailData['to_name']);

        // CC ke admin jika ada
        if (!empty($emailData['cc_emails'])) {
            foreach ($emailData['cc_emails'] as $cc_email) {
                $mail->addCC($cc_email);
            }
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $emailData['subject'];
        $mail->Body    = $emailData['body'];
        $mail->AltBody = strip_tags($emailData['body']);

        $mail->send();
        error_log("Email berhasil dikirim ke: " . $emailData['to_email'] . " - Subjek: " . $emailData['subject']);
        return true;
    } catch (Exception $e) {
        error_log("Email gagal dikirim ke: " . $emailData['to_email'] . " - Error: " . $mail->ErrorInfo);
        return false;
    }
}

// ============================================
// FUNGSI KIRIM EMAIL NOTIFIKASI PENDING KE ADMIN
// ============================================
function kirimNotifikasiPendingKeAdmin($con, $id_transaksi)
{
    try {
        // Ambil data transaksi
        $stmt = $con->prepare("
            SELECT t.id_transaksi, t.total, t.tgl_transaksi, 
                   m.nama, m.email, p.nama_paket
            FROM tbl_transaksi_online t
            JOIN tbl_member m ON t.id_member = m.id_member
            JOIN tbl_paket p ON t.id_paket = p.id_paket
            WHERE t.id_transaksi = ? AND t.status = 'pending'
        ");
        $stmt->bind_param("s", $id_transaksi);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return false;
        }

        $data = $result->fetch_assoc();
        $stmt->close();

        // Ambil semua email admin
        $admin_emails = [];
        $admin_query = $con->query("SELECT email FROM tbl_user WHERE role = 'admin' AND email IS NOT NULL AND email != ''");
        while ($admin = $admin_query->fetch_assoc()) {
            $admin_emails[] = $admin['email'];
        }

        // Jika tidak ada email admin, return false
        if (empty($admin_emails)) {
            return false;
        }

        // Template email HTML untuk admin
        $emailBody = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Transaksi Baru Menunggu Verifikasi</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 5px; }
                .content { padding: 25px; background: white; }
                .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #666; }
                table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                table td { padding: 10px; border-bottom: 1px solid #eee; }
                .status-pending { color: #ff9800; font-weight: bold; }
                .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>🔔 Transaksi Baru Menunggu Verifikasi</h2>
                </div>
                <div class="content">
                    <p>Halo <strong>Admin</strong>,</p>
                    <p>Ada transaksi baru yang menunggu verifikasi Anda.</p>
                    
                    <h3>Detail Transaksi:</h3>
                    <table>
                        <tr><td><strong>ID Transaksi</strong></td><td>: #' . htmlspecialchars($data['id_transaksi']) . '</td></tr>
                        <tr><td><strong>Member</strong></td><td>: ' . htmlspecialchars($data['nama']) . '</td></tr>
                        <tr><td><strong>Email Member</strong></td><td>: ' . htmlspecialchars($data['email']) . '</td></tr>
                        <tr><td><strong>Paket</strong></td><td>: ' . htmlspecialchars($data['nama_paket']) . '</td></tr>
                        <tr><td><strong>Total</strong></td><td>: Rp ' . number_format($data['total'], 0, ',', '.') . '</td></tr>
                        <tr><td><strong>Tanggal</strong></td><td>: ' . date('d/m/Y H:i', strtotime($data['tgl_transaksi'])) . '</td></tr>
                        <tr><td><strong>Status</strong></td><td>: <span class="status-pending">MENUNGGU VERIFIKASI</span></td></tr>
                    </table>
                    
                    <p>Silakan login ke dashboard admin untuk memverifikasi transaksi ini.</p>
                    
                    <p>Terima kasih,<br><strong>Sistem Gym</strong></p>
                </div>
                <div class="footer">
                    <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        // Kirim email ke semua admin
        foreach ($admin_emails as $admin_email) {
            $emailData = [
                'to_email' => $admin_email,
                'to_name' => 'Admin',
                'subject' => 'Transaksi Baru Menunggu Verifikasi - #' . $data['id_transaksi'],
                'body' => $emailBody,
                'cc_emails' => [] // Tidak perlu CC karena sudah dikirim ke semua admin
            ];

            kirimEmailPHPMailer($emailData);
        }

        return true;
    } catch (Exception $e) {
        error_log("Error kirim email notifikasi pending ke admin: " . $e->getMessage());
        return false;
    }
}

// ============================================
// FUNGSI CEK SUDAH KIRIM EMAIL PENDING ATAU BELUM
// ============================================
function cekSudahKirimEmailPending($con, $id_transaksi)
{
    $stmt = $con->prepare("SELECT email_notifikasi FROM tbl_transaksi_online WHERE id_transaksi = ?");
    $stmt->bind_param("s", $id_transaksi);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['email_notifikasi'] == 1; // Return true jika sudah dikirim
    }

    $stmt->close();
    return false;
}

// ============================================
// FUNGSI UPDATE STATUS EMAIL NOTIFIKASI
// ============================================
function updateStatusEmailNotifikasi($con, $id_transaksi, $status = 1)
{
    $stmt = $con->prepare("UPDATE tbl_transaksi_online SET email_notifikasi = ? WHERE id_transaksi = ?");
    $stmt->bind_param("is", $status, $id_transaksi);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// ============================================
// EXPORT KE EXCEL
// ============================================
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    $status_filter = $_GET['status'] ?? 'all';
    $valid_statuses = ['pending', 'approved', 'rejected', 'all'];
    $status_filter = in_array($status_filter, $valid_statuses) ? $status_filter : 'all';

    $where_clause = "";
    if ($status_filter !== 'all') {
        $where_clause = "WHERE ton.status = '" . $con->real_escape_string($status_filter) . "'";
    }

    $sql = "SELECT 
                ton.id_transaksi,
                ton.tgl_transaksi,
                m.nama AS nama_member,
                m.email,
                p.nama_paket,
                p.durasi_hari,
                ton.total,
                ton.status,
                u.username AS admin_verifikator,
                ton.tgl_verifikasi
            FROM tbl_transaksi_online ton
            JOIN tbl_member m ON ton.id_member = m.id_member
            JOIN tbl_paket p ON ton.id_paket = p.id_paket
            LEFT JOIN tbl_user u ON ton.admin_verifikasi = u.id_user
            $where_clause
            ORDER BY ton.tgl_transaksi DESC";

    $result = $con->query($sql);

    // Set header untuk download Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="transaksi_online_' . $status_filter . '_' . date('Ymd_His') . '.xls"');
    header('Cache-Control: max-age=0');

    echo '<table border="1">
        <tr>
            <th colspan="10" style="background:#f0f0f0; font-size:16px; padding:10px;">
                LAPORAN TRANSAKSI ONLINE - STATUS: ' . strtoupper($status_filter) . '<br>
                <span style="font-size:12px;">Tanggal Export: ' . date('d/m/Y H:i:s') . '</span>
            </th>
        </tr>
        <tr style="background:#4CAF50; color:white;">
            <th>NO</th>
            <th>ID TRANSAKSI</th>
            <th>TANGGAL</th>
            <th>MEMBER</th>
            <th>EMAIL</th>
            <th>PAKET</th>
            <th>DURASI</th>
            <th>TOTAL</th>
            <th>STATUS</th>
            <th>VERIFIKATOR</th>
        </tr>';

    $no = 1;
    $total_all = 0;

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $total_all += $row['total'];

            echo '<tr>
                <td>' . $no++ . '</td>
                <td>' . $row['id_transaksi'] . '</td>
                <td>' . date('d/m/Y H:i', strtotime($row['tgl_transaksi'])) . '</td>
                <td>' . $row['nama_member'] . '</td>
                <td>' . $row['email'] . '</td>
                <td>' . $row['nama_paket'] . '</td>
                <td>' . $row['durasi_hari'] . ' hari</td>
                <td>Rp ' . number_format($row['total'], 0, ',', '.') . '</td>
                <td>' . strtoupper($row['status']) . '</td>
                <td>' . ($row['admin_verifikator'] ? $row['admin_verifikator'] : '-') . '</td>
            </tr>';
        }
    }

    echo '<tr style="background:#f0f0f0; font-weight:bold;">
            <td colspan="7" align="right">TOTAL SELURUH TRANSAKSI:</td>
            <td>Rp ' . number_format($total_all, 0, ',', '.') . '</td>
            <td colspan="2"></td>
        </tr>
    </table>';
    exit;
}

// ============================================
// CETAK LAPORAN (PDF/PRINT VIEW)
// ============================================
if (isset($_GET['cetak']) && $_GET['cetak'] == '1') {
    $status_filter = $_GET['status'] ?? 'all';
    $valid_statuses = ['pending', 'approved', 'rejected', 'all'];
    $status_filter = in_array($status_filter, $valid_statuses) ? $status_filter : 'all';

    $where_clause = "";
    if ($status_filter !== 'all') {
        $where_clause = "WHERE ton.status = '" . $con->real_escape_string($status_filter) . "'";
    }

    $sql = "SELECT 
                ton.*, 
                m.nama AS nama_member, 
                m.email,
                p.nama_paket, 
                p.durasi_hari,
                u.username AS admin_verifikator
            FROM tbl_transaksi_online ton
            JOIN tbl_member m ON ton.id_member = m.id_member
            JOIN tbl_paket p ON ton.id_paket = p.id_paket
            LEFT JOIN tbl_user u ON ton.admin_verifikasi = u.id_user
            $where_clause
            ORDER BY ton.tgl_transaksi DESC";

    $transaksi = $con->query($sql);

    // Hitung total
    $total_sql = "SELECT SUM(total) as grand_total FROM tbl_transaksi_online";
    if ($status_filter !== 'all') {
        $total_sql .= " WHERE status = '" . $con->real_escape_string($status_filter) . "'";
    }
    $total_result = $con->query($total_sql);
    $total_row = $total_result->fetch_assoc();
    $grand_total = $total_row['grand_total'] ?? 0;
?>
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <title>Cetak Transaksi Online</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
            }

            .container {
                width: 100%;
                margin: 0 auto;
            }

            .header {
                text-align: center;
                margin-bottom: 20px;
            }

            .header h1 {
                margin: 0;
                font-size: 20px;
            }

            .header .subtitle {
                margin: 5px 0;
                color: #666;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }

            table th {
                background-color: #f2f2f2;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
            }

            table td {
                padding: 6px;
                border: 1px solid #ddd;
            }

            .text-right {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }

            .footer {
                margin-top: 30px;
                padding-top: 10px;
                border-top: 1px solid #ddd;
            }

            .page-break {
                page-break-after: always;
            }

            .status-pending {
                color: #ff9800;
                font-weight: bold;
            }

            .status-approved {
                color: #4caf50;
                font-weight: bold;
            }

            .status-rejected {
                color: #f44336;
                font-weight: bold;
            }

            @media print {
                .no-print {
                    display: none;
                }

                .print-btn {
                    display: none;
                }
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="header">
                <h1>LAPORAN TRANSAKSI ONLINE</h1>
                <div class="subtitle">Status: <?= strtoupper($status_filter) ?></div>
                <div class="subtitle">Tanggal Cetak: <?= date('d/m/Y H:i:s') ?></div>
            </div>

            <div class="no-print" style="margin-bottom: 10px;">
                <button class="print-btn" onclick="window.print()" style="padding: 8px 15px; background: #4CAF50; color: white; border: none; cursor: pointer;">
                    🖨️ Cetak Halaman
                </button>
                <button class="print-btn" onclick="window.close()" style="padding: 8px 15px; background: #f44336; color: white; border: none; cursor: pointer;">
                    ✖️ Tutup
                </button>
            </div>

            <table>
                <thead>
                    <tr>
                        <th width="5%">NO</th>
                        <th width="12%">ID TRANSAKSI</th>
                        <th width="15%">MEMBER</th>
                        <th width="15%">PAKET</th>
                        <th width="10%">TOTAL</th>
                        <th width="15%">TANGGAL</th>
                        <th width="8%">STATUS</th>
                        <th width="10%">VERIFIKATOR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if ($transaksi && $transaksi->num_rows > 0) {
                        while ($row = $transaksi->fetch_assoc()) {
                            $status_class = 'status-' . $row['status'];
                    ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= $row['id_transaksi'] ?></td>
                                <td>
                                    <div><strong><?= $row['nama_member'] ?></strong></div>
                                    <small><?= $row['email'] ?></small>
                                </td>
                                <td>
                                    <div><?= $row['nama_paket'] ?></div>
                                    <small><?= $row['durasi_hari'] ?> hari</small>
                                </td>
                                <td class="text-right">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                <td>
                                    <div><?= date('d/m/Y H:i', strtotime($row['tgl_transaksi'])) ?></div>
                                    <?php if ($row['tgl_verifikasi']): ?>
                                        <small>Verif: <?= date('d/m/Y H:i', strtotime($row['tgl_verifikasi'])) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center <?= $status_class ?>">
                                    <?= strtoupper($row['status']) ?>
                                </td>
                                <td class="text-center"><?= $row['admin_verifikator'] ? $row['admin_verifikator'] : '-' ?></td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="8" class="text-center">Tidak ada data transaksi</td></tr>';
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr style="background-color: #f9f9f9;">
                        <td colspan="4" class="text-right"><strong>GRAND TOTAL:</strong></td>
                        <td class="text-right"><strong>Rp <?= number_format($grand_total, 0, ',', '.') ?></strong></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>

            <div class="footer">
                <div style="float: left;">
                    <p>Dicetak oleh: <?= $_SESSION['username'] ?? 'Admin' ?></p>
                </div>
                <div style="float: right; text-align: right;">
                    <p>Total Data: <?= $transaksi ? $transaksi->num_rows : 0 ?> transaksi</p>
                </div>
                <div style="clear: both;"></div>
            </div>
        </div>

        <script>
            window.onload = function() {
                // Auto print jika diinginkan
                // window.print();
            }
        </script>
    </body>

    </html>
<?php
    exit;
}

// ============================================
// PROSES APPROVE / REJECT (AJAX HANDLER)
// ============================================
if (isset($_POST['action']) && isset($_POST['id_transaksi'])) {
    header('Content-Type: application/json');
    $id_trx = $_POST['id_transaksi'];
    $action = $_POST['action']; // approve atau reject

    try {
        $con->begin_transaction();

        if ($action === 'approve') {
            // 1. Ambil data transaksi pending
            $stmt = $con->prepare("SELECT id_member, id_paket, total FROM tbl_transaksi_online WHERE id_transaksi = ? AND status = 'pending'");
            $stmt->bind_param("s", $id_trx);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("Transaksi tidak ditemukan atau sudah diproses");
            }

            $trx = $result->fetch_assoc();
            $stmt->close();

            $id_member = (int)$trx['id_member'];
            $id_paket  = (int)$trx['id_paket'];
            $total_trx = (float)$trx['total'];

            // 2. Ambil data member
            $stmt = $con->prepare("SELECT nama, email FROM tbl_member WHERE id_member = ?");
            $stmt->bind_param("i", $id_member);
            $stmt->execute();
            $member_result = $stmt->get_result();
            $member_data = $member_result->fetch_assoc();
            $nama_member = $member_data['nama'] ?? 'Member';
            $email_member = $member_data['email'] ?? '';
            $stmt->close();

            // 3. Ambil data paket
            $stmt = $con->prepare("SELECT nama_paket, durasi_hari FROM tbl_paket WHERE id_paket = ?");
            $stmt->bind_param("i", $id_paket);
            $stmt->execute();
            $paket_result = $stmt->get_result();
            $paket_data = $paket_result->fetch_assoc();
            $nama_paket = $paket_data['nama_paket'] ?? 'Paket';
            $durasi = isset($paket_data['durasi_hari']) ? (int)$paket_data['durasi_hari'] : 30;
            $stmt->close();

            // 4. Hitung tanggal berakhir membership
            $tgl_mulai = date('Y-m-d H:i:s');
            $tgl_berakhir = date('Y-m-d 23:59:59', strtotime("+$durasi days"));

            // 5. Insert ke tabel membership
            $stmt = $con->prepare("INSERT INTO tbl_membership (id_member, id_transaksi, id_paket, tgl_mulai, tgl_berakhir, sumber) VALUES (?, ?, ?, ?, ?, 'online')");
            $stmt->bind_param("isiss", $id_member, $id_trx, $id_paket, $tgl_mulai, $tgl_berakhir);
            $stmt->execute();
            $stmt->close();

            // 6. Update status member menjadi aktif
            $stmt = $con->prepare("UPDATE tbl_member SET membership_status = 'aktif' WHERE id_member = ?");
            $stmt->bind_param("i", $id_member);
            $stmt->execute();
            $stmt->close();

            // 7. Update status transaksi online
            $stmt = $con->prepare("UPDATE tbl_transaksi_online SET status = 'approved', admin_verifikasi = ?, tgl_verifikasi = NOW() WHERE id_transaksi = ?");
            $stmt->bind_param("is", $_SESSION['id_user'], $id_trx);
            $stmt->execute();
            $stmt->close();

            // 8. Kirim email notifikasi approval ke member
            $emailBodyApproval = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Pembayaran Disetujui</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #d4edda; padding: 20px; text-align: center; border-radius: 5px; color: #155724; }
                    .content { padding: 25px; background: white; }
                    .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #666; }
                    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                    table td { padding: 10px; border-bottom: 1px solid #eee; }
                    .status-success { color: #28a745; font-weight: bold; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>🎉 Pembayaran Disetujui!</h2>
                    </div>
                    <div class="content">
                        <p>Halo <strong>' . htmlspecialchars($nama_member) . '</strong>,</p>
                        <p>Selamat! Pembayaran Anda telah <strong class="status-success">DISETUJUI</strong> dan membership Anda sekarang aktif.</p>
                        
                        <h3>Detail Membership:</h3>
                        <table>
                            <tr><td><strong>ID Transaksi</strong></td><td>: #' . htmlspecialchars($id_trx) . '</td></tr>
                            <tr><td><strong>Paket</strong></td><td>: ' . htmlspecialchars($nama_paket) . '</td></tr>
                            <tr><td><strong>Total</strong></td><td>: Rp ' . number_format($total_trx, 0, ',', '.') . '</td></tr>
                            <tr><td><strong>Berlaku Sampai</strong></td><td>: ' . date('d/m/Y', strtotime($tgl_berakhir)) . '</td></tr>
                        </table>
                        
                        <p>Anda sekarang dapat menggunakan fasilitas gym sesuai dengan paket yang Anda pilih.</p>
                        
                        <p>Terima kasih telah bergabung dengan kami!<br><strong>Admin Gym</strong></p>
                    </div>
                    <div class="footer">
                        <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
                    </div>
                </div>
            </body>
            </html>
            ';

            $emailDataApproval = [
                'to_email' => $email_member,
                'to_name' => $nama_member,
                'subject' => 'Pembayaran Disetujui - Membership Aktif - #' . $id_trx,
                'body' => $emailBodyApproval,
                'cc_emails' => []
            ];

            kirimEmailPHPMailer($emailDataApproval);

            $con->commit();
            echo json_encode(['success' => true, 'msg' => 'Transaksi berhasil disetujui & membership diaktifkan!']);
        } elseif ($action === 'reject') {
            // 1. Ambil data member untuk email
            $stmt = $con->prepare("SELECT m.nama, m.email FROM tbl_transaksi_online t JOIN tbl_member m ON t.id_member = m.id_member WHERE t.id_transaksi = ?");
            $stmt->bind_param("s", $id_trx);
            $stmt->execute();
            $member_result = $stmt->get_result();
            $member_data = $member_result->fetch_assoc();
            $nama_member = $member_data['nama'] ?? 'Member';
            $email_member = $member_data['email'] ?? '';
            $stmt->close();

            // 2. Update status transaksi menjadi rejected
            $stmt = $con->prepare("UPDATE tbl_transaksi_online SET status = 'rejected', admin_verifikasi = ?, tgl_verifikasi = NOW() WHERE id_transaksi = ? AND status = 'pending'");
            $stmt->bind_param("is", $_SESSION['id_user'], $id_trx);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Transaksi tidak ditemukan atau sudah diproses");
            }
            $stmt->close();

            // 3. Kirim email notifikasi rejection ke member
            $emailBodyRejection = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Pembayaran Ditolak</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #f8d7da; padding: 20px; text-align: center; border-radius: 5px; color: #721c24; }
                    .content { padding: 25px; background: white; }
                    .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #666; }
                    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                    table td { padding: 10px; border-bottom: 1px solid #eee; }
                    .status-rejected { color: #dc3545; font-weight: bold; }
                    ul { padding-left: 20px; }
                    li { margin-bottom: 5px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>⚠️ Pembayaran Ditolak</h2>
                    </div>
                    <div class="content">
                        <p>Halo <strong>' . htmlspecialchars($nama_member) . '</strong>,</p>
                        <p>Mohon maaf, pembayaran Anda dengan ID Transaksi <strong>#' . htmlspecialchars($id_trx) . '</strong> telah <strong class="status-rejected">DITOLAK</strong>.</p>
                        
                        <p><strong>Alasan penolakan:</strong></p>
                        <ul>
                            <li>Bukti pembayaran tidak jelas/tidak terbaca</li>
                            <li>Jumlah pembayaran tidak sesuai</li>
                            <li>Informasi tidak lengkap</li>
                        </ul>
                        
                        <p>Silakan hubungi admin gym untuk informasi lebih lanjut atau lakukan pembayaran ulang dengan mengikuti prosedur yang benar.</p>
                        
                        <p>Terima kasih,<br><strong>Admin Gym</strong></p>
                    </div>
                    <div class="footer">
                        <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
                    </div>
                </div>
            </body>
            </html>
            ';

            $emailDataRejection = [
                'to_email' => $email_member,
                'to_name' => $nama_member,
                'subject' => 'Pembayaran Ditolak - #' . $id_trx,
                'body' => $emailBodyRejection,
                'cc_emails' => []
            ];

            kirimEmailPHPMailer($emailDataRejection);

            $con->commit();
            echo json_encode(['success' => true, 'msg' => 'Transaksi berhasil ditolak']);
        } else {
            throw new Exception("Action tidak valid");
        }
    } catch (Exception $e) {
        $con->rollback();
        echo json_encode(['success' => false, 'msg' => 'Gagal: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================
// TAMPILAN HALAMAN (NON-AJAX)
// ============================================
$status_filter = $_GET['status'] ?? 'pending';
$valid_statuses = ['pending', 'approved', 'rejected', 'all'];
$status_filter = in_array($status_filter, $valid_statuses) ? $status_filter : 'pending';

// Query untuk menghitung statistik
$stats_sql = "SELECT 
    status,
    COUNT(*) as count,
    SUM(total) as total_amount
FROM tbl_transaksi_online 
GROUP BY status";

$stats_result = $con->query($stats_sql);
$stats = [
    'pending' => ['count' => 0, 'total' => 0],
    'approved' => ['count' => 0, 'total' => 0],
    'rejected' => ['count' => 0, 'total' => 0],
    'all' => ['count' => 0, 'total' => 0]
];

if ($stats_result) {
    while ($row = $stats_result->fetch_assoc()) {
        $s = $row['status'];
        $stats[$s] = [
            'count' => (int)$row['count'],
            'total' => (float)$row['total_amount']
        ];
        $stats['all']['count'] += (int)$row['count'];
        $stats['all']['total'] += (float)$row['total_amount'];
    }
}

// Query data transaksi berdasarkan filter
$where_clause = "";
if ($status_filter !== 'all') {
    $where_clause = "WHERE ton.status = '" . $con->real_escape_string($status_filter) . "'";
}

$sql = "SELECT 
            ton.*, 
            m.nama AS nama_member, 
            m.email,
            p.nama_paket, 
            p.durasi_hari,
            p.harga_umum,
            p.harga_mahasiswa,
            u.username AS admin_verifikator
        FROM tbl_transaksi_online ton
        JOIN tbl_member m ON ton.id_member = m.id_member
        JOIN tbl_paket p ON ton.id_paket = p.id_paket
        LEFT JOIN tbl_user u ON ton.admin_verifikasi = u.id_user
        $where_clause
        ORDER BY ton.tgl_transaksi DESC";

$transaksi = $con->query($sql);
?>

<?php include '../../../view/master/header.php'; ?>
<?php include '../../../view/master/sidebar.php'; ?>

<!-- DARK MODE CSS (inline) -->
<style>
    /* Basic */
    body {
        background: #0f1117;
        color: #e5e7eb;
        font-family: 'Poppins', sans-serif;
    }

    .content,
    .container-fluid {
        color: #e5e7eb;
    }

    /* Card / Panel */
    /* Fix container kepotong */
    .content-wrapper,
    .container-fluid,
    .card,
    .card-glass {
        overflow: visible !important;
    }

    /* Agar tabel tidak kepotong kanan */
    .table-responsive {
        overflow-x: auto !important;
    }

    /* Tambahkan ruang pinggir */
    .card-glass {
        padding-left: 20px !important;
        padding-right: 20px !important;
    }


    /* Info boxes */
    .info-box {
        border-radius: 12px;
    }

    .info-box .info-box-icon {
        background: rgba(255, 255, 255, 0.04);
        color: #e5e7eb;
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #0ea5e9, #2563eb) !important;
        color: #fff;
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #10b981, #059669) !important;
        color: #fff;
    }

    .bg-gradient-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626) !important;
        color: #fff;
    }

    .bg-gradient-secondary {
        background: linear-gradient(135deg, #6b7280, #4b5563) !important;
        color: #fff;
    }

    /* Table */
    .table th {
        background: linear-gradient(135deg, #1f2937, #111827);
        color: #e5e7eb;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    }

    .table td {
        background: transparent;
        border-top: 1px solid rgba(255, 255, 255, 0.02);
        color: #d1d5db;
    }

    .table-hover tbody tr:hover td {
        background: rgba(255, 255, 255, 0.02);
        transform: translateY(-1px);
    }

    .table-dark {
        background: transparent;
    }

    /* Badges */
    .badge {
        background: rgba(255, 255, 255, 0.04);
        color: #e5e7eb;
        border-radius: 8px;
        padding: 6px 10px;
        font-weight: 700;
    }

    .badge-success {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
    }

    .badge-warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff;
    }

    .badge-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff;
    }

    /* Buttons */
    .btn {
        border-radius: 8px;
        font-weight: 600;
        transition: all .2s;
    }

    .btn-primary {
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        border: none;
        color: #fff;
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981, #059669);
        border: none;
        color: #fff;
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border: none;
        color: #fff;
    }

    .btn-info {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        border: none;
        color: #fff;
    }

    .btn-outline-primary,
    .btn-outline-success,
    .btn-outline-danger,
    .btn-outline-secondary {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.06);
        color: #e5e7eb;
    }

    .btn-xs {
        padding: 6px 8px;
        font-size: .8rem;
    }

    .btn-group-vertical .btn {
        display: block;
        margin: 3px 0;
        width: 100%;
    }

    /* Modal (dark) */
    .modal-content {
        background: #0f1724;
        color: #e5e7eb;
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(2, 6, 23, 0.8);
    }

    .modal-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    }

    .modal-header .close {
        color: #fff;
        opacity: .9;
    }

    /* Small helpers */
    .text-muted {
        color: #9ca3af !important;
    }

    .text-primary {
        color: #60a5fa !important;
    }

    .text-success {
        color: #10b981 !important;
    }

    .text-info {
        color: #38bdf8 !important;
    }

    .fw-bold {
        font-weight: 700;
    }

    /* Preview image */
    #imgBukti {
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.02);
        padding: 8px;
    }

    @media (max-width: 768px) {
        .btn-group-vertical .btn {
            font-size: 0.85rem;
            padding: 8px 10px;
        }
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-credit-card"></i> Verifikasi Pembayaran Online</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#" style="color: #9ca3af">Admin</a></li>
                    <li class="breadcrumb-item active">Verifikasi Pembayaran</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Statistik Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="info-box bg-gradient-info">
                    <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Menunggu</span>
                        <span class="info-box-number"><?= $stats['pending']['count'] ?></span>
                        <span class="progress-description">
                            Rp <?= number_format($stats['pending']['total'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-gradient-success">
                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Disetujui</span>
                        <span class="info-box-number"><?= $stats['approved']['count'] ?></span>
                        <span class="progress-description">
                            Rp <?= number_format($stats['approved']['total'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-gradient-danger">
                    <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Ditolak</span>
                        <span class="info-box-number"><?= $stats['rejected']['count'] ?></span>
                        <span class="progress-description">
                            Rp <?= number_format($stats['rejected']['total'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-gradient-secondary">
                    <span class="info-box-icon"><i class="fas fa-list"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total</span>
                        <span class="info-box-number"><?= $stats['all']['count'] ?></span>
                        <span class="progress-description">
                            Rp <?= number_format($stats['all']['total'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filter Status</h3>
                <div class="card-tools">
                    <span class="badge badge-light">Total: <?= $transaksi ? $transaksi->num_rows : 0 ?> transaksi</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="btn-group">
                            <a href="?status=pending" class="btn <?= $status_filter == 'pending' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                <i class="fas fa-clock"></i> Menunggu (<?= $stats['pending']['count'] ?>)
                            </a>
                            <a href="?status=approved" class="btn <?= $status_filter == 'approved' ? 'btn-success' : 'btn-outline-success' ?>">
                                <i class="fas fa-check-circle"></i> Disetujui (<?= $stats['approved']['count'] ?>)
                            </a>
                            <a href="?status=rejected" class="btn <?= $status_filter == 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">
                                <i class="fas fa-times-circle"></i> Ditolak (<?= $stats['rejected']['count'] ?>)
                            </a>
                            <a href="?status=all" class="btn <?= $status_filter == 'all' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                                <i class="fas fa-list"></i> Semua (<?= $stats['all']['count'] ?>)
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 text-right">
                        <div class="small text-muted mt-1">
                            Ditampilkan: <strong><?= $status_filter == 'all' ? 'Semua Status' : ucfirst($status_filter) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="card">
            <div class="card-header <?= $status_filter == 'pending' ? 'bg-gradient-info' : ($status_filter == 'approved' ? 'bg-gradient-success' : ($status_filter == 'rejected' ? 'bg-gradient-danger' : 'bg-gradient-secondary')) ?> text-white">
                <h3 class="card-title">
                    <i class="fas fa-list"></i>
                    Daftar Transaksi - <?= $status_filter == 'all' ? 'Semua Status' : ucfirst($status_filter) ?>
                </h3>
                <div class="card-tools">
                    <div class="btn-group">
                        <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="?export=excel&status=<?= $status_filter ?>">
                                <i class="fas fa-file-excel text-success"></i> Export ke Excel
                            </a>
                            <a class="dropdown-item" href="?cetak=1&status=<?= $status_filter ?>" target="_blank">
                                <i class="fas fa-print text-info"></i> Cetak Laporan
                            </a>
                        </div>
                    </div>
                    <span class="badge badge-light badge-lg"><?= $transaksi ? $transaksi->num_rows : 0 ?> transaksi</span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (!$transaksi || $transaksi->num_rows == 0): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-5x text-muted mb-3"></i>
                        <h4 class="text-muted">Tidak ada transaksi</h4>
                        <p class="text-muted">Tidak ditemukan transaksi dengan status "<?= $status_filter ?>"</p>
                    </div>
                <?php else: ?>
                    <div class="card-glass">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabelPelatih" style="font-size: 0.9rem;">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="11%">ID TRANSAKSI</th>
                                        <th width="15%">MEMBER</th>
                                        <th width="14%">PAKET</th>
                                        <th width="9%">TOTAL</th>
                                        <th width="13%">TANGGAL</th>
                                        <th width="8%">STATUS</th>
                                        <th width="10%">VERIFIKATOR</th>
                                        <th width="9%">BUKTI</th>
                                        <th width="11%" class="text-center">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $counter = 0;
                                    while ($row = $transaksi->fetch_assoc()):
                                        $counter++;
                                        // Kirim notifikasi email ke admin untuk transaksi pending yang baru
                                        if ($row['status'] == 'pending' && $counter <= 5) {
                                            // Cek apakah sudah pernah dikirim email
                                            if (!cekSudahKirimEmailPending($con, $row['id_transaksi'])) {
                                                kirimNotifikasiPendingKeAdmin($con, $row['id_transaksi']);
                                                updateStatusEmailNotifikasi($con, $row['id_transaksi'], 1);
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <!-- ID TRANSAKSI -->
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="badge <?= $row['status'] == 'pending' ? 'badge-warning text-dark fw-bold' : ($row['status'] == 'approved' ? 'badge-success fw-bold' : 'badge-danger fw-bold') ?>">
                                                        <?= htmlspecialchars($row['id_transaksi']) ?>
                                                    </span>
                                                    <small class="text-muted mt-1">
                                                        <?= date('d/m/Y', strtotime($row['tgl_transaksi'])) ?>
                                                    </small>
                                                </div>
                                            </td>

                                            <!-- MEMBER -->
                                            <td style="white-space: normal; word-break: break-word;">
                                                <div>
                                                    <strong class="text-primary d-block"><?= htmlspecialchars($row['nama_member']) ?></strong>
                                                    <small class="text-muted d-block" style="font-size: 0.8rem;">
                                                        <?= htmlspecialchars($row['email']) ?>
                                                    </small>
                                                </div>
                                            </td>

                                            <!-- PAKET -->
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($row['nama_paket']) ?></strong>
                                                    <small class="text-muted d-block">
                                                        <?= htmlspecialchars($row['durasi_hari']) ?> hari
                                                    </small>
                                                </div>
                                            </td>

                                            <!-- TOTAL -->
                                            <td>
                                                <strong class="text-success">Rp <?= number_format($row['total'], 0, ',', '.') ?></strong>
                                            </td>

                                            <!-- TANGGAL -->
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span><?= date('d/m/Y H:i', strtotime($row['tgl_transaksi'])) ?></span>
                                                    <?php if (!empty($row['tgl_verifikasi'])): ?>
                                                        <small class="text-info">
                                                            Verif: <?= date('d/m/Y H:i', strtotime($row['tgl_verifikasi'])) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>

                                            <!-- STATUS -->
                                            <td>
                                                <span class="<?= $row['status'] == 'pending' ? 'badge badge-warning' : ($row['status'] == 'approved' ? 'badge badge-success' : 'badge badge-danger') ?>">
                                                    <?= strtoupper($row['status']) ?>
                                                </span>
                                            </td>

                                            <!-- VERIFIKATOR -->
                                            <td>
                                                <?php if ($row['admin_verifikator']): ?>
                                                    <span class="text-success fw-bold"><?= htmlspecialchars($row['admin_verifikator']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- BUKTI -->
                                            <td>
                                                <?php
                                                $bukti_path = "../../../Uploads/bukti_pembayaran/" . $row['bukti_pembayaran'];
                                                $file_exists = !empty($row['bukti_pembayaran']) && file_exists($bukti_path);
                                                ?>
                                                <button class="btn btn-xs btn-info btn-preview-bukti"
                                                    data-bukti="<?= htmlspecialchars($row['bukti_pembayaran']) ?>"
                                                    data-id="<?= htmlspecialchars($row['id_transaksi']) ?>"
                                                    <?= !$file_exists ? 'disabled' : '' ?>
                                                    title="Lihat Bukti Pembayaran">
                                                    <i class="fas fa-image"></i>
                                                    <?= $file_exists ? ' Lihat' : ' No File' ?>
                                                </button>
                                            </td>

                                            <!-- AKSI -->
                                            <td class="text-center">
                                                <?php if ($row['status'] == 'pending'): ?>
                                                    <div class="btn-group-vertical btn-group-xs" style="width: 100px; margin: 0 auto;">
                                                        <button class="btn btn-success btn-approve"
                                                            data-id="<?= htmlspecialchars($row['id_transaksi']) ?>"
                                                            data-member="<?= htmlspecialchars($row['nama_member']) ?>"
                                                            data-paket="<?= htmlspecialchars($row['nama_paket']) ?>"
                                                            data-total="Rp <?= number_format($row['total'], 0, ',', '.') ?>"
                                                            title="Setujui Transaksi">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-reject"
                                                            data-id="<?= htmlspecialchars($row['id_transaksi']) ?>"
                                                            data-member="<?= htmlspecialchars($row['nama_member']) ?>"
                                                            title="Tolak Transaksi">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="<?= $row['status'] == 'approved' ? 'badge badge-success' : 'badge badge-danger' ?>">
                                                        <?= $row['status'] == 'approved' ? '✓' : '✗' ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            Total data: <?= $transaksi ? $transaksi->num_rows : 0 ?> transaksi
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Bukti -->
<div class="modal fade" id="modalBukti" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-image"></i> Bukti Pembayaran - <span id="modalTrxId"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="imgBukti" src="" alt="Bukti Pembayaran" class="img-fluid rounded" style="max-height: 70vh;">
                <div class="mt-3">
                    <a href="#" id="downloadBukti" class="btn btn-success btn-sm" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../../view/master/footer.php'; ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Preview Bukti Pembayaran
        $(document).on('click', '.btn-preview-bukti', function() {
            const buktiFile = $(this).data('bukti');
            const trxId = $(this).data('id');
            const buktiPath = '../../../Uploads/bukti_pembayaran/' + buktiFile;

            $('#modalTrxId').text('#' + trxId);
            $('#imgBukti').attr('src', buktiPath);
            $('#downloadBukti').attr('href', buktiPath);
            $('#modalBukti').modal('show');
        });

        // Approve Transaksi
        $(document).on('click', '.btn-approve', function() {
            const id = $(this).data('id');
            const member = $(this).data('member');
            const paket = $(this).data('paket');
            const total = $(this).data('total');

            Swal.fire({
                title: 'Setujui Pembayaran?',
                html: `<div class="text-left">
                    <p><strong>Detail Transaksi:</strong></p>
                    <ul style="text-align:left;">
                        <li><strong>Member:</strong> ${member}</li>
                        <li><strong>Paket:</strong> ${paket}</li>
                        <li><strong>Total:</strong> ${total}</li>
                    </ul>
                    <p class="text-success"><i class="fas fa-info-circle"></i> Membership akan langsung aktif setelah disetujui!</p>
                   </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check"></i> Ya, Setujui',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                reverseButtons: true,
                width: '600px'
            }).then((result) => {
                if (result.isConfirmed) {
                    processAction(id, 'approve');
                }
            });
        });

        // Reject Transaksi
        $(document).on('click', '.btn-reject', function() {
            const id = $(this).data('id');
            const member = $(this).data('member');

            Swal.fire({
                title: 'Tolak Pembayaran?',
                html: `<div class="text-left">
                    <p>Anda akan menolak pembayaran dari:</p>
                    <p><strong>${member}</strong></p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Transaksi akan ditandai sebagai ditolak.</p>
                   </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-times"></i> Ya, Tolak',
                cancelButtonText: '<i class="fas fa-arrow-left"></i> Batal',
                reverseButtons: true,
                width: '500px'
            }).then((result) => {
                if (result.isConfirmed) {
                    processAction(id, 'reject');
                }
            });
        });

        // Process Action
        function processAction(id, action) {
            const button = $(`.btn-${action}[data-id="${id}"]`);
            const originalHtml = button.html();

            // Disable button and show loading
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            // Send AJAX request
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: action,
                    id_transaksi: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.msg,
                            icon: 'success',
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'OK',
                            timer: 1500,
                            timerProgressBar: true
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: response.msg,
                            icon: 'error',
                            confirmButtonColor: '#dc3545',
                            confirmButtonText: 'OK'
                        });
                        button.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat memproses permintaan.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK'
                    });
                    button.prop('disabled', false).html(originalHtml);
                }
            });
        }

        // Auto refresh every 30 seconds if there are pending transactions
        <?php if ($status_filter == 'pending' && $transaksi && $transaksi->num_rows > 0): ?>
            let autoRefreshInterval = setInterval(() => {
                console.log('Auto-refresh untuk transaksi pending...');
                // Optional: only reload if no modal is open
                if (!$('#modalBukti').hasClass('show')) {
                    location.reload();
                }
            }, 30000);
        <?php endif; ?>

        // Fitur Export Excel dengan SweetAlert
        $('.btn-export-excel').click(function(e) {
            e.preventDefault();
            const status = '<?= $status_filter ?>';
            Swal.fire({
                title: 'Export ke Excel?',
                html: `Anda akan mengekspor data transaksi dengan status <strong>${status}</strong> ke format Excel.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-file-excel"></i> Export',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?export=excel&status=${status}`;
                }
            });
        });
    });
</script>