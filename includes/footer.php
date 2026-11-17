</main>

<?php
// Social links: an empty URL in config/content.php renders the icon without a link.
$socials = [
    'Facebook'  => ['facebook_url',  '<svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true"><circle cx="12" cy="12" r="11" fill="currentColor"/><path fill="#3b2a1f" d="M13.4 20v-6.9h2.3l.4-2.7h-2.7V8.7c0-.8.3-1.3 1.4-1.3h1.4V5c-.3 0-1.1-.1-2.1-.1-2.1 0-3.5 1.3-3.5 3.6v2h-2.3v2.7h2.3V20h2.8z"/></svg>'],
    'TikTok'    => ['tiktok_url',    '<svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true"><path fill="currentColor" d="M16.6 3c.3 2.3 1.7 3.8 3.9 4v2.9c-1.4.1-2.7-.3-3.9-1.1v6.2c0 3.1-2.5 5.5-5.6 5.5-3 0-5.4-2.4-5.4-5.4s2.6-5.7 6-5.3v3c-1.6-.5-3.1.7-3.1 2.3 0 1.4 1.1 2.5 2.5 2.5s2.6-1.1 2.6-2.6V3h3z"/></svg>'],
    'Instagram' => ['instagram_url', '<svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4.2"/><circle cx="17.3" cy="6.7" r="1.1" fill="currentColor" stroke="none"/></svg>'],
];
$facebookUrl = siteContent('facebook_url');
$tiktokUrl   = siteContent('tiktok_url');
?>
<footer class="site-footer">
    <div class="footer-notch" aria-hidden="true"></div>
    <div class="footer-inner">
        <div class="footer-brand">
            <a href="<?php echo SITE_URL; ?>/index.php" class="footer-logo" aria-label="<?php echo e(SITE_NAME); ?> home">
                <img src="<?php echo SITE_URL; ?>/assets/img/logo-mark.svg" alt="" width="32" height="32">
                <span class="footer-wordmark"><span>THE</span><strong>POP CABIN</strong></span>
            </a>
            <div class="socials">
                <?php foreach ($socials as $label => [$key, $icon]): ?>
                    <?php $url = siteContent($key); ?>
                    <a class="social-link <?php echo $url ? '' : 'is-pending'; ?>" aria-label="<?php echo e($label); ?>"
                       <?php if ($url): ?>href="<?php echo e($url); ?>" target="_blank" rel="noopener"<?php endif; ?>><?php echo $icon; ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="footer-cols">
            <nav class="footer-col" aria-labelledby="f-assist">
                <h2 id="f-assist">Assistance</h2>
                <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
                <a href="<?php echo SITE_URL; ?>/shop.php">Shop Collections</a>
                <a href="<?php echo SITE_URL; ?>/mechanics.php">System Mechanics</a>
            </nav>
            <nav class="footer-col" aria-labelledby="f-about">
                <h2 id="f-about">About Pop Cabin</h2>
                <a href="<?php echo SITE_URL; ?>/about.php">About us</a>
                <a href="<?php echo SITE_URL; ?>/terms.php">Terms of Service</a>
            </nav>
            <nav class="footer-col" aria-labelledby="f-connect">
                <h2 id="f-connect">Connect With Us</h2>
                <a href="<?php echo SITE_URL; ?>/contact.php">Contact Us</a>
                <a class="<?php echo $facebookUrl ? '' : 'is-pending'; ?>" <?php if ($facebookUrl): ?>href="<?php echo e($facebookUrl); ?>" target="_blank" rel="noopener"<?php endif; ?>>Visit Our Facebook Page</a>
                <a class="<?php echo $tiktokUrl ? '' : 'is-pending'; ?>" <?php if ($tiktokUrl): ?>href="<?php echo e($tiktokUrl); ?>" target="_blank" rel="noopener"<?php endif; ?>>Tiktok</a>
            </nav>
        </div>

        <div class="footer-legal">
            <p>&copy; <?php echo date('Y'); ?> <?php echo e(SITE_NAME); ?>. All rights reserved.</p>
            <?php if (siteContent('trademark_line')): ?>
                <p><?php echo e(siteContent('trademark_line')); ?></p>
            <?php endif; ?>
            <span class="ph-flag" title="Philippines">
                <svg viewBox="0 0 30 16" width="30" height="16" role="img" aria-label="Philippine flag">
                    <rect width="30" height="8" fill="#0038a8"/><rect y="8" width="30" height="8" fill="#ce1126"/>
                    <path d="M0 0L14 8 0 16z" fill="#fff"/>
                    <circle cx="4.2" cy="8" r="1.7" fill="#fcd116"/>
                </svg>
            </span>
        </div>
    </div>
</footer>

<div class="toast" role="status" aria-live="polite"></div>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js" defer></script>
</body>
</html>
