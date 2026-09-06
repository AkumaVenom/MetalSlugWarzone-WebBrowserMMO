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
        $state['log'][]='Enemy contact updated to Rebel Biker.';
    }

    // v0.7.5 preserves the accepted v0.7.3/v0.7.4 player-relative enemy LEVEL
    // contract, but adds an explicit underlevel progression gate for dangerous warzones.
    // Existing v3/v4 encounters keep their committed enemy level and roll; only combat
    // stats are recalibrated through the v5 readiness-pressure model while current enemy
    // HP percentage is preserved. Older encounters still receive one deterministic legal
    // level migration before the v5 stat pass.
    $model=(string)($state['scaling']['model']??'');
    if($model!=='warzone_player_threat_window_v5'){
        $context=(string)($state['context']??'field');
        $threat=max(1,(int)($state['enemy']['threat']??$state['scaling']['threat']??1));
        $playerLevel=max(1,(int)($state['player']['level']??$state['scaling']['player_level']??1));
        $oldEnemyLevel=max(1,(int)($state['enemy']['level']??1));
        $window=msw_enemy_level_window($playerLevel,$threat,$context);
        $preservedCommittedLevel=in_array($model,['warzone_player_threat_window_v3','warzone_player_threat_window_v4'],true);
        if($preservedCommittedLevel){
            // v3/v4 already committed a legal level roll when the encounter was created.
            // Preserve that exact level and roll: v0.7.5 changes progression pressure,
            // never grants a refresh-based second chance to reroll the opponent.
            $enemyLevel=$oldEnemyLevel;
            $enemyRoll=[
                'level'=>$enemyLevel,
                'offset'=>(int)($state['scaling']['enemy_level_offset']??($enemyLevel-$playerLevel)),
                'roll'=>max(1,min(100,(int)($state['scaling']['level_roll']??50))),
                'min_offset'=>(int)($state['scaling']['min_offset']??$window['min_offset']),
                'max_offset'=>(int)($state['scaling']['max_offset']??$window['max_offset']),
            ];
        }else{
            $seed=implode('|',[
                $context,
                (string)($state['context_key']??''),
                $key,
                (string)$playerLevel,
                (string)$oldEnemyLevel,
                (string)($state['round']??1),
            ]);
            $migrationRoll=((int)sprintf('%u',crc32($seed))%100)+1;
            $enemyRoll=msw_roll_enemy_level($playerLevel,$threat,$context,$migrationRoll);
            $enemyLevel=(int)$enemyRoll['level'];
        }
        $oldMax=max(1,(int)($state['enemy']['max_hp']??$state['enemy']['hp']??1));
        $oldHp=max(0,(int)($state['enemy']['hp']??$oldMax));
        $hpRatio=max(0.0,min(1.0,$oldHp/$oldMax));
        $scaled=msw_enemy_scaled_stats($entry,$enemyLevel,$threat,$context,$playerLevel);
        $state['enemy']['level']=$enemyLevel;
        $state['enemy']['level_offset']=(int)$enemyRoll['offset'];
        $state['enemy']['max_hp']=(int)$scaled['hp'];
        $state['enemy']['hp']=max(0,(int)round((int)$scaled['hp']*$hpRatio));
        $state['enemy']['attack']=(int)$scaled['attack'];
        $state['enemy']['defense']=(int)$scaled['defense'];
        $state['enemy']['speed']=(int)$scaled['speed'];
        $state['enemy']['threat']=$threat;
        if(!isset($state['scaling'])||!is_array($state['scaling']))$state['scaling']=[];
        $state['scaling']['model']='warzone_player_threat_window_v5';
        $state['scaling']['threat']=$threat;
        $state['scaling']['player_level']=$playerLevel;
        $state['scaling']['enemy_level']=$enemyLevel;
        $state['scaling']['enemy_level_offset']=(int)$enemyRoll['offset'];
        $state['scaling']['level_roll']=(int)$enemyRoll['roll'];
        $state['scaling']['min_offset']=(int)$enemyRoll['min_offset'];
        $state['scaling']['max_offset']=(int)$enemyRoll['max_offset'];
        $pressure=(array)($scaled['readiness_pressure']??[]);
        $state['scaling']['recommended_level']=(int)($pressure['recommended_level']??$playerLevel);
        $state['scaling']['underlevel_gap']=(int)($pressure['underlevel_gap']??0);
        $state['scaling']['underlevel_hp_multiplier']=(float)($pressure['hp_multiplier']??1.0);
        $state['scaling']['underlevel_attack_multiplier']=(float)($pressure['attack_multiplier']??1.0);
        $state['scaling']['underlevel_defense_multiplier']=(float)($pressure['defense_multiplier']??1.0);
        $state['log'][]=$preservedCommittedLevel?'High-risk warzone pressure recalibrated this active encounter without rerolling its committed enemy level.':'Threat-aware enemy scaling migrated this older active encounter into the v0.7.5 progression-gate model.';
    }
}

