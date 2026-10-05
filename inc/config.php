<?php
// ======================================================
// ADMIN CONFIGURATION
// ======================================================
// Credentials are read from ONE file: config/credentials.json
//   { "username": "...", "password": "...", "token": "..." }
//
// - password : admin login password. Plain text, or a hash from password_hash().
// - token    : ADMIN_TOKEN, for scripts: "Authorization: Bearer <token>".
//
// The file is git-ignored and blocked from the web by config/.htaccess.
// Copy config/credentials.example.json to config/credentials.json and edit it.
// DO NOT expose these values to frontend code.
// ======================================================
declare(strict_types=1);

$creds = json_decode((string) @file_get_contents(dirname(__DIR__) . '/config/credentials.json'), true);
$creds = is_array($creds) ? $creds : [];

define('ADMIN_USERNAME', (string) ($creds['username'] ?? 'admin'));
define('ADMIN_PASSWORD', (string) ($creds['password'] ?? ''));
define('ADMIN_TOKEN', (string) ($creds['token'] ?? ''));
unset($creds);

define('SESSION_TTL', 8 * 3600);
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads');
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);
const ALLOWED_EXT = ['json', 'txt', 'csv', 'png', 'jpg', 'jpeg', 'pdf', 'glb', 'xlsx'];
const EDITABLE_EXT = ['json', 'txt', 'csv'];
const IMAGE_EXT = ['png', 'jpg', 'jpeg'];
