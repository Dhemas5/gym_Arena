<!DOCTYPE html>
<html>

<head>
    <title>QR Transfer BCA</title>
    <style>
        body {
            font-family: Arial;
            text-align: center;
            margin: 40px;
        }

        .qris-box {
            border: 2px solid #333;
            padding: 20px;
            display: inline-block;
            border-radius: 12px;
        }

        img {
            width: 250px;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
        }
    </style>
</head>

<body>
    <div class="qris-box">
        <h2>GYM FIT JEMBER</h2>
        <p><strong>QR TRANSFER BCA</strong></p>

        <!-- QR Generator -->
        <img id="qrImage" src="" alt="QR BCA">

        <h3 id="rekText"></h3>
        <p><em>Scan dengan m-BCA untuk transfer otomatis</em></p>
    </div>

    <br>
    <button onclick="window.print()">CETAK QR</button>

    <script>
        // Ganti dengan rekening kamu
        let rekening = "YOUR_REKENING_HERE";
        let nama = "YOUR_NAME_HERE";

        // Link format m-BCA
        let link = `bca://transfer?no_rek=${rekening}&nama=${encodeURIComponent(nama)}`;

        // Generate QR
        document.getElementById("qrImage").src =
            "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" + encodeURIComponent(link);

        // Tampilkan nomor rekening
        document.getElementById("rekText").innerText = rekening;
    </script>

</body>

</html>