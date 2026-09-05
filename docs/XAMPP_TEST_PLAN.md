# XAMPP Runtime Acceptance Plan — v0.5.0 Combat Support, Global FOB Invasion & Competitive AI

This is the release-blocking runtime plan for the v0.5.0 XAMPP candidate. Perform it against a backed-up copy of the accepted persistent database and use at least two human test accounts where multiplayer behavior is involved.

## 1. Upgrade and schema integrity — RELEASE BLOCKING

1. Back up the accepted database.
2. Replace the runtime files with the v0.5.0 candidate while preserving the package layout.
3. Open `_setup.php` locally and run **Update / Repair**. Do **not** Fresh Install.
4. Run **Confirm Installation** and require:
   - schema revision `7`;
   - `security_backup_slots` present;
   - `security_backup_integrity = OK · valid party support slots`;
   - existing bot population/distribution checks remain OK;
   - `fob_slot_collision_guard = OK`;
   - `fob_spatial_distribution = OK`.
5. Confirm existing human Commander XP, characters, resources, inventory, staff/vehicles, sector levels, home FOB biome/shard/slot/skin, dispatches, raid history, PvP and social state remain intact.
6. Restart Apache/MySQL once and reconfirm the same state.

## 2. Fulton threshold rebalance — RELEASE BLOCKING

Use accounts/test staff arrangements that produce the required R&D levels.

1. At R&D 4, confirm Fulton and Fulton+ are available, but Cargo and Wormhole are locked.
2. Reach R&D 5 and confirm **Cargo Fulton Pack** unlocks immediately and manufactures quantity 2 for the displayed cost.
3. In battle against a vehicle-class contact, confirm Cargo Fulton is accepted at R&D 5 and still rejects an invalid/unrecoverable class as before.
4. At R&D 7, confirm Wormhole remains locked.
5. Reach R&D 8 and confirm **Wormhole Fulton** unlocks and can recover valid personnel/vehicle/air classes according to the catalog.
6. Confirm no client-side form modification can bypass a missing R&D level.

## 3. Medical Team consumables — RELEASE BLOCKING

1. Reach R&D 2 + Medical 2. Manufacture **Combat Medkit ×3** and confirm exact resource debit and persistent inventory credit.
2. Damage the Commander in a PvE fight and use one Combat Medkit. Confirm 35 base HP is restored (subject to missing-HP cap), exactly one item is consumed, Security backup may act, then the living enemy receives its normal counter turn.
3. Attempt to use a medical item at full HP. Confirm the action is refused and the item is **not consumed**.
4. With R&D 5 + Medical 5, manufacture/use Trauma Kit and confirm 80 base healing.
5. With R&D 8 + Medical 8, manufacture/use Nanomed Injector and confirm 160 base healing.
6. Verify a sufficient R&D level without the required Medical level does not unlock the medical recipe/use, and vice versa.
7. Relog and confirm remaining medical inventory persists.

## 4. Support Unit healing bonuses — RELEASE BLOCKING

1. At Support 1–2, verify base medical values.
2. At Support 3–5, verify displayed/effective healing is 115% of base before the missing-HP cap.
3. At Support 6+, verify total healing is 125% of base.
4. Confirm the browser cannot alter the multiplier by changing form values.

## 5. Security two-member backup detail — RELEASE BLOCKING

1. Assign at least three recovered personnel to the Security Team.
2. Open Staff and select two different eligible infantry/heavy-infantry members into Security Escort Detail slots 1 and 2.
3. Confirm a vehicle/air unit cannot be selected even if assigned to Security.
4. Confirm the same unit cannot occupy both slots.
5. Start a PvE battle and confirm both valid escorts appear beside the Commander.
6. Attack for several rounds. Confirm escorts automatically provide occasional covering fire after the player's action, misses are possible, and their damage is visibly much lower than primary-commander output.
7. Test a boss: confirm backup damage remains tightly capped and does not trivialize the boss.
8. At Security 4, verify the 5 percentage-point assist-accuracy improvement through repeated controlled testing; at Security 7, verify only the modest non-boss damage-ceiling increase.
9. Dispatch a selected escort through a mission/FOB strike. Confirm it is unavailable as active battle backup while dispatched.
10. Reassign a selected escort away from Security and confirm its backup slot is cleared.
11. Relog and confirm valid selected slots persist.

