<?php

function formatPrice(float $price): string
{
    return CURRENCY . ' ' . number_format($price, 0, '.', ',');
}

function getCategories(PDO $db): array
{
    $stmt = $db->query('SELECT * FROM categories ORDER BY name');
    return $stmt->fetchAll();
}

function getProducts(PDO $db, ?string $categorySlug = null, ?string $search = null): array
{
    $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.stock > 0';
    $params = [];

    if ($categorySlug) {
        $sql .= ' AND c.slug = ?';
        $params[] = $categorySlug;
    }

    if ($search) {
        $sql .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY p.featured DESC, p.name ASC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getFeaturedProducts(PDO $db, int $limit = 8): array
{
    $stmt = $db->prepare(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         JOIN categories c ON p.category_id = c.id
         WHERE p.featured = 1 AND p.stock > 0
         ORDER BY p.created_at DESC
         LIMIT ?'
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getProductBySlug(PDO $db, string $slug): ?array
{
    $stmt = $db->prepare(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         JOIN categories c ON p.category_id = c.id
         WHERE p.slug = ?'
    );
    $stmt->execute([$slug]);
    $product = $stmt->fetch();
    return $product ?: null;
}

function getProductById(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    return $product ?: null;
}

function addToCart(int $productId, int $quantity = 1): bool
{
    $db = getDBConnection();
    $product = getProductById($db, $productId);

    if (!$product || $product['stock'] < 1) {
        return false;
    }

    $currentQty = $_SESSION['cart'][$productId] ?? 0;
    $newQty = $currentQty + $quantity;

    if ($newQty > $product['stock']) {
        $newQty = $product['stock'];
    }

    $_SESSION['cart'][$productId] = $newQty;
    return true;
}

function updateCartQuantity(int $productId, int $quantity): bool
{
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
        return true;
    }

    $db = getDBConnection();
    $product = getProductById($db, $productId);

    if (!$product) {
        unset($_SESSION['cart'][$productId]);
        return false;
    }

    $_SESSION['cart'][$productId] = min($quantity, $product['stock']);
    return true;
}

function removeFromCart(int $productId): void
{
    unset($_SESSION['cart'][$productId]);
}

function clearCart(): void
{
    $_SESSION['cart'] = [];
}

function getCartItems(): array
{
    if (empty($_SESSION['cart'])) {
        return [];
    }

    $db = getDBConnection();
    $items = [];
    $total = 0;

    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $product = getProductById($db, (int) $productId);
        if ($product) {
            $subtotal = $product['price'] * $quantity;
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
            $total += $subtotal;
        }
    }

    return ['items' => $items, 'total' => $total, 'count' => array_sum($_SESSION['cart'])];
}

function getCartCount(): int
{
    return array_sum($_SESSION['cart'] ?? []);
}

function generateOrderNumber(): string
{
    return 'BMB-' . strtoupper(substr(uniqid(), -8));
}

function createOrder(PDO $db, array $customerData, array $cartItems, float $total): ?array
{
    try {
        $db->beginTransaction();

        $stmt = $db->prepare(
            'INSERT INTO customers (full_name, email, phone, address, city) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $customerData['full_name'],
            $customerData['email'],
            $customerData['phone'],
            $customerData['address'],
            $customerData['city'],
        ]);
        $customerId = (int) $db->lastInsertId();

        $orderNumber = generateOrderNumber();
        $stmt = $db->prepare(
            'INSERT INTO orders (customer_id, order_number, total, status, notes) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $customerId,
            $orderNumber,
            $total,
            'confirmed',
            $customerData['notes'] ?? null,
        ]);
        $orderId = (int) $db->lastInsertId();

        $itemStmt = $db->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)'
        );
        $stockStmt = $db->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');

        foreach ($cartItems as $item) {
            $product = $item['product'];
            $qty = $item['quantity'];

            $itemStmt->execute([$orderId, $product['id'], $product['name'], $qty, $product['price']]);
            $stockStmt->execute([$qty, $product['id'], $qty]);
        }

        $db->commit();
        clearCart();

        return [
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'customer_id' => $customerId,
        ];
    } catch (Exception $e) {
        $db->rollBack();
        return null;
    }
}

function getOrderByNumber(PDO $db, string $orderNumber): ?array
{
    $stmt = $db->prepare(
        'SELECT o.*, c.full_name, c.email, c.phone, c.address, c.city
         FROM orders o
         JOIN customers c ON o.customer_id = c.id
         WHERE o.order_number = ?'
    );
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();

    if (!$order) {
        return null;
    }

    $stmt = $db->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $stmt->execute([$order['id']]);
    $order['items'] = $stmt->fetchAll();

    return $order;
}

function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderProductCard(array $product): string
{
    $price = formatPrice((float) $product['price']);
    $name = sanitize($product['name']);
    $slug = sanitize($product['slug']);
    $image = sanitize($product['image_url']);
    $category = sanitize($product['category_name']);

    return <<<HTML
    <article class="product-card">
        <a href="product.php?slug={$slug}" class="product-card__image-link">
            <img src="{$image}" alt="{$name}" class="product-card__image" loading="lazy">
            <span class="product-card__category">{$category}</span>
        </a>
        <div class="product-card__body">
            <h3 class="product-card__title">
                <a href="product.php?slug={$slug}">{$name}</a>
            </h3>
            <p class="product-card__price">{$price}</p>
            <form action="cart-action.php" method="POST" class="product-card__form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="{$product['id']}">
                <input type="hidden" name="redirect" value="{$_SERVER['REQUEST_URI']}">
                <button type="submit" class="btn btn--primary btn--sm">Add to Cart</button>
            </form>
        </div>
    </article>
    HTML;
}
