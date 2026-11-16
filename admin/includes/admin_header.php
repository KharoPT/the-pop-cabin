<?php
/**
 * Admin layout header. Every admin page calls requireAdmin() before including this.
 * Pages may set before including: $pageTitle, $adminCrumbs (e.g. [['Products','admin/products.php'],['Edit',null]]),
 * $adminErrors (extra error messages to show as banners).
 */
$adminPage     = basename($_SERVER['PHP_SELF']);
$flashMessages = getFlashMessages();
$adminErrors   = $adminErrors ?? [];
$adminCrumbs   = $adminCrumbs ?? [];

// Product form/delete screens belong to the Products menu item.
$adminSection = in_array($adminPage, ['product_form.php', 'product_delete.php'], true) ? 'products.php' : $adminPage;

$ico = [
    'dashboard' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1"/><rect x="13.5" y="3.5" width="7" height="7" rx="1"/><rect x="3.5" y="13.5" width="7" height="7" rx="1"/><rect x="13.5" y="13.5" width="7" height="7" rx="1"/></svg>',
    'products'  => '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><path d="M12 2.8l8 4.3v9.8l-8 4.3-8-4.3V7.1z"/><path d="M4 7.1l8 4.4 8-4.4M12 11.5v9.7"/></svg>',
    'users'     => '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.8 20c.4-3.4 3-5.5 6.2-5.5s5.8 2.1 6.2 5.5M16 4.8a3.5 3.5 0 0 1 0 6.4M18.5 14.8c1.7.7 2.8 2.4 3 4.7"/></svg>',
    'external'  => '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3h7v7M21 3l-9 9M18 14v5.5a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 4 19.5v-11A1.5 1.5 0 0 1 5.5 7H11"/></svg>',
    'logout'    => '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3.5H5.5A1.5 1.5 0 0 0 4 5v14a1.5 1.5 0 0 0 1.5 1.5H9M16 8l4 4-4 4M20 12H9"/></svg>',
];
$adminNav = [
    'index.php'    => ['Dashboard',    'dashboard'],
    'products.php' => ['Products',     'products'],
    'users.php'    => ['Users',        'users'],
];
$bannerIcon = [
    'success' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9.5"/><path d="M7.8 12.3l3 3 5.4-6"/></svg>',
    'error'   => '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9.5"/><path d="M12 7.5v5.5M12 16.6h.01"/></svg>',
];
$banners = [];
foreach ($flashMessages as $type => $message) { $banners[] = [$type === 'success' ? 'success' : 'error', $message]; }
foreach ($adminErrors as $message) { $banners[] = ['error', $message]; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?php echo e($pageTitle ?? 'Admin — ' . SITE_NAME); ?></title>
<link rel="icon" type="image/svg+xml" href="<?php echo SITE_URL; ?>/assets/img/logo.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cabin:wght@400;500;600;700&family=Zilla+Slab:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="admin-body">
<script>
  window.CSRF_TOKEN = <?php echo json_encode(generateCsrfToken()); ?>;
  window.SITE_URL = <?php echo json_encode(SITE_URL); ?>;
</script>

<header class="admin-header">
    <div class="admin-bar">
        <a href="<?php echo SITE_URL; ?>/admin/index.php" class="admin-id">
            <span class="pc-box">PC</span>
            <span class="admin-brand"><strong>The Pop Cabin</strong><span>Admin</span></span>
        </a>
        <button type="button" class="admin-menu-btn" id="admin-menu-btn" aria-expanded="false" aria-controls="admin-menu">
            <span class="admin-menu-icon" aria-hidden="true"><span></span><span></span><span></span></span>
            Menu
        </button>
    </div>
    <nav class="admin-menu" id="admin-menu" aria-label="Admin menu" hidden>
        <ul>
            <?php foreach ($adminNav as $file => [$label, $icon]): ?>
                <li><a href="<?php echo SITE_URL; ?>/admin/<?php echo $file; ?>" class="<?php echo $adminSection === $file ? 'is-active' : ''; ?>" <?php echo $adminSection === $file ? 'aria-current="page"' : ''; ?>><?php echo $ico[$icon]; ?><span><?php echo e($label); ?></span></a></li>
            <?php endforeach; ?>
        </ul>
        <ul class="admin-menu-foot">
            <li><a href="<?php echo SITE_URL; ?>/index.php"><?php echo $ico['external']; ?><span>View public site</span></a></li>
            <li><a href="<?php echo SITE_URL; ?>/logout.php"><?php echo $ico['logout']; ?><span>Log out</span></a></li>
        </ul>
    </nav>
</header>
<div class="admin-scrim" id="admin-scrim" hidden></div>

<main class="admin-main" id="main">
    <nav class="crumbs" aria-label="Breadcrumb">
        <a href="<?php echo SITE_URL; ?>/admin/index.php">Admin</a>
        <?php foreach ($adminCrumbs as $i => [$label, $path]): ?>
            <span aria-hidden="true">/</span>
            <?php if ($path && $i < count($adminCrumbs) - 1): ?><a href="<?php echo SITE_URL . '/' . $path; ?>"><?php echo e($label); ?></a>
            <?php else: ?><span aria-current="page"><?php echo e($label); ?></span><?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <?php foreach ($banners as [$type, $message]): ?>
        <div class="admin-banner admin-banner-<?php echo $type; ?>" role="<?php echo $type === 'error' ? 'alert' : 'status'; ?>">
            <?php echo $bannerIcon[$type]; ?>
            <p><?php echo e($message); ?></p>
            <button type="button" class="banner-close" data-dismiss aria-label="Dismiss message">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg>
            </button>
        </div>
    <?php endforeach; ?>
