<?php
declare(strict_types=1);

/**
 * Persistent autonomous commander runtime.
 *
 * Bots are real rows in users with is_bot=1 and dedicated bot_commanders state.
 * They never receive a login session. Simulation is request-driven and bounded:
 * map presence polls advance only a small leased batch, so 1,000 persistent
 * commanders do not become 1,000 PHP jobs per request.
 */

function msw_is_bot_user(int $uid): bool {
    $row=msw_one('SELECT is_bot FROM users WHERE id=?','i',[$uid]);
    return (int)($row['is_bot']??0)===1;
}

function msw_bot_row(int $uid): ?array {
    return msw_one('SELECT * FROM bot_commanders WHERE user_id=? AND enabled=1','i',[$uid]);
}

function msw_bot_population_summary(): array {
    $rows=msw_all("SELECT u.active_map map_key,COUNT(*) total FROM bot_commanders b JOIN users u ON u.id=b.user_id WHERE b.enabled=1 GROUP BY u.active_map ORDER BY u.active_map");
    $maps=[];$total=0;foreach($rows as $row){$maps[(string)$row['map_key']]=(int)$row['total'];$total+=(int)$row['total'];}
    return ['total'=>$total,'maps'=>$maps];
}

function msw_bot_set_activity(int $uid,string $activity,?string $enemyKey=null): void {
    $activity=mb_substr(trim($activity),0,160);
    msw_stmt('UPDATE bot_commanders SET activity=?,last_enemy_key=?,last_action_at=NOW() WHERE user_id=?','ssi',[$activity,$enemyKey,$uid]);
}

function msw_bot_schedule_next(int $uid,bool $backoff=false): void {
    $min=max(6,(int)(msw_config('bot_action_min_seconds')??12));
    $max=max($min,(int)(msw_config('bot_action_max_seconds')??32));
    $delay=$backoff?max(30,$max):random_int($min,$max);
    msw_stmt('UPDATE bot_commanders SET next_action_at=DATE_ADD(NOW(),INTERVAL ? SECOND),lease_until=NULL,last_action_at=NOW() WHERE user_id=?','ii',[$delay,$uid]);
}

function msw_bot_move(int $uid,array $user): bool {
    $map=(string)($user['active_map']??'');
    if($map===''||!isset(msw_map_catalog()[$map])) return false;
    [$x,$y]=msw_map_safe_position($map,(int)$user['map_x'],(int)$user['map_y']);
    $dirs=['up','down','left','right'];shuffle($dirs);
    foreach($dirs as $dir){
        $nx=$x;$ny=$y;
        if($dir==='up')$ny-=18;elseif($dir==='down')$ny+=18;elseif($dir==='left')$nx-=18;else $nx+=18;
        if(msw_map_path_collision($map,$x,$y,$nx,$ny)!==null) continue;
        msw_stmt('UPDATE users SET map_x=?,map_y=?,facing=?,last_seen=NOW() WHERE id=?','iisi',[$nx,$ny,$dir,$uid]);
        msw_bot_set_activity($uid,'Patrolling '.$map);
        return true;
    }
    $facing=$dirs[0]??'right';
    msw_stmt('UPDATE users SET facing=?,last_seen=NOW() WHERE id=?','si',[$facing,$uid]);
    msw_bot_set_activity($uid,'Holding at blocked terrain');
    return false;
}

function msw_bot_rd_level(int $uid): int {
    $row=msw_one("SELECT level FROM base_sectors WHERE user_id=? AND sector_key='rd'",'i',[$uid]);
    return max(1,(int)($row['level']??1));
}

function msw_bot_roster_count(int $uid): int {
    $row=msw_one('SELECT COUNT(*) c FROM units WHERE owner_user_id=?','i',[$uid]);
    return (int)($row['c']??0);
}

