## v0.8.6.3 — Attached Character Markers — 2026-09-10

- Put each remote character image and label inside one positioned map actor.
  Center the marker three pixels below the sprite box; move, hide and remove
  the complete actor together. Preserve profile links and hover/focus identity.
- Correct the independent CSS scale/centering interaction that shifted mirrored
  Trevor one sprite width away from his marker. Mirror only the artwork inside
  remote actors and fix standalone local-avatar centering as well.
- Retain four-direction sprite initialization and failed-image recovery. Keep
  stable actor reuse and apply smooth movement only to the shared parent, with
  a reduced-motion override.
- Add content-derived revision keys to core CSS/JavaScript URLs so preserved
  local version settings cannot keep the old mismatched renderer/styles cached.
- Preserve the uploaded public baseline’s blank MySQL password, with no
  password environment fallback. Retain its original database test fixtures.
- Ship the complete project with every supplied baseline file and all native
  image/map assets retained. Preserve schema 9, the AI population, real Dispatch
  menus, text-only player Combat Missions, gameplay state and backend logic.
- Update client tests for shared positioning, atomic visibility/removal and
  late image events. Add a standalone local browser fixture for host validation.

## Superseded initial AI visibility patch — 2026-09-10

- Fix first-seen remote AI and human sprites when the server reports up/down
  movement. Select the existing right-facing art for vertical/default headings,
  with authored left sprites and Trevor mirroring retained for left movement.
- Keep each remote label and image hidden until the image has decoded successfully;
  hide both after image failure and retry on the next normal presence poll.
- Avoid resetting an already-loaded matching image source on repeated polls.
  Retain stable node reuse, profile links, hover names and complete removal.
- Apply through the common renderer used by all 17 warzones. Preserve the existing
  AI roster, server presence filtering, map geometry, CSS and gameplay systems.
- Ship as a focused update over installed v0.8.6.1 or v0.8.6.2, preserving their
  configuration and version. Add executable client regression coverage.

## v0.8.6.1 — Restore original Dispatch menu

- Restore the accepted Dispatch page: Available Dispatches, one staff-selection
  form per mission, and the original Dispatch History table.
- Keep all four original staff missions first and append all eleven new high-threat
  staff missions in that same section. Remove the assignment selector/review screen.
- Restore player Combat Missions to text cards. Remove every mission-card image,
  Local Staff Dispatch button and mission-page staff-dispatch shortcut.
- Restore shared UI, core styling and dispatch-selection controls to the accepted
  v0.8.5.1 presentation. Retain backend level/reward/XP additions and input guards.
- Keep all pending deployments visible in the original history table.
- Replace rejected-layout assets with inactive compatibility files for existing
  v0.8.6 installs. Update HTTP and control tests to enforce menu separation.
- Provide a small correction usable over v0.8.5.1 or v0.8.6 and a complete ZIP,
  both using plain ZIP STORE format with independent extraction verification.

## v0.8.6 — Expanded Tactical Operations — 2026-09-10

- Add eleven Combat Missions and eleven Staff Dispatch assignments, covering every
  Threat 13–23 warzone and bringing both catalogs to fifteen.
- Use the existing Lv20–70 map minimums and adaptive combat scaling for mission
  targets; add authored Commander XP and distinct resource rewards per mission.
- Add longer four-staff expeditions with rising difficulty, resource rewards and
  success/failure staff XP. Keep original definitions and 80/25 XP fallbacks exact.
- Show new mission/location briefings in battle, plus local enemy pools and proper
  map-level opposition in dispatch replays. Playback still never grants rewards.
- Expand mission cards with previews, readiness, level ranges, XP, clears and a
  corresponding local staff-dispatch link.
- Replace repeated staff grids with one assignment review and squad picker. Preview
  combined power/success chance, preserve the selected assignment after launch,
  and show all pending deployments separately from the latest twelve results.
- Rank expansion staff candidates by Combat + 3 × level; filter AI expansion jobs
  by squad power and recheck their benchmark inside the launch transaction.
- Retain authoritative ownership, exact staff counts, reservations, timers,
  transactional settlement and existing battle version guards. Reject malformed
  operation keys and staff IDs. Keep all native assets and schema revision 9.
- Add catalog, real database, HTTP and dispatch-preview regression tests.

## v0.8.5.1 — Higher-level expansion enemies

- Added ascending map minimum enemy levels of 20–70 in five-level steps for the
  eleven Threat 13–23 warzones. Keep the existing Commander-maturity range widths.
- Increased the player-relative upper offset to +10 on Threat 13, rising by two
  per map to +30 on Threat 23. Shift the whole range above each map minimum when
  needed, preserving random variety. Higher levels also raise derived combat stats.
- Aligned readiness recommendations and map text with the new progression.
- Applied the shared minimum to AI field, development and catch-up recovery;
  preserve existing captured units, career bonuses, staff caps and XP behavior.
- Read counter accuracy from the committed encounter readiness gap so existing
  fights do not gain a mid-battle penalty after this balance update.
- Preserve original maps, bosses, native assets, AI deployment, schema 9 and all
  existing player/AI progress. Added floor and low-career recovery regressions.

## v0.8.5 — Expanded Warzones — 2026-09-10

- Integrated all 11 supplied v4 maps at original native resolution, with authored
  collision, safe spawns, habitat-specific Rebel pools and multiplayer deployment.
- Extended normal threat progression from 12 to 23 without changing accepted
  Threat 1–12 or boss balance. Every new threat raises the full enemy-level window
  by one; readiness rises by two levels, reaching Commander Lv 38 at Threat 23.
- Added bounded HP/ATK-focused high-threat pressure and modest counter increases;
  preserve the current v5 encounter model and committed fights.
- Added transactional, one-time AI redistribution across all 17 maps without
  resetting careers, units, economies, FOB homes or operation history. Repeated
  Update / Repair retains patrol positions. Balanced all six skins per map and
  globally for the 1,000-commander population.
- Extended local development/catch-up recruitment levels on expansion maps while
  retaining existing field captures, unit caps, leveling and scheduler budgets.
- Added lightweight previews for all 17 maps, lazy image loading, current enemy
  ranges, recommended Commander levels, and setup content/redistribution checks.
- Retained all 67 original images, all 11 supplied PNG bytes, core CSS/JS, schema 9
  and Automatic World setup/restart behavior. Added focused regression tests and
  upgrade/expansion documentation.

## v0.8.4.6 — Automatic World access-denied setup repair

- Fixed Fresh Install and Update / Repair reporting a setup failure after the
  database had completed because Windows denied scheduled-task registration.
- Release database maintenance before starting automatic execution. Keep native
  failures visible in their own status and add CSRF-protected Enable / Retry
  without database writes. Database confirmation reports its own integrity.
- Register through the windowless Task Scheduler API under the actual Apache
  identity: InteractiveToken for desktop XAMPP, service mode for built-in service
  accounts, and S4U for a custom service identity. No passwords or elevated task
  privileges are requested. Same-account task names avoid old ownership conflicts.
- Preserve indefinite automatic restart, actual heartbeat verification, duplicate
  guards, canonical strike settlement, continuous AI and database schema 9.
- Added Access-denied HTTP regression, native adapter contract tests, account-mode
  and Windows-path checks. Shipping every original file and all 67 images.


## v0.8.4.5 complete package correction — 2026-09-08

- Rebuilt the full distributable after a truncated full ZIP and an unclear
  update-only download. The runtime version remains 0.8.4.5.
- Includes every original v0.8.4.2 package file, all 67 unchanged original images,
  the retained game content and the tested setup, strike and offline-AI repairs.
- Shortened the enclosing folder to `MetalSlugWarzone_Package`, clarified the
  existing-world copy steps and added a SHA-256 inventory for extracted files.
- Runtime behavior is unchanged by this packaging correction. The native
  Windows task registration acceptance gate still applies.


- Replaced the manually launched worldworker PHP/batch files with an internal automatic server entry point. Local Update / Repair registers and starts a per-installation Windows task using XAMPP's windowless `php-win.exe` and the actual absolute game/INI paths.
- AI careers and strike arrivals continue with every player offline while Apache and MySQL are running. Native scheduling checks every minute, ignores duplicate instances and has no default 72-hour execution limit. The engine stops when Apache stops, reconnects after MySQL outages, and retires between batches when deployed source changes.
- Added verified background health to local setup. Registration alone is not reported as Running: the process must complete a world update first. Local settings/status files return 404 with no body over HTTP. Public gameplay cannot register or launch operating-system tasks.
- Setup repair shares the world lock, retains the corrected SQL clock alias, and reports database-repair success separately when automatic startup registration fails. No credentials or Windows passwords are requested or stored by the task installer.
- Added lifetime recovery of invalid future scheduler/lease timestamps and future retry metadata. The shared pulse throttle still prevents accounts/tabs/processes from multiplying progression.
- Background browser tabs now continue a lower-rate heartbeat; connection failures, server retries and expired sessions have honest status feedback. The incoming board uses server-owned countdown data and preserves draft controls.
- Added reproducible background/HTTP tests and setup health coverage. The PHP/MariaDB engine was tested with zero HTTP traffic, stopped/restarted database and web services, duplicate starts and deployment changes. Generated task definitions validate against Microsoft's official schema. Native Windows registration and a real browser renderer were unavailable; target-host acceptance remains required.
- Schema remains 9; all 67 images and the original CSS/JavaScript remain unchanged. See UPGRADE_v0.8.4.5.md.

## v0.8.4.4 — Setup Confirmation Fix — XAMPP Test Candidate

