<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Product.php';

requireAdmin();

$productModel = new Product();

$param   = fn(string $k): string => is_string($_GET[$k] ?? null) ? trim($_GET[$k]) : '';
$filters = [
    'search'   => sanitizeInput($param('q')),
    'category' => $param('franchise'),
    'stock'    => in_array($param('stock'), ['in', 'out'], true) ? $param('stock') : '',
    'sort'     => 'newest',
];
$total      = $productModel->countAll($filters);
$totalPages = max(1, (int) ceil($total / ADMIN_PER_PAGE));
$page       = min($totalPages, max(1, (int) $param('page')));
$products   = $productModel->getAll($filters, ADMIN_PER_PAGE, ($page - 1) * ADMIN_PER_PAGE);
$franchises = getAllCategories();

$filtersOpen = $filters['category'] !== '' || $filters['stock'] !== '';
$qs = array_filter(['q' => $filters['search'], 'franchise' => $filters['category'], 'stock' => $filters['stock']], fn($v) => $v !== '');
$pageUrl = fn(int $p): string => SITE_URL . '/admin/products.php?' . http_build_query($qs + ['page' => $p]);

$pageTitle   = 'Products — ' . SITE_NAME . ' Admin';
$adminCrumbs = [['Products', null]];
require_once __DIR__ . '/includes/admin_header.php';
?>

<h1 class="admin-title">Products</h1>

<a class="btn btn-dark btn-block btn-lg" href="<?php echo SITE_URL; ?>/admin/product_form.php">+ Add product</a>

<form class="admin-search" method="get" action="<?php echo SITE_URL; ?>/admin/products.php" role="search">
    <label class="sr-only" for="q">Search products</label>
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
    <input type="search" id="q" name="q" placeholder="Search products..." value="<?php echo e($filters['search']); ?>">
    <?php if ($filtersOpen): ?>
        <input type="hidden" name="franchise" value="<?php echo e($filters['category']); ?>">
        <input type="hidden" name="stock" value="<?php echo e($filters['stock']); ?>">
    <?php endif; ?>
</form>

<details class="admin-filters" <?php echo $filtersOpen ? 'open' : ''; ?>>
    <summary>Filters</summary>
    <form method="get" action="<?php echo SITE_URL; ?>/admin/products.php">
        <input type="hidden" name="q" value="<?php echo e($filters['search']); ?>">
        <div class="form-row">
            <label for="franchise">Franchise</label>
            <select id="franchise" name="franchise">
                <option value="">All franchises</option>
                <?php foreach ($franchises as $c): ?>
                    <option value="<?php echo e($c['slug']); ?>" <?php echo $filters['category'] === $c['slug'] ? 'selected' : ''; ?>><?php echo e($c['category_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <label for="stock">Stock</label>
            <select id="stock" name="stock">
                <option value="">All</option>
                <option value="in"  <?php echo $filters['stock'] === 'in' ? 'selected' : ''; ?>>In stock</option>
                <option value="out" <?php echo $filters['stock'] === 'out' ? 'selected' : ''; ?>>Out of stock</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-dark">Apply</button>
            <a class="btn btn-outline" href="<?php echo SITE_URL; ?>/admin/products.php<?php echo $filters['search'] !== '' ? '?q=' . urlencode($filters['search']) : ''; ?>">Clear</a>
        </div>
    </form>
</details>

<div class="admin-list">
    <?php foreach ($products as $p): ?>
        <?php
            $stock = (int) $p['stock_quantity'];
            $stockClass = $stock <= 0 ? 'is-out' : ($stock <= 2 ? 'is-low' : '');
            $thumb = imgSrc($p['image_url'] ?? '');
        ?>
        <article class="admin-product">
            <div class="admin-product-top">
                <span class="admin-thumb">
                    <?php if ($thumb): ?><img src="<?php echo e($thumb); ?>" alt="" loading="lazy">
                    <?php else: ?><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="4.5" width="17" height="15" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="M4 17l5-4.5 3.5 3L16 12l4.5 4.5"/></svg><?php endif; ?>
                </span>
                <div class="admin-product-info">
                    <h2><?php echo e($p['name']); ?></h2>
                    <p class="admin-product-sub"><?php echo e($p['category_name'] ?? 'No franchise'); ?> - #<?php echo (int) $p['product_id']; ?></p>
                    <div class="admin-product-row">
                        <span class="admin-price"><?php echo formatPrice($p['price']); ?></span>
                        <span class="stock-pill <?php echo $stockClass; ?>"><?php echo $stock; ?> in stock</span>
                    </div>
                </div>
            </div>
            <div class="admin-product-actions">
                <a class="btn btn-outline" href="<?php echo SITE_URL; ?>/admin/product_form.php?id=<?php echo (int) $p['product_id']; ?>">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><path d="M4 20l1-4L16.5 4.5a2 2 0 0 1 2.8 0l.2.2a2 2 0 0 1 0 2.8L8 19z"/></svg>
                    Edit<span class="sr-only"> <?php echo e($p['name']); ?></span>
                </a>
                <a class="btn btn-outline btn-strong" href="<?php echo SITE_URL; ?>/admin/product_delete.php?id=<?php echo (int) $p['product_id']; ?>">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M9.5 7V4.5h5V7M6.5 7l.8 12.5h9.4L17.5 7M10 11v5.5M14 11v5.5"/></svg>
                    Delete<span class="sr-only"> <?php echo e($p['name']); ?></span>
                </a>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$products): ?>
        <p class="admin-empty"><?php echo $qs ? 'No products match your search.' : 'No products yet. Add your first one above.'; ?></p>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
    <nav class="pager admin-pager" aria-label="Pagination">
        <p class="pager-count"><?php echo ($page - 1) * ADMIN_PER_PAGE + 1; ?>-<?php echo ($page - 1) * ADMIN_PER_PAGE + count($products); ?> out of <?php echo $total; ?> products</p>
        <div class="pager-links">
            <?php if ($page > 1): ?><a class="pager-btn" href="<?php echo e($pageUrl($page - 1)); ?>">&lsaquo; prev</a><?php else: ?><span class="pager-btn is-disabled">&lsaquo; prev</span><?php endif; ?>
            <?php if ($page < $totalPages): ?><a class="pager-btn" href="<?php echo e($pageUrl($page + 1)); ?>">next &rsaquo;</a><?php else: ?><span class="pager-btn is-disabled">next &rsaquo;</span><?php endif; ?>
        </div>
    </nav>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
