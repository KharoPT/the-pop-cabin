<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Order.php';

requireAdmin();

$orderModel = new Order();

// ---- Save a status change (post, then redirect so a refresh never resubmits) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $backTo = 'admin/orders.php';
    if ((int) ($_POST['page'] ?? 1) > 1) {
        $backTo .= '?page=' . (int) $_POST['page'];
    }
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Your session expired. Please try again.');
        redirect('admin/orders.php');
    }

    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status  = (string) ($_POST['order_status'] ?? '');
    if ($orderId > 0 && $orderModel->updateStatus($orderId, $status)) {
        setFlash('success', 'Order status updated to ' . ucfirst($status) . '.');
    } else {
        setFlash('error', 'That status could not be saved.');
    }
    redirect($backTo);
}

// ---- Build the page ----
$total      = $orderModel->countAll();
$totalPages = max(1, (int) ceil($total / ADMIN_PER_PAGE));
$page       = min($totalPages, max(1, (int) ($_GET['page'] ?? 1)));
$orders     = $orderModel->getAllWithCustomer(ADMIN_PER_PAGE, ($page - 1) * ADMIN_PER_PAGE);

$statusOptions = [
    'pending'    => 'Pending',
    'processing' => 'Processing',
    'shipped'    => 'Shipped',
    'delivered'  => 'Delivered',
    'cancelled'  => 'Cancelled',
];

$pageTitle   = 'Orders — ' . SITE_NAME . ' Admin';
$adminCrumbs = [['Orders', null]];
require_once __DIR__ . '/includes/admin_header.php';
?>

<h1 class="admin-title">Orders</h1>

<div class="admin-list">
    <?php foreach ($orders as $o): ?>
        <?php
            $status     = (string) ($o['order_status'] ?? 'pending');
            $statusKey  = array_key_exists($status, $statusOptions) ? $status : 'pending';
            $customer   = trim((string) ($o['customer_name'] ?? '')) !== '' ? $o['customer_name'] : 'Customer #' . (int) $o['user_id'];
            $itemCount  = (int) ($o['item_count'] ?? 0);
            $items      = $orderModel->getItemsByOrderId((int) $o['order_id']);
            usort($items, fn(array $a, array $b): int => (int) $a['order_item_id'] <=> (int) $b['order_item_id']);
            $shippingFee = (float) $o['shipping_fee'];
        ?>
        <article class="admin-res">
            <div class="admin-res-top">
                <div>
                    <p class="res-name"><?php echo e($o['order_number']); ?></p>
                    <p class="res-sub"><?php echo e(shortDate($o['created_at'], true)); ?> &middot; <?php echo e($customer); ?> &middot; <?php echo $itemCount; ?> <?php echo $itemCount === 1 ? 'pop' : 'pops'; ?></p>
                </div>
                <span class="status-pill status-<?php echo e($statusKey); ?>"><?php echo e($statusOptions[$statusKey]); ?></span>
            </div>

            <?php $itemsLabelId = 'order-items-' . (int) $o['order_id']; ?>
            <div class="order-detail">
                <div class="order-detail-items">
                    <p class="order-detail-label" id="<?php echo $itemsLabelId; ?>">Products</p>
                    <?php if ($items): ?>
                        <ul class="order-lines" aria-labelledby="<?php echo $itemsLabelId; ?>">
                            <?php foreach ($items as $item): ?>
                                <li class="order-line">
                                    <span class="order-line-name"><?php echo e($item['product_name']); ?></span>
                                    <span class="order-line-qty"><?php echo formatPrice($item['price']); ?> &times; <?php echo (int) $item['quantity']; ?></span>
                                    <span class="order-line-total"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="order-lines-empty">No products were recorded for this order.</p>
                    <?php endif; ?>
                </div>

                <div class="order-detail-side">
                    <p class="order-detail-label">Summary</p>
                    <dl class="order-totals">
                        <div class="order-total-row"><dt>Subtotal</dt><dd><?php echo formatPrice($o['subtotal']); ?></dd></div>
                        <div class="order-total-row"><dt>Shipping</dt><dd><?php echo $shippingFee > 0 ? formatPrice($shippingFee) : 'Free'; ?></dd></div>
                    </dl>
                    <p class="order-detail-label">Ship to</p>
                    <p class="order-ship-to">
                        <?php echo e($o['shipping_name']); ?><br>
                        <?php echo e($o['shipping_address']); ?><br>
                        <?php echo e($o['shipping_city']); ?>, <?php echo e($o['shipping_postal_code']); ?><br>
                        <?php echo e($o['shipping_country']); ?>
                    </p>
                </div>
            </div>

            <div class="admin-res-actions">
                <span class="admin-price"><span class="sr-only">Order total </span><?php echo formatPrice($o['total_amount']); ?></span>
                <form class="status-form" method="post" action="<?php echo SITE_URL; ?>/admin/orders.php">
                    <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
                    <input type="hidden" name="order_id" value="<?php echo (int) $o['order_id']; ?>">
                    <input type="hidden" name="page" value="<?php echo $page; ?>">
                    <label class="sr-only" for="status-<?php echo (int) $o['order_id']; ?>">Status for order <?php echo e($o['order_number']); ?></label>
                    <select id="status-<?php echo (int) $o['order_id']; ?>" name="order_status">
                        <?php foreach ($statusOptions as $value => $label): ?>
                            <option value="<?php echo e($value); ?>" <?php echo $statusKey === $value ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-dark">Save</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$orders): ?><p class="admin-empty">No orders yet.</p><?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
    <nav class="pager admin-pager" aria-label="Pagination">
        <p class="pager-count">Page <?php echo $page; ?> of <?php echo $totalPages; ?></p>
        <div class="pager-links">
            <?php if ($page > 1): ?><a class="pager-btn" href="?page=<?php echo $page - 1; ?>">&lsaquo; prev</a><?php else: ?><span class="pager-btn is-disabled">&lsaquo; prev</span><?php endif; ?>
            <?php if ($page < $totalPages): ?><a class="pager-btn" href="?page=<?php echo $page + 1; ?>">next &rsaquo;</a><?php else: ?><span class="pager-btn is-disabled">next &rsaquo;</span><?php endif; ?>
        </div>
    </nav>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
