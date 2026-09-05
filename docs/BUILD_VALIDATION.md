# Build Validation — v0.5.0 Polished Combat Support, Global FOB Invasion & Competitive AI

## Static release gate completed

The v0.5.0 candidate was validated in the build container after implementation and documentation updates.

- PHP runtime used for syntax validation: **PHP 8.4.23**.
- All **57 PHP files** under `public_html/` pass `php -l` with zero syntax errors.
- `public_html/assets/js/msw.js` passes `node --check` under Node **v22.16.0**.
- `public_html/assets/css/msw.css` passes structural brace-balance validation (**597 blocks**).
- Literal `require` / `require_once` paths were checked and all referenced files exist.
- Static `msw_url('*.php')` page references were checked and all referenced PHP pages exist.
- Pure combat-support assertions passed for Support healing multipliers, Intel move recommendation, and Security escort damage ceilings across repeated non-boss/boss samples.
- FOB slot mapping assertions confirmed 144 unique deterministic positions for each tested biome/shard combination.
- Cross-shard authority scan confirmed `msw_fob_same_world()` has no invasion callers; the helper remains only as inherited utility code.
- No nested `.zip` archive exists inside the candidate.
- No `source_assets/` directory exists inside the candidate.
- All **44 runtime image assets** are SHA-256 byte-identical to the accepted v0.4.1 baseline. v0.5.0 adds no image binary.

## Version and schema assertions

Validated directly against the candidate sources:

- `public_html/config/app.php` reports application version **0.5.0**.
- `public_html/includes/schema.php` defines `MSW_SCHEMA_REVISION = 7`.
- `database/install_schema.sql` records schema revision **7**.
- Runtime schema and fresh-install SQL both define `security_backup_slots`.
- `_setup.php` requires `security_backup_slots` and exposes `security_backup_integrity` in Confirm Installation.
- Security integrity checks reject invalid slot indexes, missing/cross-owner units, non-Security assignments and non-personnel classes.

## Catalog and gameplay invariant assertions

The authoritative catalog was executed directly and assertions passed for:

- standard Fulton = R&D 1;
- Fulton+ = R&D 4;
- Cargo Fulton = **R&D 5**;
- Wormhole Fulton = **R&D 8**;
- Combat Medkit = R&D 2 + Medical 2;
- Trauma Kit = R&D 5 + Medical 5;
- Nanomed Injector = R&D 8 + Medical 8;
- four Intel milestones and three Security milestones present.

Static integration checks also confirmed the candidate contains:

- `msw_manufacture_item()` transactional manufacturing path;
- `msw_use_battle_item()` medical combat path;
- `msw_security_backup_fighters()` Security escort projection;
- action FX classes used by the battle UI;
- `msw_fob_world_directory()` global shard directory authority;
- `fob_shards.php` linked from the deployed Earth globe.

## Transaction/persistence changes reviewed

- R&D material debit and inventory credit now settle in one `msw_manufacture_item()` database transaction. Existing bot material-based restocking uses the same atomic helper.
- Medical item consumption occurs inside the existing encounter row/version transaction.
- Security backup selection is persisted in schema revision 7 and runtime revalidates assignment/class/dispatch eligibility before use.
- Cross-shard staff strikes persist the target defender's `world_id` while the attacker's home membership remains untouched.
- Direct/staff FOB settlement retains row locking, exact resource debit/credit and defender protection.
- Autonomous human-target raids use the same raid ledger and protection rules, with a reduced autonomous direct-transfer rate/caps.

## Runtime acceptance boundary

A local MySQL/MariaDB service is not available inside the build container, so this gate does **not** claim database/browser acceptance. The package therefore remains correctly labeled **XAMPP Test Candidate**.

Before promoting the build, back up the accepted database, run `_setup.php` **Update / Repair** to schema 7, run **Confirm Installation**, and complete every release-blocking scenario in `docs/XAMPP_TEST_PLAN.md` using the actual XAMPP/MariaDB/browser environment.
