<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function msw_context_definition(string $context,string $key): ?array {
    return match($context){
        'mission' => msw_mission_catalog()[$key] ?? null,
        'sidequest' => msw_sidequest_catalog()[$key] ?? null,
        'trainer' => msw_trainer_catalog()[$key] ?? null,
        'boss' => msw_boss_catalog()[$key] ?? null,
        default => null,
    };
}

function msw_runtime_enemy_key(string $key): string {
    return in_array($key,['girida','dicokka','rshobu'],true) ? 'biker' : $key;
}

function msw_sync_enemy_runtime_state(array &$state): void {
    if(empty($state['enemy']) || !is_array($state['enemy'])) return;
    $original=(string)($state['enemy']['key']??'');
    $key=msw_runtime_enemy_key($original);
    $catalog=msw_enemy_catalog();
    if(!isset($catalog[$key])) return;
    $entry=$catalog[$key];
    $name=(string)$entry['name'];
    if((string)($state['context']??'')==='trainer'){
        $definition=msw_context_definition('trainer',(string)($state['context_key']??''));
        if($definition) $name=(string)$definition['name'].' · '.$name;
    }
    $state['enemy']['key']=$key;
    $state['enemy']['name']=$name;
    $state['enemy']['class']=(string)$entry['class'];
    $state['enemy']['type']=(string)$entry['type'];
    $state['enemy']['sprite']=(string)$entry['sprite'];
    $state['enemy']['recruitable']=(string)($state['context']??'')==='trainer'?0:(int)$entry['recruitable'];
    if($original!==$key){
        $state['log'][]='Legacy contact visual normalized to the production Rebel Biker asset.';
    }
}

function msw_battle_system_snapshot(int $uid): array {
    $levels=msw_sector_levels($uid);
    return [
        'rd'=>(int)($levels['rd']??1),
        'medical'=>(int)($levels['medical']??1),
        'intel'=>(int)($levels['intel']??1),
        'security'=>(int)($levels['security']??1),
        'support'=>(int)($levels['support']??1),
    ];
}

function msw_battle_fx(array &$state,string $action,array $extra=[]): void {
    $seq=(int)($state['fx']['seq']??0)+1;
    $state['fx']=array_merge([
        'seq'=>$seq,'kind'=>'turn','action'=>$action,'player_hit'=>false,'enemy_counter'=>false,'enemy_hit'=>false,
        'backup_slots'=>[],'heal'=>0,'recovery_success'=>false,
    ],$extra);
}

function msw_new_battle_state(int $uid,string $enemyKey,string $context='field',string $contextKey=''): array {
    $enemies=msw_enemy_catalog();
    if(!isset($enemies[$enemyKey])) throw new InvalidArgumentException('Unknown enemy.');
    $enemy=$enemies[$enemyKey];
    $lead=msw_commander_fighter($uid);
    $definition=msw_context_definition($context,$contextKey);
    $fieldThreat=(int)(msw_map_catalog()[$contextKey]['level']??1);
    $requestedLevel=max(1,(int)($definition['level']??($context==='boss'?12:max($fieldThreat,(int)$lead['level']))));
    $leadLevel=max(1,(int)($lead['level']??1));
    $scale=$context==='boss' ? 1.0 : max(1.0,1.0+(($leadLevel-1)*0.035));
    $enemyName=(string)$enemy['name'];
    if($context==='trainer' && $definition) $enemyName=(string)$definition['name'].' · '.$enemyName;

    return [
        'round'=>1,
        'log'=>['Contact! '.$enemyName.' entered the combat zone.'],
        'player'=>[
            'unit_id'=>0,'name'=>(string)$lead['name'],'class'=>(string)$lead['class'],'type'=>(string)$lead['type'],
            'level'=>$leadLevel,'hp'=>(int)$lead['max_hp'],'max_hp'=>(int)$lead['max_hp'],'attack'=>(int)$lead['attack'],'defense'=>(int)$lead['defense'],'speed'=>(int)$lead['speed'],
        ],
        'enemy'=>[
            'key'=>$enemyKey,'name'=>$enemyName,'class'=>$enemy['class'],'type'=>$enemy['type'],'sprite'=>$enemy['sprite'],'recruitable'=>$context==='trainer'?0:(int)$enemy['recruitable'],
            'level'=>$requestedLevel,'hp'=>(int)round($enemy['hp']*$scale),'max_hp'=>(int)round($enemy['hp']*$scale),
            'attack'=>(int)round($enemy['atk']*$scale),'defense'=>(int)round($enemy['def']*$scale),'speed'=>(int)round($enemy['spd']*$scale),
        ],
        'systems'=>msw_battle_system_snapshot($uid),
        'backups'=>msw_security_backup_fighters($uid),
        'fx'=>['seq'=>1,'kind'=>'contact','action'=>'contact','player_hit'=>false,'enemy_counter'=>false,'enemy_hit'=>false,'backup_slots'=>[],'heal'=>0,'recovery_success'=>false],
        'context'=>$context,'context_key'=>$contextKey,'finished'=>false,'result'=>null,
    ];
}

