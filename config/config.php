<?php
/**
 * The Pop Cabin — Core Configuration
 * Update the values below to match your local environment before running the site.
 */

// ---- Database credentials ----
// The database must already exist: create one named popcabdb in phpMyAdmin,
// then import your .sql dump into it. (XAMPP defaults: user "root", empty password.)
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'popcabdb');
define('DB_USER', 'root');
define('DB_PASS', '');

// ---- Site settings ----
// Base URL of the project, no trailing slash. Examples:
//   PHP built-in server (from the project root): php -S localhost:8000   -> http://localhost:8000
//   XAMPP / MAMP, project copied into htdocs/popcabdb-frontend/popcab    -> http://localhost/popcabdb-frontend/popcab
define('SITE_URL', 'http://localhost/popcab');
define('SITE_NAME', 'The Pop Cabin');
define('SITE_CURRENCY_SYMBOL', '₱');
define('FREE_SHIPPING_THRESHOLD', 100);
define('FLAT_SHIPPING_FEE', 15);
define('PRODUCTS_PER_PAGE', 16);
define('ADMIN_PER_PAGE', 20);
define('MAX_PRODUCT_IMAGE_MB', 5);
define('MAX_CONTACT_FILE_MB', 10);

date_default_timezone_set('Asia/Manila');

// ---- Sessions ----
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    // "Remember me" keeps a login for up to 30 days, so the server must keep sessions that long too.
    ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
    session_start();
}

// ---- Error reporting (turn display_errors off in production) ----
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ---- Core includes ----
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