## 6. Intel Team functional unlocks — RELEASE BLOCKING

1. Intel 1: confirm advanced tactical information is absent.
2. Intel 2: confirm enemy ATK, DEF and SPD appear in PvE.
3. Intel 4: confirm move-effectiveness information and the recommended attack appear; compare recommendation to the displayed enemy class/type multipliers.
4. Intel 6: damage a recoverable enemy and confirm the exact current Fulton chance is shown **before** item commitment and changes when enemy HP changes.
5. Intel 8: verify enemy counterattack accuracy uses the 6 percentage-point reduction. Confirm this is applied server-side rather than represented as a cosmetic badge only.

## 7. Capability Matrix — RELEASE BLOCKING

1. Open Mother Base and locate the Capability Matrix.
2. Verify R&D, Medical, Intel, Security and Support milestones match the runtime thresholds in this plan.
3. Raise/lower effective sector levels by staff assignment and recalculate the base; confirm ACTIVE/LOCKED states follow current persisted sector levels.
4. Confirm this matrix does not expose nonexistent features or stale Cargo R&D 8 / Wormhole R&D 15 requirements.

## 8. Unified PvE battle choreography — RELEASE BLOCKING

Exercise one of each: field contact, mission, sidequest, rival commander and boss.

For each applicable fight verify:

- initial contact enters cleanly;
- player attacks produce a forward action/lunge and enemy hit reaction when hit;
- enemy counterattacks animate the enemy and player impact appropriately;
- Security covering fire visibly identifies the acting backup slot/member;
- medical use produces healing feedback;
- Fulton attempts produce extraction feedback and success animation when recovered;
- combat log/result/state remains consistent with the animation after reload;
- repeated clicks/version conflicts cannot duplicate a turn.

Enable OS/browser reduced-motion preference and confirm nonessential motion is suppressed while combat remains usable.

## 9. PvP choreography regression — RELEASE BLOCKING

1. Run human-v-human Live PvP if two accounts are available.
2. Run Live AI and Commander Snapshot against an autonomous commander.
3. Confirm each committed attack records and displays attacker motion / defender impact corresponding to the authoritative turn.
4. Confirm PvP damage, version locking, match results and no-permanent-unit-death behavior remain unchanged.
5. Confirm Security escorts and PvE medical items are not injected into PvP competitive balance.

## 10. Earth globe and cross-shard navigation — RELEASE BLOCKING

1. With a deployed commander, open **FOB** and confirm the Earth globe is now the Global Invasion Network rather than immediately forcing the home shard.
2. Select each biome and confirm `fob_shards.php` lists its populated shards with population/capacity and human/AI composition.
3. Open the commander's own shard and confirm it is marked HOME.
4. Open a different populated shard, including a different biome if available. Confirm its native 2000×2000 overview renders and the page is marked REMOTE.
5. Return/relog and confirm the commander's permanent home biome, shard, slot, skin and x/y did not change.

## 11. Cross-shard human invasion — RELEASE BLOCKING

1. Place human A and human B in different shard instances (preferably different biomes for a clear test).
2. From A's globe, navigate to B's shard and select B's marker/target row.
3. Confirm target details show the remote shard context.
4. Launch an immediate invasion and verify a normal After Action Report is created.
5. Confirm successful resource transfer is exact/atomic; a loss transfers zero.
6. Confirm B receives defender protection after **either** result and cannot be immediately spammed.
7. Without waiting on any attacker cooldown, have A attack a different open target and confirm it is allowed.
8. Tamper with `world_id` so it does not match B's actual membership; confirm the target/attack is rejected.
9. Confirm After Action Report return links lead to B's relevant shard without relocating A's home FOB.

