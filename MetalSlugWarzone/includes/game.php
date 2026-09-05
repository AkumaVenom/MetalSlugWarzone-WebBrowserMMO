<?php
declare(strict_types=1);

function msw_initialize_player(int $uid): void {
    msw_stmt('INSERT IGNORE INTO player_resources(user_id) VALUES(?)', 'i', [$uid]);
    foreach (msw_sectors() as $key => $_) {
        msw_stmt('INSERT IGNORE INTO base_sectors(user_id,sector_key) VALUES(?,?)', 'is', [$uid,$key]);
    }
    foreach (['fulton'=>8,'fulton_plus'=>2,'cargo_fulton'=>1,'wormhole_fulton'=>0,'field_medkit'=>0,'trauma_kit'=>0,'nanomed_injector'=>0] as $item=>$qty) {
        msw_stmt('INSERT IGNORE INTO inventory(user_id,item_key,quantity) VALUES(?,?,?)', 'isi', [$uid,$item,$qty]);
    }

    $count = msw_one('SELECT COUNT(*) AS c FROM units WHERE owner_user_id=?', 'i', [$uid]);
    if ((int)($count['c'] ?? 0) === 0) {
        $seed = [
            ['rifle','Vanguard Fox','infantry','ballistic',1,58,58,16,12,15,22,10,14,12,10,18,15],
            ['bazooka','Iron Jackal','heavy_infantry','explosive',1,70,70,23,13,9,26,12,16,9,8,14,17],
            ['shield','Steel Bear','heavy_infantry','ballistic',1,82,82,15,23,7,20,10,13,11,12,10,24],
        ];
        foreach ($seed as $unit) {
            $score = max(array_slice($unit,10,7));
            $grade = msw_grade_for_score($score);
            msw_stmt(
                'INSERT INTO units(owner_user_id,source_enemy_key,callsign,unit_class,affinity_type,level,hp,max_hp,attack,defense,speed,combat,rd,support,intel,medical,mess,security,grade,assignment,active_combat) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1)',
                'issssiiiiiiiiiiiiiss',
                [$uid,...$unit,$grade,'combat']
            );
        }
    }
    msw_recalculate_base($uid);
}

function msw_resources(int $uid): array {
    return msw_one('SELECT * FROM player_resources WHERE user_id=?', 'i', [$uid]) ?: [];
}


function msw_sector_levels(int $uid): array {
    $levels=[];
    foreach(msw_all('SELECT sector_key,level FROM base_sectors WHERE user_id=?','i',[$uid]) as $row) {
        $levels[(string)$row['sector_key']]=max(1,(int)$row['level']);
    }
    foreach(array_keys(msw_sectors()) as $key) if(!isset($levels[$key])) $levels[$key]=1;
    return $levels;
}

function msw_sector_level(int $uid,string $sector): int {
    if(!isset(msw_sectors()[$sector])) return 1;
    $row=msw_one('SELECT level FROM base_sectors WHERE user_id=? AND sector_key=?','is',[$uid,$sector]);
    return max(1,(int)($row['level']??1));
}

function msw_requirements_met(int $uid,array $requirements,?array $levels=null): bool {
    $levels=$levels??msw_sector_levels($uid);
    foreach($requirements as $sector=>$required){
        if(!isset(msw_sectors()[$sector])) return false;
        if((int)($levels[$sector]??1)<max(1,(int)$required)) return false;
    }
    return true;
}

function msw_rd_recipe_requirements(array $recipe): array {
    $requirements=(array)($recipe['requirements']??[]);
    if(!$requirements && isset($recipe['rd'])) $requirements=['rd'=>(int)$recipe['rd']];
    return $requirements;
}

function msw_rd_recipe_unlocked(int $uid,array $recipe,?array $levels=null): bool {
    return msw_requirements_met($uid,msw_rd_recipe_requirements($recipe),$levels);
}

function msw_requirement_label(array $requirements): string {
    $names=[];
    foreach($requirements as $sector=>$level){
        $sectorMeta=msw_sectors()[$sector]??null;
        $label=$sectorMeta?(string)$sectorMeta['name']:strtoupper($sector);
        $names[]=$label.' Lv '.max(1,(int)$level);
    }
    return implode(' · ',$names);
}

