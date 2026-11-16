<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/User.php';

requireAdmin();

$userModel  = new User();
$total      = $userModel->countAll();
$totalPages = max(1, (int) ceil($total / ADMIN_PER_PAGE));
$page       = min($totalPages, max(1, (int) ($_GET['page'] ?? 1)));
$users      = $userModel->getAll(ADMIN_PER_PAGE, ($page - 1) * ADMIN_PER_PAGE);

$pageTitle   = 'Users — ' . SITE_NAME . ' Admin';
$adminCrumbs = [['Users', null]];
require_once __DIR__ . '/includes/admin_header.php';
?>

<h1 class="admin-title">Users</h1>

<div class="admin-list">
    <?php foreach ($users as $u): ?>
        <article class="admin-res">
            <div class="admin-res-top">
                <div>
                    <p class="res-name"><?php echo e($u['full_name']); ?></p>
                    <p class="res-sub"><?php echo e($u['email']); ?> - @<?php echo e($u['username']); ?></p>
                </div>
                <span class="status-pill <?php echo ($u['user_role'] ?? '') === 'admin' ? 'status-shipped' : ''; ?>"><?php echo e(ucfirst($u['user_role'] ?? 'customer')); ?></span>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$users): ?><p class="admin-empty">No users yet.</p><?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
    <nav class="pager admin-pager" aria-label="Pagination">
        <p class="pager-count">Page <?php echo $page; ?> of <?php echo $totalPages; ?></p>
        <div class="pager-links">
            <?php if ($page > 1): ?><a class="pager-btn" href="?page=<?php echo $page - 1; ?>">&lsaquo; prev</a><?php else: ?><span class="pager-btn is-disabled">&lsaquo; prev</span><?php endif; ?>
            <?php if ($page < $totalPages): ?><a class="pager-btn" href="?page=<?php echo $page + 1; ?>">next &rsaquo;</a><?php else: ?><span class="pager-btn is-disabled">next &rsaquo;</span><?php endif; ?>
        </div>
    </nav>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
