# XAMPP Runtime Acceptance Plan — v0.4.1 Polished FOB Spatial Distribution & Globe Alignment

v0.4.1 is a non-destructive corrective update over the **v0.4.0 Sharded Global FOB World** baseline. Schema advances **5 → 6** to reflow existing FOB x/y coordinates from their unchanged slot identities. The same repair path also supports direct upgrades from v0.3.5/schema 4. Use **Update / Repair** for an existing database; do not Fresh Install unless deliberately testing a clean database.

## 1. Backup and schema migration — RELEASE BLOCKING

1. Back up the existing database (v0.4.0/schema 5 or earlier supported baseline).
2. Replace runtime files with the v0.4.1 candidate while preserving the package structure.
3. Open `_setup.php` locally and run **Update / Repair**.
4. Run **Confirm Installation** and require:
   - schema revision `6`;
   - exactly 1,000 enabled persistent autonomous commanders;
   - `fob_worlds`, `fob_world_memberships`, `fob_strike_dispatches` all `OK`;
   - `autonomous_fob_memberships = OK · 1000 persistent unique slots`;
   - `autonomous_fob_distribution` reports 200 bots in each of Continental / Forest / Desert / Arctic / Sea;
   - `fob_slot_collision_guard = OK · zero duplicate world slots`;
   - `fob_spatial_distribution = OK` and reports irregular collision-free anchors.
5. Confirm existing human Commander XP, characters, resources, staff/vehicles, sector levels, standard dispatches, raid history, PvP/social state and inventory remain intact.
6. Existing human accounts should have **no forced automatic FOB membership** until their owner makes the new globe choice.

## 2. New account FOB onboarding — RELEASE BLOCKING

1. Create a new account and choose a field operative.
2. Confirm signup no longer asks for a Mother Base and sends the new commander directly to the Earth FOB deployment screen.
3. Confirm the supplied 1254×1254 Earth overview is visible with five choices: **Continental, Forest, Desert, Arctic, Sea**. Verify each control is visually centered on its matching art region: Continental = gold Americas, Forest = green Eurasia, Desert = orange Africa, Arctic = polar ice, Sea = open ocean.
4. Select each land biome in separate disposable accounts/tests and confirm the next screen exposes only its matching skin.
5. Select **Sea** and confirm exactly three coherent choices appear: Offshore Alpha, Offshore Bravo and Maritime Fortress.
6. Deploy a skin and confirm the account enters its native 2000×2000 overview world and the camera centers on the player's FOB.

## 3. Existing account migration flow — RELEASE BLOCKING

1. For a v0.3.5 account, log in with a pre-update human commander and confirm the one-time globe flow still works. For an already deployed v0.4.0 account, confirm no new biome/skin selection is forced.
2. Open **FOB**. Confirm the globe/skin deployment flow appears once.
3. On another not-yet-deployed legacy account, open **Mother Base** and confirm it redirects to the same global deployment flow rather than silently using an incoherent legacy base. On an already deployed v0.4.0 account, record its `world_id`, `shard_index`, `slot_index` and `skin_key` before repair and confirm all four remain unchanged afterward.
4. Complete deployment and confirm all existing staff, hardware, resources, sector levels, XP and historical raid records remain.
5. Relog/restart browser and confirm the account returns directly to the same overview world, same shard, same FOB skin and same slot.
6. Open Profile and confirm independent Mother Base redeployment is no longer offered; the Global FOB placement is presented as locked persistent identity.

## 4. Shard placement and non-overlap — RELEASE BLOCKING

1. With several human accounts in one biome, confirm every FOB has a distinct position.
2. Query `fob_world_memberships` and confirm no duplicate `(world_id,slot_index)` pair exists.
3. Confirm every membership x/y equals `msw_fob_slot_position(slot_index, biome_key, shard_index)`, the icons are visually separated, and the population is visibly irregular rather than arranged in horizontal/vertical rows.
4. Confirm the overview header reports the correct shard label (`FOREST-001`, `SEA-002`, etc.) and population/capacity.
5. Capacity test in a disposable database: populate a biome to 144 memberships, then deploy one additional account. Confirm the server creates the next shard and places the new account there rather than overlapping shard 001.
6. Confirm a commander cannot manually switch to another shard from the UI or by changing query parameters.
7. Compare two partially populated shards of the same biome and confirm their visible anchor subsets are not identical; biome/shard permutation should alter the partial spatial pattern while remaining deterministic after reload.
8. Reload/restart Apache and MySQL and confirm every FOB returns to the exact same irregular x/y position.

## 5. Same-shard target authority — RELEASE BLOCKING

1. Use two accounts in the same shard and confirm each appears on the other's overview map.
2. Select the rival icon and confirm `fob_target.php` opens its correct commander/base/security details.
3. Use an account in a different shard and attempt to substitute its user ID into `fob_target.php?id=...` or the attack POST. Confirm the target is rejected / not found.
4. Confirm the classic Infiltration Network lists only rival FOBs in the current world instance.

