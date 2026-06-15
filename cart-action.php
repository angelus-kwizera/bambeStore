<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$action = $_POST['action'] ?? '';
$productId = (int) ($_POST['product_id'] ?? 0);
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));
$redirect = $_POST['redirect'] ?? 'cart.php';

if (!str_starts_with($redirect, '/') && !str_contains($redirect, '..')) {
    $redirect = ltrim($redirect, '/');
} else {
    $redirect = 'cart.php';
}

switch ($action) {
    case 'add':
        if (addToCart($productId, $quantity)) {
            setFlash('success', 'Item added to your cart!');
        } else {
            setFlash('error', 'Could not add item to cart.');
        }
        break;

    case 'update':
        if (updateCartQuantity($productId, $quantity)) {
            setFlash('success', 'Cart updated.');
        } else {
            setFlash('error', 'Could not update cart.');
        }
        break;

    case 'remove':
        removeFromCart($productId);
        setFlash('success', 'Item removed from cart.');
        break;

    case 'clear':
        clearCart();
        setFlash('success', 'Cart cleared.');
        break;
}

header('Location: ' . $redirect);
exit;
