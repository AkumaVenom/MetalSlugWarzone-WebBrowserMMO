# v0.8.4.6 — Access-denied setup repair

Verified on PHP 8.2.32 and MariaDB 10.11.18 using real HTTP and database calls:

- **39 setup checks with Access denied injected at the Windows adapter boundary.**
  Fresh Install, upgrade and confirmation complete; all existing database row
  hashes survive repair and background-only retry. RESET and CSRF guards remain.
  The shared maintenance lock is released before startup. The status endpoint
  retains the native error without claiming that the background engine is running.
- **33 normal setup checks** pass separately. These overlap the failure suite;
  they are separate scenarios, not 72 unique requirements.
- **12 background-engine and 19 deployed HTTP checks** pass again, including real
  AI XP growth and two defenders' committed strikes with zero HTTP requests,
  duplicate rejection, Apache-port lifecycle and immutable results after restart.
- **7 native-adapter contract checks** run the shipped JScript with substituted
  COM objects: correct identity/logon flags, no saved password or elevated run
  level, separate registration/start, and exact Access denied/HRESULT reporting.
- **18 PHP task-definition/account/path checks** pass; all three generated task
  modes validate against Microsoft's official Task Scheduler XSD.
- All **69 PHP files**, JavaScript files and Python scripts parse successfully.

Results: `tests/setup_0846_results.json`. Run the setup suite with `--startup-error`
for the isolated Access-denied scenario; the production code has no test bypass.

**Native Windows registration is untested in this Linux workspace.** COM contracts
and XML validation do not prove that a specific Windows host grants scheduling
permission. Actual setup must show Running automatically on that host. The
background tests run PHP independently; no production database was accessed.

The complete release retains every original v0.8.4.2 package file, all 67 image
hashes and the original msw.css/msw.js. Schema remains 9. The game engine and
canonical strike/AI authority are unchanged by this setup repair.

Historical validation follows.

---

# v0.8.4.5 — Automatic Offline World

Environment: PHP **8.2.32**, mysqli/mysqlnd/mbstring; MariaDB **10.11.18**;
real independent PHP processes and actual HTTP routes in an isolated test world.

- **41 database checks passed:** canonical strike settlement, rollback/retry,
  resource conservation, concurrent idempotency, training starvation, shared
  throttle, initial and later stale-timer recovery, and complete 1,000-commander
  progression across accelerated one-, two- and seven-day backlog fixtures.
- **33 setup HTTP checks passed:** the real setup forms, SQL-clock report, full
  population seeding, non-destructive repeated repair, read-only confirmation and
  the Fresh Install RESET guard. Windows task installation was not invoked by
  this Linux database-only suite.
- **12 reproducible automatic-engine checks plus 19 HTTP integration checks passed**
  on the final engine: no premature Running status while the maintenance lock was
  held; real offline AI XP gains; two offline defenders' canonical arrivals;
  duplicate-process refusal; stale-throttle recovery; web-server stop/start;
  persistent raid bindings; deployed subfolder URLs; login/session/CSRF/origin;
  top-100 payload; incoming/replay routing; live setup health and HTTP protection
  of generated local settings. The test engine received **zero HTTP requests**
  during its initial unattended progression stage. Final local Rankings response
  was **7.06 ms**, before any browser simulation work.
- Additional isolated integration verified a real MariaDB stop/restart, the same
  background process reconnecting and resuming XP, and code-change retirement /
  reload between batches. These are local process checks, not native Windows
  Task Scheduler execution. Overlapping checks are not summed into an inflated total.
- **13 JavaScript behavior checks passed:** actual shipped script in the existing
  DOM/timer fixture; text-only names; authenticated requests; hidden-tab cadence;
  retry/reconnect; expired session; invalid-clock response; server-owned arrival
  results; stable rows; report links and back-forward cache recovery.
- Normal-user S4U and built-in-service task XML both validated against Microsoft's
  official Task Scheduler XSD, including path escaping, windowless PHP command,
  indefinite one-minute repetition, IgnoreNew and no 72-hour task time limit.

## Material limits

The workspace cannot run Windows Task Scheduler, so **native Windows registration,
permissions and automatic wake-up still require the setup check on the XAMPP PC**.
The engine tests invoke the exact PHP entry point independently and use a real PHP
HTTP server for the Apache-port liveness contract; they do not claim to run Windows
Apache. Chromium could not start because the workspace prohibits its required
Unix sockets; HTTP/script tests are not real browser rendering or screenshot QA.
The real game database was not accessed or deployed. Accelerated backlog tests
are not a multi-day wall-clock soak.

