# Changelog

## v0.3.4 — Level-1 Fulton Manufacturing — XAMPP Test Candidate

### Early R&D progression deadlock fix
- Rebased directly on the v0.3.3 Per-Warzone AI Operative Variety public baseline.
- Added the standard `fulton` item to the normal R&D manufacturing catalog at **R&D Level 1**, making the first personnel-recovery system manufacturable from the beginning of R&D progression instead of relying permanently on finite starter/Field Contract stock.
- The basic recipe manufactures **4 Fulton Recovery units** for **60 Common Metal + 40 Fuel**, giving a sustainable early-game recovery loop while still consuming persistent server-authoritative resources.
- `Fulton+ Balloon Pack` remains gated at **R&D 4**, `Cargo Fulton Pack` at **R&D 8**, and `Wormhole Fulton` at **R&D 15** with their existing costs, yields, recovery bonuses and target-class restrictions unchanged.
- Battle-side recovery validation remains server-authoritative: inventory is consumed normally and each Fulton tier still requires the R&D level defined by `msw_fulton_catalog()`.

### Preservation
- Schema remains revision **4**; no database migration or progression reset is required.
- Existing starter inventories, Field Contract rewards, autonomous commander systems, per-warzone operative variety, compact AI labels, Mother Base, dispatch, FOB and PvP behavior are unchanged.
- Runtime artwork is unchanged and runtime-only packaging remains enforced.

## v0.3.3 — Per-Warzone AI Operative Variety — XAMPP Test Candidate

### Clone-army correlation fix
- Rebased on the runtime-accepted v0.3.1 Compact AI Warzone Labels baseline plus the v0.3.2 in-place skin-repair work.
- Corrected the v0.3.2 mapping defect where both warzone slot and operative slot used the same `bot_index mod 6`, causing each individual map to contain one repeated operative skin.
- Fresh seeding now rotates operative skins **inside each warzone** using the local per-map ordinal plus map offset.
- Update / Repair now reconciles already-persisted bot indexes 1–1000 in-place by current warzone and durable `bot_index`, cycling Marco, Tarma, Eri, Fio, Nadia and Trevor independently within every map.
- Each 166–167-bot warzone now receives approximately **27–28 of every operative skin**, with all six skins represented on every map.
- Repair changes only bot `users.character_key`; identity, username, XP, coordinates, roster, captures, resources, Mother Base, dispatch, FOB and PvP state remain untouched.
- Confirm Installation now validates **per-warzone** six-skin diversity instead of accepting only a globally balanced population.

### Preservation
- Compact v0.3.1 `AI` hover/focus labels remain unchanged.
- Schema remains revision **4**.
- All gameplay PHP outside population repair/setup health reporting remains unchanged.
- All 31 accepted runtime images remain unchanged.
- Runtime-only packaging remains enforced with no source archives.

## v0.3.2 — Balanced AI Operative Variety — XAMPP Test Candidate

### Autonomous commander visual variety repair
- Rebased directly on the runtime-accepted v0.3.1 Compact AI Warzone Labels baseline.
- Fixed existing persistent autonomous populations that could remain on one old/default operative skin even though the fresh seeder already cycled the six-character catalog.
- Update / Repair now deterministically reconciles bot indexes 1–1000 across Marco, Tarma, Eri, Fio, Nadia and Trevor at **167/167/167/167/166/166**.
- The repair updates only each bot user's `character_key`; bot IDs/usernames, progression, coordinates, rosters, resources, Mother Bases, dispatches, FOB history and PvP records remain untouched.
- Re-running repair is idempotent and preserves the same skin assignment for each durable `bot_index`.
- Confirm Installation now validates and reports the six-skin autonomous population distribution.

### Preservation
- Compact v0.3.1 `AI` hover labels remain unchanged.
- Schema remains revision **4** with no structural database migration.
- All accepted v0.3.1 gameplay, bot simulation, maps, collisions and runtime artwork remain unchanged.
- Runtime-only packaging remains enforced with no source asset archives.

## v0.3.1 — Compact AI Warzone Labels — XAMPP Test Candidate

