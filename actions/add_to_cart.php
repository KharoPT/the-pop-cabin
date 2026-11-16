<?php
/**
 * Add a product to the current cart.
 *
 * Answers JSON when called from JavaScript (X-Requested-With / Accept: application/json),
 * otherwise sets a flash message and redirects, so the product page still works without JS.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Cart.php';
require_once __DIR__ . '/../classes/Product.php';

const MAX_QTY_PER_REQUEST = 99;

$wantsJson = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
    || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

/** Send the result as JSON or as a flash + redirect, then stop. */
function cartResponse(bool $ok, string $message, ?int $count, bool $json, string $redirectTo, int $status = 200): void
{
    if ($json) {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => $ok, 'message' => $message, 'cart_count' => $count]);
        exit;
    }
    setFlash($ok ? 'success' : 'error', $message);
    redirect($redirectTo);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cartResponse(false, 'Invalid request.', null, $wantsJson, 'shop.php', 405);
}
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    cartResponse(false, 'Your session expired. Please refresh the page and try again.', null, $wantsJson, 'shop.php', 403);
}

$productId = (int) ($_POST['product_id'] ?? 0);
$quantity  = max(1, min(MAX_QTY_PER_REQUEST, (int) ($_POST['quantity'] ?? 1)));

$product = $productId > 0 ? (new Product())->getById($productId) : false;
if (!$product) {
    cartResponse(false, 'That item could not be found.', null, $wantsJson, 'shop.php', 404);
}

$productPage = 'product.php?slug=' . urlencode($product['slug']);
$stock       = (int) $product['stock_quantity'];
$cart        = new Cart();

if ($stock <= 0) {
    cartResponse(false, 'Sorry, "' . $product['name'] . '" is sold out.', $cart->getItemCount(), $wantsJson, $productPage, 409);
}

// Never let the cart hold more of an item than is in stock.
$inCart = 0;
foreach ($cart->getItems() as $line) {
    if ((int) $line['product_id'] === $productId) {
        $inCart = (int) $line['quantity'];
        break;
    }
}
$room = $stock - $inCart;
if ($room <= 0) {
    cartResponse(
        false,
        'You already have all ' . $stock . ' available "' . $product['name'] . '" in your cart.',
        $cart->getItemCount(), $wantsJson, 'cart.php', 409
    );
}

$added   = min($quantity, $room);
$cart->addItem($productId, $added);

$message = $added < $quantity
    ? 'Only ' . $added . ' more could be added — that is all we have in stock.'
    : '"' . $product['name'] . '" was added to your cart.';

cartResponse(true, $message, $cart->getItemCount(), $wantsJson, 'cart.php');
