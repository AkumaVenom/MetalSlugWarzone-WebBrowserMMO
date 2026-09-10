<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/audio.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, no-store, max-age=0');
header('Vary: Cookie');

function msw_audio_reply(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (!in_array($method, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    msw_audio_reply(405, ['ok' => false, 'error' => 'method_not_allowed']);
}
$user = msw_user();
if (!$user || (int)$user['is_bot'] !== 0) {
    msw_audio_reply(401, ['ok' => false, 'error' => 'session_expired']);
}
// Account identity comes only from the verified login session, never JSON/query.
$uid = (int)$user['id'];
$payload = null;
if ($method === 'POST') {
    $contentType = strtolower(trim(explode(';', (string)($_SERVER['CONTENT_TYPE'] ?? ''))[0]));
    if ($contentType !== 'application/json') {
        msw_audio_reply(415, ['ok' => false, 'error' => 'json_required']);
    }
    if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 4096) {
        msw_audio_reply(413, ['ok' => false, 'error' => 'request_too_large']);
    }
    $raw = file_get_contents('php://input', false, null, 0, 4097);
    if ($raw === false || strlen($raw) > 4096) {
        msw_audio_reply(413, ['ok' => false, 'error' => 'request_too_large']);
    }
    try {
        $input = json_decode($raw, true, 8, JSON_THROW_ON_ERROR);
        if (!is_array($input)) throw new InvalidArgumentException('A JSON object is required.');
    } catch (Throwable $e) {
        msw_audio_reply(400, ['ok' => false, 'error' => 'invalid_json']);
    }
    if (!isset($input['csrf']) || !is_string($input['csrf']) || !hash_equals(msw_csrf(), $input['csrf'])) {
        msw_audio_reply(419, ['ok' => false, 'error' => 'csrf_expired']);
    }
    // CSRF remains mandatory even when Origin is omitted by a browser. If an
    // Origin is present, require this exact host and port as an extra safeguard.
    $origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
    if ($origin !== '') {
        $parts = parse_url($origin);
        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        $originHost = is_array($parts) ? strtolower((string)($parts['host'] ?? '')) : '';
        $scheme = is_array($parts) ? strtolower((string)($parts['scheme'] ?? '')) : '';
        $originPort = is_array($parts) ? (int)($parts['port'] ?? ($scheme === 'https' ? 443 : 80)) : 0;
        $requestScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $requestParts = parse_url($requestScheme . '://' . $host);
        $requestHost = is_array($requestParts) ? strtolower((string)($requestParts['host'] ?? '')) : '';
        $requestPort = is_array($requestParts) ? (int)($requestParts['port'] ?? ($requestScheme === 'https' ? 443 : 80)) : 0;
        if ($originHost === '' || $originHost !== $requestHost || $originPort !== $requestPort || $scheme !== $requestScheme) {
            msw_audio_reply(403, ['ok' => false, 'error' => 'origin_rejected']);
        }
    }
    try {
        $payload = msw_audio_validate_payload($input);
    } catch (InvalidArgumentException $e) {
        msw_audio_reply(422, ['ok' => false, 'error' => 'invalid_sound_state', 'message' => $e->getMessage()]);
    }
}
// Never hold the PHP session lock during a checkpoint or block page navigation.
session_write_close();
try {
    $result = $payload === null ? ['ok' => true, 'state' => msw_audio_read_state($uid)] : msw_audio_save_state($uid, $payload);
    $result['available'] = true;
    msw_audio_reply($result['ok'] ? 200 : 409, $result);
} catch (Throwable $e) {
    $missing = $e instanceof mysqli_sql_exception && in_array((int)$e->getCode(), [1146, 1054], true);
    if (!$missing) error_log('[MSW audio] Account sound storage is unavailable.');
    msw_audio_reply(503, [
        'ok' => false, 'available' => false,
        'error' => $missing ? 'schema_update_required' : 'temporarily_unavailable',
        'message' => $missing ? 'Account sound saving needs the server Update / Repair.' : 'Account sound saving is temporarily unavailable.',
    ]);
}