All **68 PHP files**, runtime/test JavaScript and Python test scripts parse.
Schema remains **9**. The prior clock-alias fix is retained. All 67 images and the
original `msw.css` / `msw.js` remain unchanged. Runtime-generated local settings,
health, lock files, dependency binaries and temporary test worlds are excluded
from delivery. The manual worldworker PHP/batch launchers are removed.

Reproducible suites: `tests/background_world_regression.py`,
`tests/automatic_http_regression.py` (its HTTP stage),
`tests/world_runtime_regression.php`, `tests/setup_http_regression.py` and
`tests/world_runtime_client.test.js`. Current results are recorded in
`tests/automatic_world_results.json`.

Historical release-specific evidence follows. New native automatic startup is
not inferred from any earlier manual-worker result.

---

# v0.8.4.4 — Setup Confirmation Fix

**33 setup HTTP regression checks passed** on PHP 8.2.32 (mysqli/mysqlnd,
mbstring) and MariaDB 10.11.18. The actual localhost `_setup.php` was served by
PHP's HTTP server; requests used its real session token and posted the existing
forms. The previous reserved alias reproduced the user's exact 1064 syntax error.

The new suite exercised initial Update / Repair, complete confirmation, 1,000
seeded AI commanders plus an existing human account, a missing arrival index,
schema-8-to-9 repair, repeated repair/confirmation, the Fresh Install RESET guard
and a real fresh install on the disposable database. Every table's row content
was hashed before/after confirmation; all hashes stayed identical. All gameplay
table hashes also stayed identical across the additive repair, including modified
human and AI levels, XP, resources, inventory and staff. Setup emitted no PHP
warnings, fatal errors or deprecations.

The v0.8.4.3 HTTP checks below did **not** submit Confirm Installation and therefore
did not validate the faulty report query. They remain historical validation of
the gameplay paths they list. This release adds direct setup-route coverage.

All **66 PHP files** pass syntax validation, and the new Python runner parses.
The setup test result is retained in `tests/setup_regression_results.json`.

Runtime changes from v0.8.4.3 are only `_setup.php` and the version value in
`config/app.php`. Schema remains 9; the world-runtime code, all 67 images, CSS,
JavaScript and worker remain unchanged. Test dependencies, databases and logs are
excluded. Windows XAMPP and the user's live database were not exercised.

Run `python tests/setup_http_regression.py --php <php-executable>` from the release
root with a local MariaDB/MySQL test service. The suite uses only a random database
that it creates and drops; it does not use the installed game database.

See `../UPGRADE_v0.8.4.4.md` for the single-file fix and complete-package upgrade.

---

# v0.8.4.3 — Durable Strike Arrivals + Continuous AI

Status: local implementation/regression validation passed. Existing-world XAMPP
and real-browser acceptance remains required before promoting this candidate.

## Baseline defects reproduced

The supplied v0.8.4.2 source was exercised against an isolated MariaDB database.
With the host DB default timezone differing from PHP's configured Melbourne time,
a PHP-authored strike deadline was past its countdown while the SQL due predicate
still returned false. The old defender-side call resolved zero incoming operations.
The old training selection returned zero trained staff while a higher-level staff
member still had room below its assignment-stat cap.

Source inspection also confirmed that Rankings had no recurring pulse and most
other pages could not keep AI running while left open. These are reproducible code
paths; the user's live database and PHP error logs were not supplied, so the exact
combination active on that host has not been independently observed.

## Database regression

Environment: **PHP 8.2.32**, mysqli/mysqlnd, **MariaDB 10.11.18**, InnoDB. The suite
creates and drops only its own random test database; it does not touch a game DB.

**37 checks passed**, covering:

- PHP/SQL clock alignment with a different host default timezone;
- fresh global arrival index, repeatable v8→v9 migration, preserved account data;
- capped-staff training starvation and the unchanged 99-stat ceiling;
- defender-triggered arrivals from a disabled AI attacker;
- canonical raid binding, resource conservation, exactly-once staff XP/replay;
- future-arrival refusal, protected withdrawal and newer-reservation preservation;
- two real concurrent PHP settlement processes committing only one result;
- an injected mid-settlement DB failure rolling back resources and the raid ledger;
- failure isolation, durable retry backoff and successful recovery;
- non-blocking world locks, shared throttle and future schedule/lease repair;
- full-population fairness, persisted XP and canonical Base Power equivalence.

The same real 1,000-commander fixture was serviced with successive overdue states:

| Backlog age | Commanders serviced | Bounded pulses | Accelerated run time | Largest observed pulse |
| --- | ---: | ---: | ---: | ---: |
| 1 day | 1,000 | 250 | 11.25 s | 73.89 ms |
| 2 days | 1,000 | 250 | 11.54 s | 72.45 ms |
| 7 days | 1,000 | 250 | 10.70 s | 95.80 ms |