## 12. Cross-shard staff invasion — RELEASE BLOCKING

1. From a remote target, select 2–4 available staff and launch a staff FOB strike.
2. Confirm the strike ledger stores/links to the defender's remote world and the countdown persists across reload.
3. Verify selected staff are unavailable to standard Dispatch and any other FOB strike while reserved.
4. Restart Apache/MySQL before `finish_at`; return after due time and confirm exactly-once settlement.
5. Verify staff XP/Commander XP and conditional reservation release.
6. Protect the defender through another completed invasion before a second strike arrives; confirm the arriving strike becomes `protected_abort`, transfers zero and returns staff safely.
7. Repeat the cross-ledger expiry-boundary race test with standard Dispatch to confirm an older completion never clears a newer reservation.

## 13. Autonomous progression pace — RELEASE BLOCKING

1. Record a sample of AI Commander levels/Base Power/ranking positions and activity timestamps.
2. Generate normal game/network traffic for a sustained test interval and revisit the sample.
3. Confirm bots act on persisted 10–26 second schedules, progress more visibly than the v0.4.1 baseline, and still do not execute multiple actions before their own `next_action_at` merely because extra browser tabs are opened.
4. Confirm field wins/losses continue to use real Commander XP/resources, roster capacity, R&D, inventory and staff assignment; no artificial ranking number is written directly.
5. Confirm pulse work stays bounded and normal page responsiveness remains acceptable with the full 1,000-bot population.

## 14. Autonomous global/human FOB competition — RELEASE BLOCKING

1. Observe AI Network/recent activity until autonomous direct raids and staff invasion deployments appear at a noticeably higher frequency than before.
2. Verify AI attackers can choose targets outside their home shard.
3. Keep a human test FOB open (not protected) and allow simulation to run until an autonomous commander attacks it.
4. Confirm the human defense is recorded in `fob_raids`, appears in recent incoming defense history, and produces a local `FOB · DEFENSE` server-console event.
5. If the AI wins an immediate autonomous raid, confirm transfer uses the reduced autonomous rate/caps rather than the larger human direct-raid transfer.
6. Confirm human defender protection is applied and subsequent bots respect it until expiry.
7. Confirm AI staff strikes against humans remain persistent/restart-safe and can protected-abort exactly like human-launched strikes.

## 15. FOB spatial and Mother Base regression — RELEASE BLOCKING

1. Confirm every membership still has a unique `(world_id,slot_index)` and its stored x/y matches the deterministic v0.4.1 irregular anchor mapping.
2. Reload/restart and confirm all FOB markers return to the exact same positions.
3. Fill a disposable 144-member shard and confirm the next deployment creates the next shard instead of overlapping.
4. Enter physical Mother Base and confirm its map remains coherent with the permanent home FOB skin, not the most recently browsed remote shard.
5. Confirm Mother Base movement/collision, staff/hardware projection and friend/Strike Force visitation remain correct.

## 16. Existing systems regression — RELEASE BLOCKING

Verify at least one successful cycle of each accepted foundation system:

- standard Combat Unit Dispatch;
- strategic/base project timer;
- sidequest and mission rewards;
- Fulton recovery persistence;
- friends/direct messages/Strike Force access;
- rankings;
- local WorldServer console filtering/privacy;
- six warzones with mandatory multiplayer presence and autonomous avatars.

## 17. Runtime-only package check — RELEASE BLOCKING

Confirm the candidate contains the accepted runtime artwork and contains:

- no `source_assets/` directory;
- no nested `.zip` development/source archive;
- no unintended cache/temp/database dump files.

## Acceptance

Promote v0.5.0 only after the real XAMPP/MariaDB/browser environment passes all release-blocking sections. Static lint/build validation is necessary but does not replace multiplayer/database runtime acceptance.
