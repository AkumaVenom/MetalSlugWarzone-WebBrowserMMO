# XAMPP Runtime Acceptance Plan — v0.8.1 Corrected Peace Walker-Style Automatic Battle Playback

This v0.8.1 candidate corrects the automatic battle presentation for standard Dispatch, FOB staff strikes and FOB raid results. Test against a backed-up persistent database. Schema revision remains 8, so Update / Repair is a safety verification rather than a migration. The v0.7.5 high-threat progression gate and all inherited gameplay authority remain release-blocking regressions.


## A-0000. v0.8.1 automatic battle visual correction — RELEASE BLOCKING

1. Open a completed FOB invasion result in Firefox/Edge after replacing the files. Confirm the page automatically loads the **0.8.1** CSS/JS without a hard refresh and the battle is fully styled; no giant raw sprites or stacked unstyled text are acceptable.
2. Confirm the arena visually matches the normal player encounter battle language: compact sprites, fighter cards, HP bars, two opposing sides and a centered VS/event area.
3. Confirm **both teams are visible at the same time**. Test a defender with zero assigned combat staff and require visible FOB/base defenders rather than an empty enemy side.
4. Confirm every friendly and enemy fighter shows **HP current / max** and a visible HP bar. Confirm both top Force HP bars/percentages update while the replay runs.
5. Let the battle autoplay. Require visible attacks/hit feedback, changing HP, individual KO states and a final losing force at 0%. Replay Battle must restart the same sequence; Skip Battle must jump to the same final state.
6. Inspect the battle/result screens as a player. There must be no implementation/debug copy such as “authoritative”, “deterministic”, “persisted”, “server result”, “pre-resolution” or “resolver”.
7. Repeat items 2–6 for a normal Dispatch result and an FOB Staff Strike result (including a protected-abort strike).
8. Verify the result and rewards still match the previously settled Dispatch/FOB record and are not changed by replay, skip or refresh.

## A-000. Automatic operations battle playback — RELEASE BLOCKING

1. Launch a normal **Dispatch Mission** with two or more units. Confirm the nearest pending countdown transfers automatically to `dispatch_result.php` at zero, while a manually opened not-yet-due result route remains **Mission In Progress** and does not resolve from client time alone.
2. After server resolution, confirm the Dispatch result route autoplays a tactical amber-versus-red battle before the AAR. Friendly cards must match the dispatched owned units, the operation label/power/difficulty/result must match the stored mission, and the final overlay must agree exactly with the existing Dispatch result/history row.
3. Use **Replay** repeatedly and refresh the result page. Confirm the winner, final integrity pattern and result copy do not change. Use **Skip** and confirm it immediately reaches the same committed result. Confirm no extra rewards, XP or state changes occur from playback.
4. Run an FOB **Staff Strike / Strike Team** that resolves into combat. Confirm its timer transfers to `fob_dispatch_result.php`, the resulting raid redirects to canonical `fob_result.php`, and the battle autoplays before the raid AAR. Verify attacker success chance shown to the attacker matches the committed strike chance; when viewed by the defender, the displayed pre-resolution odds are the complementary defender-side probability.
5. Run an FOB staff strike that reaches a defender who has entered recovery protection. Confirm the result remains `protected_abort`, no raid/resource transfer is fabricated, and the result page presents the dedicated shield/withdrawal automatic sequence followed by the protected-abort report.
6. Launch a **Direct Invasion** and a **Retaliation**. Confirm each canonical `fob_result.php` autoplays before the existing AAR and displays the persisted attacker/defender settlement roll values in the force-power HUD, oriented so the logged-in viewer is always the left-side force. Final victory/defeat must match the stored raid result.
7. Test a recovered infantry unit and vehicle with known `source_enemy_key` in a new FOB snapshot. Confirm subsequent playback uses the correct existing runtime sprite where available. Historical raids without the new snapshot fields must still render through class-based fallback with no errors.
8. Check standard Dispatch History, FOB Staff Operations and Command Centre recent results. Confirm settled entries expose **Battle Replay**/operation playback links and no duplicate settlement route is created.
9. Enable the OS/browser **reduced motion** preference. Confirm the result page reaches the committed final battle state without timed combat animation while preserving the AAR and controls. Test desktop and a narrow/mobile viewport for no clipped result controls or unreadable unit cards.
10. Open two accounts around the same FOB result and refresh aggressively. Confirm replay traffic never changes `fob_raids`, resources, shields, staff XP or result; only the existing authority functions may settle due work.

