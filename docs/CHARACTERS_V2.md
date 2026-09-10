# Characters V2 — v0.8.7

This release integrates all **16 PNGs** in the supplied `Characters v2.zip` into
the existing enemy, combat and operation catalogs. There are **14 new recoverable
units** (four personnel, six ground vehicles and four aircraft) and **two new
non-capturable bosses**. The six playable Commander avatars remain the established
character selection; V2 units are enemies and recoverable staff/hardware.

## Complete character and recovery mapping

| Character | Catalog key | Class | Recovery requirement |
| --- | --- | --- | --- |
| Arab Soldier | `arab_soldier` | Infantry | Fulton Recovery (R&D Lv1) |
| Japanese Soldier | `japanese_soldier` | Infantry | Fulton Recovery (R&D Lv1) |
| General T | `general_t` | Heavy infantry | Fulton Recovery (R&D Lv1) |
| Alien | `alien` | Infantry | Fulton Recovery (R&D Lv1) |
| Rebel Gear | `rebel_gear` | Ground vehicle | Cargo Fulton (R&D Lv5) or Wormhole |
| Rebel Slug | `rebel_slug` | Ground vehicle | Cargo Fulton (R&D Lv5) or Wormhole |
| Rebel Tank | `rebel_tank` | Ground vehicle | Cargo Fulton (R&D Lv5) or Wormhole |
| Rebel S.A.M. | `rebel_sam` | Ground vehicle | Cargo Fulton (R&D Lv5) or Wormhole |
| Japanese Tank | `japanese_tank` | Ground vehicle | Cargo Fulton (R&D Lv5) or Wormhole |
| Alien Walker | `alien_walker` | Ground vehicle | Cargo Fulton (R&D Lv5) or Wormhole |
| Rebel Helicopter | `rebel_helicopter` | Aircraft | Wormhole Fulton (R&D Lv8) |
| Rebel Bomber | `rebel_bomber` | Aircraft | Wormhole Fulton (R&D Lv8) |
| Japanese Hi Fighter | `japanese_hi_fighter` | Aircraft | Wormhole Fulton (R&D Lv8) |
| Mini-UFO | `mini_ufo` | Aircraft | Wormhole Fulton (R&D Lv8) |
| Hi-Do | `hi_do` | Boss | Unavailable — non-capturable boss |
| UFO Boss | `ufo_boss` | Boss | Unavailable — non-capturable boss |

The table gives the minimum compatible recovery system. Personnel can also use
Fulton+ at R&D Lv4, Cargo at Lv5 or Wormhole at Lv8. Cargo-compatible ground
vehicles can use Wormhole too; aircraft require Wormhole. The player must meet the
R&D threshold and carry the selected item. Existing damage, support and recovery
chance rules remain in force; eligibility does not guarantee a successful capture.

A successful player recovery creates the permanent owned unit with the captured
enemy's source key, class, affinity, level and combat stats. It uses the existing
staff/hardware roster, team, training and Mother Base/hangar systems. It grants
the established recovery XP, and does not also award a mission victory or clear.

## Expansion-map placement

The following table lists **V2 contacts only** in each revised local pool; familiar
enemies also appear alongside them. Minimum levels are the existing map floors, with the normal
Commander-relative scaling above those floors. Bosses are selected through their
operations and are excluded from random field encounter pools.

| Warzone | Minimum enemy level | New field encounters |
| --- | --- | --- |
| Swamp Encampment | Lv20 | Japanese Soldier, Rebel Slug, Rebel Helicopter |
| Abandoned Carnival | Lv25 | Arab Soldier, Rebel Gear, Rebel Bomber |
| Neon District | Lv30 | Japanese Soldier, General T, Rebel Helicopter |
| Scrapyard Depot | Lv35 | Rebel Gear, Rebel Slug, Rebel Tank |
| Canyon Missile Base | Lv40 | Arab Soldier, Rebel S.A.M., Rebel Tank |
| Alpine Radar Station | Lv45 | Japanese Soldier, Japanese Tank, Japanese Hi Fighter, Rebel S.A.M. |
| Offshore Platform | Lv50 | General T, Rebel S.A.M., Rebel Helicopter, Rebel Bomber |
| Subterranean Terminus | Lv55 | Japanese Soldier, Rebel Gear, Japanese Tank, Alien |
| Volcanic Forge | Lv60 | General T, Rebel Slug, Rebel Tank, Alien Walker |
| Containment Laboratory | Lv65 | Alien, Alien Walker, Mini-UFO, General T |
| Lunar Outpost | Lv70 | Alien, Alien Walker, Mini-UFO, Japanese Hi Fighter |