- Fixed the MariaDB syntax error near `current_time` in the actual Confirm Installation action. The database-clock report now uses the unreserved `server_clock` alias.
- A missing global arrival index now produces an error status even when the stored schema revision is already 9.
- Added a real HTTP setup regression suite with an isolated MariaDB database: the old query reproduces error 1064; corrected confirmation completes; Update / Repair is repeatable; existing human and AI progress remains; fresh setup works on the disposable test database. All 33 checks passed.
- The prior v0.8.4.3 HTTP tests did not submit Confirm Installation. Their passing results did not cover this regression; the new test closes that gap.
- Runtime code changes are limited to `_setup.php` and application version metadata. Schema remains 9. Durable incoming strikes, continuous AI, the worker, all assets and presentation code are retained.
- Existing v0.8.4.3 users can apply the separate one-file setup hotfix without overwriting configuration or reinstalling the world. See `UPGRADE_v0.8.4.4.md`.

## v0.8.4.2 — Responsive Autonomous Commander Pulse Corrective Patch — XAMPP Test Candidate

### Critical performance fix
- Corrected the v0.8.4/v0.8.4.1 foreground-request regression that could make Rankings and other AI-enabled pages appear frozen. The cause was the new competitive AI system allowing a single page request to service a much larger batch of overdue commanders, with each commander potentially compressing many missed actions into expensive recruitment/training/layout work.
- Rankings is now a pure read of the persisted ladder and performs **no AI simulation work before rendering**. Opening the ranking page can no longer trigger a large autonomous catch-up wave.
- Autonomous progression now uses a global non-blocking MySQL advisory lease (`msw_bot_pulse`) so overlapping browser tabs/map-presence polls cannot run competing AI batches simultaneously on the same XAMPP database.
- Added a strict per-request progression ceiling: at most 4 detailed commanders per pulse, a configured 180 ms cooperative time budget, an 8-commander absolute pulse ceiling, and a short 15-second per-bot lease. The time guard is checked between commanders so normal page work retains priority.
- Removed eager Mother Base visual-position rebuilding from bot roster finalization. Staff positions are already synchronized lazily when a Mother Base is actually viewed, so autonomous development no longer performs collision/layout work for unseen bases.
- Catch-up still records all elapsed field/logistics productivity, but materializes at most one recovered staff row and a small bounded training batch per detailed catch-up. This preserves strong competitive progression without multiplying SQL work by every missed 4–11 second action window.
- The v0.8.4 rival classes, competitive human-power anchor, productive decision weighting, real staff/sector Base Power calculation and 4–11 second action cadence remain intact.

### Fresh install / upgrade safety
- The one-time v0.8.4 activation marker no longer makes all 1,000 commanders immediately overdue. It only clears stale expired leases, preventing a fresh install or upgrade from creating an initial thundering-herd catch-up spike.
- Schema revision remains **8** and no database wipe/migration is required.
- MySQL/XAMPP authentication remains **root + blank password only**. No non-empty database-password literal or database-password environment override is present in this build.

### Presentation regression protection
- `msw.css`, `msw.js` and all 67 runtime image assets are byte-identical to the accepted v0.8.3 presentation baseline. No sprite dimensions, image files, UI sizing rules or battle-facing rules were changed.
- Application version advances **0.8.4.1 → 0.8.4.2** only to identify the corrective build and refresh the existing cache-busted asset URLs.

## v0.8.4.3 — Durable Strike Arrivals + Continuous AI — XAMPP Test Candidate

### Fixed
- Extracted a canonical, row-locked single-strike arrival resolver. Command Centre now services incoming as well as outgoing due strikes, and global settlement works even if the attacker is offline, disabled or has not received its next AI tick.
- Aligned MySQL session time with the configured PHP timezone in runtime, setup and the CLI worker. This fixes the reproduced zero-countdown/future-SQL-arrival mismatch without rewriting historical DATETIME values.
- Filtered capped and deployed staff before the training selection limit. Saturated low-level veterans no longer starve higher-level personnel with trainable assignment stats.
- Removed synchronous AI simulation from navigation and map-presence routes. Shared authenticated background pulses now run on every signed-in page, including Rankings and base pages that previously had no recurring update.
- Added in-place standings and incoming-defense updates, server-clock-adjusted arrival timers, reconnect backoff, visibility resumption and back-forward cache recovery. Existing draft forms and unchanged incoming rows are preserved.
- Isolated failed arrivals, retained their pending state after rollback, and added bounded retry backoff plus a rotating global arrival cursor. Failed standard AI dispatch rows no longer prevent the bot's career action/catch-up.
- Refreshed the human power anchor during long-running workers and repaired implausibly future legacy schedules/leases on first activation.

### Operations and upgrade
- Added `worldworker.php` (`--once`, `--loop`, `--status`) and a portable XAMPP `worldworker.bat` launcher. The worker and browser heartbeat share database-scoped serialization and throttling; temporary DB outages trigger a reconnect/retry.
- Added schema revision **9**, consisting of `idx_fob_dispatch_due(result,id,finish_at)`, with idempotent Update / Repair support and a Confirm Installation check.
- Added an in-place upgrade guide, operator notes and reproducible PHP/JavaScript regression suites. No database reset is needed.

### Validation and preserved behavior
- Passed 37 real PHP/MariaDB checks, including simultaneous exactly-once settlement, rollback/retry and 1,000-commander progression across one-, two- and seven-day backlog cases.
- Passed 16 HTTP integration checks and 10 JavaScript behavior checks. See `docs/BUILD_VALIDATION.md` for scope and limitations.
- Preserved existing staff-strike odds, transfers, protection, one-use retaliation, conditional reservation release and report playback. Actual staff/sector calculations still own AI Base Power.
- Preserved root + blank database password and every runtime image. Existing `msw.css` and `msw.js` remain byte-identical to v0.8.4.2; no artwork or battle-animation changes.

## v0.8.4 — Competitive Autonomous Commander Activity — XAMPP Test Candidate

## v0.8.4.1 — Blank XAMPP Database Password Corrective Patch

### Fixed
- Removed the non-empty MySQL/XAMPP database password from the project configuration. Database connections now use the intended empty password (`''`) for `root`.
- Removed the database-password environment override so this build accepts only the blank database-password contract requested for this XAMPP setup.
- Fresh Install, Upgrade / Repair and normal runtime connections all resolve through the same `public_html/config/app.php` DB credential, preventing setup/runtime password drift.
- Bumped the application version to 0.8.4.1 so the existing asset-version query string refreshes browser caches without changing the accepted physical CSS, JavaScript or image assets.

### Preserved
- The v0.8.4 Competitive Autonomous Commander activity/progression system is unchanged.
- Player commander login passwords and password hashing are unchanged; this correction applies only to the MySQL/XAMPP DB credential.
- Schema revision remains 8 with no migration or wipe.
- `msw.css`, `msw.js` and all runtime PNG/JPG/JPEG/WebP/GIF assets remain byte-identical to v0.8.4, protecting the accepted compact UI/image sizing and combat-facing behavior.

### Continuous competitive AI progression
- Reworked all 1,000 `WarzoneAI` commanders from sparse request-time activity into an elapsed-time-aware autonomous career model. A leased bot still performs only bounded server work per request, but overdue commanders now compress missed field/logistics/training work into a capped catch-up operation instead of losing all progression while their warzone is not being watched.
- Added a one-time upgrade activation marker (`bot_competitive_rivals_v084`) that makes the existing autonomous population immediately due without rewriting rank, staff, resources, identity, map position, FOB placement or combat history. Existing installations therefore begin using the new progression naturally as soon as normal game requests resume.
- Reduced the base action window from 10–26 seconds to **4–11 seconds**, raised bounded pulse throughput, and retained per-bot `next_action_at` plus a 45-second lease so extra browser clients cannot double-run the same commander.
- Rebalanced autonomous decisions away from non-progressing patrol movement. Field combat, Mother Base development, recruitment/training, Dispatch, FOB operations and PvP now dominate the decision mix; failed/unavailable FOB or PvP actions fall back into productive field/base work instead of wasting a turn.

### Rival classes and strong-base tail
- Added deterministic competitive classes tied to durable `bot_index`: **726 Active Rivals**, **202 Contenders**, **65 Elite Rivals** and **7 Apex Rivals** across the 1,000-command population. The class never reshuffles after restart.
- Every class targets a moving power band anchored to the stronger of the configured 3,500 Base Power floor or the current strongest human Commander. Most AI bases are driven toward credible player competition; Elite/Apex classes receive higher staff-quality ceilings, larger staff caps, faster cadence and stronger catch-up so a small rare tail can become exceptionally powerful.
- Base Power is never written as a fabricated number. Autonomous growth still comes from real recovered/trained `units`, real sector assignments and the same sector-score/level/base-grade formula used by players.
- Improved recovered staff quality, capture reliability and staff assignment logic. New AI recruits are distributed with underdeveloped-sector pressure while preserving the existing early R&D/Cargo Fulton progression guarantee.
- Added productive Mother Base training at/near roster capacity so mature rivals can continue increasing real sector scores instead of permanently plateauing once their roster is full.

### Performance and presentation polish
- Added a bot-only batched Base Power recalculation path that produces the same sector score/level/capacity/grade formula while collapsing the former repeated per-sector read/update loop, reducing MySQL pressure during catch-up bursts.
- AI Network now identifies **Active / Contender / Elite / Apex** Rival Class and AI Commander profiles show the same stable class badge. Rankings copy now accurately describes continuous rival operations.
- Freshly seeded autonomous commanders receive earlier initial action timestamps so new installations become lively almost immediately.
- Application version advances **0.8.3 → 0.8.4**. Schema revision remains **8**: no table/column migration is introduced.
- Superseded by v0.8.4.1: database authentication now uses the blank-password-only XAMPP contract.

### Preserved
- Human accounts, inventories, resources, staff, progression, FOB homes, raid history, PvP records and all autonomous identities remain intact.
- Existing 28% human FOB target preference, defender protection, autonomous direct-raid transfer limits and transactional FOB/Dispatch authority are unchanged.
- No CSS/JavaScript sizing rules or runtime PNG/JPG/JPEG/WebP/GIF assets are changed by this release, specifically protecting the accepted v0.8.1–v0.8.3 battle-size/facing presentation from regression.