function msw_sync_commander_battle_state(int $uid,array &$state): void {
    $commander=msw_commander_fighter($uid);
    $old=$state['player']??[];
    $oldMax=max(1,(int)($old['max_hp']??$commander['max_hp']));
    $oldHp=max(0,(int)($old['hp']??$oldMax));
    $ratio=max(0.0,min(1.0,$oldHp/$oldMax));
    $state['player']=[
        'unit_id'=>0,'name'=>(string)$commander['name'],'class'=>(string)$commander['class'],'type'=>(string)$commander['type'],'level'=>(int)$commander['level'],
        'hp'=>max(0,(int)round((int)$commander['max_hp']*$ratio)),'max_hp'=>(int)$commander['max_hp'],'attack'=>(int)$commander['attack'],'defense'=>(int)$commander['defense'],'speed'=>(int)$commander['speed'],
    ];
}

function msw_sync_battle_support_state(int $uid,array &$state): void {
    $state['systems']=msw_battle_system_snapshot($uid);
    $state['backups']=msw_security_backup_fighters($uid);
}

function msw_start_encounter(int $uid,string $enemyKey,string $context='field',string $contextKey=''): int {
    $active=msw_active_encounter($uid);
    if($active) return (int)$active['id'];
    if(!in_array($context,['field','mission','sidequest','trainer','boss'],true)) throw new InvalidArgumentException('Unknown battle context.');
    $state=msw_new_battle_state($uid,$enemyKey,$context,$contextKey);
    msw_stmt('INSERT INTO encounters(user_id,context_type,context_key,state_json) VALUES(?,?,?,?)','isss',[$uid,$context,$contextKey,json_encode($state,JSON_UNESCAPED_SLASHES)]);
    $encounterId=(int)msw_db()->insert_id;
    $category=in_array($context,['mission','sidequest','trainer','boss'],true)?'MISSION':'COMBAT';
    msw_console_event_for_user($uid,$category,'ENGAGE','Engaged '.$state['enemy']['name'].'.',['encounter_id'=>$encounterId,'context'=>$context,'context_key'=>$contextKey,'enemy'=>$state['enemy']['name']]);
    return $encounterId;
}

function msw_damage(int $power,int $attack,int $defense,float $multiplier): int {
    $base=(($power*max(1,$attack))/(max(8,$defense)*1.6));
    $variance=random_int(90,110)/100;
    return max(1,(int)round($base*$multiplier*$variance));
}

