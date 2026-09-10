## v0.9.0 account audio layer

`includes/audio_ui.php` mounts one global dock from the shared footer before
`msw.js` runs. `config/audio.php` is the fixed asset/track authority;
`includes/audio_context.php` supplies page routing and presentation events.
`assets/js/audio.js` owns media, gain controls, seeking, per-account caches,
revision-aware checkpoints, tab ownership and bounded effect voices. It never
submits gameplay actions. `audio.css` scopes its controls without changing
existing game artwork or card styling.

`audio_state.php` validates the existing authenticated session, CSRF, origin,
JSON shape and allowlisted track IDs. `includes/audio.php` reads/writes only two
additive account tables. Settings and each track use separate compare-and-swap
revisions; delayed checkpoints cannot overwrite newer committed positions or
preferences. Regular checkpoint traffic is excluded from the activity console.
The endpoint releases the PHP session lock before storage work.

PvE/PvP sound events are derived from committed FX sequence IDs. Quick AI duels
retain the already committed player cue before the AI reply. Dispatch/FOB audio
is added to deterministic film metadata after the existing authoritative result
is settled. Replay changes presentation only. Browser autoplay and reduced-motion
settings remain independent of game outcomes and saved account mute.

# Architecture — Metal Slug Warzone v0.8.7

## v0.8.7 character and operation extension

`includes/catalog.php` remains the common source for enemy metadata, map encounter
pools, recovery compatibility, R&D recipes, Boss Operations, Combat Missions and
staff Dispatch assignments. The 16 V2 keys extend existing catalogs; the original
keys retain their meaning. The six original maps and first 15 definitions in each
mission catalog retain their accepted values and order. The eleven expansion
pools include the new recruits. Lunar Outpost explicitly includes all three
recruitable alien variants; all new alien-targeted operations use that map.

The 14 recoverable definitions use existing `infantry`, `heavy_infantry`, `vehicle`
and `air` classes. Permanent recruits retain `source_enemy_key`, `unit_class` and
`affinity_type` through the existing owned-unit persistence paths. Hi-Do and UFO
Boss use `class=boss`, `recruitable=0` and the existing boss encounter mode. Boss
Combat Mission targets likewise retain boss combat and recovery exclusions.

AI live recovery resolves a compatible item through the Fulton catalog. Aircraft
require Wormhole at the shared R&D Lv8 threshold; restocking uses the same authored
R&D recipe. The development/catch-up candidate filter and final recruit guard also
apply that aircraft unlock. Existing background operations continue to represent
supplies abstractly; the release does not retrofit per-item consumption into old
elapsed-recruitment logic or alter ground-unit recovery progression. Existing
local pool selection, staff caps, career boosts and bounded scheduler remain.

V2 Dispatch entries name an explicit enemy target. The report builder snapshots
that authored opposition into the existing automatic-battle data; untargeted
historical dispatches retain their map-based fallback. Replay remains a projection
of the committed result, with no capture or settlement authority. Aircraft use the
existing hardware presentation size. Both mission menus retain their text-only
cards and accepted controls.

There are no new tables, columns, schema revisions or AI distribution markers.
Active fights and deployments keep their committed snapshots. The resident world
process reloads this release through the existing source-change retirement/startup
mechanism. See [CHARACTERS_V2.md](CHARACTERS_V2.md),
[TACTICAL_OPERATIONS.md](TACTICAL_OPERATIONS.md) and [../UPGRADE_v0.8.7.md](../UPGRADE_v0.8.7.md).

## v0.8.5.1 enemy-level correction

`catalog.php::msw_warzone_enemy_level_floor()` supplies the shared Threat 13–23
minimums (Lv20–70) to battle generation and all autonomous recovery paths. Keeping
this helper in the common catalog supports offline bot execution without loading
the battle UI. `msw_enemy_level_window()` shifts the full relative window above
the floor and preserves its maturity spread. Both map readouts consume this same
function. `msw_enemy_counter_profile()` uses the committed `underlevel_gap` when
available, with the existing calculation as fallback for older snapshots.
No schema migration, unit rewrite or AI redistribution is added.