## v0.8.3 — Combat Facing Consistency — XAMPP Test Candidate

### Fixed
- Corrected normal player-encounter **Security Backup Squad** orientation. Recovered Security escorts deploy on the Commander's left side and are now mirrored in code/CSS so they face right, inward toward the enemy, while the Commander keeps the already-correct right-facing pose.
- Corrected **live player-vs-player commander battles** so the local Commander on the left always uses the character's right-facing sprite and the opposing Commander on the right always uses the character's left-facing sprite.
- Applied the same opposing-Commander direction rules to **Live AI PvP** and **Quick AI Duel / snapshot PvP**, because all three modes share the canonical `pvp_match.php` battle renderer.
- Added a safe mirror fallback for characters that do not have a separate authored left-facing file. Trevor continues to use the existing `mirror_left` catalog contract and is mirrored at render time rather than creating a duplicate sprite asset.
- Added explicit `data-battle-facing`/facing classes to the affected battle sprites and a protected horizontal-mirror utility so future presentation rules cannot silently reverse the intended inward-facing formation.
- Bumped the application version to 0.8.3 so the existing CSS/JS cache-busting mechanism immediately loads the corrected facing rules.

### Preserved
- No PNG/JPG/JPEG/WebP/GIF runtime artwork was generated, duplicated, flipped on disk or modified; all facing corrections are code/CSS and existing authored directional character sprites only.
- PvE battle damage, Security backup interception/covering-fire behavior, PvP turn resolution, AI PvP behavior, snapshot PvP behavior, XP awards and persistence are unchanged.
- Automatic Dispatch/FOB battle playback remains unchanged from v0.8.2.
- Database schema revision remains 8 with no migration.

## v0.8.2 — Automatic Battle Friendly Facing Fix — XAMPP Test Candidate

### Fixed
- Corrected friendly combatant orientation in automatic Dispatch/FOB battle playback: units rendered on the left now explicitly face right, inward toward the opposing force.
- Preserved the enemy roster's accepted orientation on the right so hostile units continue to face left toward the friendly squad.
- Replaced the previous broad side-based sprite transform with explicit render-time facing classes plus a protected CSS transform, preventing other fighter presentation rules from cancelling the intended friendly mirror.
- Kept side-aware attack choreography aligned with the sprites: friendly lunge/muzzle effects travel right and enemy lunge/muzzle effects travel left.
- Bumped the application version to 0.8.2 so CSS/JS cache-busting forces the corrected battle-facing rules to load without a manual hard refresh.

### Preserved
- No runtime sprite/image assets are added, generated, mirrored on disk or modified; the fix is code/CSS only.
- Automatic battle HP, KO sequencing, autoplay, replay, skip, result choreography and player-facing text remain unchanged from the accepted v0.8.1 behavior.
- Dispatch/FOB settlement logic and database schema remain unchanged; schema revision stays at 8 with no migration.

## v0.8.1 — Corrected Peace Walker-Style Automatic Battle Playback — XAMPP Test Candidate

### Fixed
- Replaced the broken oversized automatic battle layout with a compact multi-unit arena built from the same fighter cards, sprites, HP bars and visual proportions used by the normal player encounter battle screen.
- Added explicit friendly and enemy rosters to every replay. FOB reports with no assigned combat defenders now show a base/security defense representation instead of leaving the opposing side blank.
- Added numeric current/max HP for every combatant and animated Force HP bars for both sides. Exchange and KO events now visibly reduce enemy/friendly HP to the final result.
- Added clearer firing/impact feedback and a one-at-a-time KO finish so the battle visibly plays rather than jumping from setup to the AAR.
- Added release-version cache busting to `msw.css` and `msw.js`, preventing an older cached stylesheet/script from producing unstyled giant sprites or disabling autoplay after an update.
- Rewrote automatic-battle and result-page copy as player-facing game text; implementation/debug terminology is no longer shown to players.

### Preserved
- Dispatch and FOB gameplay settlement remains unchanged and replay-only.
- Schema revision remains 8; no database migration or new runtime image asset is required.

# Changelog
## v0.8.0 — Automatic Operations Battle Playback — XAMPP Test Candidate

### Peace Walker-inspired automatic result battles
- Added a reusable deterministic battle-playback layer in `public_html/includes/auto_battle.php` for **standard Dispatch Missions**, **FOB staff strike-force operations**, and **direct/retaliation FOB raids**. The opposing-force HUD, unit cards, force integrity, event log, hit/KO presentation and final result overlay are presentation only and consume an already-settled server result.
- Added `dispatch_result.php` as the canonical standard Dispatch result/replay route. Due missions are first passed through the existing dispatch authority, pending missions retain their timer, and settled missions render the automatic battle before their authoritative result/reward summary.
- Added `fob_dispatch_result.php` as the staff-strike completion route. Pending strikes resolve only through the existing FOB authority; combat resolutions redirect into the canonical FOB raid AAR/replay, while a defender protection abort receives a dedicated shield/withdrawal playback and no fabricated combat settlement.
- Upgraded `fob_result.php` so every accessible settled raid—direct invasion, retaliation or staff strike—plays the automatic battle before the established resource/readiness After Action Report. The logged-in commander is consistently presented as the left-side force.
- Standard Dispatch and FOB staff ledgers now expose **Battle Replay** links. The nearest pending operation on each relevant surface carries an automatic result URL so its client countdown transfers into the battle result as soon as it reaches zero.

### Authority and deterministic replay safety
- Gameplay settlement remains untouched: `dispatch_authority.php` still owns Dispatch result/reward/unit-XP resolution; `fob_world.php` still owns FOB result, resource transfer, recovery protection and staff-XP settlement. The browser cannot submit or alter a replay outcome.
- Replay choreography is seeded from stable operation/raid identity plus the committed result. Replaying or refreshing produces the same presentation outcome and contains no result roll or reward calculation.
- Immediate/retaliation FOB reports consume the exact attacker/defender comparison rolls already persisted in each raid snapshot. Staff-strike reports consume the persisted success chance; defender-facing playback presents the complementary defender probability.
- FOB unit snapshots now also retain `source_enemy_key`, ATK, DEF and SPD so future battle reports can preserve recovered unit sprite identity and richer snapshot fidelity without changing the database schema.

### Presentation and compatibility
- Added a responsive Peace Walker-inspired amber-versus-red operations HUD to `assets/css/msw.css` and the autoplay/replay/skip/countdown transition controller to `assets/js/msw.js`. Reduced-motion preference is honored by applying the settled final state immediately.
- Added result-route labels to the server console and wired the shared playback include through the existing UI bootstrap.
- Application version advances **0.7.5 → 0.8.0**. Schema revision remains **8**; no migration or install-schema change is introduced and no runtime image assets are added or modified.

## v0.7.5 — Underlevel High-Threat Progression Gate — XAMPP Test Candidate

### Low-level late-warzone progression gate
- Preserved the complete v0.7.3/v0.7.4 player-relative enemy level-window contract, including Threat ceilings +0/+1/+2/+3/+4/+5 and the required Lv 5/Threat 12 = Lv8–10 plus Lv 20/Threat 12 = Lv19–25 anchors.
- Added `msw_warzone_readiness_pressure()` as a second difficulty axis for ordinary Threat 4–12 contacts. Readiness benchmarks are T4 Lv 5, T5 Lv6, T6 Lv7, T7 Lv9, T8 Lv10, T9 Lv12, T10 Lv13, T11 Lv15 and T12 Lv16; Threat 1–3 have no underlevel gate.
- When Commander level falls below the benchmark, normal-enemy HP/ATK/DEF/SPD receive capped multipliers of up to +55% / +85% / +35% / +18%. This extra pressure stacks after the accepted rolled-level + map-threat factors and disappears completely at/above the benchmark.
- Added up to +6 underlevel points to normal enemy counter base accuracy before Commander SPD/Intel reductions. Player accuracy is unchanged and SPD remains beneficial-only.

### Reproduced low-level balance anchors
- Lv4 / Threat 5 / Lv6 Rebel Biker: approximately **133 HP / 33 ATK / 21 DEF / 21 SPD**.
- Lv4 / Threat 7 / Lv6 Rebel Heavy Gunner: approximately **130 HP / 44 ATK / 20 DEF / 9 SPD**.
- Lv4 / Threat 9 / Lv8 Rebel Biker: approximately **206 HP / 54 ATK / 28 DEF / 24 SPD**.
- Lv4 / Threat 12 / Lv9 Rebel Shield Trooper: approximately **232 HP / 47 ATK / 43 DEF / 10 SPD**.
- These anchors deliberately target the user's observed failure mode: a starter/lightly developed Lv3–4 Commander should no longer expect to clear high-threat zones without meaningful Mother Base/stat investment.

### Progression and compatibility
- Mother Base/R&D Commander bonuses remain unchanged and continue to be the intended way for a lower-level player to overcome dangerous maps through direct HP/ATK/DEF/SPD improvement.
- Security escort interception/covering fire remain unchanged; higher incoming ATK simply burns through escort HP faster in late warzones.
- New encounters use `warzone_player_threat_window_v5`. Existing v3/v4 encounters preserve committed enemy level/roll, preserve current HP percentage, and receive one stat recalibration through v5.
- Application version advances **0.7.4 → 0.7.5**. Schema revision remains **8**; no database migration is introduced. Runtime PHP changes remain limited to `public_html/config/app.php` and `public_html/includes/battle_engine.php`.

## v0.7.4 — High-Threat Combat Pressure Calibration — XAMPP Test Candidate

