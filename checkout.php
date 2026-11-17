<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Cart.php';
require_once __DIR__ . '/classes/User.php';

requireLogin();

$cart  = new Cart();
$items = $cart->getItems();

if (empty($items)) {
    setFlash('error', 'Your cart is empty — add something before checking out.');
    redirect('cart.php');
}

$user = (new User())->getById(currentUserId());

$total       = $cart->getTotal();
$shippingFee = $total < FREE_SHIPPING_THRESHOLD ? FLAT_SHIPPING_FEE : 0;

$pageTitle = 'Checkout — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container"><h1>Checkout</h1></div>
</section>

<section class="section">
    <div class="container cart-layout">
        <form class="checkout-form" method="post" action="<?php echo SITE_URL; ?>/actions/place_order.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">

            <h3>Shipping Details</h3>
            <div class="form-row">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" required value="<?php echo e($user['full_name'] ?? ''); ?>">
            </div>
            <div class="form-row">
                <label for="address">Street address</label>
                <input type="text" id="address" name="address" required value="<?php echo e($user['address'] ?? ''); ?>">
            </div>
            <div class="form-row form-row-split">
                <div>
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" required value="<?php echo e($user['city'] ?? ''); ?>">
                </div>
                <div>
                    <label for="postal_code">Postal code</label>
                    <input type="text" id="postal_code" name="postal_code" required value="<?php echo e($user['postal_code'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-row">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" required value="<?php echo e($user['country'] ?? ''); ?>">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Place Order</button>
        </form>

        <aside class="cart-summary">
            <h3>Order Summary</h3>
            <?php foreach ($items as $item): ?>
                <div class="summary-line">
                    <span><?php echo e($item['name']); ?> &times; <?php echo (int) $item['quantity']; ?></span>
                    <span><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                </div>
            <?php endforeach; ?>
            <div class="summary-line"><span>Shipping</span><span><?php echo $shippingFee > 0 ? formatPrice($shippingFee) : 'Free'; ?></span></div>
            <div class="summary-line summary-total"><span>Total</span><span><?php echo formatPrice($total + $shippingFee); ?></span></div>
        </aside>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
