# Local WorldServer Console — v0.3.5

`serverconsole.bat` opens the server-operator activity console. It is designed for the server PC only and does not expose a browser administration page.

## What the console shows

Each line contains local time, a color-coded channel, the authenticated human commander, user ID, remote IP, action and a compact description. Normal successful page traffic appears under `WEB`; gameplay actions use dedicated channels such as `AUTH`, `COMBAT`, `RECOVERY`, `MISSION`, `R&D`, `BASE`, `DISPATCH`, `STRATEGIC`, `FOB`, `PVP`, `SOCIAL` and `PROFILE`.

Examples of intended information include successful login/logout, page requests, attacks and engagement results, Fulton success/failure as a gameplay outcome, manufacturing, staff reassignment, dispatch deployment/result, strategic project start/claim, FOB results, PvP turns, friend/Strike Force activity and message transmission metadata. Direct-message text is never shown.

## What is intentionally excluded

The console does **not** show warzone movement, Mother Base movement, presence heartbeats or PvP state polling. It also does not function as an error console: Apache/PHP/MySQL errors, exceptions, stack traces, warnings and debug logs are not ingested.

The hard-suppressed routes are:

- `map_move.php`
- `map_presence.php`
- `mother_base_move.php`
- `mother_base_presence.php`
- `pvp_state.php`

## Local-only design

The feed is `_server_console/events.ndjson` at the project root, outside `public_html`. No HTTP/SSE/WebSocket/API endpoint exists for it. `_server_console/.htaccess` contains `Require all denied` as defense in depth.

Only authenticated human users are accepted by the event helper. `users.is_bot=1` rows are rejected even if a server-side code path attempts to emit an event.

## Privacy boundary

Never persisted by this subsystem:

- passwords or password hashes;
- cookies/session IDs;
- CSRF tokens;
- raw POST/request payloads;
- direct-message bodies;
- movement coordinates/commands;
- PHP/Apache/MySQL error content.

For traffic identification, the local operator console does include the player's username, database user ID and connection IP address.

## Operation

1. Keep the extracted release structure intact so `serverconsole.bat`, `serverconsole.ps1`, `public_html/`, and `_server_console/` remain siblings.
2. Start Apache/MySQL normally.
3. Double-click `serverconsole.bat` on the server PC.
4. The console replays up to the latest 80 activity events, then follows new events live.
5. Press `C` to clear the display without deleting the feed. Press `Q` to close the console.

The event feed rotates automatically at 8 MiB and keeps three prior generations (`events.ndjson.1` through `.3`). Rotation does not touch game state or MySQL.

## Acceptance checks

Use two human accounts from separate browsers/PCs if available. Confirm each account's requests identify the correct commander/IP. Exercise R&D, combat, Fulton recovery, staff assignment, dispatch, FOB/PvP and social actions. Then continuously move in a warzone and Mother Base for at least 30 seconds and confirm movement/presence lines never appear. Leave AI simulation active and confirm autonomous commander actions never appear in this console.
