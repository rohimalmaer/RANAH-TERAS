<?php
include 'db.php';
$order_id = $_GET['order_id'] ?? 0;

// Ambil data order
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();

$menu_ids = explode(",", $order['menu_ids']);
$quantities = explode(",", $order['quantities']);

// Ambil data menu terkait
$menu_query = $conn->query("SELECT * FROM menu WHERE id IN (" . implode(',', array_map('intval', $menu_ids)) . ")");
$menus = [];
$total = 0;

// Gabungkan menu dengan jumlah
while ($row = $menu_query->fetch_assoc()) {
    $id = $row['id'];
    $index = array_search($id, $menu_ids); // cari posisi item
    $qty = intval($quantities[$index] ?? 1); // fallback ke 1
    $subtotal = $row['price'] * $qty;
    $menus[] = [
        'name' => $row['name'],
        'price' => $row['price'],
        'qty' => $qty,
        'subtotal' => $subtotal
    ];
    $total += $subtotal;
}

// Format tanggal
$tanggal_order = date('d F Y, H:i', strtotime($order['created_at'])) . ' WIB';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran</title>
    <link rel="stylesheet" href="pay.css">
</head>
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
<body>
<div class="container">
    <h2>Detail Pembayaran</h2>

    <p><strong>No Order:</strong> <?= $order['order_code'] ?></p>
    <p><strong>Waktu Order:</strong> <?= $tanggal_order ?></p>
    <p><strong>Nomor Meja:</strong> <?= $order['table_number'] ?></p>

    <h3>Pesanan:</h3>
    <ul>
        <?php foreach ($menus as $item): ?>
            <li><?= $item['name'] ?> × <?= $item['qty'] ?> - Rp<?= number_format($item['subtotal'], 0, ',', '.') ?></li>
        <?php endforeach; ?>
    </ul>
    <p><strong>Total:</strong> Rp<?= number_format($total, 0, ',', '.') ?></p>

    <form action="notify.php" method="POST">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">
        <input type="hidden" name="total" value="<?= $total ?>">

        <label for="method">Pilih Metode Pembayaran:</label>
        <select name="method" id="method" onchange="toggleCashInfo()">
            <option value="cash">Cash</option>
            <option value="transfer">Transfer</option>
        </select>

        <div id="cash-info" style="margin-top:10px;">
            <p><strong>Tunjukkan total ini ke kasir:</strong></p>
            <div style="font-size: 24px; font-weight: bold; color: green;">
                Rp<?= number_format($total, 0, ',', '.') ?>
            </div>
        </div>

        <button type="submit">Konfirmasi Pembayaran</button>
    </form>
</div>

<script>
function toggleCashInfo() {
    const method = document.getElementById('method').value;
    const cashInfo = document.getElementById('cash-info');
    cashInfo.style.display = method === 'cash' ? 'block' : 'none';
}
window.onload = toggleCashInfo;
</script>
</body>
</html>