### Upper-warzone threat pressure
- Preserved the complete v0.7.3 player-relative enemy level-window system. Threat ceilings remain +0 / +1 / +2 / +3 / +4 / +5 across the established map bands, including the required Lv 5/Threat 12 = Lv8–10 and Lv 20/Threat 12 = Lv19–25 contracts.
- Recalibrated normal-enemy threat multipliers so upper maps no longer feel too close to low/mid threat when the Commander has only light Mother Base development and active Security escorts. Threat 12 now reaches approximately **1.48× HP, 1.40× ATK, 1.22× DEF and 1.08× SPD** before enemy-level growth.
- Changed the normal threat-pressure exponent from 1.25 to 1.10 and widened the upper multipliers while preserving the accepted Threat 1 factors exactly. Threat 5/7/9 therefore ramp earlier and more coherently instead of saving too much of the curve for the final band. HP/ATK receive the strongest lift; DEF remains intentionally restrained to avoid excessive sponge fights.
- Normal enemy counter move threat contribution now reaches +8 instead of +6, while still keeping enemy ATK as a single input to `msw_damage()` and therefore preserving the v0.7.1 anti-double-scaling fix.
- Normal enemy counter base accuracy gains up to +4 points from threat. Commander SPD and Intel remain pure defensive reductions and continue to subtract from enemy accuracy; player attack accuracy remains untouched at its beneficial 94–100% model.

### Security / Commander progression preservation
- Security interception rate, battle-local escort HP, KO persistence, covering-fire rules and support caps are unchanged. Higher-threat enemies naturally consume escort HP faster because incoming damage is now appropriately higher.
- Immediate R&D staffing contribution, all Mother Base Commander stat mappings, personal Commander level growth and Command Centre navigation remain unchanged.

### Active encounter migration
- New encounters use `warzone_player_threat_window_v4`.
- Existing v0.7.3 encounters keep their already-valid enemy level and stored level roll, recalculate only enemy HP/ATK/DEF/SPD through v0.7.4 pressure, and preserve current enemy HP percentage. They do not reroll level on upgrade or refresh.
- Older pre-v0.7.3 encounters still receive one deterministic legal-window level migration before the v4 stat calibration.

### Release boundary
- Application version advances **0.7.3 → 0.7.4**. Schema revision remains **8**; no database migration is introduced.
- Intended runtime code changes remain limited to `public_html/config/app.php` and `public_html/includes/battle_engine.php`; documentation/release-gate files are updated accordingly.

## v0.7.3 — Threat-Aware Player-Relative Warzone Scaling — XAMPP Test Candidate

### Threat-driven enemy level windows
- Replaced the universal Commander −3..+2 normal-PvE level window with a dynamic window derived from both **Commander level** and **warzone/operation threat**. The maximum enemy offset rises progressively with threat: Threat 1 caps at +0, Threat 2–3 at +1, Threat 4–5 at +2, Threat 6–7 at +3, Threat 8–9 at +4, and Threat 10–12 at +5.
- Added Commander-maturity spread control. Commanders Lv1–5 use a 2-level span below each threat ceiling; Lv6–9 use 3; Lv10–14 use 4; Lv15–19 use 5; Lv 20+ use 6, with the normal lower bound capped at −3. This yields the requested **Lv 5 / Threat 12 = Lv8–10** and **Lv 20 / Threat 12 = Lv19–25** contracts.
- Added threat-weighted probability blending across the legal window. Safe maps favor the lower side; dangerous maps progressively favor the upper side. At Lv 5/Threat 12 the exhaustive 1–100 mapping is +3 19%, +4 33%, +5 48%. At Lv 20/Threat 12 the complete −1..+5 range remains available.
- Encounter state now persists the v3 scaling model, level roll, min/max offsets and final offset so reloads never reroll enemy level.

### Threat/stat pressure correction
- Reworked normal enemy HP/ATK/DEF/SPD factors so rolled enemy level supplies progression scaling and warzone threat supplies a separate nonlinear map-danger multiplier. Threat 12 therefore remains materially stronger than Threat 1 even at the same enemy level.
- Increased normal enemy level growth to 3.0% HP, 2.8% ATK, 2.4% DEF and 1.0% SPD per enemy level step, with controlled nonlinear threat multipliers reaching approximately 1.30× HP, 1.22× ATK, 1.16× DEF and 1.06× SPD at Threat 12 before level scaling.
- Kept bosses on a dedicated tighter level window and gentler stat curve because their catalog base stats are already exceptional.
- Enemy counter move power retains single-ATK application and now uses a smooth threat bonus, preserving the v0.7.1 fix against ATK double-counting while allowing high-threat contacts to hit meaningfully harder.

### Active encounter compatibility
- Active pre-v0.7.3 encounters are hot-upgraded to `warzone_player_threat_window_v3`. A deterministic migration roll moves the enemy into the legal v0.7.3 level window; HP percentage is preserved when new HP/ATK/DEF/SPD are calculated. Repeated reloads cannot reroll or heal the enemy.
- Existing Commander/Mother Base/R&D progression, SPD accuracy/evasion, Security escort support, Command Centre navigation and all unrelated systems remain unchanged.

### Compatibility
- Application version advances **0.7.2 → 0.7.3**. Schema revision remains **8**; no database migration is introduced.
- CSS, JavaScript, `database/install_schema.sql` and runtime art remain unchanged.

## v0.7.2 — Command Centre Navigation Label Polish — XAMPP Test Candidate

### Navigation terminology correction
- Renamed the primary top-navigation link for `fob.php` from **FOB** to **Command Centre** so the navigation matches the page's established Invasion Command Centre identity.
- This is intentionally a presentation-only navigation correction. Existing FOB terminology remains unchanged where it describes actual Forward Operating Bases, FOB worlds/maps, targets, shields, raids, strike operations and persistence contracts.

### Compatibility
- Application version advances **0.7.1 → 0.7.2**. Schema revision remains **8**; no migration is introduced.
- No combat, Mother Base progression, enemy scaling, Security support, FOB gameplay, CSS, JavaScript, database schema/install SQL, runtime art or persistence behavior is changed.

## v0.7.1 — Polished Commander Progression & Combat Fairness Hotfix — XAMPP Test Candidate

### Immediate Mother Base staffing progression
- Fixed Commander combat bonuses using only completed integer Mother Base sector levels. The runtime projection now consumes persisted `base_sectors.score` continuously: 120 score remains one full development step, while partial progress contributes proportionally before the next displayed level. A non-empty staffed sector also receives a minimum visible contribution for each mapped stat so the first valid assignment cannot appear inert because of integer rounding.
- Increased all seven sector combat-growth rates so Mother Base development creates a clearly measurable power advantage instead of merely offsetting enemy scaling. R&D now contributes substantial ATK plus SPD, with Combat/Medical/Security/Intel remaining the primary ATK/HP/DEF/SPD paths and Support/Mess supplying mixed growth.
- Staff Management now shows live Commander HP/ATK/DEF/SPD and reports resulting whole-stat deltas after personnel reassignment. Mother Base stat cards now show each sector's live score and current contribution.
- Slightly strengthened the personal Commander level curve so levelling itself remains beneficial independent of Mother Base development.

### PvE accuracy and enemy fairness correction
- Added an authoritative player attack profile. Commander SPD has no offensive penalty path and only increases PvE hit chance; Combat/Intel may add small supporting bonuses. Final PvE attack accuracy has a 94% floor and 100% ceiling and is displayed in the battle command selector.
- Retained SPD as a defensive benefit with a revised enemy counter profile: 88% base counter accuracy, SPD-derived reduction, Intel Lv8 stacking, and a 55% floor.
- Fixed enemy counterattack power double-scaling. Counter move power is now based on enemy class plus threat while enemy ATK is applied once through the shared damage formula.
- Softened normal/boss enemy HP/ATK/DEF/SPD growth coefficients and shifted all player-relative level distributions toward below/equal contacts. The −3..+2 hard window remains and +2 is now exactly 1% in every threat band.
- Updated encounter scaling metadata from `warzone_player_window_v1` to `warzone_player_window_v2`. Active v0.7.0 encounters are hot-upgraded on their next load by recalculating enemy stats through v2 while preserving current enemy HP percentage and the already-rolled level/threat.

### Security escort support
- Raised Security covering-fire accuracy and restrained support damage so selected escorts contribute more consistently without replacing Commander damage.
- Increased rotating damage interception from the prior 13–28% envelope to an approximately 20–40% envelope, still bounded by escort HP and preserving KO/no-refresh-heal behavior.

### Compatibility
- Application version advances **0.7.0 → 0.7.1**. Schema revision remains **8**; no migration is introduced.
- CSS, JavaScript, `database/install_schema.sql`, runtime maps/sprites/art and established recovery/R&D/Dispatch/PvP/FOB/social persistence contracts remain unchanged.

## v0.7.0 — Polished Commander / Mother Base / Warzone Balance — XAMPP Test Candidate

### Commander and Mother Base progression
- Added authoritative automatic Mother Base combat-stat contributions for all seven sectors. Combat → ATK, Medical → MAX HP, Security → DEF, Intel → SPD, with R&D, Support and Mess providing complementary mixed-stat growth.
- Commander personal-level base stats remain unchanged and Mother Base bonuses stack on top, preserving existing level progression while making base development a real combat-power path.
- Added a diminishing effective-sector curve: full contribution through Lv10, 65% contribution across Lv11–20, and 40% contribution thereafter to prevent runaway late-game scaling.
- Added Mother Base and Command-page telemetry for current bonus contributions using existing presentation components.

### Warzone and enemy progression rebalance
- Replaced direct raw-stat scaling from Commander level with a bounded player-relative enemy level roll plus independent warzone/operation threat scaling.
- Normal enemy levels are always constrained to Commander Lv −3 through Lv +2 (with Lv1 floor). Low-threat zones bias toward −3/−2/−1; high-threat zones bias toward equal/+1; +2 remains rare.
- Enemy HP/ATK/DEF/SPD are now derived from the actual rolled enemy level and threat. Bosses use a gentler dedicated scaling profile on top of their already-high catalog base stats.
- Battle state persists enemy level offset, threat and scaling-model metadata for deterministic display/reload behavior after encounter creation.