function msw_security_backup_assist(array &$state): array {
    if((int)($state['enemy']['hp']??0)<=0)return [];
    $security=max(1,(int)($state['systems']['security']??1));
    $accuracy=min(72,60+($security>=4?5:0)+(int)floor(max(0,$security-1)/5));
    $capRate=(string)($state['enemy']['class']??'')==='boss'?0.035:($security>=7?0.07:0.06);
    $hits=[];
    foreach((array)($state['backups']??[]) as $backup){
        if((int)$state['enemy']['hp']<=0)break;
        if(random_int(1,100)>$accuracy){$state['log'][]=(string)$backup['name'].' provided covering fire but missed.';continue;}
        $mult=msw_type_multiplier((string)$backup['type'],(string)$state['enemy']['class']);
        $damage=msw_damage(12,(int)$backup['attack'],(int)$state['enemy']['defense'],$mult*.55);
        $cap=max(2,(int)floor((int)$state['enemy']['max_hp']*$capRate));
        $damage=max(1,min($cap,$damage));
        $state['enemy']['hp']=max(0,(int)$state['enemy']['hp']-$damage);
        $slot=(int)($backup['slot']??0);$hits[]=$slot;
        $state['log'][]=(string)$backup['name'].' landed controlled backup fire for '.$damage.' damage.';
    }
    if(isset($state['fx'])&&is_array($state['fx']))$state['fx']['backup_slots']=$hits;
    if((int)$state['enemy']['hp']<=0){$state['finished']=true;$state['result']='won';}
    return $hits;
}

function msw_battle_attack(array &$state,string $moveKey): void {
    $moves=msw_move_catalog();
    if(!isset($moves[$moveKey])) $moveKey='rifle_burst';
    $move=$moves[$moveKey];$hit=false;$damage=0;
    if(random_int(1,100) <= (int)$move['accuracy']){
        $hit=true;$multiplier=msw_type_multiplier((string)$move['type'],(string)$state['enemy']['class']);
        $damage=msw_damage((int)$move['power'],(int)$state['player']['attack'],(int)$state['enemy']['defense'],$multiplier);
        $state['enemy']['hp']=max(0,(int)$state['enemy']['hp']-$damage);
        $tag=$multiplier>=1.35?' Critical effectiveness!':($multiplier<=0.65?' Reduced effectiveness.':'');
        $state['log'][]=$state['player']['name'].' used '.$move['name'].' for '.$damage.' damage.'.$tag;
    }else{$state['log'][]=$state['player']['name'].' missed with '.$move['name'].'.';}
    msw_battle_fx($state,'attack',['player_hit'=>$hit,'damage'=>$damage,'move'=>$moveKey]);
    if((int)$state['enemy']['hp']<=0){$state['finished']=true;$state['result']='won';return;}
    msw_security_backup_assist($state);
    if(!empty($state['finished']))return;
    msw_enemy_turn($state);
}

function msw_enemy_turn(array &$state): void {
    $intel=max(1,(int)($state['systems']['intel']??1));
    $accuracy=max(50,90-($intel>=8?6:0));
    $move=['name'=>'Counterattack','type'=>$state['enemy']['type'],'power'=>max(14,min(32,(int)$state['enemy']['attack'])),'accuracy'=>$accuracy];
    $hit=false;$damage=0;
    if(random_int(1,100)<=(int)$move['accuracy']){
        $hit=true;$multiplier=msw_type_multiplier((string)$move['type'],(string)$state['player']['class']);
        $damage=msw_damage((int)$move['power'],(int)$state['enemy']['attack'],(int)$state['player']['defense'],$multiplier);
        $state['player']['hp']=max(0,(int)$state['player']['hp']-$damage);
        $state['log'][]=$state['enemy']['name'].' countered for '.$damage.' damage.';
    }else{$state['log'][]=$state['enemy']['name'].' missed its counterattack'.($intel>=8?' after Intel countermeasure prediction.':'.');}
    if(!isset($state['fx'])||!is_array($state['fx']))msw_battle_fx($state,'counter');
    $state['fx']['enemy_counter']=true;$state['fx']['enemy_hit']=$hit;$state['fx']['counter_damage']=$damage;
    if((int)$state['player']['hp']<=0){$state['finished']=true;$state['result']='lost';}else{$state['round']=(int)$state['round']+1;}
}

function msw_recovery_chance(array $state,array $fulton): float {
    $enemy=$state['enemy'];
    $ratio=max(0.0,min(1.0,(int)$enemy['hp']/max(1,(int)$enemy['max_hp'])));
    $damageBonus=(1.0-$ratio)*0.62;
    $classBase=in_array($enemy['class'],['infantry','heavy_infantry'],true)?0.20:0.10;
    return min(0.92,$classBase+$damageBonus+(float)$fulton['bonus']);
}

