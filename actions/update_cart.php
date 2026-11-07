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
$quantity   = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$cartItemId || $quantity === false || $quantity === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid item or quantity.']);
    exit;
}

$cart = new Cart();
$cart->updateItem($cartItemId, $quantity);

echo json_encode([
    'success'    => true,
    'cart_count' => $cart->getItemCount(),
    'cart_total' => formatPrice($cart->getTotal()),
]);