## A-001. v0.8.0 authority and regression boundary — RELEASE BLOCKING

1. Confirm `config/app.php` reports **0.8.1** and schema revision remains **8**. Run Update / Repair and require no new migration.
2. Confirm `database/install_schema.sql` is unchanged from the accepted v0.7.5 baseline and all existing runtime image assets remain byte-identical.
3. Confirm Dispatch success/reward settlement still occurs only through `includes/dispatch_authority.php`; inspect multiple wins/failures to ensure playback never changes committed outcome or rewards.
4. Confirm FOB raid/strike result, resource transfer, protection and staff XP settlement still occurs only through `includes/fob_world.php`; playback must remain a post-settlement view.
5. Complete the inherited v0.7.5 progression/combat, Mother Base, Security, PvP, FOB, persistence and social regression matrix below before promoting v0.8.0.

## A-00. Underlevel high-threat progression gate — RELEASE BLOCKING

1. Use a lightly developed **Commander Lv4** with the same two low-level Security escorts used in the reported v0.7.4 screenshots. Do not add major Combat/Medical/Security/R&D development for the first pass.
2. At **Threat 5**, verify a Lv6-class contact is a meaningful but still plausible fight; a representative Biker should be around 133 HP / 33 ATK / 21 DEF / 21 SPD.
3. At **Threat 7**, verify the same Lv4/light-development profile is now clearly underprepared. A representative Lv6 Heavy Gunner should be around 130 HP / 44 ATK / 20 DEF / 9 SPD and should not be comfortably farmable without healing, stronger stats or favorable play.
4. At **Threat 9**, verify a Lv8 Biker is approximately 206 HP / 54 ATK / 28 DEF / 24 SPD and that a starter/lightly upgraded Lv4 Commander is expected to lose or be forced to retreat unless substantially developed.
5. At **Threat 12**, verify a Lv4 Commander faces the accepted +3..+5 level window and extreme underlevel pressure; a representative Lv9 Shield Trooper is approximately 232 HP / 47 ATK / 43 DEF / 10 SPD. This zone should function as a progression gate, not a low-level farming area.
6. Add meaningful Mother Base development (Combat/R&D ATK, Medical HP, Security DEF, Intel/R&D SPD and/or Support/Mess mixed bonuses) while keeping the same Commander level. Confirm the player becomes materially more capable against the same threat band because Commander stats rise; enemies must not dynamically weaken when staff are moved.
7. Raise Commander level to each readiness benchmark and confirm the extra underlevel multiplier disappears exactly: T5 Lv6, T7 Lv9, T9 Lv12 and T12 Lv16. Enemy difficulty should then fall back to the accepted v0.7.4 rolled-level + threat curve.
8. Confirm Threat 1–3 are unchanged by the readiness gate and remain appropriate early progression zones.
9. Confirm enemy counter accuracy receives additional underlevel pressure in dangerous maps, while Commander SPD/Intel still reduce it and player attack accuracy remains 94–100%.
10. Load an active v0.7.4 fight under v0.7.5. Confirm enemy level/roll do not reroll, HP percentage is preserved, stats recalibrate once, and repeated refreshes do not heal or re-scale again.

## A-0. Inherited v0.7.4 base threat curve — RELEASE BLOCKING

1. Verify the v0.7.4 base threat factors are unchanged beneath the new progression gate: Threat 1 remains 0.94× HP / 0.96× ATK / 0.96× DEF / 0.98× SPD before level growth, while Threat 12 remains approximately 1.48× / 1.40× / 1.22× / 1.08× before level growth.
2. Use a Commander at or above each readiness benchmark and verify `underlevel_gap=0`; enemy stats must then match the inherited v0.7.4 rolled-level + threat formula exactly.
3. Confirm the same enemy type/rolled level still grows monotonically through Threat 5/7/9/12 before any underlevel pressure is applied.
4. Confirm enemy ATK remains a single input to `msw_damage()` and the historical ATK×ATK counter bug does not return.
5. Confirm Security interception/covering-fire formulas are byte-for-byte unchanged from v0.7.4.
6. Confirm bosses remain on their dedicated curve and do not inherit the normal underlevel readiness gate.

## A-1. Threat-aware enemy level progression — RELEASE BLOCKING

