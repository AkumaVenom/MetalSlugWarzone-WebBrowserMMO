# Build Validation — v0.3.4 Level-1 Fulton Manufacturing

Parent baseline: **v0.3.3 Per-Warzone AI Operative Variety public baseline**  
Schema revision: **4 (unchanged)**

Static pre-package validation completed on the packaged candidate source tree:

- **46/46 PHP files** pass `php -l` under PHP **8.4.23**.
- **1/1 JavaScript files** passes `node --check` under Node **v22.16.0**.
- CSS structure remains balanced at **401 opening / 401 closing braces**.
- R&D catalog contains `fulton` as the first/basic manufacturing tier at **R&D 1**.
- Basic recipe cost/yield is exactly **60 Common Metal + 40 Fuel → 4 Fulton Recovery units**.
- At R&D Level 1, the static lock calculation exposes **only** the basic `fulton` recipe; `fulton_plus`, `cargo_fulton` and `wormhole_fulton` remain locked.
- Existing higher-tier manufacturing thresholds remain exactly **Fulton+ 4 / Cargo Fulton 8 / Wormhole Fulton 15**.
- Combat Fulton capability thresholds remain exactly **1 / 4 / 8 / 15**, preserving battle-side validation.
- Schema revision remains **4** and no SQL/database migration is introduced.
- Runtime delta versus v0.3.3 is limited to `public_html/config/app.php` and `public_html/includes/catalog.php`; all database/schema runtime files are unchanged.
- Existing starter inventory and Field Contract reward definitions are unchanged.
- v0.3.3 autonomous commander population/skin-repair logic is unchanged.
- **31/31 runtime images** are byte-identical to the v0.3.3 baseline.
- **45 unique literal internal PHP references** resolve.
- **33 unique literal runtime asset references** resolve.
- Package tree contains **0 `source_assets/` directories** and **0 nested/source ZIP archives**.

Runtime acceptance still requires the user's actual XAMPP/MariaDB environment to verify the live R&D button state, authoritative resource debit, persistent inventory gain and normal combat consumption.