function msw_battle_system_snapshot(int $uid): array {
    $levels=msw_sector_levels($uid);
    return [
        'combat'=>(int)($levels['combat']??1),
        'rd'=>(int)($levels['rd']??1),
        'medical'=>(int)($levels['medical']??1),
        'intel'=>(int)($levels['intel']??1),
        'security'=>(int)($levels['security']??1),
        'support'=>(int)($levels['support']??1),
        'mess'=>(int)($levels['mess']??1),
    ];
}

function msw_battle_threat_level(string $context,string $contextKey,?array $definition=null): int {
    if($context==='field') return max(1,(int)(msw_map_catalog()[$contextKey]['level']??1));
    $definition=$definition??msw_context_definition($context,$contextKey);
    if($definition && isset($definition['level'])) return max(1,(int)$definition['level']);
    return $context==='boss'?12:1;
}

/**
 * Progression-gate pressure for ordinary warzones.
 *
 * Enemy LEVEL remains player-relative and threat-aware. This second axis exists so a
 * very low-level Commander cannot comfortably clear late warzones with only starter
 * stats plus light Security support. The pressure disappears as Commander level reaches
 * the warzone's readiness benchmark; Mother Base growth remains the player's direct way
 * to overcome the same enemy at a lower personal level through higher HP/ATK/DEF/SPD.
 */
function msw_warzone_readiness_pressure(int $playerLevel,int $threat,string $context='field'): array {
    $playerLevel=max(1,$playerLevel);
    $threatCap=min(12,max(1,$threat));
    if($context==='boss'){
        return [
            'recommended_level'=>$playerLevel,'underlevel_gap'=>0,
            'hp_multiplier'=>1.0,'attack_multiplier'=>1.0,'defense_multiplier'=>1.0,'speed_multiplier'=>1.0,
            'counter_accuracy_bonus'=>0,
        ];
    }

    // These are readiness benchmarks, not hard entry requirements. They shape only the
    // extra underlevel pressure; the authoritative enemy level window still follows the
    // player-relative threat model below.
    $recommended=match($threatCap){
        1=>1,2=>2,3=>3,4=>5,5=>6,6=>7,7=>9,8=>10,9=>12,10=>13,11=>15,default=>16,
    };
    $gap=$threatCap>=4?max(0,$recommended-$playerLevel):0;

    return [
        'recommended_level'=>$recommended,
        'underlevel_gap'=>$gap,
        // HP/ATK are the main gate. DEF rises more gently to avoid pure sponge fights;
        // SPD only receives a small lift so Commander SPD investment remains meaningful.
        'hp_multiplier'=>1.0+min(0.55,$gap*0.045),
        'attack_multiplier'=>1.0+min(0.85,$gap*0.070),
        'defense_multiplier'=>1.0+min(0.35,$gap*0.030),
        'speed_multiplier'=>1.0+min(0.18,$gap*0.015),
        // Underlevel late-warzone contacts also execute counters more reliably. Intel/SPD
        // reductions still apply afterwards, so those upgrades retain their value.
        'counter_accuracy_bonus'=>$threatCap>=5?min(6,(int)ceil($gap*0.55)):0,
    ];
}

function msw_enemy_level_window(int $playerLevel,int $threat,string $context='field'): array {
    $playerLevel=max(1,$playerLevel);
    $threat=max(1,$threat);

    // Bosses already carry exceptional authored base stats, so they keep a tighter
    // player-relative window than ordinary warzone contacts.
    if($context==='boss'){
        $maxOffset=$threat>=12?3:2;
        $spread=$playerLevel>=20?4:($playerLevel>=10?3:2);
        return [
            'min_offset'=>max(-1,$maxOffset-$spread),
            'max_offset'=>$maxOffset,
        ];
    }

    // Warzone threat sets the upper edge of the level window. Threat 12 can reach
    // Commander +5; early maps stay at or around the Commander instead of inheriting
    // the same late-game ceiling.
    $threatCap=min(12,$threat);
    $maxOffset=match(true){
        $threatCap<=1=>0,
        $threatCap<=3=>1,
        $threatCap<=5=>2,
        $threatCap<=7=>3,
        $threatCap<=9=>4,
        default=>5,
    };

    // As the Commander matures the window deliberately widens downward to preserve
    // encounter variety. This gives the requested Threat 12 behavior:
    //   Commander Lv5  -> enemy Lv8..10  (+3..+5)
    //   Commander Lv20 -> enemy Lv19..25 (-1..+5)
    $spread=match(true){
        $playerLevel<=5=>2,
        $playerLevel<=9=>3,
        $playerLevel<=14=>4,
        $playerLevel<=19=>5,
        default=>6,
    };
    $minOffset=max(-3,$maxOffset-$spread);
    return ['min_offset'=>$minOffset,'max_offset'=>$maxOffset];
}

