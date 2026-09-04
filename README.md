# Metal Slug Warzone — Web Browser MMORPG

**Version:** 0.3.4 Level-1 Fulton Manufacturing — XAMPP Test Candidate  
**Schema revision:** 4

Metal Slug Warzone is a server-authoritative PHP/MySQL browser MMORPG built around persistent four-way exploration, turn-based combat, Fulton recovery, permanent staff/hardware ownership, physical Mother Base visitation, base management, dispatch operations, FOB infiltration, live PvP and a persistent social command network. v0.3.4 is a focused progression repair over the v0.3.3 public baseline: the standard Fulton Recovery system can now be manufactured at R&D Level 1, preventing commanders from exhausting finite starter stock and becoming unable to recover the staff needed to advance R&D.



## v0.3.4 polish: sustainable level-1 Fulton recovery

- The standard **Fulton Recovery Pack** is now present in the R&D manufacturing catalog at **R&D Level 1**, so it is unlocked by default for every commander.
- The recipe produces **4 Fulton Recovery units** for **60 Common Metal + 40 Fuel**. This preserves a resource cost while guaranteeing a renewable early-game personnel-recovery path.
- This resolves the progression trap where a commander could consume all starter/contract Fulton stock while still at R&D 1 and then have no way to recover additional staff to improve the R&D Team.
- Higher technology remains unchanged: **Fulton+ = R&D 4**, **Cargo Fulton = R&D 8**, and **Wormhole Fulton = R&D 15**. Their existing costs, yields, recovery bonuses and eligible target classes are preserved.
- Schema remains **4**. Existing inventories, characters, staff, bases, resources and multiplayer state require no reset or conversion.

## v0.3.3 polish: real operative variety inside every warzone

- The v0.3.2 global modulo mapping was correlated with the six-way warzone seeder and could therefore produce one operative skin per map. v0.3.3 removes that correlation.
- Fresh populations rotate the six player operatives **within each warzone**, not merely across the global 1,000-bot population.
- Update / Repair also fixes already-persisted bot populations in-place by ordering bots inside each current warzone by durable `bot_index` and cycling Marco, Tarma, Eri, Fio, Nadia and Trevor independently for that map.
- Every 166–167-bot warzone should contain all six skins, normally about **27–28 of each operative per map**.
- Only `users.character_key` for autonomous commanders is reconciled. Bot IDs/usernames, XP, positions, captured personnel/vehicles, resources, Mother Bases, dispatch state, FOB history and PvP records remain preserved.
- The assignment is deterministic and idempotent for the current six-warzone population; repeated Update / Repair does not create bots or reset progression.
- Confirm Installation now checks per-warzone skin diversity and fails if a map is still a clone army.
- Schema remains **4** and the compact v0.3.1 `AI` hover labels are preserved.

## v0.3.1 polish: compact autonomous commander map labels

- Autonomous commanders now render as a compact cyan **AI** tag at rest instead of drawing the full callsign / `AI COMMANDER` / grade string for every visible bot.
- Hovering either an autonomous commander's operative sprite or its **AI** tag reveals that commander's complete callsign, AI COMMANDER marker and grade.
- Keyboard focus on the AI profile link exposes the same full identity, and the full text remains available through accessible label metadata.
- The keyed 3-second presence updater refreshes each bot's hidden full identity/activity in place without rebuilding the map entity or forcing the expanded label open.
- Human player labels are unchanged. Bot population, simulation, collision, profiles, PvP, FOB, Mother Base and persistence behavior are unchanged.
- Schema remains **4** and no database migration is required beyond the usual idempotent **Update / Repair** verification.

## v0.3.0 major feature: 1,000 persistent autonomous commanders

- Exactly **1,000** persistent AI commander identities are seeded idempotently at schema revision 4 and remain database-backed across browser/Apache/MySQL restarts.
- `bot_index` 1–1000 is authoritative. Default names are `WarzoneAI0001`–`WarzoneAI1000`; a pre-existing human username collision is preserved and receives a deterministic alternate AI presentation name instead of blocking migration.
- Bots use the same six selectable Metal Slug operative skins as player commanders; they never use enemy sprites as their commander identity and cannot log in through the human authentication flow.
- Fresh populations are balanced across all six warzones (167/167/167/167/166/166) and start on distinct legal collision-safe positions sampled across each native map.
- Bots move using the accepted 18-pixel server-authoritative movement/collision path, resolve field contacts, gain Commander XP/resources, use Fulton recovery, own permanent soldiers/vehicles, staff Mother Base sectors and increase Base Power.
- Bot simulation is bounded, leased and timestamp-driven. Presence refreshes advance small due batches instead of creating one PHP worker per bot; opening additional browsers cannot make an individual bot act faster than its persisted `next_action_at`.
- Mature bots develop R&D legitimately and can unlock Cargo Fulton, allowing actual vehicle-class recoveries without bypassing player progression restrictions.
- Autonomous Combat Unit dispatches use the same persistent `dispatch_missions` ledger and MySQL completion timestamps as player dispatches; pending missions survive browser/Apache/MySQL restarts and settle rewards/unit XP once.
- Human commanders can invade bot FOBs through the existing transactional snapshot/resource-transfer system. Bots also raid other bots, so their resource stocks and FOB histories evolve autonomously without silently draining offline human players.
- Bot profiles support **Live AI PvP** and **Commander Snapshot Battle**. AI turns are committed server-side without a hidden bot session, while bot-v-bot PvP activity is also persisted in the shared match ledger.
- Added an **AI Network** page with exact population/map distribution and recent autonomous activity. AI commanders also appear in rankings, FOB targets and normal mandatory map presence.
- Warzone presence rendering now updates keyed remote entities in place, avoiding full DOM teardown/recreation with ~166+ AI commanders visible on a map.

