<?php
require_once __DIR__ . '/includes/init.php';
requireAdmin();

$db = getDBConnection();
$categories = getCategories($db);
$product = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $product = getAdminProductById($db, (int) $_GET['id']);
    if (!$product) {
        setAdminFlash('error', 'Product not found.');
        header('Location: products.php');
        exit;
    }
    $isEdit = true;
}

$pageTitle = $isEdit ? 'Edit Product' : 'Add Product';

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-panel admin-panel--form">
    <form action="product-action.php" method="POST" enctype="multipart/form-data" class="admin-form">
        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
        <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $product['id'] ?>">
        <input type="hidden" name="current_image" value="<?= sanitize($product['image_url']) ?>">
        <?php endif; ?>

        <div class="admin-form__grid">
            <div class="admin-form__main">
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" required
                           value="<?= sanitize($product['name'] ?? $_POST['name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="5" required><?= sanitize($product['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= ($product['category_id'] ?? $_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= sanitize($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (RWF) *</label>
                        <input type="number" id="price" name="price" min="0" step="1" required
                               value="<?= sanitize((string) ($product['price'] ?? $_POST['price'] ?? '')) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="stock">Stock Quantity *</label>
                        <input type="number" id="stock" name="stock" min="0" required
                               value="<?= sanitize((string) ($product['stock'] ?? $_POST['stock'] ?? '0')) ?>">
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="featured" value="1"
                                <?= ($product['featured'] ?? $_POST['featured'] ?? 0) ? 'checked' : '' ?>>
                            Featured product
                        </label>
                    </div>
                </div>
            </div>

            <div class="admin-form__sidebar">
                <div class="form-group">
                    <label>Product Image</label>
                    <?php if ($isEdit && $product['image_url']): ?>
                    <div class="image-preview">
                        <img src="../<?= sanitize($product['image_url']) ?>" alt="Current image" id="imagePreview">
                    </div>
                    <?php else: ?>
                    <div class="image-preview image-preview--empty" id="imagePreviewWrap">
                        <img src="" alt="" id="imagePreview" style="display:none">
                        <span id="imagePlaceholder">No image selected</span>
                    </div>
                    <?php endif; ?>

                    <label for="image_file" class="btn btn--outline btn--block" style="margin-top:12px">Upload Image</label>
                    <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" class="file-input">

                    <p class="form-hint">Or paste an image URL:</p>
                    <input type="url" name="image_url" placeholder="https://example.com/image.jpg"
                           value="<?= sanitize(
                               (isset($product['image_url']) && !str_starts_with($product['image_url'], 'uploads/'))
                                   ? $product['image_url']
                                   : ($_POST['image_url'] ?? '')
                           ) ?>">
                </div>
            </div>
        </div>

        <div class="admin-form__actions">
            <button type="submit" class="btn btn--primary btn--lg">
                <?= $isEdit ? 'Update Product' : 'Create Product' ?>
            </button>
            <a href="products.php" class="btn btn--outline btn--lg">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
