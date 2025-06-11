<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Scan QR Code</title>
    <link rel="stylesheet" href="assets/css/style.css"> <!-- opsional jika ada file CSS tambahan -->
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        #qrcode {
            margin: 20px auto;
            width: 250px;
            height: 250px;
        }

        p {
            color: #555;
            margin-bottom: 25px;
        }

        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #45a049;
        }

        @media (max-width: 500px) {
            .container {
                width: 90%;
                padding: 20px;
            }

            #qrcode {
                width: 200px;
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Scan QR untuk Pesan</h1>

        <!-- QR Code -->
        <div id="qrcode"></div>

        <p>Arahkan kamera HP ke QR code ini untuk mulai memesan.</p>

        <!-- Tombol simulasi -->
        <a href="menu.php" class="btn">Buka Menu Langsung</a>
    </div>

    <!-- QRCode.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Ganti URL sesuai lokasi file menu.php
        const menuURL = window.location.origin + "/menu.php";
        new QRCode(document.getElementById("qrcode"), {
            text: menuURL,
            width: 250,
            height: 250,
        });
    </script>
</body>
</html>
