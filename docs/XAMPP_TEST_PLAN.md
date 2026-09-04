# XAMPP Runtime Acceptance Plan — v0.3.3 Per-Warzone AI Operative Variety

v0.3.3 is a focused population-presentation repair over the **runtime-accepted v0.3.1 Compact AI Warzone Labels** baseline. It supersedes the rejected v0.3.2 global-only skin mapping. Schema remains revision **4**.

## 1. Backup and Update / Repair — RELEASE BLOCKING

1. Back up the current database.
2. Replace the current runtime files with v0.3.3.
3. Open `_setup.php` locally and run **Update / Repair**. Do **not** Fresh Install.
4. Run **Confirm Installation** and verify schema revision **4**, exactly **1,000 persistent autonomous commanders**, balanced six-warzone population, and `autonomous_skin_variety` reports **OK · every warzone mixed**.
5. Confirm no human or bot XP, coordinates, rosters, Mother Bases, resources, dispatches, FOB history or PvP records are reset.

## 2. Per-warzone operative variety — RELEASE BLOCKING

1. Enter **Jungle Front** and confirm Marco, Tarma, Eri, Fio, Nadia and Trevor all appear together among the AI population.
2. Repeat for Industrial Railhead, Ruined Temple, Occupied City, Coastal Breach and Iron Citadel Interior.
3. Each 166–167-bot map should contain roughly **27–28 of each of the six operative skins**. No map may be a single-skin clone army.
4. Leave a map open through repeated presence refreshes. Each bot must keep its assigned skin while moving.
5. Hover several compact **AI** tags and confirm the v0.3.1 full-identity hover/focus behavior remains unchanged.
6. Open several AI profiles and verify the profile operative matches the sprite shown for that commander in warzone presence.

## 3. Progression/state preservation — RELEASE BLOCKING

1. Before Update / Repair, record several bot IDs/indexes, XP, resources, coordinates and owned-unit counts.
2. After repair, verify those values are unchanged except for `character_key` where required by the per-map variety repair.
3. Confirm field captures, persistent dispatches, FOB raids, Snapshot battles and Live AI PvP still function normally.

## 4. Idempotence / restart persistence — RELEASE BLOCKING

1. Record several bot indexes from different maps and their new operative skins.
2. Run Update / Repair a second time; verify the same map-local assignments remain stable and no bots are duplicated.
3. Restart Apache/MySQL and verify the same assignments and all progression persist.

## 5. Runtime-only packaging

Confirm the extracted candidate contains no `source_assets/` directory and no nested/source `.zip` archives. All runtime images must remain unchanged from accepted v0.3.1.

## Acceptance

Promote v0.3.3 only after real XAMPP/browser testing confirms **all six player-operative skins coexist inside every individual warzone** while every accepted v0.3.1 gameplay/persistence system remains intact.
