# Runtime Asset Manifest — v0.9.0

## v0.9.0 supplied sound and music library

All **18 MP3 music tracks and 8 WAV sound effects** from the supplied
`Metal Slug Warzone Sound And Music Library v1.zip` are included. All 26 original
files are preserved byte-for-byte. Runtime names are normalized to lowercase
hyphenated paths under `public_html/assets/audio/`. Existing artwork, maps,
sprites and mission card presentation are unchanged.

Music is stereo 44.1 kHz MP3. Effects are PCM 16-bit WAV; seven effects use their
original 22.05/44.1 kHz files. The supplied rocket effect is 192 kHz mono, so its
runtime copy is resampled to 44.1 kHz mono PCM16 for a smaller browser decode
payload. The 192 kHz original remains at
`assets/audio/source/rocket-original-192khz.wav`. No trimming, gain adjustment or
other audio transformation is applied.

`config/audio.php` is the runtime catalog. `assets/audio/manifest.json` records
each source name, runtime path, duration, codec/rate, source/runtime byte counts
and SHA-256 hashes. Durations below are decoded-container estimates in seconds;
the browser uses loaded metadata for seek limits.

| Audio ID | Uploaded filename | Runtime path | Duration | Runtime bytes |
| --- | --- | --- | ---: | ---: |
| `mother_base` | `Mother Base.mp3` | `assets/audio/music/mother-base.mp3` | 172.173 s | 2,308,844 |
| `extra_mother_base` | `Extra Mother Base.mp3` | `assets/audio/music/extra-mother-base.mp3` | 156.552 s | 2,455,078 |
| `missions_select` | `MissionsSelect.mp3` | `assets/audio/music/missions-select.mp3` | 114.024 s | 1,647,864 |
| `map_1` | `Level Map Variety 1.mp3` | `assets/audio/music/map-1.mp3` | 77.897 s | 1,072,385 |
| `map_2` | `Level Map Variety 2.mp3` | `assets/audio/music/map-2.mp3` | 64.705 s | 918,727 |
| `map_3` | `Level Map Variety 3.mp3` | `assets/audio/music/map-3.mp3` | 47.700 s | 631,573 |
| `map_lunar` | `Level Map Variety LUNAR ONLY.mp3` | `assets/audio/music/map-lunar.mp3` | 87.040 s | 1,305,232 |
| `battle_1` | `Battle Encounter Variety 1.mp3` | `assets/audio/music/battle-1.mp3` | 88.764 s | 1,356,853 |
| `battle_2` | `Battle Encounter Variety 2.mp3` | `assets/audio/music/battle-2.mp3` | 123.507 s | 1,901,886 |
| `battle_3` | `Battle Encounter Variety 3.mp3` | `assets/audio/music/battle-3.mp3` | 76.408 s | 1,162,761 |
| `battle_lunar` | `Battle Encounter Variety LUNAR ONLY.mp3` | `assets/audio/music/battle-lunar.mp3` | 61.257 s | 886,498 |
| `boss_battle` | `ALL BOSS BATTLES Music.mp3` | `assets/audio/music/boss-battle.mp3` | 120.007 s | 1,854,931 |
| `battle_lost` | `Battle or mission lost failed.mp3` | `assets/audio/music/battle-lost.mp3` | 7.262 s | 84,861 |
| `boss_win` | `Boss Battle WIN.mp3` | `assets/audio/music/boss-win.mp3` | 11.389 s | 142,360 |
| `fob_battle` | `Invade Fob Battle Replay Music.mp3` | `assets/audio/music/fob-battle.mp3` | 14.315 s | 202,167 |
| `fob_win` | `Invade Fob Battle Replay WIN THEME.mp3` | `assets/audio/music/fob-win.mp3` | 9.665 s | 114,583 |
| `versus` | `Metal Gear Solid_ Peace Walker Expanded Soundtrack - 56 - VERSUS OPS.mp3` | `assets/audio/music/versus.mp3` | 77.192 s | 1,099,041 |
| `mission_complete` | `Metal Gear Solid_ Peace Walker Expanded Soundtrack - 66 - EXTRA OPS (Mission Complete).mp3` | `assets/audio/music/mission-complete.mp3` | 6.191 s | 65,497 |
| `rifle` | `S_Rifle_Fire_1.wav` | `assets/audio/effects/rifle.wav` | 1.602 s | 282,570 |
| `bullet` | `S_Bullet.WAV` | `assets/audio/effects/bullet.wav` | 0.218 s | 19,907 |
| `rocket` | `S_Rocket_Fire.WAV` | `assets/audio/effects/rocket.wav` | 2.334 s | 205,956 |
| `explosion` | `S_explosion_01.WAV` | `assets/audio/effects/explosion.wav` | 2.378 s | 104,980 |
| `death_1` | `S_Male_Death_01.WAV` | `assets/audio/effects/death-1.wav` | 0.646 s | 28,520 |
| `death_2` | `S_Male_Death_02.WAV` | `assets/audio/effects/death-2.wav` | 1.107 s | 48,860 |
| `enemy_winner` | `S_AI_Commando_or_AnyEnemy_WINNER.WAV` | `assets/audio/effects/enemy-winner.wav` | 1.277 s | 225,406 |
| `flawless_win` | `S_Player_Flawless_Win.WAV` | `assets/audio/effects/flawless-win.wav` | 1.707 s | 75,302 |

