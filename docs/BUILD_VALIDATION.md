# Build Validation — v0.8.1 Corrected Peace Walker-Style Automatic Battle Playback


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
