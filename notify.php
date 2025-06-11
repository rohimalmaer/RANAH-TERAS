<?php
include 'db.php';

$order_id = (int)$_POST['order_id'];
$method = $_POST['method'] ?? 'cash';
$total = (int)$_POST['total'];

// Update order: set metode pembayaran & status jadi "paid"
$conn->query("UPDATE orders SET payment_method = '$method', status = 'paid' WHERE id = $order_id");

// Redirect ke halaman bukti pembayaran (yang nanti redirect ke manage.php)
header("Location: payment_success.php?order_id=$order_id");
exit;
?>
