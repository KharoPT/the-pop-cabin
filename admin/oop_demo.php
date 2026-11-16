<?php
/**
 * OOP demonstration (admin only, not linked in the menu).
 * Shows polymorphism: the SAME call, $model->describe(), gives a different result per class,
 * because Product and User override BaseModel::describe() while Review and Inquiry inherit it.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Review.php';
require_once __DIR__ . '/../classes/Inquiry.php';

requireAdmin();

$models = [new User(), new Product(), new Review(), new Inquiry()];

$pageTitle   = 'OOP demo — ' . SITE_NAME . ' Admin';
$adminCrumbs = [['OOP demo', null]];
require_once __DIR__ . '/includes/admin_header.php';
?>

<h1 class="admin-title">OOP demo</h1>

<section class="admin-card">
    <div class="admin-card-head"><h2>Polymorphism: one call, many behaviours</h2></div>
    <ul class="res-list">
        <?php foreach ($models as $model): ?>
            <li>
                <div>
                    <p class="res-name"><?php echo e(get_class($model)); ?> <span class="res-sub">extends <?php echo e(get_parent_class($model)); ?></span></p>
                    <p class="res-sub"><?php echo e($model->describe()); ?></p>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
