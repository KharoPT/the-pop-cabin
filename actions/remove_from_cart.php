<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Cart.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$cartItemId = filter_input(INPUT_POST, 'cart_item_id', FILTER_VALIDATE_INT);

if (!$cartItemId) {
    echo json_encode(['success' => false, 'message' => 'Invalid item.']);
    exit;
}

$cart = new Cart();
$cart->removeItem($cartItemId);

echo json_encode([
    'success'    => true,
    'cart_count' => $cart->getItemCount(),
    'cart_total' => formatPrice($cart->getTotal()),
]);