The harness deliberately clears the pulse throttle between calls to exercise the
population quickly. These timings are local test measurements, not production
throughput promises or seven days of wall-clock soak. With the normal two-second
throttle, 250 four-commander pulses take at least 8 minutes 20 seconds, and can take
longer on a slower host or with contention. Staff/roster/stat caps remain intentional.

## HTTP integration

**16 checks passed** against the actual PHP routes and a disposable database:
real login, unauthenticated rejection, POST/CSRF/origin enforcement, uncached
Rankings payloads, real AI Base Power advancement from the heartbeat, inbound
render hooks, arrival completion, defender-only report visibility, canonical
battle-report rendering, repeat-poll idempotency, CLI execution/status and restart.
The measured local Rankings HTML response was **6.22 ms**, before background work.

## Unattended worker recovery

A further **3 integration checks passed** with the real `--loop` process: it
advanced the AI with no HTTP traffic, survived a deliberate stop of the disposable
MariaDB service, and the same worker process reconnected after restart and resumed
persisted XP progression. Windows process supervision and the launcher still need
target-host acceptance.

## Client behavior

**10 checks passed** using `tests/world_runtime_client.test.js`, which executes the
shipped script with a dependency-free DOM/timer fixture: authenticated POST,
text-only names, temporary-failure backoff, reconnect, hidden-tab suppression,
visibility resumption, session expiry, server-owned arrival state, stable unchanged
rows, committed report links and browser back-forward-cache restoration.
This is a behavior harness, not a browser screenshot/render acceptance test.

## Package and inherited contracts

- All **65 PHP sources** pass syntax validation; both runtime JavaScript files and the
  client test parse successfully.
- Runtime version is **0.8.4.3**; fresh/install-repair schema revision is **9**.
- All **67 runtime image files**, `assets/css/msw.css` and the original
  `assets/js/msw.js` are byte-identical to the supplied v0.8.4.2 archive.
- Existing automatic battle playback, facing, size rules, one-use retaliation,
  resource-transfer odds/caps, shield rules and conditional staff release remain.
- Root + blank DB password remains the runtime/setup contract. New public heartbeat
  requests require the existing authenticated session and CSRF/origin verification.
- Temporary test databases, fixtures, generated console traffic and dependency
  binaries are excluded from the release. Reproducible PHP and JS tests are included.
- The production game has not been deployed or modified by this build operation.

See `../UPGRADE_v0.8.4.3.md` and `XAMPP_TEST_PLAN.md` for target-host acceptance.
Historical validation below records earlier release states; this section supersedes
older scheduling and schema assertions.

---

# v0.8.4.2 Build Validation Addendum

- [x] 60 PHP files pass `php -l`.
- [x] `assets/js/msw.js` passes `node --check`.
- [x] `assets/css/msw.css` has balanced structural braces.
- [x] Runtime DB configuration evaluates to `root` + empty password (`''`).
- [x] No non-empty DB password fallback or DB-password environment override exists in the release tree.
- [x] 67/67 runtime PNG/JPG/JPEG/WebP/GIF files are byte-identical to v0.8.3.
- [x] `assets/css/msw.css` and `assets/js/msw.js` are byte-identical to v0.8.3.
- [x] Rankings no longer invokes autonomous simulation before rendering.
- [x] AI pulse has a non-blocking global MySQL advisory lock, 4-commander hard cap, 180 ms cooperative budget and 15-second bot lease.
- [x] Bot roster finalization no longer rebuilds unseen Mother Base visual positions; layout remains lazy through `msw_mb_staff_state()`.
- [x] Schema revision remains 8; no migration or data wipe required.

---

# Build Validation — v0.8.4 Competitive Autonomous Commander Activity


## v0.8.4 competitive autonomous commander static release gate

