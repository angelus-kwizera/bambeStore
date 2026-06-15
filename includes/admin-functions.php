<?php

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_id']);
}

function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function loginAdmin(PDO $db, string $username, string $password): bool
{
    $stmt = $db->prepare('SELECT * FROM admins WHERE username = ?');
    $stmt->execute([trim($username)]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_username'] = $admin['username'];
        return true;
    }

    return false;
}

function logoutAdmin(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_username']);
}

function getAdminName(): string
{
    return $_SESSION['admin_name'] ?? 'Admin';
}

function generateSlug(string $text): string
{
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

function ensureUniqueProductSlug(PDO $db, string $slug, ?int $excludeId = null): string
{
    $baseSlug = $slug;
    $counter = 1;

    while (true) {
        $sql = 'SELECT id FROM products WHERE slug = ?';
        $params = [$slug];

        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        if (!$stmt->fetch()) {
            return $slug;
        }

        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
}

function getAllProductsAdmin(PDO $db): array
{
    $stmt = $db->query(
        'SELECT p.*, c.name AS category_name
         FROM products p
         JOIN categories c ON p.category_id = c.id
         ORDER BY p.created_at DESC'
    );
    return $stmt->fetchAll();
}

function getAdminProductById(PDO $db, int $id): ?array
{
    $stmt = $db->prepare(
        'SELECT p.*, c.name AS category_name
         FROM products p
         JOIN categories c ON p.category_id = c.id
         WHERE p.id = ?'
    );
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    return $product ?: null;
}

function handleProductImageUpload(?array $file, string $fallbackUrl = ''): ?string
{
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed, true)) {
            return null;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };

        $uploadDir = __DIR__ . '/../uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid('product_', true) . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return 'uploads/products/' . $filename;
        }
    }

    if ($fallbackUrl !== '') {
        return $fallbackUrl;
    }

    return null;
}

function createProduct(PDO $db, array $data): bool
{
    $slug = ensureUniqueProductSlug($db, generateSlug($data['name']));

    $stmt = $db->prepare(
        'INSERT INTO products (category_id, name, slug, description, price, image_url, stock, featured)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );

    return $stmt->execute([
        $data['category_id'],
        $data['name'],
        $slug,
        $data['description'],
        $data['price'],
        $data['image_url'],
        $data['stock'],
        $data['featured'] ? 1 : 0,
    ]);
}

function updateProduct(PDO $db, int $id, array $data): bool
{
    $slug = ensureUniqueProductSlug($db, generateSlug($data['name']), $id);

    $stmt = $db->prepare(
        'UPDATE products SET category_id = ?, name = ?, slug = ?, description = ?,
         price = ?, image_url = ?, stock = ?, featured = ? WHERE id = ?'
    );

    return $stmt->execute([
        $data['category_id'],
        $data['name'],
        $slug,
        $data['description'],
        $data['price'],
        $data['image_url'],
        $data['stock'],
        $data['featured'] ? 1 : 0,
        $id,
    ]);
}

function deleteProduct(PDO $db, int $id): bool
{
    $product = getProductById($db, $id);
    if ($product && str_starts_with($product['image_url'], 'uploads/')) {
        $path = __DIR__ . '/../' . $product['image_url'];
        if (file_exists($path)) {
            unlink($path);
        }
    }

    $stmt = $db->prepare('DELETE FROM products WHERE id = ?');
    return $stmt->execute([$id]);
}

function getAllOrdersAdmin(PDO $db): array
{
    $stmt = $db->query(
        'SELECT o.*, c.full_name, c.email, c.phone, c.city,
                (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
         FROM orders o
         JOIN customers c ON o.customer_id = c.id
         ORDER BY o.created_at DESC'
    );
    return $stmt->fetchAll();
}

function getOrderByIdAdmin(PDO $db, int $id): ?array
{
    $stmt = $db->prepare(
        'SELECT o.*, c.full_name, c.email, c.phone, c.address, c.city
         FROM orders o
         JOIN customers c ON o.customer_id = c.id
         WHERE o.id = ?'
    );
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    if (!$order) {
        return null;
    }

    $stmt = $db->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $stmt->execute([$id]);
    $order['items'] = $stmt->fetchAll();

    return $order;
}

function updateOrderStatus(PDO $db, int $orderId, string $status): bool
{
    $allowed = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($status, $allowed, true)) {
        return false;
    }

    $stmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
    return $stmt->execute([$status, $orderId]);
}

function getDashboardStats(PDO $db): array
{
    $stats = [];

    $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE status != 'cancelled'");
    $stats['total_orders'] = (int) $stmt->fetchColumn();

    $stmt = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'");
    $stats['total_revenue'] = (float) $stmt->fetchColumn();

    $stmt = $db->query('SELECT COUNT(*) FROM products');
    $stats['total_products'] = (int) $stmt->fetchColumn();

    $stmt = $db->query('SELECT COUNT(*) FROM customers');
    $stats['total_customers'] = (int) $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'");
    $stats['orders_today'] = (int) $stmt->fetchColumn();

    $stmt = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status != 'cancelled'");
    $stats['revenue_month'] = (float) $stmt->fetchColumn();

    $stmt = $db->query('SELECT COUNT(*) FROM products WHERE stock <= 5');
    $stats['low_stock'] = (int) $stmt->fetchColumn();

    $stmt = $db->query(
        "SELECT status, COUNT(*) AS count FROM orders GROUP BY status"
    );
    $stats['orders_by_status'] = $stmt->fetchAll();

    $stmt = $db->query(
        'SELECT oi.product_name, SUM(oi.quantity) AS total_sold, SUM(oi.quantity * oi.price) AS revenue
         FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         WHERE o.status != \'cancelled\'
         GROUP BY oi.product_id, oi.product_name
         ORDER BY total_sold DESC
         LIMIT 5'
    );
    $stats['top_products'] = $stmt->fetchAll();

    $stmt = $db->query(
        'SELECT o.id, o.order_number, o.total, o.status, o.created_at, c.full_name
         FROM orders o
         JOIN customers c ON o.customer_id = c.id
         ORDER BY o.created_at DESC
         LIMIT 8'
    );
    $stats['recent_orders'] = $stmt->fetchAll();

    $stmt = $db->query(
        "SELECT DATE(created_at) AS order_date, COUNT(*) AS order_count, SUM(total) AS daily_revenue
         FROM orders
         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status != 'cancelled'
         GROUP BY DATE(created_at)
         ORDER BY order_date ASC"
    );
    $stats['sales_last_7_days'] = $stmt->fetchAll();

    return $stats;
}

function setAdminFlash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

function getAdminFlash(): ?array
{
    if (isset($_SESSION['admin_flash'])) {
        $flash = $_SESSION['admin_flash'];
        unset($_SESSION['admin_flash']);
        return $flash;
    }
    return null;
}

function orderStatusBadgeClass(string $status): string
{
    return match ($status) {
        'pending' => 'badge--warning',
        'confirmed' => 'badge--info',
        'shipped' => 'badge--primary',
        'delivered' => 'badge--success',
        'cancelled' => 'badge--danger',
        default => '',
    };
}
