<?php
declare(strict_types=1);

return [
    'name' => 'Metal Slug Warzone',
    'version' => '0.3.4',
    'timezone' => 'Australia/Melbourne',
    'db' => [
        'host' => getenv('MSW_DB_HOST') ?: '127.0.0.1',
        'port' => (int)(getenv('MSW_DB_PORT') ?: 3306),
        'name' => getenv('MSW_DB_NAME') ?: 'metal_slug_warzone',
        'user' => getenv('MSW_DB_USER') ?: 'root',
        'pass' => getenv('MSW_DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'presence_ttl_seconds' => 45,
    'movement_min_interval_ms' => 80,
    'encounter_chance' => 7,
    'strategic_project_seconds' => 86400,
    'dispatch_tick_seconds' => 60,
    'fob_attack_cooldown_seconds' => 300,
    'fob_defender_protection_seconds' => 900,
    'bot_population_enabled' => true,
    'bot_population_size' => 1000,
    'bot_presence_limit_per_map' => 240,
    'bot_action_min_seconds' => 12,
    'bot_action_max_seconds' => 32,
    'bot_roster_cap' => 48,
];