function msw_bot_refresh_combat_team(int $uid): void {
    msw_stmt('UPDATE units SET active_combat=0 WHERE owner_user_id=?','i',[$uid]);
    $rows=msw_all("SELECT id FROM units WHERE owner_user_id=? AND (dispatched_until IS NULL OR dispatched_until<=NOW()) ORDER BY combat DESC,level DESC,id ASC LIMIT 4",'i',[$uid]);
    foreach($rows as $row) msw_stmt('UPDATE units SET active_combat=1 WHERE id=? AND owner_user_id=?','ii',[(int)$row['id'],$uid]);
}

function msw_bot_assign_unit(int $uid,int $unitId): void {
    $unit=msw_one('SELECT * FROM units WHERE id=? AND owner_user_id=?','ii',[$unitId,$uid]);if(!$unit)return;
    $choices=[];foreach(msw_sectors() as $sector=>$meta)$choices[$sector]=(int)$unit[$meta['stat']];
    arsort($choices);
    $assignment='reserve';
    // Autonomous commanders deliberately develop Cargo Fulton capability rather
    // than waiting for an unlikely perfectly optimized random staff layout.
    // They still obey the same R&D level/capacity requirement as human players.
    if(msw_bot_rd_level($uid)<8 && (int)$unit['rd']>=36 && msw_sector_has_capacity_for_assignment($uid,'rd',$unitId)) {
        $assignment='rd';
    } else {
        foreach(array_keys($choices) as $sector){if(msw_sector_has_capacity_for_assignment($uid,$sector,$unitId)){$assignment=$sector;break;}}
    }
    msw_stmt('UPDATE units SET assignment=? WHERE id=? AND owner_user_id=?','sii',[$assignment,$unitId,$uid]);
}

function msw_bot_create_recruit(int $uid,string $enemyKey,int $level): ?int {
    $catalog=msw_enemy_catalog();$enemy=$catalog[$enemyKey]??null;if(!$enemy||empty($enemy['recruitable']))return null;
    $cap=max(12,(int)(msw_config('bot_roster_cap')??48));if(msw_bot_roster_count($uid)>=$cap)return null;
    $level=max(1,min(99,$level));$seed=random_int(0,8);
    $combat=max(8,min(99,(int)$enemy['atk']+$seed));
    $rd=random_int(8,55);$support=random_int(8,55);$intel=random_int(8,55);$medical=random_int(8,55);$mess=random_int(8,55);$security=random_int(8,55);
    $best=max($combat,$rd,$support,$intel,$medical,$mess,$security);$grade=msw_grade_for_score($best);
    $callsign=strtoupper(substr(hash('crc32b',$uid.'|'.$enemyKey.'|'.microtime(true).'|'.random_int(1,999999)),0,6)).' '.$enemy['name'];
    msw_stmt(
        'INSERT INTO units(owner_user_id,source_enemy_key,callsign,unit_class,affinity_type,level,hp,max_hp,attack,defense,speed,combat,rd,support,intel,medical,mess,security,grade,assignment,active_combat) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0)',
        'issssiiiiiiiiiiiiiss',
        [$uid,$enemyKey,$callsign,$enemy['class'],$enemy['type'],$level,(int)$enemy['hp'],(int)$enemy['hp'],(int)$enemy['atk'],(int)$enemy['def'],(int)$enemy['spd'],$combat,$rd,$support,$intel,$medical,$mess,$security,$grade,'reserve']
    );
    $id=(int)msw_db()->insert_id;
    msw_bot_assign_unit($uid,$id);msw_bot_refresh_combat_team($uid);msw_recalculate_base($uid);
    $u=msw_one('SELECT mother_base_key FROM users WHERE id=?','i',[$uid]);
    if($u) msw_mb_sync_unit_positions($uid,(string)$u['mother_base_key']);
    return $id;
}

