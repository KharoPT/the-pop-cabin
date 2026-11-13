<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/User.php';

if (isLoggedIn()) {
    redirect('account.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $identifier = sanitizeInput($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if ($identifier === '' || $password === '') {
            $errors[] = 'Please enter your email/username and password.';
        } else {
            $user = (new User())->login($identifier, $password);
            if ($user) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['user_role'] ?? 'customer';

                $redirectTo = $_SESSION['redirect_after_login'] ?? null;
                unset($_SESSION['redirect_after_login']);

                setFlash('success', 'Welcome back, ' . explode(' ', $user['full_name'])[0] . '!');

                if ($redirectTo) {
                    header('Location: ' . $redirectTo);
                    exit;
                }
                redirect($_SESSION['role'] === 'admin' ? 'admin/index.php' : 'index.php');
            } else {
                $errors[] = 'Those credentials do not match our records.';
            }
        }
    }
}

$pageTitle = 'Sign In — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<section class="section auth-section">
    <div class="container auth-container">
        <div class="auth-card">
            <h1>Sign In</h1>
            <p class="auth-sub">New here? <a href="<?php echo SITE_URL; ?>/register.php">Create an account</a></p>

            <?php foreach ($errors as $error): ?>
                <div class="flash-message flash-error"><?php echo e($error); ?></div>
            <?php endforeach; ?>

            <form method="post" action="<?php echo SITE_URL; ?>/login.php">
                <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
                <div class="form-row">
                    <label for="identifier">Email or username</label>
                    <input type="text" id="identifier" name="identifier" required value="<?php echo e($_POST['identifier'] ?? ''); ?>">
                </div>
                <div class="form-row">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>

            <p class="auth-hint">Admin demo login &mdash; username <code>admin</code>, password <code>Admin123!</code></p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