1. Use a **Commander Lv5** account in **Threat 12 / Iron Citadel Interior** and create at least 30 new field encounters. Every enemy must be **Lv8–10**; no Lv5/equal contact is valid in this case. Across a larger sample, Lv9–10 should clearly dominate and Lv10 should occur frequently.
2. Use a **Commander Lv20** account in Threat 12 and sample new encounters. Every enemy must remain within **Lv19–25**, proving the requested −1..+5 mature-player variety.
3. Compare the same Commander across the map progression. Verify the legal upper level offset rises coherently: Threat 1 → +0, Threat 3 → +1, Threat 5 → +2, Threat 7 → +3, Threat 9 → +4, Threat 12 → +5.
4. Confirm lower-threat maps still produce below-player contacts and do not inherit the Threat 12 ceiling/bias. The difficulty source must be the selected warzone, not simply Commander levelling.
5. For Commander Lv5 / Threat 12, the exhaustive server mapping is +3 ≈19%, +4 ≈33%, +5 ≈48%. For Commander Lv20 / Threat 12, confirm the complete −1..+5 window can occur while upper offsets are more common than lower offsets.
6. Compare the same enemy type and same rolled level at Threat 1 versus Threat 12 using Intel display/debug inspection. Threat 12 must have higher HP, ATK, DEF and SPD.
7. Verify representative Threat 12 Shield Troopers around Commander Lv5 retain the Lv8–10 legal range and, because Lv5 is far below the Threat 12 readiness benchmark, land around **220–230 HP / 44–47 ATK / 42–43 DEF / 9–10 SPD** under v0.7.5.
8. Keep an active v0.7.3/v0.7.4 encounter, deploy v0.7.5, and load that battle. Confirm it retains the already-rolled committed enemy level, recalculates stats through v5 while retaining prior HP percentage, and never rerolls/heals on refresh. Also verify an older pre-v0.7.3 state receives one deterministic legal-window level migration before v5.
9. Confirm new encounter state records `warzone_player_threat_window_v5`, `level_roll`, `min_offset`, `max_offset`, final `enemy_level_offset`, readiness benchmark and underlevel multiplier metadata.
10. Confirm bosses remain dangerous but use their separate tighter level window rather than the normal +5 warzone window.


## A0. Command Centre navigation label — RELEASE BLOCKING

1. Log in and inspect the primary top navigation. Confirm the former **FOB** tab now reads **COMMAND CENTRE** (case may be styled by CSS).
2. Click **COMMAND CENTRE** and confirm it opens the existing `fob.php` Invasion Command Centre page shown by the established Command Centre hero/content.
3. Confirm legitimate FOB terminology still appears where appropriate inside FOB maps, enemy-target surfaces, shields, raids and related operations.

## A. Immediate Mother Base staff-to-stat progression — RELEASE BLOCKING

1. On Staff Management, record Commander HP/ATK/DEF/SPD and the live Mother Base contribution values.
2. Take an eligible **Reserve** staff member with a non-zero R&D stat and assign them to **R&D** while the R&D sector remains below its next 120-point whole-level threshold. Confirm R&D score rises immediately and the R&D contribution shows non-zero ATK and SPD without requiring the displayed R&D level to increase; with R&D as the only contributing source for the checked stat, the first valid staff assignment must not round back to zero.
3. Repeat with Combat, Medical, Security and Intel and confirm the intended primary mapping: Combat → ATK, Medical → HP, Security → DEF, Intel → SPD. Verify Support/Mess mixed contributions as well.
4. Confirm the reassignment success message reports any resulting whole-stat delta and the live Commander power panel reflects the new totals after redirect.
5. Cross a 120-point sector boundary and confirm the displayed sector level increases normally while combat growth remains continuous rather than jumping from zero.
6. Test score around 1080/2280 points and confirm the documented late-game diminishing curve still applies.
7. Reload/logout/login/restart Apache+MySQL and confirm totals reproduce from persisted sector score with no new schema fields.

## B. SPD and player attack accuracy — RELEASE BLOCKING

1. Start a fresh PvE encounter and inspect every attack pattern. Confirm the UI displays final **ACC** and no option is below 94%.
2. Raise Commander SPD through Mother Base development and start/reload combat. Confirm final attack accuracy never decreases because the enemy is faster; there is no enemy-speed offensive penalty path.
3. Use low-base-accuracy attacks (Grenade / Armor Piercer) repeatedly and confirm the authoritative hit rate corresponds to the displayed boosted accuracy rather than the raw catalog value.
4. Raise Combat and Intel where practical and confirm their small supporting accuracy bonuses can only improve/cap accuracy, never reduce it.
5. Verify enemy counter accuracy separately: higher Commander SPD reduces counter accuracy and Intel Lv8 stacks with the SPD benefit. Confirm the final enemy counter accuracy never drops below 55%.

