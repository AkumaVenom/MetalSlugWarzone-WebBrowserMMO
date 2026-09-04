# Build Validation — v0.3.3 Per-Warzone AI Operative Variety

Parent baseline: **runtime-accepted v0.3.1 Compact AI Warzone Labels**  
Schema revision: **4 (unchanged)**

Static pre-package validation:

- **46/46 PHP files** pass `php -l` under PHP 8.4.
- **1/1 JavaScript files** passes `node --check`.
- CSS structure is balanced at **401 opening / 401 closing braces**.
- Production simulation covers exactly **1,000 autonomous commanders** across the existing **167/167/167/167/166/166** warzone population.
- Every individual warzone contains **all six** accepted player-operative skins.
- Per-warzone operative counts differ by at most one: each map has approximately **27–28 Marco/Tarma/Eri/Fio/Nadia/Trevor commanders**.
- Fresh seeding uses a local per-map ordinal plus map offset; skin assignment is no longer correlated with the round-robin map slot.
- Existing-population Update / Repair performs the same per-warzone rotation in place and updates only autonomous `users.character_key` values.
- Confirm Installation validates six-skin diversity independently on each warzone.
- The compact v0.3.1 AI-label JavaScript/CSS contract remains byte-identical.
- Runtime delta versus accepted v0.3.1 is restricted to `_setup.php`, `config/app.php`, and `includes/schema.php`.
- **31/31 runtime images** are byte-identical to accepted v0.3.1.
- **46 literal internal PHP references** resolve.
- **33 runtime asset references** resolve.
- Schema revision remains **4**.
- Package tree contains **0 `source_assets/` directories** and **0 nested/source ZIP archives**.
- Final distributable ZIP integrity passes, followed by post-extraction PHP/JS/per-warzone-variety/runtime-only rechecks.

Runtime acceptance still requires the user's actual XAMPP/MariaDB database because static validation cannot execute the real migration against the persisted 1,000-bot population.