function msw_bot_restock_recovery(int $uid,string $item): bool {
    if($item==='fulton'){
        $u=msw_one('SELECT gmp FROM users WHERE id=?','i',[$uid]);
        if((int)($u['gmp']??0)<300)return false;
        $st=msw_stmt('UPDATE users SET gmp=gmp-300 WHERE id=? AND gmp>=300','i',[$uid]);if($st->affected_rows!==1)return false;
        msw_add_item($uid,'fulton',8);return true;
    }
    if($item==='cargo_fulton'&&msw_bot_rd_level($uid)>=8){
        if(msw_spend_resources($uid,['common_metal'=>120,'minor_metal'=>60])){msw_add_item($uid,'cargo_fulton',2);return true;}
    }
    return false;
}

function msw_bot_try_capture(int $uid,string $enemyKey,int $enemyLevel): bool {
    $enemy=msw_enemy_catalog()[$enemyKey]??null;if(!$enemy||empty($enemy['recruitable']))return false;
    if(msw_bot_roster_count($uid)>=max(12,(int)(msw_config('bot_roster_cap')??48)))return false;
    $vehicle=(string)$enemy['class']==='vehicle';$item=$vehicle?'cargo_fulton':'fulton';
    if($vehicle&&msw_bot_rd_level($uid)<8)return false;
    $inv=msw_inventory($uid);if((int)($inv[$item]??0)<1){if(!msw_bot_restock_recovery($uid,$item))return false;$inv=msw_inventory($uid);}
    if(!msw_consume_item($uid,$item,1))return false;
    $damagedRatio=random_int(18,45)/100.0;$classBase=$vehicle?0.10:0.20;$damageBonus=(1.0-$damagedRatio)*0.62;$bonus=$vehicle?0.08:0.00;
    $chance=min(0.92,$classBase+$damageBonus+$bonus);
    if((random_int(1,10000)/10000)>$chance)return false;
    if(msw_bot_create_recruit($uid,$enemyKey,$enemyLevel)===null)return false;
    msw_stmt('UPDATE bot_commanders SET recoveries=recoveries+1,vehicle_recoveries=vehicle_recoveries+? WHERE user_id=?','ii',[$vehicle?1:0,$uid]);
    return true;
}

function msw_bot_field_action(int $uid,array $user): void {
    $mapKey=(string)($user['active_map']??'');$map=msw_map_catalog()[$mapKey]??null;if(!$map)return;
    $enemyKey=msw_random_enemy_for_map($mapKey);$enemy=msw_enemy_catalog()[$enemyKey]??null;if(!$enemy)return;
    $enemyLevel=max(1,(int)$map['level']+random_int(-1,2));$level=max(1,(int)$user['level']);
    $winChance=max(45,min(95,76+($level*2)-((int)$map['level']*3)));
    $won=random_int(1,100)<=$winChance;
    msw_stmt('UPDATE bot_commanders SET field_battles=field_battles+1,last_enemy_key=? WHERE user_id=?','si',[$enemyKey,$uid]);
    if(!$won){msw_level_up_user($uid,12);msw_bot_set_activity($uid,'Regrouping after '.$enemy['name'].' contact',$enemyKey);return;}
    msw_stmt('UPDATE bot_commanders SET field_wins=field_wins+1 WHERE user_id=?','i',[$uid]);
    msw_grant_resources($uid,['gmp'=>random_int(180,420),'common_metal'=>random_int(35,95),'fuel'=>random_int(20,60)]);
    msw_level_up_user($uid,random_int(45,80));
    $captured=msw_bot_try_capture($uid,$enemyKey,$enemyLevel);
    msw_bot_set_activity($uid,$captured?'Recovered '.$enemy['name'].' by Fulton':'Defeated '.$enemy['name'],$enemyKey);
}

function msw_bot_manage_base(int $uid): void {
    $reserves=msw_all("SELECT id FROM units WHERE owner_user_id=? AND assignment='reserve' ORDER BY level DESC,id ASC LIMIT 6",'i',[$uid]);
    foreach($reserves as $r)msw_bot_assign_unit($uid,(int)$r['id']);
    msw_bot_refresh_combat_team($uid);msw_recalculate_base($uid);
    $rd=msw_bot_rd_level($uid);$inv=msw_inventory($uid);
    if($rd>=4&&(int)($inv['fulton_plus']??0)<2&&msw_spend_resources($uid,['common_metal'=>80,'minor_metal'=>35]))msw_add_item($uid,'fulton_plus',2);
    if($rd>=8&&(int)($inv['cargo_fulton']??0)<2&&msw_spend_resources($uid,['common_metal'=>120,'minor_metal'=>60]))msw_add_item($uid,'cargo_fulton',2);
    msw_bot_set_activity($uid,'Reorganizing Mother Base staff');
}

