# Global FOB World System — v0.6.0

## Integrated invasion command layer

For a deployed commander, `fob.php` is now the strategic **Invasion Command Centre** rather than a redirect. It composes existing FOB authority into one coordinated surface:

- global priority target matrix;
- 2–4 member staff strike planner for repeated concurrent launches;
- active outbound staff operations;
- detected inbound staff operations;
- one-use retaliation orders derived from incoming raid incidents;
- recent outgoing After Action Reports;
- live home recovery-shield state and the offensive protection doctrine.

This is presentation/orchestration over existing server-owned ledgers. It does not introduce a client-side combat queue or alternate resource state. The Earth globe and shard screens remain the geographic reconnaissance layer below the command hub.

## Retaliation incident binding

An incoming `fob_raids` row where the current commander was the defender is a retaliation source incident. A retaliatory direct raid writes that source ID into nullable `fob_raids.retaliation_for_raid_id`. `UNIQUE uq_fob_retaliation_source(retaliation_for_raid_id)` ensures one source incident can be consumed by at most one retaliation.

The server revalidates that the source incident's original defender is the current retaliator and its original attacker is the retaliation target. Normal target membership/world validation and target protection still apply. A protected target therefore cannot be bypassed merely because a retaliation authorization exists.

## Home deployment contract

A commander has one permanent home FOB identity in `fob_world_memberships`. Initial deployment remains server-authoritative:

1. choose a biome on the supplied Earth globe;
2. choose a compatible Mother Base/FOB skin;
3. PHP selects/creates an available shard;
4. PHP selects a free authoritative slot under the placement lock;
5. deterministic irregular x/y is derived from the slot, biome and shard;
6. membership and coherent `mother_base_key` are persisted.

The browser never chooses a shard number, slot index or placement coordinates during deployment.

## Globe dual-purpose flow

For a commander without a membership, `fob_globe.php` is the one-time deployment surface.

For a deployed commander, the same globe becomes the **Global Invasion Network**. Selecting Continental, Forest, Desert, Arctic or Sea opens `fob_shards.php`, which lists every populated shard for that biome with population, human/AI counts, open-target count and home-shard status. Opening a shard passes its `world_id` into `fob_world.php`.

Remote browsing is non-destructive: selecting another shard never changes the commander's persisted home `world_id`, `skin_key`, `slot_index`, x/y or physical Mother Base.

## Biome and skin contract

The five supported biomes remain:

| Biome | Physical skin choices |
| --- | --- |
| Continental | Continental Mother Base |
| Forest | Forest Mother Base |
| Desert | Desert Mother Base |
| Arctic | Arctic Mother Base |
| Sea | Offshore Alpha, Offshore Bravo, Maritime Fortress |

The globe artwork and hotspot coordinates are presentation only. PHP validates biome and skin compatibility before persistence.

## Shard topology and spatial placement

Every `fob_worlds` row has a biome, monotonically allocated shard index and capacity of 144. `UNIQUE(world_id,slot_index)` is the final ownership collision guard.

v0.4.1's 144 irregular collision-safe anchor set remains authoritative. `msw_fob_slot_position()` deterministically permutes the anchor set by biome/shard so partial worlds do not show identical patterns. Update / Repair preserves membership identity and can reconcile stored x/y from the authoritative slot mapping.

## Global target authority

v0.5.0 intentionally removed the old same-shard invasion restriction; v0.6.0 preserves that global target authority.

A valid invasion target must satisfy all of the following:

- attacker is a different valid commander;
- attacker owns a valid FOB membership;
- defender owns a valid FOB membership;
- if the request carries a selected `world_id`, that world must be the defender's actual world;
- defender protection must be expired when the raid/dispatch is committed.

The selected `world_id` is context/validation, not relocation authority. Editing a URL cannot move a user between shards or convert a commander in one world into a target in another.

`msw_fob_targets_in_world()` scopes the rendered target list/markers to the selected shard. `msw_fob_target_row()` performs global target authorization and optional world matching.

## Immediate invasion

`msw_fob_resolve_direct_raid()` revalidates the global target and then locks attacker/defender rows and resource ledgers. Attacker and defender power are snapshotted, the result is resolved server-side, and any successful transfer is an exact debit/credit operation.

There is intentionally no attacker cooldown. Defender protection remains the anti-drain rule: every completed invasion attempt, including a failed attack, protects the defender temporarily. v0.6.0 also prevents protection from becoming an offensive staging shield: if the attacker is currently protected, a successfully committed direct invasion or retaliation atomically clears that attacker protection before settlement. A rejected/blocked attempt leaves it unchanged.

Human direct invasion retains its established 8% transfer rate/caps. Autonomous direct invasion uses 3% with lower caps because autonomous commanders may now attack offline human bases.

## Staff invasion dispatch

A human or autonomous commander can launch a 2–4 unit staff invasion against a valid target in any shard.

Launch authority:

- resolves due standard dispatches first;
- validates global target/protection;
- normalizes selected unit IDs;
- locks attacker/defender and selected units;
- verifies ownership and current `dispatched_until` availability;
- snapshots attacker/defender state;
- atomically drops the attacker's active protection if this valid launch is being committed;
- stores the **defender's world ID** in `fob_strike_dispatches`;
- reserves every selected unit until the persisted `finish_at` timestamp.

Resolution authority:

- claims only pending, due rows;
- locks involved commanders/resources;
- checks defender protection again at arrival;
- resolves once and writes a normal `fob_raids` report, or records `protected_abort` with zero transfer;
- awards configured unit/Commander XP;
- conditionally clears only the reservation belonging to that finished mission.

Browser/server restarts do not cancel the operation because time and status are database-backed.

## Autonomous invasion behavior

Autonomous commanders now compete globally rather than only with bots in their home shard.

Target selection:

- can prefer the attacker's local shard or consider the whole populated network;
- accepts human and AI defenders;
- respects defender protection;
- uses a configurable 28% human-target preference when eligible human targets exist;
- never selects the attacker itself.

Autonomous action weighting allocates about 9% to FOB staff strikes and 6% to immediate autonomous raids. Together with 10–26 second persisted action spacing and the bounded pulse multiplier, this makes invasion activity substantially more visible while keeping simulation bounded.

A human defender is notified through the local server-console `FOB · DEFENSE` channel after a resolved AI attack. The raid is also present in the shared history because autonomous attacks use the same `fob_raids` ledger.

## Incoming defense visibility

For deployed users, `fob_globe.php` includes recent incoming defense results. Human and AI attackers share the same report path, so a commander can distinguish AI competitors without requiring a separate synthetic combat history.

## Regression contracts

v0.6.0 must preserve:

- exactly one persistent home membership per commander;
- 144-slot capacity and collision-free irregular positions;
- automatic next-shard creation for full worlds;
- coherent physical Mother Base skin;
- no remote-shard browsing side effect on home membership;
- no attacker raid cooldown;
- defender protection after every completed attempt;
- offensive protection break only after a valid direct/retaliation/staff launch commits;
- exactly one retaliation per incoming source incident;
- retaliation never bypasses current target protection;
- transactionally exact resource debit/credit;
- separate standard Dispatch and FOB strike ledgers;
- shared staff reservation safety through `units.dispatched_until`;
- restart-safe exactly-once staff-strike settlement;
- all 1,000 autonomous memberships and five-biome distribution.
