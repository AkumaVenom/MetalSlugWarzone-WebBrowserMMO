# XAMPP Runtime Acceptance Plan — v0.6.0 Advanced FOB Invasion Command Centre & Retaliation

This is the release-blocking runtime plan for the v0.6.0 XAMPP candidate. Perform it against a backed-up copy of the accepted persistent database and use at least two human test accounts where multiplayer behavior is involved.

## 1. Upgrade and schema integrity — RELEASE BLOCKING

1. Back up the accepted database.
2. Replace the runtime files with the v0.6.0 candidate while preserving the package layout.
3. Open `_setup.php` locally and run **Update / Repair**. Do **not** Fresh Install.
4. Run **Confirm Installation** and require:
   - schema revision `8`;
   - `fob_raids.retaliation_for_raid_id` present;
   - unique index `uq_fob_retaliation_source` present;
   - `fob_retaliation_integrity = OK · one-use incident binding enforced`;
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

## 10. Integrated FOB Command Centre and multi-invasion coordination — RELEASE BLOCKING

1. Open **FOB** with a deployed commander and confirm `fob.php` renders the Invasion Command Centre instead of redirecting directly to the globe.
2. Confirm the status strip shows current recovery-shield state, active outbound staff invasions, inbound staff threats, retaliation orders and globally open targets.
3. Confirm the priority target matrix contains valid global human/AI rivals and that protected targets are visible but cannot launch an invasion.
4. Use **Full Intel** and the globe/shard controls and confirm all existing reconnaissance pages return cleanly to the Command Centre.
5. In the Staff Strike Planner, choose an open target and 2–4 available staff. Confirm the hidden world context follows the selected target and server-side launch succeeds.
6. Without waiting for the first strike to resolve, select a different target and different available staff and launch a second operation. Confirm both are simultaneously visible in **Active Outbound Invasions** with distinct IDs/countdowns.
7. Confirm staff reserved by operation A cannot be reused by operation B or standard Dispatch; only genuinely unreserved staff remain selectable.
8. Reload, logout/login and restart Apache/MySQL before completion. Confirm both operations persist and retain their stored target/world/timing.
9. Confirm detected inbound `fob_strike_dispatches` aimed at this commander appear in **Inbound Staff Threats** without changing their authoritative settlement state from the browser.
10. Confirm recent direct, staff and retaliation AARs appear in the Command Archive and open the same canonical `fob_result.php` reports.

## 11. Retaliation and offensive protection doctrine — RELEASE BLOCKING

1. Have human B invade human A so A receives defender protection and the incoming raid appears in A's **Retaliation Command Desk** as an unconsumed incident.
2. While A is protected, choose an unrelated open target C and commit a direct invasion. Confirm A's `fob_protection_until` is cleared immediately as part of that successful offense, while C receives normal post-invasion protection after resolution.
3. Restore/procure protection on A, then attempt to attack a currently protected target. Confirm the request is rejected and A's protection remains intact because no offense committed.
4. Restore/procure protection on A, then launch a valid 2–4 member staff strike. Confirm A's protection is cleared at **launch**, not at later arrival, and the strike remains persisted normally.
5. Return to the incoming incident from B. If B is currently protected, confirm the retaliation card remains locked and shows the target protection countdown rather than bypassing it.
6. Once B is open, retaliate from the incident. Confirm the new AAR mode is **Retaliation Strike**, `retaliation_for_raid_id` points to the exact source incident, and the AAR links back to that source.
7. If A was protected immediately before that retaliation, confirm the successful retaliation clears A's own protection. Confirm B receives normal defender protection after the retaliatory attempt, win or loss.
8. Submit the same retaliation source a second time (including a duplicate tab/double-submit attempt). Confirm the server rejects it, creates no second retaliation row, transfers no additional resources and leaves the unique source binding intact.
9. Tamper `defender_id`, `world_id` or `retaliation_raid_id` so the source incident no longer matches the original attacker/defender relationship. Confirm the request is rejected.
10. Trigger an autonomous commander that currently has protection to launch a direct or staff FOB offense. Confirm the bot uses the same doctrine and its own remaining protection is removed when the offense commits.
11. Run `_setup.php` **Confirm Installation** afterward and require `fob_retaliation_integrity = OK · one-use incident binding enforced`.

## 12. Earth globe and cross-shard navigation — RELEASE BLOCKING

1. With a deployed commander, open **FOB** and confirm the new **Invasion Command Centre** is the primary FOB surface. From it, select **Global Theatre Map** and confirm the Earth globe remains the Global Invasion Network.
2. Select each biome and confirm `fob_shards.php` lists its populated shards with population/capacity and human/AI composition.
3. Open the commander's own shard and confirm it is marked HOME.
4. Open a different populated shard, including a different biome if available. Confirm its native 2000×2000 overview renders and the page is marked REMOTE.
5. Return/relog and confirm the commander's permanent home biome, shard, slot, skin and x/y did not change.

## 13. Cross-shard human invasion — RELEASE BLOCKING

1. Place human A and human B in different shard instances (preferably different biomes for a clear test).
2. From A's globe, navigate to B's shard and select B's marker/target row.
3. Confirm target details show the remote shard context.
4. Launch an immediate invasion and verify a normal After Action Report is created.
5. Confirm successful resource transfer is exact/atomic; a loss transfers zero.
6. Confirm B receives defender protection after **either** result and cannot be immediately spammed.
7. Without waiting on any attacker cooldown, have A attack a different open target and confirm it is allowed.
8. Tamper with `world_id` so it does not match B's actual membership; confirm the target/attack is rejected.
9. Confirm After Action Report return links lead to B's relevant shard without relocating A's home FOB.

