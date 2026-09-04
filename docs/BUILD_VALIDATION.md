# Build Validation — v0.4.1 Polished FOB Spatial Distribution & Globe Alignment

Parent baseline: **v0.4.0 Polished Sharded Global FOB World — supplied/accepted test-candidate baseline**  
Schema revision: **6**  
Candidate type: **Runtime-only XAMPP test candidate**

Static release-gate validation completed on the candidate source tree:

- **56/56 PHP files** pass `php -l` under PHP 8.4.23.
- **1/1 JavaScript file** passes `node --check` under Node 22.16.0.
- CSS structure remains balanced at **481 opening / 481 closing braces**.
- All **63 literal `__DIR__` include/require references** found by the release scanner resolve.
- All **78 literal `msw_url()` path prefixes** found by the release scanner resolve to present runtime routes/assets.
- Application version is **0.4.1** and both PHP schema authority and standalone install SQL report **schema revision 6**.
- Fresh-install SQL still contains **25 top-level statements** under the quote-aware statement scanner.
- FOB world capacity remains **144** and the database still owns exclusivity through `UNIQUE(world_id,slot_index)`.
- The new irregular layout contains exactly **144/144 unique anchors**.
- Anchor geometry produces **0 overlap pairs** under the validated **136×96 center-clearance envelope**, while the desktop marker footprint is 128×86.
- Biome/shard permutation was validated for Continental, Forest, Desert, Arctic and Sea across shard indices 001–003; all tested 57-member partial-layout hashes are distinct while each full 144-slot mapping remains bijective.
- Globe hotspots are centralized in `msw_fob_globe_hotspots()` at normalized source coordinates: Arctic **50/14**, Continental **27/35**, Forest **70.5/34**, Desert **62.5/55.5**, Sea **49.5/76.5**.
- Human placement, autonomous bot seeding and schema repair all call the same biome/shard-aware `msw_fob_slot_position()` authority.
- Update / Repair contains the v0.4.1 reflow pass that preserves `user_id`, `world_id`, `skin_key` and `slot_index` while recalculating only stored x/y.
- Confirm Installation now validates `fob_spatial_distribution` in addition to the inherited slot uniqueness and autonomous population checks.
- All **44 runtime image assets** are byte-identical to the v0.4.0 package; v0.4.1 modifies no artwork.
- All **13 FOB runtime assets** remain present at exact supplied dimensions: globe **1254×1254**, five overview maps **2000×2000**, seven FOB icons **256×171**.
- Package tree contains **0 nested/source archives** and **0 `source_assets/` directories**.
- Immediate raid authority still contains no attacker-cooldown gate; defender post-invasion protection remains authoritative.
- Staff FOB strike, standard Dispatch, autonomous commander, Mother Base coherence, PvP/social and local WorldServer-console code paths were not structurally replaced by this hotfix.

## Runtime acceptance boundary

This environment can validate source integrity, deterministic FOB geometry and package structure, but it cannot substitute for the user's live Apache + MariaDB/MySQL + browser stack. Database migration execution, real concurrent SQL behavior, actual browser positioning/scrolling and restart persistence must therefore be accepted on the XAMPP server PC.

For an existing v0.4.0 database, back it up, replace the runtime files, run **Update / Repair** to advance **schema 5 → 6**, then run **Confirm Installation** and require `fob_spatial_distribution = OK`. Complete every release-blocking scenario in `docs/XAMPP_TEST_PLAN.md` before promoting v0.4.1 from test candidate to stable.
