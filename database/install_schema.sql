CREATE TABLE IF NOT EXISTS schema_meta (
 meta_key VARCHAR(64) PRIMARY KEY,
 meta_value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(24) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 is_bot TINYINT(1) NOT NULL DEFAULT 0,
 character_key VARCHAR(32) NOT NULL DEFAULT 'marco',
 mother_base_key VARCHAR(64) NOT NULL DEFAULT 'land_dirt',
 level INT UNSIGNED NOT NULL DEFAULT 1,
 xp BIGINT UNSIGNED NOT NULL DEFAULT 0,
 command_rank INT UNSIGNED NOT NULL DEFAULT 1,
 gmp BIGINT UNSIGNED NOT NULL DEFAULT 2500,
 base_power BIGINT UNSIGNED NOT NULL DEFAULT 0,
 base_grade VARCHAR(4) NOT NULL DEFAULT 'E--',
 active_map VARCHAR(64) DEFAULT NULL,
 map_x INT NOT NULL DEFAULT 120,
 map_y INT NOT NULL DEFAULT 150,
 facing ENUM('up','down','left','right') NOT NULL DEFAULT 'right',
 last_seen DATETIME DEFAULT NULL,
 last_fob_attack_at DATETIME DEFAULT NULL,
 fob_protection_until DATETIME DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_presence(active_map,last_seen),
 INDEX idx_bot_map(is_bot,active_map),
 INDEX idx_power(base_power),
 INDEX idx_fob_protection(fob_protection_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bot_commanders (
 user_id BIGINT UNSIGNED PRIMARY KEY,
 bot_index INT UNSIGNED NOT NULL UNIQUE,
 enabled TINYINT(1) NOT NULL DEFAULT 1,
 personality ENUM('balanced','aggressive','collector','builder') NOT NULL DEFAULT 'balanced',
 activity VARCHAR(160) NOT NULL DEFAULT 'Patrolling assigned warzone',
 last_enemy_key VARCHAR(64) DEFAULT NULL,
 field_battles BIGINT UNSIGNED NOT NULL DEFAULT 0,
 field_wins BIGINT UNSIGNED NOT NULL DEFAULT 0,
 recoveries BIGINT UNSIGNED NOT NULL DEFAULT 0,
 vehicle_recoveries BIGINT UNSIGNED NOT NULL DEFAULT 0,
 fob_attacks BIGINT UNSIGNED NOT NULL DEFAULT 0,
 fob_wins BIGINT UNSIGNED NOT NULL DEFAULT 0,
 pvp_battles BIGINT UNSIGNED NOT NULL DEFAULT 0,
 pvp_wins BIGINT UNSIGNED NOT NULL DEFAULT 0,
 next_action_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 last_action_at DATETIME DEFAULT NULL,
 lease_until DATETIME DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_bot_due(enabled,next_action_at,lease_until),
 INDEX idx_bot_activity(enabled,activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS player_resources (
 user_id BIGINT UNSIGNED PRIMARY KEY,
 common_metal BIGINT UNSIGNED NOT NULL DEFAULT 500,
 minor_metal BIGINT UNSIGNED NOT NULL DEFAULT 250,
 precious_metal BIGINT UNSIGNED NOT NULL DEFAULT 60,
 fuel BIGINT UNSIGNED NOT NULL DEFAULT 400,
 biological BIGINT UNSIGNED NOT NULL DEFAULT 160,
 strategic_devices INT UNSIGNED NOT NULL DEFAULT 0,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventory (
 user_id BIGINT UNSIGNED NOT NULL,
 item_key VARCHAR(64) NOT NULL,
 quantity INT UNSIGNED NOT NULL DEFAULT 0,
 PRIMARY KEY(user_id,item_key),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS base_sectors (
 user_id BIGINT UNSIGNED NOT NULL,
 sector_key VARCHAR(32) NOT NULL,
 level INT UNSIGNED NOT NULL DEFAULT 1,
 capacity INT UNSIGNED NOT NULL DEFAULT 10,
 score INT UNSIGNED NOT NULL DEFAULT 0,
 grade VARCHAR(4) NOT NULL DEFAULT 'E--',
 PRIMARY KEY(user_id,sector_key),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS units (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 owner_user_id BIGINT UNSIGNED NOT NULL,
 source_enemy_key VARCHAR(64) NOT NULL,
 callsign VARCHAR(64) NOT NULL,
 unit_class VARCHAR(32) NOT NULL,
 affinity_type VARCHAR(32) NOT NULL,
 level INT UNSIGNED NOT NULL DEFAULT 1,
 xp BIGINT UNSIGNED NOT NULL DEFAULT 0,
 hp INT UNSIGNED NOT NULL DEFAULT 50,
 max_hp INT UNSIGNED NOT NULL DEFAULT 50,
 attack INT UNSIGNED NOT NULL DEFAULT 10,
 defense INT UNSIGNED NOT NULL DEFAULT 10,
 speed INT UNSIGNED NOT NULL DEFAULT 10,
 combat INT UNSIGNED NOT NULL DEFAULT 10,
 rd INT UNSIGNED NOT NULL DEFAULT 10,
 support INT UNSIGNED NOT NULL DEFAULT 10,
 intel INT UNSIGNED NOT NULL DEFAULT 10,
 medical INT UNSIGNED NOT NULL DEFAULT 10,
 mess INT UNSIGNED NOT NULL DEFAULT 10,
 security INT UNSIGNED NOT NULL DEFAULT 10,
 grade VARCHAR(4) NOT NULL DEFAULT 'E--',
 assignment VARCHAR(32) NOT NULL DEFAULT 'reserve',
 active_combat TINYINT(1) NOT NULL DEFAULT 0,
 dispatched_until DATETIME DEFAULT NULL,
 recruited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(owner_user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_units_owner(owner_user_id,assignment),
 INDEX idx_active(owner_user_id,active_combat),
 INDEX idx_dispatch_state(owner_user_id,dispatched_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS security_backup_slots (
 user_id BIGINT UNSIGNED NOT NULL,
 slot_index TINYINT UNSIGNED NOT NULL,
 unit_id BIGINT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,slot_index),
 UNIQUE KEY uq_security_backup_unit(user_id,unit_id),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(unit_id) REFERENCES units(id) ON DELETE CASCADE,
 INDEX idx_security_backup_unit(unit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mother_base_presence (
 user_id BIGINT UNSIGNED PRIMARY KEY,
 base_owner_user_id BIGINT UNSIGNED NOT NULL,
 base_key VARCHAR(64) NOT NULL,
 x INT NOT NULL,
 y INT NOT NULL,
 facing ENUM('up','down','left','right') NOT NULL DEFAULT 'right',
 last_seen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(base_owner_user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_mb_presence(base_owner_user_id,base_key,last_seen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mother_base_unit_positions (
 unit_id BIGINT UNSIGNED PRIMARY KEY,
 owner_user_id BIGINT UNSIGNED NOT NULL,
 base_key VARCHAR(64) NOT NULL,
 x INT NOT NULL,
 y INT NOT NULL,
 facing ENUM('up','down','left','right') NOT NULL DEFAULT 'right',
 anchor_x INT NOT NULL,
 anchor_y INT NOT NULL,
 next_move_at DATETIME DEFAULT NULL,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(unit_id) REFERENCES units(id) ON DELETE CASCADE,
 FOREIGN KEY(owner_user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_mb_units(owner_user_id,base_key),
 INDEX idx_mb_move(owner_user_id,base_key,next_move_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS encounters (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 context_type ENUM('field','mission','sidequest','trainer','boss') NOT NULL DEFAULT 'field',
 context_key VARCHAR(64) NOT NULL,
 state_json MEDIUMTEXT NOT NULL,
 status ENUM('active','won','lost','recovered','retreated') NOT NULL DEFAULT 'active',
 version INT UNSIGNED NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_encounter_user(user_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mission_progress (
 user_id BIGINT UNSIGNED NOT NULL,
 mission_key VARCHAR(64) NOT NULL,
 clears INT UNSIGNED NOT NULL DEFAULT 0,
 best_grade VARCHAR(4) DEFAULT NULL,
 last_cleared_at DATETIME DEFAULT NULL,
 PRIMARY KEY(user_id,mission_key),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS dispatch_missions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 mission_key VARCHAR(64) NOT NULL,
 unit_ids_json TEXT NOT NULL,
 snapshot_power INT UNSIGNED NOT NULL,
 success_chance DECIMAL(5,4) NOT NULL,
 started_at DATETIME NOT NULL,
 finish_at DATETIME NOT NULL,
 resolved_at DATETIME DEFAULT NULL,
 result ENUM('pending','success','failure') NOT NULL DEFAULT 'pending',
 reward_json TEXT DEFAULT NULL,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_dispatch_due(user_id,result,finish_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS base_projects (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 project_key VARCHAR(64) NOT NULL,
 state ENUM('building','complete','claimed','cancelled') NOT NULL DEFAULT 'building',
 started_at DATETIME NOT NULL,
 finish_at DATETIME NOT NULL,
 payload_json TEXT DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_projects(user_id,state,finish_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS fob_worlds (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 biome_key VARCHAR(32) NOT NULL,
 shard_index INT UNSIGNED NOT NULL,
 capacity SMALLINT UNSIGNED NOT NULL DEFAULT 144,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_fob_world_shard(biome_key,shard_index),
 INDEX idx_fob_world_biome(biome_key,shard_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fob_world_memberships (
 user_id BIGINT UNSIGNED PRIMARY KEY,
 world_id BIGINT UNSIGNED NOT NULL,
 skin_key VARCHAR(64) NOT NULL,
 slot_index SMALLINT UNSIGNED NOT NULL,
 x SMALLINT UNSIGNED NOT NULL,
 y SMALLINT UNSIGNED NOT NULL,
 placed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(world_id) REFERENCES fob_worlds(id) ON DELETE CASCADE,
 UNIQUE KEY uq_fob_world_slot(world_id,slot_index),
 INDEX idx_fob_members_world(world_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fob_strike_dispatches (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 attacker_user_id BIGINT UNSIGNED NOT NULL,
 defender_user_id BIGINT UNSIGNED NOT NULL,
 world_id BIGINT UNSIGNED NOT NULL,
 unit_ids_json TEXT NOT NULL,
 attacker_snapshot_json MEDIUMTEXT NOT NULL,
 defender_snapshot_json MEDIUMTEXT NOT NULL,
 snapshot_power INT UNSIGNED NOT NULL,
 success_chance DECIMAL(5,4) NOT NULL,
 started_at DATETIME NOT NULL,
 finish_at DATETIME NOT NULL,
 resolved_at DATETIME DEFAULT NULL,
 result ENUM('pending','attacker_win','defender_win','protected_abort') NOT NULL DEFAULT 'pending',
 transfer_json TEXT DEFAULT NULL,
 raid_id BIGINT UNSIGNED DEFAULT NULL,
 FOREIGN KEY(attacker_user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(defender_user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(world_id) REFERENCES fob_worlds(id) ON DELETE CASCADE,
 INDEX idx_fob_dispatch_attacker(attacker_user_id,result,finish_at),
 INDEX idx_fob_dispatch_target(defender_user_id,result,finish_at),
 INDEX idx_fob_dispatch_world(world_id,result,finish_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fob_raids (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 attacker_user_id BIGINT UNSIGNED NOT NULL,
 defender_user_id BIGINT UNSIGNED NOT NULL,
 attacker_snapshot_json MEDIUMTEXT NOT NULL,
 defender_snapshot_json MEDIUMTEXT NOT NULL,
 result ENUM('attacker_win','defender_win') NOT NULL,
 transfer_json TEXT NOT NULL,
 retaliation_for_raid_id BIGINT UNSIGNED DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(attacker_user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(defender_user_id) REFERENCES users(id) ON DELETE CASCADE,
 UNIQUE KEY uq_fob_retaliation_source(retaliation_for_raid_id),
 INDEX idx_fob_attacker(attacker_user_id,created_at),
 INDEX idx_fob_defender(defender_user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pvp_matches (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 player1_id BIGINT UNSIGNED NOT NULL,
 player2_id BIGINT UNSIGNED NOT NULL,
 match_mode ENUM('live','live_ai','snapshot','ai_sim') NOT NULL DEFAULT 'live',
 current_turn_user_id BIGINT UNSIGNED NOT NULL,
 state_json MEDIUMTEXT NOT NULL,
 status ENUM('active','player1_win','player2_win','draw','cancelled') NOT NULL DEFAULT 'active',
 version INT UNSIGNED NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(player1_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(player2_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_pvp_p1(player1_id,status),
 INDEX idx_pvp_p2(player2_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS friend_requests (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 sender_user_id BIGINT UNSIGNED NOT NULL,
 receiver_user_id BIGINT UNSIGNED NOT NULL,
 status ENUM('pending','accepted','declined') NOT NULL DEFAULT 'pending',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uniq_friend_request(sender_user_id,receiver_user_id),
 FOREIGN KEY(sender_user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(receiver_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS friends (
 user_id BIGINT UNSIGNED NOT NULL,
 friend_user_id BIGINT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,friend_user_id),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(friend_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS direct_messages (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 sender_user_id BIGINT UNSIGNED NOT NULL,
 receiver_user_id BIGINT UNSIGNED NOT NULL,
 body VARCHAR(1000) NOT NULL,
 read_at DATETIME DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(sender_user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(receiver_user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_dm_receiver(receiver_user_id,created_at),
 INDEX idx_dm_sender(sender_user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS strike_forces (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(40) NOT NULL UNIQUE,
 tag VARCHAR(6) NOT NULL UNIQUE,
 owner_user_id BIGINT UNSIGNED NOT NULL,
 description VARCHAR(500) NOT NULL DEFAULT '',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(owner_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS strike_force_members (
 strike_force_id BIGINT UNSIGNED NOT NULL,
 user_id BIGINT UNSIGNED NOT NULL UNIQUE,
 role ENUM('commander','officer','member') NOT NULL DEFAULT 'member',
 joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(strike_force_id,user_id),
 FOREIGN KEY(strike_force_id) REFERENCES strike_forces(id) ON DELETE CASCADE,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempts (
 ip_hash CHAR(64) NOT NULL,
 username_key VARCHAR(64) NOT NULL,
 attempts INT UNSIGNED NOT NULL DEFAULT 0,
 window_started DATETIME NOT NULL,
 locked_until DATETIME DEFAULT NULL,
 PRIMARY KEY(ip_hash,username_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO schema_meta(meta_key,meta_value) VALUES ("schema_revision","8") ON DUPLICATE KEY UPDATE meta_value=VALUES(meta_value);