function msw_battle_medical_multiplier(array $state): float {
    $support=max(1,(int)($state['systems']['support']??1));
    return $support>=6?1.25:($support>=3?1.15:1.0);
}

function msw_use_battle_item(int $uid,array &$state,string $itemKey): array {
    $items=msw_battle_item_catalog();if(!isset($items[$itemKey]))return [false,'Unknown battlefield medical supply.'];
    $item=$items[$itemKey];
    if(!msw_requirements_met($uid,(array)$item['requirements']))return [false,'Mother Base medical/R&D requirement not met for '.$item['name'].'.'];
    $missing=max(0,(int)$state['player']['max_hp']-(int)$state['player']['hp']);if($missing<=0)return [false,'Commander HP is already full.'];
    if(!msw_consume_item($uid,$itemKey,1))return [false,'No '.$item['name'].' units remain.'];
    $heal=min($missing,max(1,(int)round((int)$item['heal']*msw_battle_medical_multiplier($state))));
    $state['player']['hp']=min((int)$state['player']['max_hp'],(int)$state['player']['hp']+$heal);
    $state['log'][]=$state['player']['name'].' used '.$item['name'].' and restored '.$heal.' HP.';
    msw_battle_fx($state,'medical',['heal'=>$heal,'medical_item'=>$itemKey]);
    msw_security_backup_assist($state);if(!empty($state['finished']))return [true,'Medical supply deployed; Security backup finished the contact.'];
    msw_enemy_turn($state);return [true,'Medical supply deployed.'];
}

function msw_battle_recommended_move(array $state): ?array {
    if((int)($state['systems']['intel']??1)<4)return null;
    $bestKey=null;$best=-1.0;$bestMult=1.0;
    foreach(msw_move_catalog() as $key=>$move){
        $mult=msw_type_multiplier((string)$move['type'],(string)$state['enemy']['class']);
        $score=(int)$move['power']*$mult*((int)$move['accuracy']/100);
        if($score>$best){$best=$score;$bestKey=$key;$bestMult=$mult;}
    }
    if($bestKey===null)return null;$move=msw_move_catalog()[$bestKey];
    return ['key'=>$bestKey,'name'=>$move['name'],'multiplier'=>$bestMult,'score'=>$best];
}

function msw_try_recovery(int $uid,array &$state,string $itemKey): array {
    if(($state['context']??'field')==='trainer') return [false,'Rival Commander units cannot be extracted during a command duel.'];
    $catalog=msw_fulton_catalog();
    if(!isset($catalog[$itemKey])) return [false,'Unknown recovery system.'];
    $fulton=$catalog[$itemKey];$class=(string)$state['enemy']['class'];
    if(!(int)$state['enemy']['recruitable']) return [false,'This target cannot be recovered.'];
    if(!in_array($class,$fulton['classes'],true)) return [false,$fulton['name'].' cannot recover this target class.'];
    if(!msw_requirements_met($uid,['rd'=>(int)$fulton['rd']])) return [false,'R&D level is too low for this recovery system.'];
    if(!msw_consume_item($uid,$itemKey,1)) return [false,'No '.$fulton['name'].' units remain.'];
    $chance=msw_recovery_chance($state,$fulton);$roll=random_int(1,10000)/10000;$percent=(int)round($chance*100);
    msw_battle_fx($state,'recovery',['recovery_item'=>$itemKey,'recovery_chance'=>$percent]);
    if($roll<=$chance){
        msw_create_recruit($uid,$state['enemy']);$state['finished']=true;$state['result']='recovered';$state['fx']['recovery_success']=true;
        $state['log'][]='Fulton locked. '.$state['enemy']['name'].' recovered successfully ('.$percent.'% calculated chance).';return [true,'Recovery successful.'];
    }
    $state['log'][]='Fulton recovery failed ('.$percent.'% calculated chance).';
    msw_security_backup_assist($state);if(!empty($state['finished']))return [false,'Recovery failed, but Security backup neutralized the target.'];
    msw_enemy_turn($state);return [false,'Recovery failed.'];
}