function msw_inventory(int $uid): array {
    $rows = msw_all('SELECT item_key,quantity FROM inventory WHERE user_id=? ORDER BY item_key', 'i', [$uid]);
    $out=[];
    foreach($rows as $row) $out[$row['item_key']] = (int)$row['quantity'];
    return $out;
}

function msw_add_item(int $uid,string $item,int $qty): void {
    if ($qty <= 0) return;
    msw_stmt(
        'INSERT INTO inventory(user_id,item_key,quantity) VALUES(?,?,?) ON DUPLICATE KEY UPDATE quantity=quantity+VALUES(quantity)',
        'isi',
        [$uid,$item,$qty]
    );
}

function msw_consume_item(int $uid,string $item,int $qty=1): bool {
    if ($qty <= 0) return false;
    $stmt=msw_stmt(
        'UPDATE inventory SET quantity=quantity-? WHERE user_id=? AND item_key=? AND quantity>=?',
        'iisi',
        [$qty,$uid,$item,$qty]
    );
    return $stmt->affected_rows===1;
}

function msw_grant_resources(int $uid,array $reward): void {
    $allowed=['common_metal','minor_metal','precious_metal','fuel','biological','gmp'];
    foreach($reward as $key=>$amount) {
        $amount=max(0,(int)$amount);
        if($amount===0 || !in_array($key,$allowed,true)) continue;
        if($key==='gmp') {
            msw_stmt('UPDATE users SET gmp=gmp+? WHERE id=?','ii',[$amount,$uid]);
        } else {
            msw_stmt("UPDATE player_resources SET {$key}={$key}+? WHERE user_id=?",'ii',[$amount,$uid]);
        }
    }
}

function msw_spend_resources(int $uid,array $cost): bool {
    $allowed=['common_metal','minor_metal','precious_metal','fuel','biological'];
    $db=msw_db();
    $db->begin_transaction();
    try {
        $row=msw_one('SELECT * FROM player_resources WHERE user_id=? FOR UPDATE','i',[$uid]);
        if(!$row) throw new RuntimeException('Resource ledger missing.');
        foreach($cost as $key=>$amount) {
            if(in_array($key,$allowed,true) && (int)$row[$key] < max(0,(int)$amount)) {
                $db->rollback();
                return false;
            }
        }
        foreach($cost as $key=>$amount) {
            $amount=max(0,(int)$amount);
            if(in_array($key,$allowed,true) && $amount>0) {
                msw_stmt("UPDATE player_resources SET {$key}={$key}-? WHERE user_id=?",'ii',[$amount,$uid]);
            }
        }
        $db->commit();
        return true;
    } catch(Throwable $e){
        $db->rollback();
        throw $e;
    }
}

function msw_manufacture_item(int $uid,string $item,int $qty,array $cost): bool {
    if($uid<1 || $item==='' || $qty<=0) return false;
    $allowed=['common_metal','minor_metal','precious_metal','fuel','biological'];
    $normalized=[];
    foreach($cost as $key=>$amount){
        if(!in_array((string)$key,$allowed,true)) continue;
        $amount=max(0,(int)$amount);
        if($amount>0)$normalized[(string)$key]=$amount;
    }
    $db=msw_db();$db->begin_transaction();
    try{
        $row=msw_one('SELECT * FROM player_resources WHERE user_id=? FOR UPDATE','i',[$uid]);
        if(!$row) throw new RuntimeException('Resource ledger missing.');
        foreach($normalized as $key=>$amount){
            if((int)($row[$key]??0)<$amount){$db->rollback();return false;}
        }
        foreach($normalized as $key=>$amount){
            $st=msw_stmt("UPDATE player_resources SET {$key}={$key}-? WHERE user_id=? AND {$key}>=?",'iii',[$amount,$uid,$amount]);
            if($st->affected_rows!==1) throw new RuntimeException('Manufacturing resource ledger changed during settlement.');
        }
        msw_stmt(
            'INSERT INTO inventory(user_id,item_key,quantity) VALUES(?,?,?) ON DUPLICATE KEY UPDATE quantity=quantity+VALUES(quantity)',
            'isi',[$uid,$item,$qty]
        );
        $db->commit();
        return true;
    }catch(Throwable $e){
        try{$db->rollback();}catch(Throwable $_){}
        throw $e;
    }
}

