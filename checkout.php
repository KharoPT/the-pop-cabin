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

$pageTitle  = 'Checkout — ' . SITE_NAME;
$bodyClass  = 'page-checkout';
$pageStyles = ['css/receipt.css', 'css/checkout.css'];

require_once __DIR__ . '/includes/header.php';
?>

<img class="checkout-bg" src="<?php echo assetUrl('img/cart-bg.webp'); ?>" width="716" height="304" alt="">

<div class="wrap checkout-shell">
    <h1 class="checkout-title">Checkout</h1>

    <div class="checkout-body">
        <form class="checkout-form" method="post" action="<?php echo SITE_URL; ?>/actions/place_order.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">

            <h2 class="checkout-sub">Shipping Details</h2>

            <div class="checkout-fields">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required placeholder="Your Full Name"
                           value="<?php echo e($user['full_name'] ?? ''); ?>">
                </div>

                <div class="field">
                    <label for="address">Street Address</label>
                    <input type="text" id="address" name="address" required placeholder="Street Number/Street Name"
                           value="<?php echo e($user['address'] ?? ''); ?>">
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" required placeholder="City"
                               value="<?php echo e($user['city'] ?? ''); ?>">
                    </div>
                    <div class="field">
                        <label for="postal_code">Postal code</label>
                        <input type="text" id="postal_code" name="postal_code" required placeholder="1234"
                               value="<?php echo e($user['postal_code'] ?? ''); ?>">
                    </div>
                </div>

                <div class="field">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" required placeholder="Current Country"
                           value="<?php echo e($user['country'] ?? ''); ?>">
                </div>
            </div>

            <button type="submit" class="checkout-btn">Place Order</button>
        </form>

        <aside class="receipt-card" aria-labelledby="summary-title">
            <h2 id="summary-title">Order Summary</h2>

            <dl class="receipt-lines">
                <?php foreach ($items as $item): ?>
                    <div class="receipt-row">
                        <dt><?php echo e($item['name']); ?> &times; <?php echo (int) $item['quantity']; ?></dt>
                        <dd><?php echo formatPrice($item['price'] * $item['quantity']); ?></dd>
                    </div>
                <?php endforeach; ?>
                <div class="receipt-row">
                    <dt>Shipping</dt>
                    <dd><?php echo $shippingFee > 0 ? formatPrice($shippingFee) : 'Free'; ?></dd>
                </div>
            </dl>

            <hr class="receipt-divider">

            <div class="receipt-row receipt-total"><span>Total</span><span><?php echo formatPrice($total + $shippingFee); ?></span></div>
        </aside>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
