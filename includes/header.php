<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bambe - Rwanda's premier online store for clothes and shoes. Shop trendy fashion delivered across Kigali.">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' : '' ?><?= SITE_NAME ?> - <?= SITE_TAGLINE ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header__inner">
            <a href="index.php" class="logo">
                <span class="logo__text">Bambe</span>
                <span class="logo__tagline">Fashion Store</span>
            </a>

            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav class="nav" id="mainNav">
                <ul class="nav__list">
                    <li><a href="index.php" class="nav__link <?= $currentPage === 'index' ? 'nav__link--active' : '' ?>">Home</a></li>
                    <li><a href="products.php" class="nav__link <?= $currentPage === 'products' ? 'nav__link--active' : '' ?>">Shop</a></li>
                    <li><a href="products.php?category=clothes" class="nav__link">Clothes</a></li>
                    <li><a href="products.php?category=shoes" class="nav__link">Shoes</a></li>
                    <li class="nav__login-mobile"><a href="admin/login.php" class="nav__link nav__link--login">Login</a></li>
                </ul>
            </nav>

            <div class="header__actions">
                <form action="products.php" method="GET" class="search-form">
                    <input type="search" name="search" placeholder="Search products..." class="search-form__input" value="<?= sanitize($_GET['search'] ?? '') ?>">
                    <button type="submit" class="search-form__btn" aria-label="Search">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </button>
                </form>
                <a href="admin/login.php" class="btn btn--outline btn--sm header__login">Login</a>
                <a href="cart.php" class="cart-link">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <span class="cart-link__count" id="cartCount"><?= $cartCount ?></span>
                </a>
            </div>
        </div>
    </header>

    <main class="main">
        <?php $flash = getFlash(); if ($flash): ?>
        <div class="container">
            <div class="alert alert--<?= sanitize($flash['type']) ?>">
                <?= sanitize($flash['message']) ?>
            </div>
        </div>
        <?php endif; ?>