function msw_recalculate_base(int $uid): void {
    $sectors=msw_sectors();
    $total=0;
    foreach($sectors as $key=>$meta){
        $stat=$meta['stat'];
        $row=msw_one("SELECT COALESCE(SUM({$stat}),0) s, COUNT(*) c FROM units WHERE owner_user_id=? AND assignment=?",'is',[$uid,$key]);
        $score=(int)($row['s']??0);
        $count=(int)($row['c']??0);
        $level=max(1,(int)floor($score/120)+1);
        $capacity=10+(($level-1)*5);
        $grade=msw_grade_for_score($count ? (int)round($score/$count) : 0);
        msw_stmt(
            'UPDATE base_sectors SET score=?,level=?,capacity=?,grade=? WHERE user_id=? AND sector_key=?',
            'iiisis',
            [$score,$level,$capacity,$grade,$uid,$key]
        );
        $total += $score * $level;
    }
    $avg=(int)min(110,floor(sqrt(max(0,$total))*2.2));
    msw_stmt('UPDATE users SET base_power=?,base_grade=? WHERE id=?','isi',[$total,msw_grade_for_score($avg),$uid]);
}

function msw_sector_has_capacity_for_assignment(int $uid,string $sector,int $unitId): bool {
    if ($sector === 'reserve') return true;
    if (!isset(msw_sectors()[$sector])) return false;
    $meta=msw_one('SELECT capacity FROM base_sectors WHERE user_id=? AND sector_key=?','is',[$uid,$sector]);
    $capacity=(int)($meta['capacity']??10);
    $count=msw_one('SELECT COUNT(*) c FROM units WHERE owner_user_id=? AND assignment=? AND id<>?','isi',[$uid,$sector,$unitId]);
    return (int)($count['c']??0) < $capacity;
}

function msw_security_backup_capacity(int $uid): int {
    return msw_sector_level($uid,'security')>=1 ? 2 : 0;
}

function msw_security_backup_slots(int $uid): array {
    $rows=msw_all(
        "SELECT s.slot_index,s.unit_id,u.callsign,u.source_enemy_key,u.unit_class,u.affinity_type,u.level,u.attack,u.defense,u.speed,u.combat,u.security,u.grade,u.assignment,u.dispatched_until FROM security_backup_slots s JOIN units u ON u.id=s.unit_id AND u.owner_user_id=s.user_id WHERE s.user_id=? ORDER BY s.slot_index",
        'i',[$uid]
    );
    $out=[];foreach($rows as $row)$out[(int)$row['slot_index']]=$row;
    return $out;
}

function msw_security_backup_candidates(int $uid): array {
    return msw_all(
        "SELECT id,callsign,source_enemy_key,unit_class,affinity_type,level,attack,defense,speed,combat,security,grade,dispatched_until FROM units WHERE owner_user_id=? AND assignment='security' AND unit_class IN ('infantry','heavy_infantry') ORDER BY security DESC,combat DESC,level DESC,id ASC",
        'i',[$uid]
    );
}

function msw_set_security_backup_slot(int $uid,int $slotIndex,int $unitId): void {
    $capacity=msw_security_backup_capacity($uid);
    if($slotIndex<1||$slotIndex>$capacity) throw new RuntimeException('That Security backup slot is not available.');
    if($unitId<=0){
        msw_stmt('DELETE FROM security_backup_slots WHERE user_id=? AND slot_index=?','ii',[$uid,$slotIndex]);
        return;
    }
    $unit=msw_one("SELECT id,assignment,unit_class,dispatched_until FROM units WHERE id=? AND owner_user_id=?",'ii',[$unitId,$uid]);
    if(!$unit) throw new RuntimeException('Security backup unit unavailable.');
    if((string)$unit['assignment']!=='security') throw new RuntimeException('Only staff assigned to the Security Team can join the backup detail.');
    if(!in_array((string)$unit['unit_class'],['infantry','heavy_infantry'],true)) throw new RuntimeException('Security backup party slots are reserved for personnel, not recovered vehicles or aircraft.');
    if(!empty($unit['dispatched_until'])&&strtotime((string)$unit['dispatched_until'])>time()) throw new RuntimeException('A dispatched staff member cannot join the active backup detail.');
    $db=msw_db();$db->begin_transaction();
    try{
        msw_stmt('DELETE FROM security_backup_slots WHERE user_id=? AND unit_id=?','ii',[$uid,$unitId]);
        msw_stmt('INSERT INTO security_backup_slots(user_id,slot_index,unit_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE unit_id=VALUES(unit_id),updated_at=CURRENT_TIMESTAMP','iii',[$uid,$slotIndex,$unitId]);
        $db->commit();
    }catch(Throwable $e){$db->rollback();throw $e;}
}

