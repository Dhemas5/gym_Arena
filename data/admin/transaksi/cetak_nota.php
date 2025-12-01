<?php
require "../../../setting/session.php";
require "../../../setting/koneksi.php";

if (!isset($_GET['id'])) {
    die('ID Transaksi tidak valid');
}

$id_transaksi = intval($_GET['id']);

$sql = "SELECT 
        t.id_transaksi,
        DATE_FORMAT(t.tanggal_transaksi, '%d/%m/%Y %H:%i') as tanggal,
        u.nama as kasir,
        COALESCE(m.nama, 'Pelanggan Umum') as member,
        p.nama_paket,
        t.durasi_hari,
        t.harga_paket,
        t.diskon,
        t.grand_total,
        t.jumlah_dibayar,
        t.kembalian,
        t.metode_pembayaran
        FROM tbl_transaksi t
        LEFT JOIN tbl_user u ON t.id_user_kasir = u.id_user
        LEFT JOIN tbl_member m ON t.id_member = m.id_member
        LEFT JOIN tbl_paket p ON t.id_paket = p.id_paket
        WHERE t.id_transaksi = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_transaksi);
$stmt->execute();
$result = $stmt->get_result();
$transaksi = $result->fetch_assoc();

if (!$transaksi) {
    die('Transaksi tidak ditemukan');
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Nota Transaksi #<?= sprintf('TRX%06d', $transaksi['id_transaksi']) ?></title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-width: 300px;
            margin: 0 auto;
            padding: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .d-flex {
            display: flex;
        }

        .justify-between {
            justify-content: space-between;
        }

        .mb-1 {
            margin-bottom: 5px;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        .mt-2 {
            margin-top: 10px;
        }

        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .nota-container {
            display: none;
            font-family: 'Courier New', monospace;
            max-width: 300px;
            margin: 20px auto;
            padding: 15px;
            border: 1px dashed #ccc;
            background: white;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1050;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .nota-container,
            .nota-container * {
                visibility: visible;
            }

            .nota-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="text-center mb-2">
        <h3 class="mb-1">GYM FITNESS CENTER</h3>
        <p class="mb-1">Jl. Contoh No. 123, Jakarta</p>
        <p class="mb-1">Telp: (021) 123-4567</p>
        <hr>
    </div>

    <div class="mb-2">
        <div class="d-flex justify-between mb-1">
            <span>No. Transaksi:</span>
            <span>TRX<?= str_pad($transaksi['id_transaksi'], 6, '0', STR_PAD_LEFT) ?></span>
        </div>
        <div class="d-flex justify-between mb-1">
            <span>Tanggal:</span>
            <span><?= $transaksi['tanggal'] ?></span>
        </div>
        <div class="d-flex justify-between mb-1">
            <span>Kasir:</span>
            <span><?= $transaksi['kasir'] ?></span>
        </div>
    </div>

    <hr>

    <div class="mb-2">
        <div class="d-flex justify-between mb-1">
            <span>Member:</span>
            <span><?= $transaksi['member'] ?></span>
        </div>
        <div class="d-flex justify-between mb-1">
            <span>Paket:</span>
            <span><?= $transaksi['nama_paket'] ?></span>
        </div>
        <div class="d-flex justify-between mb-1">
            <span>Durasi:</span>
            <span><?= $transaksi['durasi_hari'] ?> hari</span>
        </div>
    </div>

    <hr>

    <div class="mb-2">
        <div class="d-flex justify-between mb-1">
            <span>Harga:</span>
            <span>Rp <?= number_format($transaksi['harga_paket'], 0, ',', '.') ?></span>
        </div>
        <div class="d-flex justify-between mb-1">
            <span>Diskon:</span>
            <span>Rp <?= number_format($transaksi['diskon'], 0, ',', '.') ?></span>
        </div>
        <div class="d-flex justify-between mb-1">
            <span>Total:</span>
            <span>Rp <?= number_format($transaksi['grand_total'], 0, ',', '.') ?></span>
        </div>
        <div class="d-flex justify-between mb-1">
            <span>Dibayar:</span>
            <span>Rp <?= number_format($transaksi['jumlah_dibayar'], 0, ',', '.') ?></span>
        </div>
        <div class="d-flex justify-between mb-1">
            <span>Kembalian:</span>
            <span>Rp <?= number_format($transaksi['kembalian'], 0, ',', '.') ?></span>
        </div>
        <div class="d-flex justify-between mb-1">
            <span>Metode:</span>
            <span><?= $transaksi['metode_pembayaran'] ?></span>
        </div>
    </div>

    <hr>

    <div class="text-center mt-2">
        <p class="mb-1">Terima kasih atas kunjungan Anda</p>
        <p class="mb-1">*** Semoga Sehat Selalu ***</p>
    </div>
</body>

</html>
<?php
$stmt->close();
$con->close();
?>