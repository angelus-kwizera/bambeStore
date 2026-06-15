<?php
require_once __DIR__ . '/includes/init.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$db = getDBConnection();
$action = $_POST['action'] ?? '';

if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id && deleteProduct($db, $id)) {
        setAdminFlash('success', 'Product deleted successfully.');
    } else {
        setAdminFlash('error', 'Failed to delete product.');
    }
    header('Location: products.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$categoryId = (int) ($_POST['category_id'] ?? 0);
$price = (float) ($_POST['price'] ?? 0);
$stock = (int) ($_POST['stock'] ?? 0);
$featured = isset($_POST['featured']);
$imageUrl = trim($_POST['image_url'] ?? '');
$currentImage = trim($_POST['current_image'] ?? '');

$errors = [];
if ($name === '') $errors[] = 'Product name is required.';
if ($description === '') $errors[] = 'Description is required.';
if ($categoryId < 1) $errors[] = 'Category is required.';
if ($price < 0) $errors[] = 'Price must be valid.';
if ($stock < 0) $errors[] = 'Stock cannot be negative.';

$uploadedImage = handleProductImageUpload($_FILES['image_file'] ?? null, '');

if (!empty($errors)) {
    setAdminFlash('error', implode(' ', $errors));
    $redirect = $action === 'update' ? 'product-form.php?id=' . (int) $_POST['id'] : 'product-form.php';
    header('Location: ' . $redirect);
    exit;
}

$finalImage = $uploadedImage ?? ($imageUrl !== '' ? $imageUrl : $currentImage);

if ($finalImage === '') {
    setAdminFlash('error', 'Please upload an image or provide an image URL.');
    $redirect = $action === 'update' ? 'product-form.php?id=' . (int) $_POST['id'] : 'product-form.php';
    header('Location: ' . $redirect);
    exit;
}

$data = [
    'name' => $name,
    'description' => $description,
    'category_id' => $categoryId,
    'price' => $price,
    'stock' => $stock,
    'featured' => $featured,
    'image_url' => $finalImage,
];

if ($action === 'create') {
    if (createProduct($db, $data)) {
        setAdminFlash('success', 'Product created successfully!');
        header('Location: products.php');
    } else {
        setAdminFlash('error', 'Failed to create product.');
        header('Location: product-form.php');
    }
    exit;
}

if ($action === 'update') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id && updateProduct($db, $id, $data)) {
        if ($uploadedImage && $currentImage && str_starts_with($currentImage, 'uploads/')) {
            $oldPath = __DIR__ . '/../' . $currentImage;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
        setAdminFlash('success', 'Product updated successfully!');
        header('Location: products.php');
    } else {
        setAdminFlash('error', 'Failed to update product.');
        header('Location: product-form.php?id=' . $id);
    }
    exit;
}

header('Location: products.php');
exit;
