<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';

// POST + CSRF so a third-party page cannot log the user out.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok($_POST['csrf'] ?? null)) {
    $_SESSION = [];
    setcookie(session_name(), '', ['expires' => 1, 'path' => '/', 'secure' => !empty($_SERVER['HTTPS']), 'httponly' => true, 'samesite' => 'Strict']);
    session_destroy();
}
header('Location: login.php');
