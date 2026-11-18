<?php
/**
 * Public site header. Pages may set before including this file:
 *   $pageTitle, $metaDescription, $bodyClass, $pageBanner (true = title banner under the header)
 */
if (!isset($cartCountForHeader)) {
    require_once __DIR__ . '/../classes/Cart.php';
    $cartCountForHeader = (new Cart())->getItemCount();
}
$currentPage   = basename($_SERVER['PHP_SELF']);
$flashMessages = getFlashMessages();
$metaDescription = $metaDescription ?? 'The Pop Cabin: Funko Pop collectibles.';

$menuLinks = [
    ['shop.php',      'Shop Collections'],
    ['mechanics.php', 'System Mechanics'],
    ['about.php',     'About us'],
    ['contact.php',   'Contact Us'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($pageTitle ?? SITE_NAME); ?></title>
<meta name="description" content="<?php echo e($metaDescription); ?>">
<link rel="icon" type="image/svg+xml" href="<?php echo assetUrl('img/favicon.svg'); ?>">
<link rel="stylesheet" href="<?php echo assetUrl('css/tokens.css'); ?>">
<link rel="stylesheet" href="<?php echo assetUrl('css/base.css'); ?>">
<link rel="stylesheet" href="<?php echo assetUrl('css/header.css'); ?>">
<link rel="stylesheet" href="<?php echo assetUrl('css/footer.css'); ?>">
<?php foreach (($pageStyles ?? []) as $style): ?>
<link rel="stylesheet" href="<?php echo assetUrl($style); ?>">
<?php endforeach; ?>
</head>
<body class="<?php echo e(trim(($bodyClass ?? '') . (!empty($flashMessages) ? ' has-flash' : ''))); ?>">
<script>
  window.CSRF_TOKEN = <?php echo json_encode(generateCsrfToken()); ?>;
  window.SITE_URL = <?php echo json_encode(SITE_URL); ?>;
</script>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header" id="top">
    <div class="header-bar">
        <button type="button" class="menu-btn" id="menu-btn" aria-label="Open menu" aria-expanded="false" aria-controls="site-menu">
            <img src="<?php echo assetUrl('img/icon-menu.svg'); ?>" alt="" width="32" height="32">
        </button>

        <a href="<?php echo SITE_URL; ?>/index.php" class="logo-link" aria-label="<?php echo e(SITE_NAME); ?> home">
            <img src="<?php echo assetUrl('img/logo-mark.svg'); ?>" alt="" width="32" height="32">
        </a>

        <nav class="header-nav" aria-label="Main menu">
            <ul>
                <?php foreach ($menuLinks as [$file, $label]): ?>
                    <li><a href="<?php echo SITE_URL . '/' . $file; ?>" <?php echo $currentPage === $file ? 'aria-current="page"' : ''; ?>><?php echo e($label); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <form class="search-form" role="search" action="<?php echo SITE_URL; ?>/shop.php" method="get">
            <label class="sr-only" for="site-search">Search pops</label>
            <input type="search" id="site-search" name="search" placeholder="Search a Pop..." value="<?php echo e(is_string($_GET['search'] ?? null) ? $_GET['search'] : ''); ?>">
            <button type="submit" class="sr-only">Search</button>
        </form>

        <div class="header-actions">
            <a class="header-cart" href="<?php echo SITE_URL; ?>/cart.php" <?php echo $currentPage === 'cart.php' ? 'aria-current="page"' : ''; ?>>
                <img src="<?php echo assetUrl('img/icon-cart.svg'); ?>" alt="" width="20" height="20">
                <span class="header-cart-label">Cart</span>
                <span class="cart-badge" style="<?php echo $cartCountForHeader > 0 ? '' : 'display:none;'; ?>"><?php echo (int) $cartCountForHeader; ?></span>
            </a>

            <?php if (isLoggedIn()): ?>
                <details class="account-menu">
                    <summary class="login-btn account-trigger">
                        <span>Account</span>
                        <svg class="account-chevron" width="10" height="7" viewBox="0 0 10 7" fill="none" aria-hidden="true" focusable="false">
                            <path d="M1 1.5 5 5.5l4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </summary>
                    <div class="account-menu-panel">
                        <a href="<?php echo SITE_URL; ?>/account.php" <?php echo $currentPage === 'account.php' ? 'aria-current="page"' : ''; ?>>Account Details</a>
                        <?php if (isAdmin()): ?>
                            <a href="<?php echo SITE_URL; ?>/admin/index.php">Admin Panel</a>
                        <?php endif; ?>
                        <a class="account-menu-signout" href="<?php echo SITE_URL; ?>/logout.php">Sign Out</a>
                    </div>
                </details>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/login.php" class="login-btn">Sign In</a>
            <?php endif; ?>
        </div>
    </div>

    <nav class="site-menu" id="site-menu" aria-label="Main menu" hidden>
        <ul>
            <?php foreach ($menuLinks as [$file, $label]): ?>
                <li><a href="<?php echo SITE_URL . '/' . $file; ?>" <?php echo $currentPage === $file ? 'aria-current="page"' : ''; ?>><?php echo e($label); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>
</header>

<?php if (!empty($flashMessages)): ?>
    <div class="flash-wrap">
        <?php foreach ($flashMessages as $type => $message): ?>
            <div class="flash-message flash-<?php echo e($type); ?>" role="status"><?php echo e($message); ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main id="main">