## v0.8.5 expanded map catalog

The map and collision catalogs are the single source of truth for all 17 warzones.
Battle threat, runtime movement, human presence, autonomous enemy pools and setup
placement consume these catalogs. Above Threat 12, a bounded continuation extends
normal combat; no old balance denominator or committed encounter is changed.
`msw_seed_bot_population()` performs one-time transactional placement tracked by
`warzone_expansion_v085`, while setup retains its shared world-maintenance lock.
The existing schema revision 9 and global scheduler remain unchanged.
See [WARZONE_EXPANSION.md](WARZONE_EXPANSION.md) for formulas and preservation rules.

## Current autonomous world runtime

`includes/world_runtime.php` is the shared scheduler used by the authenticated
`world_pulse.php` endpoint and automatic `includes/world_service.php`. `includes/ui.php` loads the
separate `assets/js/world_runtime.js` script after normal page rendering. Heavy
AI simulation no longer runs in navigation or map-presence requests.

A non-blocking advisory lock is scoped to the configured database name. While
holding it, a durable `schema_meta` timestamp limits the world to one pulse per
configured interval (2 seconds by default), regardless of tab/account/worker
count. Each pulse has separate arrival and AI budgets. The default arrival batch
is at most 4 rows with a 120 ms cooperative deadline; detailed AI work remains at
most 4 commanders with a 180 ms cooperative deadline. Limits are checked between
operations, so one transaction can exceed the cooperative time boundary. Runtime
work uses a 2-second InnoDB lock-wait limit to avoid indefinite queue waits.

Arrivals use the original settlement formulas through
`msw_fob_resolve_staff_dispatch()`. It rechecks pending/due state under the mission
row lock, then locks commanders in ID order and commits resources, protection,
unit XP, reservation release and the canonical raid link together. A failed
transaction leaves its original mission pending. The worker applies a 60-second
retry delay and rotates its global cursor, preventing a failed record from
monopolizing service. Inbound work for the viewing defender gets priority within
the same bounded batch; global processing also covers offline/disabled attackers.
The original attacker-specific wrapper remains for existing outbound call sites.

AI selection is globally oldest-due, independent of the currently viewed map.
The existing capped catch-up policy, competitive classes, roster/stat ceilings and
real sector-derived Base Power remain authoritative. Training filters capped or
currently deployed staff before LIMIT. The human-power anchor cache expires after
5 seconds so a long-running worker can follow player progression.

The endpoint authenticates and verifies CSRF/origin before releasing the PHP
session lock. It returns only public leaderboard fields and the authenticated
commander's incoming/report references, never private snapshots, resources or
another defender's reports. Readouts use server timestamps and committed results;
JavaScript has no gameplay settlement authority. Retry, tab visibility and browser
back-forward restoration are handled in the new script, preserving `msw.js`.

## Clock and upgrade contract

