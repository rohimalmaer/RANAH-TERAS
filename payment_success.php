<?php
session_start();
include 'db.php';

$order_id = $_GET['order_id'] ?? 0;
$order_id = (int)$order_id;
if ($order_id <= 0) {
    die("Order ID tidak valid.");
}

$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
if (!$order) {
    die("Order tidak ditemukan.");
}

$menu_ids = explode(",", $order['menu_ids']);
$quantities = explode(",", $order['quantities']);

$menu_id_placeholders = implode(",", array_map('intval', $menu_ids));
$menu_query = $conn->query("SELECT * FROM menu WHERE id IN ($menu_id_placeholders)");

$menus = [];
$total = 0;
while ($row = $menu_query->fetch_assoc()) {
    $menu_id = $row['id'];
    $index = array_search($menu_id, $menu_ids);
    $qty = isset($quantities[$index]) ? (int)$quantities[$index] : 1;
    $row['qty'] = $qty;
    $row['subtotal'] = $row['price'] * $qty;
    $menus[] = $row;
    $total += $row['subtotal'];
}

$tanggal_order = date('d F Y, H:i', strtotime($order['created_at'])) . ' WIB';

$notif = $_SESSION['notif'] ?? null;
unset($_SESSION['notif']);

$status = strtolower($order['status']);
$is_success = in_array($status, ['lunas', 'sukses']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pembayaran</title>

    <?php if (!$is_success): ?>
        <!-- Auto-refresh jika belum sukses/lunas -->
        <meta http-equiv="refresh" content="15">
    <?php endif; ?>

    <style>
        body {
            background-color: #f7f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            background-color: white;
            margin: 50px auto;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
        }

        p {
            font-size: 1.1rem;
            margin: 8px 0;
        }

        p strong {
            color: #34495e;
        }

        h3 {
            margin-top: 30px;
            color: #34495e;
            border-bottom: 2px solid #27ae60;
            padding-bottom: 8px;
        }

        ul {
            list-style-type: disc;
            padding-left: 20px;
            margin-top: 15px;
        }

        ul li {
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .status-success {
            color: green;
            font-weight: bold;
            font-size: 18px;
            text-align: center;
            margin-top: 30px;
        }

        .status-waiting {
            color: orange;
            font-weight: bold;
            font-size: 18px;
            text-align: center;
            margin-top: 30px;
        }

        .notif-box {
            font-weight: 600;
            text-align: center;
            box-sizing: border-box;
            padding: 10px;
            background-color: #d4edda !important;
            color: #155724 !important;
            border: 1px solid #c3e6cb !important;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .refresh-btn {
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        .refresh-btn button {
            padding: 10px 20px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        @media screen and (max-width: 480px) {
            .container {
                margin: 30px 15px;
                padding: 20px;
            }

            p, ul li {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
<style>
    body {
        background-image: url('img/dalam.jpg'); /* Ganti path sesuai lokasi gambar */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
    }
</style>
<div class="container">

    <?php if ($notif): ?>
        <div class="notif-box"><?= htmlspecialchars($notif) ?></div>
    <?php endif; ?>

    <h2>Bukti Pembayaran</h2>

    <p><strong>No Order:</strong> <?= htmlspecialchars($order['order_code']) ?></p>
    <p><strong>Waktu Order:</strong> <?= $tanggal_order ?></p>
    <p><strong>Nomor Meja:</strong> <?= htmlspecialchars($order['table_number']) ?></p>
    <p><strong>Metode Pembayaran:</strong> <?= htmlspecialchars(ucfirst($order['payment_method'] ?? '')) ?></p>
    <p><strong>Status Pembayaran:</strong> <?= ucfirst($status) ?></p>

    <h3>Detail Pesanan:</h3>
    <ul>
        <?php foreach ($menus as $menu): ?>
            <li><?= htmlspecialchars($menu['name']) ?> (x<?= $menu['qty'] ?>) - Rp<?= number_format($menu['subtotal'], 0, ',', '.') ?></li>
        <?php endforeach; ?>
    </ul>

    <p><strong>Total Dibayar:</strong> Rp<?= number_format($total, 0, ',', '.') ?></p>

    <?php if ($is_success): ?>
        <p class="status-success">✅ Pembayaran Berhasil!</p>
        <p style="text-align: center; margin-top: 20px;">Terima kasih sudah melakukan pembayaran.</p>
    <?php else: ?>
        <p class="status-waiting">⚠️ Menunggu konfirmasi pembayaran oleh kasir.</p>
        <p style="text-align: center; margin-top: 20px;">Silakan tunggu, pesanan Anda sedang diproses.</p>

        <form method="get" class="refresh-btn">
            <input type="hidden" name="order_id" value="<?= $order_id ?>">
            <button type="submit">🔄 Perbarui Status</button>
        </form>
    <?php endif; ?>

</div>
</body>
</html>
