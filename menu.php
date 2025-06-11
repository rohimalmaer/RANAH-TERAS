<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Menu</title>
    <style>
        /* Umum */
        body {
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        /* Container utama */
        .container {
            max-width: 960px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Judul utama */
        h2 {
            text-align: center;
            margin-bottom: 40px;
            color: #2c3e50;
            font-size: 2rem;
        }

        /* Judul kategori */
        h3 {
            font-size: 1.4rem;
            color: #34495e;
            border-left: 6px solid #3498db;
            padding-left: 12px;
            margin-top: 50px;
            margin-bottom: 20px;
        }

        /* Form input meja */
        form label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        form input[type="number"]#table_number {
            width: 100%;
            max-width: 200px;
            padding: 10px 14px;
            font-size: 1rem;
            border: 2px solid #ccc;
            border-radius: 6px;
            margin-bottom: 30px;
            transition: border-color 0.3s ease;
        }

        form input[type="number"]#table_number:focus {
            border-color: #3498db;
            outline: none;
        }

        /* Grid menu */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 25px;
        }

        /* Kartu menu */
        .menu-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            padding: 20px 15px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }

        .menu-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }

        /* Nama menu */
        .menu-card h4 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #2c3e50;
            min-height: 40px;
        }

        /* Harga */
        .menu-card p {
            font-weight: 700;
            color: #27ae60;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        /* Kontrol kuantitas */
        .quantity-control {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        /* Tombol - dan + */
        .quantity-control button {
            background-color: #3498db;
            border: none;
            color: white;
            font-size: 1.2rem;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .quantity-control button:hover {
            background-color: #2980b9;
        }

        /* Input jumlah */
        .quantity-control input[type="number"] {
            width: 50px;
            text-align: center;
            font-size: 1rem;
            padding: 6px 8px;
            border: 2px solid #ddd;
            border-radius: 6px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .quantity-control input[type="number"]:focus {
            border-color: #3498db;
        }

        /* Tombol submit */
        form button[type="submit"] {
            margin-top: 40px;
            background-color: #27ae60;
            border: none;
            padding: 14px 30px;
            font-size: 1.2rem;
            font-weight: bold;
            color: white;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            max-width: 400px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            transition: background-color 0.3s ease;
        }

        form button[type="submit"]:hover {
            background-color: #219150;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }

            .quantity-control button {
                padding: 6px 10px;
                font-size: 1rem;
            }

            .quantity-control input[type="number"] {
                width: 40px;
                font-size: 0.9rem;
            }

            form input#table_number {
                max-width: 100%;
            }

            h2 {
                font-size: 1.6rem;
            }

            h3 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Daftar Menu | RANAH TERAS JAWAI</h2>
    <form id="orderForm" action="order.php" method="POST">
        <label for="table_number">Nomor Meja:</label>
        <input type="number" name="table_number" id="table_number" required min="1" placeholder="Masukkan nomor meja">

        <?php
        function renderMenuByCategory($conn, $category, $title) {
            $result = $conn->query("SELECT * FROM menu WHERE category = '$category' ORDER BY name ASC");

            if ($result->num_rows > 0) {
                echo "<h3>$title</h3>";
                echo "<div class='menu-grid'>";
                while ($row = $result->fetch_assoc()) {
                    $menuId = $row['id'];
                    echo "
                    <div class='menu-card'>
                        <h4>" . htmlspecialchars($row['name']) . "</h4>
                        <p>Rp" . number_format($row['price'], 0, ',', '.') . "</p>
                        <div class='quantity-control'>
                            <button type='button' onclick='decrement($menuId)'>-</button>
                            <input type='number' name='quantities[$menuId]' id='qty-$menuId' value='0' min='0'>
                            <button type='button' onclick='increment($menuId)'>+</button>
                        </div>
                    </div>
                    ";
                }
                echo "</div>";
            }
        }

        renderMenuByCategory($conn, 'makanan', '🍽 Makanan');
        renderMenuByCategory($conn, 'cemilan', '🍟 Cemilan Serba 10K');
        renderMenuByCategory($conn, 'minuman', '🥤 Minuman');
        ?>

        <button type="submit">Pesan Sekarang</button>
    </form>
</div>
<style>
    body {
        background-image: url('img/cafe.jpeg'); /* Ganti path sesuai lokasi gambar */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
    }
</style>

<script>
    function increment(id) {
        let qty = document.getElementById('qty-' + id);
        qty.value = parseInt(qty.value) + 1;
    }

    function decrement(id) {
        let qty = document.getElementById('qty-' + id);
        if (parseInt(qty.value) > 0) {
            qty.value = parseInt(qty.value) - 1;
        }
    }
</script>
</body>
</html>