function msw_clear_security_backup_for_unit(int $uid,int $unitId): void {
    msw_stmt('DELETE FROM security_backup_slots WHERE user_id=? AND unit_id=?','ii',[$uid,$unitId]);
}

function msw_security_backup_fighters(int $uid): array {
    $capacity=msw_security_backup_capacity($uid);
    if($capacity<=0)return [];
    $rows=msw_all(
        "SELECT s.slot_index,u.id,u.callsign,u.source_enemy_key,u.unit_class,u.affinity_type,u.level,u.attack,u.defense,u.speed,u.combat,u.security,u.grade FROM security_backup_slots s JOIN units u ON u.id=s.unit_id AND u.owner_user_id=s.user_id WHERE s.user_id=? AND s.slot_index BETWEEN 1 AND ? AND u.assignment='security' AND u.unit_class IN ('infantry','heavy_infantry') AND (u.dispatched_until IS NULL OR u.dispatched_until<=NOW()) ORDER BY s.slot_index",
        'ii',[$uid,$capacity]
    );
    $enemyCatalog=msw_enemy_catalog();$out=[];
    foreach($rows as $row){
        $enemy=$enemyCatalog[(string)$row['source_enemy_key']]??$enemyCatalog['rifle'];
        $out[]=[
            'slot'=>(int)$row['slot_index'],'unit_id'=>(int)$row['id'],'name'=>(string)$row['callsign'],'class'=>(string)$row['unit_class'],'type'=>(string)$row['affinity_type'],
            'level'=>(int)$row['level'],'attack'=>max(6,(int)round(((int)$row['attack']*.34)+((int)$row['combat']*.16))),'defense'=>(int)$row['defense'],'speed'=>(int)$row['speed'],
            'security'=>(int)$row['security'],'grade'=>(string)$row['grade'],'sprite'=>(string)$enemy['sprite'],
        ];
    }
    return $out;
}

function msw_combat_units(int $uid): array {
    return msw_all(
        "SELECT * FROM units WHERE owner_user_id=? AND active_combat=1 AND (dispatched_until IS NULL OR dispatched_until<=NOW()) ORDER BY combat DESC, level DESC, id ASC LIMIT 4",
        'i',
        [$uid]
    );
}

function msw_heal_units(int $uid): void {
    msw_stmt('UPDATE units SET hp=max_hp WHERE owner_user_id=?','i',[$uid]);
}

function msw_add_unit_xp(int $uid,int $unitId,int $xp): void {
    $xp=max(0,$xp);
    if($xp===0) return;
    $row=msw_one('SELECT level,xp FROM units WHERE id=? AND owner_user_id=? FOR UPDATE','ii',[$unitId,$uid]);
    if(!$row) return;
    $level=(int)$row['level'];
    $total=(int)$row['xp']+$xp;
    while($level<99 && $total >= ($level*150)) $level++;
    msw_stmt('UPDATE units SET xp=?,level=? WHERE id=? AND owner_user_id=?','iiii',[$total,$level,$unitId,$uid]);
}

function msw_random_enemy_for_map(string $mapKey): string {
    $maps=msw_map_catalog();
    $list=$maps[$mapKey]['encounters'] ?? ['rifle'];
    return (string)$list[array_rand($list)];
}


