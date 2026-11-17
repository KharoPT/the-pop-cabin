<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Review.php';

$productModel = new Product();
$featured     = $productModel->getFeatured(6);
$collections  = $productModel->getCollectionTiles();
$franchises   = $productModel->getFranchiseTiles();
$popCount     = $productModel->countAll();
$reviews      = (new Review())->getLatest(6);   // the review section only appears when there are reviews
$reviewCount  = (new Review())->countAll();
$team         = siteContent('team', []);
$facebookUrl  = siteContent('facebook_url');

// Photo for the "ready to order" section: the newest featured pop that has an image.
$ctaImage = '';
foreach ($featured as $f) {
    if (!empty($f['image_url'])) { $ctaImage = imgSrc($f['image_url']); break; }
}

$pageTitle       = SITE_NAME . ' — Funko Pop collectibles';
$metaDescription = siteContent('hero_description');
$bodyClass       = 'page-home';
require_once __DIR__ . '/includes/header.php';

/** Draws a category/franchise tile row. */
function renderTileRow(array $tiles, string $param, string $label): void { ?>
    <div class="tile-row" role="region" aria-label="<?php echo e($label); ?>" tabindex="0">
        <?php foreach ($tiles as $t): ?>
            <a class="tile" href="<?php echo SITE_URL; ?>/shop.php?<?php echo e($param); ?>=<?php echo urlencode($t['slug']); ?>">
                <span class="tile-img">
                    <?php if (!empty($t['image_url'])): ?>
                        <img src="<?php echo e(imgSrc($t['image_url'])); ?>" alt="" loading="lazy">
                    <?php else: ?>
                        <span class="img-ph" aria-hidden="true"></span>
                    <?php endif; ?>
                </span>
                <span class="tile-name"><?php echo e($t['name']); ?></span>
            </a>
        <?php endforeach; ?>
    </div>
<?php } ?>

<!-- Hero -->
<section class="hero">
    <div class="hero-inner">
        <span class="hero-tab">The</span>
        <h1 class="hero-title">POP CABIN</h1>
        <p class="hero-desc"><?php echo e(siteContent('hero_description')); ?></p>
        <div class="hero-stats">
            <div class="hero-stat"><strong><?php echo number_format($popCount); ?></strong><span>Funko Pops</span></div>
            <div class="hero-stat"><strong><?php echo number_format($reviewCount); ?></strong><span>Reviews</span></div>
        </div>
    </div>
    <div class="hero-notch">
        <a class="btn-pill-teal" href="<?php echo SITE_URL; ?>/shop.php">Shop Collection</a>
    </div>
</section>

<?php if ($featured): ?>
<!-- Exclusive pops carousel (products marked "Show on home page") -->
<section class="section featured" aria-labelledby="featured-title">
    <div class="wrap">
        <h2 class="sign sign-left" id="featured-title">Exclusive Pops</h2>
        <div class="frame" data-carousel>
            <div class="carousel-track">
                <?php foreach ($featured as $f): ?>
                    <a class="slide" href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo urlencode($f['slug']); ?>">
                        <?php if (!empty($f['image_url'])): ?>
                            <img src="<?php echo e(imgSrc($f['image_url'])); ?>" alt="<?php echo e($f['name']); ?>" loading="lazy">
                        <?php else: ?>
                            <span class="img-ph" role="img" aria-label="<?php echo e($f['name']); ?>"></span>
                        <?php endif; ?>
                        <span class="slide-caption"><span><?php echo e($f['name']); ?></span><strong><?php echo formatPrice($f['price']); ?></strong></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="frame-tab"><div class="dots" data-dots></div></div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Discover pops -->
<section class="section discover" id="discover" aria-labelledby="discover-title">
    <h2 class="sign sign-center sign-light" id="discover-title">Discover Pops</h2>
    <p class="discover-sub">Featured Releases</p>
    <?php if ($collections) renderTileRow($collections, 'collection', 'Categories'); ?>
    <?php if ($collections && $franchises): ?><hr class="stitch"><?php endif; ?>
    <?php if ($franchises) renderTileRow($franchises, 'category', 'Franchises'); ?>
    <div class="center">
        <a class="btn-pill-honey" href="<?php echo SITE_URL; ?>/shop.php">View All</a>
    </div>
</section>

<?php if ($reviews): ?>
<!-- Optional review section: only shown once buyers have left reviews -->
<section class="reviews-band" aria-labelledby="reviews-title">
    <div class="wrap">
        <h2 class="sign-stack" id="reviews-title"><span>Words from</span><span>buyers</span></h2>
        <div data-carousel>
            <div class="carousel-track review-track">
                <?php foreach ($reviews as $r): ?>
                    <article class="review-slide">
                        <div class="review-card">
                            <p class="review-text"><?php echo e(strlen($r['comment']) > 240 ? rtrim(substr($r['comment'], 0, 240)) . '…' : $r['comment']); ?></p>
                            <?php if (!empty($r['product_name'])): ?>
                                <p class="review-about">on <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo urlencode($r['product_slug']); ?>"><?php echo e($r['product_name']); ?></a></p>
                            <?php endif; ?>
                            <footer class="review-by">
                                <span class="avatar"><?php echo $r['is_anonymous'] ? '?' : e(strtoupper(substr($r['display_name'], 0, 1))); ?></span>
                                <span class="review-who">
                                    <span class="review-name"><?php echo e($r['display_name']); ?></span>
                                    <?php echo renderStars((float) $r['rating']); ?>
                                    <span class="sr-only"><?php echo (int) $r['rating']; ?> out of 5 stars</span>
                                </span>
                            </footer>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="dots dots-lg" data-dots></div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Meet the team -->
<?php if ($team): ?>
<section class="section team" aria-labelledby="team-title">
    <div class="wrap">
        <h2 class="sign sign-center" id="team-title">Meet the Devs</h2>
        <div class="team-grid">
            <?php foreach ($team as $member): ?>
                <article class="team-card">
                    <div class="team-photo">
                        <?php if (!empty($member['photo'])): ?>
                            <img src="<?php echo e(imgSrc($member['photo'])); ?>" alt="<?php echo e($member['name']); ?>" loading="lazy">
                        <?php else: ?>
                            <span class="img-ph img-ph-person" role="img" aria-label="Photo to come"></span>
                        <?php endif; ?>
                    </div>
                    <div class="team-plate">
                        <p class="team-name"><?php echo e($member['name']); ?></p>
                        <p class="team-role"><?php echo e($member['role']); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if (siteContent('team_caption')): ?>
            <p class="team-caption"><?php echo e(siteContent('team_caption')); ?></p>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Ready to order -->
<section class="section cta" aria-labelledby="cta-title">
    <div class="wrap">
        <hr class="stitch">
        <h2 class="sign sign-left" id="cta-title">Ready to order your pop?</h2>
        <div class="cta-visual">
            <?php if ($ctaImage): ?>
                <img src="<?php echo e($ctaImage); ?>" alt="" loading="lazy">
            <?php else: ?>
                <span class="img-ph" aria-hidden="true"></span>
            <?php endif; ?>
            <div class="cta-panel">
                <p><?php echo e(siteContent('cta_text')); ?></p>
                <a class="btn btn-light" href="<?php echo SITE_URL; ?>/shop.php">Get yours now</a>
            </div>
        </div>
        <div class="cta-end">
            <span class="chip">Always open</span>
            <a class="btn btn-dark btn-wide" href="<?php echo SITE_URL; ?>/index.php">Ready</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
