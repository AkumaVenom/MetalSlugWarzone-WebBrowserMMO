# Security and Exploit-Resistance Contract — v0.3.4

This candidate treats all browser state as untrusted and does not give autonomous commanders login sessions.

## Existing controls preserved

- Prepared mysqli statements for user-controlled database values.
- Password hashing with `password_hash()` / `password_verify()` for human accounts.
- CSRF protection and same-origin POST validation.
- Authentication session regeneration, HttpOnly/SameSite cookies and automatic Secure cookies on HTTPS.
- Login throttling, CSP, frame denial, content-type hardening and restricted permissions.
- Server ownership checks for units, battles, PvP, raids, friends/messages and progression.
- Versioned/locked battle state and transactionally locked resource transfers.
- Movement rate limiting and full-path server collision.
- Loopback-only `_setup.php`.

## Autonomous commander security contracts

- Bots are explicitly persisted as `users.is_bot=1`; normal login requires `is_bot=0` before password verification.
- Bot passwords are not usable credentials and bots never receive authenticated browser sessions.
- Client requests cannot submit bot coordinates, activity counters, next-action timestamps, resources, recovered units, FOB result or AI move selection as authority.
- Autonomous action scheduling is selected server-side from due rows and protected by short database leases. Concurrent presence requests cannot claim the same bot simultaneously.
- `next_action_at` is written by the server after every action/backoff, preventing repeated browser polling from directly accelerating one bot.
- Bot movement uses the same authoritative map bounds/collision/path sampling as human movement.
- Bot recovery validates actual roster capacity, inventory, R&D level and equipment consumption before creating a persistent unit.
- Bot FOB attacks use transactions/row locks and exact debit-credit resource settlement. Bots autonomously target bots only; offline human stocks are not silently farmed by the simulation.
- Live AI / Snapshot PvP accepts only a valid human participant and a real bot identity. Bot moves are selected/committed server-side against the current match version.
- Bot identities are excluded from friend requests and direct-message recipient selection because they cannot consent/respond through a player session.

## Population/repair safety

- Stable `bot_index` values 1–1000 make seeding idempotent.
- `bot_index` is authoritative rather than username text. If a pre-v0.3.0 human account already owns a default AI-style username, seeding preserves the human row and allocates a deterministic alternate bot username instead of overwriting/renaming the player or rolling back the population migration.
- Update / Repair creates only missing indexes and re-enables the production range; it does not recreate existing bot progression.
- Any historical bot indexes above the configured production population are disabled rather than destructively deleted.
- `_setup.php` Confirm Installation verifies exact enabled/distinct population and balanced six-warzone distribution.

## Mother Base security contracts preserved

- A Mother Base owner ID supplied by the browser is never trusted as authorization.
- Owner/friend/Strike Force access is re-read from the database on page entry, movement and presence polling.
- Movement coordinates are read from server persistence; clients submit only intent.
- Staff coordinates/roaming timers are server-owned and row-locked.
- Vehicles are classified server-side and cannot be made mobile by browser state.

## Internet-facing deployment

Use HTTPS, a least-privilege DB account, hardened Apache/PHP configuration, centralized rate limiting, monitoring/audit logs, backups and an appropriate reverse proxy/WAF. The default local XAMPP configuration is intended for development/testing, not direct public exposure.


## v0.3.1 presentation-only note

Compact AI labels do not reduce server identity or authorization checks. Full bot IDs/callsigns remain sourced from authenticated presence responses, profile URLs remain server-generated, and label hover metadata is presentation-only. Bot login isolation and all v0.3.0 authority boundaries are unchanged.


## v0.3.3 per-warzone variety migration safety

The skin reconciliation operates only on `users` rows joined to production `bot_commanders` indexes 1–1000 and updates only `users.character_key`. Human accounts are never selected. Existing bot identity/progression/resource/roster/position/FOB/PvP data is not recreated. The assignment is deterministic for the current persisted warzone grouping and is executed inside the same population transaction, so a failed Update / Repair cannot commit only part of the skin reconciliation.


## v0.3.4 R&D manufacturing authority

Making the basic Fulton recipe available at R&D 1 does not move authority to the browser. `rd.php` continues to resolve the submitted recipe against `msw_rd_catalog()`, verify the persisted R&D sector level, debit persistent resources through the locked resource ledger, and only then add inventory. Battle recovery independently validates the selected item against `msw_fulton_catalog()`, R&D level, target class and inventory consumption. Higher-tier unlock thresholds are unchanged.
