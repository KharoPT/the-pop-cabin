<?php
/**
 * The Pop Cabin — Shared helper functions
 */

/** Escape a value for safe HTML output. */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * URL of an asset under /assets, versioned with its modification time so a
 * changed file (CSS, icon, image) is fetched again instead of a cached copy.
 */
function assetUrl(string $path): string
{
    $rel  = ltrim($path, '/');
    $file = __DIR__ . '/../assets/' . $rel;
    $v    = is_file($file) ? filemtime($file) : null;
    return SITE_URL . '/assets/' . $rel . ($v ? '?v=' . $v : '');
}

/** Trim and strip tags from raw input before it touches the database. */
function sanitizeInput($value): string
{
    return trim(strip_tags((string) $value));
}

/** Redirect to a path relative to SITE_URL and stop execution. */
function redirect(string $path): void
{
    header('Location: ' . SITE_URL . '/' . ltrim($path, '/'));
    exit;
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

function currentUserId(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

/** Send guests to the login page and remember where they were headed. */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        setFlash('error', 'Please sign in to continue.');
        redirect('login.php');
    }
}

function requireAdmin(): void
{
    if (!isAdmin()) {
        setFlash('error', 'You do not have access to that page.');
        redirect('login.php');
    }
}

function formatPrice($amount): string
{
    $amount = (float) $amount;
    $decimals = abs($amount - round($amount)) < 0.005 ? 0 : 2;
    return SITE_CURRENCY_SYMBOL . number_format($amount, $decimals);
}

/** Turn a product name into a URL-friendly, unique-ish slug. */
function slugify(string $text): string
{
    $text = preg_replace('/[^a-zA-Z0-9]+/', '-', $text);
    $text = strtolower(trim($text, '-'));
    return $text !== '' ? $text : 'item-' . substr(md5((string) mt_rand()), 0, 8);
}

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token): bool
{
    return !empty($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

/** Queue a one-time flash message, read by includes/header.php on the next page render. */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function getFlashMessages(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

/** An order's status; a missing (NULL) status is treated as "pending". */
function orderStatus(array $order): string
{
    return $order['order_status'] ?: 'pending';
}

function conditionOptions(): array
{
    return ['Mint', 'Near Mint', 'Excellent', 'Good', 'Fair'];
}

function getAllCategories(): array
{
    $db = Database::getInstance()->getConnection();
    return $db->query('SELECT * FROM categories ORDER BY category_name ASC')->fetchAll();
}


// ---------------------------------------------------------------------------
// Front-end helpers
// ---------------------------------------------------------------------------

/** Read a value from config/content.php (loaded once). */
function siteContent(string $key, $default = '')
{
    static $content = null;
    if ($content === null) {
        $content = require __DIR__ . '/../config/content.php';
    }
    return $content[$key] ?? $default;
}

/** Edition choices for the admin product form. Add more names here. */
function editionOptions(): array
{
    return ['Regular', 'Chase', 'Flocked'];
}

/** Turn a stored image value (full URL or a path under the site root) into a usable src. */
function imgSrc(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $value)) {
        return $value;
    }
    return SITE_URL . '/' . ltrim($value, '/');
}

/** Only allow redirects to a relative path inside this site (no scheme, no //host). */
function safeNextPath(?string $path): ?string
{
    $path = trim((string) $path);
    if ($path === '' || str_contains($path, '\\') || preg_match('#^([a-z][a-z0-9+.-]*:|//)#i', $path)) {
        return null;
    }
    return ltrim($path, '/');
}

/** Five star icons; $filled of them are solid. Decorative: the rating is given as text elsewhere. */
function renderStars(float $rating, string $class = 'stars'): string
{
    $filled = (int) round($rating);
    $out = '<span class="' . e($class) . '" aria-hidden="true">';
    for ($i = 1; $i <= 5; $i++) {
        $out .= '<svg class="star' . ($i <= $filled ? ' is-on' : '') . '" viewBox="0 0 24 24" width="18" height="18"><path d="M12 2.6l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4l-5.9 3.2 1.2-6.5L2.5 9.5l6.6-.9z"/></svg>';
    }
    return $out . '</span>';
}

/** "Ana Reyes" -> "Ana R." for public display. */
function publicName(?string $fullName): string
{
    $parts = preg_split('/\s+/', trim((string) $fullName));
    if (!$parts || $parts[0] === '') {
        return 'Customer';
    }
    $first = $parts[0];
    if (count($parts) > 1) {
        preg_match('/./u', end($parts), $m); // first character, multibyte-safe without mbstring
        return $first . ' ' . ($m[0] ?? '') . '.';
    }
    return $first;
}

/** "Nov 9" style date from a DB timestamp. */
function shortDate(?string $timestamp, bool $withYear = false): string
{
    $t = $timestamp ? strtotime($timestamp) : false;
    return $t ? date($withYear ? 'M j, Y' : 'M j', $t) : '';
}

/**
 * Page numbers for a pager, as in the wireframe ("1 2 3 ... 25 26"): the first page, the last two
 * pages and the pages around the current one, with null marking a gap ("...").
 */
function pageWindow(int $current, int $total): array
{
    $keep = [1, $total - 1, $total, $current - 1, $current, $current + 1];
    if ($current <= 3) {
        $keep = array_merge($keep, [1, 2, 3]);
    }
    $keep = array_values(array_unique(array_filter($keep, fn($p) => $p >= 1 && $p <= $total)));
    sort($keep);

    $out  = [];
    $prev = 0;
    foreach ($keep as $p) {
        if ($prev && $p - $prev > 1) {
            $out[] = null;
        }
        $out[] = $p;
        $prev = $p;
    }
    return $out;
}

/**
 * Validate and store an uploaded image under uploads/products/.
 * Returns ['path' => 'uploads/products/xyz.jpg'] on success, ['error' => '...'] on failure,
 * or [] when no file was chosen. The real image type is checked, not just the file name.
 */
function saveProductImage(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
            ? 'That photo is larger than the server allows.' : 'The photo could not be uploaded. Please try again.'];
    }
    if ($file['size'] > MAX_PRODUCT_IMAGE_MB * 1024 * 1024) {
        return ['error' => 'The photo must be ' . MAX_PRODUCT_IMAGE_MB . ' MB or smaller.'];
    }
    $info = @getimagesize($file['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($types[$info[2]])) {
        return ['error' => 'The photo must be a JPG, PNG, GIF or WEBP image.'];
    }
    $dir = __DIR__ . '/../uploads/products';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return ['error' => 'The uploads folder could not be created.'];
    }
    $name = 'pop-' . bin2hex(random_bytes(8)) . '.' . $types[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        return ['error' => 'The photo could not be saved. Check that uploads/products is writable.'];
    }
    return ['path' => 'uploads/products/' . $name];
}

/** Delete a previously uploaded product photo (only ever inside uploads/products/). */
function deleteProductImage(?string $path): void
{
    if ($path && preg_match('#^uploads/products/[A-Za-z0-9._-]+$#', $path)) {
        $full = __DIR__ . '/../' . $path;
        if (is_file($full)) {
            @unlink($full);
        }
    }
}

/** Trim a string to $max characters without needing the mbstring extension. */
function mb_strimwidth_safe(string $s, int $max): string
{
    return function_exists('mb_substr') ? mb_substr($s, 0, $max) : substr($s, 0, $max);
}
