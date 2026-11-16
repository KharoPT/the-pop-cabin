<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Cart.php';
require_once __DIR__ . '/../classes/Order.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Your session expired — please try again.');
    redirect('checkout.php');
}

$cart  = new Cart();
$items = $cart->getItems();

if (empty($items)) {
    setFlash('error', 'Your cart is empty.');
    redirect('cart.php');
}

$required = ['name', 'address', 'city', 'postal_code', 'country'];
$shipping = [];
foreach ($required as $field) {
    $value = sanitizeInput($_POST[$field] ?? '');
    if ($value === '') {
        setFlash('error', 'Please complete all shipping fields.');
        redirect('checkout.php');
    }
    $shipping[$field] = $value;
}

$result = (new Order())->create(currentUserId(), $items, $shipping);

if (!$result['success']) {
    setFlash('error', $result['message'] ?? 'We could not place your order — please review your cart and try again.');
    redirect('cart.php');
}

$cart->clear();
setFlash('success', 'Thank you! Your order ' . $result['order_number'] . ' has been placed.');
redirect('order_detail.php?order=' . urlencode($result['order_number']));
