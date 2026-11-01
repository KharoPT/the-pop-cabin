<?php

$currentAdminPage = basename($_SERVER['PHP_SELF']);
$flashMessages    = getFlashMessages();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($pageTitle ?? ('Admin — ' . SITE_NAME)); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a href="<?php echo SITE_URL; ?>/admin/index.php" class="logo admin-logo">
            <span class="logo-mark">PC</span><span class="logo-text">Admin</span>
        </a>
        <nav class="admin-nav">
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="<?php echo $currentAdminPage === 'index.php' ? 'active' : ''; ?>">Dashboard</a>
            <a href="<?php echo SITE_URL; ?>/admin/products.php" class="<?php echo in_array($currentAdminPage, ['products.php', 'product_form.php'], true) ? 'active' : ''; ?>">Products</a>
            <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="<?php echo $currentAdminPage === 'orders.php' ? 'active' : ''; ?>">Orders</a>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="<?php echo SITE_URL; ?>/index.php">Back to Store</a>
            <a href="<?php echo SITE_URL; ?>/logout.php">Sign Out</a>
        </div>
    </aside>
    <main class="admin-main">
        <?php foreach ($flashMessages as $type => $message): ?>
            <div class="flash-message flash-<?php echo e($type); ?>"><?php echo e($message); ?></div>
        <?php endforeach; ?>
