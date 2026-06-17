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
    echo json_encode(['error' => 'PayPal is not configured']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$paypalOrderId = trim($input['paypal_order_id'] ?? '');

$customerData = [
    'full_name' => trim($input['full_name'] ?? ''),
    'email' => trim($input['email'] ?? ''),
    'phone' => trim($input['phone'] ?? ''),
    'address' => trim($input['address'] ?? ''),
    'city' => trim($input['city'] ?? 'Kigali'),
    'notes' => trim($input['notes'] ?? ''),
];

$errors = [];
if (empty($customerData['full_name'])) {
    $errors[] = 'Full name is required';
}
if (empty($customerData['email']) || !filter_var($customerData['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}
if (empty($customerData['phone'])) {
    $errors[] = 'Phone is required';
}
if (empty($customerData['address'])) {
    $errors[] = 'Address is required';
}
if (empty($paypalOrderId)) {
    $errors[] = 'PayPal order ID is required';
}

$cart = getCartItems();
if (empty($cart['items'])) {
    $errors[] = 'Cart is empty';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => implode('. ', $errors)]);
    exit;
}

$capture = capturePayPalOrder($paypalOrderId);
if (!$capture || ($capture['status'] ?? '') !== 'COMPLETED') {
    http_response_code(402);
    echo json_encode(['error' => 'PayPal payment was not completed']);
    exit;
}

$captureId = $capture['purchase_units'][0]['payments']['captures'][0]['id'] ?? $paypalOrderId;
$deliveryFee = $cart['total'] >= 50000 ? 0 : 3000;
$grandTotal = $cart['total'] + $deliveryFee;

$db = getDBConnection();
$order = createOrder($db, $customerData, $cart['items'], $grandTotal, [
    'payment_method' => 'paypal',
    'payment_status' => 'paid',
    'paypal_order_id' => $paypalOrderId,
    'paypal_capture_id' => $captureId,
]);

if (!$order) {
    http_response_code(500);
    echo json_encode(['error' => 'Payment received but order creation failed. Contact support.']);
    exit;
}

echo json_encode([
    'success' => true,
    'order_number' => $order['order_number'],
    'redirect' => 'order-confirmation.php?order=' . urlencode($order['order_number']),
]);
