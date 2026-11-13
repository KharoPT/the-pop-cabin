<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Product.php';

$productModel = new Product();
$featured     = $productModel->getFeatured(4);
$newArrivals  = $productModel->getAll(['sort' => 'newest'], 8, 0);
$categories   = getAllCategories();

$pageTitle = SITE_NAME . ' — Authenticated Rare Collectibles';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero-inner">
        <h1>Own a piece of history.</h1>
        <p class="hero-sub">The Pop Cabin is a marketplace for authenticated funko pops - every listing checked for condition and provenance before it reaches you.</p>
        <div class="hero-actions">
            <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn-primary">Browse the Vault</a>
            <a href="#featured" class="btn btn-ghost">See Featured Finds</a>
        </div>
    </div>
</section>

<section class="trust-strip">
    <div class="container trust-grid">
        <div class="trust-item">
            <strong>Authenticity Checked</strong>
            <span>Every rarity verified before it's listed</span>
        </div>
        <div class="trust-item">
            <strong>Insured Shipping</strong>
            <span>Packed and protected for the journey</span>
        </div>
        <div class="trust-item">
            <strong>Collector Support</strong>
            <span>Real people who know the hobby</span>
        </div>
    </div>
</section>

<section id="featured" class="section">
    <div class="container">
        <div class="section-heading">
            <h2>Featured Finds</h2>
            <a href="<?php echo SITE_URL; ?>/shop.php" class="section-link">View all</a>
        </div>
        <?php if (empty($featured)): ?>
            <p class="empty-note">No featured items yet — check back soon.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($featured as $product): ?>
                    <?php include __DIR__ . '/includes/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-muted">
    <div class="container">
        <div class="section-heading"><h2>Shop by Category</h2></div>
        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
                <a class="category-card" href="<?php echo SITE_URL; ?>/shop.php?category=<?php echo e($cat['slug']); ?>">
                    <span class="category-name"><?php echo e($cat['category_name']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-heading">
            <h2>New Arrivals</h2>
            <a href="<?php echo SITE_URL; ?>/shop.php?sort=newest" class="section-link">View all</a>
        </div>
        <div class="product-grid">
            <?php foreach (array_slice($newArrivals, 0, 8) as $product): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
