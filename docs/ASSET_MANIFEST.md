# Runtime Asset Manifest — v0.4.1

The XAMPP package is runtime-only. It contains no `source_assets/` tree and no supplied source ZIP archives.

## Mother Base / FOB maps

Copied byte-for-byte from the user-supplied development input and installed only as runtime PNGs:

| Runtime file | Native size |
| --- | ---: |
| `assets/maps/motherbase/MotherBase_Land_Desert.png` | 1672×941 |
| `assets/maps/motherbase/MotherBase_Land_Dirt.png` | 1672×941 |
| `assets/maps/motherbase/MotherBase_Land_Forest.png` | 1672×941 |
| `assets/maps/motherbase/MotherBase_Land_Snow.png` | 1672×941 |
| `assets/maps/motherbase/MotherBase_Sea_FOB1.png` | 1672×941 |
| `assets/maps/motherbase/MotherBase_Sea_FOB2.png` | 1672×941 |
| `assets/maps/motherbase/MotherBase_Sea_FOB3.png` | 1774×887 |

The runtime never stretches these maps to page width; they remain 1:1 native pixels inside a bounded scrolling camera.

## Existing accepted runtime art

The accepted v0.1.4 warzone maps, polished selectable operatives, active field contacts and boss runtime art remain unchanged. Rebel Biker remains the active vehicle visual fallback for retired legacy vehicle source keys.

No generated art is included.


## v0.3.0 autonomous commander asset policy

v0.3.0 adds no new artwork. Autonomous commanders use the existing accepted six player-operative runtime sprites and the same warzone/Mother Base/contact/boss runtime assets already validated in v0.2.0. No bot-specific generated art, enemy-skin commander art, source libraries or development ZIPs are included.


## v0.3.1 asset delta

No runtime image, map or sprite asset changes. This release changes only AI nameplate presentation plus version/documentation metadata.


## v0.3.3 asset delta

No binary artwork changed. The patch only redistributes existing autonomous commander `character_key` values across the six already accepted player-operative sprite sets. All runtime image bytes remain identical to v0.3.1.


## v0.3.4 asset delta

No binary artwork changed. The release adds only the level-1 basic Fulton manufacturing recipe plus version/documentation metadata; all existing runtime image bytes are preserved.


## v0.3.5 asset delta

No image, sprite or map binary changed. The release adds only PHP/Batch/PowerShell console logic plus documentation/version metadata; all accepted runtime artwork remains byte-identical to v0.3.4.


## v0.4.1 asset status

v0.4.1 changes globe hotspot metadata and FOB placement geometry only. **No image asset is added, regenerated, resized or modified** relative to v0.4.0.

## v0.4.0 global FOB overview assets

Copied from the user-supplied overview-world development input and installed only as runtime PNGs:

| Runtime file | Native size | Purpose |
| --- | ---: | --- |
| `assets/maps/fob_world/WorldGlobe_FullOverview.png` | 1254×1254 | Global biome-selection screen |
| `assets/maps/fob_world/Forest_FOB_Overview_Map.png` | 2000×2000 | Forest overview shards |
| `assets/maps/fob_world/Dirt_FOB_Overview_Map.png` | 2000×2000 | Continental overview shards |
| `assets/maps/fob_world/Desert_FOB_Overview_Map.png` | 2000×2000 | Desert overview shards |
| `assets/maps/fob_world/Arctic_FOB_Overview_Map.png` | 2000×2000 | Arctic overview shards |
| `assets/maps/fob_world/Sea_FOB_Overview_Map.png` | 2000×2000 | Sea overview shards |
| `assets/sprites/fob/Forest Mother Base.png` | 256×171 | Forest FOB marker |
| `assets/sprites/fob/Continental Mother Base.png` | 256×171 | Continental FOB marker |
| `assets/sprites/fob/Desert Mother Base.png` | 256×171 | Desert FOB marker |
| `assets/sprites/fob/Arctic Mother Base.png` | 256×171 | Arctic FOB marker |
| `assets/sprites/fob/Offshore Mother Base Alpha.png` | 256×171 | Sea FOB marker |
| `assets/sprites/fob/Offshore Mother Base Bravo.png` | 256×171 | Sea FOB marker |
| `assets/sprites/fob/Maritime Fortress Mother Base.png` | 256×171 | Sea FOB marker |

The development asset ZIP is not included in the runtime package. Overview maps remain native-size assets inside a scroll viewport; icon files are rendered down for world-marker presentation without modifying the supplied binary source.
