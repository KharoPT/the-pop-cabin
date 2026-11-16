<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Order.php';

requireLogin();

$orderNumber = $_GET['order'] ?? '';
$orderModel  = new Order();

// Admins may open any order (e.g. from the admin orders list); customers only their own.
$order = isAdmin()
    ? $orderModel->getByOrderNumber($orderNumber)
    : $orderModel->getByOrderNumber($orderNumber, currentUserId());

if (!$order) {
    http_response_code(404);
    $pageTitle = 'Order Not Found — ' . SITE_NAME;
    require_once __DIR__ . '/includes/header.php';
    ?>
    <section class="section container">
        <h1>Order not found</h1>
        <p>We could not find that order on your account.</p>
        <a class="btn btn-primary" href="<?php echo SITE_URL; ?>/account.php">Back to My Account</a>
    </section>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$orderItems = $orderModel->getItemsByOrderId((int) $order['order_id']);
$status     = orderStatus($order);

$pageTitle = 'Order ' . $order['order_number'] . ' — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Order <?php echo e($order['order_number']); ?></h1>
        <p>
            Status:
            <span class="status-pill status-<?php echo e($status); ?>"><?php echo e(ucfirst($status)); ?></span>
        </p>
    </div>
</section>

<section class="section">
    <div class="container cart-layout">
        <div class="cart-items">
            <?php foreach ($orderItems as $item): ?>
                <div class="cart-row cart-row-static">
                    <div class="cart-row-info">
                        <span class="cart-item-name"><?php echo e($item['product_name']); ?></span>
                        <p class="cart-item-price"><?php echo formatPrice($item['price']); ?> &times; <?php echo (int) $item['quantity']; ?></p>
                    </div>
                    <p class="cart-row-total"><?php echo formatPrice($item['price'] * $item['quantity']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <aside class="cart-summary">
            <h3>Summary</h3>
            <div class="summary-line"><span>Subtotal</span><span><?php echo formatPrice($order['subtotal']); ?></span></div>
            <div class="summary-line"><span>Shipping</span><span><?php echo $order['shipping_fee'] > 0 ? formatPrice($order['shipping_fee']) : 'Free'; ?></span></div>
            <div class="summary-line summary-total"><span>Total</span><span><?php echo formatPrice($order['total_amount']); ?></span></div>
            <h4>Shipping To</h4>
            <p>
                <?php echo e($order['shipping_name']); ?><br>
                <?php echo e($order['shipping_address']); ?><br>
                <?php echo e($order['shipping_city']); ?>, <?php echo e($order['shipping_postal_code']); ?><br>
                <?php echo e($order['shipping_country']); ?>
            </p>
        </aside>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
