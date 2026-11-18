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
        ?>
        <article class="admin-res">
            <div class="admin-res-top">
                <div>
                    <p class="res-name"><?php echo e($o['order_number']); ?></p>
                    <p class="res-sub"><?php echo e(shortDate($o['created_at'], true)); ?> &middot; <?php echo e($customer); ?> &middot; <?php echo $itemCount; ?> <?php echo $itemCount === 1 ? 'pop' : 'pops'; ?></p>
                </div>
                <span class="status-pill status-<?php echo e($statusKey); ?>"><?php echo e($statusOptions[$statusKey]); ?></span>
            </div>

            <div class="admin-res-actions">
                <span class="admin-price"><?php echo formatPrice($o['total_amount']); ?></span>
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
