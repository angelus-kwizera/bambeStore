<?php
require_once __DIR__ . '/includes/init.php';
requireAdmin();

$db = getDBConnection();
$order = null;

if (isset($_GET['lookup']) && $_GET['lookup'] === 'number') {
    $orderNumber = $_GET['id'] ?? '';
    $order = getOrderByNumber($db, $orderNumber);
} else {
    $orderId = (int) ($_GET['id'] ?? 0);
    $order = getOrderByIdAdmin($db, $orderId);
}

if (!$order) {
    setAdminFlash('error', 'Order not found.');
    header('Location: orders.php');
    exit;
}

$pageTitle = 'Order ' . $order['order_number'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-toolbar">
    <a href="orders.php" class="btn btn--outline">&larr; Back to Orders</a>
</div>

<div class="admin-grid-2">
    <div class="admin-panel">
        <h2 class="admin-panel__title">Order Details</h2>
        <dl class="detail-list">
            <div class="detail-list__row">
                <dt>Order Number</dt>
                <dd><strong><?= sanitize($order['order_number']) ?></strong></dd>
            </div>
            <div class="detail-list__row">
                <dt>Date</dt>
                <dd><?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Total</dt>
                <dd class="text-accent"><strong><?= formatPrice((float) $order['total']) ?></strong></dd>
            </div>
            <div class="detail-list__row">
                <dt>Status</dt>
                <dd><span class="badge <?= orderStatusBadgeClass($order['status']) ?>"><?= ucfirst($order['status']) ?></span></dd>
            </div>
            <?php if (!empty($order['notes'])): ?>
            <div class="detail-list__row">
                <dt>Notes</dt>
                <dd><?= sanitize($order['notes']) ?></dd>
            </div>
            <?php endif; ?>
        </dl>

        <form action="order-action.php" method="POST" class="status-form">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <div class="form-group">
                <label for="status">Update Status</label>
                <div class="status-form__row">
                    <select id="status" name="status">
                        <?php foreach (['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn--primary">Update</button>
                </div>
            </div>
        </form>
    </div>

    <div class="admin-panel">
        <h2 class="admin-panel__title">Customer Information</h2>
        <dl class="detail-list">
            <div class="detail-list__row">
                <dt>Name</dt>
                <dd><?= sanitize($order['full_name']) ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Email</dt>
                <dd><?= sanitize($order['email']) ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Phone</dt>
                <dd><?= sanitize($order['phone']) ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Address</dt>
                <dd><?= sanitize($order['address']) ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>City</dt>
                <dd><?= sanitize($order['city']) ?></dd>
            </div>
        </dl>
    </div>
</div>

<div class="admin-panel">
    <h2 class="admin-panel__title">Order Items</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['items'] as $item): ?>
            <tr>
                <td><?= sanitize($item['product_name']) ?></td>
                <td><?= formatPrice((float) $item['price']) ?></td>
                <td><?= $item['quantity'] ?></td>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
