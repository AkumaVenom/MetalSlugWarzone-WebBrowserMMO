# Tactical Operations — v0.8.7

## v0.8.7 — Sixteen V2 operation pairs

**Combat Missions and Staff Dispatch each contain 31 entries.** The existing 15
entries remain first with the same keys, names, ordering, rewards and controls;
16 new target-specific assignments are appended to each catalog. Together they
cover all 16 supplied V2 enemy types, including Hi-Do and UFO Boss.

Both pages keep **text-only mission cards**. Combat Missions remain player
battles. Dispatch retains **Available Dispatches**, one staff-selection form and
Dispatch button per assignment, and the existing **Dispatch History** table.
No mission-card images, assignment dropdowns or additional review screen are added.

All four new alien-targeted operation pairs use **Lunar Outpost**. The other pairs
follow the new enemy's authored expansion theatre. Mission entry does not move a
player's persistent field position. The targets below are explicit, so selecting
a mission reaches that V2 enemy without waiting for a random field encounter.

### New player Combat Missions

| Combat Mission | Enemy target | Warzone | Enemy minimum/profile | Victory GMP | Commander XP |
| --- | --- | --- | --- | --- | --- |
| Reedline Duel | Japanese Soldier | Swamp Encampment | Lv20 | 2,600 | 260 |
| Swamp Slug Intercept | Rebel Slug | Swamp Encampment | Lv20 | 2,900 | 280 |
| Midway Blade Hunt | Arab Soldier | Abandoned Carnival | Lv25 | 2,900 | 300 |
| Neon Rotor Sweep | Rebel Helicopter | Neon District | Lv30 | 3,500 | 360 |
| Scrapwalker Shutdown | Rebel Gear | Scrapyard Depot | Lv35 | 3,900 | 400 |
| Canyon S.A.M. Silence | Rebel S.A.M. | Canyon Missile Base | Lv40 | 4,300 | 440 |
| Alpine Fighter Intercept | Japanese Hi Fighter | Alpine Radar Station | Lv45 | 4,700 | 480 |
| Blackwater Bomber Run | Rebel Bomber | Offshore Platform | Lv50 | 5,300 | 520 |
| Terminus Tank Blockade | Japanese Tank | Subterranean Terminus | Lv55 | 5,900 | 560 |
| Forge Armour Breaker | Rebel Tank | Volcanic Forge | Lv60 | 6,600 | 600 |
| Blacksite Command Break | General T | Containment Laboratory | Lv65 | 6,900 | 640 |
| Lunar Alien Contact | Alien | Lunar Outpost | Lv70 | 7,600 | 680 |
| Lunar Walker Siege | Alien Walker | Lunar Outpost | Lv70 | 8,100 | 720 |
| Lunar Scout Intercept | Mini-UFO | Lunar Outpost | Lv70 | 7,900 | 700 |
| Blackwater Hi-Do Strike | Hi-Do | Offshore Platform | Boss profile | 8,500 | 720 |
| Lunar UFO Assault | UFO Boss | Lunar Outpost | Boss profile | 11,000 | 880 |

The minimum is a floor for normal mission targets; the complete current range
scales with Commander level. Boss targets use their dedicated boss combat profile,
not the normal Lv20–70 floor formula. Mission cards show the authoritative current
range. Every new mission also grants its authored resource mix, shown on its card.

Defeating a target awards that mission's GMP/resources, Commander XP and mission
clear through the existing transaction. Recovering one of the 14 eligible targets
instead grants its permanent recruit and the established 55 recovery XP, without a
victory reward or clear. Hi-Do/UFO Boss cannot be recovered. Their Combat Mission
rewards above are separate from the fixed reward for a Boss Operations battle.

### New four-staff Dispatch assignments

Each V2 dispatch requires exactly **four eligible staff**. Recommended staff level
is guidance; combined **Combat + 3 × level** determines power and the existing
server-calculated success chance. Difficulty is not a new player-entry level lock.
The new dispatches explicitly name their target and accompanying enemy force;
reports show that authored target first, including boss targets.

