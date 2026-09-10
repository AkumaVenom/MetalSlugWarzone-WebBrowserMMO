<?php
declare(strict_types=1);

/**
 * Account-only sound preferences and per-track checkpoints (schema revision 10).
 * This module never creates schema or reads/writes gameplay state. Page rendering
 * is read-only; the authenticated audio_state.php endpoint owns all saves.
 */
function msw_audio_defaults(): array {
    return [
        'muted' => false,
        'musicVolume' => 0.55,
        'effectsVolume' => 0.75,
        'settingsRevision' => 0,
        'positions' => (object)[],
        'positionRevisions' => (object)[],
    ];
}

/** The authored soundtrack manifest is the only accepted track-ID authority. */
function msw_audio_allowed_tracks(): array {
    static $tracks = null;
    if ($tracks !== null) return $tracks;
    $manifest = require __DIR__ . '/../config/audio.php';
    $tracks = [];
    foreach (($manifest['tracks'] ?? []) as $key => $track) {
        if (is_string($key) && preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/D', $key) && is_array($track)) {
            $tracks[$key] = $track;
        }
    }
    return $tracks;
}

function msw_audio_read_state(int $uid): array {
    if ($uid <= 0) throw new InvalidArgumentException('An account is required.');
    $state = msw_audio_defaults();
    $row = msw_one('SELECT muted,music_volume,effects_volume,settings_revision FROM audio_preferences WHERE user_id=? LIMIT 1', 'i', [$uid]);
    if ($row) {
        $state['muted'] = (int)$row['muted'] === 1;
        $state['musicVolume'] = max(0.0, min(1.0, (float)$row['music_volume']));
        $state['effectsVolume'] = max(0.0, min(1.0, (float)$row['effects_volume']));
        $state['settingsRevision'] = (int)$row['settings_revision'];
    }
    // Always read both tables, including for first-time accounts: an incomplete
    // upgrade must produce an honest unavailable flag instead of claiming a save.
    $positions = [];
    $positionRevisions = [];
    $allowed = msw_audio_allowed_tracks();
    foreach (msw_all('SELECT track_id,position_seconds,position_revision FROM audio_track_positions WHERE user_id=?', 'i', [$uid]) as $position) {
        $id = (string)$position['track_id'];
        if (isset($allowed[$id])) {
            $positions[$id] = max(0.0, min(86400.0, (float)$position['position_seconds']));
            $positionRevisions[$id] = (int)$position['position_revision'];
        }
    }
    $state['positions'] = (object)$positions;
    $state['positionRevisions'] = (object)$positionRevisions;
    return $state;
}

function msw_audio_bootstrap(?array $u): array {
    $uid = (int)($u['id'] ?? 0);
    $data = [
        'accountId' => $uid,
        'csrf' => msw_csrf(),
        'endpoint' => msw_url('audio_state.php'),
        'available' => false,
        'state' => msw_audio_defaults(),
    ];
    if ($uid <= 0) return $data;
    try {
        $data['state'] = msw_audio_read_state($uid);
        $data['available'] = true;
    } catch (Throwable $e) {
        // Older installs remain playable until the administrator runs repair.
        $data['saveMessage'] = 'Account sound saving is unavailable. The server may need Update / Repair.';
    }
    return $data;
}

