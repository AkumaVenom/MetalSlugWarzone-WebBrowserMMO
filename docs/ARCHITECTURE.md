# Architecture — Metal Slug Warzone v0.4.1

## Authority model

PHP/MySQL remains authoritative. Browsers submit intent and render returned state; they do not own coordinates, collision, staff movement, bot state, unit ownership, access relationships, battle state, resources or timers.

## Persistent autonomous commander model

Autonomous commanders are first-class game identities rather than browser-only NPC markers:

- `users.is_bot=1` identifies an autonomous commander while retaining the same operative, Commander XP, resources, Base Power, active-map and Mother Base fields used by humans.
- `bot_commanders` adds stable `bot_index`, behavior personality, current activity, counters and persisted `next_action_at` / lease timestamps.
- Production population is exactly 1,000 stable indexes. Update / Repair seeds only missing indexes and does not recreate existing bot identities.
- Bot authentication is impossible through the normal login path: `login.php` requires `is_bot=0` before password verification.
- Bots use the same `units`, `player_resources`, `inventory`, `base_sectors`, `fob_raids` and `pvp_matches` ledgers as human players instead of parallel fake inventories.

Fresh seeding round-robins the six warzones and spatially samples the entire legal 36-pixel seed lattice. The expected initial population is 167/167/167/167/166/166 with unique legal positions on each map.

## Bounded autonomous scheduling

This PHP/XAMPP architecture does not launch 1,000 background PHP workers. Autonomous activity is request-driven and bounded:

1. each bot persists `next_action_at`;
2. a gameplay/presence request selects only a small due batch;
3. the selected row receives a short database lease before simulation;
4. one action resolves and writes a new future `next_action_at`;
5. failures are logged and receive a longer backoff.

Warzone presence polling advances a local batch plus a smaller global batch. This keeps the viewed map responsive while also allowing bots in other warzones to progress. Multiple browsers may increase how many *due* bots are serviced, but cannot make one bot act before its own persisted schedule.

## Autonomous gameplay loop

A due bot can:

- move one accepted 18-pixel step through the exact server collision path;
- resolve a field contact, gain resources/Commander XP and attempt Fulton recovery;
- assign staff/recalculate Mother Base/R&D and restock recovery equipment by spending real state;
- start and later resolve a real persisted Combat Unit dispatch through `dispatch_missions`, using the same MySQL `finish_at`, success-chance, reward, unit-XP and unit-return contracts as the player system;
- perform an autonomous FOB raid against another bot;
- resolve an autonomous bot-v-bot PvP exercise.

Recovered contacts create normal persistent `units` rows. Roster capacity is bounded. Human-class recoveries consume Fulton equipment; vehicle recovery requires the same Cargo Fulton/R&D threshold used by players. Autonomous assignment prioritizes qualifying R&D staff until Cargo Fulton capability is legitimately reachable, then returns to aptitude-based assignment.

Because bot units are normal roster rows, the accepted physical Mother Base projection can place their personnel/hardware using the same garrison system when required.

## Warzone presence

`msw_presence()` merges mandatory human presence with enabled bot commanders on the current map. Bots do not expire through human `last_seen` TTL semantics; their persistent `active_map/map_x/map_y/facing` state represents their assigned live presence.

The browser polls every three seconds. Remote avatars are keyed by user ID and updated in place instead of being destroyed/recreated each poll, which is important when roughly 166–167 autonomous commanders share a map.

## PvP modes

`pvp_matches.match_mode` supports:

- `live` — existing human-v-human version-locked turns;
- `live_ai` — human-v-bot live turns with the bot turn committed server-side after a short response delay;
- `snapshot` — human-v-bot immutable commander snapshot battle with immediate server AI response;
- `ai_sim` — autonomous bot-v-bot resolved simulation stored in the same PvP ledger.

The selected Metal Slug operative and Commander stats are snapshotted at match creation. A bot never needs a browser session or synthetic authentication cookie to take its turn.

## FOB economy

Human-to-bot FOB infiltration uses the accepted transactionally locked snapshot/resource-transfer path. Bot defenders therefore expose actual Base Power, active Combat Unit, Security Team and resource stock.

Autonomous bot raids deliberately target other bots only. This keeps the autonomous economy active without silently draining offline human accounts. Bot-v-bot raids still use row locks, attacker cooldown, defender protection, immutable snapshots and exact debit/credit resource transfer.

## Physical-space separation

Two multiplayer physical-space systems continue to coexist:

- **Warzones:** authoritative position is stored on `users.active_map/map_x/map_y/facing/last_seen`.
- **Mother Bases:** authoritative visitor position is stored in `mother_base_presence` with `base_owner_user_id`, `base_key`, native-map coordinates, facing and heartbeat.

Entering a Mother Base clears human warzone presence. Deploying to a warzone deletes human Mother Base presence. Autonomous commanders do not receive interactive browser visitor sessions.

## Mother Base selection and garrison projection

`users.mother_base_key` selects one entry from `msw_mother_base_catalog()`. `units` remains the authoritative roster and `mother_base_unit_positions` remains a derived spatial layer keyed by `unit_id`.

Personnel use persistent local-anchor roaming with slow server timestamps/collision; vehicle/air hardware remains stationary. Friend/Strike Force visitation remains authorized through `msw_mb_access_relation()` and is revalidated on page entry, movement and presence polling.

## Schema revision 4

v0.3.0 adds non-destructively:

- `users.is_bot`
- `bot_commanders`
- `pvp_matches.match_mode`

It preserves all revision-3 Mother Base visitation tables/state. `_setup.php` Update / Repair additionally seeds/repairs the exact production autonomous population without deleting human progression.


