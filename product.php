<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Product.php';

$productModel = new Product();
$product = $productModel->getBySlug($_GET['slug'] ?? '');

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Item Not Found — ' . SITE_NAME;
    require_once __DIR__ . '/includes/header.php';
    ?>
    <section class="section container">
        <h1>Item not found</h1>
        <p>That collectible may have sold or the link is out of date.</p>
        <a class="btn btn-primary" href="<?php echo SITE_URL; ?>/shop.php">Back to Shop</a>
    </section>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$related = $productModel->getRelated((int) $product['category_id'], (int) $product['product_id'], 4);

$pageTitle = $product['name'] . ' — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';

$imgSrc = $product['image_url'] ?: 'https://placehold.co/700x700/1e1b24/c8a355?text=' . urlencode($product['name']);
?>

<section class="section">
    <div class="container">
        <nav class="breadcrumbs">
            <a href="<?php echo SITE_URL; ?>/index.php">Home</a> /
            <a href="<?php echo SITE_URL; ?>/shop.php">Shop</a>
            <?php if (!empty($product['category_name'])): ?>
                / <a href="<?php echo SITE_URL; ?>/shop.php?category=<?php echo e($product['category_slug']); ?>"><?php echo e($product['category_name']); ?></a>
            <?php endif; ?>
            / <span><?php echo e($product['name']); ?></span>
        </nav>

        <div class="product-detail">
            <div class="product-detail-image">
                <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e($product['name']); ?>">
                <?php if (!empty($product['is_authenticated'])): ?>
                    <span class="authenticity-badge">Authenticity Verified</span>
                <?php endif; ?>
            </div>

            <div class="product-detail-info">
                <h1><?php echo e($product['name']); ?></h1>

                <?php if (!empty($product['condition_grade'])): ?>
                    <div class="product-tags">
                        <span class="tag">Condition: <?php echo e($product['condition_grade']); ?></span>
                    </div>
                <?php endif; ?>

                <p class="product-price-lg"><?php echo formatPrice($product['price']); ?></p>

                <?php if ((int) $product['stock_quantity'] > 0): ?>
                    <p class="stock-note stock-in"><?php echo (int) $product['stock_quantity']; ?> in stock</p>
                    <form class="add-to-cart-form" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
                        <input type="hidden" name="product_id" value="<?php echo (int) $product['product_id']; ?>">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo (int) $product['stock_quantity']; ?>">
                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <p class="stock-note stock-out">Sold out</p>
                    <button class="btn btn-disabled btn-block" disabled>Currently Unavailable</button>
                <?php endif; ?>

                <ul class="trust-list">
                    <li>Insured, tracked shipping on every order</li>
                    <li>Item condition graded by our specialists</li>
                    <li>Secure checkout, no account sharing</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($related)): ?>
<section class="section section-muted">
    <div class="container">
        <div class="section-heading"><h2>You Might Also Like</h2></div>
        <div class="product-grid">
            <?php foreach ($related as $product): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