function msw_bot_resolve_due_dispatches(int $uid): int {
    return msw_dispatch_resolve_due_for_user($uid,2,true);
}

function msw_bot_dispatch_action(int $uid): void {
    if(msw_fob_resolve_due_dispatches($uid,2)>0){msw_bot_set_activity($uid,'Staff FOB invasion mission resolved');return;}
    if(msw_bot_resolve_due_dispatches($uid)>0){msw_bot_refresh_combat_team($uid);return;}
    $pending=msw_one("SELECT mission_key,finish_at FROM dispatch_missions WHERE user_id=? AND result='pending' ORDER BY id DESC LIMIT 1",'i',[$uid]);
    if($pending){
        $definition=msw_dispatch_catalog()[(string)$pending['mission_key']]??null;
        msw_bot_set_activity($uid,'Combat Unit deployed on '.(string)($definition['name']??$pending['mission_key']));
        return;
    }

    $catalog=msw_dispatch_catalog();$availableCount=msw_one("SELECT COUNT(*) c FROM units WHERE owner_user_id=? AND (dispatched_until IS NULL OR dispatched_until<=NOW())",'i',[$uid]);
    $count=(int)($availableCount['c']??0);if($count<2)return;
    $eligible=[];foreach($catalog as $key=>$definition)if((int)$definition['slots']<=$count)$eligible[$key]=$definition;
    if(!$eligible)return;
    $keys=array_keys($eligible);$key=(string)$keys[array_rand($keys)];$definition=$eligible[$key];$slots=(int)$definition['slots'];

    $db=msw_db();$db->begin_transaction();
    try{
        $units=msw_all("SELECT id,combat,level,dispatched_until FROM units WHERE owner_user_id=? AND (dispatched_until IS NULL OR dispatched_until<=NOW()) ORDER BY combat DESC,level DESC,id ASC LIMIT {$slots} FOR UPDATE",'i',[$uid]);
        if(count($units)!==$slots){$db->rollback();return;}
        $ids=array_map(fn($r)=>(int)$r['id'],$units);
        $power=array_sum(array_map(fn($r)=>(int)$r['combat']+((int)$r['level']*3),$units));
        $chance=max(.18,min(.95,.45+(($power-(int)$definition['difficulty'])/600)));
        $finish=date('Y-m-d H:i:s',time()+(int)$definition['duration']);
        msw_stmt('INSERT INTO dispatch_missions(user_id,mission_key,unit_ids_json,snapshot_power,success_chance,started_at,finish_at) VALUES(?,?,?,?,?,NOW(),?)','issids',[$uid,$key,json_encode($ids),$power,$chance,$finish]);
        foreach($ids as $unitId)msw_stmt('UPDATE units SET dispatched_until=? WHERE id=? AND owner_user_id=?','sii',[$finish,$unitId,$uid]);
        $db->commit();
        msw_bot_refresh_combat_team($uid);
        msw_bot_set_activity($uid,'Combat Unit deployed on '.$definition['name']);
    }catch(Throwable $e){$db->rollback();throw $e;}
}

function msw_bot_autonomous_fob_raid(int $attackerId): bool {
    $membership=msw_fob_membership($attackerId);if(!$membership)return false;
    $target=msw_one(
        "SELECT u.id FROM fob_world_memberships m JOIN users u ON u.id=m.user_id JOIN bot_commanders b ON b.user_id=u.id AND b.enabled=1 WHERE m.world_id=? AND u.id<>? AND (u.fob_protection_until IS NULL OR u.fob_protection_until<=NOW()) ORDER BY RAND() LIMIT 1",
        'ii',[(int)$membership['world_id'],$attackerId]
    );
    if(!$target)return false;
    $defenderId=(int)$target['id'];
    msw_fob_resolve_direct_raid($attackerId,$defenderId,'autonomous');
    msw_bot_set_activity($attackerId,'Autonomous FOB infiltration resolved inside '.msw_fob_world_name($membership));
    return true;
}