- Application version: **0.8.4**; schema revision remains **8** with no table/column migration.
- Runtime AI scheduling remains request-driven and leased, but missed elapsed time now produces a bounded catch-up count rather than being discarded when `next_action_at` is overdue.
- Competitive class mapping is deterministic from durable `bot_index`; the 1,000 indexes resolve to **726 Active / 202 Contender / 65 Elite / 7 Apex**.
- Competitive Base Power targeting uses the stronger of a 3,500 floor or current top human Base Power. Target multipliers, staff-quality ranges, roster caps and cadence vary by class/personality.
- New development/catch-up paths create or train real `units`, use real assignments and recalculate real sector/Base Power state; no runtime path directly assigns an arbitrary competitive `users.base_power` value.
- Patrol weighting is reduced and productive Field/Development/Base/Dispatch/FOB/PvP choices dominate. Unavailable FOB/PvP choices fall back to productive work.
- Bot staff assignment now batches sector/capacity reads and includes underdeveloped-sector pressure; bot Base Power recalculation batches sector aggregation/upserts to control MySQL cost during catch-up.
- Upgrade activation is state-preserving: it only advances scheduling debt once through `schema_meta.bot_competitive_rivals_v084` and does not alter human progress, bot identity/history or FOB placement.
- AI Network/Profile Rival Class presentation is PHP/HTML-only. The class badge is kept inside the existing Commander cell so the activity table does not gain a wider column.
- Full static validation: **60 / 60 PHP files** pass `php -l`; `assets/js/msw.js` passes `node --check`; stylesheet brace balance is zero.
- Byte-identity against the uploaded v0.8.3 baseline: `assets/css/msw.css`, `assets/js/msw.js`, `database/install_schema.sql`, and all **67 / 67 runtime images** are unchanged. No nested archive exists in the candidate tree.
- Competitive-profile harness passes the deterministic **726 Active / 202 Contender / 65 Elite / 7 Apex** distribution and bounded catch-up-cap assertion.
- Detailed static evidence is recorded in `RELEASE_GATE_v0.8.4.txt`. Runtime XAMPP/MySQL/browser acceptance remains required before baseline promotion; see `docs/XAMPP_TEST_PLAN.md`.


## v0.8.1 corrective static release gate

- Application version: **0.8.1**; schema revision remains **8** with no migration.
- Corrected automatic battles reuse the normal encounter fighter/battle-card/HP presentation and constrain multi-unit sprites to compact battle proportions.
- Both sides are guaranteed renderable in FOB replays; empty combat-team snapshots receive a visual base/security defense representation.
- Every combatant carries current/max HP and every rendered fighter exposes numeric HP plus an HP bar; top-level Force HP bars update from the same event state.
- CSS/JavaScript URLs are cache-busted with the application version so old v0.8.0 assets cannot be mixed with v0.8.1 markup.
- Static model harness verifies both rosters, enemy HP, attack exchanges, KO events, final loser 0% integrity, Force HP output and absence of technical player-facing terminology.
- Full syntax gate: **60 / 60 PHP files** pass `php -l`; `assets/js/msw.js` passes `node --check`; stylesheet braces are balanced.
- Runtime XAMPP/browser acceptance remains required before baseline promotion.

## v0.8.0 static release gate

This candidate adds a presentation-only automatic battle projection around existing Dispatch/FOB authorities. Runtime XAMPP/MySQL/browser acceptance remains release-blocking and is defined in `docs/XAMPP_TEST_PLAN.md`.

- Application version: **0.8.0**.
- Schema revision: **8** (unchanged; no migration required).
- New routes: `public_html/dispatch_result.php`, `public_html/fob_dispatch_result.php`; canonical `public_html/fob_result.php` now embeds the replay before its AAR.
- New shared presentation helper: `public_html/includes/auto_battle.php`. It deterministically derives choreography from already-settled operation identity/result and does not contain a gameplay success/reward/resource roll.
- Existing gameplay authority remains in `includes/dispatch_authority.php` and `includes/fob_world.php`. The FOB-world runtime change only enriches future unit snapshots with existing unit fields used by the renderer.
- JavaScript adds expired-countdown result routing plus automatic battle autoplay/replay/skip state; CSS adds the responsive operations HUD.
- `database/install_schema.sql` and runtime image assets are required to remain byte-identical to the uploaded v0.7.5 baseline.
- Final syntax, deterministic-model, byte-identity and package-integrity results are recorded in `RELEASE_GATE_v0.8.0.txt`.
- Final static result: **60 / 60 PHP files** pass `php -l`; `assets/js/msw.js` passes `node --check`; stylesheet brace balance is zero.
- Deterministic model harness passes Dispatch success/failure final-state, FOB viewer orientation, persisted direct-raid roll, staff-strike complementary odds, sprite-fidelity and protected-abort contracts.
- Byte-identity comparison against v0.7.5 confirms `database/install_schema.sql`, `includes/dispatch_authority.php`, `includes/battle_engine.php`, and all **67 / 67 runtime images** are unchanged.
- Candidate tree contains no nested ZIP/7z/RAR archives. Live XAMPP/MySQL/browser acceptance is not inferred from these checks.

## Historical validation — v0.7.4 High-Threat Combat Pressure Calibration

## v0.7.4 static/formula release gate