function msw_map_collision_reason(string $mapKey,int $x,int $y,int $padding=9): ?string {
    $maps=msw_map_catalog();
    $profiles=msw_map_collision_catalog();
    if(!isset($maps[$mapKey])) return 'warzone boundary';
    $map=$maps[$mapKey];
    $profile=$profiles[$mapKey]??[];
    $bounds=$profile['bounds']??[24,42,(int)$map['w']-24,(int)$map['h']-18];
    [$minX,$minY,$maxX,$maxY]=array_map('intval',$bounds);
    if($x<$minX || $x>$maxX || $y<$minY || $y>$maxY) return 'warzone boundary';
    foreach(($profile['rects']??[]) as $rect){
        if(count($rect)<4) continue;
        $x1=(int)$rect[0]-$padding;$y1=(int)$rect[1]-$padding;
        $x2=(int)$rect[2]+$padding;$y2=(int)$rect[3]+$padding;
        if($x>=$x1 && $x<=$x2 && $y>=$y1 && $y<=$y2) return (string)($rect[4]??'blocked terrain');
    }
    return null;
}

function msw_map_path_collision(string $mapKey,int $fromX,int $fromY,int $toX,int $toY): ?string {
    $distance=max(abs($toX-$fromX),abs($toY-$fromY));
    $samples=max(1,(int)ceil($distance/4));
    for($i=1;$i<=$samples;$i++){
        $t=$i/$samples;
        $x=(int)round($fromX+(($toX-$fromX)*$t));
        $y=(int)round($fromY+(($toY-$fromY)*$t));
        $reason=msw_map_collision_reason($mapKey,$x,$y);
        if($reason!==null) return $reason;
    }
    return null;
}

function msw_map_spawn(string $mapKey): array {
    $map=msw_map_catalog()[$mapKey]??null;
    if(!$map) return [120,150];
    $spawn=$map['spawn']??[(int)floor((int)$map['w']/2),(int)floor((int)$map['h']/2)];
    return [(int)$spawn[0],(int)$spawn[1]];
}

function msw_map_safe_position(string $mapKey,int $x,int $y): array {
    $maps=msw_map_catalog();
    $profiles=msw_map_collision_catalog();
    if(!isset($maps[$mapKey])) return [$x,$y];
    $map=$maps[$mapKey];
    $bounds=$profiles[$mapKey]['bounds']??[24,42,(int)$map['w']-24,(int)$map['h']-18];
    [$minX,$minY,$maxX,$maxY]=array_map('intval',$bounds);
    $x=max($minX,min($maxX,$x));
    $y=max($minY,min($maxY,$y));
    if(msw_map_collision_reason($mapKey,$x,$y)===null) return [$x,$y];

    // Find the nearest legal foot point on the movement grid. This repairs old
    // persisted coordinates after a map replacement without resetting progress.
    for($radius=18;$radius<=432;$radius+=18){
        for($dx=-$radius;$dx<=$radius;$dx+=18){
            foreach([-$radius,$radius] as $dy){
                $cx=max($minX,min($maxX,$x+$dx));$cy=max($minY,min($maxY,$y+$dy));
                if(msw_map_collision_reason($mapKey,$cx,$cy)===null) return [$cx,$cy];
            }
        }
        for($dy=-$radius+18;$dy<=$radius-18;$dy+=18){
            foreach([-$radius,$radius] as $dx){
                $cx=max($minX,min($maxX,$x+$dx));$cy=max($minY,min($maxY,$y+$dy));
                if(msw_map_collision_reason($mapKey,$cx,$cy)===null) return [$cx,$cy];
            }
        }
    }
    [$sx,$sy]=msw_map_spawn($mapKey);
    if(msw_map_collision_reason($mapKey,$sx,$sy)===null) return [$sx,$sy];
    return [$minX,$minY];
}

function msw_active_encounter(int $uid): ?array {
    return msw_one("SELECT id,context_type,context_key,version FROM encounters WHERE user_id=? AND status='active' ORDER BY id DESC LIMIT 1",'i',[$uid]);
}

function msw_presence_touch(int $uid,string $map,int $x,int $y,string $facing): void {
    msw_stmt('UPDATE users SET active_map=?,map_x=?,map_y=?,facing=?,last_seen=NOW() WHERE id=?','siisi',[$map,$x,$y,$facing,$uid]);
}

