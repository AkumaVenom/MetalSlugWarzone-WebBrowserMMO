# XAMPP Runtime Acceptance Plan — v0.3.4 Level-1 Fulton Manufacturing

v0.3.4 is a focused progression repair over the **v0.3.3 Per-Warzone AI Operative Variety** public baseline. It makes only the standard Fulton recovery recipe renewable at R&D Level 1. Schema remains revision **4**.

## 1. Backup and Update / Repair — RELEASE BLOCKING

1. Back up the current database.
2. Replace the current runtime files with v0.3.4.
3. Open `_setup.php` locally and run **Update / Repair**. Do **not** Fresh Install.
4. Run **Confirm Installation** and verify schema revision **4**, exactly **1,000 persistent autonomous commanders**, balanced six-warzone population, and `autonomous_skin_variety` reports **OK · every warzone mixed**.
5. Confirm no human or bot XP, coordinates, rosters, Mother Bases, resources, dispatches, FOB history or PvP records are reset.

## 2. Level-1 Fulton manufacturing — RELEASE BLOCKING

1. Use a commander whose R&D Team is **Level 1** and open **Mother Base → R&D Laboratory**.
2. Confirm **Fulton Recovery Pack** is visible and its button is enabled at R&D 1.
3. Confirm the card displays **60 Common Metal + 40 Fuel** and **Manufacture x4**.
4. Confirm **Fulton+ Balloon Pack** is still locked below R&D 4, **Cargo Fulton Pack** below R&D 8, and **Wormhole Fulton** below R&D 15.
5. Record Common Metal, Fuel and basic Fulton inventory; manufacture one batch. Confirm exactly 60 Common Metal and 40 Fuel are removed and exactly 4 `fulton` units are added.
6. Spend/reduce basic Fulton stock in normal recoverable combat, then manufacture again. Confirm the commander cannot become permanently stranded merely because starter Fulton inventory has been exhausted.
7. Attempt normal infantry recovery with the manufactured basic Fulton and verify the battle engine still consumes one item and applies the existing R&D 1/personnel-only rules.
8. Attempt to use basic Fulton against a vehicle contact and verify it remains ineligible; Cargo Fulton remains the R&D 8 vehicle path.

## 3. Preserved v0.3.3 per-warzone operative variety — RELEASE BLOCKING

1. Enter each of the six warzones and confirm Marco, Tarma, Eri, Fio, Nadia and Trevor coexist among the AI population.
2. Each 166–167-bot map should still contain roughly **27–28 of each accepted operative skin**.
3. Leave a map open through repeated presence refreshes and confirm each bot keeps its assigned skin while moving.
4. Hover/focus several compact **AI** tags and confirm the accepted full-identity behavior remains intact.

## 4. Progression/state preservation — RELEASE BLOCKING

1. Verify existing inventories retain their prior Fulton quantities after update; v0.3.4 adds a recipe and does not rewrite inventory.
2. Confirm missions, Field Contracts, staff assignment/R&D recalculation, dispatches, FOB raids, Snapshot battles and Live AI PvP still function normally.
3. Restart Apache/MySQL and verify manufactured Fulton inventory and all existing progression persist.

## 5. Runtime-only packaging

Confirm the extracted candidate contains no `source_assets/` directory and no nested/source `.zip` archives. Runtime images must remain unchanged.

## Acceptance

Promote v0.3.4 only after real XAMPP/browser testing confirms **basic Fulton is renewable at R&D Level 1**, **higher Fulton tiers remain locked at 4/8/15**, and the accepted v0.3.3 gameplay/persistence/AI-variety behavior remains intact.
