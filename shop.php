<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Product.php';

$productModel = new Product();

$filters = [
    'category'  => $_GET['category'] ?? '',
    'search'    => sanitizeInput($_GET['search'] ?? ''),
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'condition' => $_GET['condition'] ?? '',
    'sort'      => $_GET['sort'] ?? 'newest',
];

$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = PRODUCTS_PER_PAGE;
$offset  = ($page - 1) * $perPage;

$products      = $productModel->getAll($filters, $perPage, $offset);
$totalProducts = $productModel->countAll($filters);
$totalPages    = max(1, (int) ceil($totalProducts / $perPage));
$categories    = getAllCategories();

$pageTitle = 'Shop All Collectibles — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Shop the Collection</h1>
        <p><?php echo (int) $totalProducts; ?> authenticated pieces and counting.</p>
    </div>
</section>

<section class="section">
    <div class="container shop-grid">
        <aside class="shop-filters">
            <form method="get" action="<?php echo SITE_URL; ?>/shop.php">
                <?php if (!empty($filters['search'])): ?>
                    <input type="hidden" name="search" value="<?php echo e($filters['search']); ?>">
                <?php endif; ?>

                <div class="filter-group">
                    <h4>Category</h4>
                    <label class="filter-option">
                        <input type="radio" name="category" value="" <?php echo $filters['category'] === '' ? 'checked' : ''; ?> onchange="this.form.submit()">
                        All categories
                    </label>
                    <?php foreach ($categories as $cat): ?>
                        <label class="filter-option">
                            <input type="radio" name="category" value="<?php echo e($cat['slug']); ?>" <?php echo $filters['category'] === $cat['slug'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <?php echo e($cat['category_name']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="filter-group">
                    <h4>Price</h4>
                    <div class="price-inputs">
                        <input type="number" name="min_price" placeholder="Min" min="0" value="<?php echo e($filters['min_price']); ?>">
                        <span>&ndash;</span>
                        <input type="number" name="max_price" placeholder="Max" min="0" value="<?php echo e($filters['max_price']); ?>">
                    </div>
                </div>

                <div class="filter-group">
                    <h4>Condition</h4>
                    <select name="condition" onchange="this.form.submit()">
                        <option value="">Any condition</option>
                        <?php foreach (conditionOptions() as $c): ?>
                            <option value="<?php echo e($c); ?>" <?php echo $filters['condition'] === $c ? 'selected' : ''; ?>><?php echo e($c); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn-ghost btn-block">Clear All</a>
            </form>
        </aside>

        <div class="shop-results">
            <div class="shop-toolbar">
                <?php if (!empty($filters['search'])): ?>
                    <p>Results for &ldquo;<?php echo e($filters['search']); ?>&rdquo;</p>
                <?php else: ?>
                    <p><?php echo (int) $totalProducts; ?> items</p>
                <?php endif; ?>
                <form method="get" action="<?php echo SITE_URL; ?>/shop.php" class="sort-form">
                    <?php foreach (['category', 'search', 'min_price', 'max_price', 'condition'] as $key): ?>
                        <?php if (!empty($filters[$key])): ?>
                            <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($filters[$key]); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <label for="sort">Sort</label>
                    <select name="sort" id="sort" onchange="this.form.submit()">
                        <option value="newest" <?php echo $filters['sort'] === 'newest' ? 'selected' : ''; ?>>Newest</option>
                        <option value="price_asc" <?php echo $filters['sort'] === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php echo $filters['sort'] === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name" <?php echo $filters['sort'] === 'name' ? 'selected' : ''; ?>>Name A&ndash;Z</option>
                    </select>
                </form>
            </div>

            <?php if (empty($products)): ?>
                <p class="empty-note">No collectibles match these filters yet. Try widening your search.</p>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <?php include __DIR__ . '/includes/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="pagination">
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <?php $query = array_filter(array_merge($filters, ['page' => $p])); ?>
                            <a href="<?php echo SITE_URL . '/shop.php?' . e(http_build_query($query)); ?>" class="page-link <?php echo $p === $page ? 'active' : ''; ?>"><?php echo $p; ?></a>
                        <?php endfor; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