function msw_enemy_level_offset_for_roll(int $playerLevel,int $threat,int $roll,string $context='field'): int {
    $playerLevel=max(1,$playerLevel);
    $threat=max(1,$threat);
    $roll=max(1,min(100,$roll));
    $window=msw_enemy_level_window($playerLevel,$threat,$context);
    $minOffset=(int)$window['min_offset'];
    $maxOffset=(int)$window['max_offset'];
    if($minOffset>=$maxOffset)return $minOffset;

    // Blend from lower-offset weighting on safe maps toward upper-offset weighting
    // on dangerous maps. A compressed low-level Threat 12 window therefore strongly
    // favors +4/+5, while a mature Commander still receives the full -1..+5 variety.
    $threatCap=min(12,$threat);
    $bias=max(0.10,min(0.93,0.10+(($threatCap-1)*(0.83/11))));
    $offsets=range($minOffset,$maxOffset);
    $count=count($offsets);
    $weights=[];
    $total=0.0;
    foreach($offsets as $index=>$offset){
        $lowWeight=$count-$index;
        $highWeight=$index+1;
        $weight=((1.0-$bias)*$lowWeight)+($bias*$highWeight);
        $weights[(int)$offset]=$weight;
        $total+=$weight;
    }

    $target=(($roll-0.5)/100.0)*$total;
    $cursor=0.0;
    foreach($weights as $offset=>$weight){
        $cursor+=$weight;
        if($target<=$cursor)return (int)$offset;
    }
    return $maxOffset;
}

function msw_roll_enemy_level(int $playerLevel,int $threat,string $context='field',?int $forcedRoll=null): array {
    $playerLevel=max(1,$playerLevel);
    $threat=max(1,$threat);
    $roll=$forcedRoll===null?random_int(1,100):max(1,min(100,$forcedRoll));
    $window=msw_enemy_level_window($playerLevel,$threat,$context);
    $offset=msw_enemy_level_offset_for_roll($playerLevel,$threat,$roll,$context);
    return [
        'level'=>max(1,$playerLevel+$offset),
        'offset'=>$offset,
        'roll'=>$roll,
        'min_offset'=>(int)$window['min_offset'],
        'max_offset'=>(int)$window['max_offset'],
    ];
}

