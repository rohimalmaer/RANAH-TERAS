<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_number = (int)($_POST['table_number'] ?? 0);
    $quantities_input = $_POST['quantities'] ?? [];

    if ($table_number < 1 || empty($quantities_input)) {
        header("Location: menu.php?error=invalid");
        exit;
    }

    $menu_ids = [];
    $quantities = [];

    foreach ($quantities_input as $menu_id => $qty) {
        $menu_id = (int)$menu_id;
        $qty = (int)$qty;
        if ($qty > 0) {
            $menu_ids[] = $menu_id;
            $quantities[] = $qty;
        }
    }

    if (empty($menu_ids)) {
        header("Location: menu.php?error=empty_order");
        exit;
    }

    $menu_ids_str = implode(",", $menu_ids);
    $quantities_str = implode(",", $quantities);

    // Cek apakah meja sudah digunakan dalam 1 jam terakhir
    $current_time = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("
        SELECT id FROM orders 
        WHERE table_number = ? 
        AND created_at >= DATE_SUB(?, INTERVAL 1 HOUR)
    ");
    $stmt->bind_param("is", $table_number, $current_time);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "<script>alert('Nomor meja ini telah digunakan dalam 1 jam terakhir. Silakan pilih meja lain.'); window.history.back();</script>";
        exit;
    }

    // Buat order code
    $today = date('Y-m-d');
    $result = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE DATE(created_at) = '$today'");
    $row = $result->fetch_assoc();
    $count_today = (int)$row['total'] + 1;
    $order_code = 'ORD-' . str_pad($count_today, 4, '0', STR_PAD_LEFT);

    // Simpan order ke database
    $stmt = $conn->prepare("INSERT INTO orders (order_code, menu_ids, quantities, table_number, status, created_at) VALUES (?, ?, ?, ?, 'unpaid', NOW())");
    $stmt->bind_param("sssi", $order_code, $menu_ids_str, $quantities_str, $table_number);
    if (!$stmt->execute()) {
        die("Insert error: " . $stmt->error);
    }

    $order_id = $stmt->insert_id;
    header("Location: payment.php?order_id=$order_id");
    exit;
}
?>

<!-- Jika tidak POST -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pesanan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <h2>Pesanan Tidak Valid</h2>
    <p>Silakan kembali ke <a href="menu.php">halaman menu</a>, pilih item dan isi nomor meja sebelum melanjutkan.</p>
</div>
</body>
</html>
