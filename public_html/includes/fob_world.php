<?php
declare(strict_types=1);

/**
 * Persistent sharded FOB world authority.
 *
 * The browser only renders membership rows. World assignment, slot ownership,
 * raid target validation, protection, resource transfer and staff deployment
 * remain authoritative in MySQL/PHP.
 */

function msw_fob_membership(int $uid): ?array {
    return msw_one(
        'SELECT m.user_id,m.world_id,m.skin_key,m.slot_index,m.x,m.y,m.placed_at,w.biome_key,w.shard_index,w.capacity FROM fob_world_memberships m JOIN fob_worlds w ON w.id=m.world_id WHERE m.user_id=? LIMIT 1',
        'i',[$uid]
    );
}

function msw_fob_world_row(int $worldId): ?array {
    return msw_one('SELECT * FROM fob_worlds WHERE id=? LIMIT 1','i',[$worldId]);
}

function msw_fob_world_name(array $world): string {
    $biome=msw_fob_biome_catalog()[(string)($world['biome_key']??'')]??null;
    $name=(string)($biome['name']??ucfirst((string)($world['biome_key']??'World')));
    return strtoupper($name).'-'.str_pad((string)(int)($world['shard_index']??1),3,'0',STR_PAD_LEFT);
}

function msw_fob_world_members(int $worldId): array {
    return msw_all(
        'SELECT m.user_id id,m.skin_key,m.slot_index,m.x,m.y,m.placed_at,u.username,u.base_power,u.base_grade,u.is_bot,u.fob_protection_until,b.bot_index FROM fob_world_memberships m JOIN users u ON u.id=m.user_id LEFT JOIN bot_commanders b ON b.user_id=u.id AND b.enabled=1 WHERE m.world_id=? ORDER BY m.slot_index',
        'i',[$worldId]
    );
}

function msw_fob_world_population(int $worldId): int {
    $row=msw_one('SELECT COUNT(*) c FROM fob_world_memberships WHERE world_id=?','i',[$worldId]);
    return (int)($row['c']??0);
}

function msw_fob_lock_name(string $biomeKey): string {
    return 'msw_fob_place_'.substr(preg_replace('/[^a-z0-9_]/','',strtolower($biomeKey))?:'world',0,32);
}

