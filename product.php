<?php
require_once __DIR__ . '/includes/init.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header('Location: products.php');
    exit;
}

$db = getDBConnection();
$product = getProductBySlug($db, $slug);

if (!$product) {
    setFlash('error', 'Product not found.');
    header('Location: products.php');
    exit;
}

$pageTitle = $product['name'];
$relatedProducts = getProducts($db, $product['category_slug']);
$relatedProducts = array_filter($relatedProducts, fn($p) => $p['id'] !== $product['id']);
$relatedProducts = array_slice($relatedProducts, 0, 4);

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header page-header--sm">
    <div class="container">
        <p class="page-header__breadcrumb">
            <a href="index.php">Home</a> /
            <a href="products.php?category=<?= sanitize($product['category_slug']) ?>"><?= sanitize($product['category_name']) ?></a> /
            <?= sanitize($product['name']) ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="product-detail">
            <div class="product-detail__gallery">
                <img src="<?= sanitize($product['image_url']) ?>" alt="<?= sanitize($product['name']) ?>" class="product-detail__image">
            </div>
            <div class="product-detail__info">
                <span class="product-detail__category"><?= sanitize($product['category_name']) ?></span>
                <h1 class="product-detail__title"><?= sanitize($product['name']) ?></h1>
                <p class="product-detail__price"><?= formatPrice((float) $product['price']) ?></p>

                <p class="product-detail__description"><?= sanitize($product['description']) ?></p>

                <div class="product-detail__meta">
                    <span class="badge <?= $product['stock'] > 0 ? 'badge--success' : 'badge--danger' ?>">
                        <?= $product['stock'] > 0 ? 'In Stock (' . $product['stock'] . ' available)' : 'Out of Stock' ?>
                    </span>
                </div>

                <?php if ($product['stock'] > 0): ?>
                <form action="cart-action.php" method="POST" class="product-detail__form">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="redirect" value="cart.php">

                    <div class="quantity-selector">
                        <label for="quantity">Quantity</label>
                        <div class="quantity-selector__controls">
                            <button type="button" class="qty-btn" data-action="decrease">−</button>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="qty-input">
                            <button type="button" class="qty-btn" data-action="increase">+</button>
                        </div>
                    </div>

                    <div class="product-detail__actions">
                        <button type="submit" class="btn btn--primary btn--lg">Add to Cart</button>
                        <a href="products.php?category=<?= sanitize($product['category_slug']) ?>" class="btn btn--outline btn--lg">Continue Shopping</a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($relatedProducts)): ?>
        <div class="related-products">
            <h2 class="section__title">You May Also Like</h2>
            <div class="product-grid">
                <?php foreach ($relatedProducts as $related): ?>
                    <?= renderProductCard($related) ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
