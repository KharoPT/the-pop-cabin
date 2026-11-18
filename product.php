<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Review.php';

$productModel = new Product();
$slug    = is_string($_GET['slug'] ?? null) ? $_GET['slug'] : '';
$product = $slug !== '' ? $productModel->getBySlug($slug) : false;

$pageStyles = ['css/product.css'];

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Pop not found — ' . SITE_NAME;
    $bodyClass = 'page-product';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="wrap product-missing">
        <h1 class="product-missing-title">Pop not found</h1>
        <p class="product-missing-text">That pop is not in the collection (it may have been removed).</p>
        <a class="back-link" href="<?php echo SITE_URL; ?>/shop.php">Back to Collection</a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$productId   = (int) $product['product_id'];
$reviewModel = new Review();
$showAll     = ($_GET['reviews'] ?? '') === 'all';
$summary     = $reviewModel->summary($productId);
$reviews     = $reviewModel->getByProduct($productId, $showAll ? 50 : 3);
$related     = $productModel->getRelated($product['category_id'] ? (int) $product['category_id'] : null, $productId, 4);
$inStock     = (int) $product['stock_quantity'] > 0;
$image       = imgSrc($product['image_url'] ?? '');
$productUrl  = 'product.php?slug=' . urlencode($product['slug']);

$canReview    = isLoggedIn() && !$reviewModel->userHasReviewed($productId, (int) currentUserId());
$hasReviewed  = isLoggedIn() && !$canReview;
$reviewOpen   = ($_GET['review'] ?? '') === 'error';

$pageTitle       = $product['name'] . ' — ' . SITE_NAME;
$metaDescription = trim((string) $product['description']) !== '' ? mb_strimwidth_safe(preg_replace('/\s+/', ' ', $product['description']), 155) : $product['name'] . ' at ' . SITE_NAME;
$bodyClass       = 'page-product';

// Padlock shown on each mechanic chip (from the FigCraft frame).
$lockIcon = '<svg class="lock" viewBox="0 0 10 12" width="10" height="12" fill="currentColor" aria-hidden="true"><path d="M1.25 12L8.75 12C9.438 12 10 11.46 10 10.8L10 5.4C10 4.74 9.438 4.2 8.75 4.2L8.125 4.2L8.125 3C8.125 1.344 6.725 0 5 0C3.275 0 1.875 1.344 1.875 3L1.875 4.2L1.25 4.2C0.562 4.2 0 4.74 0 5.4L0 10.8C0 11.46 0.562 12 1.25 12ZM5.625 9.432L5.625 10.8L4.375 10.8L4.375 9.432C4.186 9.327 4.029 9.177 3.919 8.996C3.81 8.815 3.751 8.609 3.75 8.4C3.75 7.74 4.312 7.2 5 7.2C5.688 7.2 6.25 7.74 6.25 8.4C6.249 8.609 6.19 8.815 6.081 8.996C5.971 9.177 5.814 9.327 5.625 9.432ZM3.125 3C3.125 2.01 3.969 1.2 5 1.2C6.031 1.2 6.875 2.01 6.875 3L6.875 4.2L3.125 4.2L3.125 3Z"/></svg>';

