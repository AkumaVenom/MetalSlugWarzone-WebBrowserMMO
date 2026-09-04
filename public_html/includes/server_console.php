<?php
declare(strict_types=1);

/**
 * Local WorldServer-style activity feed.
 *
 * Security/privacy contract:
 * - Only authenticated human accounts are ever written.
 * - No password, CSRF token, message body, cookie or POST payload is persisted.
 * - Movement/presence/state-poll endpoints are suppressed completely.
 * - This is an application activity stream, not an error/exception log.
 * - The backing directory is denied to HTTP and is consumed only from the
 *   server filesystem by serverconsole.bat/serverconsole.ps1.
 */


function msw_console_clip(string $value, int $limit): string {
    if ($limit <= 0) return '';
    if (function_exists('mb_substr')) return (string)mb_substr($value, 0, $limit, 'UTF-8');
    return substr($value, 0, $limit);
}

function msw_console_dir(): string {
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '_server_console';
}

function msw_console_event_path(): string {
    return msw_console_dir() . DIRECTORY_SEPARATOR . 'events.ndjson';
}

function msw_console_suppressed_routes(): array {
    return [
        'map_move.php',
        'map_presence.php',
        'mother_base_move.php',
        'mother_base_presence.php',
        'pvp_state.php',
    ];
}

function msw_console_route_name(): string {
    $script = basename((string)($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''));
    return strtolower($script);
}

function msw_console_is_suppressed_route(?string $route = null): bool {
    $route = strtolower((string)($route ?? msw_console_route_name()));
    return in_array($route, msw_console_suppressed_routes(), true);
}

function msw_console_human_identity(int $uid): ?array {
    static $cache = [];
    if ($uid <= 0) return null;
    if (array_key_exists($uid, $cache)) return $cache[$uid];
    try {
        $row = msw_one('SELECT id,username,is_bot FROM users WHERE id=? LIMIT 1', 'i', [$uid]);
        if (!$row || (int)($row['is_bot'] ?? 0) !== 0) return $cache[$uid] = null;
        return $cache[$uid] = ['id'=>(int)$row['id'], 'username'=>(string)$row['username']];
    } catch (Throwable $e) {
        // The admin console must never become an error-reporting surface.
        return $cache[$uid] = null;
    }
}

function msw_console_rotate_locked(string $path): void {
    clearstatcache(true, $path);
    if (!is_file($path) || (int)@filesize($path) < 8 * 1024 * 1024) return;
    for ($i = 3; $i >= 1; $i--) {
        $src = $path . '.' . $i;
        $dst = $path . '.' . ($i + 1);
        if ($i === 3 && is_file($src)) @unlink($src);
        elseif (is_file($src)) @rename($src, $dst);
    }
    @rename($path, $path . '.1');
}

function msw_console_write(array $event): void {
    try {
        $dir = msw_console_dir();
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) return;
        $path = msw_console_event_path();
        $lockPath = $dir . DIRECTORY_SEPARATOR . 'events.lock';
        $lock = @fopen($lockPath, 'c');
        if (!$lock) return;
        if (!@flock($lock, LOCK_EX)) { @fclose($lock); return; }
        msw_console_rotate_locked($path);
        $line = json_encode($event, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (is_string($line)) @file_put_contents($path, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        @flock($lock, LOCK_UN);
        @fclose($lock);
    } catch (Throwable $e) {
        // Logging is intentionally fail-silent and can never break gameplay.
    }
}

function msw_console_event_for_user(
    int $uid,
    string $category,
    string $action,
    string $message,
    array $meta = [],
    ?string $route = null
): void {
    $identity = msw_console_human_identity($uid);
    if (!$identity) return;
    $routeName = strtolower((string)($route ?? msw_console_route_name()));
    if (msw_console_is_suppressed_route($routeName)) return;

    $cleanMeta = [];
    foreach ($meta as $key => $value) {
        if (!is_string($key) || !preg_match('/^[a-z0-9_]{1,32}$/i', $key)) continue;
        if (is_bool($value) || is_int($value) || is_float($value) || $value === null) $cleanMeta[$key] = $value;
        elseif (is_string($value)) $cleanMeta[$key] = msw_console_clip($value, 160);
    }

    msw_console_write([
        'ts' => date(DATE_ATOM),
        'category' => strtoupper(msw_console_clip(trim($category), 16)),
        'action' => strtoupper(msw_console_clip(trim($action), 20)),
        'player_id' => (int)$identity['id'],
        'player' => msw_console_clip((string)$identity['username'], 40),
        'ip' => msw_console_clip((string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 64),
        'route' => '/' . ltrim($routeName, '/'),
        'message' => msw_console_clip(trim($message), 320),
        'meta' => $cleanMeta,
    ]);
}

function msw_console_page_label(string $route): string {
    static $labels = [
        'index.php'=>'Landing Page', 'dashboard.php'=>'Command Dashboard', 'map_select.php'=>'Warzone Select',
        'map.php'=>'Warzone', 'battle.php'=>'Combat Engagement', 'missions.php'=>'Combat Missions',
        'sidequests.php'=>'Field Contracts', 'commanders.php'=>'Rival Commanders', 'bosses.php'=>'Boss Operations',
        'base.php'=>'Mother Base Command', 'mother_base.php'=>'Physical Mother Base', 'staff.php'=>'Staff Management',
        'rd.php'=>'R&D Laboratory', 'dispatch.php'=>'Combat Dispatch', 'strategic.php'=>'Strategic Systems',
        'fob.php'=>'FOB Command Router', 'fob_globe.php'=>'FOB Globe Deployment', 'fob_skin.php'=>'FOB Skin Deployment',
        'fob_world.php'=>'FOB Overview World', 'fob_infiltration.php'=>'FOB Infiltration Network', 'fob_target.php'=>'Enemy FOB Command',
        'fob_dispatch.php'=>'FOB Staff Strike Ledger', 'fob_attack.php'=>'FOB Raid Action', 'fob_result.php'=>'FOB Raid Report', 'pvp.php'=>'PvP Network',
        'pvp_match.php'=>'PvP Match', 'community.php'=>'Community Network', 'friends.php'=>'Friends',
        'messages.php'=>'Direct Messages', 'strike_forces.php'=>'Strike Forces', 'rankings.php'=>'Rankings',
        'profile.php'=>'Commander Profile', 'ai_commanders.php'=>'AI Network', 'logout.php'=>'Logout',
    ];
    return $labels[$route] ?? ($route !== '' ? $route : 'Game Request');
}

function msw_console_register_request_traffic(): void {
    $route = msw_console_route_name();
    if ($route === '' || msw_console_is_suppressed_route($route)) return;
    $uid = msw_user_id();
    if ($uid <= 0) return;
    $identity = msw_console_human_identity($uid);
    if (!$identity) return;
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if (!in_array($method, ['GET','POST','HEAD'], true)) return;
    $started = microtime(true);
    $ip = msw_console_clip((string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 64);

    register_shutdown_function(static function() use ($identity, $route, $method, $started, $ip): void {
        $status = http_response_code();
        if ($status >= 400) return;
        $last = error_get_last();
        if (is_array($last) && in_array((int)($last['type'] ?? 0), [E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR], true)) return;
        msw_console_write([
            'ts' => date(DATE_ATOM),
            'category' => 'WEB',
            'action' => $method,
            'player_id' => (int)$identity['id'],
            'player' => msw_console_clip((string)$identity['username'], 40),
            'ip' => $ip,
            'route' => '/' . $route,
            'message' => msw_console_page_label($route),
            'meta' => ['ms'=>(int)round((microtime(true)-$started)*1000)],
        ]);
    });
}
