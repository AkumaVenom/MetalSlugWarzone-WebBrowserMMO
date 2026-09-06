<?php
declare(strict_types=1);

/**
 * Persistent autonomous commander runtime.
 *
 * Bots are real rows in users with is_bot=1 and dedicated bot_commanders state.
 * They never receive a login session. Simulation is request-driven, leased and
 * elapsed-time aware: bounded request batches advance live actions while compact
 * catch-up accounts for autonomous work completed between observation windows.
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


/**
 * Stable competitive identity for every autonomous commander.
 *
 * The distribution is deterministic from bot_index, so existing identities do
 * not reshuffle between requests or server restarts. Most commanders are Active
 * Rivals, while a deliberately small tail becomes Contender/Elite/Apex strength.
 */
function msw_bot_competitive_profile(int $botIndex,string $personality='balanced'): array {
    $botIndex=max(1,$botIndex);
    $tierRoll=(int)(sprintf('%u',crc32('msw-v084-tier|'.$botIndex))%1000);
    $spread=(int)(sprintf('%u',crc32('msw-v084-power|'.$botIndex))%101)/100.0;
    if($tierRoll<10){
        $profile=['tier'=>'apex','label'=>'Apex Rival','target_min'=>1.75,'target_max'=>2.45,'power_floor'=>9000,'roster_bonus'=>24,'quality_min'=>52,'quality_max'=>92,'pace'=>0.55,'catchup_ops'=>12,'catchup_recruits'=>5,'capture_bonus'=>0.15];
    }elseif($tierRoll<60){
        $profile=['tier'=>'elite','label'=>'Elite Rival','target_min'=>1.25,'target_max'=>1.75,'power_floor'=>6500,'roster_bonus'=>16,'quality_min'=>44,'quality_max'=>82,'pace'=>0.68,'catchup_ops'=>11,'catchup_recruits'=>4,'capture_bonus'=>0.11];
    }elseif($tierRoll<250){
        $profile=['tier'=>'contender','label'=>'Contender','target_min'=>0.90,'target_max'=>1.30,'power_floor'=>4500,'roster_bonus'=>8,'quality_min'=>36,'quality_max'=>72,'pace'=>0.82,'catchup_ops'=>9,'catchup_recruits'=>3,'capture_bonus'=>0.08];
    }else{
        $profile=['tier'=>'active','label'=>'Active Rival','target_min'=>0.60,'target_max'=>1.05,'power_floor'=>2800,'roster_bonus'=>0,'quality_min'=>28,'quality_max'=>64,'pace'=>1.00,'catchup_ops'=>8,'catchup_recruits'=>2,'capture_bonus'=>0.05];
    }
    $profile['target_multiplier']=$profile['target_min']+(($profile['target_max']-$profile['target_min'])*$spread);
    $profile['roster_cap']=max(12,(int)(msw_config('bot_roster_cap')??48))+(int)$profile['roster_bonus'];
    // Personalities remain meaningful inside the new competitive system rather
    // than being replaced by one universal script.
    if($personality==='aggressive'){$profile['pace']*=0.92;$profile['target_multiplier']*=1.04;}
    elseif($personality==='collector'){$profile['catchup_recruits']++;$profile['capture_bonus']+=0.03;}
    elseif($personality==='builder'){$profile['target_multiplier']*=1.08;$profile['catchup_recruits']++;}
    $profile['pace']=max(0.45,min(1.10,(float)$profile['pace']));
    $profile['catchup_recruits']=max(1,min(6,(int)$profile['catchup_recruits']));
    return $profile;
}

function msw_bot_power_anchor(): array {
    static $anchor=null;
    if(is_array($anchor))return $anchor;
    $row=msw_one('SELECT COALESCE(MAX(base_power),0) base_power,COALESCE(MAX(level),1) level FROM users WHERE is_bot=0');
    $anchor=[
        'base_power'=>max((int)(msw_config('bot_competitive_anchor_power')??3500),(int)($row['base_power']??0)),
        'level'=>max(1,(int)($row['level']??1)),
    ];
    return $anchor;
}

function msw_bot_target_power(array $user,array $bot): int {
    $profile=msw_bot_competitive_profile((int)$bot['bot_index'],(string)$bot['personality']);
    $anchor=msw_bot_power_anchor();
    return max((int)$profile['power_floor'],(int)round((int)$anchor['base_power']*(float)$profile['target_multiplier']));
}

function msw_bot_roster_cap_for(int $uid,?array $bot=null): int {
    $bot=$bot??msw_bot_row($uid);
    if(!$bot)return max(12,(int)(msw_config('bot_roster_cap')??48));
    return (int)msw_bot_competitive_profile((int)$bot['bot_index'],(string)$bot['personality'])['roster_cap'];
}

/**
 * One-time runtime activation for upgrades from older builds. This deliberately
 * does not rewrite rank, staff, resources or combat history. It only makes all
 * existing bots immediately overdue so the elapsed-time catch-up system can
 * advance the entire population as requests arrive.
 */