## 6. Direct invasion cooldown removal + defender protection — RELEASE BLOCKING

1. Have attacker A and open defenders B and C in the same shard.
2. A immediately infiltrates B and receives a normal After Action Report.
3. Without waiting, A infiltrates C. Confirm there is **no attacker cooldown** blocking the second raid.
4. Immediately try B again. Confirm B is blocked only because B has post-invasion protection.
5. Test both an attacker-win and a defender-win result and confirm the invaded defender receives protection in either case.
6. After protection expires, confirm B becomes attackable again.
7. Verify successful transfers debit B and credit A atomically and failure transfers zero.

## 7. Staff FOB invasion dispatch — RELEASE BLOCKING

1. Open an enemy FOB and select 2–4 available staff.
2. Confirm fewer than 2 / more than 4 cannot be submitted; server validation must still reject malformed requests if browser controls are bypassed.
3. Launch the strike and verify the Staff Strike Ledger shows target, team count, stored chance and countdown.
4. While staff are en route, open standard `dispatch.php` and confirm those same units are unavailable because `units.dispatched_until` is shared.
5. Reload pages and confirm the mission persists.
6. For restart persistence, stop/restart Apache/MySQL before `finish_at`, then return after the timestamp and confirm the strike resolves exactly once.
7. Confirm staff return (`dispatched_until` cleared), receive XP, and the result links to a normal FOB After Action Report.
8. Launch a staff strike, then have a different commander invade/protect the target before the strike arrives. Confirm the arriving mission becomes `protected_abort`, transfers zero resources and safely returns the staff.

## 8. Standard Dispatch regression — RELEASE BLOCKING

1. Run at least one normal `dispatch.php` mission from start to finish.
2. Confirm its original mission catalog, slot count, success chance, reward, unit XP and return behavior remain unchanged.
3. Confirm a unit on a normal Dispatch mission cannot be selected for an FOB staff invasion.
4. Confirm `dispatch_missions` and `fob_strike_dispatches` remain separate ledgers.
5. Expiry-boundary race test: let a standard mission reach `finish_at`, then immediately open an FOB target; confirm the due standard mission resolves before those units are offered for an FOB strike. Repeat in reverse with an FOB strike reaching `finish_at` and then opening standard Dispatch.
6. With two browser tabs submitting near the same expiry boundary, confirm an older completion never clears the `dispatched_until` timestamp of a newer mission and no unit is simultaneously active in both ledgers.

## 9. Autonomous commander FOB integration — RELEASE BLOCKING

1. Open AI Network and confirm the Autonomous FOB Distribution section reports five biomes and the expected shard counts/populations.
2. In your own FOB shard, confirm AI FOB markers are visible and selectable.
3. Infiltrate an AI FOB as a human and confirm normal raid/protection/resource behavior.
4. Let bot simulation run and verify bot activities can record both immediate FOB infiltration and staff invasion deployment/return.
5. Inspect bot staff while a bot strike is pending and confirm its selected units are reserved through `dispatched_until`.
6. Confirm autonomous attack targets remain other bots only; no offline human resource loss should occur from background bot aggression.

## 10. Mother Base coherence / visitation regression — RELEASE BLOCKING

1. Enter the physical Mother Base after global deployment and confirm its map exactly matches the selected overview FOB skin.
2. Confirm staff/hardware projection, movement, collision and persistent positions still function.
3. Friend/Strike Force visitation must still authorize correctly.
4. Verify a Sea overview commander uses the selected Sea physical base; Forest/Desert/Arctic/Continental commanders use their corresponding land base.

## 11. Local WorldServer console regression — RELEASE BLOCKING

1. Launch `serverconsole.bat` on the server PC.
2. Confirm normal human page/action events still appear and bot events remain excluded.
3. Confirm new FOB deployment, direct raid and staff dispatch actions produce concise `FOB` channel entries for human commanders.
4. Confirm warzone/Mother Base movement and presence polling remain completely suppressed.
5. Confirm no passwords, cookies, CSRF tokens, raw POST data or direct-message bodies enter `_server_console/events.ndjson`.

## 12. Runtime-only packaging

Confirm the candidate contains:

- the runtime globe, five overview maps and seven overview FOB icons;
- **no** `source_assets/` directory;
- **no** nested/source ZIP archive, including the supplied overview-world development ZIP.

## Acceptance

Promote v0.4.1 only after the real XAMPP/MariaDB/browser environment confirms correctly aligned globe controls, irregular persistent collision-free FOB placement, unchanged shard/slot ownership for upgraded v0.4.0 accounts, automatic next-shard creation, same-shard attack authority, no attacker cooldown, defender-only protection, restart-safe staff invasions, unchanged standard Dispatch behavior, and complete autonomous commander integration.
