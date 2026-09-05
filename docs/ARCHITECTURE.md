# Architecture — Metal Slug Warzone v0.6.0

## Authority model

PHP/MySQL remains authoritative. The browser submits intent and renders returned state; it does not own movement coordinates, collision, staff movement, unit ownership, sector levels, inventory, battle state, FOB membership, target authority, bot state, resources or timers. v0.6.0 continues that rule: the new FOB Command Centre composes existing ledgers and adds only a one-use retaliation link; it does not create client-owned shortcuts.

## Persistent autonomous commander model

Autonomous commanders remain first-class game identities:

- `users.is_bot=1` identifies an autonomous commander while retaining the same operative, Commander XP, resources, Base Power, active-map and Mother Base fields used by humans.
- `bot_commanders` provides the stable `bot_index`, activity/counter state and persisted `next_action_at` / lease timestamps.
- Production population remains exactly 1,000 stable indexes. Update / Repair seeds only missing production identities and never recreates valid persistent bot progression.
- Bot authentication remains blocked through the normal login path.
- Bots use the normal `units`, `player_resources`, `inventory`, `base_sectors`, `dispatch_missions`, `fob_strike_dispatches`, `fob_raids` and `pvp_matches` ledgers instead of parallel fake state.

Fresh seeding remains balanced across all six warzones and uses the accepted legal collision lattice. Per-warzone operative variety remains deterministic and all six accepted player skins are represented within each large autonomous population.

## Bounded autonomous scheduling and v0.5 pacing

The XAMPP/PHP runtime does not create 1,000 background workers. Simulation remains request-driven, persisted and bounded:

1. each bot has a persisted `next_action_at`;
2. a gameplay/presence/network request selects a bounded due batch;
3. selected bots receive short database leases;
4. one autonomous action is committed per lease;
5. the next action time is persisted before later pulses can service that bot again.

v0.5.0 tightens normal action spacing from 12–32 seconds to 10–26 seconds and applies a bounded 1.25× pulse-budget multiplier, with a hard pulse cap of 45. Field-combat XP/resources are modestly increased. These changes make the shared rankings evolve faster without allowing extra browser tabs to bypass a commander's own persisted schedule.

Autonomous action weighting now gives FOB aggression roughly 15% of action decisions (staff invasion plus immediate raid combined). Target selection can prefer local competition or search globally; when eligible targets exist, a configured 28% bias can prefer human defenders. Defender protection is always respected.

## Mother Base sector systems

`base_sectors` remains the authoritative level source. `msw_sector_unlock_catalog()` is the canonical description of runtime milestones and the Mother Base Capability Matrix renders that same catalog.

### R&D

- Lv1: standard Fulton manufacturing/recovery.
- Lv4: Fulton+.
- Lv5: Cargo Fulton, including ground-vehicle recovery.
- Lv8: Wormhole Fulton, including aircraft recovery.

`msw_fulton_catalog()` controls battle capability and `msw_rd_catalog()` controls manufacturing. Both are checked server-side.

### Medical

Medical consumables use multi-sector recipe requirements and are real persistent inventory items:

- Combat Medkit: R&D 2 + Medical 2; base 35 HP.
- Trauma Kit: R&D 5 + Medical 5; base 80 HP.
- Nanomed Injector: R&D 8 + Medical 8; base 160 HP.

`msw_use_battle_item()` revalidates requirements, refuses a full-HP use before consumption, atomically consumes one inventory unit, applies healing, advances the player's action, then permits normal Security backup/enemy response flow.

### Intel

Intel is not display-only:

- Lv2 reveals enemy ATK/DEF/SPD.
- Lv4 reveals type effectiveness and a server-calculated recommended move.
- Lv6 reveals the exact current Fulton success forecast before use.
- Lv8 reduces the normal PvE enemy counterattack accuracy by 6 percentage points.

### Security backup party

Schema revision 7 adds `security_backup_slots`, a two-slot persistent selection layer over existing owned `units` rows. A valid escort must:

- belong to the player;
- be assigned to `security`;
- be `infantry` or `heavy_infantry`;
- not be actively dispatched.

The Staff page manages the slots. Reassigning a selected unit away from Security clears its backup selection. At battle synchronization, only currently valid rows are projected into the fight.

Backup output is deliberately constrained. The derived backup attack is substantially reduced from the unit's normal stats, assist accuracy starts around 60%, and each hit is capped to a small percentage of enemy maximum HP (with a lower boss ceiling). Security Lv4 adds 5 percentage points of assist accuracy; Security Lv7 raises the non-boss controlled-damage ceiling slightly. The primary commander remains the dominant damage source.

### Support

Support Lv3 raises medical-item healing to 115% of base and Support Lv6 to 125% total. This multiplier is applied in the authoritative battle engine, not calculated by the browser.

## Unified PvE battle flow and animation contract

Field contacts, normal missions, sidequests, rival commander fights and bosses all use `battle.php` and the shared encounter state in `battle_engine.php`. v0.5.0 adds a small `fx` snapshot to each committed action so rendering can describe the action that actually occurred without granting the client combat authority.

The PvE sequence is:

1. the player attacks, heals or attempts Fulton recovery;
2. if the enemy is still active, selected Security escorts may provide controlled covering fire;
3. if the encounter remains active, the enemy executes its counterattack;
4. committed state/version is persisted using the existing encounter concurrency contract.

The UI maps committed FX to CSS choreography: commander lunge, enemy impact, enemy counter-lunge, commander impact, Security covering fire, medical pulse and Fulton extraction feedback. Initial contact also animates. `prefers-reduced-motion` disables the nonessential motion.