function msw_fob_assign_user(int $uid,string $biomeKey,string $skinKey): array {
    $biomes=msw_fob_biome_catalog();
    if(!isset($biomes[$biomeKey])) throw new RuntimeException('Invalid FOB continent type.');
    if(!msw_fob_skin_is_valid_for_biome($skinKey,$biomeKey)) throw new RuntimeException('That FOB skin is not compatible with the selected continent.');
    if(!isset(msw_mother_base_catalog()[$skinKey])) throw new RuntimeException('FOB skin is unavailable.');

    $existing=msw_fob_membership($uid);
    if($existing) return $existing;

    $db=msw_db();
    $lockName=msw_fob_lock_name($biomeKey);
    $lock=msw_one('SELECT GET_LOCK(?,8) acquired','s',[$lockName]);
    if((int)($lock['acquired']??0)!==1) throw new RuntimeException('FOB placement network is busy. Retry the deployment.');

    try{
        $db->begin_transaction();
        try{
            $again=msw_one('SELECT user_id FROM fob_world_memberships WHERE user_id=? FOR UPDATE','i',[$uid]);
            if($again){
                $db->commit();
                return msw_fob_membership($uid)??throw new RuntimeException('FOB placement lookup failed.');
            }
            $owner=msw_one('SELECT id,is_bot FROM users WHERE id=? FOR UPDATE','i',[$uid]);
            if(!$owner) throw new RuntimeException('Commander unavailable.');

            $worlds=msw_all('SELECT id,biome_key,shard_index,capacity FROM fob_worlds WHERE biome_key=? ORDER BY shard_index FOR UPDATE','s',[$biomeKey]);
            $world=null;
            foreach($worlds as $candidate){
                $count=msw_one('SELECT COUNT(*) c FROM fob_world_memberships WHERE world_id=?','i',[(int)$candidate['id']]);
                if((int)($count['c']??0)<(int)$candidate['capacity']){$world=$candidate;break;}
            }
            if(!$world){
                $next=1;
                foreach($worlds as $candidate)$next=max($next,(int)$candidate['shard_index']+1);
                $capacity=msw_fob_world_capacity();
                msw_stmt('INSERT INTO fob_worlds(biome_key,shard_index,capacity) VALUES(?,?,?)','sii',[$biomeKey,$next,$capacity]);
                $world=['id'=>(int)$db->insert_id,'biome_key'=>$biomeKey,'shard_index'=>$next,'capacity'=>$capacity];
            }

            $worldId=(int)$world['id'];$capacity=(int)$world['capacity'];
            $occupied=[];
            foreach(msw_all('SELECT slot_index FROM fob_world_memberships WHERE world_id=? ORDER BY slot_index','i',[$worldId]) as $row)$occupied[(int)$row['slot_index']]=true;
            if(count($occupied)>=$capacity) throw new RuntimeException('FOB world filled during placement. Retry deployment.');

            $start=(int)(sprintf('%u',crc32($uid.'|'.$biomeKey))%max(1,$capacity));
            $step=37; // coprime with 144, therefore visits every slot before repeating.
            $slot=-1;
            for($i=0;$i<$capacity;$i++){
                $candidate=($start+($i*$step))%$capacity;
                if(!isset($occupied[$candidate])){$slot=$candidate;break;}
            }
            if($slot<0) throw new RuntimeException('No legal FOB slot is available in this world.');
            [$x,$y]=msw_fob_slot_position($slot,$biomeKey,(int)$world['shard_index']);

            msw_stmt('INSERT INTO fob_world_memberships(user_id,world_id,skin_key,slot_index,x,y) VALUES(?,?,?,?,?,?)','iisiii',[$uid,$worldId,$skinKey,$slot,$x,$y]);
            msw_stmt('UPDATE users SET mother_base_key=? WHERE id=?','si',[$skinKey,$uid]);
            msw_mb_reset_layout($uid);
            $db->commit();
        }catch(Throwable $e){
            $db->rollback();
            throw $e;
        }
    }finally{
        try{msw_one('SELECT RELEASE_LOCK(?) released','s',[$lockName]);}catch(Throwable $_){}
    }

    $membership=msw_fob_membership($uid);
    if(!$membership) throw new RuntimeException('FOB placement was not persisted.');
    return $membership;
}

function msw_fob_same_world(int $a,int $b): bool {
    $row=msw_one('SELECT a.world_id FROM fob_world_memberships a JOIN fob_world_memberships b ON b.user_id=? AND b.world_id=a.world_id WHERE a.user_id=? LIMIT 1','ii',[$b,$a]);
    return $row!==null;
}

function msw_fob_target_row(int $viewerId,int $targetId): ?array {
    return msw_one(
        'SELECT u.id,u.username,u.base_power,u.base_grade,u.is_bot,u.fob_protection_until,m.world_id,m.skin_key,m.x,m.y,w.biome_key,w.shard_index,b.bot_index FROM fob_world_memberships me JOIN fob_world_memberships m ON m.world_id=me.world_id JOIN users u ON u.id=m.user_id JOIN fob_worlds w ON w.id=m.world_id LEFT JOIN bot_commanders b ON b.user_id=u.id AND b.enabled=1 WHERE me.user_id=? AND m.user_id=? AND m.user_id<>me.user_id LIMIT 1',
        'ii',[$viewerId,$targetId]
    );
}

function msw_fob_snapshot_locked(int $id,?array $specificUnitIds=null): array {
    $user=msw_one('SELECT id,username,base_power,base_grade,mother_base_key,is_bot FROM users WHERE id=?','i',[$id]);
    if(!$user) throw new RuntimeException('FOB commander unavailable.');
    if($specificUnitIds===null){
        $team=msw_all("SELECT id,callsign,unit_class,level,combat,security,grade FROM units WHERE owner_user_id=? AND active_combat=1 AND (dispatched_until IS NULL OR dispatched_until<=NOW()) ORDER BY combat DESC,id ASC LIMIT 4 FOR UPDATE",'i',[$id]);
    }else{
        $specificUnitIds=array_values(array_unique(array_filter(array_map('intval',$specificUnitIds),fn($v)=>$v>0)));
        if(!$specificUnitIds) $team=[];
        else{
            $in=implode(',',array_map('intval',$specificUnitIds));
            $team=msw_all("SELECT id,callsign,unit_class,level,combat,security,grade FROM units WHERE owner_user_id=? AND id IN ({$in}) ORDER BY combat DESC,id ASC FOR UPDATE",'i',[$id]);
        }
    }
    $security=msw_one("SELECT score,level,grade FROM base_sectors WHERE user_id=? AND sector_key='security' FOR UPDATE",'i',[$id]);
    return ['user'=>$user,'team'=>$team,'security'=>$security,'captured_at'=>date(DATE_ATOM)];
}