### Security escort and combat-support polish
- Security escorts now carry battle-local HP and rotate damage-interception duty. A living escort absorbs a controlled portion of incoming Commander damage based on Security Team level plus that escort's Security stat, capped at 28%.
- Escort battle HP persists proportionally through battle support resynchronization, preventing refresh/reload healing exploits. KO'd escorts no longer intercept or provide covering fire.
- Covering-fire output was increased slightly while retaining strict per-hit enemy-max-HP caps and the tighter boss ceiling, so escorts help without replacing the Commander.
- Commander SPD now contributes to PvE counter-evasion when faster than the enemy (up to −8 counter accuracy); Intel Lv8's existing −6 benefit stacks with it, with a 50% enemy-accuracy floor.

### Compatibility and release boundary
- Application version advances **0.6.1 → 0.7.0**. Schema revision remains **8**; no database migration is introduced.
- Existing CSS, JavaScript, `database/install_schema.sql` and runtime image assets are intentionally unchanged.
- Existing recovery, inventory, R&D, Dispatch, PvP, FOB, autonomous Commander, social and persistence contracts remain outside this balance pass.

## v0.6.1 — Production Gamer Readability Pass — XAMPP Test Candidate

### Full-site player-facing copy pass
- Rewrote visible production copy across landing/onboarding, Command, Warzone, Mother Base, staff, missions, battles, Dispatch, R&D, Strategic Systems, PvP, AI Commanders, Community, rankings and all FOB invasion/deployment/reporting surfaces.
- Replaced implementation-facing explanations with concise player language while retaining useful Metal Slug Warzone terminology such as Mother Base, FOB, R&D, Fulton, Security, Strike Force and PvP.
- Removed ordinary player exposure to wording such as server-authoritative, atomic ledger, persisted rows, MySQL/Apache timing, immutable snapshots and version-locking where those concepts did not help the player make a gameplay decision.
- Added presentation-only label helpers for PvP modes/statuses, Dispatch states and FOB raid outcomes so stored backend values render as natural game text without changing database contracts.
- Polished buttons, helper copy, warnings, tooltips, empty states, combat Intel/Fulton guidance, account feedback, AI activity text and FOB navigation terminology for consistency.

### Regression boundary
- Application version advances **0.6.0 → 0.6.1**; schema revision remains **8**.
- No database schema/install SQL changes.
- No CSS sizing/layout changes and no JavaScript behavior changes.
- No artwork, map, character sprite or other runtime image changes.
- No combat, recovery, dispatch, PvP, FOB, AI, economy, persistence or social gameplay rule changes; edits are limited to player-facing presentation text and label formatting.

## v0.6.0 — Advanced FOB Invasion Command Centre, Retaliation & Command Network Visual Overhaul — XAMPP Test Candidate

### Integrated FOB Command Centre
- Replaced the deployed-commander's `fob.php` redirect with a full **Invasion Command Centre** that is now the primary strategic FOB surface rather than a bolt-on utility page.
- Added a command-status strip for recovery-shield state, active outbound invasions, detected inbound staff strikes, retaliation orders and globally open targets.
- Added a global priority targeting matrix with direct invasion controls and full target-intel links while retaining Earth/theatre/shard browsing for detailed reconnaissance.
- Added a **Multi-Invasion Staff Strike Planner** that can launch repeated 2–4 member operations against different open targets. Each operation continues to use its own persisted `fob_strike_dispatches` row and the shared `units.dispatched_until` reservation contract, so parallel invasions are naturally bounded by actually available staff.
- Added live outbound-operation and inbound-threat boards with persisted ETA countdowns, target/world context and stored success chances.
- Added recent outgoing After Action Reports directly to command so direct, staff and retaliation results can be reviewed without leaving the strategic hub.

### One-use retaliation system
- Added the **Retaliation Command Desk** using the existing authoritative `fob_raids` history as the incident source. Each incoming raid can authorize exactly one retaliatory direct strike against that exact attacker.
- Added nullable `fob_raids.retaliation_for_raid_id` with unique index `uq_fob_retaliation_source`. The unique database constraint prevents double-click/replay/race attempts from consuming the same incoming incident twice.
- Retaliation revalidates the original attacker/defender relationship, the target's current global FOB membership, optional world context and target protection inside the normal locked raid transaction.
- Retaliation does **not** bypass defender protection. If the attacker is currently protected, the command desk tracks the shield countdown until that target becomes eligible again.
- Retaliation After Action Reports are tagged as `retaliation` in the immutable attacker snapshot and link back to the original incident.

### Offensive protection doctrine
- Defender protection remains the anti-drain recovery boundary after every completed invasion attempt.
- A commander may still launch offensively while personally protected, but a **successfully committed direct invasion, retaliation or staff-strike launch immediately removes the attacker's remaining protection** in the same database transaction.
- Invalid, stale, protected-target or otherwise rejected launch attempts do not remove the attacker's shield.
- The rule applies to human and autonomous commanders because both direct and staff invasion authority share the same server-side functions.
- Protected-state actions exposed by the browser display a confirmation warning, but PHP/MySQL—not JavaScript—owns the actual shield removal.

### Schema, compatibility and polish
- Schema revision advances **7 → 8** with the additive nullable retaliation source column and unique index only. Existing users, resources, staff, FOB memberships, coordinates, dispatches and raid history remain intact.
- `_setup.php` Update / Repair adds the column/index idempotently and Confirm Installation now reports `fob_retaliation_integrity`.
- Existing `fob_target.php`, shard targeting, infiltration ledger, strike ledger, globe and raid-report screens now link back into the Command Centre and surface the offensive protection doctrine consistently.
- Added a full supplied-art **Command Network visual integration** across the website. All 23 supplied JPGs are stored byte-for-byte in `public_html/assets/artwork/` and mapped coherently to their gameplay systems instead of being used as detached decorative banners.
- Replaced the old green-dominant website tone with a gunmetal/charcoal base, sunset amber/orange command accents, steel/cyan information states and semantic green/red success/threat states derived from the supplied artwork.
- Added per-resource telemetry colours for Common Metal, Minor Metal, Precious Metal, Fuel, Biological and Strategic Devices, plus stronger value colouring for GMP, Base Power, Security and other key command statistics.
- Added a moving CRT scan beam, top-bar command-link pulse/sweep, selected-operative sprite presence inside page heroes, restrained sprite idle motion, panel/card elevation and overlap, button glints, map-card zoom, and viewport reveal choreography. Motion is presentation-only and honors `prefers-reduced-motion`.
- Existing accepted maps/sprites remain byte-identical; the new artwork is copied without resize/recompression and no generated image is included.
- Updated README, architecture, FOB authority, security, asset manifest, build validation and XAMPP release-blocking acceptance documentation for v0.6.0.


## v0.5.0 — Polished Combat Support, Global FOB Invasion & Competitive AI — XAMPP Test Candidate

### Mother Base progression and manufacturing
- Moved **Cargo Fulton** from R&D 8 to **R&D 5** and **Wormhole Fulton** from R&D 15 to **R&D 8** while preserving standard Fulton at R&D 1 and Fulton+ at R&D 4.
- Added server-authoritative multi-sector R&D requirements and three persistent medical consumables: Combat Medkit (R&D 2 + Medical 2, 35 HP), Trauma Kit (R&D 5 + Medical 5, 80 HP), and Nanomed Injector (R&D 8 + Medical 8, 160 HP).
- Added functional Intel milestones at levels 2/4/6/8, Security milestones at 1/4/7, and Support medical-logistics bonuses at 3/6.
- Added a live Mother Base **Capability Matrix** so active/locked sector systems are visible from the same catalog that controls gameplay.

### Combat support and battle presentation
- Added `security_backup_slots` and a Staff UI for selecting up to two Security-assigned infantry/heavy-infantry escorts. Dispatched staff are excluded automatically and reassignment away from Security clears the slot.
- Security escorts provide automatic covering fire after player actions with deliberately reduced attack scaling, conservative hit chance and a per-hit enemy-max-HP ceiling; bosses use an even lower ceiling.
- Added in-battle medical item use with atomic inventory consumption, full-HP waste prevention, turn consumption, Support Team healing multipliers and normal enemy counterplay.
- Added Intel threat-stat reveal, move-effectiveness/recommendation display, exact Fulton forecast, and level-8 enemy accuracy countermeasure.
- Added shared PvE action FX metadata and CSS choreography for field contacts, missions, sidequests, rival commanders and bosses: attack lunge, hit reaction, enemy counter, Security covering fire, heal pulse and extraction feedback. PvP now records/renders matching turn-action animation. `prefers-reduced-motion` is honored.

### Global FOB invasion network
- Converted the deployed commander's Earth globe into a **Global Invasion Network**. Players can select a biome, inspect populated shard instances and enter any selected shard without changing their permanent home FOB membership.
- Added `fob_shards.php` as the populated-shard directory and made `fob_world.php`, target inspection, direct raids, infiltration lists and staff strikes world-context aware.
- Removed same-shard target authority for invasion only: a valid attacker must still own a FOB membership, but the defender may occupy any valid populated shard. Defender protection and transactionally locked resource transfer remain authoritative.
- Cross-shard staff strikes persist the defender's target `world_id`, reuse the shared `units.dispatched_until` reservation contract and keep restart-safe exactly-once settlement.
- Added recent incoming defense visibility on the globe and WorldServer `FOB · DEFENSE` events for human defenders.