| Staff Dispatch | Lead enemy | Recommended staff | Duration | Difficulty | Success GMP | Staff XP success/failure |
| --- | --- | --- | --- | --- | --- | --- |
| Reedline Patrol Break | Japanese Soldier | Lv20+ | 2h 35m | 440 | 4,900 | 130 / 35 |
| Marsh Slug Supply Raid | Rebel Slug | Lv20+ | 2h 40m | 480 | 5,500 | 150 / 40 |
| Midway Swordsman Sweep | Arab Soldier | Lv25+ | 3h 05m | 500 | 6,000 | 160 / 40 |
| Neon Air Corridor | Rebel Helicopter | Lv30+ | 3h 40m | 590 | 7,700 | 200 / 50 |
| Scrap Gear Interdiction | Rebel Gear | Lv35+ | 4h 10m | 660 | 9,000 | 240 / 55 |
| Canyon Missile Screen | Rebel S.A.M. | Lv40+ | 4h 40m | 720 | 10,400 | 270 / 60 |
| Alpine Air Patrol Hunt | Japanese Hi Fighter | Lv45+ | 5h 10m | 770 | 11,600 | 290 / 65 |
| Blackwater Bomber Screen | Rebel Bomber | Lv50+ | 5h 40m | 830 | 13,200 | 320 / 70 |
| Tunnel Tank Interdiction | Japanese Tank | Lv55+ | 6h 10m | 900 | 14,900 | 360 / 75 |
| Forge Tank Stockpile Raid | Rebel Tank | Lv60+ | 6h 40m | 960 | 16,400 | 390 / 80 |
| Blacksite General Raid | General T | Lv65+ | 7h 10m | 1,000 | 17,600 | 410 / 85 |
| Lunar Alien Cache Raid | Alien | Lv70+ | 7h 35m | 1,040 | 19,100 | 430 / 85 |
| Lunar Walker Salvage | Alien Walker | Lv70+ | 7h 40m | 1,080 | 20,200 | 450 / 90 |
| Lunar UFO Scout Sweep | Mini-UFO | Lv70+ | 7h 40m | 1,070 | 19,800 | 440 / 90 |
| Blackwater Flagship Raid | Hi-Do | Lv50+ | 6h 30m | 1,020 | 17,000 | 420 / 85 |
| Lunar Command Ship Raid | UFO Boss | Lv70+ | 8h 30m | 1,340 | 25,000 | 540 / 105 |

Each dispatch uses the same warzone as its paired player mission above. Success
also grants the authored resource mix shown in its card. Failure retains 120 GMP
plus the listed failure XP per assigned staff member. All staff return through
the existing persistent reservation/settlement flow. Dispatch never grants the
opposing enemies as captured units, including missions with recruitable targets.

The unchanged chance formula is `45% + (power − difficulty) / 6` percentage points,
capped at 18%–95%. AI squads may select a new assignment once their strongest
available squad reaches its difficulty; locked staff and that benchmark are
rechecked before launch. Existing staff caps, autonomous schedules and economy
remain in force.

Targeted reports retain the authored lead/escort order. Untargeted expansion
reports can draw across their larger local pools through a deterministic rotation,
so aircraft and later pool entries are represented too. Aircraft receive hardware
sprite sizing. A report is still presentation of a committed result; reopening or
replaying it never rerolls success, consumes staff, grants rewards or creates units.
Previously committed report snapshots and pending mission identities are retained.

Balance definitions live in `public_html/includes/catalog.php`. See
[CHARACTERS_V2.md](CHARACTERS_V2.md) for the complete recovery/map mapping and
[../UPGRADE_v0.8.7.md](../UPGRADE_v0.8.7.md) for deployment without a schema reset.

## Retained v0.8.6.1 operation foundation

The historical tables below describe the original 11 expansion assignments, which
remain ahead of the new V2 entries. References to "new" in this retained section
refer to that earlier release.

Each expansion warzone now has one repeatable Combat Mission and one Staff Dispatch assignment.
The original four entries in each catalog keep their names, keys, balance, rewards and XP.
Player Combat Missions and staff Dispatch remain separate menus. Combat cards are text-only.

## Combat Missions

All new targets come from the local map encounter pool. The minimum is a floor:
enemy levels also rise with Commander progression. Cards show the current full range
and recommended Commander level using the same rules as combat. Mission entry does
not move your persistent map position or impose a new level lock.

