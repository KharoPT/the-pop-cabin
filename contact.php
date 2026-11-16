<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Inquiry.php';
require_once __DIR__ . '/classes/User.php';

// Prefill the visitor's details when they are signed in.
$prefill = ['first_name' => '', 'last_name' => '', 'email' => ''];
if (isLoggedIn() && ($u = (new User())->getById((int) currentUserId()))) {
    $parts = preg_split('/\s+/', trim($u['full_name']), 2);
    $prefill = ['first_name' => $parts[0] ?? '', 'last_name' => $parts[1] ?? '', 'email' => $u['email']];
}

$errors = [];
$old    = $prefill + ['message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
        'last_name'  => sanitizeInput($_POST['last_name'] ?? ''),
        'email'      => sanitizeInput($_POST['email'] ?? ''),
        'message'    => sanitizeInput($_POST['message'] ?? ''),
    ];

    if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        // The browser sent more than PHP's post_max_size allows, so PHP discarded everything.
        $errors[] = 'That upload is larger than the server allows. Try a smaller file.';
    } elseif (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        if ($old['message'] === '' || strlen($old['message']) > 5000) {
            $errors[] = 'Please tell us your feedback, comment or request (up to 5000 characters).';
        }
        if ($old['first_name'] === '' || strlen($old['first_name']) > 100) {
            $errors[] = 'Please enter your first name.';
        }
        if ($old['last_name'] === '' || strlen($old['last_name']) > 100) {
            $errors[] = 'Please enter your last name.';
        }
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['email']) > 255) {
            $errors[] = 'Please enter a valid email address.';
        }

        $attachment = [];
        if (empty($errors)) {
            $attachment = Inquiry::saveAttachment($_FILES['attachment'] ?? []);
            if (isset($attachment['error'])) {
                $errors[] = $attachment['error'];
            }
        }

        if (empty($errors)) {
            $saved = (new Inquiry())->create($old + [
                'user_id'         => currentUserId(),
                'attachment_path' => $attachment['path'] ?? null,
                'attachment_name' => $attachment['name'] ?? null,
            ]);
            if ($saved) {
                setFlash('success', 'Thank you! Your message has been sent. We will respond as soon as possible.');
                redirect('contact.php');
            }
            // Don't leave an orphaned file behind if the database insert failed.
            if (!empty($attachment['path'])) {
                @unlink(__DIR__ . '/' . $attachment['path']);
            }
            $errors[] = 'Your message could not be saved. Please try again in a moment.';
        }
    }
}

$pageTitle = 'Contact Us — ' . SITE_NAME;
$bodyClass = 'page-contact';
require_once __DIR__ . '/includes/header.php';
?>

<section class="wrap contact">
    <h1 class="sign">Contact Us</h1>
    <p class="contact-intro"><?php echo e(siteContent('contact_intro')); ?></p>

    <?php foreach ($errors as $error): ?>
        <div class="flash-message flash-error" role="alert"><?php echo e($error); ?></div>
    <?php endforeach; ?>

    <form class="contact-form" method="post" action="<?php echo SITE_URL; ?>/contact.php" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">

        <div class="field">
            <label class="sign sign-label" for="message">Comment/Description/Inquiry</label>
            <textarea id="message" name="message" rows="7" maxlength="5000" required placeholder="Enter your feedback, comment, or details of your request."><?php echo e($old['message']); ?></textarea>
            <p class="field-note">We will respond as soon as possible.</p>
        </div>

        <div class="field">
            <div class="field-head">
                <label class="sign sign-label" for="attachment">Upload a file (optional)</label>
                <span class="field-max">Maximum size: <?php echo MAX_CONTACT_FILE_MB; ?> MB</span>
            </div>
            <label class="dropzone" for="attachment" data-dropzone>
                <span class="dz-title" data-dz-title>Choose a file or drop it here.</span>
                <span class="dz-hint">PNG, JPG, JPEG, DOCX, TXT or MD only</span>
                <input type="file" id="attachment" name="attachment" accept=".png,.jpg,.jpeg,.docx,.txt,.md">
            </label>
        </div>

        <div class="field">
            <label class="sign sign-label" for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" required maxlength="100" autocomplete="given-name" placeholder="Your First Name..." value="<?php echo e($old['first_name']); ?>">
        </div>
        <div class="field">
            <label class="sign sign-label" for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" required maxlength="100" autocomplete="family-name" placeholder="Your Last Name..." value="<?php echo e($old['last_name']); ?>">
        </div>
        <div class="field">
            <label class="sign sign-label" for="email">Email</label>
            <input type="email" id="email" name="email" required maxlength="255" autocomplete="email" placeholder="Your Valid Email..." value="<?php echo e($old['email']); ?>">
        </div>

        <button type="submit" class="btn btn-dark btn-block btn-lg">Submit</button>
        <p class="submit-note">Please ensure that the details above are valid and true before submitting</p>
    </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
