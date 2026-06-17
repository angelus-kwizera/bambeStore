<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/paypal.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isPayPalConfigured()) {
    http_response_code(503);
    echo json_encode(['error' => 'PayPal is not configured. Set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET.']);
    exit;
}

$cart = getCartItems();
if (empty($cart['items'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Cart is empty']);
    exit;
}

$deliveryFee = $cart['total'] >= 50000 ? 0 : 3000;
$grandTotalRwf = $cart['total'] + $deliveryFee;
$amountUsd = rwfToUsd($grandTotalRwf);

if ($amountUsd < 0.01) {
    http_response_code(400);
    echo json_encode(['error' => 'Order amount too small for PayPal']);
    exit;
}

$order = createPayPalOrder($amountUsd, 'Bambe Fashion Store Order');

if (!$order || empty($order['id'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create PayPal order']);
    exit;
}

echo json_encode([
    'paypal_order_id' => $order['id'],
    'amount_usd' => $amountUsd,
    'amount_rwf' => $grandTotalRwf,
]);