This v0.7.4 candidate preserves v0.7.3 enemy level selection and adjusts only normal-enemy upper-warzone pressure plus version/documentation. Runtime XAMPP/MySQL/browser acceptance remains release-blocking.

- Application version: **0.7.4**.
- Schema revision: **8** (unchanged; no migration required).
- Intended runtime-code scope: `public_html/includes/battle_engine.php` only; `public_html/config/app.php` changes version metadata.
- v0.7.3 level-window contracts are unchanged: Lv5/Threat12 remains enemy Lv8–10; Lv20/Threat12 remains enemy Lv19–25; threat ceilings remain +0/+1/+2/+3/+4/+5.
- Normal Threat 12 stat factors now reach approximately **1.48× HP / 1.40× ATK / 1.22× DEF / 1.08× SPD** before enemy-level growth. Representative Shield Trooper Lv8–10 at Threat 12 is approximately HP **147–154**, ATK **25–26**, DEF **31–33**, SPD **8**.
- Threat pressure ramps earlier across Threat 5/7/9 instead of remaining too compressed toward Threat 12, while Threat 1 normal-enemy stat factors remain exactly unchanged from v0.7.3.
- Normal-enemy counter threat bonus reaches **+8 move power** at Threat 12 and enemy counter base accuracy gains up to **+4 points** from threat before SPD/Intel reductions. Player attack accuracy remains untouched.
- Security interception/covering-fire formulas and all Commander/Mother Base/R&D stat formulas are unchanged.
- Existing `warzone_player_threat_window_v3` encounters preserve their rolled enemy level and level roll, recalculate only enemy stats, preserve enemy HP percentage and mark v4; refresh cannot reroll the level.
- Static and byte-identity checks for this candidate are recorded in `RELEASE_GATE_v0.7.4.txt`; live browser/MySQL acceptance is not claimed.

## v0.7.3 static/formula release gate

This v0.7.3 candidate changes only PvE threat/level/stat scaling authority plus the application version/documentation. Runtime XAMPP/MySQL/browser acceptance remains release-blocking.

- Application version: **0.7.3**.
- Schema revision: **8** (unchanged; no migration required).
- Intended runtime-code scope: `public_html/includes/battle_engine.php` only; `config/app.php` changes version metadata.
- Dynamic normal-enemy level windows are driven by Commander level + threat. Required contracts: Lv5/Threat12 = +3..+5 (enemy Lv8–10); Lv20/Threat12 = −1..+5 (enemy Lv19–25).
- Exhaustive 1–100 mapping for Lv5/Threat12 produces +3 19%, +4 33%, +5 48%. Lv20/Threat12 produces approximately −1 5%, +0 8%, +1 11%, +2 15%, +3 17%, +4 21%, +5 23%.
- Threat ceiling progression is monotonic: T1 +0, T3 +1, T5 +2, T7 +3, T9 +4, T10–12 +5.
- Normal enemy stat calculations are monotonic with threat at a fixed enemy level and use separate level and threat factors. Representative Shield Trooper at Commander Lv5/Threat12 legal levels Lv8–10 yields approximately HP 129–135, ATK 22–23, DEF 30–31, SPD 8.
- Active pre-v0.7.3 encounter migration uses a deterministic roll, preserves enemy HP percentage, records v3 scaling metadata and does not reroll once marked `warzone_player_threat_window_v3`.
- v0.7.1 Commander/Mother Base/R&D/SPD/Security logic and v0.7.2 Command Centre navigation remain outside this correction and must remain unchanged.
- Static validation completed: **57 / 57 PHP files** pass `php -l`; `public_html/assets/js/msw.js` passes `node --check`; stylesheet brace balance is zero.
- Byte-identity validation against v0.7.2 confirms `assets/css/msw.css`, `assets/js/msw.js`, `database/install_schema.sql`, and all **67 / 67 runtime images** are unchanged.
- Runtime PHP diff against v0.7.2 is limited to `public_html/config/app.php` (version metadata) and `public_html/includes/battle_engine.php` (v3 scaling authority).
- Package structure contains no nested ZIP/7z/RAR archives.
- Direct PHP formula/migration harness passes the required level-window, exhaustive probability, threat-stat ordering, boss-window, counter-power and v2→v3 HP-ratio/no-reroll assertions.

## v0.7.2 static release gate

This v0.7.2 candidate is a presentation-only navigation-label correction built directly from v0.7.1. Runtime gameplay behavior is intentionally unchanged.