**Lunar Outpost is the lunar base:** Alien, Alien Walker and Mini-UFO are all in
its random encounter pool. The UFO Boss operation is assigned there, and the four
new alien-targeted Combat Missions and Dispatch assignments all use Lunar Outpost.
Alien contacts also appear in the themed underground/containment environments
listed above. Existing six-map pools, native map geometry and movement are retained.

## Boss Operations

| Boss | Operation theatre | Threat step / rank | Recoverable |
| --- | --- | --- | --- |
| Hi-Do | Offshore Platform | 19 / S++ | No |
| UFO Boss | Lunar Outpost | 23 / S++ | No |

Hi-Do and UFO Boss join Huge Hermit and Rootmars on the existing Boss Operations
page. Their definitions use dedicated boss HP/attack/defense/speed, the established
boss encounter profile and server-authoritative battle settlement. All Fulton
systems reject boss recovery before creating a recruit.

A win entered from **Boss Operations** retains the existing boss reward: **4,500
GMP, 900 common metal, 500 minor metal, 140 precious metal, 700 fuel and 650 Commander
XP**. The completed encounter is persisted. Boss Operations do not introduce a new
mission-clear counter. The separate **Blackwater Hi-Do Strike** and **Lunar UFO
Assault** Combat Missions use their own authored mission rewards/XP and mission
clear tracking while retaining boss combat and non-capture rules.

## Player missions and staff dispatch

There are **16 new Combat Missions and 16 new Dispatch assignments**, one pair for
every V2 enemy, including both bosses. Each catalog now contains **31 entries**.
The existing first 15 definitions and their order are retained. Both mission menus
keep text-only cards; Dispatch keeps its per-card staff selection and history table.
Exact targets, locations and balance values are in
[TACTICAL_OPERATIONS.md](TACTICAL_OPERATIONS.md).

Staff Dispatch assignments retain their existing resource/staff-XP outcomes.
Their replays include the authored V2 target and escorts, but do not grant captured
enemy units. Replay does not reroll or award anything after settlement.

## Autonomous AI commanders

Field contacts use each commander's local map pool, so the new definitions feed
existing AI battles and capture attempts. Live recovery selects equipment from
the same class compatibility catalog: standard Fulton for personnel, Cargo for
ground vehicles and Wormhole for aircraft. Aircraft restocking uses the existing
Wormhole recipe: one item for **420 minor metal, 120 precious metal and 350 fuel**,
unlocked at R&D Lv8.

Background development and elapsed catch-up also draw from local recruitable
pools, with an aircraft R&D Lv8 candidate filter and final recruitment guard.
These paths retain their established aggregate supply model; they do not become
per-item live recovery attempts. Existing ground recruitment, career boosts,
training, staff caps and scheduler budgets are retained. Boss definitions remain
excluded from permanent recruitment. Qualified AI staff squads can select the new
Dispatch assignments through the existing strength and reservation checks.

Existing AI commanders are retained, not reseeded or given an instant replacement
roster. Already full rosters continue their established training behavior. New
eligible content appears through normal subsequent world progression.

## Assets, persistence and acceptance

All 16 supplied PNGs are copied without resizing or recompression, with readable
runtime filenames. Exact source-to-runtime mappings and native sizes are in
[ASSET_MANIFEST.md](ASSET_MANIFEST.md). Existing artwork and maps remain in the
complete release. No generated replacements are used.

Schema remains **9**. Existing accounts, worlds, staff, active fights, pending
deployments, AI identities and their earned progress stay in the existing database.
New content applies to subsequent encounters/operations; committed fights retain
their saved state. See [../UPGRADE_v0.8.7.md](../UPGRADE_v0.8.7.md),
[XAMPP_TEST_PLAN.md](XAMPP_TEST_PLAN.md) and the executed-check record in
[BUILD_VALIDATION.md](BUILD_VALIDATION.md).