| Threat | Warzone | Combat Mission | Enemy floor | Victory GMP | Commander XP |
|---:|---|---|---:|---:|---:|
| 13 | Swamp Encampment | Marsh Signal Cut | Lv20 | 2,400 | 240 |
| 14 | Abandoned Carnival | Midway Intercept | Lv25 | 2,700 | 280 |
| 15 | Neon District | Neon Blackout | Lv30 | 3,000 | 320 |
| 16 | Scrapyard Depot | Scrapline Ambush | Lv35 | 3,400 | 360 |
| 17 | Canyon Missile Base | Launch Window | Lv40 | 3,800 | 400 |
| 18 | Alpine Radar Station | Whiteout Relay | Lv45 | 4,200 | 440 |
| 19 | Offshore Platform | Blackwater Lockdown | Lv50 | 4,700 | 480 |
| 20 | Subterranean Terminus | Last Train Out | Lv55 | 5,200 | 520 |
| 21 | Volcanic Forge | Forge Breaker | Lv60 | 5,800 | 560 |
| 22 | Containment Laboratory | Protocol Severance | Lv65 | 6,400 | 600 |
| 23 | Lunar Outpost | Moonfall Directive | Lv70 | 7,100 | 640 |

All missions also award their listed resource mix. Swamp operations include biological
supplies, offshore and lunar assignments carry substantial fuel, and salvage/forge
assignments emphasize construction metals. Full amounts appear on each mission card.

A victory grants the authored reward, Commander XP and a repeatable mission clear.
Fulton recovery instead retains the established recovery reward (55 Commander XP and
a permanent recruit); it does not also count as a mission victory. Defeat grants no
victory reward. All outcomes use the existing roster restoration behavior.

## Staff Dispatch

Every new dispatch requires **four staff**. Recommended staff level is a guide; actual
squad power determines success. Open **Dispatch → Available Dispatches**, select staff
inside the chosen mission's card, and click that card's **Dispatch** button. The four
original assignments appear first, followed by the eleven new assignments. Each card
uses its own selection and required staff count. Up to40 available staff appear in
the original Combat-first order. Deployed staff cannot be reserved again.

| Threat | Staff Dispatch | Recommended staff | Duration | Difficulty | Success GMP | Staff XP success / failure |
|---:|---|---:|---:|---:|---:|---:|
| 13 | Marsh Supply Run | Lv20+ | 2h30m | 420 | 4,500 | 120 / 35 |
| 14 | Fairground Sweep | Lv25+ | 3h00m | 480 | 5,600 | 150 / 40 |
| 15 | Neon Courier Hunt | Lv30+ | 3h30m | 540 | 6,800 | 180 / 45 |
| 16 | Salvage Belt Recovery | Lv35+ | 4h00m | 600 | 8,000 | 210 / 50 |
| 17 | Canyon Stockpile Raid | Lv40+ | 4h30m | 660 | 9,300 | 240 / 55 |
| 18 | Alpine Relay Recovery | Lv45+ | 5h00m | 720 | 10,600 | 270 / 60 |
| 19 | Blackwater Fuel Lift | Lv50+ | 5h30m | 780 | 12,000 | 300 / 65 |
| 20 | Tunnel Freight Intercept | Lv55+ | 6h00m | 840 | 13,400 | 330 / 70 |
| 21 | Inferno Material Recovery | Lv60+ | 6h30m | 900 | 14,900 | 360 / 75 |
| 22 | Blacksite Research Extraction | Lv65+ | 7h00m | 960 | 16,400 | 390 / 80 |
| 23 | Orbital Supply Interdiction | Lv70+ | 7h30m | 1,020 | 18,000 | 420 / 85 |

Each staff member contributes **Combat + 3 × level** to team power. Success chance
is `45% + (team power − difficulty) / 6` percentage points, capped between **18% and
95%**. Four Combat80 staff at the recommended level have approximately **68.3%**
success at each new tier. The server validates and snapshots actual staff/power
when the dispatch launches. The saved chance appears in the result report, to four
decimal places, as in the accepted baseline.

Success pays the displayed GMP/resources and XP to each assigned staff member.
Failure pays **120 GMP** plus the tier’s failure XP. Staff return after either outcome.
The original four dispatches keep **80 XP on success / 25 XP on failure**.

All active deployments remain visible even with more than twelve concurrent runs.
The next finishing run opens its result when the timer expires. Completed history
shows the latest twelve reports. Reopening a report does not reroll or grant rewards.
New replays use the map’s enemy types and Lv20–23 through Lv70–73 opposition.

## AI commanders and continuity

AI commanders can select expansion dispatches when their strongest available squad
reaches that assignment’s difficulty. Veteran level contributes to this selection,
and the benchmark is checked again against locked staff at launch. Core mission
eligibility and Combat-first squad ordering remain unchanged. AI careers, field
captures, map population and existing Commander XP gains continue normally.

These assignments extend the existing combat and dispatch systems. Balance values
live in `public_html/includes/catalog.php`.
