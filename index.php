<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Review.php';

$productModel = new Product();
$collections  = $productModel->getCollectionTiles();
$popCount     = $productModel->countAll();
$reviewCount  = (new Review())->countAll();

// Card B1 rule 10: Figma team names, roles, quotes and portraits are real.
// Used when config/content.php still holds the generic placeholder rows.
$teamFallback = [
    ['name' => 'Abalos, Jamon Ray',       'role' => 'Database Developer',   'photo' => 'assets/img/team-abalos.png',   'quote' => '“Magic chess is my life.”'],
    ['name' => 'Caburnay, Rey Alexis',    'role' => 'Leader, Full Stack',   'photo' => 'assets/img/team-caburnay.png', 'quote' => '“Your next move matters more than your previous mistake.”'],
    ['name' => 'Estrada, Gian Ernest',    'role' => 'Backend Developer',    'photo' => 'assets/img/team-estrada.png',  'quote' => '“I was born to make mistakes, not to fake perfection.”'],
    ['name' => 'De Guzman, Chryswynne',   'role' => 'Frontend Developer',   'photo' => 'assets/img/team-deguzman.png', 'quote' => '“Larp it ‘til you make it.”'],
];
$team = siteContent('team', []);
if (!$team || (($team[0]['name'] ?? '') === 'Team member')) {
    $team = $teamFallback;
}

$pageTitle       = SITE_NAME . ' — Funko Pop collectibles';
$metaDescription = siteContent('hero_description');
$bodyClass       = 'page-home';
$pageStyles      = ['css/home.css'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero (Figma Mobile/Public, 390x2826 frame) -->
<section class="hero">
    <img class="hero-bg" src="<?php echo SITE_URL; ?>/assets/img/bg-hero.jpg" alt="" width="748" height="1408">
    <img class="hero-notch hero-notch-left" src="<?php echo SITE_URL; ?>/assets/img/hero-notch-left.svg" alt="" width="126" height="54">
    <img class="hero-notch hero-notch-right" src="<?php echo SITE_URL; ?>/assets/img/hero-notch-right.svg" alt="" width="125" height="54">
    <div class="hero-inner">
        <span class="hero-tab">THE</span>
        <h1 class="hero-title">POP CABIN</h1>
        <p class="hero-desc"><?php echo e(siteContent('hero_description')); ?></p>
        <div class="hero-stats">
            <div class="hero-stat"><strong><?php echo number_format($popCount); ?></strong><span>Funko Pops</span></div>
            <div class="hero-stat"><strong><?php echo number_format($reviewCount); ?></strong><span>Reviews</span></div>
        </div>
    </div>
    <a class="hero-shop" href="<?php echo SITE_URL; ?>/shop.php">Shop Collection</a>
</section>

<!-- Overview (Figma copy is Lorem ipsum; same length/style, real Pop Cabin words) -->
<section class="overview" aria-labelledby="overview-title">
    <h2 class="overview-title" id="overview-title">Pops with a story</h2>
    <p class="overview-sub">Curated for every collector</p>
    <p class="overview-body">Every pop is chosen by hand. We hunt the figures collectors actually want, from chase pieces to shelf staples. Find one that tells your story.</p>
    <a class="btn-team" href="#team">Meet the Devs</a>
    <img class="overview-art" src="<?php echo SITE_URL; ?>/assets/img/object-logo.png" alt="The Pop Cabin wooden sign with two characters" width="358" height="186" loading="lazy">
</section>

<!-- Discover Pops -->
<section class="discover" id="discover" aria-labelledby="discover-title">
    <h2 class="discover-title" id="discover-title">Discover Pops</h2>
    <p class="discover-sub">Featured Releases</p>
    <?php if ($collections): ?>
        <div class="discover-carousel">
            <button type="button" class="tile-nav tile-nav-prev" data-tile-nav="tile-row" data-dir="prev" aria-label="Show previous categories"><img src="<?php echo SITE_URL; ?>/assets/img/arrow-prev.svg" alt="" width="32" height="32"></button>
            <div class="tile-row" id="tile-row" role="region" aria-label="Categories" tabindex="0">
                <?php foreach ($collections as $t): ?>
                    <a class="tile" href="<?php echo SITE_URL; ?>/shop.php?collection=<?php echo urlencode($t['slug']); ?>">
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
            <button type="button" class="tile-nav tile-nav-next" data-tile-nav="tile-row" data-dir="next" aria-label="Show more categories"><img src="<?php echo SITE_URL; ?>/assets/img/arrow-next.svg" alt="" width="32" height="32"></button>
        </div>
    <?php endif; ?>
    <div class="center"><a class="btn-viewall" href="<?php echo SITE_URL; ?>/shop.php">View All</a></div>
</section>

<!-- Meet the Devs -->
<section class="team-band" id="team" aria-labelledby="team-title">
    <img class="team-bg" src="<?php echo SITE_URL; ?>/assets/img/bg-devs.jpg" alt="" width="1266" height="806" loading="lazy">
    <h2 class="team-title" id="team-title">Meet the Devs</h2>
    <div class="dev-grid">
        <?php foreach ($team as $member): ?>
            <article class="dev-card">
                <?php if (!empty($member['quote'])): ?><p class="dev-quote"><?php echo e($member['quote']); ?></p><?php endif; ?>
                <div class="dev-person">
                    <?php if (!empty($member['photo'])): ?>
                        <img class="dev-avatar" src="<?php echo e(imgSrc($member['photo'])); ?>" alt="<?php echo e($member['name']); ?>" width="44" height="44" loading="lazy">
                    <?php else: ?>
                        <span class="dev-avatar img-ph img-ph-person" role="img" aria-label="Photo to come"></span>
                    <?php endif; ?>
                    <div class="dev-id">
                        <p class="dev-name"><?php echo e($member['name']); ?></p>
                        <p class="dev-role"><?php echo e($member['role']); ?></p>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
