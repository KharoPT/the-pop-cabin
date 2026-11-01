<?php
if (!isset($cartCountForHeader)) {
    require_once __DIR__ . '/../classes/Cart.php';
    $cartCountForHeader = (new Cart())->getItemCount();
}
$currentPage    = basename($_SERVER['PHP_SELF']);
$flashMessages  = getFlashMessages();
$navCategories  = getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($pageTitle ?? SITE_NAME); ?></title>
<meta name="description" content="The Pop Cabin — shop Funko Pop vinyl figures for fans and collectors.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
<script>
  window.CSRF_TOKEN = <?php echo json_encode(generateCsrfToken()); ?>;
  window.SITE_URL = <?php echo json_encode(SITE_URL); ?>;
</script>

<header class="site-header">
    <div class="container header-inner">
        <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
            <span class="logo-mark">PC</span>
            <span class="logo-text"><?php echo e(SITE_NAME); ?></span>
        </a>

        <button type="button" class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav">
            <a href="<?php echo SITE_URL; ?>/index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Home</a>
            <a href="<?php echo SITE_URL; ?>/shop.php" class="<?php echo $currentPage === 'shop.php' ? 'active' : ''; ?>">Shop All</a>
            <div class="nav-dropdown">
                <button type="button" class="nav-dropdown-trigger">Categories</button>
                <div class="nav-dropdown-menu">
                    <?php foreach ($navCategories as $navCat): ?>
                        <a href="<?php echo SITE_URL; ?>/shop.php?category=<?php echo e($navCat['slug']); ?>"><?php echo e($navCat['category_name']); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <form class="header-search" action="<?php echo SITE_URL; ?>/shop.php" method="get">
                <input type="text" name="search" placeholder="Search Funko Pops" value="<?php echo e($_GET['search'] ?? ''); ?>">
                <button type="submit" aria-label="Search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </button>
            </form>

            <?php if (isLoggedIn()): ?>
                <div class="nav-dropdown account-dropdown">
                    <button type="button" class="nav-dropdown-trigger">Hi, <?php echo e(explode(' ', $_SESSION['full_name'] ?? 'there')[0]); ?></button>
                    <div class="nav-dropdown-menu">
                        <a href="<?php echo SITE_URL; ?>/account.php">My Account</a>
                        <?php if (isAdmin()): ?>
                            <a href="<?php echo SITE_URL; ?>/admin/index.php">Admin Panel</a>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/logout.php">Sign Out</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/login.php" class="header-link <?php echo $currentPage === 'login.php' ? 'active' : ''; ?>">Sign In</a>
            <?php endif; ?>

            <a href="<?php echo SITE_URL; ?>/cart.php" class="cart-link <?php echo $currentPage === 'cart.php' ? 'active' : ''; ?>">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                Cart
                <span class="cart-badge" style="<?php echo $cartCountForHeader > 0 ? '' : 'display:none;'; ?>"><?php echo (int) $cartCountForHeader; ?></span>
            </a>
        </nav>
    </div>
</header>

<?php if (!empty($flashMessages)): ?>
    <div class="flash-wrap container">
        <?php foreach ($flashMessages as $type => $message): ?>
            <div class="flash-message flash-<?php echo e($type); ?>"><?php echo e($message); ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main>
