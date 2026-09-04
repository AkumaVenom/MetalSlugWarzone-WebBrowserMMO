# Global FOB World System — v0.4.1

## Runtime flow

1. A commander without `fob_world_memberships` enters `fob_globe.php`.
2. The server accepts only a key from `msw_fob_biome_catalog()`.
3. `fob_skin.php` exposes only that biome's compatible `mother_base_key` values.
4. `msw_fob_assign_user()` selects the first non-full shard or creates the next shard.
5. The server owns `world_id`, `shard_index`, `slot_index`, `x` and `y`; none are client-selected.
6. `users.mother_base_key` is synchronized to the selected FOB skin and the physical Mother Base layout is safely rebuilt for that skin.
7. `fob_world.php` renders the member rows for that one shard at native overview-map resolution.

## Globe hotspot contract

The five deployment controls use normalized coordinates from `msw_fob_globe_hotspots()` against the square 1254×1254 source image. They intentionally target the actual art regions rather than semantic guesses: Arctic = polar ice, Continental = gold Americas, Forest = green Eurasia, Desert = orange Africa, Sea = open ocean. Because both the stage and source are square, percentage coordinates remain aligned at responsive sizes.

## Biome / skin contract

| Biome | Overview map | Eligible physical/overview skin |
| --- | --- | --- |
| Continental | Dirt_FOB_Overview_Map.png | Continental Mother Base (`land_dirt`) |
| Forest | Forest_FOB_Overview_Map.png | Forest Mother Base (`land_forest`) |
| Desert | Desert_FOB_Overview_Map.png | Desert Mother Base (`land_desert`) |
| Arctic | Arctic_FOB_Overview_Map.png | Arctic Mother Base (`land_snow`) |
| Sea | Sea_FOB_Overview_Map.png | Offshore Alpha / Offshore Bravo / Maritime Fortress (`sea_fob1/2/3`) |

The same key drives the overview marker and physical Mother Base. The player cannot independently redeploy the physical base after global FOB placement.

## Shard topology and capacity

`fob_worlds` is intentionally sparse and demand-created. There is no predeclared maximum number of overview instances per biome in game logic. A shard stores a sequential integer index and capacity. The current production capacity is 144.

A shard still owns 144 authoritative `slot_index` identities, but v0.4.1 no longer projects those identities onto a visible grid. `msw_fob_slot_anchors()` defines 144 irregular native-map centers, validated against a 136×96 rectangular clearance envelope while the desktop marker footprint is 128×86. `msw_fob_slot_position()` then deterministically permutes that constellation by biome + shard, so partial populations are dispersed differently across shards while remaining stable forever for a given `(biome, shard, slot)`.

The stored x/y value is the center of the FOB marker. The unique key on `(world_id, slot_index)` remains the database-level exclusivity authority; irregular x/y projection is presentation/persistence geometry derived from that exclusive slot identity.

Human placement obtains `GET_LOCK('msw_fob_place_<biome>', 8)`, rechecks membership, locks existing world rows, chooses/creates capacity and inserts the membership. The unique key remains the final protection if an unexpected concurrent path bypasses the advisory lock.

## Persistence / migration

- `fob_world_memberships.user_id` is the primary key: one commander can own exactly one global FOB placement.
- Human v0.3.5 accounts are not silently assigned to a biome. Their old progression remains untouched until the owner completes the new two-step deployment.
- New human accounts are sent directly to the globe after signup.
- All 1,000 enabled autonomous commanders are assigned during Update / Repair. `bot_index` determines a balanced five-biome distribution, while Sea bots rotate all three maritime skins.
- Re-running Update / Repair leaves every bot in the same world/shard/slot and repairs missing membership. v0.4.1 additionally reprojects every existing membership's stored x/y from that same slot identity onto the irregular anchor constellation; no FOB is moved to another shard or assigned another slot.

## Immediate invasion

`msw_fob_resolve_direct_raid()` is the shared authority for human immediate raids and autonomous bot raids.

Required conditions:

- attacker and defender are different users;
- both have memberships in the same `world_id`;
- the defender is not currently under `fob_protection_until`.

There is no attacker cooldown check. `last_fob_attack_at` remains only as backward-compatible historical state.

Resolution locks both user rows, both resource ledgers, the attacking active Combat Unit and defender Security/Combat Unit rows. A win transfers bounded percentages through exact guarded debits and credits. Every completed attempt, including a defender win, assigns post-invasion protection to the defender.

Autonomous immediate raids preserve the accepted safety contract: AI aggressors select only other AI FOBs in their own shard. Humans can still attack AI FOBs normally.

## Staff invasion dispatch

`fob_strike_dispatches` is purpose-built for FOB assaults and does not alter the normal `dispatch_missions` table.

Launch rules:

- target must share the attacker's current shard;
- target must be open at launch;
- select 2–4 distinct staff owned by the attacker;
- every selected unit must have `dispatched_until IS NULL` or already expired;
- selected units are locked and assigned `dispatched_until = finish_at`.

The mission stores attacker/defender snapshots, unit IDs, a snapshot power and success chance. Because the selected rows use the existing reservation field, standard Dispatch and FOB invasion dispatch cannot double-book the same staff.

At/after `finish_at`, resolution is single-write under a locked `result='pending'` row. If the target became protected before arrival, the strike becomes `protected_abort`, returns the staff and transfers nothing. Otherwise it resolves attacker/defender win, writes a normal `fob_raids` report, applies protection, grants staff/Commander XP and clears reservation state.

`includes/dispatch_authority.php` centralizes completion of the existing standard Combat Unit Dispatch ledger. Both mission systems settle due work before re-offering staff, selected IDs are normalized/locked deterministically, and completion only clears `units.dispatched_until` when the stored reservation is not newer than the mission being completed. This prevents an expiry-boundary race from double-booking a staff member or allowing an older mission to erase a newer reservation.

## Bot simulation

Bots now have three FOB-related activities:

- remain persistently visible/invadable as overview-world members;
- perform immediate bot-v-bot raids against open targets in the same shard;
- launch and resolve timed 2-staff invasion dispatches using normal unit reservation.

The normal bounded/leased bot pulse remains unchanged. There is no browser session or synthetic login for autonomous actions.

## Regression contracts

The v0.4.1 FOB implementation must not change:

- standard Combat Unit `dispatch_missions` catalog, reward semantics and page presentation;
- existing `fob_raids` after-action history compatibility;
- Mother Base roster ownership, staff projection or friend/Strike Force visitation;
- warzone presence/collision;
- bot durable identity, warzone assignment and human-login prohibition;
- local WorldServer console isolation / bot-event exclusion.