### Warzone readability polish
- Rebased directly on the runtime-accepted v0.3.0 Persistent Autonomous Commanders baseline with no gameplay, schema, population or asset changes.
- Autonomous commander warzone labels now render as a compact **AI** pill by default, removing the full callsign/nameplate wall created by ~166–167 bots per warzone.
- Hovering an AI operative sprite or its AI pill reveals that exact commander's complete `callsign · AI COMMANDER · grade` identity.
- Keyboard focus reveals the same full identity and the profile link keeps a full accessible `aria-label`.
- Presence refreshes continue updating keyed DOM entities in place; the full hover identity/activity is refreshed without forcing the compact label open or causing entity teardown/recreation.
- Human player labels remain unchanged and fully visible.

### Preservation
- Schema remains revision **4**.
- Exactly 1,000 persistent autonomous commanders, collision-aware movement, field/Fulton progression, dispatches, Mother Base growth, FOB systems, Snapshot/Live AI PvP and bot-v-bot simulation remain unchanged.
- Runtime-only packaging remains enforced: no `source_assets/` directory and no nested development/source ZIP archives.

## v0.3.0 — Persistent Autonomous Commanders — XAMPP Test Candidate

### 1,000 persistent AI commanders
- Added exactly 1,000 database-backed autonomous commanders, normally presented as `WarzoneAI0001`–`WarzoneAI1000`, using the same six selectable Metal Slug operative skins as human commanders. `bot_index` is the durable identity; if a pre-existing human account already owns one of those presentation names, migration chooses a deterministic conflict-safe AI name without modifying the human account. Bot accounts are explicitly marked `is_bot=1`, cannot authenticate through the normal login flow, and retain identity/state across browser, Apache and MySQL restarts.
- Fresh Install and Update / Repair seed the population idempotently through stable `bot_index` values 1–1000. Re-running repair does not duplicate identities; experimental indexes above the production population are disabled rather than deleted.
- Fresh population distribution is balanced across all six warzones (167/167/167/167/166/166) and spatially sampled across each map's complete legal collision lattice instead of stacking commanders around a single spawn.
- Added a persistent `bot_commanders` runtime ledger for personality, activity, autonomous scheduling/leases, encounter/capture counters, FOB performance and PvP performance.

### Autonomous warzone simulation and progression
- Bots use the accepted server-authoritative 18px four-way movement path and exact v3 collision profiles. Autonomous movement cannot cross walls, cliffs, fences, buildings, machinery, water or other blocked terrain.
- Simulation is bounded and request-driven: small leased batches advance from map/presence and network surfaces instead of spawning 1,000 PHP workers. Per-bot `next_action_at` timestamps prevent extra browser clients from accelerating a commander's schedule.
- Bots resolve field contacts, earn persistent Commander XP/resources, use Fulton items, recruit actual `units` rows and grow their active combat teams/sector staffing. Recovery obeys the same roster, R&D and Cargo Fulton restrictions as player recovery.
- Autonomous staff assignment deliberately develops R&D when appropriate so mature bots can legitimately unlock Cargo Fulton and recover vehicle-class contacts rather than bypassing player progression rules.
- Bots reorganize Mother Base staff, recalculate sector levels/Base Power, manufacture/restock recovery equipment from real GMP/resources, and use the real persisted `dispatch_missions` ledger. Autonomous dispatches remain pending against MySQL `finish_at`, survive server/browser restarts, return deployed units, and resolve rewards/unit XP exactly once.
- Captured personnel/hardware remain normal persistent roster entities and therefore feed the accepted physical Mother Base garrison/parking system.

### FOB and PvP parity
- Human players can select AI commander FOBs through the existing transactional FOB snapshot/infiltration system. Bot resources, Base Power, Security Team and active Combat Unit are real authoritative defender state and real successful transfers debit the bot's stock.
- Bots autonomously raid other bots using the same snapshot/resource-ledger principles, attacker cooldown and defender protection. Autonomous bot raids intentionally do not silently drain human commanders while they are offline.
- Added server-driven **Live AI PvP** and immutable **Commander Snapshot Battle** modes against bots. The selected Metal Slug operative and persistent Commander stats are snapshotted at match creation; bot turns are committed server-side without a bot browser/session.
- Added autonomous bot-v-bot PvP simulations recorded in the shared `pvp_matches` ledger with persistent XP and win/loss counters.
- Added `match_mode` (`live`, `live_ai`, `snapshot`, `ai_sim`) to PvP persistence while preserving version-locked turn settlement.