- Application version: **0.7.2**.
- Schema revision: **8** (unchanged; no migration required).
- `public_html/includes/ui.php`: the `fob.php` main-navigation label is **Command Centre**.
- Legitimate FOB terminology elsewhere remains unchanged.
- No CSS, JavaScript, database install schema, runtime images or gameplay formulas are intentionally modified.
- Static syntax regression passed: all **57 / 57 PHP files** pass `php -l`; `public_html/assets/js/msw.js` passes `node --check`.
- Byte-identity verification against v0.7.1 confirms `assets/css/msw.css`, `assets/js/msw.js`, `database/install_schema.sql`, and all **67 / 67 runtime images** are unchanged.
- Runtime-code diff is limited to `public_html/includes/ui.php` (navigation label) and `public_html/config/app.php` (application version).
- Package structure contains no nested ZIP/7z/RAR archives.

---

## Historical validation — v0.7.1 Commander Progression & Combat Fairness Hotfix

This section records the static/formula release boundary for the v0.7.1 corrective gameplay candidate. Runtime XAMPP/MySQL/browser acceptance remains release-blocking and is defined in `docs/XAMPP_TEST_PLAN.md`.

- Application version: **0.7.1**.
- Schema revision: **8** (unchanged; no migration required).
- Intended runtime-code scope: continuous Mother Base sector-score Commander projection, personal Commander growth coefficients, PvE player accuracy, enemy level/threat coefficients, enemy counter power, Security support/interception, and related telemetry.
- Enemy-level probability tables exhaustively cover rolls 1–100 in every threat band, remain bounded to Commander −3..+2, sum to 100%, and assign +2 exactly **1%** in every band.
- Continuous Mother Base development maps `score / 120` into fractional steps before the existing diminishing curve, so scores below 120 can already create real stat contribution. Representative R&D values: 8 score produces at least +0.51 raw ATK and +0.51 raw SPD (minimum staffed-sector visibility floor); 30 score ≈ +1.75 ATK / +0.75 SPD; 120 score = +7 ATK / +3 SPD before cross-sector aggregation/rounding.
- Player attack profile clamps final PvE accuracy to **94–100%**. SPD contributes only positively; no enemy-speed offensive penalty exists.
- Enemy counter profile begins at 88%, stacks SPD/Intel reductions, and retains a 55% floor. Counter move power is class/threat-derived rather than copied from enemy ATK, preventing effective double application of ATK.
- Security interception is bounded to a hard **40%** ceiling and remains limited by escort battle HP; covering-fire caps remain below primary Commander output.
- Static syntax regression: all **57 PHP files** pass `php -l`; `public_html/assets/js/msw.js` passes `node --check`.
- Byte-identity comparison against the v0.7.0 input candidate confirms `assets/css/msw.css`, `assets/js/msw.js`, `database/install_schema.sql`, and all **67 / 67 runtime images** are unchanged. No nested ZIP/7z/RAR archives are present.
- Direct PHP formula harness passes the level distribution, player-accuracy monotonicity, threat stat ordering, active v1→v2 encounter HP-ratio migration, Security guard ceiling, and minimum R&D fractional-contribution checks.
- No runtime acceptance is inferred from static/formula checks; XAMPP/MySQL/browser testing is still required before promotion.
---

## Historical validation — v0.7.0 Commander / Mother Base / Warzone Balance

This section records the static and formula-level release boundary for the v0.7.0 gameplay-balance candidate. Runtime acceptance against the persistent MySQL database remains release-blocking and is defined in `docs/XAMPP_TEST_PLAN.md`.

- Application version: **0.7.0**.
- Schema revision: **8** (unchanged; no migration required).
- Intended runtime-code scope: Commander Mother Base stat projection, PvE enemy level/threat calculation, Security escort support/interception, PvE counter-accuracy calculation, and related player telemetry.
- Bounded enemy-level probability contract verified exhaustively across rolls 1–100 for every threat band: all outcomes remain within Commander −3..+2; +2 probability is 1%, 1%, 1%, 2% and 3% from lowest to highest threat band.
- Effective Mother Base sector-step curve verified at representative levels: Lv1 = 0.0, Lv5 = 4.0, Lv10 = 9.0, Lv20 = 15.5 and Lv40 = 23.5 effective steps.
- Representative same-level enemy calculations confirm threat is independently meaningful (for example, a Minigun Trooper at Lv50 has higher HP/ATK/DEF under threat 12 than threat 1) without reverting to direct Commander-level raw-stat mirroring.
- Static regression checks completed for the candidate: **57 / 57 PHP files** pass `php -l`; `assets/js/msw.js` passes `node --check`; `assets/css/msw.css`, `assets/js/msw.js` and `database/install_schema.sql` are SHA-256 byte-identical to the uploaded v0.6.1 baseline; all **67 / 67 runtime images** are present at the same paths and byte-identical.
- Security support helper checks confirm round-robin guard selection, escort HP consumption, proportional HP preservation on support resynchronization, and combined Intel/SPD counter-accuracy calculation.
- Package structure check confirms no nested `.zip`, `.7z` or `.rar` archives inside the candidate tree.
- No runtime acceptance is inferred from static/formula checks; XAMPP/MySQL/browser testing is still required before promoting the candidate.

