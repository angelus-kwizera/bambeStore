<?php
require_once __DIR__ . '/includes/init.php';

logoutAdmin();
header('Location: login.php');
exit;