function msw_presence(int $uid,string $map): array {
    $ttl=max(10,min(300,(int)msw_config('presence_ttl_seconds')));
    $limit=max(80,min(300,(int)(msw_config('bot_presence_limit_per_map')??240)));
    return msw_all(
        "SELECT u.id,u.username,u.character_key,u.map_x,u.map_y,u.facing,u.base_grade,u.is_bot,b.bot_index,b.activity FROM users u LEFT JOIN bot_commanders b ON b.user_id=u.id WHERE u.id<>? AND u.active_map=? AND ((u.is_bot=0 AND u.last_seen>=DATE_SUB(NOW(),INTERVAL {$ttl} SECOND)) OR (u.is_bot=1 AND b.enabled=1)) ORDER BY u.is_bot DESC,b.bot_index ASC,u.username ASC LIMIT {$limit}",
        'is',
        [$uid,$map]
    );
}

function msw_user_xp_floor(int $level): int {
    $level=max(1,$level);
    return (int)(($level-1)*($level-1)*180);
}

function msw_user_xp_next(int $level): int {
    $level=max(1,$level);
    return (int)($level*$level*180);
}

function msw_user_progress(array $user): array {
    $level=max(1,(int)($user['level']??1));
    $total=max(0,(int)($user['xp']??0));
    $floor=msw_user_xp_floor($level);
    $next=msw_user_xp_next($level);
    $required=max(1,$next-$floor);
    $current=max(0,min($required,$total-$floor));
    $percent=$level>=999 ? 100.0 : max(0.0,min(100.0,($current/$required)*100));
    return [
        'level'=>$level,
        'total_xp'=>$total,
        'floor_xp'=>$floor,
        'next_xp'=>$next,
        'current_xp'=>$current,
        'required_xp'=>$required,
        'percent'=>$percent,
        'command_rank'=>max(1,(int)($user['command_rank']??(int)ceil($level/3))),
    ];
}

function msw_commander_fighter(int $uid): array {
    $user=msw_one('SELECT id,username,character_key,level,xp,command_rank FROM users WHERE id=?','i',[$uid]);
    if(!$user) throw new RuntimeException('Commander profile unavailable.');
    $characters=msw_character_catalog();
    $character=$characters[$user['character_key']]??reset($characters);
    $level=max(1,(int)$user['level']);
    $step=$level-1;
    $maxHp=70+(int)floor($step*3.2);
    $attack=18+(int)floor($step*1.15);
    $defense=14+(int)floor($step*.90);
    $speed=14+(int)floor($step*.38);
    return [
        'user_id'=>$uid,
        'unit_id'=>0,
        'name'=>(string)$character['name'],
        'callsign'=>(string)$character['name'],
        'character_key'=>(string)$user['character_key'],
        'class'=>'infantry',
        'unit_class'=>'infantry',
        'type'=>'ballistic',
        'affinity_type'=>'ballistic',
        'level'=>$level,
        'hp'=>$maxHp,
        'max_hp'=>$maxHp,
        'attack'=>$attack,
        'defense'=>$defense,
        'speed'=>$speed,
        'progress'=>msw_user_progress($user),
    ];
}

function msw_level_up_user(int $uid,int $xp): array {
    $xp=max(0,$xp);
    $before=msw_one('SELECT level,xp,command_rank FROM users WHERE id=?','i',[$uid]);
    if(!$before) return ['gained'=>0,'before_level'=>1,'after_level'=>1,'leveled'=>false];
    $beforeLevel=max(1,(int)$before['level']);
    if($xp>0) msw_stmt('UPDATE users SET xp=xp+? WHERE id=?','ii',[$xp,$uid]);
    $user=msw_one('SELECT level,xp,command_rank FROM users WHERE id=?','i',[$uid]);
    if(!$user) return ['gained'=>$xp,'before_level'=>$beforeLevel,'after_level'=>$beforeLevel,'leveled'=>false];
    $level=max(1,(int)$user['level']);
    $total=max(0,(int)$user['xp']);
    while($level<999 && $total>=msw_user_xp_next($level)) $level++;
    $rank=max((int)$user['command_rank'],(int)ceil($level/3));
    msw_stmt('UPDATE users SET level=?,command_rank=? WHERE id=?','iii',[$level,$rank,$uid]);
    return [
        'gained'=>$xp,
        'before_level'=>$beforeLevel,
        'after_level'=>$level,
        'leveled'=>$level>$beforeLevel,
        'progress'=>msw_user_progress(['level'=>$level,'xp'=>$total,'command_rank'=>$rank]),
    ];
}