function msw_bot_competitive_activation_once(): void {
    static $checked=false;if($checked)return;$checked=true;
    try{
        $st=msw_stmt("INSERT IGNORE INTO schema_meta(meta_key,meta_value) VALUES('bot_competitive_rivals_v084','1')");
        if($st->affected_rows===1){
            // Do not make all 1,000 commanders overdue at once. Older builds
            // already carry valid next_action_at values, and fresh installs are
            // intentionally staggered by bot_index. We only clear stale leases.
            msw_stmt("UPDATE bot_commanders SET lease_until=NULL WHERE enabled=1 AND lease_until IS NOT NULL AND lease_until<NOW()");
        }
    }catch(Throwable $e){error_log('[MSW bot activation] '.$e->getMessage());}
}

function msw_bot_set_activity(int $uid,string $activity,?string $enemyKey=null): void {
    $activity=mb_substr(trim($activity),0,160);
    msw_stmt('UPDATE bot_commanders SET activity=?,last_enemy_key=?,last_action_at=NOW() WHERE user_id=?','ssi',[$activity,$enemyKey,$uid]);
}

function msw_bot_schedule_next(int $uid,bool $backoff=false,?array $bot=null): void {
    $min=max(3,(int)(msw_config('bot_action_min_seconds')??4));
    $max=max($min,(int)(msw_config('bot_action_max_seconds')??11));
    $bot=$bot??msw_bot_row($uid);
    $profile=$bot?msw_bot_competitive_profile((int)$bot['bot_index'],(string)$bot['personality']):['pace'=>1.0];
    $pace=(float)($profile['pace']??1.0);
    $scaledMin=max(2,(int)round($min*$pace));$scaledMax=max($scaledMin,(int)round($max*$pace));
    $delay=$backoff?max(18,$scaledMax*2):random_int($scaledMin,$scaledMax);
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
    $sectorRows=msw_all('SELECT sector_key,level,capacity,score FROM base_sectors WHERE user_id=?','i',[$uid]);
    $sectors=[];foreach($sectorRows as $row)$sectors[(string)$row['sector_key']]=$row;
    $countRows=msw_all("SELECT assignment,COUNT(*) c FROM units WHERE owner_user_id=? AND assignment<>'reserve' GROUP BY assignment",'i',[$uid]);
    $counts=[];foreach($countRows as $row)$counts[(string)$row['assignment']]=(int)$row['c'];
    $bot=msw_bot_row($uid);$profile=$bot?msw_bot_competitive_profile((int)$bot['bot_index'],(string)$bot['personality']):['tier'=>'active'];
    $personality=(string)($bot['personality']??'balanced');

    // Keep the established Cargo Fulton progression guarantee, but perform the
    // capacity check from the batched sector snapshot instead of issuing a query
    // for every possible assignment.
    $rd=$sectors['rd']??null;
    if($rd && (int)$rd['level']<8 && (int)$unit['rd']>=34 && (int)($counts['rd']??0)<(int)$rd['capacity']){
        msw_stmt("UPDATE units SET assignment='rd' WHERE id=? AND owner_user_id=?",'ii',[$unitId,$uid]);return;
    }

    $maxScore=0;foreach($sectors as $row)$maxScore=max($maxScore,(int)$row['score']);
    $bestSector='reserve';$bestValue=-PHP_INT_MAX;
    foreach(msw_sectors() as $sector=>$meta){
        $row=$sectors[$sector]??null;if(!$row)continue;
        if((int)($counts[$sector]??0)>=(int)$row['capacity'])continue;
        $stat=(string)$meta['stat'];$aptitude=(int)($unit[$stat]??0);
        $gap=max(0,$maxScore-(int)$row['score']);
        $balanceBonus=min(48,(int)floor($gap/7));
        if($personality==='builder')$balanceBonus=(int)round($balanceBonus*1.30);
        $roleBonus=0;
        if($personality==='aggressive'&&in_array($sector,['combat','security'],true))$roleBonus+=8;
        if($personality==='collector'&&in_array($sector,['support','intel'],true))$roleBonus+=6;
        if($profile['tier']==='apex'&&in_array($sector,['combat','rd','security'],true))$roleBonus+=4;
        $value=$aptitude+$balanceBonus+$roleBonus;
        if($value>$bestValue){$bestValue=$value;$bestSector=$sector;}
    }
    msw_stmt('UPDATE units SET assignment=? WHERE id=? AND owner_user_id=?','sii',[$bestSector,$unitId,$uid]);
}