## v0.3.1 warzone label presentation

Autonomous commander presence still uses the same keyed server payload and DOM entities as v0.3.0. Presentation is compact-only: the anchor stores the full identity in `data-full-label`/`aria-label`, renders `AI` through CSS at rest, and expands the full identity only when the AI tag is hovered/focused or its immediately adjacent bot operative sprite is hovered. No label state is authoritative gameplay state.


## v0.3.3 per-warzone autonomous operative assignment

`bot_index` remains the durable autonomous identity, but skin assignment is deliberately **decoupled from the round-robin warzone slot**. Fresh seeding uses the bot's local ordinal inside its assigned map plus a map offset to rotate through `msw_character_catalog()`. Update / Repair performs the same concept against the current persisted population: bots are ordered by `active_map` then `bot_index`, and each map gets its own independent six-skin rotation. This guarantees all six player operatives coexist inside every 166–167-bot warzone instead of producing one skin per map. Only `users.character_key` is reconciled; presence, profiles, snapshots and Live AI PvP automatically resolve the corrected sprite through the existing character catalog.


## v0.3.4 level-1 recovery manufacturing

`msw_fulton_catalog()` remains the authoritative capability/target-class table used by combat recovery, while `msw_rd_catalog()` remains the authoritative manufacturing table. v0.3.4 aligns those two layers for the baseline recovery item by exposing `fulton` as an **R&D 1** recipe. The item is still consumed by the normal server-side inventory path and the battle engine still revalidates R&D level and target class before recovery. Higher Fulton tiers remain independently gated at R&D 4/8/15. No schema state is added; existing R&D Team level is immediately sufficient to expose the basic recipe.


## v0.3.5 local WorldServer console

The administration console is deliberately separated from browser authority. `public_html/includes/server_console.php` emits a compact NDJSON activity record to the package-root `_server_console/events.ndjson`; normal deployments expose only `public_html`, so the feed is not part of the web document tree. `_server_console/.htaccess` denies HTTP access as an additional safeguard if a broader document root is ever misconfigured. There is no console PHP page, JSON API, SSE endpoint or WebSocket listener.

`msw_console_register_request_traffic()` runs after authentication/bootstrap and registers a shutdown observer only for a real logged-in human account. It captures identity before shutdown, then writes a `WEB` event only when the request completes below HTTP 400 and did not terminate through a fatal PHP error. Movement/presence/state-poll routes are rejected before any identity lookup or write.

Gameplay events use `msw_console_event_for_user()`. That helper independently revalidates `users.is_bot=0`, clips metadata to bounded scalar values, refuses suppressed routes and never persists raw request payloads. Explicit action hooks are placed only after successful authoritative state changes or committed gameplay actions. Technical exceptions continue through the existing browser/game flows and are not converted into console events.

The writer uses an application-level lock plus append locking. At 8 MiB the active feed rotates to `.1`, retaining `.1` through `.3`. Logging is fail-silent by design: filesystem/encoding/locking problems cannot abort or roll back gameplay transactions. `serverconsole.bat` launches `serverconsole.ps1`, which reads the file locally with read/write sharing, detects rotation, renders category colors and never reads Apache/PHP/MySQL error logs.


## v0.4.x sharded global FOB authority

The primary FOB surface is now a persistent spatial layer rather than a global query of every commander. `fob_worlds` identifies a biome plus sequential shard index, while `fob_world_memberships` gives each user exactly one world, one coherent skin and one authoritative slot. The browser renders these rows but cannot choose coordinates or world IDs.

A shard has 144 authoritative slot identities. Ownership is protected by a unique `(world_id,slot_index)` key. In v0.4.1, `msw_fob_slot_position()` maps each slot through a biome/shard-specific deterministic permutation of 144 irregular collision-validated native-map anchors instead of the former visible 12×12 grid. Human deployment uses a per-biome MySQL advisory lock while it chooses/creates the first non-full shard, preventing a count/select race under simultaneous account deployments. When no shard has capacity, the next `shard_index` is inserted automatically.

FOB skin keys intentionally reuse `users.mother_base_key`. This makes one selection authoritative for both the global overview icon and the physical Mother Base map. After a membership exists, profile-based Mother Base redeployment is disabled. Existing v0.3.5 human progression is not auto-moved; those commanders make the new selection once. Autonomous commanders are reconciled idempotently during schema repair.

Immediate raids use `msw_fob_resolve_direct_raid()`. The old attacker cooldown is no longer a precondition. Same-shard membership, target post-invasion protection, user/resource row locks, Base Power/Security/Combat Unit snapshots, and exact debit/credit transfer remain server-authoritative. Every completed attempt sets defender protection.

`fob_strike_dispatches` is separate from `dispatch_missions`, but both reserve staff through `units.dispatched_until`. This keeps the existing standard Dispatch system unchanged while providing an FOB-specific persistent mission ledger. Staff invasions snapshot both sides at launch, then settle on/after `finish_at`; resolution clears the same reserved units exactly once.

Autonomous commanders occupy the same membership table and icon catalog. Their direct and staff-dispatch raids are constrained to bot targets in the same FOB shard, so the background economy evolves without silently attacking offline human accounts.


### Cross-ledger staff reservation authority

Standard Combat Unit Dispatch and FOB staff invasions intentionally share `units.dispatched_until`. `includes/dispatch_authority.php` centralizes standard mission completion, while FOB resolution uses the same reservation-release rule: due missions are settled before either surface offers staff again, selected IDs are normalized/locked deterministically, and an older completion clears `dispatched_until` only when that reservation has not been replaced by a newer mission. This preserves mutual exclusion across simultaneous tabs and expiry-boundary requests without merging the two mission ledgers.
