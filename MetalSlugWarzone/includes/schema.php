<?php
declare(strict_types=1);
require_once __DIR__ . '/catalog.php';

const MSW_SCHEMA_REVISION = 7;

function msw_schema_statements(): array {
    return [
"CREATE TABLE IF NOT EXISTS schema_meta (
 meta_key VARCHAR(64) PRIMARY KEY,
 meta_value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS users (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS bot_commanders (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS player_resources (
 user_id BIGINT UNSIGNED PRIMARY KEY,
 common_metal BIGINT UNSIGNED NOT NULL DEFAULT 500,
 minor_metal BIGINT UNSIGNED NOT NULL DEFAULT 250,
 precious_metal BIGINT UNSIGNED NOT NULL DEFAULT 60,
 fuel BIGINT UNSIGNED NOT NULL DEFAULT 400,
 biological BIGINT UNSIGNED NOT NULL DEFAULT 160,
 strategic_devices INT UNSIGNED NOT NULL DEFAULT 0,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS inventory (
 user_id BIGINT UNSIGNED NOT NULL,
 item_key VARCHAR(64) NOT NULL,
 quantity INT UNSIGNED NOT NULL DEFAULT 0,
 PRIMARY KEY(user_id,item_key),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS base_sectors (
 user_id BIGINT UNSIGNED NOT NULL,
 sector_key VARCHAR(32) NOT NULL,
 level INT UNSIGNED NOT NULL DEFAULT 1,
 capacity INT UNSIGNED NOT NULL DEFAULT 10,
 score INT UNSIGNED NOT NULL DEFAULT 0,
 grade VARCHAR(4) NOT NULL DEFAULT 'E--',
 PRIMARY KEY(user_id,sector_key),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS units (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS security_backup_slots (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS mother_base_presence (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS mother_base_unit_positions (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS encounters (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS mission_progress (
 user_id BIGINT UNSIGNED NOT NULL,
 mission_key VARCHAR(64) NOT NULL,
 clears INT UNSIGNED NOT NULL DEFAULT 0,
 best_grade VARCHAR(4) DEFAULT NULL,
 last_cleared_at DATETIME DEFAULT NULL,
 PRIMARY KEY(user_id,mission_key),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS dispatch_missions (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS base_projects (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS fob_worlds (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 biome_key VARCHAR(32) NOT NULL,
 shard_index INT UNSIGNED NOT NULL,
 capacity SMALLINT UNSIGNED NOT NULL DEFAULT 144,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_fob_world_shard(biome_key,shard_index),
 INDEX idx_fob_world_biome(biome_key,shard_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS fob_world_memberships (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS fob_strike_dispatches (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS fob_raids (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 attacker_user_id BIGINT UNSIGNED NOT NULL,
 defender_user_id BIGINT UNSIGNED NOT NULL,
 attacker_snapshot_json MEDIUMTEXT NOT NULL,
 defender_snapshot_json MEDIUMTEXT NOT NULL,
 result ENUM('attacker_win','defender_win') NOT NULL,
 transfer_json TEXT NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(attacker_user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(defender_user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_fob_attacker(attacker_user_id,created_at),
 INDEX idx_fob_defender(defender_user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS pvp_matches (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS friend_requests (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 sender_user_id BIGINT UNSIGNED NOT NULL,
 receiver_user_id BIGINT UNSIGNED NOT NULL,
 status ENUM('pending','accepted','declined') NOT NULL DEFAULT 'pending',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uniq_friend_request(sender_user_id,receiver_user_id),
 FOREIGN KEY(sender_user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(receiver_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS friends (
 user_id BIGINT UNSIGNED NOT NULL,
 friend_user_id BIGINT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,friend_user_id),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(friend_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS direct_messages (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS strike_forces (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(40) NOT NULL UNIQUE,
 tag VARCHAR(6) NOT NULL UNIQUE,
 owner_user_id BIGINT UNSIGNED NOT NULL,
 description VARCHAR(500) NOT NULL DEFAULT '',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(owner_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS strike_force_members (
 strike_force_id BIGINT UNSIGNED NOT NULL,
 user_id BIGINT UNSIGNED NOT NULL UNIQUE,
 role ENUM('commander','officer','member') NOT NULL DEFAULT 'member',
 joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(strike_force_id,user_id),
 FOREIGN KEY(strike_force_id) REFERENCES strike_forces(id) ON DELETE CASCADE,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS login_attempts (
 ip_hash CHAR(64) NOT NULL,
 username_key VARCHAR(64) NOT NULL,
 attempts INT UNSIGNED NOT NULL DEFAULT 0,
 window_started DATETIME NOT NULL,
 locked_until DATETIME DEFAULT NULL,
 PRIMARY KEY(ip_hash,username_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
}


function msw_schema_bot_collision_reason(string $mapKey,int $x,int $y,int $padding=9): ?string {
    $maps=msw_map_catalog();$profiles=msw_map_collision_catalog();
    if(!isset($maps[$mapKey])) return 'boundary';
    $map=$maps[$mapKey];$profile=$profiles[$mapKey]??[];
    $bounds=$profile['bounds']??[24,42,(int)$map['w']-24,(int)$map['h']-18];
    [$minX,$minY,$maxX,$maxY]=array_map('intval',$bounds);
    if($x<$minX||$x>$maxX||$y<$minY||$y>$maxY) return 'boundary';
    foreach(($profile['rects']??[]) as $rect){
        if(count($rect)<4) continue;
        if($x>=(int)$rect[0]-$padding&&$x<=(int)$rect[2]+$padding&&$y>=(int)$rect[1]-$padding&&$y<=(int)$rect[3]+$padding) return (string)($rect[4]??'blocked');
    }
    return null;
}

function msw_schema_bot_positions(string $mapKey): array {
    static $cache=[];if(isset($cache[$mapKey])) return $cache[$mapKey];
    $maps=msw_map_catalog();$profiles=msw_map_collision_catalog();$map=$maps[$mapKey]??null;
    if(!$map) return $cache[$mapKey]=[[120,150]];
    $bounds=$profiles[$mapKey]['bounds']??[24,42,(int)$map['w']-24,(int)$map['h']-18];
    [$minX,$minY,$maxX,$maxY]=array_map('intval',$bounds);$out=[];
    // 36px spacing gives every bot a distinct legal starting point while still
    // preserving the exact 18px movement lattice used by human commanders.
    for($y=$minY;$y<=$maxY;$y+=36){
        for($x=$minX;$x<=$maxX;$x+=36){
            if(msw_schema_bot_collision_reason($mapKey,$x,$y)===null) $out[]=[$x,$y];
        }
    }
    if(!$out){$spawn=$map['spawn']??[120,150];$out=[[(int)$spawn[0],(int)$spawn[1]]];}
    return $cache[$mapKey]=$out;
}

function msw_schema_bot_grade_for_score(int $score): string {
    $thresholds=[[15,'E--'],[25,'E-'],[35,'E'],[45,'D'],[55,'C'],[65,'B'],[75,'A'],[84,'A+'],[91,'S'],[97,'S+'],[999,'S++']];
    foreach($thresholds as [$max,$grade]) if($score<=$max) return $grade;
    return 'S++';
}

function msw_seed_bot_population(mysqli $db,int $target=1000): void {
    $target=max(0,min(5000,$target));if($target===0) return;
    $maps=array_keys(msw_map_catalog());$chars=array_keys(msw_character_catalog());$bases=array_keys(msw_mother_base_catalog());
    if(!$maps||!$chars||!$bases) throw new RuntimeException('Bot population catalogs unavailable.');
    $personalities=['balanced','aggressive','collector','builder'];
    $sectors=array_keys(msw_sectors());
    $existing=[];$res=$db->query('SELECT bot_index,user_id FROM bot_commanders');
    while($row=$res->fetch_assoc()) $existing[(int)$row['bot_index']]=(int)$row['user_id'];
    // bot_index is the durable autonomous identity. Usernames are presentation
    // labels and must never make an upgrade destructive if a pre-v0.3.0 human
    // account happened to use a future AI-style name.
    $takenUsernames=[];$res=$db->query('SELECT username FROM users');
    while($row=$res->fetch_assoc()) $takenUsernames[mb_strtolower((string)$row['username'],'UTF-8')]=true;

    $userStmt=$db->prepare("INSERT INTO users(username,password_hash,is_bot,character_key,mother_base_key,level,xp,command_rank,gmp,base_power,base_grade,active_map,map_x,map_y,facing,last_seen) VALUES(?, '!AUTONOMOUS_COMMANDER_LOGIN_DISABLED!',1,?,?,1,0,1,2500,68,'E-',?,?,?, ?,NOW())");
    $botStmt=$db->prepare("INSERT INTO bot_commanders(user_id,bot_index,enabled,personality,activity,next_action_at) VALUES(?,?,1,?,'Patrolling assigned warzone',DATE_ADD(NOW(),INTERVAL ? SECOND))");
    $resourceStmt=$db->prepare('INSERT IGNORE INTO player_resources(user_id) VALUES(?)');
    $inventoryStmt=$db->prepare('INSERT IGNORE INTO inventory(user_id,item_key,quantity) VALUES(?,?,?)');
    $sectorStmt=$db->prepare('INSERT IGNORE INTO base_sectors(user_id,sector_key,level,capacity,score,grade) VALUES(?,?,1,10,?,?)');
    $unitStmt=$db->prepare('INSERT INTO units(owner_user_id,source_enemy_key,callsign,unit_class,affinity_type,level,hp,max_hp,attack,defense,speed,combat,rd,support,intel,medical,mess,security,grade,assignment,active_combat) VALUES(?,?,?,?,?,1,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1)');

    $seedUnits=[
        ['rifle','Vanguard Fox','infantry','ballistic',58,58,16,12,15,22,10,14,12,10,18,15],
        ['bazooka','Iron Jackal','heavy_infantry','explosive',70,70,23,13,9,26,12,16,9,8,14,17],
        ['shield','Steel Bear','heavy_infantry','ballistic',82,82,15,23,7,20,10,13,11,12,10,24],
    ];
    $db->begin_transaction();
    try{
        for($i=1;$i<=$target;$i++){
            if(isset($existing[$i])) continue;
            $mapIndex=($i-1)%count($maps);$mapKey=$maps[$mapIndex];$ordinal=intdiv($i-1,count($maps));
            $positions=msw_schema_bot_positions($mapKey);
            $expectedOnMap=intdiv($target,count($maps))+(($mapIndex<($target%count($maps)))?1:0);
            // Sample across the complete legal-position list instead of consuming
            // its first rows, keeping the initial population spatially distributed.
            $positionIndex=min(count($positions)-1,(int)floor(($ordinal*count($positions))/max(1,$expectedOnMap)));
            [$x,$y]=$positions[$positionIndex];
            $char=$chars[($ordinal+$mapIndex)%count($chars)];$base=$bases[(($i-1)*3)%count($bases)];$personality=$personalities[($i-1)%count($personalities)];
            $username='';
            foreach([sprintf('WarzoneAI%04d',$i),sprintf('MSWAI%04d',$i),sprintf('WarzoneBot%04d',$i),sprintf('MSWBot%04d',$i)] as $candidate){
                $normalized=mb_strtolower($candidate,'UTF-8');
                if(!isset($takenUsernames[$normalized])){$username=$candidate;$takenUsernames[$normalized]=true;break;}
            }
            if($username===''){
                $username='AI'.sprintf('%04d',$i).'_'.substr(hash('sha256','msw-bot-'.$i),0,8);
                $takenUsernames[mb_strtolower($username,'UTF-8')]=true;
            }
            $facing=(($i%2)===0)?'left':'right';
            $userStmt->bind_param('ssssiis',$username,$char,$base,$mapKey,$x,$y,$facing);
            $userStmt->execute();$uid=(int)$db->insert_id;
            $delay=5+(($i*7)%31);$botStmt->bind_param('iisi',$uid,$i,$personality,$delay);$botStmt->execute();
            $resourceStmt->bind_param('i',$uid);$resourceStmt->execute();
            foreach(['fulton'=>20,'fulton_plus'=>4,'cargo_fulton'=>2,'wormhole_fulton'=>0,'field_medkit'=>0,'trauma_kit'=>0,'nanomed_injector'=>0] as $item=>$qty){$inventoryStmt->bind_param('isi',$uid,$item,$qty);$inventoryStmt->execute();}
            foreach($sectors as $sector){$score=$sector==='combat'?68:0;$grade=$sector==='combat'?'E-':'E--';$sectorStmt->bind_param('isis',$uid,$sector,$score,$grade);$sectorStmt->execute();}
            foreach($seedUnits as $n=>$unit){
                [$enemy,$callsign,$class,$type,$hp,$maxHp,$atk,$def,$spd,$combat,$rd,$support,$intel,$medical,$mess,$security]=$unit;
                $best=max($combat,$rd,$support,$intel,$medical,$mess,$security);$grade=msw_schema_bot_grade_for_score($best);$assignment='combat';
                $unitStmt->bind_param('issssiiiiiiiiiiiiss',$uid,$enemy,$callsign,$class,$type,$hp,$maxHp,$atk,$def,$spd,$combat,$rd,$support,$intel,$medical,$mess,$security,$grade,$assignment);
                $unitStmt->execute();
            }
        }
        // Repair is idempotent: the production index range is always enabled and
        // remains flagged as autonomous, while any historical experimental rows
        // above the configured population are disabled rather than deleted.
        $stmt=$db->prepare('UPDATE bot_commanders SET enabled=1 WHERE bot_index BETWEEN 1 AND ?');$stmt->bind_param('i',$target);$stmt->execute();
        $stmt=$db->prepare('UPDATE users u JOIN bot_commanders b ON b.user_id=u.id SET u.is_bot=1 WHERE b.bot_index BETWEEN 1 AND ?');$stmt->bind_param('i',$target);$stmt->execute();

        // Production repair contract: every *individual warzone* must contain
        // a real mixture of the same selectable operative skins available to
        // human players. Earlier global modulo assignment correlated skin slot
        // with the round-robin map slot and produced one clone skin per map.
        // Repair existing populations in-place by ordering durable bot indexes
        // inside each current warzone and rotating the six-character catalog
        // independently per map. Only users.character_key is changed.
        $charCount=count($chars);
        if($charCount>0){
            $mapOrder=array_flip($maps);$mapOrdinals=[];
            $skinRows=$db->query("SELECT b.bot_index,b.user_id,u.active_map,u.character_key FROM bot_commanders b JOIN users u ON u.id=b.user_id WHERE b.bot_index BETWEEN 1 AND ".(int)$target." ORDER BY u.active_map,b.bot_index");
            $skinStmt=$db->prepare('UPDATE users SET character_key=? WHERE id=? AND is_bot=1');
            while($skinRow=$skinRows->fetch_assoc()){
                $botIndex=(int)$skinRow['bot_index'];$uid=(int)$skinRow['user_id'];$mapKey=(string)($skinRow['active_map']??'');
                $mapIndex=isset($mapOrder[$mapKey])?(int)$mapOrder[$mapKey]:(($botIndex-1)%count($maps));
                $groupKey=isset($mapOrder[$mapKey])?$mapKey:'__fallback_'.$mapIndex;
                $ordinal=(int)($mapOrdinals[$groupKey]??0);$mapOrdinals[$groupKey]=$ordinal+1;
                $characterKey=$chars[($ordinal+$mapIndex)%$charCount];
                if((string)$skinRow['character_key']===$characterKey) continue;
                $skinStmt->bind_param('si',$characterKey,$uid);$skinStmt->execute();
            }
        }

        $stmt=$db->prepare('UPDATE bot_commanders SET enabled=0 WHERE bot_index>?');$stmt->bind_param('i',$target);$stmt->execute();
        $db->commit();
    }catch(Throwable $e){$db->rollback();throw $e;}
}


/**
 * Idempotently place the durable autonomous population into the new sharded
 * FOB overview network. Human accounts are intentionally left unplaced so the
 * owner can make the globe/skin choice once after upgrading to v0.4.x.
 */
function msw_schema_seed_bot_fob_worlds(mysqli $db,int $target=1000): void {
    $biomes=msw_fob_biome_catalog();$biomeKeys=array_keys($biomes);$capacity=msw_fob_world_capacity();
    if(!$biomeKeys||$capacity<1)return;

    $worldIds=[];$occupied=[];
    $worldRows=$db->query('SELECT id,biome_key,shard_index,capacity FROM fob_worlds ORDER BY biome_key,shard_index');
    while($r=$worldRows->fetch_assoc()){
        $biome=(string)$r['biome_key'];$shard=(int)$r['shard_index'];$wid=(int)$r['id'];
        $worldIds[$biome][$shard]=$wid;$occupied[$wid]=[];
    }
    $memberRows=$db->query('SELECT world_id,slot_index FROM fob_world_memberships ORDER BY world_id,slot_index');
    while($r=$memberRows->fetch_assoc())$occupied[(int)$r['world_id']][(int)$r['slot_index']]=true;

    $selectWorld=$db->prepare('SELECT id FROM fob_worlds WHERE biome_key=? AND shard_index=? LIMIT 1');
    $insertWorld=$db->prepare('INSERT IGNORE INTO fob_worlds(biome_key,shard_index,capacity) VALUES(?,?,?)');
    $insertMember=$db->prepare('INSERT IGNORE INTO fob_world_memberships(user_id,world_id,skin_key,slot_index,x,y) VALUES(?,?,?,?,?,?)');
    $updateBase=$db->prepare('UPDATE users SET mother_base_key=? WHERE id=? AND is_bot=1');

    $bots=$db->query("SELECT b.bot_index,b.user_id,m.world_id,m.skin_key FROM bot_commanders b JOIN users u ON u.id=b.user_id LEFT JOIN fob_world_memberships m ON m.user_id=b.user_id WHERE b.enabled=1 AND b.bot_index BETWEEN 1 AND ".(int)$target." ORDER BY b.bot_index");
    while($row=$bots->fetch_assoc()){
        $idx=(int)$row['bot_index'];$uid=(int)$row['user_id'];
        if(!empty($row['world_id'])){
            $skin=(string)$row['skin_key'];
            if(isset(msw_mother_base_catalog()[$skin])){$updateBase->bind_param('si',$skin,$uid);$updateBase->execute();}
            continue;
        }
        $biomeKey=$biomeKeys[($idx-1)%count($biomeKeys)];$allowed=$biomes[$biomeKey]['skins'];
        $skinKey=(string)$allowed[(intdiv($idx-1,count($biomeKeys)))%max(1,count($allowed))];

        $worldId=0;$slot=-1;$shard=1;
        while($slot<0){
            if(!isset($worldIds[$biomeKey][$shard])){
                $insertWorld->bind_param('sii',$biomeKey,$shard,$capacity);$insertWorld->execute();
                $selectWorld->bind_param('si',$biomeKey,$shard);$selectWorld->execute();$w=$selectWorld->get_result()->fetch_assoc();
                if(!$w)throw new RuntimeException('Unable to seed autonomous FOB world.');
                $worldIds[$biomeKey][$shard]=(int)$w['id'];$occupied[(int)$w['id']]=[];
            }
            $worldId=(int)$worldIds[$biomeKey][$shard];$used=$occupied[$worldId]??[];
            for($candidate=0;$candidate<$capacity;$candidate++)if(!isset($used[$candidate])){$slot=$candidate;break;}
            if($slot<0)$shard++;
        }
        [$x,$y]=msw_fob_slot_position($slot,$biomeKey,$shard);
        $insertMember->bind_param('iisiii',$uid,$worldId,$skinKey,$slot,$x,$y);$insertMember->execute();
        if($insertMember->affected_rows===1)$occupied[$worldId][$slot]=true;
        $updateBase->bind_param('si',$skinKey,$uid);$updateBase->execute();
    }
}


/**
 * v0.4.1 spatial-layout repair.
 *
 * v0.4.0 stored valid exclusive slot identities but projected those slots onto
 * a visible 12x12 grid. Reflow keeps user_id, world_id, skin_key, slot_index,
 * shard identity and all progression intact while deterministically projecting
 * each slot onto the irregular blue-noise anchor constellation.
 */
function msw_schema_reflow_fob_world_positions(mysqli $db): void {
    $rows=$db->query('SELECT m.user_id,m.world_id,m.slot_index,m.x,m.y,w.biome_key,w.shard_index FROM fob_world_memberships m JOIN fob_worlds w ON w.id=m.world_id ORDER BY m.world_id,m.slot_index');
    $update=$db->prepare('UPDATE fob_world_memberships SET x=?,y=? WHERE user_id=? AND world_id=?');
    while($row=$rows->fetch_assoc()){
        $uid=(int)$row['user_id'];$worldId=(int)$row['world_id'];$slot=(int)$row['slot_index'];
        [$x,$y]=msw_fob_slot_position($slot,(string)$row['biome_key'],(int)$row['shard_index']);
        if((int)$row['x']===$x&&(int)$row['y']===$y) continue;
        $update->bind_param('iiii',$x,$y,$uid,$worldId);$update->execute();
    }
}

function msw_schema_column_exists(mysqli $db, string $table, string $column): bool {
    $stmt = $db->prepare('SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=? LIMIT 1');
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    return (bool)$stmt->get_result()->fetch_row();
}

function msw_schema_index_exists(mysqli $db, string $table, string $index): bool {
    $stmt = $db->prepare('SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND INDEX_NAME=? LIMIT 1');
    $stmt->bind_param('ss', $table, $index);
    $stmt->execute();
    return (bool)$stmt->get_result()->fetch_row();
}

function msw_install_schema(mysqli $db): void {
    foreach (msw_schema_statements() as $sql) $db->query($sql);

    // Non-destructive v1 -> v2 repair path.
    if (!msw_schema_column_exists($db, 'users', 'last_fob_attack_at')) {
        $db->query('ALTER TABLE users ADD COLUMN last_fob_attack_at DATETIME DEFAULT NULL AFTER last_seen');
    }
    if (!msw_schema_column_exists($db, 'users', 'fob_protection_until')) {
        $db->query('ALTER TABLE users ADD COLUMN fob_protection_until DATETIME DEFAULT NULL AFTER last_fob_attack_at');
    }
    if (!msw_schema_index_exists($db, 'users', 'idx_fob_protection')) {
        $db->query('ALTER TABLE users ADD INDEX idx_fob_protection(fob_protection_until)');
    }
    if (!msw_schema_index_exists($db, 'units', 'idx_dispatch_state')) {
        $db->query('ALTER TABLE units ADD INDEX idx_dispatch_state(owner_user_id,dispatched_until)');
    }
    if (!msw_schema_index_exists($db, 'direct_messages', 'idx_dm_sender')) {
        $db->query('ALTER TABLE direct_messages ADD INDEX idx_dm_sender(sender_user_id,created_at)');
    }
    // Non-destructive v2 -> v3 Mother Base visitation migration.
    if (!msw_schema_column_exists($db, 'users', 'mother_base_key')) {
        $db->query("ALTER TABLE users ADD COLUMN mother_base_key VARCHAR(64) NOT NULL DEFAULT 'land_dirt' AFTER character_key");
    }

    // Non-destructive v3 -> v4 autonomous commander migration.
    if (!msw_schema_column_exists($db, 'users', 'is_bot')) {
        $db->query("ALTER TABLE users ADD COLUMN is_bot TINYINT(1) NOT NULL DEFAULT 0 AFTER password_hash");
    }
    if (!msw_schema_index_exists($db, 'users', 'idx_bot_map')) {
        $db->query('ALTER TABLE users ADD INDEX idx_bot_map(is_bot,active_map)');
    }
    if (!msw_schema_column_exists($db, 'pvp_matches', 'match_mode')) {
        $db->query("ALTER TABLE pvp_matches ADD COLUMN match_mode ENUM('live','live_ai','snapshot','ai_sim') NOT NULL DEFAULT 'live' AFTER player2_id");
    } else {
        $db->query("ALTER TABLE pvp_matches MODIFY match_mode ENUM('live','live_ai','snapshot','ai_sim') NOT NULL DEFAULT 'live'");
    }

    // Keep the encounter enum forward-compatible with the production PvE surfaces.
    $db->query("ALTER TABLE encounters MODIFY context_type ENUM('field','mission','sidequest','trainer','boss') NOT NULL DEFAULT 'field'");

    msw_seed_bot_population($db,1000);
    msw_schema_seed_bot_fob_worlds($db,1000);
    msw_schema_reflow_fob_world_positions($db);

    $rev = (string)MSW_SCHEMA_REVISION;
    $stmt = $db->prepare("INSERT INTO schema_meta(meta_key,meta_value) VALUES('schema_revision',?) ON DUPLICATE KEY UPDATE meta_value=VALUES(meta_value)");
    $stmt->bind_param('s', $rev);
    $stmt->execute();
}