function msw_enemy_scaled_stats(array $enemy,int $enemyLevel,int $threat,string $context,int $playerLevel=1): array {
    $enemyLevel=max(1,$enemyLevel);
    $threat=max(1,$threat);
    $playerLevel=max(1,$playerLevel);
    $boss=$context==='boss'||(string)($enemy['class']??'')==='boss';
    $step=$enemyLevel-1;

    if($boss){
        // Boss catalog values are already extreme. Keep their level/threat growth
        // controlled so the normal warzone curve does not multiply them excessively.
        $levelRates=['hp'=>0.014,'attack'=>0.012,'defense'=>0.011,'speed'=>0.005];
        $threatCap=min(15,$threat);
        $threatProgress=max(0.0,min(1.0,($threatCap-1)/14.0));
        $threatPressure=pow($threatProgress,1.18);
        $threatFactors=[
            'hp'=>0.98+(0.24*$threatPressure),
            'attack'=>0.98+(0.18*$threatPressure),
            'defense'=>0.99+(0.14*$threatPressure),
            'speed'=>0.995+(0.05*$threatPressure),
        ];
    }else{
        // Ordinary enemies scale from BOTH the rolled player-relative level and
        // warzone threat. v0.7.5 adds a third, deliberately conditional axis: if a very
        // low-level Commander pushes above the warzone's readiness benchmark, the contact
        // gains extra HP/ATK/DEF pressure. This is what makes Threat 7/9/12 genuine
        // progression gates without globally punishing properly levelled Commanders.
        $levelRates=['hp'=>0.030,'attack'=>0.028,'defense'=>0.024,'speed'=>0.010];
        $threatCap=min(12,$threat);
        $threatProgress=max(0.0,min(1.0,($threatCap-1)/11.0));
        $threatPressure=pow($threatProgress,1.10);
        $threatFactors=[
            // Preserve the accepted Threat 1 baseline exactly, then widen the curve.
            'hp'=>0.94+(0.54*$threatPressure),
            'attack'=>0.96+(0.44*$threatPressure),
            'defense'=>0.96+(0.26*$threatPressure),
            'speed'=>0.98+(0.10*$threatPressure),
        ];
    }

    $pressure=$boss?msw_warzone_readiness_pressure($playerLevel,$threat,'boss'):msw_warzone_readiness_pressure($playerLevel,$threat,$context);
    $pressureFactors=[
        'hp'=>(float)$pressure['hp_multiplier'],
        'attack'=>(float)$pressure['attack_multiplier'],
        'defense'=>(float)$pressure['defense_multiplier'],
        'speed'=>(float)$pressure['speed_multiplier'],
    ];
    $factors=[];
    foreach($levelRates as $stat=>$rate){
        $factors[$stat]=(1.0+($step*$rate))*$threatFactors[$stat]*$pressureFactors[$stat];
    }
    return [
        'hp'=>max(1,(int)round((int)$enemy['hp']*$factors['hp'])),
        'attack'=>max(1,(int)round((int)$enemy['atk']*$factors['attack'])),
        'defense'=>max(1,(int)round((int)$enemy['def']*$factors['defense'])),
        'speed'=>max(1,(int)round((int)$enemy['spd']*$factors['speed'])),
        'factors'=>$factors,
        'threat_factors'=>$threatFactors,
        'readiness_pressure'=>$pressure,
    ];
}

function msw_merge_security_backup_runtime(array $fresh,array $existing): array {
    $old=[];
    foreach($existing as $backup){
        if(isset($backup['unit_id']))$old[(int)$backup['unit_id']]=$backup;
    }
    foreach($fresh as &$backup){
        $unitId=(int)($backup['unit_id']??0);
        if($unitId>0 && isset($old[$unitId])){
            $oldMax=max(1,(int)($old[$unitId]['max_hp']??$backup['max_hp']??1));
            $oldHp=max(0,(int)($old[$unitId]['hp']??$oldMax));
            $ratio=max(0.0,min(1.0,$oldHp/$oldMax));
            $backup['hp']=max(0,(int)round((int)$backup['max_hp']*$ratio));
        }
    }
    unset($backup);
    return $fresh;
}

function msw_battle_fx(array &$state,string $action,array $extra=[]): void {
    $seq=(int)($state['fx']['seq']??0)+1;
    $state['fx']=array_merge([
        'seq'=>$seq,'kind'=>'turn','action'=>$action,'player_hit'=>false,'enemy_counter'=>false,'enemy_hit'=>false,
        'backup_slots'=>[],'backup_guard_slot'=>0,'backup_guard_damage'=>0,'heal'=>0,'recovery_success'=>false,
    ],$extra);
}

