# Security and Exploit-Resistance Contract — v0.6.0

The development target is local XAMPP, but gameplay state is still designed around server authority, validation and transactional persistence.

## Existing controls preserved

- Authenticated sessions and CSRF validation protect state-changing human POST actions.
- Password authentication remains isolated from autonomous commander accounts (`is_bot=1`).
- Resources, inventory, roster ownership, movement, collisions, combat outcomes, progression, dispatch timers and FOB settlement are computed/read server-side.
- MySQL row locking and transactional ledgers are used where concurrent requests could duplicate rewards, consume inventory twice or transfer the same resources inconsistently.
- User-supplied IDs are re-resolved against ownership/authorization rules instead of trusting page form contents.
- The local `_setup.php` remains loopback-oriented; Fresh Install is destructive and must never be used as an upgrade shortcut.

## v0.6.0 FOB Command Centre, retaliation and protection authority

The Command Centre is an orchestration surface only. Every state-changing action still enters existing PHP authority through CSRF-protected POSTs and re-resolves IDs from persistent state.

Retaliation has a database-backed one-use boundary:

- the submitted source raid must exist;
- its original `defender_user_id` must equal the current retaliating commander;
- its original `attacker_user_id` must equal the requested retaliation target;
- that target must still be a valid global FOB target in its real membership/world;
- the target's protection is rechecked under the normal locked raid transaction;
- `retaliation_for_raid_id` is unique, so duplicate/replayed concurrent requests cannot create two committed retaliations for one incident.

Protection removal is also server-owned. JavaScript provides only a warning. `msw_fob_break_protection_for_offense_locked()` runs inside the same transaction as a valid direct/retaliation settlement or staff-strike launch. A request rejected for stale target context, defender protection, invalid retaliation source or unavailable staff never commits the protection removal.

The rule intentionally applies to autonomous commanders as well because bots call the same direct/staff authority. A protected bot may choose to attack, but doing so gives up its own remaining recovery shield.

## v0.5.0 medical item authority

The browser submits only an item key. `msw_use_battle_item()` resolves the key against `msw_battle_item_catalog()`, rechecks persisted R&D/Medical levels, checks current HP, and calls the atomic inventory-consumption path. Full HP is rejected before consumption. Healing amount and Support Team multiplier are calculated by PHP and the action advances through the same encounter version/turn transaction as attacks.

Manufacturing is independently validated against `msw_rd_catalog()`: required sector levels and resource costs are checked from persistent state before inventory is credited.

## v0.5.0 Security backup authority

`security_backup_slots` stores only server-validated owned `unit_id` values. Setting a slot requires:

- slot 1 or 2 within current Security capacity;
- an owned unit;
- current assignment to Security;
- personnel class (`infantry` / `heavy_infantry`);
- no active dispatch reservation.

The database prevents the same unit occupying both slots. Battle startup/synchronization rechecks assignment/class/dispatch status, so stale or manually altered browser controls cannot force an ineligible unit into combat. Reassignment away from Security clears the selected slot.

Backup attack scaling, accuracy and damage ceilings are calculated on the server. A browser cannot submit backup damage or a successful assist result.

## Intel and Support authority

Intel information is exposed only when persisted Intel level reaches the required milestone. The recommended move and Fulton probability are calculated from authoritative combat state. Intel 8 enemy-accuracy modification and Support medical multipliers are applied inside the battle engine, not accepted from client values.

## Global FOB target authority

Cross-shard invasion deliberately widens target eligibility, but it does not weaken membership validation.

- The attacker must possess a valid `fob_world_memberships` row.
- The target must possess a valid membership and cannot equal the attacker.
- When a page/form includes `world_id`, the target's actual membership must match it.
- Remote shard browsing never updates the attacker's membership.
- Defender protection is checked again inside the locked raid/dispatch transaction.
- Direct raids and staff dispatch launches require CSRF validation on human POST surfaces.
- Resource transfer remains an atomic debit/credit operation under locked resource rows.

The old same-world restriction was an intentional gameplay rule, not the core authorization boundary. v0.5.0 replaces it with authenticated global membership + target-world consistency validation.

## Autonomous attacks on humans

Autonomous commanders may now target human defenders because that is an explicit v0.5.0 gameplay feature. They still use the same protected-state and resource ledgers. Autonomous direct raids use a lower 3% transfer rate and lower caps than human immediate raids to reduce unattended economic impact. Defender protection applies after every completed attempt, including a bot loss.

Bots still cannot authenticate as humans, issue social consent actions or write human-only server-console identity events. Human defenders may receive a local `FOB · DEFENSE` event generated after settlement; no bot session is synthesized.

## Staff reservation race guard

FOB strikes and standard Dispatch missions share `units.dispatched_until`. Both launch paths settle due work before reuse, selected units are re-read under locks, and completion clears a reservation only when the stored timestamp is not newer than the finishing mission. A stale completion therefore cannot erase a later reservation from either ledger.

## Population and migration safety

The 1,000 production bot indexes remain stable. Update / Repair creates missing schema objects/identities and reconciles supported metadata without deleting human progression. v0.5.0 revision 7 added `security_backup_slots`; v0.6.0 revision 8 adds only nullable `fob_raids.retaliation_for_raid_id` plus its unique index. Existing FOB world identity/coordinates and prior gameplay ledgers remain intact.

`_setup.php` Confirm Installation checks the expected schema revision, `security_backup_integrity`, `fob_retaliation_integrity` and the inherited autonomous population, FOB membership, duplicate-slot and irregular-spatial checks.

## Mother Base / physical-space security

Owner/friend/Strike Force Mother Base access continues to be re-read from persistent relationships. Movement coordinates are server-owned and clients submit movement intent. Staff roaming and collision remain authoritative. Global FOB browsing does not grant physical Mother Base visitation privileges.

## Local WorldServer console

The console remains local-filesystem-only and outside `public_html`. It does not store passwords, cookies, sessions, CSRF tokens, raw POST payloads or direct-message bodies; movement/presence polling is suppressed. Logging failure cannot abort gameplay.

## Internet-facing deployment

For public hosting, use HTTPS, least-privilege database credentials, hardened Apache/PHP settings, centralized rate limiting, secure backup/restore procedures, monitoring/audit controls and an appropriate reverse proxy/WAF. Default XAMPP is a development/test stack, not a hardened production edge configuration.


## Presentation-layer trust boundary

The v0.6.0 supplied-art palette, scan sweep, hover/reveal choreography and selected-operative hero sprite are non-authoritative presentation. The browser may add/remove CSS classes or decorative DOM nodes, but the server never trusts those classes/nodes for resource values, protection state, invasion eligibility, combat resolution, staff reservation or retaliation authorization. Resource and statistic colour classes are derived from server-rendered labels only and do not carry gameplay meaning back to PHP.
