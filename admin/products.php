<?php
require_once __DIR__ . '/includes/init.php';
requireAdmin();

$pageTitle = 'Products';
$db = getDBConnection();
$products = getAllProductsAdmin($db);

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-toolbar">
    <p class="admin-toolbar__info"><?= count($products) ?> products in catalog</p>
    <a href="product-form.php" class="btn btn--primary">+ Add New Product</a>
</div>

<div class="admin-panel">
    <div class="admin-table-wrapper">
        <table class="admin-table admin-table--products">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <img src="../<?= sanitize($product['image_url']) ?>" alt="" class="admin-table__thumb"
                             onerror="this.src='https://via.placeholder.com/60x60?text=No+Image'">
                    </td>
                    <td>
                        <strong><?= sanitize($product['name']) ?></strong>
                        <br><small class="text-muted"><?= sanitize($product['slug']) ?></small>
                    </td>
                    <td><?= sanitize($product['category_name']) ?></td>
                    <td><?= formatPrice((float) $product['price']) ?></td>
                    <td>
                        <span class="<?= $product['stock'] <= 5 ? 'text-danger' : '' ?>">
                            <?= $product['stock'] ?>
                        </span>
                    </td>
                    <td><?= $product['featured'] ? '⭐ Yes' : '—' ?></td>
                    <td class="admin-table__actions">
                        <a href="product-form.php?id=<?= $product['id'] ?>" class="btn btn--sm btn--outline">Edit</a>
                        <form action="product-action.php" method="POST" class="inline-form"
                              onsubmit="return confirm('Delete this product?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                            <button type="submit" class="btn btn--sm btn--danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
