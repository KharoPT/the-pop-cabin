<?php
require_once __DIR__ . '/config/config.php';

$images   = array_values(siteContent('about_images', []));
$text     = siteContent('about_text', []);
$mapEmbed = siteContent('map_embed_url');
$address  = siteContent('address_lines', []);

$pageTitle = 'About Us — ' . SITE_NAME;
$bodyClass = 'page-about';
require_once __DIR__ . '/includes/header.php';
?>

<section class="wrap about-hero">
    <h1 class="sign sign-center about-title">About Us</h1>
    <div class="about-gallery">
        <?php for ($i = 0; $i < 4; $i++): ?>
            <figure class="about-photo about-photo-<?php echo $i + 1; ?>">
                <?php if (!empty($images[$i])): ?>
                    <img src="<?php echo e(imgSrc($images[$i])); ?>" alt="" loading="lazy">
                <?php else: ?>
                    <span class="img-ph" aria-hidden="true"></span>
                <?php endif; ?>
            </figure>
        <?php endfor; ?>
    </div>
</section>

<section class="wrap about-text">
    <h2 class="sign sign-center"><?php echo e(siteContent('about_subtitle')); ?></h2>
    <div class="prose">
        <?php foreach ($text as $paragraph): ?><p><?php echo e($paragraph); ?></p><?php endforeach; ?>
    </div>
</section>

<section class="find-us" aria-labelledby="find-title">
    <div class="wrap">
        <div class="find-frame">
            <h2 class="find-tab" id="find-title">Where to Find Us</h2>
            <div class="find-map">
                <?php if ($mapEmbed): ?>
                    <iframe src="<?php echo e($mapEmbed); ?>" title="Map to <?php echo e(SITE_NAME); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                <?php else: ?>
                    <span class="img-ph img-ph-map" role="img" aria-label="Map to come"></span>
                    <span class="map-pin" aria-hidden="true"></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<div class="wrap center about-end">
    <?php if ($address): ?>
        <div class="address-lines">
            <?php foreach ($address as $line): ?><p><?php echo e($line); ?></p><?php endforeach; ?>
        </div>
    <?php endif; ?>
    <a class="btn btn-dark btn-wide" href="<?php echo SITE_URL; ?>/shop.php">Back to the Store</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
