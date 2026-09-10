# Sound System — Metal Slug Warzone v0.9.0

v0.9.0 integrates the supplied **Sound And Music Library v1** throughout the
shared game interface: **18 music tracks and 8 battle effects**, with account
sound preferences and a separate saved playback position for each music track.
The release is based on the uploaded **v0.8.7 Characters V2 Complete** package.

## Player controls

Use the global **Sound** button at the bottom left to open the audio controls on
any rendered game page. **Mute all / Unmute** controls both channels. Music and
effects have independent volume sliders. New accounts start with sound **enabled**, music at **55%** and
effects at **75%**. Adjustments belong to the signed-in account, so a saved mute
choice and both volume levels return on later visits.

The panel shows the current track, elapsed/total time and a seek slider once media
metadata is available. **Pause music / Play music** temporarily controls music
without muting battle effects; this pause is tab-session state, not an account
preference. **Test effect** plays a rifle sample at the chosen effects level.
**Retry audio / save** retries the current recording and pending account save. The status
line distinguishes loaded/saved settings, a pending save and unavailable account
saving. Guest mute and volume choices are kept on that browser device and remain
separate from signed-in account preferences.

Browsers may require a click, tap or keyboard interaction before they allow sound.
An enabled account therefore does not guarantee audible playback before the first
interaction with a new browser session. Use the Sound control or interact with the
page to allow playback. Browser autoplay restrictions do not change the saved mute
preference. A muted account stays muted after navigation, refresh and sign-in.

Each music track keeps its own playback position. Returning to a track restores
its position instead of starting it again from the beginning. Refreshing a page
also restores the current track near the last saved position. The controller
checkpoints to the account approximately every **12 seconds**, and attempts
additional saves when a track changes, a page is left, the tab becomes hidden or
the player logs out. A recent tab-session cache, accepted for up to five minutes,
helps bridge navigation and a temporary failed request. An abrupt browser/device
shutdown or lost network can leave the account at its last successful checkpoint.
Normal page
navigation still reloads a PHP document, so a brief audio gap while the next page
loads is expected; this is position continuity across pages, not a continuous
single-page audio stream.

Hidden tabs pause music and cancel queued effects. An audio-ownership lease lets
the foreground game tab take over and limits duplicate music from tabs using the
same account and installation in the same browser. Returning to the page resumes
its own theme and saved position; it does not replay attacks missed while hidden.

## Music placement

The supplied filenames determine their principal use. Related pages share a
suitable supplied theme where there is no dedicated music file.

| Game area | Music |
| --- | --- |
| Public entry, authentication, command dashboard and Mother Base | Mother Base |
| Staff, R&D, social pages, rankings, strategic and FOB planning | Extra Mother Base |
| Warzone selection, Combat Missions, Side Operations, Boss Operations and Dispatch menus | Mission Selection |
| Ground warzones | Warzone Patrol I, II or III, consistently assigned by map |
| Lunar Outpost exploration | Lunar Patrol |
| Ground encounters and mission fights | Battle Encounter I, II or III, selected from the deployed map |
| Lunar Outpost encounters | Lunar Encounter |
| Boss fights, including bosses in Combat Missions | Boss Battle |
| Live PvP, quick duels and rival commander menus | Versus Operations |
| Active live PvP fights | Battle Encounter II |
| Staff Dispatch battle playback | The deployment map's encounter theme; boss targets use Boss Battle |
| FOB invasion battle playback | FOB Battle |
| Successful FOB playback | FOB Victory |
| Boss victory | Boss Victory |
| Other successful operations | Mission Complete |
| Defeat or failed operation | Operation Failed |

Result themes play as short outcome cues and then return to a suitable background
theme. Lunar-only music is reserved for Lunar Outpost. A boss encounter takes
priority over the ordinary encounter theme, including lunar bosses.

## Battle effects and authority

Rifle fire, bullet impacts, rocket launches, explosions, defeat voices, enemy
victory and a flawless-win voice use the supplied effects. Weapon categories
reuse a suitable effect where the library has no exact weapon recording. Sound
follows committed battle events and existing playback timing in regular battles,
PvP, Dispatch replays and FOB replays.

Audio is presentation only. The browser does not recalculate damage, outcomes,
captures, rewards, staff progress or resource transfers. Stable event identities
prevent normal refresh/polling from replaying the same committed effect as a new
attack. Muted, browser-blocked or hidden historical effects are not queued to
burst after sound becomes available. Effects load on demand and are limited to
eight simultaneous voices.

Dispatch and FOB effects follow deterministic frames derived from the settled
report. **Skip** cancels the old attack sequence and presents the result; an
explicit **Replay** intentionally plays a fresh presentation of that same report.
Reduced-motion mode keeps its instant visual result while pacing the audio.
Neither control settles the operation again.

The flawless-win voice uses a conservative condition: the winner must finish on
the first turn at full HP; PvE additionally requires no enemy hit and all backups
intact. A healed full HP bar after a longer fight does not qualify. This avoids
calling such a recovered victory flawless when prior damage is not provably absent.

## Installation and account persistence

Fresh installation and **Update / Repair** use **schema revision 10**. Upgrade an
existing installation with the complete release files, retain its database/host
configuration, and run Update / Repair. This adds the audio persistence structures
without resetting accounts, characters, inventory, AI commanders or saved battles.
The uploaded v0.8.7 configuration is preserved in this package; no unrelated
database-password change is part of the sound update.

