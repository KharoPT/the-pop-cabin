<?php
/** Save a buyer review from the product page (signed-in customers, one review per pop). */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/Review.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('shop.php');
}

$productId = (int) ($_POST['product_id'] ?? 0);
$product   = $productId > 0 ? (new Product())->getById($productId) : false;
if (!$product) {
    setFlash('error', 'That pop could not be found.');
    redirect('shop.php');
}
$back = 'product.php?slug=' . urlencode($product['slug']);

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = SITE_URL . '/' . $back . '#write-review';
    setFlash('error', 'Please log in to write a review.');
    redirect('login.php');
}
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Your session expired. Please try again.');
    redirect($back . '#reviews');
}

$rating    = (int) ($_POST['rating'] ?? 0);
$comment   = sanitizeInput($_POST['comment'] ?? '');
$anonymous = !empty($_POST['anonymous']);

if ($rating < 1 || $rating > 5 || $comment === '' || strlen($comment) > 1000) {
    setFlash('error', 'Please choose a star rating and write a comment (up to 1000 characters).');
    redirect($back . '&review=error#write-review');
}

if ((new Review())->create($productId, (int) currentUserId(), $rating, $comment, $anonymous)) {
    setFlash('success', 'Thanks! Your review has been posted.');
} else {
    setFlash('error', 'We could not save your review. You may have already reviewed this pop.');
}
redirect($back . '#reviews');