## PvP modes and choreography

`pvp_matches.match_mode` continues to support `live`, `live_ai`, `snapshot` and `ai_sim`. PvP state now also stores the last committed action FX so the match screen can animate the attacking and impacted sides. Combat settlement remains server-side/version-locked; the animation is presentation of persisted turn state, not a client simulation.

Security escorts and PvE medical consumables are intentionally not injected into competitive PvP balance.

## FOB Command Centre orchestration

`fob.php` is the primary deployed-FOB strategic surface. It reads global target candidates, available staff, pending outbound/inbound strike rows, incoming raid incidents and recent outgoing reports, then submits only normal direct-raid or staff-dispatch intent back to PHP authority.

Parallel staff invasion support does not require a new queue table: each committed strike already owns a durable `fob_strike_dispatches` row and selected staff are reserved through `units.dispatched_until`. Repeated Command Centre launches simply create additional valid strike rows using different currently available staff.

Retaliation adds a nullable self-ledger reference, `fob_raids.retaliation_for_raid_id`. It identifies the exact incoming incident that authorized a retaliation and is protected by a unique index. The attacker/defender relationship is revalidated before resolution, and the retaliation still goes through `msw_fob_resolve_direct_raid()` with the normal target-protection/resource-locking path.

Protection is now explicitly passive recovery state. Direct raids, retaliation and staff-strike launch call a shared locked helper that clears the attacker's active protection only when the offense is being successfully committed.

## Global FOB topology

Each commander still has one permanent `fob_world_memberships` row: biome, world/shard, FOB skin, slot and deterministic x/y are home identity. The v0.4.1 irregular 144-anchor layout remains unchanged.

v0.5.0 separates **home identity** from **invasion browsing**. A deployed commander can use the same Earth globe to:

1. choose one of the five biome/continent theatres;
2. list populated `fob_worlds` for that biome;
3. open a selected remote or home shard;
4. inspect and attack valid occupants.

Viewing or attacking a remote shard never rewrites the attacker's membership, Mother Base skin or slot.

`msw_fob_target_row()` now authorizes a target globally: the attacker must have a valid membership, the target must have a valid membership, the IDs must differ, and an optional selected `world_id` must match the target. The former same-world requirement is intentionally removed from invasion targeting only.

## FOB economy and protection

Immediate human raids keep the accepted transactionally locked resource transfer and defender post-invasion protection. There remains no attacker-side cooldown.

Autonomous commanders may now invade human or AI defenders across shards. Because offline humans can be pressured, autonomous direct raids use a deliberately smaller 3% transfer rate with lower caps than a human immediate invasion. Defender protection still applies after every completed invasion attempt, win or loss, and therefore remains the principal anti-drain boundary.

Human defenders receive a local server-console `FOB · DEFENSE` event after resolved direct or staff attacks. Incoming reports remain visible through the normal shared `fob_raids` ledger.

## Cross-shard staff invasion dispatch

FOB staff strikes remain separate from standard Combat Unit Dispatch while sharing `units.dispatched_until` as the authoritative reservation field. Launch performs ownership/availability revalidation under row locks and persists the **target defender's world ID** in `fob_strike_dispatches`.

Completion remains MySQL-timestamp-driven and exactly-once. A protected defender causes `protected_abort`, returns reserved staff and transfers no resources. A resolved strike writes the normal `fob_raids` after-action report. The existing conditional reservation release protects against a stale mission completion clearing a newer reservation.

## Physical-space separation

Warzone position remains on `users.active_map/map_x/map_y/facing/last_seen`. Mother Base visitor position remains in `mother_base_presence`. Global FOB shard browsing is a strategic UI and does not relocate either physical-space presence system.

FOB deployment continues to bind `users.mother_base_key` to the chosen compatible FOB skin. Existing friend/Strike Force Mother Base visitation and server-authoritative collision remain unchanged.

## Schema revision 8

The current schema revision is **8**. v0.5.0 revision 7 added:

- `security_backup_slots(user_id, slot_index, unit_id, created_at, updated_at)`;
- primary key `(user_id,slot_index)`;
- unique selected unit per user;
- cascading FKs to `users` and `units`.

v0.6.0 then adds only nullable `fob_raids.retaliation_for_raid_id` plus unique index `uq_fob_retaliation_source`. All previous FOB world/spatial, Security backup and gameplay structures remain. `_setup.php` Update / Repair is additive/idempotent and Confirm Installation now includes both `security_backup_integrity` and `fob_retaliation_integrity` in addition to the existing autonomous population and FOB topology checks.

## Local WorldServer console

The v0.3.5 local-filesystem console architecture remains unchanged. It is not a network admin endpoint, ignores bot-originated console identity, suppresses movement/polling noise and fails silently if logging is unavailable. v0.5.0 adds human-visible FOB defense events only after authoritative raid settlement.


## v0.6.0 supplied-art presentation layer

The Command Network visual system remains strictly downstream of gameplay authority. `includes/ui.php` assigns a page identity class and emits semantic resource/stat classes; `assets/css/msw.css` maps those identities to the 23 supplied artwork files and owns palette, scan-line, hover, responsive and reduced-motion behavior. `assets/js/msw.js` may mirror the already-rendered selected-operative sprite into a hero and add intersection-based reveal classes, but it does not calculate rewards, eligibility, combat outcomes, invasion validity, protection state or persistence.

No visual effect creates an alternate gameplay route. Forms, CSRF validation, MySQL row locks, dispatch reservations, raid settlement and exactly-once logic continue through the same PHP authority functions documented above. Disabling JavaScript or CSS therefore changes presentation only, not the command network's authoritative state transitions.
