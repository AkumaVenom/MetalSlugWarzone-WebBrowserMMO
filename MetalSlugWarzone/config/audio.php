<?php
declare(strict_types=1);

/**
 * Supplied Sound And Music Library v1. Paths are relative to public_html.
 * Duration metadata is advisory; the browser uses decoded duration for seeking.
 * Source audio is preserved byte-for-byte; rocket uses a documented 44.1 kHz derivative.
 * See assets/audio/manifest.json for provenance. No remote audio dependencies.
 */
return [
    'tracks' => [
        'mother_base' => ['file' => 'assets/audio/music/mother-base.mp3', 'title' => 'Mother Base', 'duration' => 172.173061],
        'extra_mother_base' => ['file' => 'assets/audio/music/extra-mother-base.mp3', 'title' => 'Extra Mother Base', 'duration' => 156.551837],
        'missions_select' => ['file' => 'assets/audio/music/missions-select.mp3', 'title' => 'Mission Selection', 'duration' => 114.024490],
        'map_1' => ['file' => 'assets/audio/music/map-1.mp3', 'title' => 'Warzone Patrol I', 'duration' => 77.897143],
        'map_2' => ['file' => 'assets/audio/music/map-2.mp3', 'title' => 'Warzone Patrol II', 'duration' => 64.705306],
        'map_3' => ['file' => 'assets/audio/music/map-3.mp3', 'title' => 'Warzone Patrol III', 'duration' => 47.699592],
        'map_lunar' => ['file' => 'assets/audio/music/map-lunar.mp3', 'title' => 'Lunar Patrol', 'duration' => 87.040000],
        'battle_1' => ['file' => 'assets/audio/music/battle-1.mp3', 'title' => 'Battle Encounter I', 'duration' => 88.764082],
        'battle_2' => ['file' => 'assets/audio/music/battle-2.mp3', 'title' => 'Battle Encounter II', 'duration' => 123.506939],
        'battle_3' => ['file' => 'assets/audio/music/battle-3.mp3', 'title' => 'Battle Encounter III', 'duration' => 76.408163],
        'battle_lunar' => ['file' => 'assets/audio/music/battle-lunar.mp3', 'title' => 'Lunar Encounter', 'duration' => 61.257143],
        'boss_battle' => ['file' => 'assets/audio/music/boss-battle.mp3', 'title' => 'Boss Battle', 'duration' => 120.006531],
        'battle_lost' => ['file' => 'assets/audio/music/battle-lost.mp3', 'title' => 'Operation Failed', 'duration' => 7.262041],
        'boss_win' => ['file' => 'assets/audio/music/boss-win.mp3', 'title' => 'Boss Victory', 'duration' => 11.389388],
        'fob_battle' => ['file' => 'assets/audio/music/fob-battle.mp3', 'title' => 'FOB Battle', 'duration' => 14.315102],
        'fob_win' => ['file' => 'assets/audio/music/fob-win.mp3', 'title' => 'FOB Victory', 'duration' => 9.665306],
        'versus' => ['file' => 'assets/audio/music/versus.mp3', 'title' => 'Versus Operations', 'duration' => 77.191837],
        'mission_complete' => ['file' => 'assets/audio/music/mission-complete.mp3', 'title' => 'Mission Complete', 'duration' => 6.191020],
    ],
    'effects' => [
        'rifle' => ['file' => 'assets/audio/effects/rifle.wav', 'title' => 'Rifle Fire', 'duration' => 1.601610],
        'bullet' => ['file' => 'assets/audio/effects/bullet.wav', 'title' => 'Bullet Impact', 'duration' => 0.218277],
        'rocket' => ['file' => 'assets/audio/effects/rocket.wav', 'title' => 'Rocket Fire', 'duration' => 2.334218],
        'explosion' => ['file' => 'assets/audio/effects/explosion.wav', 'title' => 'Explosion', 'duration' => 2.377642],
        'death_1' => ['file' => 'assets/audio/effects/death-1.wav', 'title' => 'Defeat Voice I', 'duration' => 0.645714],
        'death_2' => ['file' => 'assets/audio/effects/death-2.wav', 'title' => 'Defeat Voice II', 'duration' => 1.106939],
        'enemy_winner' => ['file' => 'assets/audio/effects/enemy-winner.wav', 'title' => 'Enemy Victory', 'duration' => 1.277120],
        'flawless_win' => ['file' => 'assets/audio/effects/flawless-win.wav', 'title' => 'Flawless Victory', 'duration' => 1.706531],
    ],
];
