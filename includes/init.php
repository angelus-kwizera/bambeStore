<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

define('SITE_NAME', 'Bambe');
define('SITE_TAGLINE', 'Clothes & Shoes for Every Style');
define('CURRENCY', 'RWF');