function msw_bot_fob_dispatch_action(int $uid): bool {
    if(msw_fob_resolve_due_dispatches($uid,2)>0){
        $membership=msw_fob_membership($uid);
        msw_bot_set_activity($uid,'Staff invasion team returned from '.($membership?msw_fob_world_name($membership):'FOB network'));
        return true;
    }
    $pending=msw_one("SELECT id,defender_user_id,finish_at FROM fob_strike_dispatches WHERE attacker_user_id=? AND result='pending' ORDER BY id DESC LIMIT 1",'i',[$uid]);
    if($pending){msw_bot_set_activity($uid,'Staff invasion team deployed to enemy FOB');return true;}
    $membership=msw_fob_membership($uid);if(!$membership)return false;
    $target=msw_one(
        "SELECT u.id FROM fob_world_memberships m JOIN users u ON u.id=m.user_id JOIN bot_commanders b ON b.user_id=u.id AND b.enabled=1 WHERE m.world_id=? AND u.id<>? AND (u.fob_protection_until IS NULL OR u.fob_protection_until<=NOW()) ORDER BY RAND() LIMIT 1",
        'ii',[(int)$membership['world_id'],$uid]
    );
    if(!$target)return false;
    $units=msw_all("SELECT id FROM units WHERE owner_user_id=? AND (dispatched_until IS NULL OR dispatched_until<=NOW()) ORDER BY combat DESC,level DESC,id ASC LIMIT 2",'i',[$uid]);
    if(count($units)<2)return false;
    $ids=array_map(fn($r)=>(int)$r['id'],$units);
    msw_fob_launch_staff_dispatch($uid,(int)$target['id'],$ids);
    msw_bot_set_activity($uid,'Dispatched staff invasion team to enemy FOB');
    return true;
}

function msw_bot_simulate_pvp_pair(int $a,int $b): void {
    if($a===$b)return;$fa=msw_commander_fighter($a);$fb=msw_commander_fighter($b);$moves=msw_move_catalog();$log=['Autonomous live-fire PvP simulation established.'];$turn=$a;$round=1;$winner=0;
    while($round<=36&&$fa['hp']>0&&$fb['hp']>0){$actor=$turn===$a?$fa:$fb;$target=$turn===$a?$fb:$fa;$bestKey='rifle_burst';$best=-1.0;foreach($moves as $k=>$mv){$score=(float)$mv['power']*msw_type_multiplier((string)$mv['type'],(string)$target['class'])*((int)$mv['accuracy']/100);if($score>$best){$best=$score;$bestKey=$k;}}$mv=$moves[$bestKey];$hit=random_int(1,100)<=(int)$mv['accuracy'];$damage=$hit?max(1,(int)floor((((int)$mv['power']+(int)$actor['attack']*.55)-((int)$target['defense']*.35))*msw_type_multiplier((string)$mv['type'],(string)$target['class'])*random_int(90,110)/100)):0;
        if($turn===$a){$fb['hp']=max(0,$fb['hp']-$damage);$log[]=$fa['name'].($hit?' dealt '.$damage.' damage.':' missed.');}else{$fa['hp']=max(0,$fa['hp']-$damage);$log[]=$fb['name'].($hit?' dealt '.$damage.' damage.':' missed.');}$turn=$turn===$a?$b:$a;$round++;}
    if($fa['hp']===$fb['hp'])$winner=random_int(0,1)?$a:$b;else$winner=$fa['hp']>$fb['hp']?$a:$b;$status=$winner===$a?'player1_win':'player2_win';$log[]='Autonomous match resolved.';
    $state=['round'=>$round,'log'=>$log,'fighters'=>[(string)$a=>$fa,(string)$b=>$fb],'ai_simulated'=>1];
    msw_stmt('INSERT INTO pvp_matches(player1_id,player2_id,match_mode,current_turn_user_id,state_json,status,version) VALUES(?,?,\'ai_sim\',?,?,?,?)','iiissi',[$a,$b,$winner,json_encode($state,JSON_UNESCAPED_SLASHES),$status,$round]);
    msw_level_up_user($winner,120);msw_level_up_user($winner===$a?$b:$a,30);
    msw_stmt('UPDATE bot_commanders SET pvp_battles=pvp_battles+1,pvp_wins=pvp_wins+? WHERE user_id=?','ii',[$winner===$a?1:0,$a]);
    msw_stmt('UPDATE bot_commanders SET pvp_battles=pvp_battles+1,pvp_wins=pvp_wins+? WHERE user_id=?','ii',[$winner===$b?1:0,$b]);
    msw_bot_set_activity($a,$winner===$a?'Won autonomous PvP exercise':'Completed autonomous PvP exercise');msw_bot_set_activity($b,$winner===$b?'Won autonomous PvP exercise':'Completed autonomous PvP exercise');
}