### Multiplayer presence and operator surfaces
- Warzone presence now includes all bots assigned to the current map alongside human players. AI commanders are clickable, use player-character sprites/facing, and expose current autonomous activity through a dedicated profile.
- Reworked remote-presence JavaScript to update keyed DOM entities in place instead of deleting/recreating ~166+ bot avatars every 3-second refresh.
- Added **AI Network** with total population, exact per-warzone counts and a recent autonomous-activity roster.
- Added AI indicators to profiles, rankings, FOB targets and PvP surfaces. Bot profiles expose field/capture/FOB/PvP counters plus direct Live AI and Snapshot challenge actions.
- Bot identities are excluded from friend requests/direct messages because they cannot consent/respond through a login session; combat/FOB interaction remains fully available.

### Schema / setup / packaging
- Schema revision advances from 3 to **4** with additive `users.is_bot`, `bot_commanders`, and `pvp_matches.match_mode` changes. Existing human accounts, Mother Bases, rosters, resources, dispatches, FOB history, social state and Commander progression are preserved.
- Local `_setup.php` Confirm Installation now verifies exactly 1,000 enabled/distinct bot indexes and balanced six-warzone distribution. Local setup removes the normal PHP execution-time limit while population seeding/repair runs.
- Runtime-only packaging remains mandatory: no `source_assets/` directory and no nested source/development ZIP archives are shipped.


## v0.2.0 — Persistent Mother Base Visitation — XAMPP Test Candidate

### Player-selected physical Mother Bases
- Added seven user-supplied physical Mother Base/FOB maps: four land variants and three sea variants.
- Account creation now requires a Mother Base selection in addition to the field operative.
- Added `mother_base_key` to commander persistence. Existing accounts migrate non-destructively to `land_dirt` and can change bases from Account Options at any time.
- Changing the selected base preserves all Commander XP, resources, captured units/hardware, sector progression, dispatches, R&D, FOB history, social state and PvP state.

### Live Mother Base spaces
- Added `mother_base.php` as a native-size close-up shared base viewport with camera auto-follow and external WASD/Arrow command controls.
- Added server-authoritative Mother Base collision profiles covering buildings, perimeter structures, machinery, cargo/props, platform edges and ocean boundaries.
- Added nearest-legal-position repair for visitors when a base selection/layout changes.
- Entering a Mother Base clears warzone presence so an account cannot be authoritative in two physical spaces simultaneously.

### Persistent staff and hardware simulation
- Added `mother_base_unit_positions` persistence keyed directly to owned `units`. Newly recovered units appear automatically on the owner's current physical base.
- Human staff receive persistent anchor positions and move only in slow 10-pixel server-timed steps every 7–16 seconds. Movement is collision-checked and limited to a local patrol radius to avoid fast/unrealistic wandering.
- Captured vehicles/air-class hardware remain stationary in safe parking positions. Legacy retired vehicle source keys normalize visually to the current Rebel Biker runtime asset.
- Presence polling updates existing staff/visitor DOM entities in place for smooth movement without re-entering or manually refreshing the base.

### Friend and Strike Force visitation
- Established friends can visit one another's selected Mother Base directly from the Friend Network/profile.
- Members of the same Strike Force can visit one another's bases from the Strike Force member roster.
- Authorization is revalidated server-side on entry, every movement request and every presence refresh.
- Added dedicated `mother_base_presence` persistence for visitors; physical-base presence is separate from warzone presence.

### Schema / packaging
- Schema revision advanced from 2 to 3 with additive, non-destructive migration.
- Added `mother_base_presence` and `mother_base_unit_positions` tables.
- Runtime-only packaging policy is preserved. The supplied Mother Base source ZIP is not shipped.

## v0.1.4 — V3 Native Map + Collision — XAMPP Test Candidate

### Runtime-only packaging correction
- Removed the entire `source_assets/` tree from the distributable XAMPP package.
- Full source libraries and replacement ZIP archives are development inputs only and are not shipped with the runtime candidate.
- Runtime PNG assets, PHP, JavaScript, database files and documentation required to operate/test the game remain packaged.

### Authoritative v3 warzones
- Rebased directly on the runtime-working v0.1.3 candidate and replaced all six deployed warzone PNGs with the exact user-supplied v3 map files.
- New native dimensions are 1448×1086, 1402×1122, 1448×1086, 1254×1254, 1448×1086 and 1448×1086. Runtime art is never scaled to fit the page.
- Preserved existing map keys for database/update compatibility while updating theatre presentation metadata to match the new overhead environments.
- Added explicit safe spawn coordinates for every v3 map.