function msw_fob_resource_transfer(int $attackerId,int $defenderId,array $defenderResources,float $rate,array $caps): array {
    $transfer=['common_metal'=>0,'minor_metal'=>0,'precious_metal'=>0,'fuel'=>0,'biological'=>0];
    foreach($transfer as $key=>$_){
        $take=(int)floor((int)($defenderResources[$key]??0)*$rate);
        $take=min($take,(int)($caps[$key]??$caps['default']??2500));
        $transfer[$key]=$take;
        if($take<=0) continue;
        $st=msw_stmt("UPDATE player_resources SET {$key}={$key}-? WHERE user_id=? AND {$key}>=?",'iii',[$take,$defenderId,$take]);
        if($st->affected_rows!==1){$transfer[$key]=0;continue;}
        msw_stmt("UPDATE player_resources SET {$key}={$key}+? WHERE user_id=?",'ii',[$take,$attackerId]);
    }
    return $transfer;
}

function msw_fob_apply_protection(int $defenderId): string {
    $seconds=max(60,(int)(msw_config('fob_defender_protection_seconds')??900));
    $until=date('Y-m-d H:i:s',time()+$seconds);
    msw_stmt('UPDATE users SET fob_protection_until=? WHERE id=?','si',[$until,$defenderId]);
    return $until;
}

/** Resolve an immediate raid. The former attacker cooldown is intentionally absent. */
function msw_fob_resolve_direct_raid(int $attackerId,int $defenderId,string $mode='direct'): int {
    if($attackerId<1||$defenderId<1||$attackerId===$defenderId) throw new RuntimeException('Invalid FOB target.');
    if(!msw_fob_same_world($attackerId,$defenderId)) throw new RuntimeException('That FOB is not present in your overview-world instance.');

    $db=msw_db();$db->begin_transaction();
    try{
        $low=min($attackerId,$defenderId);$high=max($attackerId,$defenderId);
        $lockedUsers=msw_all('SELECT * FROM users WHERE id IN (?,?) ORDER BY id FOR UPDATE','ii',[$low,$high]);
        $users=[];foreach($lockedUsers as $row)$users[(int)$row['id']]=$row;
        $attacker=$users[$attackerId]??null;$defender=$users[$defenderId]??null;
        if(!$attacker||!$defender) throw new RuntimeException('FOB target unavailable.');
        if(!empty($defender['fob_protection_until'])&&strtotime((string)$defender['fob_protection_until'])>time()) throw new RuntimeException('That FOB is under temporary post-invasion protection.');

        $resourceRows=msw_all('SELECT * FROM player_resources WHERE user_id IN (?,?) ORDER BY user_id FOR UPDATE','ii',[$low,$high]);
        $resources=[];foreach($resourceRows as $row)$resources[(int)$row['user_id']]=$row;
        if(!isset($resources[$attackerId],$resources[$defenderId])) throw new RuntimeException('Resource ledger unavailable.');

        $as=msw_fob_snapshot_locked($attackerId);$ds=msw_fob_snapshot_locked($defenderId);
        $teamPower=(int)array_sum(array_column($as['team'],'combat'));$security=(int)($ds['security']['score']??0);
        $ar=(int)$attacker['base_power']+($teamPower*10)+random_int(0,900);
        $dr=(int)$defender['base_power']+($security*12)+random_int(0,900);
        $win=$ar>=$dr;
        $rate=$mode==='autonomous'?0.05:0.08;
        $caps=$mode==='autonomous'?['default'=>1600,'precious_metal'=>250]:['default'=>2500,'precious_metal'=>400];
        $transfer=$win?msw_fob_resource_transfer($attackerId,$defenderId,$resources[$defenderId],$rate,$caps):['common_metal'=>0,'minor_metal'=>0,'precious_metal'=>0,'fuel'=>0,'biological'=>0];

        // The defender is protected after every completed invasion attempt. There
        // is deliberately no attacker-side cooldown in v0.4.x.
        $protectedUntil=msw_fob_apply_protection($defenderId);
        msw_stmt('UPDATE users SET last_fob_attack_at=NOW() WHERE id=?','i',[$attackerId]);
        $result=$win?'attacker_win':'defender_win';
        $as['resolution']=['roll'=>$ar,'mode'=>$mode];
        $ds['resolution']=['roll'=>$dr,'protected_until'=>$protectedUntil];
        msw_stmt(
            'INSERT INTO fob_raids(attacker_user_id,defender_user_id,attacker_snapshot_json,defender_snapshot_json,result,transfer_json) VALUES(?,?,?,?,?,?)',
            'iissss',[$attackerId,$defenderId,json_encode($as,JSON_UNESCAPED_SLASHES),json_encode($ds,JSON_UNESCAPED_SLASHES),$result,json_encode($transfer,JSON_UNESCAPED_SLASHES)]
        );
        $raidId=(int)$db->insert_id;
        if((int)($attacker['is_bot']??0)===1){
            msw_stmt('UPDATE bot_commanders SET fob_attacks=fob_attacks+1,fob_wins=fob_wins+? WHERE user_id=?','ii',[$win?1:0,$attackerId]);
        }
        $db->commit();

        if((int)($attacker['is_bot']??0)===0){
            msw_console_event_for_user($attackerId,'FOB','RAID','FOB raid against '.(string)$defender['username'].' resolved: '.strtoupper(str_replace('_',' ',$result)).'.',[
                'raid_id'=>$raidId,'defender_id'=>$defenderId,'defender'=>(string)$defender['username'],'result'=>$result,'materials_transferred'=>(int)array_sum($transfer),'world_mode'=>$mode,
            ]);
        }
        return $raidId;
    }catch(Throwable $e){$db->rollback();throw $e;}
}

