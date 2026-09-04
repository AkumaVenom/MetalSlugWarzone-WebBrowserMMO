# XAMPP Runtime Acceptance Plan — v0.3.5 Local WorldServer Console

v0.3.5 is an observability/polish update over the **runtime-confirmed v0.3.4 Level-1 Fulton Manufacturing** baseline. Schema remains revision **4** and no database reset is required.

## 1. Backup and Update / Repair — RELEASE BLOCKING

1. Back up the current database.
2. Replace the current runtime project files with v0.3.5 while preserving the release folder structure.
3. Open `_setup.php` locally and run **Update / Repair**. Do **not** Fresh Install.
4. Run **Confirm Installation** and verify schema revision **4**, exactly **1,000 persistent autonomous commanders**, balanced six-warzone population, and `autonomous_skin_variety` reports every warzone mixed.
5. Confirm no human/bot XP, coordinates, rosters, Mother Bases, resources, dispatches, FOB history, PvP records or inventories are reset.

## 2. Local WorldServer console startup — RELEASE BLOCKING

1. On the server PC, double-click `serverconsole.bat`.
2. Confirm a dedicated command window opens with the **METAL SLUG WARZONE // LOCAL WORLDSERVER ACTIVITY CONSOLE** banner and color-channel legend.
3. Confirm the console states that human player traffic/gameplay actions are shown and movement/presence/error logs are excluded.
4. Confirm `C` clears/redraws the console and `Q` exits cleanly.
5. Confirm `_server_console/events.ndjson` exists beside `public_html`, not inside it.

## 3. Human-only traffic capture — RELEASE BLOCKING

1. Log in with a normal human commander from a browser.
2. Browse Dashboard, R&D, Missions, Staff, FOB, PvP, Friends and Profile.
3. Confirm each successful page request produces a `WEB` line containing the correct human username, user ID, client IP, HTTP method and route.
4. Use a second human account from another PC/browser if available and confirm its lines identify that commander/IP independently.
5. Leave autonomous commander simulation active and browse the AI Network. Confirm **no bot activity is emitted as player console events**.
6. Confirm visiting unauthenticated pages before login does not create human traffic lines.

## 4. Movement and polling suppression — RELEASE BLOCKING

1. Enter a warzone and continuously move with WASD/arrow controls for at least 30 seconds.
2. Confirm **no `map_move.php` or `map_presence.php` lines appear**, and no coordinates/directions are printed.
3. Enter a physical Mother Base and continuously move for at least 30 seconds.
4. Confirm **no `mother_base_move.php` or `mother_base_presence.php` lines appear**.
5. Open a PvP match and leave its live state watcher running. Confirm **no `pvp_state.php` polling lines appear**.
6. Normal non-movement page traffic such as opening `map.php`, `mother_base.php` or `pvp_match.php` may still appear under `WEB`.

## 5. Color-coded gameplay actions — RELEASE BLOCKING

Perform at least one valid successful action in each available system and verify an appropriately colored line appears:

- `AUTH`: successful login and logout.
- `COMBAT`: committed attack and resolved engagement.
- `RECOVERY`: actual Fulton success/failure gameplay result after a Fulton unit is consumed.
- `MISSION`: mission/contract/rival/boss engagement start.
- `R&D`: successful manufacture.
- `BASE`: staff assignment or Mother Base redeployment.
- `DISPATCH`: deployment and later result settlement.
- `STRATEGIC`: project start/claim when available.
- `FOB`: completed raid result.
- `PVP`: match start/committed turn.
- `SOCIAL`: friend, direct-message metadata, or Strike Force action.
- `PROFILE`: operative change when used.

Confirm action text is concise and readable, not raw database/request dumps.

## 6. No technical error logging / privacy boundary — RELEASE BLOCKING

1. Submit an invalid gameplay request that produces a normal browser validation/flash message. Confirm the console does not show exception/error/stack-trace content.
2. Request a nonexistent authenticated PHP route/page that returns HTTP 4xx where practical. Confirm the failed request is not emitted as a successful `WEB` event.
3. Send a direct message containing a distinctive phrase. Confirm the console shows only recipient metadata/character count and **does not show the message body**.
4. Inspect `_server_console/events.ndjson` and confirm there are no passwords, password hashes, session IDs, cookies, CSRF tokens or raw POST payloads.
5. Confirm Apache/PHP/MySQL error logs are not read or mirrored into the console.

## 7. Preserved v0.3.4 Level-1 Fulton manufacturing — RELEASE BLOCKING

1. Use a commander whose R&D Team is Level 1.
2. Confirm basic **Fulton Recovery Pack** remains manufacturable at R&D 1 for **60 Common Metal + 40 Fuel → x4**.
3. Confirm Fulton+ remains locked below R&D 4, Cargo Fulton below R&D 8, and Wormhole Fulton below R&D 15.
4. Manufacture and consume a basic Fulton in normal battle; verify persistent inventory/resource behavior remains correct.

## 8. Persistence/performance regression — RELEASE BLOCKING

1. Restart Apache/MySQL while the console is closed, then reopen `serverconsole.bat`; game progression must remain unchanged.
2. Confirm console file rotation/creation does not alter database state.
3. Keep the console open while multiple players browse/act and verify gameplay remains responsive.
4. Confirm logging failure cannot block gameplay by temporarily making the feed file read-only in a disposable test copy; requests should continue to function without exposing technical errors in the game UI.

## 9. Runtime-only packaging

Confirm the candidate contains no `source_assets/` directory and no nested/source `.zip` archives. All accepted runtime images must remain unchanged from v0.3.4.

## Acceptance

Promote v0.3.5 only after real XAMPP testing confirms the console is **local-only, human-only, color-coded, useful for real gameplay traffic/actions, completely silent for map movement/presence polling, and not an error-log surface**, while v0.3.4 Fulton progression and all existing gameplay remain intact.
