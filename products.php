<?php
require_once __DIR__ . '/includes/init.php';

$db = getDBConnection();
$categorySlug = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;
$categories = getCategories($db);
$products = getProducts($db, $categorySlug, $search);

if ($categorySlug) {
    $catName = '';
    foreach ($categories as $cat) {
        if ($cat['slug'] === $categorySlug) {
            $catName = $cat['name'];
            break;
        }
    }
    $pageTitle = $catName ?: 'Products';
} elseif ($search) {
    $pageTitle = 'Search: ' . $search;
} else {
    $pageTitle = 'All Products';
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1 class="page-header__title"><?= sanitize($pageTitle) ?></h1>
        <p class="page-header__breadcrumb">
            <a href="index.php">Home</a> / <?= sanitize($pageTitle) ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container shop-layout">
        <aside class="sidebar">
            <h3 class="sidebar__title">Categories</h3>
            <ul class="sidebar__list">
                <li>
                    <a href="products.php" class="sidebar__link <?= !$categorySlug ? 'sidebar__link--active' : '' ?>">
                        All Products
                    </a>
                </li>
                <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="products.php?category=<?= sanitize($cat['slug']) ?>"
                       class="sidebar__link <?= $categorySlug === $cat['slug'] ? 'sidebar__link--active' : '' ?>">
                        <?= sanitize($cat['name']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <div class="shop-content">
            <div class="shop-toolbar">
                <p class="shop-toolbar__count"><?= count($products) ?> product<?= count($products) !== 1 ? 's' : '' ?> found</p>
            </div>

            <?php if (empty($products)): ?>
            <div class="empty-state">
                <div class="empty-state__icon">🔍</div>
                <h2>No products found</h2>
                <p>Try a different category or search term.</p>
                <a href="products.php" class="btn btn--primary">View All Products</a>
            </div>
            <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <?= renderProductCard($product) ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
