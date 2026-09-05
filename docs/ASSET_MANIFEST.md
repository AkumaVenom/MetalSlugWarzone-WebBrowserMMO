# Runtime Asset Manifest — v0.6.0

The XAMPP package is runtime-only. It contains no `source_assets/` tree and no nested development/source ZIP archives.

## v0.6.0 asset delta

The visual-polish continuation of v0.6.0 adds **23 user-supplied JPG artwork files** under `public_html/assets/artwork/`. They are copied byte-for-byte from `NewAssetsForMetalSlugWarzone.zip`: no resize, crop, recompression, paint-over or generated replacement is performed in the package.

| Supplied runtime artwork | Native size | Integrated system |
| --- | ---: | --- |
| `MAINHEADER.jpg` | 1983×793 | Public landing/auth command-network presentation |
| `CommandCentreHeader.jpg` | 2172×724 | Dashboard and global FOB command surfaces |
| `CommandCentre.jpg` | 1408×768 | Command-centre ambient scene layer |
| `ExtaCommanderScanner.jpg` | 1254×1254 | FOB/AI command intelligence ambient scene |
| `SelectWarzoneHeader.jpg` | 1945×809 | Warzone selection / field deployment |
| `SelectWarzone.jpg` | 1254×1254 | Warzone ambient scene |
| `CombatMissionsHeader.jpg` | 1944×809 | Combat Missions / Side Operations |
| `CombatMissions.jpg` | 1254×1254 | Combat mission ambient scene |
| `BOSSOPERATIONSHeader.jpg` | 1944×809 | Boss Operations |
| `BOSSOPERATIONS.jpg` | 1254×1254 | Boss-operation ambient scene |
| `DispatchHeader.jpg` | 2153×730 | Dispatch / staff invasion dispatch |
| `Dispatch.jpg` | 1254×1254 | Dispatch ambient scene |
| `R&DHeader.jpg` | 1942×809 | Research & Development |
| `R&D.jpg` | 1254×1254 | R&D ambient scene |
| `NUCLEAEDETERRENCEHEADER.jpg` | 1942×809 | Strategic / deterrence systems |
| `NUCLEAEDETERRENCE.jpg` | 1254×1254 | Strategic ambient scene |
| `RankingHeader.jpg` | 1983×793 | Commander rankings |
| `Ranking.jpg` | 1254×1254 | Ranking ambient scene |
| `SlugHangerHeader.jpg` | 1774×887 | Mother Base / staff / hangar surfaces |
| `SlugHanger.jpg` | 1254×1254 | Mother Base ambient scene |
| `MessHallHeader.jpg` | 2172×724 | Community / social / Strike Force surfaces |
| `MessHall.jpg` | 1254×1254 | Social ambient scene |
| `AICOMMANDERS.jpg` | 1983×793 | AI Commanders / rival commanders / PvP command surfaces |

Every supplied image is referenced by the production stylesheet. Existing accepted maps and sprites remain untouched.

## Mother Base / FOB maps

The accepted runtime Mother Base images remain at native resolution:

| Runtime file | Native size |
| --- | ---: |
| `assets/maps/motherbase/MotherBase_Land_Desert.png` | 1672×941 |
| `assets/maps/motherbase/MotherBase_Land_Dirt.png` | 1672×941 |
| `assets/maps/motherbase/MotherBase_Land_Forest.png` | 1672×941 |
| `assets/maps/motherbase/MotherBase_Land_Snow.png` | 1672×941 |
| `assets/maps/motherbase/MotherBase_Sea_FOB1.png` | 1672×941 |
| `assets/maps/motherbase/MotherBase_Sea_FOB2.png` | 1672×941 |
| `assets/maps/motherbase/MotherBase_Sea_FOB3.png` | 1774×887 |

They remain 1:1 native pixels inside the bounded scrolling camera.

## Global FOB overview assets

| Runtime file | Native size | Purpose |
| --- | ---: | --- |
| `assets/maps/fob_world/WorldGlobe_FullOverview.png` | 1254×1254 | Initial deployment and v0.5 global invasion continent selector |
| `assets/maps/fob_world/Forest_FOB_Overview_Map.png` | 2000×2000 | Forest shards |
| `assets/maps/fob_world/Dirt_FOB_Overview_Map.png` | 2000×2000 | Continental shards |
| `assets/maps/fob_world/Desert_FOB_Overview_Map.png` | 2000×2000 | Desert shards |
| `assets/maps/fob_world/Arctic_FOB_Overview_Map.png` | 2000×2000 | Arctic shards |
| `assets/maps/fob_world/Sea_FOB_Overview_Map.png` | 2000×2000 | Sea shards |
| `assets/sprites/fob/Forest Mother Base.png` | 256×171 | Forest FOB marker |
| `assets/sprites/fob/Continental Mother Base.png` | 256×171 | Continental FOB marker |
| `assets/sprites/fob/Desert Mother Base.png` | 256×171 | Desert FOB marker |
| `assets/sprites/fob/Arctic Mother Base.png` | 256×171 | Arctic FOB marker |
| `assets/sprites/fob/Offshore Mother Base Alpha.png` | 256×171 | Sea FOB marker |
| `assets/sprites/fob/Offshore Mother Base Bravo.png` | 256×171 | Sea FOB marker |
| `assets/sprites/fob/Maritime Fortress Mother Base.png` | 256×171 | Sea FOB marker |

The globe continues to be reused in v0.6.0 for actual world selection. The new Command Centre JPGs are presentation-layer command artwork only and do not replace or duplicate the authoritative globe/overview map assets.

## Existing accepted runtime art

The accepted native warzone maps, six selectable player operatives, field enemies, bosses and Rebel Biker vehicle fallback remain unchanged. Autonomous commanders continue to reuse the accepted player-operative sprite catalog.

No generated art is included. The 23 new artwork files are user-supplied runtime assets and are preserved byte-for-byte.