### Autonomous commander pacing and competition
- Tightened persisted bot action scheduling from 12–32 seconds to **10–26 seconds** and introduced a **1.25× bounded pulse budget multiplier**.
- Modestly increased field combat XP/resource progression so the 1,000 persistent commanders develop at a more visible pace without bypassing the real roster, R&D, inventory or dispatch systems.
- Increased autonomous FOB action weighting to about **15%** of decisions (staff + direct combined).
- Expanded autonomous FOB target selection from same-shard bots to valid commanders across the global shard network, including human commanders. A **28% human-target preference** is applied when eligible targets exist.
- Reduced autonomous direct-raid resource transfer to 3% with lower caps to offset the new ability to pressure human bases. Existing defender post-invasion protection remains the anti-drain boundary.

### Schema, compatibility and validation
- Schema revision advances **6 → 7** with additive `security_backup_slots` (`PRIMARY KEY(user_id,slot_index)`, unique selected unit per user, cascading user/unit FKs).
- `_setup.php` requires the new table and reports `security_backup_integrity`; Update / Repair remains non-destructive and idempotent.
- Existing users, characters, inventory quantities, resources, staff/hardware, sector progression, FOB home world/slot/coordinates/skin, dispatches, raid history, PvP, social state and autonomous identities remain preserved.
- Updated README, architecture, FOB authority, security, asset manifest, build validation and XAMPP release-blocking acceptance documentation for v0.5.0.


## v0.4.1 — Polished FOB Spatial Distribution & Globe Alignment — XAMPP Test Candidate

### Globe deployment alignment
- Corrected all five normalized deployment hotspots against the supplied 1254×1254 globe.
- Continental now targets the gold Americas, Forest the green Eurasian biome, Desert the orange African biome, Arctic the polar ice, and Sea open ocean.
- Hotspot coordinates are centralized in `msw_fob_globe_hotspots()` so responsive percentage positioning remains tied to the square source artwork.

### Irregular persistent overview placement
- Replaced the visible 12×12 FOB row/column projection with 144 validated irregular native-map anchors.
- Each biome/shard uses a deterministic permutation of the anchor constellation. Partially populated shards therefore distribute occupants around the map instead of filling left-to-right/top-to-bottom rows or reproducing the same partial layout in every shard.
- The anchor catalogue is validated against a 136×96 center-clearance envelope, larger than the 128×86 desktop marker footprint, while `(world_id,slot_index)` remains the authoritative database exclusivity key.
- Human deployment and autonomous population placement now derive x/y using biome + shard identity.

### Non-destructive repair
- Schema revision advances **5 → 6**.
- Update / Repair preserves every user's world, shard, slot, skin, progression and history and only recalculates stored FOB membership x/y from the existing authoritative slot.
- Confirm Installation adds `fob_spatial_distribution` validation for exact v0.4.1 x/y projection and rendered-marker clearance.
- No runtime artwork, invasion rules, staff-dispatch rules, defender protection, bot identity or standard Dispatch behavior changed.

## v0.4.0 — Polished Sharded Global FOB World — XAMPP Test Candidate

### Global overview deployment
- Added Earth overview selection for Continental, Forest, Desert, Arctic and Sea FOB theatres using the supplied production globe.
- Added a second-stage coherent skin selector. Land biomes expose their matching land base; Sea exposes Offshore Alpha, Offshore Bravo and Maritime Fortress.
- New accounts enter this flow immediately after signup; upgraded human accounts enter once on first FOB/Mother Base access.
- Once deployed, `mother_base_key` is synchronized to the chosen FOB skin and independent profile redeployment is locked.

### Unlimited-on-demand world sharding
- Added `fob_worlds` and `fob_world_memberships`. Biomes create sequential shards only when needed.
- Each 2000×2000 overview shard provides 144 fixed non-overlapping placement slots.
- Added database uniqueness on `(world_id,slot_index)` plus per-biome serialized assignment so simultaneous deployments cannot overlap.
- FOB membership is one-row-per-user and persists across logout, browser restart, Apache/PHP restart and database restart.
- Overview rendering auto-centers on the owner's FOB and presents human/AI rivals from the same shard as selectable world entities.

### Invasion reconstruction
- Replaced the attacker cooldown gate with unrestricted attacker pacing. `fob_attack_cooldown_seconds` is retained at `0` only as a compatibility configuration key and is no longer consulted by raid authority.
- Defender post-invasion protection is retained and now applies after every completed raid attempt, including a repelled attack.
- Immediate raids require attacker and defender to share the same persisted FOB world.
- Preserved transactional user/resource locks, immutable combat/security snapshots, exact resource debit/credit transfer and the existing `fob_raids` after-action ledger.
- Preserved the classic tabular target/raid screen as `fob_infiltration.php`, now scoped to the player's shard.

### Staff FOB dispatch invasions
- Added `fob_strike_dispatches` with launch/finish/resolution timestamps, attacker/defender snapshots, selected unit IDs, success chance, transfer result and linked raid report ID.
- Players commit 2–4 staff on the enemy FOB command screen. Staff use the existing `units.dispatched_until` reservation state, preventing overlap with standard Dispatch missions.
- Due invasions resolve once from persistent timestamps and return staff with XP. A successful staff invasion transfers resources and every completed impact applies defender protection.
- If another invasion protected the target before staff arrival, the mission resolves as `protected_abort`, returns the staff and performs no resource transfer.
- Existing standard Combat Unit Dispatch missions and `dispatch_missions` remain unchanged.
- Added shared standard-dispatch completion authority plus conditional reservation release so standard Dispatch and FOB staff strikes cannot reuse or accidentally release the same staff row at an expiry-boundary race.

### Autonomous commander integration
- Update / Repair assigns all 1,000 enabled bots to coherent persistent FOB worlds without changing their durable bot identity.
- Bots distribute deterministically at 200 per biome, creating additional 144-slot shards as required.
- Autonomous direct raids now select open bot FOBs from the attacker's own shard and have no attacker cooldown.
- Autonomous simulation can also launch/resolve timed staff FOB invasion dispatches using normal unit availability.
- Human commanders can continue to invade bot FOBs. Background bot aggression remains bot-v-bot only.

### Schema / packaging
- Schema revision advanced **4 → 5**. Migration is additive and non-destructive for v0.3.5 progression/history.
- Added runtime-only globe, five overview maps and seven FOB overview icon assets from the supplied asset archive.
- Added FOB world topology, staff strike and migration documentation/tests.

## v0.3.5 — Local WorldServer Console — XAMPP Test Candidate

### Local-only human activity console
- Rebased directly on the runtime-confirmed v0.3.4 Level-1 Fulton Manufacturing baseline.
- Added `serverconsole.bat` plus `serverconsole.ps1`, providing a dedicated color-coded WorldServer-style command window on the server PC.
- Added `includes/server_console.php`, a fail-silent structured application activity feed that records only authenticated human commanders (`users.is_bot=0`).
- Successful authenticated PHP traffic is emitted with commander identity, remote IP, HTTP method, route and completion time. HTTP 4xx/5xx shutdowns and fatal request terminations are skipped.
- Added color-coded gameplay action events for login/logout, combat attacks/results, Fulton outcomes, R&D manufacturing, staff assignment, dispatch deployment/results, strategic project actions, FOB raids, PvP match/turn activity, social actions and profile/Mother Base changes.
- Warzone movement, warzone presence polling, Mother Base movement, Mother Base presence polling and PvP state polling are hard-suppressed. No movement coordinates or movement requests are emitted.
- The console is intentionally not an error log. It does not ingest PHP/Apache/MySQL errors or exceptions, and logger failures are swallowed so monitoring cannot interrupt gameplay.
- Sensitive request data is excluded: passwords, cookies, sessions, CSRF tokens, raw POST payloads and direct-message bodies are never persisted.
- The NDJSON feed lives outside `public_html` in `_server_console/`, is additionally protected by deny-all `.htaccess`, uses file locking, rotates at 8 MiB and keeps three historical generations.
- The PowerShell renderer follows rotation, replays only the latest 80 events on startup, provides category-specific console colors and supports `C` clear / `Q` quit controls.

### Preservation
- Schema remains revision **4**; no database migration or reset is required.
- v0.3.4 Level-1 Fulton manufacturing remains unchanged at 60 Common Metal + 40 Fuel -> x4 Fulton, with higher tiers still locked at R&D 4/8/15.
- Autonomous commander simulation, per-warzone operative variety, Mother Base movement, maps, combat authority, dispatch, FOB, PvP and social gameplay remain unchanged outside the new observational event hooks.
- Runtime artwork is unchanged and runtime-only packaging remains enforced.

## v0.3.4 — Level-1 Fulton Manufacturing — XAMPP Test Candidate

### Early R&D progression deadlock fix
- Rebased directly on the v0.3.3 Per-Warzone AI Operative Variety public baseline.
- Added the standard `fulton` item to the normal R&D manufacturing catalog at **R&D Level 1**, making the first personnel-recovery system manufacturable from the beginning of R&D progression instead of relying permanently on finite starter/Field Contract stock.
- The basic recipe manufactures **4 Fulton Recovery units** for **60 Common Metal + 40 Fuel**, giving a sustainable early-game recovery loop while still consuming persistent server-authoritative resources.
- `Fulton+ Balloon Pack` remains gated at **R&D 4**, `Cargo Fulton Pack` at **R&D 8**, and `Wormhole Fulton` at **R&D 15** with their existing costs, yields, recovery bonuses and target-class restrictions unchanged.
- Battle-side recovery validation remains server-authoritative: inventory is consumed normally and each Fulton tier still requires the R&D level defined by `msw_fulton_catalog()`.

### Preservation
- Schema remains revision **4**; no database migration or progression reset is required.
- Existing starter inventories, Field Contract rewards, autonomous commander systems, per-warzone operative variety, compact AI labels, Mother Base, dispatch, FOB and PvP behavior are unchanged.
- Runtime artwork is unchanged and runtime-only packaging remains enforced.

## v0.3.3 — Per-Warzone AI Operative Variety — XAMPP Test Candidate

