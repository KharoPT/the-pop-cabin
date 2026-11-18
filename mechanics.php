<?php
require_once __DIR__ . '/config/config.php';

// Price add-ons used by the rules below, formatted with the site currency.
$addSteal = formatPrice(50);
$addClaim = formatPrice(100);
$addLock  = formatPrice(200);

$pageTitle       = 'System Mechanics — ' . SITE_NAME;
$metaDescription = 'How bidding works at ' . SITE_NAME . ': Dibs, Steal, Claim and Cabin Lock — the four moves, their prices, and the rules that end an auction.';
$bodyClass       = 'page-mechanics';
$pageStyles      = ['css/mechanics.css'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="wrap mechanics-hero">
    <h1 class="sign sign-center mechanics-title">System Mechanics</h1>
    <p class="mechanics-lede">Four moves decide who takes a Pop home. Each one has its own price, its own limit, and its own moment — learn them once and the whole auction makes sense.</p>
</section>

<section class="wrap mechanics-moves" aria-labelledby="moves-title">
    <div class="mechanics-head">
        <h2 class="sign sign-left" id="moves-title">The Four Moves</h2>
        <p class="mechanics-head-note">Dibs opens the bidding. Steal raises it.  </p>
        <p class="mechanics-head-note">Claim answers. Cabin Lock closes it.</p>
    </div>

    <div class="move-grid">
        <article class="move-card" aria-labelledby="move-dibs">
            <p class="move-flag">Opening move</p>
            <h3 class="move-name sign" id="move-dibs">Dibs</h3>
            <p class="move-tagline">Call it at the tag price.</p>
            <p class="move-price">
                <span class="move-price-label">Price</span>
                <span class="move-price-value">Posted original price</span>
            </p>
            <p class="move-copy">Dibs is where every auction begins. You take the Pop at the price it was posted for — no markup, no extras — and the four-day clock starts running.</p>
        </article>

        <article class="move-card" aria-labelledby="move-steal">
            <p class="move-flag">One use only</p>
            <h3 class="move-name sign" id="move-steal">Steal</h3>
            <p class="move-tagline">Take it with a small raise.</p>
            <p class="move-price">
                <span class="move-price-label">Price</span>
                <span class="move-price-value">Posted price + <?php echo e($addSteal); ?></span>
            </p>
            <p class="move-copy">A Steal is the first raise. You pay <?php echo e($addSteal); ?> over the posted price and take the Pop at that higher bid. It is a single-use move — after one Steal, it is spent.</p>
        </article>

        <article class="move-card" aria-labelledby="move-claim">
            <p class="move-flag">Multi-use · needs a Steal</p>
            <h3 class="move-name sign" id="move-claim">Claim</h3>
            <p class="move-tagline">Answer a Steal for more.</p>
            <p class="move-price">
                <span class="move-price-label">Price</span>
                <span class="move-price-value">Steal price + <?php echo e($addClaim); ?></span>
            </p>
            <p class="move-copy">A Claim only opens after a Steal has been made. It answers that Steal for <?php echo e($addClaim); ?> more, and it can be used again and again until the bidding ends.</p>
        </article>

        <article class="move-card" aria-labelledby="move-lock">
            <p class="move-flag">One use only · locks the auction</p>
            <h3 class="move-name sign" id="move-lock">Cabin Lock</h3>
            <p class="move-tagline">Close the auction for good.</p>
            <p class="move-price">
                <span class="move-price-label">Price</span>
                <span class="move-price-value">Current price + <?php echo e($addLock); ?></span>
            </p>
            <p class="move-copy">A Cabin Lock ends the auction on the spot. You pay <?php echo e($addLock); ?> over whatever the bidding has reached, and the Pop is locked to you. It can be used only once.</p>
            <ul class="move-ledger">
                <li><span>Posted original</span><span>+ <?php echo e($addLock); ?></span></li>
                <li><span>Steal price</span><span>+ <?php echo e($addLock); ?></span></li>
                <li><span>Claim price</span><span>+ <?php echo e($addLock); ?></span></li>
            </ul>
        </article>
    </div>

    <p class="mechanics-currency">All prices are in Philippine pesos.</p>
</section>

<section class="wrap mechanics-clock" aria-labelledby="clock-title">
    <div class="clock-band">
        <p class="clock-eyebrow">The timer</p>
        <h2 class="clock-title" id="clock-title">Four days, then the Dibs is done.</h2>
        <p class="clock-figure" aria-hidden="true"><span class="clock-number">4</span><span class="clock-unit">days</span></p>
        <p class="clock-copy">If no Steal, no Claim, and no Cabin Lock is made, the Dibs ends four days after it is posted.</p>
    </div>
</section>

<section class="wrap mechanics-account" aria-labelledby="account-title">
    <div class="account-panel">
        <div class="account-head">
            <span class="account-badge" aria-hidden="true">1</span>
            <h2 class="account-title" id="account-title">One move per account</h2>
        </div>
        <p class="account-copy">One registered account can make only one of these moves — Dibs, Steal, Claim, or Cabin Lock — and only once.</p>
        <p class="account-note">So the question is never how many moves to make. It is which one, and when.</p>
    </div>
</section>

<section class="wrap mechanics-end">
    <h2 class="sign sign-center">Ready to play?</h2>
    <p class="mechanics-end-note">Find the Pop you want, then choose your move.</p>
    <a class="btn btn-dark btn-wide" href="<?php echo SITE_URL; ?>/shop.php">Browse the Collection</a>
    <p class="mechanics-fineprint">Something still unclear? <a href="<?php echo SITE_URL; ?>/contact.php">Contact us</a> and we will walk you through it.</p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
