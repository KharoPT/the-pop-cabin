<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Product.php';

requireAdmin();

$productModel = new Product();
$id      = (int) ($_GET['id'] ?? $_POST['product_id'] ?? 0);
$product = $id ? $productModel->getById($id) : false;

if (!$product) {
    setFlash('error', 'That product no longer exists.');
    redirect('admin/products.php');
}

$blocked     = false;
$adminErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $adminErrors[] = 'Your session expired. Please try again.';
    } elseif (!$productModel->delete($id)) {
        // The database refuses to delete a product that is still referenced by older records.
        $blocked       = true;
        $adminErrors[] = 'Cannot delete ' . $product['name'] . ': it is still referenced by existing records.';
    } else {
        deleteProductImage($product['image_url'] ?? null);
        setFlash('success', $product['name'] . ' was deleted.');
        redirect('admin/products.php');
    }
}

$pageTitle   = 'Delete product — ' . SITE_NAME . ' Admin';
$adminCrumbs = [['Products', 'admin/products.php'], ['Delete', null]];
require_once __DIR__ . '/includes/admin_header.php';
?>

<h1 class="admin-title">Delete product</h1>

<section class="admin-card confirm-card">
<?php if ($blocked): ?>
    <span class="confirm-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="10.5" width="14" height="10" rx="2"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5M12 14.5v2.5"/></svg>
    </span>
    <h2><?php echo e($product['name']); ?> was not deleted</h2>
    <p>It is still referenced by existing records, so the database keeps it.</p>
    <div class="confirm-note">
        <strong>What you can do</strong>
        <p>Set its stock to 0 to mark it as unavailable instead.</p>
    </div>
    <div class="confirm-actions">
        <a class="btn btn-outline" href="<?php echo SITE_URL; ?>/admin/products.php">Back to list</a>
        <a class="btn btn-dark" href="<?php echo SITE_URL; ?>/admin/product_form.php?id=<?php echo $id; ?>#stock_quantity">Edit stock</a>
    </div>
<?php else: ?>
    <span class="confirm-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.5l9.5 16.5h-19z"/><path d="M12 10v4.5M12 17.4h.01"/></svg>
    </span>
    <h2>Delete <?php echo e($product['name']); ?>?</h2>
    <p>This removes the product for good. It cannot be undone.</p>
    <form method="post" action="<?php echo SITE_URL; ?>/admin/product_delete.php?id=<?php echo $id; ?>" class="confirm-actions">
        <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
        <a class="btn btn-outline" href="<?php echo SITE_URL; ?>/admin/products.php">Cancel</a>
        <button type="submit" class="btn btn-outline btn-strong">Yes, delete</button>
    </form>
<?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
