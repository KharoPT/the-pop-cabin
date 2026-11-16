<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Product.php';

$productModel = new Product();

// Read query-string values as plain strings only (array values like ?search[]=x are ignored).
$param = fn(string $key): string => is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';

$filters = [
    'category'   => $param('category'),
    'collection' => $param('collection'),
    'search'     => sanitizeInput($param('search')),
    'min_price'  => $param('min_price'),
    'max_price'  => $param('max_price'),
    'sort'       => $param('sort'),
];
$activeFilters = array_filter($filters, fn($v, $k) => $v !== '' && $k !== 'sort', ARRAY_FILTER_USE_BOTH);
$filtersOpen   = (bool) array_diff_key($activeFilters, ['search' => 1]) || $filters['sort'] !== '';

$total      = $productModel->countAll($filters);
$perPage    = PRODUCTS_PER_PAGE;
$totalPages = max(1, (int) ceil($total / $perPage));
$page       = min($totalPages, max(1, (int) $param('page')));
$products   = $productModel->getAll($filters, $perPage, ($page - 1) * $perPage);
$from       = $total ? ($page - 1) * $perPage + 1 : 0;
$to         = $total ? $from + count($products) - 1 : 0;

$franchises  = getAllCategories();
$collections = $productModel->getCollections();

$queryBase = $activeFilters + ($filters['sort'] !== '' ? ['sort' => $filters['sort']] : []);
$pageUrl   = fn(int $p): string => SITE_URL . '/shop.php?' . http_build_query($queryBase + ['page' => $p]);

$pageTitle = 'Shop Collections — ' . SITE_NAME;
$bodyClass = 'page-shop';
require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap shop-top">
    <div class="shop-bar">
        <details class="filter-panel" <?php echo $filtersOpen ? 'open' : ''; ?>>
            <summary class="sign filter-summary">
                <span class="filter-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/></svg>
                </span>
                <span>Filter and sort</span>
            </summary>
            <form class="filter-form" method="get" action="<?php echo SITE_URL; ?>/shop.php">
                <?php if ($filters['search'] !== ''): ?>
                    <input type="hidden" name="search" value="<?php echo e($filters['search']); ?>">
                <?php endif; ?>
                <div class="form-row">
                    <label for="f-category">Franchise</label>
                    <select id="f-category" name="category">
                        <option value="">All franchises</option>
                        <?php foreach ($franchises as $c): ?>
                            <option value="<?php echo e($c['slug']); ?>" <?php echo $filters['category'] === $c['slug'] ? 'selected' : ''; ?>><?php echo e($c['category_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <label for="f-collection">Category</label>
                    <select id="f-collection" name="collection">
                        <option value="">All categories</option>
                        <?php foreach ($collections as $c): ?>
                            <option value="<?php echo e($c['slug']); ?>" <?php echo $filters['collection'] === $c['slug'] ? 'selected' : ''; ?>><?php echo e($c['collection_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row form-row-split">
                    <div>
                        <label for="f-min">Min price (<?php echo e(SITE_CURRENCY_SYMBOL); ?>)</label>
                        <input type="number" id="f-min" name="min_price" min="0" step="any" value="<?php echo e($filters['min_price']); ?>">
                    </div>
                    <div>
                        <label for="f-max">Max price (<?php echo e(SITE_CURRENCY_SYMBOL); ?>)</label>
                        <input type="number" id="f-max" name="max_price" min="0" step="any" value="<?php echo e($filters['max_price']); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <label for="f-sort">Sort by</label>
                    <select id="f-sort" name="sort">
                        <?php foreach (['' => 'Newest', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'name' => 'Name: A to Z'] as $val => $label): ?>
                            <option value="<?php echo e($val); ?>" <?php echo $filters['sort'] === $val ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-dark">Apply</button>
                    <a class="btn btn-outline" href="<?php echo SITE_URL; ?>/shop.php<?php echo $filters['search'] !== '' ? '?search=' . urlencode($filters['search']) : ''; ?>">Clear</a>
                </div>
            </form>
        </details>
        <p class="sign shop-total"><span class="shop-total-n"><?php echo number_format($total); ?></span> <?php echo $total === 1 ? 'pop' : 'pops'; ?></p>
    </div>
    <hr class="stitch">
</div>

<section class="wrap shop-results" aria-label="Pops">
    <?php if ($products): ?>
        <div class="pop-grid">
            <?php foreach ($products as $card): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>

        <nav class="pager" aria-label="Pagination">
            <p class="pager-count"><?php echo $from; ?>-<?php echo $to; ?> out of <?php echo number_format($total); ?> pops<?php echo $filters['search'] !== '' ? ' for &ldquo;' . e($filters['search']) . '&rdquo;' : ''; ?></p>
            <?php if ($totalPages > 1): ?>
                <div class="pager-links">
                    <?php if ($page > 1): ?>
                        <a class="pager-btn" href="<?php echo e($pageUrl($page - 1)); ?>" rel="prev"><span aria-hidden="true">&lsaquo;</span> prev</a>
                    <?php else: ?>
                        <span class="pager-btn is-disabled" aria-disabled="true"><span aria-hidden="true">&lsaquo;</span> prev</span>
                    <?php endif; ?>

                    <span class="pager-pages">
                        <?php foreach (pageWindow($page, $totalPages) as $p): ?>
                            <?php if ($p === null): ?>
                                <span class="pager-gap" aria-hidden="true">&hellip;</span>
                            <?php elseif ($p === $page): ?>
                                <span class="pager-num is-current" aria-current="page"><?php echo $p; ?></span>
                            <?php else: ?>
                                <a class="pager-num" href="<?php echo e($pageUrl($p)); ?>"><?php echo $p; ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </span>

                    <?php if ($page < $totalPages): ?>
                        <a class="pager-btn" href="<?php echo e($pageUrl($page + 1)); ?>" rel="next">next <span aria-hidden="true">&rsaquo;</span></a>
                    <?php else: ?>
                        <span class="pager-btn is-disabled" aria-disabled="true">next <span aria-hidden="true">&rsaquo;</span></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </nav>
    <?php else: ?>
        <div class="empty-state">
            <p>No pops match your search or filters.</p>
            <a class="btn btn-dark" href="<?php echo SITE_URL; ?>/shop.php">See the whole collection</a>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