function msw_fob_available_dispatch_units(int $uid): array {
    msw_dispatch_resolve_due_for_user($uid,20,false);
    return msw_all(
        "SELECT id,callsign,unit_class,level,combat,security,grade,assignment,active_combat FROM units WHERE owner_user_id=? AND (dispatched_until IS NULL OR dispatched_until<=NOW()) ORDER BY combat DESC,level DESC,id ASC",
        'i',[$uid]
    );
}

function msw_fob_launch_staff_dispatch(int $attackerId,int $defenderId,array $unitIds): int {
    // A finished standard dispatch remains authoritative until resolved; settle it
    // before these same staff rows can be reserved by the FOB strike ledger.
    msw_dispatch_resolve_due_for_user($attackerId,20,null);
    if(!msw_fob_same_world($attackerId,$defenderId)) throw new RuntimeException('That FOB is not present in your overview-world instance.');
    $unitIds=array_values(array_unique(array_filter(array_map('intval',$unitIds),fn($id)=>$id>0)));sort($unitIds,SORT_NUMERIC);
    if(count($unitIds)<2||count($unitIds)>4) throw new RuntimeException('Select between 2 and 4 available staff for an FOB dispatch invasion.');

    $db=msw_db();$db->begin_transaction();
    try{
        $low=min($attackerId,$defenderId);$high=max($attackerId,$defenderId);
        $lockedUsers=msw_all('SELECT * FROM users WHERE id IN (?,?) ORDER BY id FOR UPDATE','ii',[$low,$high]);$users=[];
        foreach($lockedUsers as $row)$users[(int)$row['id']]=$row;
        $attacker=$users[$attackerId]??null;$defender=$users[$defenderId]??null;
        if(!$attacker||!$defender) throw new RuntimeException('FOB target unavailable.');
        if(!empty($defender['fob_protection_until'])&&strtotime((string)$defender['fob_protection_until'])>time()) throw new RuntimeException('That FOB is under temporary post-invasion protection.');

        $in=implode(',',array_map('intval',$unitIds));
        $units=msw_all("SELECT id,callsign,unit_class,level,combat,security,grade,dispatched_until FROM units WHERE owner_user_id=? AND id IN ({$in}) ORDER BY id FOR UPDATE",'i',[$attackerId]);
        if(count($units)!==count($unitIds)) throw new RuntimeException('One or more selected staff members are unavailable.');
        foreach($units as $unit)if(!empty($unit['dispatched_until'])&&strtotime((string)$unit['dispatched_until'])>time()) throw new RuntimeException('One or more selected staff members are already deployed.');

        $as=msw_fob_snapshot_locked($attackerId,$unitIds);$ds=msw_fob_snapshot_locked($defenderId);
        $power=0;foreach($units as $unit)$power+=(int)$unit['combat']*10+(int)$unit['level']*3;
        $security=(int)($ds['security']['score']??0);
        $defense=(int)$defender['base_power']+($security*12);
        $attack=(int)$attacker['base_power']+(int)round($power*.78);
        $chance=max(.12,min(.93,.50+(($attack-$defense)/4200)));
        $duration=max(30,(int)(msw_config('fob_staff_dispatch_seconds')??120));
        $finish=date('Y-m-d H:i:s',time()+$duration);
        $membership=msw_fob_membership($attackerId);if(!$membership) throw new RuntimeException('FOB world membership unavailable.');

        msw_stmt(
            'INSERT INTO fob_strike_dispatches(attacker_user_id,defender_user_id,world_id,unit_ids_json,attacker_snapshot_json,defender_snapshot_json,snapshot_power,success_chance,started_at,finish_at) VALUES(?,?,?,?,?,?,?,?,NOW(),?)',
            'iiisssids',[$attackerId,$defenderId,(int)$membership['world_id'],json_encode($unitIds),json_encode($as,JSON_UNESCAPED_SLASHES),json_encode($ds,JSON_UNESCAPED_SLASHES),$power,$chance,$finish]
        );
        $dispatchId=(int)$db->insert_id;
        foreach($unitIds as $unitId)msw_stmt('UPDATE units SET dispatched_until=? WHERE id=? AND owner_user_id=?','sii',[$finish,$unitId,$attackerId]);
        $db->commit();

        if((int)($attacker['is_bot']??0)===0){
            msw_console_event_for_user($attackerId,'FOB','DISPATCH','Staff invasion team dispatched to '.(string)$defender['username'].'.',[
                'dispatch_id'=>$dispatchId,'defender_id'=>$defenderId,'staff_count'=>count($unitIds),'finish_at'=>$finish,
            ]);
        }
        return $dispatchId;
    }catch(Throwable $e){$db->rollback();throw $e;}
}