function msw_bot_recalculate_base_fast(int $uid): void {
    $rows=msw_all("SELECT assignment,COUNT(*) c,SUM(CASE assignment WHEN 'combat' THEN combat WHEN 'rd' THEN rd WHEN 'support' THEN support WHEN 'intel' THEN intel WHEN 'medical' THEN medical WHEN 'mess' THEN mess WHEN 'security' THEN security ELSE 0 END) s FROM units WHERE owner_user_id=? AND assignment IN ('combat','rd','support','intel','medical','mess','security') GROUP BY assignment",'i',[$uid]);
    $summary=[];foreach($rows as $row)$summary[(string)$row['assignment']]=['score'=>(int)$row['s'],'count'=>(int)$row['c']];
    $db=msw_db();$values=[];$total=0;
    foreach(msw_sectors() as $key=>$meta){
        $score=(int)($summary[$key]['score']??0);$count=(int)($summary[$key]['count']??0);$level=max(1,(int)floor($score/120)+1);$capacity=10+(($level-1)*5);$grade=msw_grade_for_score($count?(int)round($score/$count):0);$total+=$score*$level;
        $safeKey=$db->real_escape_string($key);$safeGrade=$db->real_escape_string($grade);$values[]="({$uid},'{$safeKey}',{$level},{$capacity},{$score},'{$safeGrade}')";
    }
    if($values)$db->query("INSERT INTO base_sectors(user_id,sector_key,level,capacity,score,grade) VALUES ".implode(',',$values)." ON DUPLICATE KEY UPDATE level=VALUES(level),capacity=VALUES(capacity),score=VALUES(score),grade=VALUES(grade)");
    $avg=(int)min(110,floor(sqrt(max(0,$total))*2.2));msw_stmt('UPDATE users SET base_power=?,base_grade=? WHERE id=?','isi',[$total,msw_grade_for_score($avg),$uid]);
}

function msw_bot_finalize_roster(int $uid): void {
    msw_bot_refresh_combat_team($uid);msw_bot_recalculate_base_fast($uid);
    // Mother Base layout is already synchronized lazily by msw_mb_staff_state()
    // when that base is actually viewed. Rebuilding visual positions during every
    // autonomous AI development tick needlessly turns a background progression
    // action into dozens of collision/layout queries and can stall foreground
    // navigation when many commanders are due together.
}

function msw_bot_create_recruit(int $uid,string $enemyKey,int $level,bool $deferFinalize=false): ?int {
    $catalog=msw_enemy_catalog();$enemy=$catalog[$enemyKey]??null;if(!$enemy||empty($enemy['recruitable']))return null;
    $bot=msw_bot_row($uid);if(!$bot)return null;$profile=msw_bot_competitive_profile((int)$bot['bot_index'],(string)$bot['personality']);
    $cap=(int)$profile['roster_cap'];if(msw_bot_roster_count($uid)>=$cap)return null;
    $user=msw_one('SELECT level,base_power FROM users WHERE id=?','i',[$uid])?:['level'=>1,'base_power'=>0];
    $anchor=msw_bot_power_anchor();$careerLevel=max((int)$user['level'],min(99,(int)$anchor['level']));
    $level=max(1,min(99,max($level,$careerLevel+random_int(-2,2))));
    $careerBoost=min(14,(int)floor(max(1,$careerLevel)/8));
    $low=min(90,(int)$profile['quality_min']+$careerBoost);$high=min(99,max($low+4,(int)$profile['quality_max']+$careerBoost));
    $stat=fn():int=>random_int($low,$high);
    $combat=max(8,min(99,max((int)$enemy['atk']+random_int(4,12),$stat())));
    $rd=$stat();$support=$stat();$intel=$stat();$medical=$stat();$mess=$stat();$security=$stat();
    if((string)$bot['personality']==='aggressive'){$combat=min(99,$combat+5);$security=min(99,$security+3);}
    elseif((string)$bot['personality']==='builder'){$rd=min(99,$rd+5);$support=min(99,$support+3);}
    elseif((string)$bot['personality']==='collector'){$support=min(99,$support+4);$intel=min(99,$intel+3);}
    $best=max($combat,$rd,$support,$intel,$medical,$mess,$security);$grade=msw_grade_for_score($best);
    $callsign=strtoupper(substr(hash('crc32b',$uid.'|'.$enemyKey.'|'.microtime(true).'|'.random_int(1,999999)),0,6)).' '.$enemy['name'];
    msw_stmt(
        'INSERT INTO units(owner_user_id,source_enemy_key,callsign,unit_class,affinity_type,level,hp,max_hp,attack,defense,speed,combat,rd,support,intel,medical,mess,security,grade,assignment,active_combat) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0)',
        'issssiiiiiiiiiiiiiss',
        [$uid,$enemyKey,$callsign,$enemy['class'],$enemy['type'],$level,(int)$enemy['hp'],(int)$enemy['hp'],(int)$enemy['atk'],(int)$enemy['def'],(int)$enemy['spd'],$combat,$rd,$support,$intel,$medical,$mess,$security,$grade,'reserve']
    );
    $id=(int)msw_db()->insert_id;msw_bot_assign_unit($uid,$id);
    if(!$deferFinalize)msw_bot_finalize_roster($uid);
    return $id;
}

function msw_bot_restock_recovery(int $uid,string $item): bool {
    if($item==='fulton'){
        $u=msw_one('SELECT gmp FROM users WHERE id=?','i',[$uid]);
        if((int)($u['gmp']??0)<300)return false;
        $st=msw_stmt('UPDATE users SET gmp=gmp-300 WHERE id=? AND gmp>=300','i',[$uid]);if($st->affected_rows!==1)return false;
        msw_add_item($uid,'fulton',8);return true;
    }
    if($item==='cargo_fulton'&&msw_bot_rd_level($uid)>=5){
        if(msw_manufacture_item($uid,'cargo_fulton',2,['common_metal'=>120,'minor_metal'=>60]))return true;
    }
    return false;
}

