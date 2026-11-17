<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/User.php';

// ?next=product.php?slug=x#write-review  -> where to go after signing in (site-relative paths only)
$next = safeNextPath($_GET['next'] ?? $_POST['next'] ?? null);
if ($next !== null && !isLoggedIn()) {
    $_SESSION['redirect_after_login'] = SITE_URL . '/' . $next;
}

if (isLoggedIn()) {
    redirect($next ?? 'account.php');
}

$errors      = [];
$fieldErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $identifier = sanitizeInput($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if ($identifier === '' || $password === '') {
            $errors[] = 'Please enter your email and password.';
            if ($identifier === '') {
                $fieldErrors['identifier'] = 'Please enter your email address.';
            }
            if ($password === '') {
                $fieldErrors['password'] = 'Please enter your password.';
            }
        } else {
            $user = (new User())->login($identifier, $password);
            if ($user) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['user_role'] ?? 'customer';

                if (!empty($_POST['remember'])) {
                    // "Remember me": keep the login cookie for 30 days instead of until the browser closes.
                    $cookie = session_get_cookie_params();
                    setcookie(session_name(), session_id(), [
                        'expires'  => time() + 60 * 60 * 24 * 30,
                        'path'     => $cookie['path'] ?: '/',
                        'domain'   => $cookie['domain'],
                        'secure'   => !empty($_SERVER['HTTPS']),
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                }

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

$pageTitle = 'Log In — ' . SITE_NAME;
$bodyClass = 'page-auth';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner"><p class="banner-title"><?php echo e(SITE_NAME); ?></p></div>

<section class="auth-panel">
    <div class="auth-card">
        <h1>Log In Your Account</h1>

        <?php foreach ($errors as $error): ?>
            <div class="flash-message flash-error" role="alert"><?php echo e($error); ?></div>
        <?php endforeach; ?>

        <form method="post" action="<?php echo SITE_URL; ?>/login.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
            <?php if ($next !== null): ?><input type="hidden" name="next" value="<?php echo e($next); ?>"><?php endif; ?>

            <div class="field">
                <label for="identifier">Email Address</label>
                <?php $err = $fieldErrors['identifier'] ?? null; ?>
                <input type="text" inputmode="email" id="identifier" name="identifier" required autocomplete="username" placeholder="Your Valid Email..." value="<?php echo e(is_string($_POST['identifier'] ?? null) ? $_POST['identifier'] : ''); ?>"<?php echo $err ? ' class="is-invalid" aria-invalid="true" aria-describedby="identifier-error"' : ''; ?>>
                <?php if ($err): ?><p class="field-error" id="identifier-error"><?php echo e($err); ?></p><?php endif; ?>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <?php $err = $fieldErrors['password'] ?? null; ?>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Your Password..."<?php echo $err ? ' class="is-invalid" aria-invalid="true" aria-describedby="password-error"' : ''; ?>>
                <?php if ($err): ?><p class="field-error" id="password-error"><?php echo e($err); ?></p><?php endif; ?>
            </div>

            <div class="auth-options">
                <label class="check-line"><input type="checkbox" name="remember" value="1"> Remember me</label>
                <a class="is-pending">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-dark btn-block btn-lg">Login</button>
        </form>

        <p class="auth-switch">Don&rsquo;t have an account yet? <a href="<?php echo SITE_URL; ?>/register.php">Sign Up</a></p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