function msw_bot_autonomous_pvp(int $uid): bool {
    $target=msw_one('SELECT b.user_id FROM bot_commanders b WHERE b.enabled=1 AND b.user_id<>? ORDER BY RAND() LIMIT 1','i',[$uid]);if(!$target)return false;msw_bot_simulate_pvp_pair($uid,(int)$target['user_id']);return true;
}

function msw_bot_simulate_one(int $uid): void {
    $user=msw_one('SELECT * FROM users WHERE id=? AND is_bot=1','i',[$uid]);if(!$user)return;
    $bot=msw_bot_row($uid);if(!$bot)return;
    if(msw_fob_resolve_due_dispatches($uid,2)>0){msw_bot_set_activity($uid,'Staff FOB invasion mission resolved');return;}
    $roll=random_int(1,100);
    if($roll<=50)msw_bot_move($uid,$user);
    elseif($roll<=73)msw_bot_field_action($uid,$user);
    elseif($roll<=82)msw_bot_manage_base($uid);
    elseif($roll<=89)msw_bot_dispatch_action($uid);
    elseif($roll<=94)msw_bot_fob_dispatch_action($uid);
    elseif($roll<=98)msw_bot_autonomous_fob_raid($uid);
    else msw_bot_autonomous_pvp($uid);
}

function msw_bot_simulation_pulse(?string $mapKey=null,int $budget=12): void {
    if(!(bool)(msw_config('bot_population_enabled')??true))return;
    $budget=max(1,min(30,$budget));$where="b.enabled=1 AND b.next_action_at<=NOW() AND (b.lease_until IS NULL OR b.lease_until<NOW())";$types='';$params=[];
    if($mapKey!==null&&$mapKey!==''){$where.=' AND u.active_map=?';$types='s';$params=[$mapKey];}
    $ids=msw_all("SELECT b.user_id FROM bot_commanders b JOIN users u ON u.id=b.user_id WHERE {$where} ORDER BY b.next_action_at,b.bot_index LIMIT {$budget}",$types,$params);
    foreach($ids as $row){$uid=(int)$row['user_id'];$claim=msw_stmt('UPDATE bot_commanders SET lease_until=DATE_ADD(NOW(),INTERVAL 8 SECOND) WHERE user_id=? AND enabled=1 AND (lease_until IS NULL OR lease_until<NOW())','i',[$uid]);if($claim->affected_rows!==1)continue;
        try{msw_bot_simulate_one($uid);msw_bot_schedule_next($uid,false);}catch(Throwable $e){error_log('[MSW bot '.$uid.'] '.$e->getMessage());msw_bot_set_activity($uid,'Simulation backoff');msw_bot_schedule_next($uid,true);}}
}