function msw_bot_try_capture(int $uid,string $enemyKey,int $enemyLevel): bool {
    $enemy=msw_enemy_catalog()[$enemyKey]??null;if(!$enemy||empty($enemy['recruitable']))return false;
    $bot=msw_bot_row($uid);if(!$bot||msw_bot_roster_count($uid)>=msw_bot_roster_cap_for($uid,$bot))return false;
    $vehicle=(string)$enemy['class']==='vehicle';$item=$vehicle?'cargo_fulton':'fulton';
    if($vehicle&&msw_bot_rd_level($uid)<5)return false;
    $inv=msw_inventory($uid);if((int)($inv[$item]??0)<1){if(!msw_bot_restock_recovery($uid,$item))return false;$inv=msw_inventory($uid);}
    if(!msw_consume_item($uid,$item,1))return false;
    $damagedRatio=random_int(18,45)/100.0;$classBase=$vehicle?0.10:0.20;$damageBonus=(1.0-$damagedRatio)*0.62;$bonus=$vehicle?0.08:0.00;
    $profile=msw_bot_competitive_profile((int)$bot['bot_index'],(string)$bot['personality']);
    $chance=min(0.96,$classBase+$damageBonus+$bonus+(float)$profile['capture_bonus']);
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
    if(!$won){msw_level_up_user($uid,16);msw_bot_set_activity($uid,'Regrouping after '.$enemy['name'].' contact',$enemyKey);return;}
    msw_stmt('UPDATE bot_commanders SET field_wins=field_wins+1 WHERE user_id=?','i',[$uid]);
    msw_grant_resources($uid,['gmp'=>random_int(210,470),'common_metal'=>random_int(40,105),'fuel'=>random_int(22,66)]);
    msw_level_up_user($uid,random_int(55,90));
    $captured=msw_bot_try_capture($uid,$enemyKey,$enemyLevel);
    msw_bot_set_activity($uid,$captured?'Recovered '.$enemy['name'].' by Fulton':'Defeated '.$enemy['name'],$enemyKey);
}