function msw_new_battle_state(int $uid,string $enemyKey,string $context='field',string $contextKey=''): array {
    $enemies=msw_enemy_catalog();
    if(!isset($enemies[$enemyKey])) throw new InvalidArgumentException('That enemy is unavailable.');
    $enemy=$enemies[$enemyKey];
    $lead=msw_commander_fighter($uid);
    $definition=msw_context_definition($context,$contextKey);
    $threat=msw_battle_threat_level($context,$contextKey,$definition);
    $leadLevel=max(1,(int)($lead['level']??1));
    $enemyRoll=msw_roll_enemy_level($leadLevel,$threat,$context);
    $enemyLevel=(int)$enemyRoll['level'];
    $scaled=msw_enemy_scaled_stats($enemy,$enemyLevel,$threat,$context,$leadLevel);
    $enemyName=(string)$enemy['name'];
    if($context==='trainer' && $definition) $enemyName=(string)$definition['name'].' · '.$enemyName;
    $contactLog=[
        'Contact! '.$enemyName.' entered the combat zone.',
        'Threat '.$threat.' contact calibrated at Lv '.$enemyLevel.' against Commander Lv '.$leadLevel.' (window '.(($enemyRoll['min_offset']??0)>=0?'+':'').(int)($enemyRoll['min_offset']??0).' to '.(($enemyRoll['max_offset']??0)>=0?'+':'').(int)($enemyRoll['max_offset']??0).').',
    ];
    $underlevelGap=(int)($scaled['readiness_pressure']['underlevel_gap']??0);
    $recommendedLevel=(int)($scaled['readiness_pressure']['recommended_level']??$leadLevel);
    if($underlevelGap>0 && $context!=='boss'){
        $contactLog[]='High-risk deployment: this Threat '.$threat.' warzone is calibrated around Commander Lv '.$recommendedLevel.' readiness. Mother Base development is strongly advised.';
    }

    return [
        'round'=>1,
        'log'=>$contactLog,
        'player'=>[
            'unit_id'=>0,'name'=>(string)$lead['name'],'class'=>(string)$lead['class'],'type'=>(string)$lead['type'],
            'level'=>$leadLevel,'hp'=>(int)$lead['max_hp'],'max_hp'=>(int)$lead['max_hp'],'attack'=>(int)$lead['attack'],'defense'=>(int)$lead['defense'],'speed'=>(int)$lead['speed'],
        ],
        'enemy'=>[
            'key'=>$enemyKey,'name'=>$enemyName,'class'=>$enemy['class'],'type'=>$enemy['type'],'sprite'=>$enemy['sprite'],'recruitable'=>$context==='trainer'?0:(int)$enemy['recruitable'],
            'level'=>$enemyLevel,'level_offset'=>(int)$enemyRoll['offset'],'threat'=>$threat,
            'hp'=>(int)$scaled['hp'],'max_hp'=>(int)$scaled['hp'],
            'attack'=>(int)$scaled['attack'],'defense'=>(int)$scaled['defense'],'speed'=>(int)$scaled['speed'],
        ],
        'scaling'=>[
            'model'=>'warzone_player_threat_window_v5',
            'threat'=>$threat,
            'player_level'=>$leadLevel,
            'enemy_level'=>$enemyLevel,
            'enemy_level_offset'=>(int)$enemyRoll['offset'],
            'level_roll'=>(int)$enemyRoll['roll'],
            'min_offset'=>(int)$enemyRoll['min_offset'],
            'max_offset'=>(int)$enemyRoll['max_offset'],
            'recommended_level'=>(int)($scaled['readiness_pressure']['recommended_level']??$leadLevel),
            'underlevel_gap'=>(int)($scaled['readiness_pressure']['underlevel_gap']??0),
            'underlevel_hp_multiplier'=>(float)($scaled['readiness_pressure']['hp_multiplier']??1.0),
            'underlevel_attack_multiplier'=>(float)($scaled['readiness_pressure']['attack_multiplier']??1.0),
            'underlevel_defense_multiplier'=>(float)($scaled['readiness_pressure']['defense_multiplier']??1.0),
        ],
        'systems'=>msw_battle_system_snapshot($uid),
        'backups'=>msw_security_backup_fighters($uid),
        'fx'=>['seq'=>1,'kind'=>'contact','action'=>'contact','player_hit'=>false,'enemy_counter'=>false,'enemy_hit'=>false,'backup_slots'=>[],'backup_guard_slot'=>0,'backup_guard_damage'=>0,'heal'=>0,'recovery_success'=>false],
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
    $state['backups']=msw_merge_security_backup_runtime(msw_security_backup_fighters($uid),(array)($state['backups']??[]));
}

function msw_start_encounter(int $uid,string $enemyKey,string $context='field',string $contextKey=''): int {
    $active=msw_active_encounter($uid);
    if($active) return (int)$active['id'];
    if(!in_array($context,['field','mission','sidequest','trainer','boss'],true)) throw new InvalidArgumentException('That battle is unavailable.');
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
    $accuracy=min(86,72+($security>=4?6:0)+(int)floor(max(0,$security-1)/4));
    $capRate=(string)($state['enemy']['class']??'')==='boss'?0.045:($security>=7?0.11:0.09);
    $hits=[];
    foreach((array)($state['backups']??[]) as $index=>$backup){
        if((int)$state['enemy']['hp']<=0)break;
        if((int)($backup['hp']??1)<=0)continue;
        if(random_int(1,100)>$accuracy){$state['log'][]=(string)$backup['name'].' fired backup cover but missed.';continue;}
        $mult=msw_type_multiplier((string)$backup['type'],(string)$state['enemy']['class']);
        $supportMultiplier=$security>=7?0.82:0.74;
        $damage=msw_damage(12,(int)$backup['attack'],(int)$state['enemy']['defense'],$mult*$supportMultiplier);
        $cap=max(2,(int)floor((int)$state['enemy']['max_hp']*$capRate));
        $damage=max(1,min($cap,$damage));
        $state['enemy']['hp']=max(0,(int)$state['enemy']['hp']-$damage);
        $slot=(int)($backup['slot']??0);$hits[]=$slot;
        $state['log'][]=(string)$backup['name'].' landed backup fire for '.$damage.' damage.';
    }
    if(isset($state['fx'])&&is_array($state['fx']))$state['fx']['backup_slots']=$hits;
    if((int)$state['enemy']['hp']<=0){$state['finished']=true;$state['result']='won';}
    return $hits;
}

function msw_security_backup_guard(array &$state,int $incomingDamage): array {
    $incomingDamage=max(0,$incomingDamage);
    if($incomingDamage<=0)return ['damage'=>0,'absorbed'=>0,'slot'=>0,'name'=>''];
    $eligible=[];
    foreach((array)($state['backups']??[]) as $index=>$backup){
        if((int)($backup['hp']??0)>0)$eligible[]=$index;
    }
    if(!$eligible)return ['damage'=>$incomingDamage,'absorbed'=>0,'slot'=>0,'name'=>''];

    // Rotate intercept duty between active escorts so both party members matter.
    $pick=$eligible[((max(1,(int)($state['round']??1))-1)%count($eligible))];
    $backup=&$state['backups'][$pick];
    $securityLevel=max(1,(int)($state['systems']['security']??1));
    $securityStat=max(0,(int)($backup['security']??0));
    $rate=min(0.40,0.20+min(0.14,max(0,$securityLevel-1)*0.015)+min(0.06,$securityStat/1000));
    $absorbed=max(1,(int)round($incomingDamage*$rate));
    $absorbed=min($incomingDamage,max(0,(int)$backup['hp']),$absorbed);
    if($absorbed<=0){unset($backup);return ['damage'=>$incomingDamage,'absorbed'=>0,'slot'=>0,'name'=>''];}

    $backup['hp']=max(0,(int)$backup['hp']-$absorbed);
    $slot=(int)($backup['slot']??0);
    $name=(string)($backup['name']??'Security escort');
    if(isset($state['fx'])&&is_array($state['fx'])){
        $state['fx']['backup_guard_slot']=$slot;
        $state['fx']['backup_guard_damage']=$absorbed;
    }
    if((int)$backup['hp']<=0)$state['log'][]=$name.' was knocked out while shielding the Commander.';
    unset($backup);
    return ['damage'=>max(0,$incomingDamage-$absorbed),'absorbed'=>$absorbed,'slot'=>$slot,'name'=>$name];
}

function msw_player_attack_profile(array $state,array $move): array {
    $base=max(1,min(100,(int)($move['accuracy']??100)));
    $speed=max(1,(int)($state['player']['speed']??1));
    $combat=max(1,(int)($state['systems']['combat']??1));
    $intel=max(1,(int)($state['systems']['intel']??1));
    // SPD can only help the Commander. It never creates an enemy-speed penalty.
    // Even low-accuracy attack patterns retain a forgiving PvE floor so progression
    // does not feel like repeated coin-flip misses.
    $baseline=max(94,$base);
    $speedBonus=min(5,(int)floor($speed/12));
    $combatBonus=min(3,(int)floor(max(0,$combat-1)/3));
    $intelBonus=$intel>=4?1:0;
    $accuracy=min(100,$baseline+$speedBonus+$combatBonus+$intelBonus);
    return [
        'base_accuracy'=>$base,
        'speed_bonus'=>$speedBonus,
        'combat_bonus'=>$combatBonus,
        'intel_bonus'=>$intelBonus,
        'accuracy'=>$accuracy,
    ];
}

function msw_battle_attack(array &$state,string $moveKey): void {
    $moves=msw_move_catalog();
    if(!isset($moves[$moveKey])) $moveKey='rifle_burst';
    $move=$moves[$moveKey];$profile=msw_player_attack_profile($state,$move);$hit=false;$damage=0;
    if(random_int(1,100) <= (int)$profile['accuracy']){
        $hit=true;$multiplier=msw_type_multiplier((string)$move['type'],(string)$state['enemy']['class']);
        $damage=msw_damage((int)$move['power'],(int)$state['player']['attack'],(int)$state['enemy']['defense'],$multiplier);
        $state['enemy']['hp']=max(0,(int)$state['enemy']['hp']-$damage);
        $tag=$multiplier>=1.35?' Super effective!':($multiplier<=0.65?' Not very effective.':'');
        $state['log'][]=$state['player']['name'].' used '.$move['name'].' for '.$damage.' damage.'.$tag;
    }else{$state['log'][]=$state['player']['name'].' missed with '.$move['name'].'.';}
    msw_battle_fx($state,'attack',['player_hit'=>$hit,'damage'=>$damage,'move'=>$moveKey]);
    if((int)$state['enemy']['hp']<=0){$state['finished']=true;$state['result']='won';return;}
    msw_security_backup_assist($state);
    if(!empty($state['finished']))return;
    msw_enemy_turn($state);
}

function msw_enemy_counter_profile(array $state): array {
    $intel=max(1,(int)($state['systems']['intel']??1));
    $intelReduction=$intel>=8?6:0;
    $playerSpeed=max(1,(int)($state['player']['speed']??1));
    $enemySpeed=max(1,(int)($state['enemy']['speed']??1));
    $baseSpeedReduction=min(6,(int)floor($playerSpeed/15));
    $advantageReduction=min(6,(int)floor(max(0,$playerSpeed-$enemySpeed)/4));
    $speedReduction=min(10,$baseSpeedReduction+$advantageReduction);
    $threat=max(1,(int)($state['enemy']['threat']??$state['scaling']['threat']??1));
    $threatCap=min(12,$threat);
    $threatProgress=max(0.0,min(1.0,($threatCap-1)/11.0));
    $threatPressure=pow($threatProgress,1.10);
    // High-threat normal contacts should execute their counters more reliably, but SPD
    // and Intel still subtract cleanly from that pressure. Boss accuracy is deliberately
    // left on the established profile because authored boss stats already provide their
    // separate difficulty curve. Threat never creates a player attack-accuracy penalty.
    $isBoss=(string)($state['enemy']['class']??'')==='boss';
    $threatAccuracyBonus=$isBoss?0:(int)round(4*$threatPressure);
    $playerLevel=max(1,(int)($state['player']['level']??$state['scaling']['player_level']??1));
    $readiness=msw_warzone_readiness_pressure($playerLevel,$threat,$isBoss?'boss':(string)($state['context']??'field'));
    $underlevelAccuracyBonus=$isBoss?0:(int)($readiness['counter_accuracy_bonus']??0);
    $baseAccuracy=min(99,88+$threatAccuracyBonus+$underlevelAccuracyBonus);
    return [
        'base_accuracy'=>$baseAccuracy,
        'threat_accuracy_bonus'=>$threatAccuracyBonus,
        'underlevel_accuracy_bonus'=>$underlevelAccuracyBonus,
        'intel_reduction'=>$intelReduction,
        'speed_reduction'=>$speedReduction,
        'accuracy'=>max(55,$baseAccuracy-$intelReduction-$speedReduction),
    ];
}

function msw_enemy_counter_power(array $state): int {
    $class=(string)($state['enemy']['class']??'infantry');
    $threat=max(1,(int)($state['enemy']['threat']??$state['scaling']['threat']??1));
    $base=match($class){
        'heavy_infantry'=>16,
        'vehicle','air'=>17,
        'boss'=>22,
        default=>14,
    };
    $cap=$class==='boss'?15:12;
    $progress=max(0.0,min(1.0,(min($cap,$threat)-1)/max(1,$cap-1)));
    // Normal high-threat contacts gain a little more move pressure than v0.7.3.
    // Enemy ATK is still consumed exactly once by msw_damage(); this does not restore
    // the old ATK×ATK escalation bug.
    $threatBonus=(int)round(($class==='boss'?7:8)*pow($progress,1.10));
    return min($class==='boss'?31:27,$base+$threatBonus);
}

function msw_enemy_turn(array &$state): void {
    $intel=max(1,(int)($state['systems']['intel']??1));
    $counter=msw_enemy_counter_profile($state);
    $accuracy=(int)$counter['accuracy'];
    $move=['name'=>'Counterattack','type'=>$state['enemy']['type'],'power'=>msw_enemy_counter_power($state),'accuracy'=>$accuracy];
    $hit=false;$damage=0;
    if(random_int(1,100)<=(int)$move['accuracy']){
        $hit=true;$multiplier=msw_type_multiplier((string)$move['type'],(string)$state['player']['class']);
        $rawDamage=msw_damage((int)$move['power'],(int)$state['enemy']['attack'],(int)$state['player']['defense'],$multiplier);
        $guard=msw_security_backup_guard($state,$rawDamage);
        $damage=(int)$guard['damage'];
        $state['player']['hp']=max(0,(int)$state['player']['hp']-$damage);
        $guardText=(int)$guard['absorbed']>0?' after '.(string)$guard['name'].' absorbed '.(int)$guard['absorbed']:'';
        $state['log'][]=$state['enemy']['name'].' countered for '.$damage.' Commander damage'.$guardText.'.';
    }else{
        $reason=$intel>=8?' after your Intel Team called the move':((int)$counter['speed_reduction']>0?' after your Commander used the speed advantage to evade':'');
        $state['log'][]=$state['enemy']['name'].' missed its counterattack'.$reason.'.';
    }
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
    $items=msw_battle_item_catalog();if(!isset($items[$itemKey]))return [false,'That medical item is unavailable.'];
    $item=$items[$itemKey];
    if(!msw_requirements_met($uid,(array)$item['requirements']))return [false,'You have not unlocked '.$item['name'].'.'];
    $missing=max(0,(int)$state['player']['max_hp']-(int)$state['player']['hp']);if($missing<=0)return [false,'Commander HP is already full.'];
    if(!msw_consume_item($uid,$itemKey,1))return [false,'No '.$item['name'].' units remain.'];
    $heal=min($missing,max(1,(int)round((int)$item['heal']*msw_battle_medical_multiplier($state))));
    $state['player']['hp']=min((int)$state['player']['max_hp'],(int)$state['player']['hp']+$heal);
    $state['log'][]=$state['player']['name'].' used '.$item['name'].' and restored '.$heal.' HP.';
    msw_battle_fx($state,'medical',['heal'=>$heal,'medical_item'=>$itemKey]);
    msw_security_backup_assist($state);if(!empty($state['finished']))return [true,'Medical item used; your Security backup finished the enemy.'];
    msw_enemy_turn($state);return [true,'Medical item used.'];
}

function msw_battle_recommended_move(array $state): ?array {
    if((int)($state['systems']['intel']??1)<4)return null;
    $bestKey=null;$best=-1.0;$bestMult=1.0;
    foreach(msw_move_catalog() as $key=>$move){
        $mult=msw_type_multiplier((string)$move['type'],(string)$state['enemy']['class']);
        $profile=msw_player_attack_profile($state,$move);
        $score=(int)$move['power']*$mult*((int)$profile['accuracy']/100);
        if($score>$best){$best=$score;$bestKey=$key;$bestMult=$mult;}
    }
    if($bestKey===null)return null;$move=msw_move_catalog()[$bestKey];
    return ['key'=>$bestKey,'name'=>$move['name'],'multiplier'=>$bestMult,'score'=>$best];
}

function msw_try_recovery(int $uid,array &$state,string $itemKey): array {
    if(($state['context']??'field')==='trainer') return [false,'Rival Commander units cannot be extracted during a command duel.'];
    $catalog=msw_fulton_catalog();
    if(!isset($catalog[$itemKey])) return [false,'That Fulton system is unavailable.'];
    $fulton=$catalog[$itemKey];$class=(string)$state['enemy']['class'];
    if(!(int)$state['enemy']['recruitable']) return [false,'This target cannot be recovered.'];
    if(!in_array($class,$fulton['classes'],true)) return [false,$fulton['name'].' cannot recover this target class.'];
    if(!msw_requirements_met($uid,['rd'=>(int)$fulton['rd']])) return [false,'Your R&D Team level is too low for this Fulton system.'];
    if(!msw_consume_item($uid,$itemKey,1)) return [false,'No '.$fulton['name'].' units remain.'];
    $chance=msw_recovery_chance($state,$fulton);$roll=random_int(1,10000)/10000;$percent=(int)round($chance*100);
    msw_battle_fx($state,'recovery',['recovery_item'=>$itemKey,'recovery_chance'=>$percent]);
    if($roll<=$chance){
        msw_create_recruit($uid,$state['enemy']);$state['finished']=true;$state['result']='recovered';$state['fx']['recovery_success']=true;
        $state['log'][]='Fulton secured! '.$state['enemy']['name'].' recovered ('.$percent.'% chance).';return [true,'Recovery successful.'];
    }
    $state['log'][]='Fulton recovery failed ('.$percent.'% chance).';
    msw_security_backup_assist($state);if(!empty($state['finished']))return [false,'Fulton failed, but your Security backup finished the target.'];
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
