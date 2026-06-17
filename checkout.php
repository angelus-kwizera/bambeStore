<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/paypal.php';

$pageTitle = 'Checkout';
$cart = getCartItems();

if (empty($cart['items'])) {
    setFlash('error', 'Your cart is empty. Add items before checkout.');
    header('Location: cart.php');
    exit;
}

$deliveryFee = $cart['total'] >= 50000 ? 0 : 3000;
$grandTotal = $cart['total'] + $deliveryFee;
$errors = [];
$paypalEnabled = isPayPalConfigured();
$selectedPayment = $_POST['payment_method'] ?? 'cod';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedPayment === 'cod') {
    $customerData = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? 'Kigali'),
        'notes' => trim($_POST['notes'] ?? ''),
    ];

    if (empty($customerData['full_name'])) {
        $errors[] = 'Full name is required.';
    }
    if (empty($customerData['email']) || !filter_var($customerData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if (empty($customerData['phone'])) {
        $errors[] = 'Phone number is required.';
    }
    if (empty($customerData['address'])) {
        $errors[] = 'Delivery address is required.';
    }

    if (empty($errors)) {
        $db = getDBConnection();
        $order = createOrder($db, $customerData, $cart['items'], $grandTotal, [
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);

        if ($order) {
            header('Location: order-confirmation.php?order=' . urlencode($order['order_number']));
            exit;
        }
        $errors[] = 'Failed to place order. Please try again.';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">Checkout</h1>
        <p class="page-header__breadcrumb">
            <a href="index.php">Home</a> / <a href="cart.php">Cart</a> / Checkout
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?= sanitize($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" class="checkout-layout" id="checkoutForm" novalidate>
            <div class="checkout-form">
                <h2 class="checkout-form__title">Delivery Details</h2>

                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required
                           value="<?= sanitize($_POST['full_name'] ?? '') ?>" placeholder="Jean Baptiste">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required
                               value="<?= sanitize($_POST['email'] ?? '') ?>" placeholder="you@email.com">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone *</label>
                        <input type="tel" id="phone" name="phone" required
                               value="<?= sanitize($_POST['phone'] ?? '') ?>" placeholder="+250 788 000 000">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Delivery Address *</label>
                    <textarea id="address" name="address" rows="3" required placeholder="Street, building, landmark..."><?= sanitize($_POST['address'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <select id="city" name="city">
                        <?php
                        $cities = ['Kigali', 'Musanze', 'Huye', 'Rubavu', 'Nyagatare', 'Muhanga', 'Other'];
                        $selectedCity = $_POST['city'] ?? 'Kigali';
                        foreach ($cities as $city):
                        ?>
                        <option value="<?= $city ?>" <?= $selectedCity === $city ? 'selected' : '' ?>><?= $city ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Order Notes (optional)</label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Special delivery instructions..."><?= sanitize($_POST['notes'] ?? '') ?></textarea>
                </div>

                <h2 class="checkout-form__title checkout-form__title--payment">Payment Method</h2>
                <div class="payment-methods">
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="cod" <?= $selectedPayment === 'cod' ? 'checked' : '' ?>>
                        <span class="payment-method__box">
                            <span class="payment-method__icon">💵</span>
                            <span class="payment-method__info">
                                <strong>Cash on Delivery</strong>
                                <small>Pay when your order arrives</small>
                            </span>
                        </span>
                    </label>
                    <label class="payment-method <?= !$paypalEnabled ? 'payment-method--disabled' : '' ?>">
                        <input type="radio" name="payment_method" value="paypal" <?= $selectedPayment === 'paypal' ? 'checked' : '' ?> <?= !$paypalEnabled ? 'disabled' : '' ?>>
                        <span class="payment-method__box">
                            <span class="payment-method__icon">🅿️</span>
                            <span class="payment-method__info">
                                <strong>PayPal</strong>
                                <small>Secure online payment<?= !$paypalEnabled ? ' (configure API keys to enable)' : '' ?></small>
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <aside class="checkout-summary">
                <h2 class="checkout-summary__title">Order Summary</h2>
                <div class="checkout-summary__items">
                    <?php foreach ($cart['items'] as $item): ?>
                    <div class="checkout-summary__item">
                        <img src="<?= sanitize($item['product']['image_url']) ?>" alt="">
                        <div>
                            <p class="checkout-summary__name"><?= sanitize($item['product']['name']) ?></p>
                            <p class="checkout-summary__qty">Qty: <?= $item['quantity'] ?> × <?= formatPrice((float) $item['product']['price']) ?></p>
                        </div>
                        <span class="checkout-summary__price"><?= formatPrice($item['subtotal']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="cart-summary__row">
                    <span>Subtotal</span>
                    <span><?= formatPrice($cart['total']) ?></span>
                </div>
                <div class="cart-summary__row">
                    <span>Delivery</span>
                    <span><?= $deliveryFee === 0 ? 'Free' : formatPrice($deliveryFee) ?></span>
                </div>
                <div class="cart-summary__row cart-summary__row--total">
                    <span>Total</span>
                    <span id="checkoutTotal"><?= formatPrice($grandTotal) ?></span>
                </div>

                <button type="submit" class="btn btn--primary btn--lg btn--block" id="placeOrderBtn">Place Order</button>
                <div id="paypalButtonContainer" class="paypal-button-container" hidden></div>
                <p class="checkout-summary__secure">🔒 Your information is secure</p>
            </aside>
        </form>
    </div>
</section>

<?php if ($paypalEnabled): ?>
<script src="https://www.paypal.com/sdk/js?client-id=<?= sanitize(PAYPAL_CLIENT_ID) ?>&currency=USD"></script>
<?php endif; ?>
<script>
window.BAMBE_CHECKOUT = {
    paypalEnabled: <?= $paypalEnabled ? 'true' : 'false' ?>,
    grandTotalRwf: <?= (float) $grandTotal ?>,
};
</script>
<script src="assets/js/checkout.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
