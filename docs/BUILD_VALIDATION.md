# Build Validation — v0.6.0 Advanced FOB Invasion Command Centre, Retaliation & Command Network Visual Overhaul

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
