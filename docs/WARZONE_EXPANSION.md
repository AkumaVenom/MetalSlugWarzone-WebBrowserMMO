# Expanded warzones — v0.8.5.1

All eleven v4 PNGs are integrated at **1672 × 941**, without resizing or modifying
the playable source image. Warzone Select now lists 17 maps in threat order.
The six original keys, dimensions, spawns, encounter pools and collision profiles
are unchanged. The following additions follow Iron Citadel Interior (Threat 12):

| Threat | Warzone | Minimum / readiness | Enemy range at Commander Lv20 |
| ---: | --- | ---: | --- |
| 13 | Swamp Encampment | Lv20 | Lv24–30 |
| 14 | Abandoned Carnival | Lv25 | Lv26–32 |
| 15 | Neon District | Lv30 | Lv30–36 |
| 16 | Scrapyard Depot | Lv35 | Lv35–41 |
| 17 | Canyon Missile Base | Lv40 | Lv40–46 |
| 18 | Alpine Radar Station | Lv45 | Lv45–51 |
| 19 | Offshore Platform | Lv50 | Lv50–56 |
| 20 | Subterranean Terminus | Lv55 | Lv55–61 |
| 21 | Volcanic Forge | Lv60 | Lv60–66 |
| 22 | Containment Laboratory | Lv65 | Lv65–71 |
| 23 | Lunar Outpost | Lv70 | Lv70–76 |

Readiness is a recommendation, not an entry lock. Each expansion map now has a
minimum enemy level, plus stronger player-relative scaling above that minimum.
The same maturity-dependent range width remains: Lv1–5 Commanders get a two-level
spread, Lv6–9 three, Lv10–14 four, Lv15–19 five, and Lv20+ six. The entire window
shifts upward together, so minimums do not collapse all rolls to one level.

At Commander Lv5, Swamp Encampment yields Lv20–22 and Lunar Outpost yields Lv70–72.
At Commander Lv20 they yield Lv24–30 and Lv70–76. At Commander Lv99 they yield
Lv103–109 and Lv123–129. Higher encounter levels also increase derived HP/ATK/DEF/SPD.
The existing Fulton system retains the actual enemy's level and stats on capture.

## Combat progression

Threat 1–12 formulas remain unchanged. Above 12, let `extra = clamp(threat - 12, 0, 11)`:

- Map minimum becomes `20 + 5 × (extra - 1)` for expansion threats.
- Relative upper offset becomes `8 + 2 × extra` (+10 through +30).
  Start with `player level + upper offset - maturity spread`; raise that minimum
  to the map minimum if needed, then add the same spread to obtain the maximum.
- Readiness equals the map minimum (Lv20–70); existing underlevel multiplier caps stay.
- Add `0.060 × extra` HP, `0.050 × extra` ATK, `0.025 × extra` DEF and
  `0.007 × extra` SPD to the original Threat 12 factors before enemy-level and
  underlevel factors. Threat 23 factors are 2.14 / 1.95 / 1.495 / 1.157 respectively.
- Roll bias retains its 0.93 ceiling. Counter accuracy adds at most 2 points;
  counter move power adds at most 4 and retains a 31 ceiling. Intel/SPD reductions,
  player accuracy and the single application of enemy ATK remain intact.
- Boss curves and existing mission/sidequest/trainer definitions remain unchanged.
  The existing resource/XP settlement and recovery-item requirements also remain.

The v5 encounter marker is deliberately retained: existing normal and boss
encounters already contain their committed roll, threat, HP and combat stats.
Existing battles retain their committed level, roll, HP and stats. Their stored
readiness gap also governs counter accuracy, preventing a mid-battle difficulty
change. New levels apply to new encounters; finish or retreat from any active fight.

## Persistent AI deployment

Update / Repair assigns the same 1,000 bot identities round-robin over all 17 maps:
first 14 maps receive 59 each and last 3 receive 58 each. It samples each map's legal
positions across the authored area and balances all six operative skins locally
and globally. `schema_meta.warzone_expansion_v085` records successful placement.
The marker and placement commit together inside bot seeding under setup's existing
world-maintenance lock. Repeat repair retains patrol positions.

This only changes warzone placement and skin distribution. Player positions,
levels, XP, staff, captures, resources, activity/career statistics, due schedules,
leases, dispatches, Mother Bases and permanent FOB memberships are preserved.

Bots patrol their assigned warzone as before. Their live field action uses the
local enemy pool. Original-map contact levels remain `threat - 1` through
`threat + 2`; expansion contacts use the shared map minimum through minimum+3,
with the existing bounded win chance, XP and Fulton recovery paths.
Development and elapsed-time catch-up also use the local pool; on new maps their
recruit level is at least the same local contact roll before the existing career
boost and staff Lv 99 cap. Established full rosters continue training rather than
bypassing their caps. World updates remain global, serialized and bounded, so the
new maps continue progressing when every player is offline.

## Terrain and presentation

Every new map has native pixel bounds and named rectangular terrain blockers.
Spawns occupy connected open ground. Walls, large cargo, rocks, marsh edges,
platform edges and buildings block movement; painted surfaces and floor grates do
not. Collision remains authoritative in both runtime movement and setup seeding.
The battlefield camera and native-size presentation use the existing map renderer.

Selection previews are separately derived JPEGs for all 17 maps, totaling about
803 KiB. The eleven new native PNGs total about 36.4 MiB and load only when deployed.
The original 67 images and all 11 uploaded source PNGs remain byte-identical.

See `BUILD_VALIDATION.md` for executable checks and `XAMPP_TEST_PLAN.md` for the
remaining real-host acceptance steps.