### Clone-army correlation fix
- Rebased on the runtime-accepted v0.3.1 Compact AI Warzone Labels baseline plus the v0.3.2 in-place skin-repair work.
- Corrected the v0.3.2 mapping defect where both warzone slot and operative slot used the same `bot_index mod 6`, causing each individual map to contain one repeated operative skin.
- Fresh seeding now rotates operative skins **inside each warzone** using the local per-map ordinal plus map offset.
- Update / Repair now reconciles already-persisted bot indexes 1–1000 in-place by current warzone and durable `bot_index`, cycling Marco, Tarma, Eri, Fio, Nadia and Trevor independently within every map.
- Each 166–167-bot warzone now receives approximately **27–28 of every operative skin**, with all six skins represented on every map.
- Repair changes only bot `users.character_key`; identity, username, XP, coordinates, roster, captures, resources, Mother Base, dispatch, FOB and PvP state remain untouched.
- Confirm Installation now validates **per-warzone** six-skin diversity instead of accepting only a globally balanced population.

### Preservation
- Compact v0.3.1 `AI` hover/focus labels remain unchanged.
- Schema remains revision **4**.
- All gameplay PHP outside population repair/setup health reporting remains unchanged.
- All 31 accepted runtime images remain unchanged.
- Runtime-only packaging remains enforced with no source archives.

## v0.3.2 — Balanced AI Operative Variety — XAMPP Test Candidate

### Autonomous commander visual variety repair
- Rebased directly on the runtime-accepted v0.3.1 Compact AI Warzone Labels baseline.
- Fixed existing persistent autonomous populations that could remain on one old/default operative skin even though the fresh seeder already cycled the six-character catalog.
- Update / Repair now deterministically reconciles bot indexes 1–1000 across Marco, Tarma, Eri, Fio, Nadia and Trevor at **167/167/167/167/166/166**.
- The repair updates only each bot user's `character_key`; bot IDs/usernames, progression, coordinates, rosters, resources, Mother Bases, dispatches, FOB history and PvP records remain untouched.
- Re-running repair is idempotent and preserves the same skin assignment for each durable `bot_index`.
- Confirm Installation now validates and reports the six-skin autonomous population distribution.

### Preservation
- Compact v0.3.1 `AI` hover labels remain unchanged.
- Schema remains revision **4** with no structural database migration.
- All accepted v0.3.1 gameplay, bot simulation, maps, collisions and runtime artwork remain unchanged.
- Runtime-only packaging remains enforced with no source asset archives.

## v0.3.1 — Compact AI Warzone Labels — XAMPP Test Candidate

### Warzone readability polish
- Rebased directly on the runtime-accepted v0.3.0 Persistent Autonomous Commanders baseline with no gameplay, schema, population or asset changes.
- Autonomous commander warzone labels now render as a compact **AI** pill by default, removing the full callsign/nameplate wall created by ~166–167 bots per warzone.
- Hovering an AI operative sprite or its AI pill reveals that exact commander's complete `callsign · AI COMMANDER · grade` identity.
- Keyboard focus reveals the same full identity and the profile link keeps a full accessible `aria-label`.
- Presence refreshes continue updating keyed DOM entities in place; the full hover identity/activity is refreshed without forcing the compact label open or causing entity teardown/recreation.
- Human player labels remain unchanged and fully visible.

### Preservation
- Schema remains revision **4**.
- Exactly 1,000 persistent autonomous commanders, collision-aware movement, field/Fulton progression, dispatches, Mother Base growth, FOB systems, Snapshot/Live AI PvP and bot-v-bot simulation remain unchanged.
- Runtime-only packaging remains enforced: no `source_assets/` directory and no nested development/source ZIP archives.

## v0.3.0 — Persistent Autonomous Commanders — XAMPP Test Candidate

### 1,000 persistent AI commanders
- Added exactly 1,000 database-backed autonomous commanders, normally presented as `WarzoneAI0001`–`WarzoneAI1000`, using the same six selectable Metal Slug operative skins as human commanders. `bot_index` is the durable identity; if a pre-existing human account already owns one of those presentation names, migration chooses a deterministic conflict-safe AI name without modifying the human account. Bot accounts are explicitly marked `is_bot=1`, cannot authenticate through the normal login flow, and retain identity/state across browser, Apache and MySQL restarts.
- Fresh Install and Update / Repair seed the population idempotently through stable `bot_index` values 1–1000. Re-running repair does not duplicate identities; experimental indexes above the production population are disabled rather than deleted.
- Fresh population distribution is balanced across all six warzones (167/167/167/167/166/166) and spatially sampled across each map's complete legal collision lattice instead of stacking commanders around a single spawn.
- Added a persistent `bot_commanders` runtime ledger for personality, activity, autonomous scheduling/leases, encounter/capture counters, FOB performance and PvP performance.

### Autonomous warzone simulation and progression
- Bots use the accepted server-authoritative 18px four-way movement path and exact v3 collision profiles. Autonomous movement cannot cross walls, cliffs, fences, buildings, machinery, water or other blocked terrain.
- Simulation is bounded and request-driven: small leased batches advance from map/presence and network surfaces instead of spawning 1,000 PHP workers. Per-bot `next_action_at` timestamps prevent extra browser clients from accelerating a commander's schedule.
- Bots resolve field contacts, earn persistent Commander XP/resources, use Fulton items, recruit actual `units` rows and grow their active combat teams/sector staffing. Recovery obeys the same roster, R&D and Cargo Fulton restrictions as player recovery.
- Autonomous staff assignment deliberately develops R&D when appropriate so mature bots can legitimately unlock Cargo Fulton and recover vehicle-class contacts rather than bypassing player progression rules.
- Bots reorganize Mother Base staff, recalculate sector levels/Base Power, manufacture/restock recovery equipment from real GMP/resources, and use the real persisted `dispatch_missions` ledger. Autonomous dispatches remain pending against MySQL `finish_at`, survive server/browser restarts, return deployed units, and resolve rewards/unit XP exactly once.
- Captured personnel/hardware remain normal persistent roster entities and therefore feed the accepted physical Mother Base garrison/parking system.

### FOB and PvP parity
- Human players can select AI commander FOBs through the existing transactional FOB snapshot/infiltration system. Bot resources, Base Power, Security Team and active Combat Unit are real authoritative defender state and real successful transfers debit the bot's stock.
- Bots autonomously raid other bots using the same snapshot/resource-ledger principles, attacker cooldown and defender protection. Autonomous bot raids intentionally do not silently drain human commanders while they are offline.
- Added server-driven **Live AI PvP** and immutable **Commander Snapshot Battle** modes against bots. The selected Metal Slug operative and persistent Commander stats are snapshotted at match creation; bot turns are committed server-side without a bot browser/session.
- Added autonomous bot-v-bot PvP simulations recorded in the shared `pvp_matches` ledger with persistent XP and win/loss counters.
- Added `match_mode` (`live`, `live_ai`, `snapshot`, `ai_sim`) to PvP persistence while preserving version-locked turn settlement.

### Multiplayer presence and operator surfaces
- Warzone presence now includes all bots assigned to the current map alongside human players. AI commanders are clickable, use player-character sprites/facing, and expose current autonomous activity through a dedicated profile.
- Reworked remote-presence JavaScript to update keyed DOM entities in place instead of deleting/recreating ~166+ bot avatars every 3-second refresh.
- Added **AI Network** with total population, exact per-warzone counts and a recent autonomous-activity roster.
- Added AI indicators to profiles, rankings, FOB targets and PvP surfaces. Bot profiles expose field/capture/FOB/PvP counters plus direct Live AI and Snapshot challenge actions.
- Bot identities are excluded from friend requests/direct messages because they cannot consent/respond through a login session; combat/FOB interaction remains fully available.

### Schema / setup / packaging
- Schema revision advances from 3 to **4** with additive `users.is_bot`, `bot_commanders`, and `pvp_matches.match_mode` changes. Existing human accounts, Mother Bases, rosters, resources, dispatches, FOB history, social state and Commander progression are preserved.
- Local `_setup.php` Confirm Installation now verifies exactly 1,000 enabled/distinct bot indexes and balanced six-warzone distribution. Local setup removes the normal PHP execution-time limit while population seeding/repair runs.
- Runtime-only packaging remains mandatory: no `source_assets/` directory and no nested source/development ZIP archives are shipped.


## v0.2.0 — Persistent Mother Base Visitation — XAMPP Test Candidate

### Player-selected physical Mother Bases
- Added seven user-supplied physical Mother Base/FOB maps: four land variants and three sea variants.
- Account creation now requires a Mother Base selection in addition to the field operative.
- Added `mother_base_key` to commander persistence. Existing accounts migrate non-destructively to `land_dirt` and can change bases from Account Options at any time.
- Changing the selected base preserves all Commander XP, resources, captured units/hardware, sector progression, dispatches, R&D, FOB history, social state and PvP state.

### Live Mother Base spaces
- Added `mother_base.php` as a native-size close-up shared base viewport with camera auto-follow and external WASD/Arrow command controls.
- Added server-authoritative Mother Base collision profiles covering buildings, perimeter structures, machinery, cargo/props, platform edges and ocean boundaries.
- Added nearest-legal-position repair for visitors when a base selection/layout changes.
- Entering a Mother Base clears warzone presence so an account cannot be authoritative in two physical spaces simultaneously.

### Persistent staff and hardware simulation
- Added `mother_base_unit_positions` persistence keyed directly to owned `units`. Newly recovered units appear automatically on the owner's current physical base.
- Human staff receive persistent anchor positions and move only in slow 10-pixel server-timed steps every 7–16 seconds. Movement is collision-checked and limited to a local patrol radius to avoid fast/unrealistic wandering.
- Captured vehicles/air-class hardware remain stationary in safe parking positions. Legacy retired vehicle source keys normalize visually to the current Rebel Biker runtime asset.
- Presence polling updates existing staff/visitor DOM entities in place for smooth movement without re-entering or manually refreshing the base.

