<?php
require_once __DIR__ . '/includes/init.php';

if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (loginAdmin(getDBConnection(), $username, $password)) {
        header('Location: index.php');
        exit;
    }

    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-login-body">
    <div class="admin-login">
        <div class="admin-login__card">
            <div class="admin-login__header">
                <h1 class="admin-login__logo">Bambe</h1>
                <p>Admin Panel</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert--error"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="admin-login__form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus
                           value="<?= sanitize($_POST['username'] ?? '') ?>" placeholder="Enter username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter password">
                </div>
                <button type="submit" class="btn btn--primary btn--block btn--lg">Sign In</button>
            </form>

            <p class="admin-login__hint">Default: <code>admin</code> / <code>admin123</code></p>
            <a href="../index.php" class="admin-login__back">&larr; Back to Store</a>
        </div>
    </div>
</body>
</html>
