<?php
require_once __DIR__ . '/includes/init.php';
requireAdmin();

$pageTitle = 'Dashboard';
$db = getDBConnection();
$stats = getDashboardStats($db);

$maxDailyRevenue = 1;
foreach ($stats['sales_last_7_days'] as $day) {
    $maxDailyRevenue = max($maxDailyRevenue, (float) $day['daily_revenue']);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card stat-card--revenue">
        <div class="stat-card__icon">💰</div>
        <div class="stat-card__content">
            <span class="stat-card__label">Total Revenue</span>
            <span class="stat-card__value"><?= formatPrice($stats['total_revenue']) ?></span>
        </div>
    </div>
    <div class="stat-card stat-card--orders">
        <div class="stat-card__icon">📦</div>
        <div class="stat-card__content">
            <span class="stat-card__label">Total Orders</span>
            <span class="stat-card__value"><?= number_format($stats['total_orders']) ?></span>
        </div>
    </div>
    <div class="stat-card stat-card--products">
        <div class="stat-card__icon">👕</div>
        <div class="stat-card__content">
            <span class="stat-card__label">Products</span>
            <span class="stat-card__value"><?= number_format($stats['total_products']) ?></span>
        </div>
    </div>
    <div class="stat-card stat-card--customers">
        <div class="stat-card__icon">👥</div>
        <div class="stat-card__content">
            <span class="stat-card__label">Customers</span>
            <span class="stat-card__value"><?= number_format($stats['total_customers']) ?></span>
        </div>
    </div>
</div>

<div class="stats-grid stats-grid--secondary">
    <div class="stat-card stat-card--sm">
        <span class="stat-card__label">Orders Today</span>
        <span class="stat-card__value"><?= $stats['orders_today'] ?></span>
    </div>
    <div class="stat-card stat-card--sm">
        <span class="stat-card__label">Revenue This Month</span>
        <span class="stat-card__value"><?= formatPrice($stats['revenue_month']) ?></span>
    </div>
    <div class="stat-card stat-card--sm <?= $stats['low_stock'] > 0 ? 'stat-card--alert' : '' ?>">
        <span class="stat-card__label">Low Stock Items</span>
        <span class="stat-card__value"><?= $stats['low_stock'] ?></span>
    </div>
</div>

<div class="admin-grid-2">
    <div class="admin-panel">
        <h2 class="admin-panel__title">Sales — Last 7 Days</h2>
        <?php if (empty($stats['sales_last_7_days'])): ?>
        <p class="admin-panel__empty">No sales data yet. Orders will appear here.</p>
        <?php else: ?>
        <div class="chart-bars">
            <?php foreach ($stats['sales_last_7_days'] as $day): ?>
            <?php $height = ((float) $day['daily_revenue'] / $maxDailyRevenue) * 100; ?>
            <div class="chart-bars__item">
                <div class="chart-bars__bar-wrap">
                    <div class="chart-bars__bar" style="height: <?= max($height, 5) ?>%"></div>
                </div>
                <span class="chart-bars__label"><?= date('M j', strtotime($day['order_date'])) ?></span>
                <span class="chart-bars__value"><?= formatPrice((float) $day['daily_revenue']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="admin-panel">
        <h2 class="admin-panel__title">Orders by Status</h2>
        <?php if (empty($stats['orders_by_status'])): ?>
        <p class="admin-panel__empty">No orders yet.</p>
        <?php else: ?>
        <div class="status-list">
            <?php foreach ($stats['orders_by_status'] as $row): ?>
            <div class="status-list__item">
                <span class="badge <?= orderStatusBadgeClass($row['status']) ?>"><?= ucfirst($row['status']) ?></span>
                <span class="status-list__count"><?= $row['count'] ?> orders</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="admin-grid-2">
    <div class="admin-panel">
        <div class="admin-panel__header">
            <h2 class="admin-panel__title">Top Selling Products</h2>
        </div>
        <?php if (empty($stats['top_products'])): ?>
        <p class="admin-panel__empty">No sales data yet.</p>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Sold</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['top_products'] as $product): ?>
                <tr>
                    <td><?= sanitize($product['product_name']) ?></td>
                    <td><?= $product['total_sold'] ?></td>
                    <td><?= formatPrice((float) $product['revenue']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div class="admin-panel">
        <div class="admin-panel__header">
            <h2 class="admin-panel__title">Recent Orders</h2>
            <a href="orders.php" class="admin-panel__link">View All</a>
        </div>
        <?php if (empty($stats['recent_orders'])): ?>
        <p class="admin-panel__empty">No orders yet.</p>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['recent_orders'] as $order): ?>
                <tr>
                    <td><a href="order-view.php?id=<?= $order['id'] ?>" class="admin-link"><?= sanitize($order['order_number']) ?></a></td>
                    <td><?= sanitize($order['full_name']) ?></td>
                    <td><?= formatPrice((float) $order['total']) ?></td>
                    <td><span class="badge <?= orderStatusBadgeClass($order['status']) ?>"><?= ucfirst($order['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
