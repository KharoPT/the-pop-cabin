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
    ['index.php',     'Home'],
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
<link rel="stylesheet" href="<?php echo assetUrl('css/style.css'); ?>">
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

        <form class="search-form" role="search" action="<?php echo SITE_URL; ?>/shop.php" method="get">
            <label class="sr-only" for="site-search">Search pops</label>
            <input type="search" id="site-search" name="search" placeholder="Search a Pop..." value="<?php echo e(is_string($_GET['search'] ?? null) ? $_GET['search'] : ''); ?>">
            <button type="submit" class="sr-only">Search</button>
        </form>

        <?php if (isLoggedIn()): ?>
            <a href="<?php echo SITE_URL; ?>/account.php" class="login-btn">Account</a>
        <?php else: ?>
            <a href="<?php echo SITE_URL; ?>/login.php" class="login-btn">Sign In</a>
        <?php endif; ?>
    </div>

    <nav class="site-menu" id="site-menu" aria-label="Main menu" hidden>
        <ul>
            <?php foreach ($menuLinks as [$file, $label]): ?>
                <li><a href="<?php echo SITE_URL . '/' . $file; ?>" <?php echo $currentPage === $file ? 'aria-current="page"' : ''; ?>><?php echo e($label); ?></a></li>
            <?php endforeach; ?>
        </ul>
        <ul class="site-menu-account">
            <li>
                <a href="<?php echo SITE_URL; ?>/cart.php" <?php echo $currentPage === 'cart.php' ? 'aria-current="page"' : ''; ?>>Cart
                    <span class="cart-badge" style="<?php echo $cartCountForHeader > 0 ? '' : 'display:none;'; ?>"><?php echo (int) $cartCountForHeader; ?></span>
                </a>
            </li>
            <?php if (isLoggedIn()): ?>
                <li><a href="<?php echo SITE_URL; ?>/account.php">My Account</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="<?php echo SITE_URL; ?>/admin/index.php">Admin Panel</a></li>
                <?php endif; ?>
                <li><a href="<?php echo SITE_URL; ?>/logout.php">Log out</a></li>
            <?php else: ?>
                <li><a href="<?php echo SITE_URL; ?>/login.php">Login</a></li>
                <li><a href="<?php echo SITE_URL; ?>/register.php">Sign Up</a></li>
            <?php endif; ?>
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
