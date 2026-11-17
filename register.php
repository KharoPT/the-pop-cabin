<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/User.php';

if (isLoggedIn()) {
    redirect('account.php');
}

// Character count that still works on hosts without the mbstring extension.
$textLength = static fn(string $s): int => function_exists('mb_strlen') ? mb_strlen($s) : strlen($s);

$errors      = [];
$fieldErrors = [];
$old         = ['first_name' => '', 'last_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
        'last_name'  => sanitizeInput($_POST['last_name'] ?? ''),
        'email'      => sanitizeInput($_POST['email'] ?? ''),
    ];
    $password        = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $fullName = trim($old['first_name'] . ' ' . $old['last_name']);

        if ($old['first_name'] === '') {
            $fieldErrors['first_name'] = 'Please enter your first name.';
        }
        if ($old['last_name'] === '') {
            $fieldErrors['last_name'] = 'Please enter your last name.';
        }
        if ($old['first_name'] === '' || $old['last_name'] === '' || $textLength($fullName) > 100) {
            $errors[] = 'Please enter your first and last name.';
        }
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || $textLength($old['email']) > 255) {
            $errors[] = 'Please enter a valid email address.';
            $fieldErrors['email'] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
            $fieldErrors['password'] = 'Password must be at least 8 characters.';
        } elseif (strlen($password) > 72) {
            // bcrypt (PASSWORD_DEFAULT) silently ignores everything past 72 bytes.
            $errors[] = 'Password must be 72 characters or fewer.';
            $fieldErrors['password'] = 'Password must be 72 characters or fewer.';
        } elseif ($password !== $passwordConfirm) {
            $errors[] = 'The two passwords do not match.';
            $fieldErrors['password_confirm'] = 'The two passwords do not match.';
        }
        if (empty($_POST['agree'])) {
            $errors[] = 'Please agree to the Terms & Conditions and Privacy Policy to continue.';
            $fieldErrors['agree'] = 'Please agree to the Terms & Conditions and Privacy Policy to continue.';
        }

        if (empty($errors)) {
            $userModel = new User();
            if ($userModel->emailExists($old['email'])) {
                $errors[] = 'An account with that email already exists. Try logging in instead.';
                $fieldErrors['email'] = 'An account with that email already exists. Try logging in instead.';
            } else {
                // The sign-up form has no username field, so one is generated from the email.
                $result = $userModel->register([
                    'full_name' => $fullName,
                    'username'  => $userModel->generateUsername($old['email']),
                    'email'     => $old['email'],
                    'password'  => $password,
                ]);

                if ($result['success']) {
                    // Sign the new customer straight in.
                    session_regenerate_id(true);
                    $_SESSION['user_id']   = $result['user_id'];
                    $_SESSION['full_name'] = $fullName;
                    $_SESSION['role']      = 'customer';

                    $redirectTo = $_SESSION['redirect_after_login'] ?? null;
                    unset($_SESSION['redirect_after_login']);

                    setFlash('success', 'Welcome, ' . $old['first_name'] . '! Your account is ready.');
                    if ($redirectTo) {
                        header('Location: ' . $redirectTo);
                        exit;
                    }
                    redirect('index.php');
                }
                $errors[] = $result['message'] ?? 'We could not create your account. Please try again.';
            }
        }
    }
}

$pageTitle = 'Create Your Account — ' . SITE_NAME;
$bodyClass = 'page-auth';
$pageStyles = ['css/auth.css'];
require_once __DIR__ . '/includes/header.php';
?>

<img class="auth-bg" src="<?php echo SITE_URL; ?>/assets/img/auth-bg.jpg" alt="" width="716" height="408">

<section class="auth-card" aria-labelledby="auth-title">
    <h1 id="auth-title">Create Your Account</h1>

    <?php foreach ($errors as $error): ?>
        <div class="flash-message flash-error" role="alert"><?php echo e($error); ?></div>
    <?php endforeach; ?>

    <form class="auth-form" method="post" action="<?php echo SITE_URL; ?>/register.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">

        <div class="auth-fields">
            <div class="auth-inputs">
                <div class="field-row">
                    <div class="field">
                        <label for="first_name">First Name</label>
                        <?php $err = $fieldErrors['first_name'] ?? null; ?>
                        <input type="text" id="first_name" name="first_name" required maxlength="50" autocomplete="given-name" placeholder="Your First Name" value="<?php echo e($old['first_name']); ?>"<?php echo $err ? ' class="is-invalid" aria-invalid="true" aria-describedby="first_name-error"' : ''; ?>>
                        <?php if ($err): ?><p class="field-error" id="first_name-error"><?php echo e($err); ?></p><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="last_name">Last Name</label>
                        <?php $err = $fieldErrors['last_name'] ?? null; ?>
                        <input type="text" id="last_name" name="last_name" required maxlength="50" autocomplete="family-name" placeholder="Your Last Name" value="<?php echo e($old['last_name']); ?>"<?php echo $err ? ' class="is-invalid" aria-invalid="true" aria-describedby="last_name-error"' : ''; ?>>
                        <?php if ($err): ?><p class="field-error" id="last_name-error"><?php echo e($err); ?></p><?php endif; ?>
                    </div>
                </div>

                <div class="field-stack">
                    <div class="field">
                        <label for="email">Email Address</label>
                        <?php $err = $fieldErrors['email'] ?? null; ?>
                        <input type="email" id="email" name="email" required maxlength="255" autocomplete="email" placeholder="e.g. username@gmail.com" value="<?php echo e($old['email']); ?>"<?php echo $err ? ' class="is-invalid" aria-invalid="true" aria-describedby="email-error"' : ''; ?>>
                        <?php if ($err): ?><p class="field-error" id="email-error"><?php echo e($err); ?></p><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <?php $err = $fieldErrors['password'] ?? null; ?>
                        <input type="password" id="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="Your Password"<?php echo $err ? ' class="is-invalid" aria-invalid="true" aria-describedby="password-error"' : ''; ?>>
                        <?php if ($err): ?><p class="field-error" id="password-error"><?php echo e($err); ?></p><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="password_confirm">Confirm Password</label>
                        <?php $err = $fieldErrors['password_confirm'] ?? null; ?>
                        <input type="password" id="password_confirm" name="password_confirm" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="Confirm Your Password"<?php echo $err ? ' class="is-invalid" aria-invalid="true" aria-describedby="password_confirm-error"' : ''; ?>>
                        <?php if ($err): ?><p class="field-error" id="password_confirm-error"><?php echo e($err); ?></p><?php endif; ?>
                    </div>
                </div>
            </div>

            <?php $err = $fieldErrors['agree'] ?? null; ?>
            <label class="check-line check-terms<?php echo $err ? ' is-invalid' : ''; ?>">
                <input type="checkbox" name="agree" value="1" required<?php echo $err ? ' aria-invalid="true" aria-describedby="agree-error"' : ''; ?>>
                <span>I agree to the <a href="<?php echo SITE_URL; ?>/terms.php">Terms &amp; Conditions</a> and <a class="is-pending">Privacy Policy</a></span>
            </label>
            <?php if ($err): ?><p class="field-error" id="agree-error"><?php echo e($err); ?></p><?php endif; ?>
        </div>

        <div class="auth-actions">
            <button type="submit" class="auth-submit">Sign Up</button>
            <p class="auth-switch">Already have an account? <a href="<?php echo SITE_URL; ?>/login.php">Sign in</a></p>
        </div>
    </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