### Native close-up warzone viewport
- Reworked the battlefield viewport to a fixed close-up camera window while leaving the map world at exact 1:1 native pixels.
- Removed the old short-map adaptive viewport behavior; the new square/overhead maps intentionally extend beyond the camera and are explored by scrolling.
- Auto-follow remains centered on the local player after accepted movement, and manual scroll remains available.
- Directional buttons remain outside the map viewport and therefore cannot scroll off-screen with the level art.

### Server-authoritative terrain collision
- Added a dedicated authored collision catalog for each of the six v3 maps, covering solid perimeter terrain plus major walls, cliffs, fences, buildings, machinery, water, pits, barricades and prop clusters.
- Collision is validated in PHP using the player's native-map foot point; browser DOM/CSS state is never authoritative.
- Every movement request checks the complete travel segment in 4-pixel samples, preventing an 18-pixel movement step from tunneling through a thin blocker.
- Blocked moves preserve the authoritative position, update facing, do not roll random encounters, and return a descriptive blocked-terrain status to the field-control UI.
- Added automatic nearest-legal-position recovery for persisted v0.1.3 coordinates that land inside solid v3 terrain after update. No database reset is required.

### Preservation
- Schema revision remains 2. Commander XP, inventory, captured units, Mother Base, resources, dispatches, FOB state, social state and PvP state are preserved.
- Existing polished character/contact assets from v0.1.3 remain authoritative and unchanged.
- Runtime uses the exact supplied v3 map PNGs at native resolution; source ZIP archives are intentionally excluded from the distributable package.

## v0.1.3 — Polished Asset Replacement — XAMPP Test Candidate

### Authoritative user-supplied production art
- Rebased directly on the runtime-tested v0.1.2 project and integrated the user-supplied `All assets to use inplace of all old.zip` as the authoritative replacement set for active field presentation.
- Replaced all six deployed warzone images with the supplied v2 maps at their exact native dimensions: 922×236, 2123×214, 898×242, 3056×224, 3105×221 and 1480×256.
- Replaced the playable-operative runtime art for Marco, Tarma, Eri, Fio, Nadia and Trevor with the supplied polished assets. Marco/Tarma/Eri/Fio/Nadia use the supplied left/right frames; Trevor uses the supplied right frame with a runtime mirror fallback only when facing left because no Trevor-left replacement was present in the supplied archive.
- Replaced active field-contact art for Rebel Rifleman, Bazooka Trooper, Shield Trooper, Heavy Gunner and Rebel Biker with the supplied polished assets.
- Retired Girida-O, Di-Cokka and R-Shobu from new field/mission/trainer encounter catalogs so no retired tank/gunship runtime sprite is selected. The Rebel Biker is now the vehicle-class contact used by those affected encounter roles.
- Existing active encounters created by an earlier build are normalized to the production Rebel Biker visual/contact metadata on the next battle load/action so an in-progress old contact cannot reference a removed runtime sprite.
- Existing boss art remains unchanged because the supplied replacement archive contains no Huge Hermit or Rootmars replacement files.

### Directional multiplayer presentation
- Multiplayer presence now transmits left/right operative sprite references and uses the supplied directional frame matching player facing.
- Local movement swaps to the correct supplied left/right operative asset immediately as horizontal facing changes.
- Trevor's left-facing state uses a presentation-only mirror of the supplied right frame; no generated or substitute image file was created.

### Native-map update safety
- Persisted map coordinates are clamped against the new v2 native dimensions when a commander deploys, preventing positions from an older larger map from rendering outside the replacement theatre after an update.
- Server-authoritative movement bounds continue to use the catalog's exact replacement-map dimensions.

## v0.1.2 — Production Runtime Polish — XAMPP Test Candidate

### Dispatch interaction correction
- Replaced modifier-key-dependent multi-select controls with explicit checkbox unit cards for every dispatch mission.
- Added live selected-slot counters, exact-slot client gating, server-side array validation and unavailable-team messaging.
- Preserved authoritative unit availability checks, mission power snapshots, database completion timestamps and one-time unit XP resolution.

