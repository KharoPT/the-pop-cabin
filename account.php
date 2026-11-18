<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Order.php';

requireLogin();

$userModel  = new User();
$orderModel = new Order();
$user   = $userModel->getById(currentUserId());
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'profile') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $data = [
            'full_name'   => sanitizeInput($_POST['full_name'] ?? ''),
            'phone'       => sanitizeInput($_POST['phone'] ?? ''),
            'address'     => sanitizeInput($_POST['address'] ?? ''),
            'city'        => sanitizeInput($_POST['city'] ?? ''),
            'postal_code' => sanitizeInput($_POST['postal_code'] ?? ''),
            'country'     => sanitizeInput($_POST['country'] ?? ''),
        ];
        if ($data['full_name'] === '') {
            $errors[] = 'Full name cannot be empty.';
        } else {
            $userModel->updateProfile($user['user_id'], $data);
            $_SESSION['full_name'] = $data['full_name'];
            setFlash('success', 'Your profile has been updated.');
            redirect('account.php');
        }
    }
}

$orders = $orderModel->getByUserId($user['user_id']);

$pageTitle = 'My Account — ' . SITE_NAME;
$pageStyles = ['css/account.css'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container"><h1>My Account</h1></div>
</section>

<section class="section">
    <div class="container account-layout">
        <div class="account-col">
            <h3>Profile Details</h3>
            <?php foreach ($errors as $error): ?>
                <div class="flash-message flash-error"><?php echo e($error); ?></div>
            <?php endforeach; ?>
            <form method="post" action="<?php echo SITE_URL; ?>/account.php">
                <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
                <input type="hidden" name="form" value="profile">
                <div class="form-row">
                    <label for="full_name">Full name</label>
                    <input type="text" id="full_name" name="full_name" required value="<?php echo e($user['full_name']); ?>">
                </div>
                <div class="form-row">
                    <label for="email">Email</label>
                    <input type="text" id="email" value="<?php echo e($user['email']); ?>" disabled>
                </div>
                <div class="form-row">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" value="<?php echo e($user['phone'] ?? ''); ?>">
                </div>
                <div class="form-row">
                    <label for="address">Street address</label>
                    <input type="text" id="address" name="address" value="<?php echo e($user['address'] ?? ''); ?>">
                </div>
                <div class="form-row form-row-split">
                    <div>
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" value="<?php echo e($user['city'] ?? ''); ?>">
                    </div>
                    <div>
                        <label for="postal_code">Postal code</label>
                        <input type="text" id="postal_code" name="postal_code" value="<?php echo e($user['postal_code'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" value="<?php echo e($user['country'] ?? ''); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>

        <div class="account-col">
            <h3>Order History</h3>
            <?php if (empty($orders)): ?>
                <p class="empty-note">You haven't placed any orders yet.</p>
            <?php else: ?>
                <div class="order-list">
                    <?php foreach ($orders as $order): ?>
                        <?php $status = orderStatus($order); ?>
                        <a href="<?php echo SITE_URL; ?>/order_detail.php?order=<?php echo e($order['order_number']); ?>" class="order-row">
                            <strong><?php echo e($order['order_number']); ?></strong>
                            <span class="status-pill status-<?php echo e($status); ?>"><?php echo e(ucfirst($status)); ?></span>
                            <span class="order-total"><?php echo formatPrice($order['total_amount']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
