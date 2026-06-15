<?php
require_once __DIR__ . '/includes/init.php';

$orderNumber = $_GET['order'] ?? '';
if (!$orderNumber) {
    header('Location: index.php');
    exit;
}

$db = getDBConnection();
$order = getOrderByNumber($db, $orderNumber);

if (!$order) {
    setFlash('error', 'Order not found.');
    header('Location: index.php');
    exit;
}

$pageTitle = 'Order Confirmed';

require_once __DIR__ . '/includes/header.php';
?>

<section class="section confirmation">
    <div class="container">
        <div class="confirmation__card">
            <div class="confirmation__icon">✓</div>
            <h1 class="confirmation__title">Thank You for Your Order!</h1>
            <p class="confirmation__message">Your order has been placed successfully. We'll send a confirmation to <strong><?= sanitize($order['email']) ?></strong>.</p>

            <div class="confirmation__details">
                <div class="confirmation__detail">
                    <span class="confirmation__label">Order Number</span>
                    <span class="confirmation__value"><?= sanitize($order['order_number']) ?></span>
                </div>
                <div class="confirmation__detail">
                    <span class="confirmation__label">Date</span>
                    <span class="confirmation__value"><?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="confirmation__detail">
                    <span class="confirmation__label">Status</span>
                    <span class="badge badge--success"><?= ucfirst(sanitize($order['status'])) ?></span>
                </div>
                <div class="confirmation__detail">
                    <span class="confirmation__label">Total</span>
                    <span class="confirmation__value confirmation__value--total"><?= formatPrice((float) $order['total']) ?></span>
                </div>
            </div>

            <div class="confirmation__section">
                <h2>Delivery Information</h2>
                <p><strong><?= sanitize($order['full_name']) ?></strong></p>
                <p><?= sanitize($order['address']) ?></p>
                <p><?= sanitize($order['city']) ?>, Rwanda</p>
                <p>📞 <?= sanitize($order['phone']) ?></p>
            </div>

            <div class="confirmation__section">
                <h2>Order Items</h2>
                <table class="confirmation__table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td><?= sanitize($item['product_name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= formatPrice((float) $item['price']) ?></td>
                            <td><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><strong>Total</strong></td>
                            <td><strong><?= formatPrice((float) $order['total']) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="confirmation__actions">
                <a href="products.php" class="btn btn--primary btn--lg">Continue Shopping</a>
                <a href="index.php" class="btn btn--outline btn--lg">Back to Home</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
