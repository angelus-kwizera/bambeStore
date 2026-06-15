<?php
require_once __DIR__ . '/includes/init.php';
requireAdmin();

$pageTitle = 'Orders';
$db = getDBConnection();
$orders = getAllOrdersAdmin($db);

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-toolbar">
    <p class="admin-toolbar__info"><?= count($orders) ?> total orders</p>
</div>

<div class="admin-panel">
    <?php if (empty($orders)): ?>
    <p class="admin-panel__empty">No orders yet. They will appear here when customers checkout.</p>
    <?php else: ?>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><strong><?= sanitize($order['order_number']) ?></strong></td>
                    <td>
                        <?= sanitize($order['full_name']) ?>
                        <br><small class="text-muted"><?= sanitize($order['email']) ?></small>
                    </td>
                    <td><?= $order['item_count'] ?></td>
                    <td><?= formatPrice((float) $order['total']) ?></td>
                    <td><span class="badge <?= orderStatusBadgeClass($order['status']) ?>"><?= ucfirst($order['status']) ?></span></td>
                    <td><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></td>
                    <td>
                        <a href="order-view.php?id=<?= $order['id'] ?>" class="btn btn--sm btn--outline">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
