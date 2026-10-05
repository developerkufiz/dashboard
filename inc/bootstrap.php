<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

ini_set('display_errors', '0');

$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_name('dash_sid');
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Strict']);
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
// Inline <style>/<script> are allowed only with this per-request nonce (see inc/ui.php)
define('CSP_NONCE', base64_encode(random_bytes(16)));
header("Content-Security-Policy: default-src 'self'; script-src 'nonce-" . CSP_NONCE . "'; style-src 'nonce-" . CSP_NONCE . "'; style-src-attr 'unsafe-inline'; img-src 'self' data:; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function role(): string
{
    if (($_SESSION['expires'] ?? 0) < time()) {
        return '';
    }
    return $_SESSION['role'] ?? ''; // '' | 'guest' | 'admin'
}

function is_admin(): bool
{
    return role() === 'admin';
}

function csrf(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(24));
}

function csrf_ok(?string $sent): bool
{
    return is_string($sent) && hash_equals(csrf(), $sent);
}

function start_session_as(string $role): void
{
    session_regenerate_id(true);
    $_SESSION = ['role' => $role, 'expires' => time() + SESSION_TTL, 'csrf' => bin2hex(random_bytes(24))];
}

function bearer_ok(): bool
{
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    return ADMIN_TOKEN !== '' && preg_match('/^Bearer (.+)$/', $h, $m) === 1 && hash_equals(ADMIN_TOKEN, $m[1]);
}

function password_ok(string $given): bool
{
    $stored = ADMIN_PASSWORD;
    if ($stored === '') {
        return false; // fail closed
    }
    return str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon')
        ? password_verify($given, $stored)
        : hash_equals($stored, $given);
}

function json_out(mixed $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

// Page guards
function require_role(): void
{
    if (role() === '') {
        header('Location: login.php');
        exit;
    }
}

function require_admin_page(): void
{
    require_role();
    if (!is_admin()) {
        header('Location: index.php');
        exit;
    }
}

// API guards. Cookie sessions must send the CSRF token on writes; Bearer tokens are not ambient.
function require_admin_api(): void
{
    if (bearer_ok()) {
        return;
    }
    if (!is_admin()) {
        json_out(['error' => 'Authentication required'], 401);
    }
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true) && !csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
        json_out(['error' => 'Invalid CSRF token'], 403);
    }
}

// Login throttle: 5 failures / 15 min / IP, kept in a temp file (no database).
function throttle_path(): string
{
    return sys_get_temp_dir() . '/dash_login_' . md5($_SERVER['REMOTE_ADDR'] ?? '') . '.json';
}

function throttled(): bool
{
    $t = json_decode((string) @file_get_contents(throttle_path()), true);
    return is_array($t) && ($t['reset'] ?? 0) > time() && ($t['count'] ?? 0) >= 5;
}

function throttle_fail(): void
{
    $t = json_decode((string) @file_get_contents(throttle_path()), true);
    $t = is_array($t) && ($t['reset'] ?? 0) > time() ? $t : ['count' => 0, 'reset' => time() + 900];
    $t['count']++;
    @file_put_contents(throttle_path(), json_encode($t));
}

function throttle_clear(): void
{
    @unlink(throttle_path());
}
