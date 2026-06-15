<?php
$currentAdminPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' : '' ?>Admin - <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar__brand">
                <a href="index.php">
                    <span class="admin-sidebar__logo">Bambe</span>
                    <span class="admin-sidebar__label">Admin Panel</span>
                </a>
            </div>
            <nav class="admin-nav">
                <a href="index.php" class="admin-nav__link <?= $currentAdminPage === 'index' ? 'admin-nav__link--active' : '' ?>">
                    <span class="admin-nav__icon">📊</span> Dashboard
                </a>
                <a href="products.php" class="admin-nav__link <?= in_array($currentAdminPage, ['products', 'product-form']) ? 'admin-nav__link--active' : '' ?>">
                    <span class="admin-nav__icon">👕</span> Products
                </a>
                <a href="product-form.php" class="admin-nav__link <?= $currentAdminPage === 'product-form' && !isset($_GET['id']) ? 'admin-nav__link--active' : '' ?>">
                    <span class="admin-nav__icon">➕</span> Add Product
                </a>
                <a href="orders.php" class="admin-nav__link <?= in_array($currentAdminPage, ['orders', 'order-view']) ? 'admin-nav__link--active' : '' ?>">
                    <span class="admin-nav__icon">📦</span> Orders
                </a>
                <a href="../index.php" class="admin-nav__link" target="_blank">
                    <span class="admin-nav__icon">🌐</span> View Store
                </a>
                <a href="logout.php" class="admin-nav__link admin-nav__link--logout">
                    <span class="admin-nav__icon">🚪</span> Logout
                </a>
            </nav>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <button class="admin-topbar__toggle" id="sidebarToggle" aria-label="Toggle sidebar">☰</button>
                <h1 class="admin-topbar__title"><?= sanitize($pageTitle ?? 'Dashboard') ?></h1>
                <div class="admin-topbar__user">
                    <span>👤 <?= sanitize(getAdminName()) ?></span>
                </div>
            </header>

            <div class="admin-content">
                <?php $adminFlash = getAdminFlash(); if ($adminFlash): ?>
                <div class="alert alert--<?= sanitize($adminFlash['type']) ?>">
                    <?= sanitize($adminFlash['message']) ?>
                </div>
                <?php endif; ?>