function msw_bot_manage_base(int $uid): void {
    $reserves=msw_all("SELECT id FROM units WHERE owner_user_id=? AND assignment='reserve' ORDER BY level DESC,id ASC LIMIT 6",'i',[$uid]);
    foreach($reserves as $r)msw_bot_assign_unit($uid,(int)$r['id']);
    msw_bot_refresh_combat_team($uid);msw_bot_recalculate_base_fast($uid);
    $rd=msw_bot_rd_level($uid);$inv=msw_inventory($uid);
    if($rd>=4&&(int)($inv['fulton_plus']??0)<2)msw_manufacture_item($uid,'fulton_plus',2,['common_metal'=>80,'minor_metal'=>35]);
    if($rd>=5&&(int)($inv['cargo_fulton']??0)<2)msw_manufacture_item($uid,'cargo_fulton',2,['common_metal'=>120,'minor_metal'=>60]);
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

function msw_bot_pick_fob_target(int $attackerId): ?array {
    $membership=msw_fob_membership($attackerId);if(!$membership)return null;
    $humanBias=max(0,min(80,(int)(msw_config('bot_human_invasion_bias_percent')??28)));
    $preferHuman=random_int(1,100)<=$humanBias;$preferLocal=random_int(1,100)<=35;
    $attempts=[];
    if($preferHuman&&$preferLocal)$attempts[]=['human'=>true,'local'=>true];
    if($preferHuman)$attempts[]=['human'=>true,'local'=>false];
    if($preferLocal)$attempts[]=['human'=>false,'local'=>true];
    $attempts[]=['human'=>false,'local'=>false];
    foreach($attempts as $a){
        $where='m.user_id<>? AND (u.fob_protection_until IS NULL OR u.fob_protection_until<=NOW())';$types='i';$params=[$attackerId];
        if($a['human']){$where.=' AND u.is_bot=0';}
        if($a['local']){$where.=' AND m.world_id=?';$types.='i';$params[]=(int)$membership['world_id'];}
        $target=msw_one("SELECT u.id,u.username,u.is_bot,m.world_id FROM fob_world_memberships m JOIN users u ON u.id=m.user_id WHERE {$where} ORDER BY RAND() LIMIT 1",$types,$params);
        if($target)return $target;
    }
    return null;
}

function msw_bot_autonomous_fob_raid(int $attackerId): bool {
    $membership=msw_fob_membership($attackerId);if(!$membership)return false;$target=msw_bot_pick_fob_target($attackerId);if(!$target)return false;
    $defenderId=(int)$target['id'];msw_fob_resolve_direct_raid($attackerId,$defenderId,'autonomous');$targetWorld=msw_fob_world_row((int)$target['world_id']);
    msw_bot_set_activity($attackerId,'Invaded '.((int)$target['is_bot']===0?'human commander ':'AI commander ').(string)$target['username'].' in '.($targetWorld?msw_fob_world_name($targetWorld):'global FOB map'));
    return true;
}

function msw_bot_fob_dispatch_action(int $uid): bool {
    if(msw_fob_resolve_due_dispatches($uid,2)>0){
        $membership=msw_fob_membership($uid);
        msw_bot_set_activity($uid,'Staff invasion team returned from '.($membership?msw_fob_world_name($membership):'FOB operations'));
        return true;
    }
    $pending=msw_one("SELECT id,defender_user_id,finish_at FROM fob_strike_dispatches WHERE attacker_user_id=? AND result='pending' ORDER BY id DESC LIMIT 1",'i',[$uid]);
    if($pending){msw_bot_set_activity($uid,'Staff invasion team deployed to enemy FOB');return true;}
    $membership=msw_fob_membership($uid);if(!$membership)return false;$target=msw_bot_pick_fob_target($uid);if(!$target)return false;
    $units=msw_all("SELECT id FROM units WHERE owner_user_id=? AND (dispatched_until IS NULL OR dispatched_until<=NOW()) ORDER BY combat DESC,level DESC,id ASC LIMIT 2",'i',[$uid]);
    if(count($units)<2)return false;
    $ids=array_map(fn($r)=>(int)$r['id'],$units);
    msw_fob_launch_staff_dispatch($uid,(int)$target['id'],$ids);
    $targetWorld=msw_fob_world_row((int)$target['world_id']);msw_bot_set_activity($uid,'Dispatched staff invasion team to '.(string)$target['username'].' in '.($targetWorld?msw_fob_world_name($targetWorld):'global FOB map'));
    return true;
}


function msw_bot_recruitable_enemy_keys(?string $mapKey=null): array {
    $catalog=msw_enemy_catalog();$keys=[];
    if($mapKey!==null&&isset(msw_map_catalog()[$mapKey])){
        foreach((array)msw_map_catalog()[$mapKey]['encounters'] as $key)if(!empty($catalog[(string)$key]['recruitable']))$keys[]=(string)$key;
    }
    if(!$keys)foreach($catalog as $key=>$enemy)if(!empty($enemy['recruitable']))$keys[]=(string)$key;
    return array_values(array_unique($keys));
}

function msw_bot_train_staff(int $uid,array $profile,int $intensity=1): int {
    $intensity=max(1,min(8,$intensity));
    $rows=msw_all("SELECT id,assignment,level,combat,rd,support,intel,medical,mess,security FROM units WHERE owner_user_id=? AND assignment IN ('combat','rd','support','intel','medical','mess','security') ORDER BY level ASC,id ASC LIMIT {$intensity}",'i',[$uid]);
    $trained=0;
    foreach($rows as $row){
        $assignment=(string)$row['assignment'];$meta=msw_sectors()[$assignment]??null;if(!$meta)continue;$stat=(string)$meta['stat'];
        $tier=(string)$profile['tier'];$gain=$tier==='apex'?random_int(3,5):($tier==='elite'?random_int(2,4):($tier==='contender'?random_int(2,3):random_int(1,3)));
        $new=min(99,(int)$row[$stat]+$gain);if($new===(int)$row[$stat])continue;
        $row[$stat]=$new;$grade=msw_grade_for_score(max((int)$row['combat'],(int)$row['rd'],(int)$row['support'],(int)$row['intel'],(int)$row['medical'],(int)$row['mess'],(int)$row['security']));
        msw_stmt("UPDATE units SET {$stat}=?,grade=? WHERE id=? AND owner_user_id=?",'isii',[$new,$grade,(int)$row['id'],$uid]);
        msw_add_unit_xp($uid,(int)$row['id'],random_int(55,105));$trained++;
    }
    return $trained;
}

function msw_bot_development_action(int $uid,array $user,?array $bot=null): void {
    $bot=$bot??msw_bot_row($uid);if(!$bot)return;$profile=msw_bot_competitive_profile((int)$bot['bot_index'],(string)$bot['personality']);
    $target=msw_bot_target_power($user,$bot);$power=max(0,(int)$user['base_power']);$ratio=$target>0?$power/$target:1.0;
    $roster=msw_bot_roster_count($uid);$cap=(int)$profile['roster_cap'];$need=max(0,$cap-$roster);
    $batch=0;
    if($need>0){
        $maxBatch=min((int)$profile['catchup_recruits'],$need);
        if($ratio<0.35)$batch=$maxBatch;
        elseif($ratio<0.65)$batch=min($maxBatch,max(2,$maxBatch-1));
        elseif($ratio<1.0)$batch=min($maxBatch,2);
        elseif(random_int(1,100)<=18)$batch=1;
    }
    if((string)$bot['personality']==='builder'&&$need>0&&$ratio<1.05)$batch=min($need,max(1,$batch+1));

    $paceBonus=(string)$profile['tier']==='apex'?1.65:((string)$profile['tier']==='elite'?1.38:((string)$profile['tier']==='contender'?1.18:1.0));
    msw_grant_resources($uid,[
        'gmp'=>(int)round(random_int(520,980)*$paceBonus),
        'common_metal'=>(int)round(random_int(100,220)*$paceBonus),
        'minor_metal'=>(int)round(random_int(45,110)*$paceBonus),
        'fuel'=>(int)round(random_int(55,130)*$paceBonus),
        'biological'=>(int)round(random_int(20,60)*$paceBonus),
    ]);
    msw_level_up_user($uid,(int)round(random_int(90,155)*$paceBonus));

    $created=0;$vehicles=0;$keys=msw_bot_recruitable_enemy_keys((string)($user['active_map']??''));
    for($i=0;$i<$batch&&$keys;$i++){
        $enemyKey=(string)$keys[array_rand($keys)];$enemy=msw_enemy_catalog()[$enemyKey]??null;
        if(msw_bot_create_recruit($uid,$enemyKey,max(1,(int)$user['level']),true)!==null){$created++;if(($enemy['class']??'')==='vehicle')$vehicles++;}
    }
    if($created>0)msw_stmt('UPDATE bot_commanders SET field_battles=field_battles+?,field_wins=field_wins+?,recoveries=recoveries+?,vehicle_recoveries=vehicle_recoveries+? WHERE user_id=?','iiiii',[$created,$created,$created,$vehicles,$uid]);
    $trainIntensity=$ratio<0.70?3:($ratio<1.0?2:1);if((string)$profile['tier']==='elite')$trainIntensity++;if((string)$profile['tier']==='apex')$trainIntensity+=2;
    $trained=msw_bot_train_staff($uid,$profile,$trainIntensity);
    if($created>0||$trained>0)msw_bot_finalize_roster($uid);else msw_bot_recalculate_base_fast($uid);
    $label=$created>0?'Expanded Mother Base with '.$created.' recovered specialist'.($created===1?'':'s'):'Completed Mother Base training cycle';
    msw_bot_set_activity($uid,$label);
}

function msw_bot_due_operation_count(array $bot,array $profile): int {
    $next=strtotime((string)($bot['next_action_at']??''));if($next===false)return 1;
    $overdue=max(0,time()-$next);
    $baseMin=max(3,(int)(msw_config('bot_action_min_seconds')??4));$baseMax=max($baseMin,(int)(msw_config('bot_action_max_seconds')??11));
    $cadence=max(2,(int)round((($baseMin+$baseMax)/2)*(float)$profile['pace']));
    $ops=1+(int)floor($overdue/$cadence);
    $globalCap=max(2,min(16,(int)(msw_config('bot_catchup_max_operations')??12)));
    return max(1,min($globalCap,(int)$profile['catchup_ops'],$ops));
}

function msw_bot_catch_up(int $uid,array $user,array $bot,int $extraOps): void {
    $extraOps=max(0,$extraOps);if($extraOps<=0)return;$profile=msw_bot_competitive_profile((int)$bot['bot_index'],(string)$bot['personality']);
    $target=msw_bot_target_power($user,$bot);$fresh=msw_one('SELECT level,base_power FROM users WHERE id=?','i',[$uid])?:$user;$power=(int)($fresh['base_power']??0);
    $tierFactor=(string)$profile['tier']==='apex'?1.55:((string)$profile['tier']==='elite'?1.32:((string)$profile['tier']==='contender'?1.15:1.0));
    msw_grant_resources($uid,[
        'gmp'=>(int)round($extraOps*random_int(190,310)*$tierFactor),
        'common_metal'=>(int)round($extraOps*random_int(34,64)*$tierFactor),
        'minor_metal'=>(int)round($extraOps*random_int(15,34)*$tierFactor),
        'fuel'=>(int)round($extraOps*random_int(18,40)*$tierFactor),
        'biological'=>(int)round($extraOps*random_int(7,18)*$tierFactor),
    ]);
    msw_level_up_user($uid,(int)round($extraOps*random_int(45,72)*$tierFactor));
    $wins=max(0,min($extraOps,(int)round($extraOps*(0.78+(float)$profile['capture_bonus']*0.35))));
    msw_stmt('UPDATE bot_commanders SET field_battles=field_battles+?,field_wins=field_wins+? WHERE user_id=?','iii',[$extraOps,$wins,$uid]);

    $roster=msw_bot_roster_count($uid);$cap=(int)$profile['roster_cap'];$need=max(0,$cap-$roster);$created=0;$vehicles=0;
    if($need>0&&$power<$target){
        // Catch-up represents many elapsed operations, but materialize at most one
        // new staff row per foreground tick. The remaining elapsed productivity is
        // already represented by the set-based resource/XP/battle gains above.
        // This keeps 1,000 commanders competitive without multiplying SQL work by
        // the number of missed 4-11 second action windows.
        $desired=1;$keys=msw_bot_recruitable_enemy_keys((string)($user['active_map']??''));
        for($i=0;$i<$desired&&$keys;$i++){
            $enemyKey=(string)$keys[array_rand($keys)];$enemy=msw_enemy_catalog()[$enemyKey]??null;
            if(msw_bot_create_recruit($uid,$enemyKey,max((int)$user['level'],(int)($fresh['level']??1)),true)!==null){$created++;if(($enemy['class']??'')==='vehicle')$vehicles++;}
        }
        if($created>0)msw_stmt('UPDATE bot_commanders SET recoveries=recoveries+?,vehicle_recoveries=vehicle_recoveries+? WHERE user_id=?','iii',[$created,$vehicles,$uid]);
    }
    $trainIntensity=min(3,max(1,(int)ceil($extraOps/4)));if((string)$profile['tier']==='apex')$trainIntensity=min(4,$trainIntensity+1);
    $trained=msw_bot_train_staff($uid,$profile,$trainIntensity);
    if($created>0||$trained>0)msw_bot_finalize_roster($uid);else msw_bot_recalculate_base_fast($uid);
    $parts=['Sustained '.($extraOps+1).' autonomous operations'];if($created>0)$parts[]='recovered '.$created.' staff';if($trained>0)$parts[]='trained '.$trained.' staff';
    msw_bot_set_activity($uid,implode(' · ',$parts));
}

function msw_bot_weighted_action(array $bot,array $profile): string {
    $weights=['move'=>8,'field'=>30,'develop'=>28,'base'=>10,'dispatch'=>8,'fob_dispatch'=>6,'fob_raid'=>5,'pvp'=>5];
    $personality=(string)$bot['personality'];
    if($personality==='aggressive'){$weights['move']=5;$weights['field']=34;$weights['develop']=22;$weights['fob_dispatch']=8;$weights['fob_raid']=8;$weights['pvp']=7;}
    elseif($personality==='collector'){$weights['move']=6;$weights['field']=38;$weights['develop']=31;$weights['base']=9;$weights['dispatch']=7;$weights['fob_dispatch']=4;$weights['fob_raid']=3;$weights['pvp']=2;}
    elseif($personality==='builder'){$weights['move']=5;$weights['field']=23;$weights['develop']=40;$weights['base']=16;$weights['dispatch']=8;$weights['fob_dispatch']=4;$weights['fob_raid']=2;$weights['pvp']=2;}
    if((string)$profile['tier']==='elite'){$weights['fob_raid']+=2;$weights['pvp']+=2;}
    elseif((string)$profile['tier']==='apex'){$weights['field']+=3;$weights['fob_dispatch']+=2;$weights['fob_raid']+=4;$weights['pvp']+=4;}
    $total=array_sum($weights);$roll=random_int(1,max(1,$total));$cursor=0;
    foreach($weights as $key=>$weight){$cursor+=$weight;if($roll<=$cursor)return $key;}
    return 'develop';
}

function msw_bot_simulate_pvp_pair(int $a,int $b): void {
    if($a===$b)return;$fa=msw_commander_fighter($a);$fb=msw_commander_fighter($b);$moves=msw_move_catalog();$log=['AI PvP battle started.'];$turn=$a;$round=1;$winner=0;
    while($round<=36&&$fa['hp']>0&&$fb['hp']>0){$actor=$turn===$a?$fa:$fb;$target=$turn===$a?$fb:$fa;$bestKey='rifle_burst';$best=-1.0;foreach($moves as $k=>$mv){$score=(float)$mv['power']*msw_type_multiplier((string)$mv['type'],(string)$target['class'])*((int)$mv['accuracy']/100);if($score>$best){$best=$score;$bestKey=$k;}}$mv=$moves[$bestKey];$hit=random_int(1,100)<=(int)$mv['accuracy'];$damage=$hit?max(1,(int)floor((((int)$mv['power']+(int)$actor['attack']*.55)-((int)$target['defense']*.35))*msw_type_multiplier((string)$mv['type'],(string)$target['class'])*random_int(90,110)/100)):0;
        if($turn===$a){$fb['hp']=max(0,$fb['hp']-$damage);$log[]=$fa['name'].($hit?' dealt '.$damage.' damage.':' missed.');}else{$fa['hp']=max(0,$fa['hp']-$damage);$log[]=$fb['name'].($hit?' dealt '.$damage.' damage.':' missed.');}$turn=$turn===$a?$b:$a;$round++;}
    if($fa['hp']===$fb['hp'])$winner=random_int(0,1)?$a:$b;else$winner=$fa['hp']>$fb['hp']?$a:$b;$status=$winner===$a?'player1_win':'player2_win';$log[]='AI PvP battle finished.';
    $state=['round'=>$round,'log'=>$log,'fighters'=>[(string)$a=>$fa,(string)$b=>$fb],'ai_simulated'=>1];
    msw_stmt('INSERT INTO pvp_matches(player1_id,player2_id,match_mode,current_turn_user_id,state_json,status,version) VALUES(?,?,\'ai_sim\',?,?,?,?)','iiissi',[$a,$b,$winner,json_encode($state,JSON_UNESCAPED_SLASHES),$status,$round]);
    msw_level_up_user($winner,120);msw_level_up_user($winner===$a?$b:$a,30);
    msw_stmt('UPDATE bot_commanders SET pvp_battles=pvp_battles+1,pvp_wins=pvp_wins+? WHERE user_id=?','ii',[$winner===$a?1:0,$a]);
    msw_stmt('UPDATE bot_commanders SET pvp_battles=pvp_battles+1,pvp_wins=pvp_wins+? WHERE user_id=?','ii',[$winner===$b?1:0,$b]);
    msw_bot_set_activity($a,$winner===$a?'Won AI PvP battle':'Completed AI PvP battle');msw_bot_set_activity($b,$winner===$b?'Won AI PvP battle':'Completed AI PvP battle');
}

function msw_bot_autonomous_pvp(int $uid): bool {
    $target=msw_one('SELECT b.user_id FROM bot_commanders b WHERE b.enabled=1 AND b.user_id<>? ORDER BY RAND() LIMIT 1','i',[$uid]);if(!$target)return false;msw_bot_simulate_pvp_pair($uid,(int)$target['user_id']);return true;
}

function msw_bot_simulate_one(int $uid,?array $leasedBot=null): void {
    $user=msw_one('SELECT * FROM users WHERE id=? AND is_bot=1','i',[$uid]);if(!$user)return;
    $bot=$leasedBot??msw_bot_row($uid);if(!$bot)return;
    $profile=msw_bot_competitive_profile((int)$bot['bot_index'],(string)$bot['personality']);$ops=msw_bot_due_operation_count($bot,$profile);

    $resolvedFob=msw_fob_resolve_due_dispatches($uid,2);$resolvedDispatch=msw_bot_resolve_due_dispatches($uid);
    if($resolvedFob>0)msw_bot_set_activity($uid,'Staff FOB invasion mission resolved');
    elseif($resolvedDispatch>0)msw_bot_set_activity($uid,'Combat Unit dispatch returned to Mother Base');
    else{
        $action=msw_bot_weighted_action($bot,$profile);
        if($action==='move')msw_bot_move($uid,$user);
        elseif($action==='field')msw_bot_field_action($uid,$user);
        elseif($action==='develop')msw_bot_development_action($uid,$user,$bot);
        elseif($action==='base')msw_bot_manage_base($uid);
        elseif($action==='dispatch')msw_bot_dispatch_action($uid);
        elseif($action==='fob_dispatch'){if(!msw_bot_fob_dispatch_action($uid))msw_bot_development_action($uid,$user,$bot);}
        elseif($action==='fob_raid'){if(!msw_bot_autonomous_fob_raid($uid))msw_bot_field_action($uid,$user);}
        elseif(!msw_bot_autonomous_pvp($uid))msw_bot_development_action($uid,$user,$bot);
    }
    if($ops>1)msw_bot_catch_up($uid,$user,$bot,$ops-1);
}

function msw_bot_simulation_pulse(?string $mapKey=null,int $budget=12): void {
    if(!(bool)(msw_config('bot_population_enabled')??true))return;

    // Only one PHP request may advance autonomous commanders at a time. Multiple
    // map-presence polls/tabs can otherwise pile up on the same XAMPP/MySQL server
    // and make normal navigation wait behind several heavy AI batches.
    $lock=msw_one("SELECT GET_LOCK('msw_bot_pulse',0) acquired");
    if((int)($lock['acquired']??0)!==1)return;

    try{
        msw_bot_competitive_activation_once();
        $mult=max(1.0,min(2.0,(float)(msw_config('bot_pulse_budget_multiplier')??1.0)));
        $maxBudget=max(1,min(16,(int)(msw_config('bot_pulse_max_budget')??8)));
        $hardCap=max(1,min(8,(int)(msw_config('bot_request_hard_cap')??4)));
        $budget=max(1,min($maxBudget,$hardCap,(int)ceil($budget*$mult)));
        $timeBudgetMs=max(40,min(500,(int)(msw_config('bot_request_time_budget_ms')??180)));
        $deadline=microtime(true)+($timeBudgetMs/1000.0);

        $where="b.enabled=1 AND b.next_action_at<=NOW() AND (b.lease_until IS NULL OR b.lease_until<NOW())";$types='';$params=[];
        if($mapKey!==null&&$mapKey!==''){$where.=' AND u.active_map=?';$types='s';$params=[$mapKey];}
        $ids=msw_all("SELECT b.user_id,b.bot_index,b.personality,b.next_action_at FROM bot_commanders b JOIN users u ON u.id=b.user_id WHERE {$where} ORDER BY b.next_action_at,b.bot_index LIMIT {$budget}",$types,$params);
        foreach($ids as $row){
            if(microtime(true)>=$deadline)break;
            $uid=(int)$row['user_id'];
            $claim=msw_stmt('UPDATE bot_commanders SET lease_until=DATE_ADD(NOW(),INTERVAL 15 SECOND) WHERE user_id=? AND enabled=1 AND next_action_at<=NOW() AND (lease_until IS NULL OR lease_until<NOW())','i',[$uid]);
            if($claim->affected_rows!==1)continue;
            try{msw_bot_simulate_one($uid,$row);msw_bot_schedule_next($uid,false,$row);}
            catch(Throwable $e){error_log('[MSW bot '.$uid.'] '.$e->getMessage());msw_bot_set_activity($uid,'Regrouping');msw_bot_schedule_next($uid,true,$row);}
        }
    } finally {
        try{msw_db()->query("DO RELEASE_LOCK('msw_bot_pulse')");}catch(Throwable $e){error_log('[MSW bot pulse unlock] '.$e->getMessage());}
    }
}