## 14. Cross-shard staff invasion — RELEASE BLOCKING

1. From a remote target, select 2–4 available staff and launch a staff FOB strike.
2. Confirm the strike ledger stores/links to the defender's remote world and the countdown persists across reload.
3. Verify selected staff are unavailable to standard Dispatch and any other FOB strike while reserved.
4. Restart Apache/MySQL before `finish_at`; return after due time and confirm exactly-once settlement.
5. Verify staff XP/Commander XP and conditional reservation release.
6. Protect the defender through another completed invasion before a second strike arrives; confirm the arriving strike becomes `protected_abort`, transfers zero and returns staff safely.
7. Repeat the cross-ledger expiry-boundary race test with standard Dispatch to confirm an older completion never clears a newer reservation.

## 15. Autonomous progression pace — RELEASE BLOCKING

1. Record a sample of AI Commander levels/Base Power/ranking positions and activity timestamps.
2. Generate normal game/network traffic for a sustained test interval and revisit the sample.
3. Confirm bots act on persisted 10–26 second schedules, progress more visibly than the v0.4.1 baseline, and still do not execute multiple actions before their own `next_action_at` merely because extra browser tabs are opened.
4. Confirm field wins/losses continue to use real Commander XP/resources, roster capacity, R&D, inventory and staff assignment; no artificial ranking number is written directly.
5. Confirm pulse work stays bounded and normal page responsiveness remains acceptable with the full 1,000-bot population.

## 16. Autonomous global/human FOB competition — RELEASE BLOCKING

1. Observe AI Network/recent activity until autonomous direct raids and staff invasion deployments appear at a noticeably higher frequency than before.
2. Verify AI attackers can choose targets outside their home shard.
3. Keep a human test FOB open (not protected) and allow simulation to run until an autonomous commander attacks it.
4. Confirm the human defense is recorded in `fob_raids`, appears in recent incoming defense history, and produces a local `FOB · DEFENSE` server-console event.
5. If the AI wins an immediate autonomous raid, confirm transfer uses the reduced autonomous rate/caps rather than the larger human direct-raid transfer.
6. Confirm human defender protection is applied and subsequent bots respect it until expiry.
7. Confirm AI staff strikes against humans remain persistent/restart-safe and can protected-abort exactly like human-launched strikes.

## 17. FOB spatial and Mother Base regression — RELEASE BLOCKING

1. Confirm every membership still has a unique `(world_id,slot_index)` and its stored x/y matches the deterministic v0.4.1 irregular anchor mapping.
2. Reload/restart and confirm all FOB markers return to the exact same positions.
3. Fill a disposable 144-member shard and confirm the next deployment creates the next shard instead of overlapping.
4. Enter physical Mother Base and confirm its map remains coherent with the permanent home FOB skin, not the most recently browsed remote shard.
5. Confirm Mother Base movement/collision, staff/hardware projection and friend/Strike Force visitation remain correct.

## 18. Existing systems regression — RELEASE BLOCKING

Verify at least one successful cycle of each accepted foundation system:

- standard Combat Unit Dispatch;
- strategic/base project timer;
- sidequest and mission rewards;
- Fulton recovery persistence;
- friends/direct messages/Strike Force access;
- rankings;
- local WorldServer console filtering/privacy;
- six warzones with mandatory multiplayer presence and autonomous avatars.

## 19. Runtime-only package check — RELEASE BLOCKING

Confirm the candidate contains the accepted runtime artwork and contains:

- no `source_assets/` directory;
- no nested `.zip` development/source archive;
- no unintended cache/temp/database dump files.

## Acceptance

Promote v0.6.0 only after the real XAMPP/MariaDB/browser environment passes all release-blocking sections. Static lint/build validation is necessary but does not replace multiplayer/database runtime acceptance.


## Visual Command Network acceptance

These checks are presentation-only and must not be used to infer gameplay authority.

1. Open Dashboard, FOB Command Centre, Warzone selection, Combat Missions, Boss Operations, Dispatch, R&D, Strategic, Rankings, Mother Base/Staff, Community and AI Commanders. Confirm each surface loads its matching supplied artwork with no broken image requests and no detached/duplicate decorative banner.
2. Confirm the website base tone is charcoal/gunmetal rather than green-dominant, with amber/orange command accents. Green must primarily signal positive/ready states; red must signal threat/danger; cyan/steel must signal information/protection/technical state.
3. Confirm the resource strip uses distinct semantic colours for Common Metal, Minor Metal, Precious Metal, Fuel, Biological and Strategic Devices and remains legible at desktop/tablet/mobile widths.
4. Confirm the global scan beam moves down the viewport, command-link indicator pulses, buttons/cards respond to hover/focus, and the selected operative appears as a decorative animated sprite on hero surfaces for authenticated commanders. None of these effects may submit a request or mutate game state.
5. Enable the operating system/browser `prefers-reduced-motion` setting and reload. Confirm non-essential scan/sweep/sprite/reveal animation stops while all controls remain fully usable.
6. Test at approximately 1440px, 1024px, 768px and 390px viewport widths. Confirm artwork crops responsively, navigation remains usable, the operative decoration does not cover hero copy, resource telemetry reflows, and no horizontal page overflow is introduced outside intentionally scrollable game/map/table viewports.
7. Verify keyboard focus on links, form controls and buttons remains visible against every new palette/background state.