The account store persists master mute, music volume, effects volume and playback
positions keyed by track ID. Browser-side state supports immediate navigation
restoration and temporary connectivity failures; the authenticated account store
provides durable preferences. `audio_preferences` stores the account mute,
volumes and settings revision; `audio_track_positions` stores positions by
`(user_id, track_id)` with an independent revision for each track. Separate
revision checks prevent stale tab settings from overwriting a newer mute/volume
choice and prevent old checkpoints from replacing a newer accepted position. A
stale position is discarded without cancelling a valid preference change in the
same request. Position-only checkpoints do not rewrite mute or volume. Account identity is determined by the existing server session,
not a submitted user ID. Preference writes use the existing
request-authentication and CSRF protections and accept only known catalog tracks
and bounded numeric values.

An installation that has not completed the schema upgrade remains playable, but
the sound controls report that account saving is unavailable. Run Update / Repair
to enable durable saves; page rendering does not attempt to create database tables.
The two new tables are created only through normal installation/repair. Repeating
repair preserves existing audio rows. Unconfigured accounts need no backfill and
use the enabled defaults until their first save.

Tracks are local files under `public_html/assets/audio/`; there are no remote
music streams, external audio credentials or third-party music dependencies.
Relative paths retain compatibility with an installation under an XAMPP subfolder.
Audio settings do not run or control the automatic world simulation.

## Runtime asset catalog

`public_html/config/audio.php` returns two associative arrays: `tracks` and
`effects`. Each ID maps to a `file` path relative to `public_html`, a display
`title`, and an advisory `duration` in seconds. Track IDs also identify persisted
playback positions, so keep IDs stable when replacing recordings.

All 26 originals are preserved. The rocket WAV source is 192 kHz mono; playback
uses a 44.1 kHz mono PCM16 derivative to reduce payload and decoding work. The
original remains at `assets/audio/source/rocket-original-192khz.wav`. The other
25 active recordings are byte-identical to their supplied files. No artwork or
character sprite was generated, replaced or resized for this update.

See [ASSET_MANIFEST.md](ASSET_MANIFEST.md) for all source-to-runtime mappings and
`public_html/assets/audio/manifest.json` for exact durations, formats, byte counts,
SHA-256 hashes and derivative provenance.

## Validation and host acceptance

Asset preparation verified all 26 originals with FFprobe and full FFmpeg decode,
compared copied originals using SHA-256, and fully decoded the rocket derivative.
Executed application checks for this release include:

| Check | Result |
| --- | --- |
| Audio controller with deterministic DOM/media stubs | 30 checks passed |
| Dispatch/FOB replay audio integration | 13 checks passed |
| Existing map-presence, world-runtime, Windows task-adapter and Dispatch UI regressions | 309 checks passed |
| Combined JavaScript regression checks above | 352 checks passed |
| Schema source parity | 27 table definitions: 2 new audio tables; 25 existing definitions unchanged |

The controller checks exercise settings/position races, muted/blocked events,
media metadata seeking, ownership and bounded effects through stubs. Schema
parity is a source comparison, not an executed migration. Source routing applies
the first-turn/full-HP flawless conditions above; no live battle acceptance is
claimed from those source checks.

PHP/MySQL were unavailable in this environment. Browser-preview access was also
rejected, so no real-browser listening/layout check or live database install,
upgrade, account-persistence or network test is claimed. The media decode and
stub-based checks do not replace those host checks. See
[BUILD_VALIDATION.md](BUILD_VALIDATION.md) for reproduction details and limits.

After installation or Update / Repair, verify on the actual XAMPP/server host:

1. Sign in to a new or previously unconfigured account. Confirm sound is enabled,
   music is 55%, effects are 75%, and the first interaction starts permitted audio.
2. Let Mother Base music run for at least 20 seconds, navigate to another page
   using that theme, and refresh. Confirm its position continues near the saved
   point. Visit a different theme and return; each track should retain its own
   position.
3. Change both sliders, mute, navigate, refresh, log out and sign in again.
   Confirm those choices return. Use a second account to verify independent
   preferences. Sign in from another browser to check server-side persistence.
4. Visit public entry/authentication pages, each navigation section, all 17
   warzones and Lunar Outpost. Confirm the global controls fit the layout and
   lunar-only themes remain confined to lunar gameplay.
5. Fight with ballistic and explosive attacks, support units and enemy counters.
   Confirm impacts line up with combat, misses do not produce hit-only impacts,
   and a refreshed result does not repeat an old attack as a new event.
6. Complete and lose regular missions, lunar encounters and boss fights; verify
   the appropriate result themes and return to background music.
7. Test both players in a live PvP match, then Dispatch and FOB report replay,
   Replay and Skip. Audio must follow presentation without changing settled
   results, rewards or resources.
8. Test Pause music, seek, Test effect and Retry audio / save. Music pause should leave
   effects active, and seeking should save the selected position. A zero effects
   level should silence current and future effects.
9. Test multiple game tabs, a hidden tab, browser Back/Forward and a temporary
   connection interruption. Confirm there is no accumulating sound overlap and
   that controls remain usable when a save or audio request fails. Change mute in
   one tab while another has an older page open; confirm the old page does not
   overwrite the newer saved choice.
10. Heal after taking damage in a longer battle and win with full HP. Confirm the
    flawless-win voice is absent; then test a first-turn, undamaged win.
11. Repeat the key mute, refresh and combat checks in your normal Edge/Chrome and
    Firefox browsers. Also test touch controls if mobile play is required.
