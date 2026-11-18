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

$pageTitle  = 'Your Cart — ' . SITE_NAME;
$bodyClass  = 'page-cart';
$pageStyles = ['css/cart.css'];

// Icons used by the quantity pill and the remove action.
$minusIcon = '<svg viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M3 8h10"/></svg>';
$plusIcon  = '<svg viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M8 3v10M3 8h10"/></svg>';
$trashIcon = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M9.5 7V5.2c0-.7.6-1.2 1.3-1.2h2.4c.7 0 1.3.5 1.3 1.2V7"/><path d="M6.5 7l.9 11.1c.1.9.8 1.6 1.7 1.6h5.8c.9 0 1.6-.7 1.7-1.6L17.5 7"/><path d="M10.4 10.6v5.6M13.6 10.6v5.6"/></svg>';

require_once __DIR__ . '/includes/header.php';
?>

<img class="cart-bg" src="<?php echo assetUrl('img/cart-bg.webp'); ?>" width="716" height="304" alt="">

<div class="wrap cart-shell">
    <h1 class="cart-title">Your Cart</h1>

    <?php if (empty($items)): ?>
        <div class="cart-empty">
            <p class="cart-empty-title">Your cart is empty</p>
            <p class="cart-empty-text">Pick a pop from the collection and it will show up here.</p>
            <a class="cart-btn cart-btn-continue cart-empty-cta" href="<?php echo SITE_URL; ?>/shop.php">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="cart-body">
            <form class="cart-form" method="post" action="<?php echo SITE_URL; ?>/cart.php">
                <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
                <input type="hidden" name="action" value="update">

                <div class="cart-list">
                    <?php foreach ($items as $item): ?>
                        <?php
                            $itemId  = (int) $item['cart_item_id'];
                            $stock   = (int) $item['stock_quantity'];
                            $over    = (int) $item['quantity'] > $stock;
                            $imgSrc  = imgSrc($item['image_url'] ?? '');
                            $itemUrl = SITE_URL . '/product.php?slug=' . urlencode($item['slug']);
                        ?>
                        <article class="cart-card">
                            <a class="cart-card-thumb" href="<?php echo e($itemUrl); ?>" tabindex="-1" aria-hidden="true">
                                <?php if ($imgSrc): ?>
                                    <img src="<?php echo e($imgSrc); ?>" alt="" loading="lazy">
                                <?php else: ?>
                                    <span class="img-ph" role="img" aria-label="No photo yet"></span>
                                <?php endif; ?>
                            </a>

                            <div class="cart-card-text">
                                <a class="cart-card-name" href="<?php echo e($itemUrl); ?>"><?php echo e($item['name']); ?></a>
                                <p class="cart-card-each"><?php echo formatPrice($item['price']); ?> each</p>
                                <?php if ($stock <= 0): ?>
                                    <p class="cart-card-note">Sold out &mdash; remove it to continue.</p>
                                <?php elseif ($over): ?>
                                    <p class="cart-card-note">Only <?php echo $stock; ?> available &mdash; update the quantity.</p>
                                <?php endif; ?>
                            </div>

                            <div class="cart-qty">
                                <button type="button" class="qty-step" data-step="-1" aria-label="Decrease quantity of <?php echo e($item['name']); ?>"><?php echo $minusIcon; ?></button>
                                <label class="visually-hidden" for="qty-<?php echo $itemId; ?>">Quantity of <?php echo e($item['name']); ?></label>
                                <input type="number" class="qty-value" id="qty-<?php echo $itemId; ?>"
                                       name="quantities[<?php echo $itemId; ?>]"
                                       value="<?php echo (int) $item['quantity']; ?>" min="0" max="<?php echo max(0, $stock); ?>">
                                <button type="button" class="qty-step" data-step="1" aria-label="Increase quantity of <?php echo e($item['name']); ?>"><?php echo $plusIcon; ?></button>
                            </div>

                            <div class="cart-card-foot">
                                <p class="cart-line-total"><?php echo formatPrice($item['price'] * $item['quantity']); ?></p>
                                <button type="submit" name="remove_item" value="<?php echo $itemId; ?>"
                                        class="cart-remove" aria-label="Remove <?php echo e($item['name']); ?> from your cart"><?php echo $trashIcon; ?>Remove</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="cart-bar">
                    <button type="submit" class="cart-btn cart-btn-update">Update Cart</button>
                    <a class="cart-btn cart-btn-continue" href="<?php echo SITE_URL; ?>/shop.php">Continue Shopping</a>
                </div>
            </form>

            <aside class="receipt-card" aria-labelledby="summary-title">
                <h2 id="summary-title">Summary</h2>

                <dl class="receipt-lines">
                    <div class="receipt-row"><dt>Subtotal</dt><dd><?php echo formatPrice($subtotal); ?></dd></div>
                    <div class="receipt-row"><dt>Shipping</dt><dd><?php echo $shippingFee > 0 ? formatPrice($shippingFee) : 'Free'; ?></dd></div>
                </dl>

                <?php if ($shippingFee > 0): ?>
                    <p class="receipt-hint">Add <?php echo formatPrice(FREE_SHIPPING_THRESHOLD - $subtotal); ?> more for free shipping.</p>
                <?php endif; ?>

                <hr class="receipt-divider">

                <div class="receipt-row receipt-total"><span>Total</span><span><?php echo formatPrice($total); ?></span></div>

                <?php if ($hasBlocked): ?>
                    <span class="checkout-btn is-blocked" aria-disabled="true">Fix quantities to check out</span>
                <?php else: ?>
                    <a class="checkout-btn" href="<?php echo SITE_URL; ?>/checkout.php">Proceed to Checkout</a>
                <?php endif; ?>
            </aside>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
