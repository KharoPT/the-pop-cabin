<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Order.php';

requireAdmin();

$productModel = new Product();

$productCount = $productModel->countAll();
$userCount    = (new User())->countAll();
$orderCount   = (new Order())->countAll();
$outOfStock   = $productModel->countOutOfStock();

$pageTitle   = 'Dashboard — ' . SITE_NAME . ' Admin';
$adminCrumbs = [['Dashboard', null]];
require_once __DIR__ . '/includes/admin_header.php';
?>

<h1 class="admin-title">Dashboard</h1>

<div class="admin-stats">
    <div class="stat-card"><span class="stat-label">Products</span><span class="stat-value"><?php echo $productCount; ?></span></div>
    <div class="stat-card"><span class="stat-label">Users</span><span class="stat-value"><?php echo $userCount; ?></span></div>
    <div class="stat-card"><span class="stat-label">Orders</span><span class="stat-value"><?php echo $orderCount; ?></span></div>
    <div class="stat-card"><span class="stat-label">Out of stock</span><span class="stat-value"><?php echo $outOfStock; ?></span></div>
</div>

<div class="admin-actions">
    <a class="btn btn-dark" href="<?php echo SITE_URL; ?>/admin/product_form.php">Add Product</a>
    <a class="btn btn-outline" href="<?php echo SITE_URL; ?>/admin/products.php">All Products</a>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