/** Validate the whole request before opening a transaction or writing any row. */
function msw_audio_validate_payload(array $input): array {
    if (array_diff(array_keys($input), ['csrf', 'settings', 'position'])) {
        throw new InvalidArgumentException('Unknown sound request field.');
    }
    if (!array_key_exists('settings', $input) && !array_key_exists('position', $input)) {
        throw new InvalidArgumentException('A sound setting or track position is required.');
    }
    $validated = [];
    if (array_key_exists('settings', $input)) {
        $s = $input['settings'];
        if (!is_array($s) || count($s) !== 4 || array_diff(['muted', 'musicVolume', 'effectsVolume', 'revision'], array_keys($s))) {
            throw new InvalidArgumentException('Complete sound settings and their revision are required.');
        }
        if (!is_bool($s['muted'])) throw new InvalidArgumentException('Muted must be true or false.');
        foreach (['musicVolume', 'effectsVolume'] as $key) {
            if ((!is_float($s[$key]) && !is_int($s[$key])) || !is_finite((float)$s[$key]) || $s[$key] < 0 || $s[$key] > 1) {
                throw new InvalidArgumentException('Sound volumes must be between zero and one.');
            }
            $s[$key] = round((float)$s[$key], 3);
        }
        if (!is_int($s['revision']) || $s['revision'] < 0 || $s['revision'] > 2147483646) {
            throw new InvalidArgumentException('Invalid sound settings revision.');
        }
        $validated['settings'] = $s;
    }
    if (array_key_exists('position', $input)) {
        $p = $input['position'];
        if (!is_array($p) || count($p) !== 3 || !array_key_exists('trackId', $p) || !array_key_exists('seconds', $p) || !array_key_exists('revision', $p)) {
            throw new InvalidArgumentException('A track identifier, position and revision are required.');
        }
        if (!is_string($p['trackId']) || !isset(msw_audio_allowed_tracks()[$p['trackId']])) {
            throw new InvalidArgumentException('Unknown music track.');
        }
        if ((!is_int($p['seconds']) && !is_float($p['seconds'])) || !is_finite((float)$p['seconds']) || $p['seconds'] < 0 || $p['seconds'] > 86400) {
            throw new InvalidArgumentException('Invalid music position.');
        }
        if (!is_int($p['revision']) || $p['revision'] < 0 || $p['revision'] > 2147483646) {
            throw new InvalidArgumentException('Invalid music position revision.');
        }
        $validated['position'] = ['trackId' => $p['trackId'], 'seconds' => round((float)$p['seconds'], 3), 'revision' => $p['revision']];
    }
    return $validated;
}

/**
 * Independent revision checks protect settings and each individual track from
 * delayed navigation/tab checkpoints. A stale position cannot cancel a valid
 * explicit mute/volume change: it is discarded with positionAccepted=false.
 */
function msw_audio_save_state(int $uid, array $payload): array {
    if ($uid <= 0) throw new InvalidArgumentException('An account is required.');
    $payload = msw_audio_validate_payload($payload);
    $db = msw_db();
    $db->begin_transaction();
    try {
        if (isset($payload['settings'])) {
            $s = $payload['settings'];
            msw_stmt('INSERT IGNORE INTO audio_preferences(user_id) VALUES(?)', 'i', [$uid]);
            $existing = msw_one('SELECT settings_revision FROM audio_preferences WHERE user_id=? FOR UPDATE', 'i', [$uid]);
            if (!$existing || (int)$existing['settings_revision'] !== $s['revision']) {
                $db->rollback();
                return ['ok' => false, 'error' => 'settings_conflict', 'state' => msw_audio_read_state($uid)];
            }
            msw_stmt('UPDATE audio_preferences SET muted=?,music_volume=?,effects_volume=?,settings_revision=settings_revision+1 WHERE user_id=? AND settings_revision=?',
                'iddii', [$s['muted'] ? 1 : 0, $s['musicVolume'], $s['effectsVolume'], $uid, $s['revision']]);
        }
        $positionAccepted = null;
        if (isset($payload['position'])) {
            $p = $payload['position'];
            if ($p['revision'] === 0) {
                msw_stmt('INSERT IGNORE INTO audio_track_positions(user_id,track_id) VALUES(?,?)', 'is', [$uid, $p['trackId']]);
            }
            $existing = msw_one('SELECT position_revision FROM audio_track_positions WHERE user_id=? AND track_id=? FOR UPDATE', 'is', [$uid, $p['trackId']]);
            $positionAccepted = $existing && (int)$existing['position_revision'] === $p['revision'];
            if (!$positionAccepted && !isset($payload['settings'])) {
                $db->rollback();
                return ['ok' => false, 'error' => 'position_conflict', 'positionAccepted' => false, 'state' => msw_audio_read_state($uid)];
            }
            if ($positionAccepted) {
                msw_stmt('UPDATE audio_track_positions SET position_seconds=?,position_revision=position_revision+1,updated_at=CURRENT_TIMESTAMP WHERE user_id=? AND track_id=? AND position_revision=?',
                    'disi', [$p['seconds'], $uid, $p['trackId'], $p['revision']]);
            }
        }
        // Read both tables before commit so a partial/older installation cannot
        // accept half of a save and only then report the missing schema.
        $state = msw_audio_read_state($uid);
        $db->commit();
        $result = ['ok' => true, 'state' => $state];
        if ($positionAccepted !== null) $result['positionAccepted'] = $positionAccepted;
        return $result;
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }
}
