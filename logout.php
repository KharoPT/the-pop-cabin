<?php
require_once __DIR__ . '/config/config.php';

// Wipe everything tied to this visit (login, role, guest-cart token, CSRF token),
// then hand the browser a fresh session id so nothing from the old one carries over.
$_SESSION = [];
session_regenerate_id(true);

setFlash('success', 'You have been signed out.');
redirect('index.php');