function msw_create_recruit(int $uid,array $enemy): void {
    $level=max(1,(int)$enemy['level']);
    $seed=random_int(0,8);
    $combat=max(8,min(99,(int)$enemy['attack']+$seed));
    $rd=random_int(8,55);$support=random_int(8,55);$intel=random_int(8,55);$medical=random_int(8,55);$mess=random_int(8,55);$security=random_int(8,55);
    $best=max($combat,$rd,$support,$intel,$medical,$mess,$security);
    $grade=msw_grade_for_score($best);
    $callsign=strtoupper(substr(hash('crc32b',$enemy['name'].'|'.microtime(true).'|'.random_int(1,999999)),0,6)).' '.$enemy['name'];
    msw_stmt(
        'INSERT INTO units(owner_user_id,source_enemy_key,callsign,unit_class,affinity_type,level,hp,max_hp,attack,defense,speed,combat,rd,support,intel,medical,mess,security,grade,assignment,active_combat) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0)',
        'issssiiiiiiiiiiiiiss',
        [$uid,$enemy['key'],$callsign,$enemy['class'],$enemy['type'],$level,(int)$enemy['max_hp'],(int)$enemy['max_hp'],(int)$enemy['attack'],(int)$enemy['defense'],(int)$enemy['speed'],$combat,$rd,$support,$intel,$medical,$mess,$security,$grade,'reserve']
    );
    msw_recalculate_base($uid);
}

function msw_finalize_battle(int $uid,int $encounterId,array &$state): void {
    $result=(string)$state['result'];
    $status=in_array($result,['won','lost','recovered','retreated'],true)?$result:'lost';
    $context=(string)($state['context']??'field');
    $contextKey=(string)($state['context_key']??'');

    if($status==='won'){
        $definition=msw_context_definition($context,$contextKey);
        if($context==='boss'){
            $reward=['gmp'=>4500,'common_metal'=>900,'minor_metal'=>500,'precious_metal'=>140,'fuel'=>700];
            $xp=650;
        }elseif(in_array($context,['mission','sidequest','trainer'],true)){
            $reward=$definition['reward']??['gmp'=>500,'common_metal'=>100];
            $xp=$context==='trainer'?220:($context==='sidequest'?120:180);
        }else{
            $reward=['gmp'=>350,'common_metal'=>90,'fuel'=>55];
            $xp=75;
        }
        msw_grant_resources($uid,$reward);
        if($context==='sidequest' && $definition){
            foreach(($definition['items']??[]) as $item=>$quantity) msw_add_item($uid,(string)$item,(int)$quantity);
        }
        $xpResult=msw_level_up_user($uid,$xp);
        $state['log'][]='Command XP +'.(int)$xpResult['gained'].'.';
        if(!empty($xpResult['leveled'])) $state['log'][]='Commander advanced to Lv '.(int)$xpResult['after_level'].'!';

        if(in_array($context,['mission','sidequest','trainer'],true)){
            $progressKey=$context==='mission'?$contextKey:$context.':'.$contextKey;
            msw_stmt(
                "INSERT INTO mission_progress(user_id,mission_key,clears,last_cleared_at) VALUES(?,?,1,NOW()) ON DUPLICATE KEY UPDATE clears=clears+1,last_cleared_at=NOW()",
                'is',
                [$uid,$progressKey]
            );
        }
    }elseif($status==='recovered'){
        $xpResult=msw_level_up_user($uid,55);
        $state['log'][]='Command XP +'.(int)$xpResult['gained'].' for successful recovery.';
        if(!empty($xpResult['leveled'])) $state['log'][]='Commander advanced to Lv '.(int)$xpResult['after_level'].'!';
    }

    msw_heal_units($uid);
    msw_stmt(
        'UPDATE encounters SET state_json=?,status=?,version=version+1 WHERE id=? AND user_id=?',
        'ssii',
        [json_encode($state,JSON_UNESCAPED_SLASHES),$status,$encounterId,$uid]
    );
}
