<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Cart.php';

$cart = new Cart();

// ---- Handle updates / removals (post, then redirect so a refresh never resubmits) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Your session expired. Please try again.');
        redirect('cart.php');
    }

    if (isset($_POST['remove_item'])) {
        $cart->removeItem((int) $_POST['remove_item']);
        setFlash('success', 'Item removed from your cart.');
        redirect('cart.php');
    }

    if (($_POST['action'] ?? '') === 'update' && is_array($_POST['quantities'] ?? null)) {
        $stockByItem = [];
        foreach ($cart->getItems() as $line) {
            $stockByItem[(int) $line['cart_item_id']] = (int) $line['stock_quantity'];
        }

        $capped = false;
        foreach ($_POST['quantities'] as $cartItemId => $qty) {
            $cartItemId = (int) $cartItemId;
            if (!isset($stockByItem[$cartItemId])) {
                continue; // not an item in this cart
            }
            $qty = (int) $qty;
            if ($qty > $stockByItem[$cartItemId]) {
                $qty    = $stockByItem[$cartItemId];
                $capped = true;
            }
            $cart->updateItem($cartItemId, $qty); // 0 or less removes the line
        }
        setFlash($capped ? 'error' : 'success', $capped
            ? 'Some quantities were lowered to the number we have in stock.'
            : 'Your cart has been updated.');
    }
    redirect('cart.php');
}

// ---- Build the page ----
$items       = $cart->getItems();
$subtotal    = $cart->getTotal();
$shippingFee = ($items && $subtotal < FREE_SHIPPING_THRESHOLD) ? FLAT_SHIPPING_FEE : 0;
$total       = $subtotal + $shippingFee;

// A line is "blocked" when it asks for more than we have; checkout waits until it is fixed.
$hasBlocked = false;
foreach ($items as $line) {
    if ((int) $line['quantity'] > (int) $line['stock_quantity']) {
        $hasBlocked = true;
        break;
    }
}

$pageTitle = 'Your Cart — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container"><h1>Your Cart</h1></div>
</section>

<section class="section">
    <div class="container">
    <?php if (empty($items)): ?>
        <p class="empty-note">Your cart is empty.</p>
        <p><a class="btn btn-primary" href="<?php echo SITE_URL; ?>/shop.php">Start Shopping</a></p>
    <?php else: ?>
        <div class="cart-layout">
            <form class="cart-items" method="post" action="<?php echo SITE_URL; ?>/cart.php">
                <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
                <input type="hidden" name="action" value="update">

                <?php foreach ($items as $item): ?>
                    <?php
                        $stock   = (int) $item['stock_quantity'];
                        $over    = (int) $item['quantity'] > $stock;
                        $imgSrc  = imgSrc($item['image_url'] ?? '');
                        $itemUrl = SITE_URL . '/product.php?slug=' . urlencode($item['slug']);
                    ?>
                    <div class="cart-row">
                        <a href="<?php echo e($itemUrl); ?>" class="cart-row-image">
                            <?php if ($imgSrc): ?>
                                <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e($item['name']); ?>" loading="lazy">
                            <?php else: ?>
                                <span class="img-ph" role="img" aria-label="<?php echo e($item['name']); ?>"></span>
                            <?php endif; ?>
                        </a>
                        <div class="cart-row-info">
                            <a class="cart-item-name" href="<?php echo e($itemUrl); ?>"><?php echo e($item['name']); ?></a>
                            <p class="cart-item-price"><?php echo formatPrice($item['price']); ?> each</p>
                            <?php if ($stock <= 0): ?>
                                <p class="stock-note stock-out">Sold out &mdash; remove it to continue.</p>
                            <?php elseif ($over): ?>
                                <p class="stock-note stock-out">Only <?php echo $stock; ?> available &mdash; update the quantity.</p>
                            <?php endif; ?>
                        </div>
                        <div class="cart-row-qty">
                            <label for="qty-<?php echo (int) $item['cart_item_id']; ?>" class="visually-hidden">Quantity</label>
                            <input type="number" id="qty-<?php echo (int) $item['cart_item_id']; ?>"
                                   name="quantities[<?php echo (int) $item['cart_item_id']; ?>]"
                                   value="<?php echo (int) $item['quantity']; ?>" min="0" max="<?php echo max(0, $stock); ?>">
                        </div>
                        <p class="cart-row-total"><?php echo formatPrice($item['price'] * $item['quantity']); ?></p>
                        <button type="submit" name="remove_item" value="<?php echo (int) $item['cart_item_id']; ?>"
                                class="link-danger cart-remove" aria-label="Remove <?php echo e($item['name']); ?>">Remove</button>
                    </div>
                <?php endforeach; ?>

                <div class="cart-actions">
                    <button type="submit" class="btn btn-ghost">Update Cart</button>
                    <a class="btn btn-ghost" href="<?php echo SITE_URL; ?>/shop.php">Continue Shopping</a>
                </div>
            </form>

            <aside class="cart-summary">
                <h3>Summary</h3>
                <div class="summary-line"><span>Subtotal</span><span><?php echo formatPrice($subtotal); ?></span></div>
                <div class="summary-line"><span>Shipping</span><span><?php echo $shippingFee > 0 ? formatPrice($shippingFee) : 'Free'; ?></span></div>
                <div class="summary-line summary-total"><span>Total</span><span><?php echo formatPrice($total); ?></span></div>

                <?php if ($shippingFee > 0): ?>
                    <p class="form-hint">Add <?php echo formatPrice(FREE_SHIPPING_THRESHOLD - $subtotal); ?> more for free shipping.</p>
                <?php endif; ?>

                <?php if ($hasBlocked): ?>
                    <button type="button" class="btn btn-disabled btn-block" disabled>Fix quantities to check out</button>
                <?php else: ?>
                    <a class="btn btn-primary btn-block" href="<?php echo SITE_URL; ?>/checkout.php">Proceed to Checkout</a>
                <?php endif; ?>
            </aside>
        </div>
    <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