### Friend and Strike Force visitation
- Established friends can visit one another's selected Mother Base directly from the Friend Network/profile.
- Members of the same Strike Force can visit one another's bases from the Strike Force member roster.
- Authorization is revalidated server-side on entry, every movement request and every presence refresh.
- Added dedicated `mother_base_presence` persistence for visitors; physical-base presence is separate from warzone presence.

### Schema / packaging
- Schema revision advanced from 2 to 3 with additive, non-destructive migration.
- Added `mother_base_presence` and `mother_base_unit_positions` tables.
- Runtime-only packaging policy is preserved. The supplied Mother Base source ZIP is not shipped.

## v0.1.4 — V3 Native Map + Collision — XAMPP Test Candidate

### Runtime-only packaging correction
- Removed the entire `source_assets/` tree from the distributable XAMPP package.
- Full source libraries and replacement ZIP archives are development inputs only and are not shipped with the runtime candidate.
- Runtime PNG assets, PHP, JavaScript, database files and documentation required to operate/test the game remain packaged.

### Authoritative v3 warzones
- Rebased directly on the runtime-working v0.1.3 candidate and replaced all six deployed warzone PNGs with the exact user-supplied v3 map files.
- New native dimensions are 1448×1086, 1402×1122, 1448×1086, 1254×1254, 1448×1086 and 1448×1086. Runtime art is never scaled to fit the page.
- Preserved existing map keys for database/update compatibility while updating theatre presentation metadata to match the new overhead environments.
- Added explicit safe spawn coordinates for every v3 map.

### Native close-up warzone viewport
- Reworked the battlefield viewport to a fixed close-up camera window while leaving the map world at exact 1:1 native pixels.
- Removed the old short-map adaptive viewport behavior; the new square/overhead maps intentionally extend beyond the camera and are explored by scrolling.
- Auto-follow remains centered on the local player after accepted movement, and manual scroll remains available.
- Directional buttons remain outside the map viewport and therefore cannot scroll off-screen with the level art.

### Server-authoritative terrain collision
- Added a dedicated authored collision catalog for each of the six v3 maps, covering solid perimeter terrain plus major walls, cliffs, fences, buildings, machinery, water, pits, barricades and prop clusters.
- Collision is validated in PHP using the player's native-map foot point; browser DOM/CSS state is never authoritative.
- Every movement request checks the complete travel segment in 4-pixel samples, preventing an 18-pixel movement step from tunneling through a thin blocker.
- Blocked moves preserve the authoritative position, update facing, do not roll random encounters, and return a descriptive blocked-terrain status to the field-control UI.
- Added automatic nearest-legal-position recovery for persisted v0.1.3 coordinates that land inside solid v3 terrain after update. No database reset is required.

### Preservation
- Schema revision remains 2. Commander XP, inventory, captured units, Mother Base, resources, dispatches, FOB state, social state and PvP state are preserved.
- Existing polished character/contact assets from v0.1.3 remain authoritative and unchanged.
- Runtime uses the exact supplied v3 map PNGs at native resolution; source ZIP archives are intentionally excluded from the distributable package.

## v0.1.3 — Polished Asset Replacement — XAMPP Test Candidate

### Authoritative user-supplied production art
- Rebased directly on the runtime-tested v0.1.2 project and integrated the user-supplied `All assets to use inplace of all old.zip` as the authoritative replacement set for active field presentation.
- Replaced all six deployed warzone images with the supplied v2 maps at their exact native dimensions: 922×236, 2123×214, 898×242, 3056×224, 3105×221 and 1480×256.
- Replaced the playable-operative runtime art for Marco, Tarma, Eri, Fio, Nadia and Trevor with the supplied polished assets. Marco/Tarma/Eri/Fio/Nadia use the supplied left/right frames; Trevor uses the supplied right frame with a runtime mirror fallback only when facing left because no Trevor-left replacement was present in the supplied archive.
- Replaced active field-contact art for Rebel Rifleman, Bazooka Trooper, Shield Trooper, Heavy Gunner and Rebel Biker with the supplied polished assets.
- Retired Girida-O, Di-Cokka and R-Shobu from new field/mission/trainer encounter catalogs so no retired tank/gunship runtime sprite is selected. The Rebel Biker is now the vehicle-class contact used by those affected encounter roles.
- Existing active encounters created by an earlier build are normalized to the production Rebel Biker visual/contact metadata on the next battle load/action so an in-progress old contact cannot reference a removed runtime sprite.
- Existing boss art remains unchanged because the supplied replacement archive contains no Huge Hermit or Rootmars replacement files.

### Directional multiplayer presentation
- Multiplayer presence now transmits left/right operative sprite references and uses the supplied directional frame matching player facing.
- Local movement swaps to the correct supplied left/right operative asset immediately as horizontal facing changes.
- Trevor's left-facing state uses a presentation-only mirror of the supplied right frame; no generated or substitute image file was created.

### Native-map update safety
- Persisted map coordinates are clamped against the new v2 native dimensions when a commander deploys, preventing positions from an older larger map from rendering outside the replacement theatre after an update.
- Server-authoritative movement bounds continue to use the catalog's exact replacement-map dimensions.

## v0.1.2 — Production Runtime Polish — XAMPP Test Candidate

### Dispatch interaction correction
- Replaced modifier-key-dependent multi-select controls with explicit checkbox unit cards for every dispatch mission.
- Added live selected-slot counters, exact-slot client gating, server-side array validation and unavailable-team messaging.
- Preserved authoritative unit availability checks, mission power snapshots, database completion timestamps and one-time unit XP resolution.

### Runtime sprite reconstruction
- Re-audited every currently used playable-character, enemy, vehicle, aircraft and boss sprite against the supplied source library.
- Replaced composite animation-sheet regions, annotation-bearing frames and visually incomplete selections with clean single-frame source art.
- Corrected runtime presentation for Marco, Tarma, Eri, Fio, Nadia, Trevor, Rifleman, Bazooka Trooper, Shield Trooper, Heavy Gunner, Rebel Biker, Girida-O, Di-Cokka, R-Shobu, Huge Hermit and Rootmars.
- Added dedicated boss framing and class-aware battle sprite sizing while preserving pixel-art rendering.

### Warzone viewport and movement reconstruction
- Rebuilt the deployed-warzone page around a fixed native-size scroll viewport instead of a page-filling battlefield container.
- Viewport height now tracks the native map height up to a controlled maximum, eliminating the large unused black field on short side-scroller maps.
- Moved the directional command pad completely outside the scrolling map so controls never disappear while the battlefield scrolls.
- Added a sticky field-control sidebar, operative card, map metadata and scroll/readiness presentation.
- Reworked keyboard movement into a throttled latest-input queue using physical Arrow/WASD key codes.
- Added clean server 429 retry timing so key auto-repeat no longer causes movement to appear stuck.
- Preserved authoritative movement bounds, encounter locking and mandatory multiplayer presence.

### Commander Level / XP progression
- Promoted existing account XP into a fully presented Commander progression system.
- Added XP floor/next-level helpers, progress percentage, Command Rank display and level-derived HP/ATK/DEF/SPD.
- The selected field operative is now the actual PvE/PvP Commander combat profile instead of showing a recovered unit callsign under the operative sprite.
- Field victories, missions, Field Contracts, Rival Commander wins, boss victories and successful Fulton recoveries now visibly award Command XP; level-up events are recorded in the combat log.
- Live PvP now awards persistent Command XP to both winner and loser exactly once when the version-locked match resolves.
- Added Commander level/XP presentation to the global header, Command Center, profile and deployed-warzone sidebar.
- Existing active PvE encounters are normalized to the Commander profile on load/action, preserving the current HP ratio during an update.


## v0.1.1 — Production Foundation Audit — XAMPP Test Candidate

### Correctness
- Fixed a release-blocking Fulton recovery parse error in the battle engine.
- Corrected Combat Unit selection so four active units are permitted as intended.
- Added Mother Base sector-capacity enforcement during reassignment.
- Reworked dispatch XP handling so XP is applied exactly once and unit level resolution occurs under a row lock.
- Prevented movement while an unresolved PvE encounter exists.
- Added server-side movement burst throttling.

### Multiplayer / map polish
- Local map labels now follow the player instead of remaining at the spawn coordinate.
- Movement now recenters the scroll viewport smoothly.
- Presence payloads now include facing state.
- Presence polling cadence and remote rendering were refined.
- Live PvP now watches the authenticated match-version endpoint and refreshes automatically when the opponent commits a turn.
- Encounter frequency was reduced to improve actual exploration pacing.
- Added restrained sprite motion to command UI, character cards and battle presentation, with `prefers-reduced-motion` support.

### PvE expansion
- Added Field Contracts as a side-operation system.
- Added Rival Commander trainer-style battles with named opponents and persistent clear counts.
- Rival Commander deployed units cannot be Fulton recovered during command duels.
- Expanded encounter contexts to field, mission, sidequest, trainer and boss.

### FOB hardening
- Added consistent user/resource row-lock ordering to reduce cross-raid deadlock risk.
- Added attacker raid cooldown.
- Added temporary defender protection after a successful resource theft.
- Preserved exact atomic attacker credit / defender debit behavior.
- Captured attack/defense resolution rolls inside immutable raid snapshots.

### Account / social hardening
- Account creation and starter roster initialization now commit atomically.
- Added direct-message burst throttling.
- Fixed invalid non-friend message selections from rendering as usable channels.
- Reciprocal pending friend requests now establish the friend link cleanly.

### Installer / schema
- Advanced schema to revision 2.
- `_setup.php` Update / Repair now performs additive migrations instead of only creating missing tables.
- Confirm Installation now checks the complete required table set and expected schema revision.
- Fresh Install requires explicit `RESET` acknowledgement.
- Added a static SQL schema reference under `database/`.

## v0.1.0 — Production Foundation
- Initial server-authoritative Metal Slug Warzone browser MMORPG foundation.