---

## Historical validation — v0.6.1 Production Gamer Readability Pass

This section records the static release boundary for the v0.6.1 production-copy update. The detailed v0.6.0 gameplay/visual validation below remains the inherited baseline because v0.6.1 does not change those systems.

- Application version: **0.6.1**.
- Schema revision: **8** (unchanged).
- Scope: player-facing copy, status labels and presentation-only wording helpers.
- CSS/layout, JavaScript, database schema/install SQL and runtime image assets remain unchanged from the uploaded v0.6.0 visual-overhaul baseline.
- Gameplay authority and persistence logic remain unchanged; the new label helpers only translate stored status/mode/result values for display.
- Final static validation completed for this candidate:
  - **57 / 57 PHP files** pass `php -l` with zero syntax errors.
  - `public_html/assets/js/msw.js` passes `node --check`.
  - `public_html/assets/css/msw.css`, `public_html/assets/js/msw.js` and `database/install_schema.sql` are byte-identical to the uploaded v0.6.0 visual-overhaul baseline.
  - All **67 runtime images** are byte-identical to the uploaded baseline.
  - A focused player-facing copy scan reports **0** remaining hits for the targeted implementation jargon set outside the localhost setup/admin surface.
  - PHP token-structure comparison reports no unexpected structural changes outside the intentional display-label helpers/calls, and SQL query literals are unchanged.

---

## Historical validation — v0.6.0 Advanced FOB Invasion Command Centre, Retaliation & Command Network Visual Overhaul

## Static release gate completed

The v0.6.0 XAMPP candidate was revalidated after the supplied-art website overhaul, interaction polish and documentation updates.

- PHP runtime used for syntax validation: **PHP 8.4.23**.
- All **57 PHP files** under `public_html/` pass `php -l` with zero syntax errors.
- `public_html/assets/js/msw.js` passes `node --check` under Node **v22.16.0**.
- `public_html/assets/css/msw.css` passes structural brace validation with **1023 blocks**, zero final imbalance and no negative nesting.
- **64** literal `__DIR__` include/require paths resolve and every referenced file exists.
- **63** literal `msw_url('*.php')` route references were checked and every referenced PHP route exists.
- No nested `.zip`, `.7z` or `.rar` archive exists inside the candidate and no `source_assets/` directory is present.
- The package contains **67 runtime images**: the **44 inherited v0.6.0 images remain SHA-256 byte-identical** to the pre-visual candidate, while the **23 new JPGs are byte-identical copies of the user-supplied library archive**.
- Every one of the 23 supplied JPGs is referenced by the production stylesheet; there are no unused supplied-art files in the runtime package.
- Compared with the pre-visual v0.6.0 candidate, **the only changed PHP file is `public_html/includes/ui.php`**; all gameplay-authority PHP remains byte-identical. CSS, JavaScript, documentation and the supplied artwork directory carry the presentation update.

## Supplied-art visual integration assertions

Static integration review confirms the visual pass is coherent and presentation-only:

- `includes/ui.php` assigns a normalized page identity class and emits semantic resource/stat classes without changing gameplay values.
- Major systems route to matching supplied artwork: Command Centre/FOB, Warzone, Combat Missions, Boss Operations, Dispatch, R&D, Strategic/Deterrence, Rankings, Mother Base/Staff, Community/Social and AI/PvP surfaces.
- The legacy green-dominant palette is overridden by neutral gunmetal/charcoal surfaces with amber/orange command accents, cyan/steel information/protection states and reserved green/red success/threat semantics.
- Resource telemetry has dedicated visual tones for Common Metal, Minor Metal, Precious Metal, Fuel, Biological and Strategic Devices.
- The existing selected-operative sprite is mirrored decoratively into page heroes; no new character image is generated or substituted.
- The moving CRT scan beam, top-bar signal sweep/pulse, hover elevation/overlap, button glints, map-card zoom and viewport reveal choreography are CSS/JavaScript presentation only.
- `prefers-reduced-motion` disables the non-essential scan/sweep/sprite/reveal motion while preserving controls and content.
- JavaScript never calculates or submits resource values, invasion eligibility, protection state, combat results, retaliation authority or persistence as part of the visual layer.

## v0.6.0 version and schema assertions

Validated directly against both runtime schema authority and fresh-install SQL:

