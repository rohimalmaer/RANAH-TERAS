<?php
include 'db.php';

// Update status jika ada parameter aksi
if (isset($_GET['serve'])) {
    $id = (int)$_GET['serve'];
    $conn->query("UPDATE orders SET is_served = 1 WHERE id = $id");
}
if (isset($_GET['receive'])) {
    $id = (int)$_GET['receive'];
    $conn->query("UPDATE orders SET is_received = 1 WHERE id = $id");
}
if (isset($_GET['confirm_payment'])) {
    $id = (int)$_GET['confirm_payment'];
    $order_check = $conn->query("SELECT payment_method, status FROM orders WHERE id = $id")->fetch_assoc();
    if ($order_check && $order_check['payment_method'] === 'cash' && $order_check['status'] !== 'lunas') {
        $conn->query("UPDATE orders SET status = 'lunas' WHERE id = $id");
    }
}

// Ambil semua order terbaru
$result = $conn->query("SELECT * FROM orders ORDER BY id DESC");

// Fungsi ambil nama menu berdasarkan ID
function getMenuNames($conn, $menu_ids) {
    $ids = explode(',', $menu_ids);
    $ids = array_map('intval', $ids);
    $id_list = implode(',', $ids);

    if (!$id_list) return '';

    $query = $conn->query("SELECT id, name FROM menu WHERE id IN ($id_list)");
    $names = [];

    while ($row = $query->fetch_assoc()) {
        $names[$row['id']] = $row['name'];
    }

    $menu_names = [];
    foreach ($ids as $id) {
        $menu_names[] = $names[$id] ?? "Menu ID $id";
    }

    return implode(', ', $menu_names);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Pesanan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            margin: 0; padding: 0;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            background: #fff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #27ae60;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #27ae60;
            color: #fff;
        }
        a.aksi {
            display: inline-block;
            margin: 4px 0;
            padding: 6px 12px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
        }
        a.konfirmasi { background-color: #2ecc71; }
        a.serve { background-color: #3498db; }
        a.receive { background-color: #e67e22; }
        a.disabled {
            background-color: #bdc3c7;
            pointer-events: none;
            cursor: default;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Manajemen Pesanan</h2>
    <table>
        <thead>
            <tr>
                <th>No Order</th>
                <th>Waktu Order</th>
                <th>No Meja</th>
                <th>Menu</th>
                <th>Status Pembayaran</th>
                <th>Metode Bayar</th>
                <th>Sudah Dilayani</th>
                <th>Sudah Diterima</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()):
                $waktu = date('d F Y, H:i', strtotime($row['created_at'])) . ' WIB';
                $payment_method = $row['payment_method'] ?? '-';
                $is_served = $row['is_served'] ?? 0;
                $is_received = $row['is_received'] ?? 0;
                $status = $row['status'];
            ?>
            <tr>
                <td><?= htmlspecialchars($row['order_code']) ?></td>
                <td><?= $waktu ?></td>
                <td><?= htmlspecialchars($row['table_number']) ?></td>
                <td><?= htmlspecialchars(getMenuNames($conn, $row['menu_ids'])) ?></td>
                <td><strong><?= htmlspecialchars(ucfirst($status)) ?></strong></td>
                <td><?= ucfirst(htmlspecialchars($payment_method)) ?></td>
                <td><?= $is_served ? '✔️' : '❌' ?></td>
                <td><?= $is_received ? '✔️' : '❌' ?></td>
                <td>
                    <?php if ($payment_method === 'cash' && $status !== 'lunas'): ?>
                        <a class="aksi konfirmasi" href="?confirm_payment=<?= $row['id'] ?>" onclick="return confirm('Konfirmasi pembayaran lunas untuk order ini?')">✔ Konfirmasi Bayar</a>
                    <?php else: ?>
                        <a class="aksi konfirmasi disabled">✔ Konfirmasi Bayar</a>
                    <?php endif; ?>

                    <?php if (!$is_served): ?>
                        <a class="aksi serve" href="?serve=<?= $row['id'] ?>" onclick="return confirm('Tandai pesanan sudah dilayani?')">🍽 Tandai Dilayani</a>
                    <?php else: ?>
                        <a class="aksi serve disabled">🍽 Sudah Dilayani</a>
                    <?php endif; ?>

                    <?php if ($is_served && !$is_received): ?>
                        <a class="aksi receive" href="?receive=<?= $row['id'] ?>" onclick="return confirm('Tandai pesanan sudah diterima pelanggan?')">✅ Tandai Diterima</a>
                    <?php elseif ($is_received): ?>
                        <a class="aksi receive disabled">✅ Sudah Diterima</a>
                    <?php else: ?>
                        <a class="aksi receive disabled">✅ Tandai Diterima</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