function msw_fob_resolve_due_dispatches(int $attackerId,int $limit=8): int {
    $limit=max(1,min(20,$limit));
    $due=msw_all("SELECT id FROM fob_strike_dispatches WHERE attacker_user_id=? AND result='pending' AND finish_at<=NOW() ORDER BY id LIMIT {$limit}",'i',[$attackerId]);
    $resolved=0;
    foreach($due as $row){
        $db=msw_db();$db->begin_transaction();
        try{
            $mission=msw_one("SELECT * FROM fob_strike_dispatches WHERE id=? AND attacker_user_id=? AND result='pending' FOR UPDATE",'ii',[(int)$row['id'],$attackerId]);
            if(!$mission||strtotime((string)$mission['finish_at'])>time()){$db->rollback();continue;}
            $defenderId=(int)$mission['defender_user_id'];$low=min($attackerId,$defenderId);$high=max($attackerId,$defenderId);
            $lockedUsers=msw_all('SELECT * FROM users WHERE id IN (?,?) ORDER BY id FOR UPDATE','ii',[$low,$high]);$users=[];foreach($lockedUsers as $u)$users[(int)$u['id']]=$u;
            $attacker=$users[$attackerId]??null;$defender=$users[$defenderId]??null;
            if(!$attacker||!$defender) throw new RuntimeException('FOB dispatch commander unavailable.');
            $unitIds=array_values(array_unique(array_filter(array_map('intval',json_decode((string)$mission['unit_ids_json'],true)?:[]),fn($id)=>$id>0)));sort($unitIds,SORT_NUMERIC);

            if(!empty($defender['fob_protection_until'])&&strtotime((string)$defender['fob_protection_until'])>time()){
                msw_stmt("UPDATE fob_strike_dispatches SET result='protected_abort',resolved_at=NOW(),transfer_json='{}' WHERE id=? AND result='pending'",'i',[(int)$mission['id']]);
                foreach($unitIds as $unitId){msw_add_unit_xp($attackerId,$unitId,10);msw_stmt('UPDATE units SET dispatched_until=NULL WHERE id=? AND owner_user_id=? AND (dispatched_until IS NULL OR dispatched_until<=?)','iis',[$unitId,$attackerId,(string)$mission['finish_at']]);}
                $db->commit();$resolved++;
                continue;
            }

            $rr=msw_all('SELECT * FROM player_resources WHERE user_id IN (?,?) ORDER BY user_id FOR UPDATE','ii',[$low,$high]);$resources=[];foreach($rr as $r)$resources[(int)$r['user_id']]=$r;
            if(!isset($resources[$attackerId],$resources[$defenderId])) throw new RuntimeException('FOB dispatch resource ledger unavailable.');

            $chance=max(0.0,min(1.0,(float)$mission['success_chance']));
            $win=(random_int(1,10000)/10000)<=$chance;
            $transfer=$win?msw_fob_resource_transfer($attackerId,$defenderId,$resources[$defenderId],0.06,['default'=>2100,'precious_metal'=>325]):['common_metal'=>0,'minor_metal'=>0,'precious_metal'=>0,'fuel'=>0,'biological'=>0];
            $protectedUntil=msw_fob_apply_protection($defenderId);
            $as=json_decode((string)$mission['attacker_snapshot_json'],true)?:[];$ds=json_decode((string)$mission['defender_snapshot_json'],true)?:[];
            $as['resolution']=['mode'=>'staff_dispatch','success_chance'=>$chance,'dispatch_id'=>(int)$mission['id']];
            $ds['resolution']=['protected_until'=>$protectedUntil];
            $result=$win?'attacker_win':'defender_win';
            msw_stmt('INSERT INTO fob_raids(attacker_user_id,defender_user_id,attacker_snapshot_json,defender_snapshot_json,result,transfer_json) VALUES(?,?,?,?,?,?)','iissss',[$attackerId,$defenderId,json_encode($as,JSON_UNESCAPED_SLASHES),json_encode($ds,JSON_UNESCAPED_SLASHES),$result,json_encode($transfer,JSON_UNESCAPED_SLASHES)]);
            $raidId=(int)$db->insert_id;
            msw_stmt('UPDATE fob_strike_dispatches SET result=?,resolved_at=NOW(),transfer_json=?,raid_id=? WHERE id=? AND result=\'pending\'','ssii',[$result,json_encode($transfer,JSON_UNESCAPED_SLASHES),$raidId,(int)$mission['id']]);
            foreach($unitIds as $unitId){msw_add_unit_xp($attackerId,$unitId,$win?95:35);msw_stmt('UPDATE units SET dispatched_until=NULL WHERE id=? AND owner_user_id=? AND (dispatched_until IS NULL OR dispatched_until<=?)','iis',[$unitId,$attackerId,(string)$mission['finish_at']]);}
            msw_level_up_user($attackerId,$win?80:25);msw_recalculate_base($attackerId);
            if((int)($attacker['is_bot']??0)===1)msw_stmt('UPDATE bot_commanders SET fob_attacks=fob_attacks+1,fob_wins=fob_wins+? WHERE user_id=?','ii',[$win?1:0,$attackerId]);
            $db->commit();$resolved++;

            if((int)($attacker['is_bot']??0)===0){
                msw_console_event_for_user($attackerId,'FOB','DISPATCH_RESOLVE','Staff FOB invasion against '.(string)$defender['username'].' resolved: '.strtoupper(str_replace('_',' ',$result)).'.',[
                    'dispatch_id'=>(int)$mission['id'],'raid_id'=>$raidId,'result'=>$result,'materials_transferred'=>(int)array_sum($transfer),
                ]);
            }
        }catch(Throwable $e){$db->rollback();throw $e;}
    }
    return $resolved;
}

function msw_fob_dispatches_for_user(int $uid,int $limit=20): array {
    $limit=max(1,min(100,$limit));
    return msw_all(
        "SELECT d.*,u.username defender FROM fob_strike_dispatches d JOIN users u ON u.id=d.defender_user_id WHERE d.attacker_user_id=? ORDER BY d.id DESC LIMIT {$limit}",
        'i',[$uid]
    );
}

function msw_fob_pending_dispatch_for_target(int $attackerId,int $defenderId): ?array {
    return msw_one("SELECT * FROM fob_strike_dispatches WHERE attacker_user_id=? AND defender_user_id=? AND result='pending' ORDER BY id DESC LIMIT 1",'ii',[$attackerId,$defenderId]);
}
