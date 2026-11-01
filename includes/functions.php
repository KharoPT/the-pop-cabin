<?php

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function sanitizeInput($value): string
{
    return trim(strip_tags((string) $value));
}

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
    return SITE_CURRENCY_SYMBOL . number_format((float) $amount, 2);
}

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
