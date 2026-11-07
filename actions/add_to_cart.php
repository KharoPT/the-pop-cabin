<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/Cart.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Your session expired — please refresh the page.']);
    exit;
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity  = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?: 1;

if (!$productId || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity.']);
    exit;
}

$product = (new Product())->getById($productId);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

if ((int) $product['stock_quantity'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock available.']);
    exit;
}

$cart = new Cart();
$cart->addItem($productId, $quantity);

echo json_encode([
    'success'    => true,
    'message'    => $product['name'] . ' was added to your cart.',
    'cart_count' => $cart->getItemCount(),
]);
