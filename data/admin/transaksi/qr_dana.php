<!DOCTYPE html>
<html>

<head>
    <title>QRIS DANA GYM</title>
    <style>
        body {
            font-family: Arial;
            text-align: center;
            margin: 50px;
        }

        .qris {
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
    <div class="qris">
        <h2>GYM FIT JEMBER</h2>
        <p><strong>QRIS DANA</strong></p>
        <p>Nomor: <strong>085719630447</strong></p>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=085719630447" alt="QRIS">
        <p><em>Scan → Transfer → Beri tahu kasir</em></p>
    </div>
    <br><button onclick="window.print()">CETAK QRIS</button>
</body>

</html>