require_once __DIR__ . '/includes/header.php';
?>

    <img class="product-bg" src="<?php echo assetUrl('img/product-bg.webp'); ?>" width="716" height="304" alt="">

    <div class="wrap product-hero">
        <div class="product-media">
            <div class="product-photo">
                <?php if ($image): ?>
                    <img src="<?php echo e($image); ?>" alt="<?php echo e($product['name']); ?>" id="product-img">
                <?php else: ?>
                    <span class="img-ph" role="img" aria-label="No photo yet"></span>
                <?php endif; ?>

                <button type="button" class="media-btn media-fav" data-fav="<?php echo $productId; ?>" aria-pressed="false" aria-label="Add <?php echo e($product['name']); ?> to favorites">
                    <svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.7 4.5 6.3 4.5c2 0 3.5 1.1 4.2 2.4h3c.7-1.3 2.2-2.4 4.2-2.4 3.6 0 5.4 3.9 3.9 7.3C19.5 16.4 12 21 12 21z" transform="translate(0 .2)"/></svg>
                </button>

                <?php if ($image): ?>
                    <button type="button" class="media-btn media-zoom" id="zoom-btn" aria-haspopup="dialog" aria-label="Zoom the photo">
                        <svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.6"/><path d="M10.5 7.6v5.8M7.6 10.5h5.8M15.4 15.4 20.6 20.6"/></svg>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="product-info">
            <div class="product-overview">
                <div class="product-heading">
                    <h1 class="product-title"><?php echo e($product['name']); ?></h1>
                    <?php if (!empty($product['category_name'])): ?>
                        <a class="product-cat" href="<?php echo SITE_URL; ?>/shop.php?category=<?php echo urlencode($product['category_slug']); ?>"><?php echo e($product['category_name']); ?></a>
                    <?php endif; ?>
                </div>

                <div class="product-meta">
                    <div class="product-prices">
                        <p class="product-stock">Stock: <?php echo (int) $product['stock_quantity']; ?></p>
                        <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                    </div>
                    <p class="product-rating">
                        <?php echo renderStars((float) $summary['average']); ?>
                        <?php if ($summary['count'] > 0): ?>
                            <span class="rating-count"><?php echo number_format($summary['average'], 1); ?> out of 5 (<?php echo $summary['count']; ?> <?php echo $summary['count'] === 1 ? 'review' : 'reviews'; ?>)</span>
                        <?php else: ?>
                            <span class="rating-count">No reviews yet</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <form class="product-buy add-to-cart-form" method="post" action="<?php echo SITE_URL; ?>/actions/add_to_cart.php">
                <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                <input type="hidden" name="quantity" value="1">

                <div class="mech-tabs" role="group" aria-label="How to get this pop">
                    <button type="button" class="mech-tab" aria-pressed="false" data-mech="Dibs!" title="Dibs is not available yet"><?php echo $lockIcon; ?>Dibs</button>
                    <button type="button" class="mech-tab" aria-pressed="false" data-mech="Steal!" title="Steal is not available yet"><?php echo $lockIcon; ?>Steal</button>
                    <button type="button" class="mech-tab" aria-pressed="false" data-mech="Claim!" title="Claim is not available yet"><?php echo $lockIcon; ?>Claim</button>
                    <button type="button" class="mech-tab" aria-pressed="false" data-mech="Yours Na!" title="Cabin Lock is not available yet"><?php echo $lockIcon; ?>Cabin Lock</button>
                </div>

                <button type="submit" class="mech-action" data-in-stock="<?php echo $inStock ? '1' : '0'; ?>" data-default-label="Add to Cart" <?php echo $inStock ? '' : 'disabled'; ?>>Add to Cart</button>
                <?php if (!$inStock): ?><p class="mech-note">Out of stock right now.</p><?php endif; ?>

                <a class="mech-learn" href="<?php echo SITE_URL; ?>/mechanics.php">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9.5"/><path d="M9.6 9.4a2.5 2.5 0 1 1 3.4 2.3c-.7.3-1 .8-1 1.5M12 17h.01" stroke-linecap="round"/></svg>
                    Learn the mechanics
                </a>
            </form>
        </div>
    </div>

    <section class="wrap product-details" aria-labelledby="details-title">
        <h2 id="details-title">Details</h2>
        <?php if (trim((string) $product['description']) !== ''): ?>
            <div class="details-text"><?php echo nl2br(e($product['description'])); ?></div>
        <?php else: ?>
            <p class="details-text is-empty">No details have been added for this pop yet.</p>
        <?php endif; ?>
    </section>

    <section class="wrap product-reviews" id="reviews" aria-labelledby="reviews-title">
        <div class="reviews-head">
            <h2 id="reviews-title">Reviews &amp; Comments</h2>
            <?php if (!isLoggedIn()): ?>
                <a class="review-cta" href="<?php echo SITE_URL; ?>/login.php?next=<?php echo urlencode($productUrl . '#write-review'); ?>">Write a Review</a>
            <?php elseif ($canReview): ?>
                <a class="review-cta" href="#write-review">Write a Review</a>
            <?php else: ?>
                <span class="review-cta is-disabled">You reviewed this</span>
            <?php endif; ?>
        </div>

        <?php if ($canReview): ?>
            <form class="review-form <?php echo $reviewOpen ? 'is-open' : ''; ?>" id="write-review" method="post" action="<?php echo SITE_URL; ?>/actions/submit_review.php">
                <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                <fieldset class="rating-input">
                    <legend>Your rating</legend>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="rate-<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo $i === 5 ? 'required' : ''; ?>>
                        <label for="rate-<?php echo $i; ?>" title="<?php echo $i; ?> star<?php echo $i > 1 ? 's' : ''; ?>">
                            <svg class="star" viewBox="0 0 24 24" width="28" height="28"><path d="M12 2.6l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4l-5.9 3.2 1.2-6.5L2.5 9.5l6.6-.9z"/></svg>
                            <span class="sr-only"><?php echo $i; ?> star<?php echo $i > 1 ? 's' : ''; ?></span>
                        </label>
                    <?php endfor; ?>
                </fieldset>
                <div class="form-row">
                    <label for="review-comment">Your comment</label>
                    <textarea id="review-comment" name="comment" rows="4" maxlength="1000" required placeholder="Tell other collectors what you think..."></textarea>
                </div>
                <label class="check-line"><input type="checkbox" name="anonymous" value="1"> Post as anonymous</label>
                <button type="submit" class="btn btn-dark">Post review</button>
            </form>
        <?php endif; ?>

        <?php if ($reviews): ?>
            <div class="review-list">
                <?php foreach ($reviews as $r): ?>
                    <article class="review-item">
                        <span class="avatar"><?php echo $r['is_anonymous'] ? '?' : e(strtoupper(substr($r['display_name'], 0, 1))); ?></span>
                        <div class="review-meta">
                            <p class="review-name"><?php echo e($r['display_name']); ?></p>
                            <p class="review-date"><?php echo e(shortDate($r['created_at'], true)); ?></p>
                        </div>
                        <div class="review-stars"><?php echo renderStars((float) $r['rating']); ?><span class="sr-only"><?php echo (int) $r['rating']; ?> out of 5 stars</span></div>
                        <p class="review-body"><?php echo nl2br(e($r['comment'])); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if ($summary['count'] > 3): ?>
                <p class="see-more">
                    <?php if ($showAll): ?>
                        <a href="<?php echo SITE_URL . '/' . e($productUrl); ?>#reviews">Show fewer</a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL . '/' . e($productUrl); ?>&amp;reviews=all#reviews">See all <?php echo $summary['count']; ?> reviews</a>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        <?php else: ?>
            <p class="empty-note">No reviews yet. Be the first to share what you think.</p>
        <?php endif; ?>
    </section>

    <?php if ($related): ?>
    <section class="wrap product-related" aria-labelledby="related-title">
        <h2 id="related-title">You may also like</h2>
        <div class="pop-grid">
            <?php foreach ($related as $card): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <div class="wrap back-row<?php echo $related ? ' is-tight' : ''; ?>">
        <a class="back-link" href="<?php echo SITE_URL; ?>/shop.php">Back to Collection</a>
    </div>

<?php if ($image): ?>
<dialog class="lightbox" id="lightbox" aria-label="<?php echo e($product['name']); ?> enlarged">
    <button type="button" class="lightbox-close" id="lightbox-close" aria-label="Close">&times;</button>
    <img src="<?php echo e($image); ?>" alt="<?php echo e($product['name']); ?>">
</dialog>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
