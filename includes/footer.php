    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-col">
                <div class="logo footer-logo">
                    <span class="logo-mark">PC</span>
                    <span class="logo-text"><?php echo e(SITE_NAME); ?></span>
                </div>
                <p>Funko Pop vinyl figures for fans and collectors.</p>
            </div>
            <div class="footer-col">
                <h4>Shop</h4>
                <a href="<?php echo SITE_URL; ?>/shop.php">All Funko Pops</a>
                <a href="<?php echo SITE_URL; ?>/shop.php?sort=newest">New Arrivals</a>
                <a href="<?php echo SITE_URL; ?>/cart.php">Your Cart</a>
            </div>
            <div class="footer-col">
                <h4>Account</h4>
                <a href="<?php echo SITE_URL; ?>/account.php">My Account</a>
                <a href="<?php echo SITE_URL; ?>/login.php">Sign In</a>
                <a href="<?php echo SITE_URL; ?>/register.php">Create Account</a>
            </div>
            <div class="footer-col">
                <h4>Stay in the loop</h4>
                <p>Word of new arrivals, before anyone else.</p>
                <form class="newsletter-form" onsubmit="event.preventDefault(); this.querySelector('button').textContent='Thanks!';">
                    <input type="email" placeholder="you@example.com" required>
                    <button type="submit">Join</button>
                </form>
            </div>
        </div>
        <div class="container footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo e(SITE_NAME); ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
