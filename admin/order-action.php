<?php
require_once __DIR__ . '/includes/init.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orders.php');
    exit;
}

$orderId = (int) ($_POST['order_id'] ?? 0);
$status = $_POST['status'] ?? '';

if ($orderId && updateOrderStatus(getDBConnection(), $orderId, $status)) {
    setAdminFlash('success', 'Order status updated to ' . ucfirst($status) . '.');
} else {
    setAdminFlash('error', 'Failed to update order status.');
}

header('Location: order-view.php?id=' . $orderId);
exit;
