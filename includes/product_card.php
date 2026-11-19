<?php
/**
 * One pop card (shop grid, "you may also like", ...). Expects $card = a product row
 * that includes category_name.
 */
$cardUrl   = SITE_URL . '/product.php?slug=' . urlencode($card['slug']);
$cardImg   = imgSrc($card['image_url'] ?? '');
$cardSold  = (int) $card['stock_quantity'] <= 0;
$cardHasStock  = !empty($cardShowStock);
$cardHasRating = !empty($cardShowRating) && (int) ($card['review_count'] ?? 0) > 0;
?>
<article class="pop-card" data-product-id="<?php echo (int) $card['product_id']; ?>">
    <div class="pop-card-media">
        <a href="<?php echo e($cardUrl); ?>" class="pop-card-link" tabindex="-1" aria-hidden="true">
            <?php if ($cardImg): ?>
                <img src="<?php echo e($cardImg); ?>" alt="" loading="lazy">
            <?php else: ?>
                <span class="img-ph" role="img" aria-label="No photo yet"></span>
            <?php endif; ?>
        </a>
        <button type="button" class="fav-btn" data-fav="<?php echo (int) $card['product_id']; ?>" aria-pressed="false"
                aria-label="Add <?php echo e($card['name']); ?> to favorites">
            <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.7 4.5 6.3 4.5c2 0 3.5 1.1 4.2 2.4h3c.7-1.3 2.2-2.4 4.2-2.4 3.6 0 5.4 3.9 3.9 7.3C19.5 16.4 12 21 12 21z" transform="translate(0 .2)"/></svg>
        </button>
        <?php if ($cardSold): ?><span class="pop-card-ribbon">Out of stock</span><?php endif; ?>
    </div>
    <div class="pop-card-body">
        <h3 class="pop-name"><a href="<?php echo e($cardUrl); ?>"><?php echo e($card['name']); ?></a></h3>
        <?php if (!empty($card['category_name'])): ?><p class="pop-cat"><?php echo e($card['category_name']); ?></p><?php endif; ?>
        <?php if ($cardHasStock || $cardHasRating): ?>
            <p class="pop-stock">
                <?php if ($cardHasStock): ?>Stock: <?php echo (int) $card['stock_quantity']; ?><?php endif; ?>
                <?php if ($cardHasRating): ?>
                    <?php echo $cardHasStock ? ' &middot; ' : ''; ?>
                    <span class="pop-rating" aria-label="Rated <?php echo e(number_format((float) ($card['average_rating'] ?? 0), 1)); ?> out of 5 from <?php echo (int) $card['review_count']; ?> <?php echo (int) $card['review_count'] === 1 ? 'review' : 'reviews'; ?>">
                        <span aria-hidden="true">&#9733;</span> <?php echo e(number_format((float) ($card['average_rating'] ?? 0), 1)); ?>
                        <span class="pop-rating-count">(<?php echo (int) $card['review_count']; ?>)</span>
                    </span>
                <?php endif; ?>
            </p>
        <?php endif; ?>
        <p class="pop-price"><?php echo formatPrice($card['price']); ?></p>
    </div>
</article>
