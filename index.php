<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Home';
$db = getDBConnection();
$featuredProducts = getFeaturedProducts($db, 8);
$categories = getCategories($db);

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero__inner">
        <div class="hero__content">
            <span class="hero__badge">New Collection 2026</span>
            <h1 class="hero__title">Step Into Style with <span>Bambe</span></h1>
            <p class="hero__text">Discover the latest trends in clothes and shoes. Quality fashion for every occasion, delivered across Rwanda.</p>
            <div class="hero__actions">
                <a href="products.php" class="btn btn--primary btn--lg">Shop Now</a>
                <a href="products.php?category=shoes" class="btn btn--outline btn--lg">Explore Shoes</a>
            </div>
        </div>
        <div class="hero__image">
            <img src="assets/dus.jpeg" alt="Bambe fashion collection">
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title">Shop by Category</h2>
            <p class="section__subtitle">Find exactly what you're looking for</p>
        </div>
        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="products.php?category=<?= sanitize($cat['slug']) ?>" class="category-card category-card--<?= sanitize($cat['slug']) ?>">
                <div class="category-card__content">
                    <h3><?= sanitize($cat['name']) ?></h3>
                    <p><?= sanitize($cat['description']) ?></p>
                    <span class="category-card__link">Browse &rarr;</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title">Featured Products</h2>
            <a href="products.php" class="section__link">View All &rarr;</a>
        </div>
        <div class="product-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <?= renderProductCard($product) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section features">
    <div class="container">
        <div class="features__grid">
            <div class="feature">
                <div class="feature__icon">🚚</div>
                <h3>Fast Delivery</h3>
                <p>Quick delivery across Kigali and nationwide shipping</p>
            </div>
            <div class="feature">
                <div class="feature__icon"></div>
                <h3>Quality Products</h3>
                <p>Carefully selected clothes and shoes from trusted brands</p>
            </div>
            <div class="feature">
                <div class="feature__icon">💳</div>
                <h3>Secure Checkout</h3>
                <p>Safe and easy ordering with order confirmation</p>
            </div>
            <div class="feature">
                <div class="feature__icon">🔄</div>
                <h3>Easy Returns</h3>
                <p>7-day return policy on unworn items with tags</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