## v0.2.0 major feature: physical Mother Base / FOB spaces

- Seven user-supplied Mother Base maps are available: four land bases and three sea FOBs.
- A Mother Base is selected during account creation and can be changed later from **Account Options** without deleting progression, staff, vehicles, resources, sector levels, dispatch state, FOB history or Commander XP.
- The owner's selected base is a native-size 1:1 map inside the same close-up auto-scrolling viewport discipline used by the accepted v0.1.4 warzones.
- The player moves in four directions with WASD/Arrow keys or the external command pad.
- Base movement is server-authoritative and collision-blocked against buildings, walls, machinery, perimeter structures, cliffs/platform edges and ocean boundaries.
- Captured personnel and hardware are projected directly from the persistent `units` roster into the physical base.
- Human staff receive persistent server positions and roam slowly around local anchor areas. Their movement is advanced from database timestamps, not browser animation authority.
- Vehicles/air-class hardware remain parked and do not roam.
- Presence polling updates staff and authorized visitors without re-entering/reloading the level. Existing NPC DOM entities move with deliberate transitions rather than being recreated every tick.
- Established friends and members of the same Strike Force may enter one another's bases. Access is checked server-side on page entry, movement requests and every live presence refresh.
- Entering a physical Mother Base clears warzone presence so one account cannot appear in two physical spaces simultaneously.

## Preserved production foundation

- Selectable Metal Slug operative and persistent Commander Level/XP progression.
- Six accepted v3 overhead warzones at exact native resolution with fixed close-up camera, four-way movement and server collision.
- Mandatory multiplayer warzone presence.
- Versioned turn-based combat, damage/type effectiveness and HP-dependent Fulton recovery.
- Persistent recovered soldiers and vehicles with levels, XP, aptitude stats and E-- through S++ grades.
- Seven Mother Base management sectors and derived Base Power.
- R&D and progression-gated Fulton systems.
- Missions, Field Contracts, Rival Commanders and Boss Operations.
- Persistent Combat Unit dispatch timers.
- Persistent strategic/nuclear-deterrence production timers.
- Transactional FOB resource theft with attacker cooldown and defender protection.
- Versioned live PvP with no permanent unit death.
- Friends, direct messages, Strike Forces and rankings.
- Loopback-only `_setup.php` with Fresh Install, Update / Repair and Confirm Installation.

## Requirements

- XAMPP or equivalent Apache + PHP + MySQL/MariaDB stack.
- PHP 8.1+; release syntax is audited with PHP 8.4.
- MySQL/MariaDB with InnoDB and `utf8mb4`.
- PHP `mysqli` extension.

## Updating from v0.3.3 / earlier schema-4 test data

1. Back up the current database, then replace the runtime project files with v0.3.4.
2. On the server PC open `_setup.php` via `127.0.0.1` / `localhost`.
3. Select **Update / Repair**. Do **not** Fresh Install. Schema remains revision 4 and the repair is non-destructive.
4. Select **Confirm Installation** and verify schema revision **4**, **1,000 persistent autonomous commanders**, the six-warzone population distribution, and `autonomous_skin_variety` reports **every warzone mixed**.
5. Open **R&D Laboratory** on an R&D Level 1 commander and verify **Fulton Recovery Pack** is immediately manufacturable while Fulton+, Cargo Fulton and Wormhole Fulton remain locked until levels 4/8/15.
6. Manufacture one basic batch and confirm 60 Common Metal + 40 Fuel are spent and inventory increases by 4.
7. Complete the focused runtime checks in `docs/XAMPP_TEST_PLAN.md`.

## Runtime-only asset policy

Release packages contain only runtime assets needed by the game. The accepted warzone/Mother Base PNGs remain runtime files; source/replacement ZIPs and development libraries are **not** included in the distributable. v0.3.4 introduces no new art assets; all existing accepted runtime images remain unchanged. No generated images are included.

See `docs/ARCHITECTURE.md`, `docs/SECURITY.md`, `docs/ASSET_MANIFEST.md`, `docs/BUILD_VALIDATION.md`, and `docs/XAMPP_TEST_PLAN.md`.
