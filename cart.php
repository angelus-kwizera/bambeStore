<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Shopping Cart';
$cart = getCartItems();

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">Shopping Cart</h1>
        <p class="page-header__breadcrumb">
            <a href="index.php">Home</a> / Cart
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($cart['items'])): ?>
        <div class="empty-state">
            <div class="empty-state__icon">🛒</div>
            <h2>Your cart is empty</h2>
            <p>Looks like you haven't added anything yet. Start shopping!</p>
            <a href="products.php" class="btn btn--primary btn--lg">Browse Products</a>
        </div>
        <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items">
                <div class="cart-table-wrapper">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart['items'] as $item): ?>
                            <?php $p = $item['product']; ?>
                            <tr>
                                <td class="cart-table__product">
                                    <img src="<?= sanitize($p['image_url']) ?>" alt="<?= sanitize($p['name']) ?>" class="cart-table__image">
                                    <div>
                                        <a href="product.php?slug=<?= sanitize($p['slug']) ?>" class="cart-table__name"><?= sanitize($p['name']) ?></a>
                                    </div>
                                </td>
                                <td class="cart-table__price"><?= formatPrice((float) $p['price']) ?></td>
                                <td>
                                    <form action="cart-action.php" method="POST" class="cart-qty-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="redirect" value="cart.php">
                                        <div class="quantity-selector__controls quantity-selector__controls--sm">
                                            <button type="button" class="qty-btn" data-action="decrease">−</button>
                                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $p['stock'] ?>" class="qty-input qty-input--sm">
                                            <button type="button" class="qty-btn" data-action="increase">+</button>
                                        </div>
                                        <button type="submit" class="btn btn--text btn--sm">Update</button>
                                    </form>
                                </td>
                                <td class="cart-table__subtotal"><?= formatPrice($item['subtotal']) ?></td>
                                <td>
                                    <form action="cart-action.php" method="POST">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="redirect" value="cart.php">
                                        <button type="submit" class="btn btn--icon" aria-label="Remove item">&times;</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <form action="cart-action.php" method="POST" class="cart-clear">
                    <input type="hidden" name="action" value="clear">
                    <input type="hidden" name="redirect" value="cart.php">
                    <button type="submit" class="btn btn--text">Clear Cart</button>
                </form>
            </div>

            <aside class="cart-summary">
                <h2 class="cart-summary__title">Order Summary</h2>
                <div class="cart-summary__row">
                    <span>Items (<?= $cart['count'] ?>)</span>
                    <span><?= formatPrice($cart['total']) ?></span>
                </div>
                <div class="cart-summary__row">
                    <span>Delivery</span>
                    <span><?= $cart['total'] >= 50000 ? 'Free' : formatPrice(3000) ?></span>
                </div>
                <?php $deliveryFee = $cart['total'] >= 50000 ? 0 : 3000; ?>
                <?php $grandTotal = $cart['total'] + $deliveryFee; ?>
                <div class="cart-summary__row cart-summary__row--total">
                    <span>Total</span>
                    <span><?= formatPrice($grandTotal) ?></span>
                </div>
                <?php if ($cart['total'] < 50000): ?>
                <p class="cart-summary__note">Add <?= formatPrice(50000 - $cart['total']) ?> more for free delivery in Kigali!</p>
                <?php endif; ?>
                <a href="checkout.php" class="btn btn--primary btn--lg btn--block">Proceed to Checkout</a>
                <a href="products.php" class="btn btn--outline btn--block">Continue Shopping</a>
            </aside>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
