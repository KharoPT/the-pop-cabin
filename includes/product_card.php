<?php

$badge  = !empty($product['is_featured']) ? 'Featured' : null;
$imgSrc = $product['image_url'] ?: 'https://placehold.co/600x600/1e1b24/c8a355?text=' . urlencode($product['name']);
?>
<article class="product-card">
    <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo e($product['slug']); ?>" class="product-card-image">
        <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e($product['name']); ?>" loading="lazy">
        <?php if ((int) $product['stock_quantity'] <= 0): ?>
            <span class="product-badge product-badge-sold">Sold Out</span>
        <?php elseif ($badge): ?>
            <span class="product-badge"><?php echo e($badge); ?></span>
        <?php endif; ?>
    </a>
    <div class="product-card-body">
        <h3 class="product-name">
            <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo e($product['slug']); ?>"><?php echo e($product['name']); ?></a>
        </h3>
        <div class="product-card-footer">
            <span class="product-price"><?php echo formatPrice($product['price']); ?></span>
            <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo e($product['slug']); ?>" class="btn btn-small">View</a>
        </div>
    </div>
</article>
