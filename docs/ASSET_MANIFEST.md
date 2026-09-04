# Runtime Asset Manifest — v0.3.5

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