Validation performed while preparing these assets: all 26 original files passed
FFprobe inspection and full FFmpeg decode; all copied originals were compared
with SHA-256, and the rocket derivative separately passed full decode. These
checks establish media integrity; browser playback and account persistence are
covered separately by the sound-system validation and XAMPP checks in
[AUDIO_SYSTEM.md](AUDIO_SYSTEM.md).

## v0.8.7 Characters V2 delta

All **16 supplied PNGs** from `Characters v2.zip` are retained at their original
bytes and native dimensions. Runtime filenames normalize the source spellings;
no sprite is resized, recompressed, cropped or repainted. Paths below are relative
to `public_html`. All pre-existing runtime art remains in the complete package.

| Uploaded filename | Runtime path | Native size | Bytes |
| --- | --- | --- | --- |
| `Alien Walker.png` | `assets/sprites/enemies/alien_walker.png` | 339 × 371 | 106,985 |
| `Alien.png` | `assets/sprites/enemies/alien.png` | 212 × 188 | 45,916 |
| `Arab Soldier.png` | `assets/sprites/enemies/arab_soldier.png` | 265 × 309 | 73,488 |
| `General_T.png` | `assets/sprites/enemies/general_t.png` | 278 × 254 | 70,453 |
| `Hi-Do Boss.png` | `assets/sprites/enemies/hi_do.png` | 222 × 198 | 37,051 |
| `Japenese Hi Fighter.png` | `assets/sprites/enemies/japanese_hi_fighter.png` | 386 × 255 | 94,017 |
| `Japenese Soldier.png` | `assets/sprites/enemies/japanese_soldier.png` | 197 × 239 | 56,258 |
| `JapeneseTank.png` | `assets/sprites/enemies/japanese_tank.png` | 275 × 212 | 80,727 |
| `Mini-UFO.png` | `assets/sprites/enemies/mini_ufo.png` | 49 × 45 | 1,150 |
| `Rebel Bomber.png` | `assets/sprites/enemies/rebel_bomber.png` | 345 × 422 | 124,721 |
| `Rebel Gear.png` | `assets/sprites/enemies/rebel_gear.png` | 275 × 246 | 78,025 |
| `Rebel Helecopter.png` | `assets/sprites/enemies/rebel_helicopter.png` | 384 × 252 | 85,354 |
| `Rebel S.A.M.png` | `assets/sprites/enemies/rebel_sam.png` | 327 × 334 | 136,672 |
| `Rebel Slug.png` | `assets/sprites/enemies/rebel_slug.png` | 256 × 231 | 62,460 |
| `Rebel Tank.png` | `assets/sprites/enemies/rebel_tank.png` | 299 × 235 | 94,795 |
| `UFO BOSS.png` | `assets/sprites/enemies/ufo_boss.png` | 447 × 265 | 112,356 |

These assets drive enemy battles, captured staff/hardware, Boss Operations and
automatic battle reports through the enemy catalog. **Combat Mission and Dispatch
cards remain text-only**. Mini-UFO retains its native 49 × 45 source; the existing
renderer determines on-screen sprite sizing. No new Commander avatar is added.
See [CHARACTERS_V2.md](CHARACTERS_V2.md) for recovery classes and gameplay placement.

## v0.8.5 expansion delta

All 67 baseline runtime images are retained byte-for-byte. All 11 files from
`v4maps.zip` are copied byte-for-byte to the named paths below. Every new map is
1672×941 native. Small previews for all 17 warzones live in
`assets/maps/thumbnails/<map_key>.jpg`; only previews are resized/recompressed.

| Uploaded filename | Runtime path | Threat |
| --- | --- | ---: |
| `ChatGPT Image Sep 8, 2026, 07_10_00 PM (4).png` | `assets/maps/swamp_encampment.png` | 13 |
| `ChatGPT Image Sep 8, 2026, 07_10_00 PM (5).png` | `assets/maps/abandoned_carnival.png` | 14 |
| `ChatGPT Image Sep 8, 2026, 07_10_00 PM (3).png` | `assets/maps/neon_district.png` | 15 |
| `ChatGPT Image Sep 8, 2026, 07_10_01 PM (10).png` | `assets/maps/scrapyard_depot.png` | 16 |
| `ChatGPT Image Sep 8, 2026, 07_10_00 PM (6).png` | `assets/maps/canyon_missile_base.png` | 17 |
| `ChatGPT Image Sep 8, 2026, 07_10_00 PM (7).png` | `assets/maps/alpine_radar.png` | 18 |
| `ChatGPT Image Sep 8, 2026, 07_10_01 PM (8).png` | `assets/maps/offshore_platform.png` | 19 |
| `ChatGPT Image Sep 8, 2026, 07_10_01 PM (9).png` | `assets/maps/subterranean_terminus.png` | 20 |
| `ChatGPT Image Sep 8, 2026, 07_10_00 PM (2).png` | `assets/maps/volcanic_forge.png` | 21 |
| `ChatGPT Image Sep 8, 2026, 07_10_02 PM (11).png` | `assets/maps/containment_laboratory.png` | 22 |
| `ChatGPT Image Sep 8, 2026, 07_10_02 PM (12).png` | `assets/maps/lunar_outpost.png` | 23 |

## Historical asset documentation

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
