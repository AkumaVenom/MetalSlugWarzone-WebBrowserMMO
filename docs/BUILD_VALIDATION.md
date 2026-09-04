# Build Validation — v0.3.5 Local WorldServer Console

Parent baseline: **v0.3.4 Level-1 Fulton Manufacturing — runtime-confirmed by the user**  
Schema revision: **4 (unchanged)**

Static and isolated subsystem validation completed on the candidate source tree:

- **47/47 PHP files** pass `php -l`.
- **1/1 JavaScript file** passes `node --check`.
- CSS structure remains balanced at **401 opening / 401 closing braces**.
- `serverconsole.ps1` has balanced static structure (**59/59 braces, 81/81 parentheses**) and `serverconsole.bat` resolves the sibling PowerShell renderer path. A native Windows PowerShell runtime was not available in the Linux build container, so final renderer execution remains an XAMPP/server-PC acceptance item.
- All **54 literal PHP include/require references** resolve.
- All **28 literal `msw_url()` PHP references** found by the static scanner resolve.
- `public_html/includes/server_console.php` stores the feed at package-root `_server_console/`, outside `public_html`.
- `_server_console/.htaccess` contains `Require all denied` as defense in depth.
- Hard suppression contains exactly the required high-noise/non-action endpoints: `map_move.php`, `map_presence.php`, `mother_base_move.php`, `mother_base_presence.php`, and `pvp_state.php`.
- Those five endpoints contain **no direct console action hooks**.
- Isolated logger test: a human event is written; a `users.is_bot=1` identity is rejected; an event attempted from `map_move.php` is rejected.
- Isolated traffic test: a successful authenticated `GET /dashboard.php` is written; `map_move.php` traffic is suppressed; an HTTP 404 completion is not written.
- Concurrency test: **60/60 parallel event writes** were preserved as valid unique NDJSON records under file locking.
- Rotation test: an event written after the feed exceeded **8 MiB** moved the previous feed to `events.ndjson.1` and wrote the new event cleanly to the fresh active feed.
- Direct-message console hook receives only precomputed character count/recipient metadata; the message-body variable is not passed to the logging function.
- The logger does not install PHP error/exception handlers and does not call `error_log()` or ingest Apache/PHP/MySQL logs.
- Combat action/result events are queued until the authoritative battle transaction commits, avoiding false-positive activity lines after rollback.
- Schema revision remains **4**; no SQL/database migration is introduced.
- v0.3.4 basic Fulton manufacturing remains present at **R&D 1**, with existing higher thresholds **4 / 8 / 15** preserved.
- **31/31 runtime images** are byte-identical to the v0.3.4 runtime-confirmed baseline.
- Package source tree contains **0 `source_assets/` directories** and **0 nested/source ZIP archives**.

## Runtime acceptance boundary

The build container cannot execute Windows `cmd.exe`/Windows PowerShell or the user's real Apache/MariaDB/browser stack. Final promotion therefore requires the server-PC checks in `docs/XAMPP_TEST_PLAN.md`: launch `serverconsole.bat`, verify real color rendering/live following, verify human/IP attribution, exercise representative gameplay actions, and confirm prolonged warzone/Mother Base movement produces no console lines.