### Runtime sprite reconstruction
- Re-audited every currently used playable-character, enemy, vehicle, aircraft and boss sprite against the supplied source library.
- Replaced composite animation-sheet regions, annotation-bearing frames and visually incomplete selections with clean single-frame source art.
- Corrected runtime presentation for Marco, Tarma, Eri, Fio, Nadia, Trevor, Rifleman, Bazooka Trooper, Shield Trooper, Heavy Gunner, Rebel Biker, Girida-O, Di-Cokka, R-Shobu, Huge Hermit and Rootmars.
- Added dedicated boss framing and class-aware battle sprite sizing while preserving pixel-art rendering.

### Warzone viewport and movement reconstruction
- Rebuilt the deployed-warzone page around a fixed native-size scroll viewport instead of a page-filling battlefield container.
- Viewport height now tracks the native map height up to a controlled maximum, eliminating the large unused black field on short side-scroller maps.
- Moved the directional command pad completely outside the scrolling map so controls never disappear while the battlefield scrolls.
- Added a sticky field-control sidebar, operative card, map metadata and scroll/readiness presentation.
- Reworked keyboard movement into a throttled latest-input queue using physical Arrow/WASD key codes.
- Added clean server 429 retry timing so key auto-repeat no longer causes movement to appear stuck.
- Preserved authoritative movement bounds, encounter locking and mandatory multiplayer presence.

### Commander Level / XP progression
- Promoted existing account XP into a fully presented Commander progression system.
- Added XP floor/next-level helpers, progress percentage, Command Rank display and level-derived HP/ATK/DEF/SPD.
- The selected field operative is now the actual PvE/PvP Commander combat profile instead of showing a recovered unit callsign under the operative sprite.
- Field victories, missions, Field Contracts, Rival Commander wins, boss victories and successful Fulton recoveries now visibly award Command XP; level-up events are recorded in the combat log.
- Live PvP now awards persistent Command XP to both winner and loser exactly once when the version-locked match resolves.
- Added Commander level/XP presentation to the global header, Command Center, profile and deployed-warzone sidebar.
- Existing active PvE encounters are normalized to the Commander profile on load/action, preserving the current HP ratio during an update.


## v0.1.1 — Production Foundation Audit — XAMPP Test Candidate

### Correctness
- Fixed a release-blocking Fulton recovery parse error in the battle engine.
- Corrected Combat Unit selection so four active units are permitted as intended.
- Added Mother Base sector-capacity enforcement during reassignment.
- Reworked dispatch XP handling so XP is applied exactly once and unit level resolution occurs under a row lock.
- Prevented movement while an unresolved PvE encounter exists.
- Added server-side movement burst throttling.

### Multiplayer / map polish
- Local map labels now follow the player instead of remaining at the spawn coordinate.
- Movement now recenters the scroll viewport smoothly.
- Presence payloads now include facing state.
- Presence polling cadence and remote rendering were refined.
- Live PvP now watches the authenticated match-version endpoint and refreshes automatically when the opponent commits a turn.
- Encounter frequency was reduced to improve actual exploration pacing.
- Added restrained sprite motion to command UI, character cards and battle presentation, with `prefers-reduced-motion` support.

### PvE expansion
- Added Field Contracts as a side-operation system.
- Added Rival Commander trainer-style battles with named opponents and persistent clear counts.
- Rival Commander deployed units cannot be Fulton recovered during command duels.
- Expanded encounter contexts to field, mission, sidequest, trainer and boss.

### FOB hardening
- Added consistent user/resource row-lock ordering to reduce cross-raid deadlock risk.
- Added attacker raid cooldown.
- Added temporary defender protection after a successful resource theft.
- Preserved exact atomic attacker credit / defender debit behavior.
- Captured attack/defense resolution rolls inside immutable raid snapshots.

### Account / social hardening
- Account creation and starter roster initialization now commit atomically.
- Added direct-message burst throttling.
- Fixed invalid non-friend message selections from rendering as usable channels.
- Reciprocal pending friend requests now establish the friend link cleanly.

### Installer / schema
- Advanced schema to revision 2.
- `_setup.php` Update / Repair now performs additive migrations instead of only creating missing tables.
- Confirm Installation now checks the complete required table set and expected schema revision.
- Fresh Install requires explicit `RESET` acknowledgement.
- Added a static SQL schema reference under `database/`.

## v0.1.0 — Production Foundation
- Initial server-authoritative Metal Slug Warzone browser MMORPG foundation.