The existing database uses local DATETIME values. Runtime and setup now set the
MySQL session timezone to PHP's current numeric offset; the worker refreshes that
offset every cycle. This aligns SQL `NOW()` with PHP-written deadlines without
requiring installed MySQL timezone tables or converting saved history. Session
timezones affect SQL clock functions; they do not rewrite stored DATETIME values.
See the [MySQL timezone documentation](https://dev.mysql.com/doc/en/time-zone-support.html).
Transaction settlement continues to use the existing InnoDB commit/rollback
contract described in the [PHP mysqli transaction documentation](https://www.php.net/manual/en/mysqli.quickstart.transactions.php).

Schema revision 9 adds only `idx_fob_dispatch_due(result,id,finish_at)`.
`msw_schema_upgrade_world_runtime()` is idempotent and is called by the existing
Update / Repair installer. Runtime activation and a periodic lifetime check repair schedules or leases
outside the supported scheduling horizon while preserving valid schedules and
earned data. No pending-strike deletion or leaderboard reseed is used to fix stalls.

Windows local setup installs an indefinite one-minute startup check for the
windowless PHP engine. The engine runs bounded two-second updates while Apache
is reachable and reconnects after MySQL outages. A file lock prevents duplicate
processes; the existing database lock/throttle also serializes browser requests.
Source/configuration updates retire the engine between batches for automatic
restart. No player request or browser visibility is needed for offline progress.
See `AUTOMATIC_WORLD.md` and `../UPGRADE_v0.8.4.5.md`.

## Inherited architecture


> **v0.8.0 automatic-operations note:** Dispatch and FOB gameplay settlement remains fully server-authoritative. The new automatic battle layer is a deterministic post-settlement projection: it reads committed mission/raid snapshots and results, then visualizes them in a Peace Walker-inspired opposing-force report without owning success rolls, rewards, resource transfer, protection, XP or persistent combat state. The accepted v0.7.5 warzone progression gate and all prior Commander/Mother Base/Security authority remain preserved.

## Authority model

PHP/MySQL remains authoritative. The browser submits intent and renders returned state; it does not own movement coordinates, collision, staff movement, unit ownership, sector levels, inventory, battle state, FOB membership, target authority, bot state, resources or timers. v0.6.0 continues that rule: the new FOB Command Centre composes existing ledgers and adds only a one-use retaliation link; it does not create client-owned shortcuts.


## Corrected automatic battle presentation (v0.8.1)

The v0.8.1 replay UI reuses the established encounter-battle visual primitives (`battle-scene`, `battle-side`, `fighter`, `fighter-sprite-shell`, `battle-card`, `hpbar`) and layers multi-unit automatic choreography on top. `includes/auto_battle.php` supplies both teams, current/max HP, deterministic event choreography and result copy; `assets/js/msw.js` animates HP, force integrity, attack/hit/KO states and replay/skip; `assets/css/msw.css` constrains the arena to the encounter scale.

FOB snapshots now retain existing `hp`/`max_hp` fields in addition to the earlier unit identity/combat fields. Historical snapshots without them fall back safely. When an FOB side has no assigned combat team, the replay renders a representative base/security defense element so the opposing force remains visible without changing the stored raid result. Static assets are version-query cache-busted through `includes/ui.php` so CSS and JavaScript from a prior release cannot be mixed with new replay markup. Gameplay authority remains outside this presentation layer.

## Automatic operations battle playback (v0.8.0)

The automatic battle system is deliberately separated from gameplay resolution. `includes/auto_battle.php` is a read/projection layer, not a combat-authority layer.

1. **Standard Dispatch:** `includes/dispatch_authority.php` remains the only resolver. `dispatch_result.php` may invoke the existing due-resolution function, then builds a replay model from the committed mission row, selected owned units, snapshot power, difficulty, stored result and stored rewards.
2. **FOB staff strike:** `includes/fob_world.php` remains the only resolver. `fob_dispatch_result.php` invokes the established due-strike resolver. A normal combat outcome is represented by its created `fob_raids` row and therefore redirects to the canonical `fob_result.php`; `protected_abort` remains a non-combat terminal state and receives a withdrawal/shield presentation only.
3. **Direct invasion / retaliation:** the existing synchronous raid transaction commits the raid first. `fob_result.php` then reads the persisted attacker/defender snapshots and result and renders playback. Immediate/retaliation HUD power can display the exact stored comparison rolls; this is display-only and cannot affect settlement.
4. **Determinism:** visual event selection uses a stable hash of operation identity/result. No `random_int()` or browser randomness is used to decide a replay winner. The event sequence is generated so its final integrity state agrees with the committed result.
5. **Client responsibility:** JavaScript only advances presentation frames, applies unit/integrity state and routes an expired countdown to a server result endpoint. If the server still considers an operation pending, the result endpoint renders the authoritative pending state rather than accepting the client clock as proof of completion.
6. **Snapshot fidelity:** FOB unit snapshots now include the existing unit `source_enemy_key`, ATK, DEF and SPD fields in addition to prior metadata. This enriches future replay rendering without adding tables/columns or mutating historical raid snapshots.
7. **Accessibility:** Replay/Skip controls are local presentation controls. `prefers-reduced-motion` skips timed choreography and applies the committed final presentation immediately.

This separation is a regression contract: future visual improvements may change choreography and HUD layout, but must not create a second Dispatch/FOB result calculation path.

## Persistent autonomous commander model

Autonomous commanders remain first-class game identities:

- `users.is_bot=1` identifies an autonomous commander while retaining the same operative, Commander XP, resources, Base Power, active-map and Mother Base fields used by humans.
- `bot_commanders` provides the stable `bot_index`, activity/counter state and persisted `next_action_at` / lease timestamps.
- Production population remains exactly 1,000 stable indexes. Update / Repair seeds only missing production identities and never recreates valid persistent bot progression.
- Bot authentication remains blocked through the normal login path.
- Bots use the normal `units`, `player_resources`, `inventory`, `base_sectors`, `dispatch_missions`, `fob_strike_dispatches`, `fob_raids` and `pvp_matches` ledgers instead of parallel fake state.

Fresh seeding remains balanced across all six warzones and uses the accepted legal collision lattice. Per-warzone operative variety remains deterministic and all six accepted player skins are represented within each large autonomous population.

## Bounded autonomous scheduling and v0.5 pacing

The XAMPP/PHP runtime does not create 1,000 background workers. Simulation remains request-driven, persisted and bounded:

1. each bot has a persisted `next_action_at`;
2. a gameplay/presence/network request selects a bounded due batch;
3. selected bots receive short database leases;
4. one autonomous action is committed per lease;
5. the next action time is persisted before later pulses can service that bot again.

v0.5.0 tightens normal action spacing from 12–32 seconds to 10–26 seconds and applies a bounded 1.25× pulse-budget multiplier, with a hard pulse cap of 45. Field-combat XP/resources are modestly increased. These changes make the shared rankings evolve faster without allowing extra browser tabs to bypass a commander's own persisted schedule.

Autonomous action weighting now gives FOB aggression roughly 15% of action decisions (staff invasion plus immediate raid combined). Target selection can prefer local competition or search globally; when eligible targets exist, a configured 28% bias can prefer human defenders. Defender protection is always respected.

## Mother Base sector systems

`base_sectors` remains the authoritative level source. `msw_sector_unlock_catalog()` is the canonical description of runtime milestones and the Mother Base Capability Matrix renders that same catalog.

### Commander combat-stat projection (v0.7.1)

`msw_commander_fighter()` remains the shared Commander-stat authority and composes the personal-level curve plus automatic Mother Base bonuses. v0.7.1 changes the Mother Base input from coarse integer levels to **continuous persisted sector development**: `base_sectors.score / 120` is the raw development-step value, so assigning staff contributes immediately even before the next displayed sector level.

The sector-to-stat contract per effective 120 score points is:

- Combat: ATK +7.00.
- R&D: ATK +7.00 and SPD +3.00.
- Support: MAX HP +8.00 and DEF +2.00.
- Intel: SPD +4.00.
- Medical: MAX HP +18.00.
- Mess: MAX HP +6.00 and SPD +2.00.
- Security: DEF +5.00.

`msw_commander_sector_development()` reads the authoritative score/level rows and preserves fractional progress. `msw_commander_sector_effective_steps()` then applies the same bounded late-game curve to that fractional value: the first nine development steps contribute at 100%, the next ten at 65%, and later steps at 40%. Before aggregation, a non-zero staffed sector floors each mapped raw contribution at 0.51 so the first valid assignment cannot disappear when the final integer Commander stat is rounded. Bonuses are otherwise aggregated by stat and rounded at the end. This guarantees that R&D staffing can affect Commander ATK/SPD before a 120-point level boundary while preventing unbounded late-game linear growth.

The personal-level curve is also slightly stronger in v0.7.1 (HP +3.8/level step, ATK +1.35, DEF +1.00, SPD +0.45 before flooring) so personal levelling and Mother Base development both create real separation from field contacts. Because PvP/AI projections consume the same Commander fighter authority, no parallel PvE-only player-stat copy is introduced.

### R&D

- Lv1: standard Fulton manufacturing/recovery.
- Lv4: Fulton+.
- Lv 5: Cargo Fulton, including ground-vehicle recovery.
- Lv8: Wormhole Fulton, including aircraft recovery.

`msw_fulton_catalog()` controls battle capability and `msw_rd_catalog()` controls manufacturing. Both are checked server-side.

### Medical

Medical consumables use multi-sector recipe requirements and are real persistent inventory items:

- Combat Medkit: R&D 2 + Medical 2; base 35 HP.
- Trauma Kit: R&D 5 + Medical 5; base 80 HP.
- Nanomed Injector: R&D 8 + Medical 8; base 160 HP.

`msw_use_battle_item()` revalidates requirements, refuses a full-HP use before consumption, atomically consumes one inventory unit, applies healing, advances the player's action, then permits normal Security backup/enemy response flow.

### Intel

Intel is not display-only:

- Lv2 reveals enemy ATK/DEF/SPD.
- Lv4 reveals type effectiveness and a server-calculated recommended move.
- Lv6 reveals the exact current Fulton success forecast before use.
- Lv8 reduces the normal PvE enemy counterattack accuracy by 6 percentage points.

### Security backup party

Schema revision 7 adds `security_backup_slots`, a two-slot persistent selection layer over existing owned `units` rows. A valid escort must:

- belong to the player;
- be assigned to `security`;
- be `infantry` or `heavy_infantry`;
- not be actively dispatched.

The Staff page manages the slots. Reassigning a selected unit away from Security clears its backup selection. At battle synchronization, only currently valid rows are projected into the fight.

Backup output remains deliberately constrained but is more reliable in v0.7.1. Base assist accuracy rises to 72%, Security Lv4 adds 6 points, later Security levels add incremental accuracy up to an 86% ceiling, and controlled non-boss per-hit caps are 9%/11% of enemy maximum HP before/after Security Lv7. Boss support remains capped at 4.5% per hit, preserving the Commander as the primary damage source.

Each selected escort also has battle-local `hp/max_hp`. `msw_security_backup_guard()` rotates interception duty among living escorts and absorbs a bounded portion of a successful enemy counter before Commander HP is reduced. Interception begins at 20%, can gain up to 14 points from Security Team level plus up to 6 points from the escort's Security stat, and is hard-capped at 40%. Absorption consumes escort HP; an escort at 0 HP is KO'd and can neither guard nor fire. `msw_merge_security_backup_runtime()` preserves the current HP ratio when valid backup rows are re-synchronized, preventing page reloads from restoring escort health. No persistent unit injury/death is introduced; this HP exists only inside the encounter state.

### Support

Support Lv3 raises medical-item healing to 115% of base and Support Lv6 to 125% total. This multiplier is applied in the authoritative battle engine, not calculated by the browser.

## Underlevel high-threat progression gate (v0.7.5)

`msw_warzone_readiness_pressure()` adds a conditional progression-gate layer on top of the accepted player-relative enemy level window and v0.7.4 threat factors. The enemy level is still rolled from Commander level + threat; the new layer exists only to prevent a very low-level Commander from treating late maps as ordinary fights.

Normal field/mission contacts use readiness benchmarks T4=5, T5=6, T6=7, T7=9, T8=10, T9=12, T10=13, T11=15 and T12=16. Threat 1–3 have no readiness penalty. `underlevel_gap = max(0, benchmark - commander_level)` and feeds capped multipliers: HP `1 + min(.55, gap*.045)`, ATK `1 + min(.85, gap*.070)`, DEF `1 + min(.35, gap*.030)`, SPD `1 + min(.18, gap*.015)`. These multiply after rolled-enemy-level and map-threat factors. Bosses remain on their dedicated curve and do not inherit this gate.

The gate is intentionally based on Commander level, not live Mother Base staffing. Mother Base development therefore never weakens an enemy behind the scenes; it strengthens the player directly. This keeps Combat/R&D/Medical/Security/Intel/Support/Mess progression legible and makes substantial base development the intended way for an ambitious underlevel Commander to survive a dangerous map.

Normal enemy counter accuracy additionally receives up to +6 points from the same underlevel gap before existing Commander SPD and Intel reductions. Player attack accuracy is untouched. Security interception/support is also untouched, but increased raw enemy ATK means escort HP is depleted faster under real late-warzone pressure.

Encounter compatibility advances to `warzone_player_threat_window_v5`. Existing v3/v4 encounters preserve the exact committed enemy level and roll, preserve current HP percentage, and recalculate only combat stats. Older models still receive the deterministic legal-window migration before v5 calibration.

## High-threat combat pressure calibration (v0.7.4)

`msw_enemy_level_window()` and `msw_enemy_level_offset_for_roll()` are intentionally unchanged from v0.7.3. Enemy **level selection** remains the progression backbone, including the +0/+1/+2/+3/+4/+5 threat ceilings and maturity-based lower spread. v0.7.4 changes only the independent **combat pressure** layered on top of the already-rolled enemy level.

For normal enemies, `msw_enemy_scaled_stats()` retains 3.0% HP, 2.8% ATK, 2.4% DEF and 1.0% SPD per enemy-level step. The threat-pressure exponent is reduced from 1.25 to 1.10 and the curve is widened from the same accepted Threat 1 factors, so the low-map baseline is byte-for-byte formula-equivalent while middle/high maps separate earlier and more coherently. Threat 12 reaches approximately **1.48× HP, 1.40× ATK, 1.22× DEF and 1.08× SPD** before enemy-level scaling. This intentionally emphasizes HP/ATK over DEF: dangerous maps gain survivability and offensive pressure without creating excessive damage-sponge behavior.

`msw_enemy_counter_profile()` adds up to +4 base counter accuracy from threat before Commander SPD and Intel reductions. `msw_enemy_counter_power()` raises the normal-enemy threat component from +6 to +8 at the top end. Enemy ATK is still applied exactly once by `msw_damage()`; counter move power remains a separate class/threat term and does not reintroduce the historical ATK×ATK escalation.

Encounter model marker `warzone_player_threat_window_v4` distinguishes the calibration. A v0.7.3 encounter already has a valid threat/player level roll, so migration preserves that enemy level and stored roll, recalculates HP/ATK/DEF/SPD, and retains current HP percentage. Older encounters still receive one deterministic legal-window level migration first.

## Threat-aware player-relative enemy scaling (v0.7.3)

PvE encounter creation deliberately separates **enemy level selection** from **threat stat pressure**, but v0.7.3 makes threat authoritative in both stages rather than using one universal level window. `msw_enemy_level_window()` first derives the legal level offset range from current Commander level and threat. Normal-map ceilings progress from +0 at Threat 1 through +1/+2/+3/+4 and finally +5 at Threat 10–12. Commander maturity then widens the lower side of the range: Lv1–5 use a two-level spread, Lv6–9 three, Lv10–14 four, Lv15–19 five and Lv 20+ six, with a normal floor of −3. This produces **Commander Lv 5 / Threat 12 = +3..+5 (enemy Lv8–10)** and **Commander Lv 20 / Threat 12 = −1..+5 (enemy Lv19–25)**. Bosses use a separate tighter window because their authored catalog values are already exceptional.

`msw_enemy_level_offset_for_roll()` applies a threat-driven weight blend across that legal window. Low threat favors the lower offsets; increasing threat shifts probability toward the upper offsets without removing range variety. The 1–100 mapping is deterministic for a supplied roll. `msw_roll_enemy_level()` persists the selected roll, final offset and min/max offsets in encounter state under `warzone_player_threat_window_v3`, so an established battle never rerolls from a browser refresh.

`msw_enemy_scaled_stats()` then derives HP/ATK/DEF/SPD from the enemy catalog base, the actual rolled enemy level and the authoritative threat. For normal contacts, level progression uses 3.0% HP, 2.8% ATK, 2.4% DEF and 1.0% SPD per level step. Threat is a separate nonlinear multiplier reaching approximately 1.30× HP, 1.22× ATK, 1.16× DEF and 1.06× SPD at Threat 12 before the level factor. The nonlinear curve keeps low/mid maps controlled while making the final warzones unmistakably stronger. Bosses retain lower dedicated rates.

`msw_sync_enemy_runtime_state()` hot-upgrades active pre-v0.7.3 encounters. It derives one deterministic migration roll from stable encounter fields, moves the enemy into the legal v3 level window, recalculates stats, and preserves the exact current HP percentage as closely as integer rounding allows. Once upgraded, the v3 model marker prevents further rerolls.

Enemy counter damage remains decoupled from raw ATK growth. `msw_enemy_counter_power()` derives move power from enemy class plus a smooth threat bonus; enemy ATK is then consumed exactly once by `msw_damage()`. This retains the v0.7.1 protection against superlinear ATK×ATK behavior while letting deeper warzones apply higher counter pressure.

Commander SPD remains strictly beneficial in PvE. `msw_player_attack_profile()` never subtracts accuracy for enemy speed: SPD adds up to 5 points on top of the PvE accuracy baseline, Combat adds up to 3, Intel Lv4 adds 1, and final player attack accuracy is clamped to 94–100%. `msw_enemy_counter_profile()` separately begins enemy counter accuracy at 88%, subtracts up to 6 points from absolute Commander SPD plus up to 6 from a positive speed advantage (10 total SPD reduction cap), then stacks Intel Lv8's −6. Final enemy counter accuracy cannot fall below 55%.

## Unified PvE battle flow and animation contract

Field contacts, normal missions, sidequests, rival commander fights and bosses all use `battle.php` and the shared encounter state in `battle_engine.php`. v0.5.0 adds a small `fx` snapshot to each committed action so rendering can describe the action that actually occurred without granting the client combat authority.

The PvE sequence is:

1. the player attacks, heals or attempts Fulton recovery;
2. if the enemy is still active, selected Security escorts may provide controlled covering fire;
3. if the encounter remains active, the enemy executes its counterattack;
4. committed state/version is persisted using the existing encounter concurrency contract.

The UI maps committed FX to CSS choreography: commander lunge, enemy impact, enemy counter-lunge, commander impact, Security covering fire, medical pulse and Fulton extraction feedback. Initial contact also animates. `prefers-reduced-motion` disables the nonessential motion.

## PvP modes and choreography

`pvp_matches.match_mode` continues to support `live`, `live_ai`, `snapshot` and `ai_sim`. PvP state now also stores the last committed action FX so the match screen can animate the attacking and impacted sides. Combat settlement remains server-side/version-locked; the animation is presentation of persisted turn state, not a client simulation.

Security escorts and PvE medical consumables are intentionally not injected into competitive PvP balance.

## FOB Command Centre orchestration

`fob.php` is the primary deployed-FOB strategic surface. It reads global target candidates, available staff, pending outbound/inbound strike rows, incoming raid incidents and recent outgoing reports, then submits only normal direct-raid or staff-dispatch intent back to PHP authority.

Parallel staff invasion support does not require a new queue table: each committed strike already owns a durable `fob_strike_dispatches` row and selected staff are reserved through `units.dispatched_until`. Repeated Command Centre launches simply create additional valid strike rows using different currently available staff.

Retaliation adds a nullable self-ledger reference, `fob_raids.retaliation_for_raid_id`. It identifies the exact incoming incident that authorized a retaliation and is protected by a unique index. The attacker/defender relationship is revalidated before resolution, and the retaliation still goes through `msw_fob_resolve_direct_raid()` with the normal target-protection/resource-locking path.

Protection is now explicitly passive recovery state. Direct raids, retaliation and staff-strike launch call a shared locked helper that clears the attacker's active protection only when the offense is being successfully committed.

## Global FOB topology

Each commander still has one permanent `fob_world_memberships` row: biome, world/shard, FOB skin, slot and deterministic x/y are home identity. The v0.4.1 irregular 144-anchor layout remains unchanged.

v0.5.0 separates **home identity** from **invasion browsing**. A deployed commander can use the same Earth globe to:

1. choose one of the five biome/continent theatres;
2. list populated `fob_worlds` for that biome;
3. open a selected remote or home shard;
4. inspect and attack valid occupants.

Viewing or attacking a remote shard never rewrites the attacker's membership, Mother Base skin or slot.

`msw_fob_target_row()` now authorizes a target globally: the attacker must have a valid membership, the target must have a valid membership, the IDs must differ, and an optional selected `world_id` must match the target. The former same-world requirement is intentionally removed from invasion targeting only.

## FOB economy and protection

Immediate human raids keep the accepted transactionally locked resource transfer and defender post-invasion protection. There remains no attacker-side cooldown.

Autonomous commanders may now invade human or AI defenders across shards. Because offline humans can be pressured, autonomous direct raids use a deliberately smaller 3% transfer rate with lower caps than a human immediate invasion. Defender protection still applies after every completed invasion attempt, win or loss, and therefore remains the principal anti-drain boundary.

Human defenders receive a local server-console `FOB · DEFENSE` event after resolved direct or staff attacks. Incoming reports remain visible through the normal shared `fob_raids` ledger.

## Cross-shard staff invasion dispatch

FOB staff strikes remain separate from standard Combat Unit Dispatch while sharing `units.dispatched_until` as the authoritative reservation field. Launch performs ownership/availability revalidation under row locks and persists the **target defender's world ID** in `fob_strike_dispatches`.

Completion remains MySQL-timestamp-driven and exactly-once. A protected defender causes `protected_abort`, returns reserved staff and transfers no resources. A resolved strike writes the normal `fob_raids` after-action report. The existing conditional reservation release protects against a stale mission completion clearing a newer reservation.

## Physical-space separation

Warzone position remains on `users.active_map/map_x/map_y/facing/last_seen`. Mother Base visitor position remains in `mother_base_presence`. Global FOB shard browsing is a strategic UI and does not relocate either physical-space presence system.

FOB deployment continues to bind `users.mother_base_key` to the chosen compatible FOB skin. Existing friend/Strike Force Mother Base visitation and server-authoritative collision remain unchanged.

## Schema revision 8

The current schema revision is **8**. v0.5.0 revision 7 added:

- `security_backup_slots(user_id, slot_index, unit_id, created_at, updated_at)`;
- primary key `(user_id,slot_index)`;
- unique selected unit per user;
- cascading FKs to `users` and `units`.

v0.6.0 then adds only nullable `fob_raids.retaliation_for_raid_id` plus unique index `uq_fob_retaliation_source`. All previous FOB world/spatial, Security backup and gameplay structures remain. `_setup.php` Update / Repair is additive/idempotent and Confirm Installation now includes both `security_backup_integrity` and `fob_retaliation_integrity` in addition to the existing autonomous population and FOB topology checks.

## Local WorldServer console

The v0.3.5 local-filesystem console architecture remains unchanged. It is not a network admin endpoint, ignores bot-originated console identity, suppresses movement/polling noise and fails silently if logging is unavailable. v0.5.0 adds human-visible FOB defense events only after authoritative raid settlement.


## v0.6.0 supplied-art presentation layer

The Command Network visual system remains strictly downstream of gameplay authority. `includes/ui.php` assigns a page identity class and emits semantic resource/stat classes; `assets/css/msw.css` maps those identities to the 23 supplied artwork files and owns palette, scan-line, hover, responsive and reduced-motion behavior. `assets/js/msw.js` may mirror the already-rendered selected-operative sprite into a hero and add intersection-based reveal classes, but it does not calculate rewards, eligibility, combat outcomes, invasion validity, protection state or persistence.

No visual effect creates an alternate gameplay route. Forms, CSRF validation, MySQL row locks, dispatch reservations, raid settlement and exactly-once logic continue through the same PHP authority functions documented above. Disabling JavaScript or CSS therefore changes presentation only, not the command network's authoritative state transitions.