## C. Player-relative enemy level and threat fairness — RELEASE BLOCKING

1. Repeat the A-1 matrix at Commander levels 1, 5, 10, 15, 20 and 30. Every result must remain inside the server-reported min/max offset for that player/threat pair.
2. Confirm average/median enemy offset rises as threat increases for a fixed Commander level.
3. Confirm higher Commander level widens the lower side of dangerous-map windows rather than raising the +5 cap further; at Lv20+ Threat 12 must remain −1..+5.
4. Confirm normal Threat 12 enemies are meaningfully more durable/dangerous than low-threat equivalents but remain beatable by appropriately progressed Commanders using sensible attacks, Mother Base development, Security support and medical gear.
5. Confirm lower maps remain practical for recovery/progression and do not become globally harder simply because the Commander gained levels.
6. Confirm the stored encounter roll/window prevents refresh-based fishing for a weaker enemy after combat begins.

## D. Enemy counter damage correction — RELEASE BLOCKING

1. Compare an enemy's displayed ATK and actual counter damage across several levels. Confirm counter move power does not equal/copy the enemy ATK value.
2. Confirm class/threat still influence counter pressure: higher-threat heavy/vehicle enemies should hit harder than low-threat infantry when other conditions are similar.
3. Verify enemy ATK is applied once by the shared damage formula and no superlinear ATK×ATK-style escalation returns at higher enemy levels.
4. Test normal field contacts at several Commander levels and confirm ordinary engagements are consistently winnable with sensible move selection, while the highest-threat zones/bosses remain meaningfully more dangerous.

## E. Security escort interception/support — RELEASE BLOCKING

1. Select two eligible Security escorts and begin PvE. Confirm both display battle-local HP/max HP and rotate interception duty.
2. On successful enemy counters, confirm a living escort absorbs a visibly larger share than v0.7.0 (roughly 20% at the low end, scaling toward but never beyond 40%).
3. Confirm absorbed damage is removed from escort battle HP and only the remainder reaches the Commander.
4. Confirm KO'd escorts stop guarding and firing, and refresh/reload does not heal them.
5. Confirm covering fire lands more consistently than v0.7.0 but remains bounded: normal per-hit cap 9%/11% around Security Lv7 and boss cap 4.5%.

## F. v0.7.5 persistence and regression boundary — RELEASE BLOCKING

1. Confirm `config/app.php` reports 0.7.5 and schema revision remains 8.
2. Run Update / Repair against the existing database and confirm no new migration is requested.
3. Verify inventory/recovery, R&D manufacturing/unlocks, Dispatch, PvP, FOB/world state, autonomous Commanders, social systems and persistence remain functional.
4. Confirm CSS/layout, JavaScript interactions and runtime images are unchanged by this hotfix.
5. Complete the inherited v0.7.3/v0.6.1/v0.6.0 regression matrix before accepting v0.7.5 as the new baseline.

---

# Inherited regression matrix from v0.7.0 / v0.6.1

## 0. Production copy and visual-regression review — RELEASE BLOCKING

1. Browse the landing/login/signup flow and every main navigation surface. Confirm instructions read as player-facing game copy rather than implementation notes.
2. Verify Command, Warzone, Mother Base, Staff, Missions, Bosses, Dispatch, R&D, Strategic, PvP, AI Commanders, Community, Rankings and all FOB pages for readable headings, buttons, helper text, warnings and empty states.
3. In PvP, Dispatch and FOB history/results, confirm stored states render as natural labels such as **Quick AI Duel**, **In Progress**, **Attacker Victory** and **Defense Held** rather than raw backend keys.
4. Confirm the accepted v0.6.0 UI scale, spacing, artwork placement, scan-line animation and responsive layout have not changed or become oversized.
5. Confirm maps, sprites and supplied JPG artwork render exactly as before and that no new/generated image assets are present.

## 1. Upgrade and schema integrity — RELEASE BLOCKING

1. Back up the accepted database.
2. Replace the runtime files with the v0.7.0 candidate while preserving the package layout.
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
