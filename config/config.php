<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'popcabdb');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SITE_URL', 'http://localhost/relic-rarity');
define('SITE_NAME', 'Relic & Rarity');
define('SITE_CURRENCY_SYMBOL', '$');
define('FREE_SHIPPING_THRESHOLD', 100);
define('FLAT_SHIPPING_FEE', 15);
define('PRODUCTS_PER_PAGE', 12);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
