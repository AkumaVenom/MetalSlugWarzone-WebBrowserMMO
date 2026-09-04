<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/app.php';
date_default_timezone_set((string)$config['timezone']);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");

require_once __DIR__ . '/catalog.php';
require_once __DIR__ . '/game.php';
require_once __DIR__ . '/mother_base.php';

function msw_config(?string $key = null): mixed {
    global $config;
    return $key === null ? $config : ($config[$key] ?? null);
}

function msw_db(): mysqli {
    static $db = null;
    if ($db instanceof mysqli) return $db;
    $cfg = msw_config('db');
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $db = new mysqli((string)$cfg['host'], (string)$cfg['user'], (string)$cfg['pass'], (string)$cfg['name'], (int)$cfg['port']);
        $db->set_charset((string)$cfg['charset']);
        $db->query("SET SESSION sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    } catch (mysqli_sql_exception $e) {
        http_response_code(503);
        exit('Database unavailable. On a local installation, open _setup.php first.');
    }
    return $db;
}

function msw_stmt(string $sql, string $types = '', array $params = []): mysqli_stmt {
    $stmt = msw_db()->prepare($sql);
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt;
}

function msw_one(string $sql, string $types = '', array $params = []): ?array {
    $res = msw_stmt($sql, $types, $params)->get_result();
    $row = $res->fetch_assoc();
    return $row ?: null;
}

function msw_all(string $sql, string $types = '', array $params = []): array {
    return msw_stmt($sql, $types, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
}

function msw_e(string|int|float|null $v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function msw_base(): string { $d = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')); return $d === '/' ? '' : rtrim($d, '/'); }
function msw_url(string $path = ''): string { return msw_base() . '/' . ltrim($path, '/'); }
function msw_redirect(string $path): never { header('Location: ' . msw_url($path), true, 302); exit; }
function msw_is_post(): bool { return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }

function msw_csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return (string)$_SESSION['csrf'];
}
function msw_csrf_field(): string { return '<input type="hidden" name="csrf" value="' . msw_e(msw_csrf()) . '">'; }
function msw_verify_post(): void {
    if (!msw_is_post()) { http_response_code(405); exit('Method Not Allowed'); }
    $token = (string)($_POST['csrf'] ?? '');
    if ($token === '' || !hash_equals(msw_csrf(), $token)) { http_response_code(419); exit('Session validation failed.'); }
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin !== '') {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $ohost = parse_url($origin, PHP_URL_HOST) ?: '';
        if ($host === '' || strcasecmp(preg_replace('/:\d+$/', '', $host), (string)$ohost) !== 0) { http_response_code(403); exit('Origin rejected.'); }
    }
}

function msw_user_id(): int { return (int)($_SESSION['uid'] ?? 0); }
function msw_user(): ?array {
    $id = msw_user_id();
    if ($id <= 0) return null;
    return msw_one('SELECT * FROM users WHERE id=? LIMIT 1', 'i', [$id]);
}
function msw_require_user(): array { $u = msw_user(); if (!$u) msw_redirect('login.php'); return $u; }
function msw_flash(?string $message = null, string $kind = 'info'): ?array {
    if ($message !== null) { $_SESSION['flash'] = ['message'=>$message,'kind'=>$kind]; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return is_array($f) ? $f : null;
}

function msw_client_ip_hash(): string {
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    return hash('sha256', 'msw|' . $ip);
}

require_once __DIR__ . '/server_console.php';
require_once __DIR__ . '/dispatch_authority.php';
require_once __DIR__ . '/fob_world.php';
require_once __DIR__ . '/bots.php';
msw_console_register_request_traffic();