- `public_html/config/app.php` reports application version **0.6.0**.
- `public_html/includes/schema.php` defines `MSW_SCHEMA_REVISION = 8`.
- `database/install_schema.sql` records schema revision **8**.
- Fresh schema and additive repair both define nullable `fob_raids.retaliation_for_raid_id`.
- Fresh schema and additive repair both enforce `UNIQUE uq_fob_retaliation_source(retaliation_for_raid_id)`.
- The repair path detects and replaces a same-named non-unique index before enforcing the one-use unique constraint.
- `_setup.php` Confirm Installation exposes `fob_retaliation_integrity` and verifies both the unique index and the reversed attacker/defender relationship of every retaliation row.

## Command Centre integration assertions

Static integration checks confirmed the deployed `fob.php` route now contains the integrated strategic command surface rather than a globe redirect, including:

- global priority target matrix;
- multi-invasion 2–4 staff strike planner;
- active outbound operation board;
- inbound staff-threat board;
- protection-doctrine status and warnings;
- one-use Retaliation Command Desk;
- recent outgoing After Action Report archive;
- navigation back into globe, shard, target-intel, raid-ledger and strike-ledger surfaces.

The selected staff-planner target carries its real persisted `world_id`; JavaScript only synchronizes presentation/form context and the server independently re-resolves the target before launch.

## Offensive protection doctrine review

The offensive protection rule is implemented in shared FOB authority, not in individual pages:

- `msw_fob_break_protection_for_offense_locked()` clears only a currently-active `users.fob_protection_until` value.
- Immediate human raids, autonomous raids and retaliation all pass through `msw_fob_resolve_direct_raid()` and invoke the protection break **inside the same transaction**, after target/protection/resource validation but before settlement.
- Staff invasions pass through `msw_fob_launch_staff_dispatch()` and invoke the same protection break at the successful reservation/launch commitment point.
- Rejected/invalid/protected-target attempts occur before the break call, so they do not consume the attacker's shield.
- Transaction rollback restores the protection state if a later authoritative settlement/launch write fails.
- The repository contains no alternate `fob_raids` or `fob_strike_dispatches` insert path outside `includes/fob_world.php`.

## Retaliation replay/integrity review

Retaliation is represented as an extension of the canonical raid ledger rather than a parallel combat system:

- a retaliation POST carries the exact incoming `retaliation_raid_id` source incident;
- the resolver locks and verifies that the source defender is the retaliator and source attacker is the requested retaliation target;
- the source is checked for prior consumption under transaction lock;
- the unique database index provides a second, concurrency-safe one-use guard;
- the retaliation target still goes through normal global membership and current defender-protection validation;
- retaliation uses the same combat snapshots, resource transfer, defender protection, console events and canonical `fob_result.php` AAR path as direct raids;
- AARs preserve/link the source incident and record whether the attacker voluntarily surrendered an active recovery shield.

## Multi-invasion persistence review

The Command Centre does not invent a separate queue. Every staff invasion remains a normal durable `fob_strike_dispatches` row with its own target, world, snapshots, stored success chance, start/finish timestamps and reserved staff IDs.

- Repeated launches can therefore coexist as true parallel operations.
- Existing `units.dispatched_until` locking prevents the same staff member being committed to two operations.
- Arrival still revalidates defender protection under lock and can resolve to `protected_abort` without resource transfer.
- Existing exactly-once dispatch settlement and canonical `fob_raids` result creation remain in use.

## Inherited regression boundaries

v0.6.0 does not modify the accepted combat-support catalogs, medical/Intel/Security/Support mechanics, maps, sprites, Mother Base gameplay art, PvP authority or autonomous population seeding logic. Cross-shard invasion remains global: the inherited `msw_fob_same_world()` helper has no invasion caller. The only new image binaries are the 23 explicitly supplied Command Network JPGs under `assets/artwork/`; all inherited image binaries remain unchanged.

The changed FOB settlement code preserves the v0.5.0 row-lock/resource-ledger/defender-protection architecture while adding only the offensive shield break and optional one-use retaliation binding.

## Runtime acceptance boundary

A local MySQL/MariaDB service/client is not available inside the build container, so this static gate deliberately does **not** claim database/browser acceptance. The package therefore remains labeled **XAMPP Test Candidate**.

Before promoting the build, back up the accepted v0.5.0 database, run `_setup.php` **Update / Repair** to schema revision **8**, run **Confirm Installation**, require `fob_retaliation_integrity = OK · one-use incident binding enforced`, then complete every release-blocking scenario in `docs/XAMPP_TEST_PLAN.md` using the real XAMPP/MariaDB/browser environment.
