# Tactical Operations — v0.8.6.1

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
