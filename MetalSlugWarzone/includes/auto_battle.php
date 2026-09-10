<?php
declare(strict_types=1);

/**
 * Automatic operations battle playback.
 *
 * IMPORTANT AUTHORITY CONTRACT
 * ----------------------------
 * Dispatch/FOB systems settle the authoritative result before this code runs.
 * This module never rerolls success, rewards, transfers, shields, XP, staff
 * availability, or any other gameplay state. It only builds a deterministic
 * battle-film from the persisted result and the force snapshots.
 */

function msw_auto_battle_hash_int(string $seed,int $index,int $min,int $max): int {
    if($max<=$min)return $min;
    $hex=substr(hash('sha256',$seed.'|'.$index),0,8);
    $value=(int)hexdec($hex);
    return $min+($value%($max-$min+1));
}

function msw_auto_battle_default_enemy_key(array $unit,int $index=0): string {
    $source=(string)($unit['source_enemy_key']??'');
    if($source!==''&&isset(msw_enemy_catalog()[$source]))return $source;
    $class=(string)($unit['unit_class']??$unit['class']??'infantry');
    if($class==='vehicle')return 'biker';
    if($class==='heavy_infantry'){
        $name=(string)($unit['callsign']??$unit['name']??'');
        return ((int)sprintf('%u',crc32($name.'|'.$index))%2)===0?'bazooka':'minigun';
    }
    return 'rifle';
}

function msw_auto_battle_fallback_max_hp(array $unit,array $enemy): int {
    $explicit=max(0,(int)($unit['max_hp']??0));
    if($explicit>0)return $explicit;
    $base=max(30,(int)($enemy['hp']??54));
    $level=max(1,(int)($unit['level']??1));
    $combat=max(1,(int)($unit['combat']??($unit['power']??20)));
    return max(45,min(999,(int)round($base+($level-1)*3.2+$combat*.72)));
}

function msw_auto_battle_unit(array $unit,int $index,string $fallbackName='Combat Unit'): array {
    $enemyKey=msw_auto_battle_default_enemy_key($unit,$index);
    $enemy=msw_enemy_catalog()[$enemyKey]??msw_enemy_catalog()['rifle'];
    $name=trim((string)($unit['callsign']??$unit['name']??''));
    if($name==='')$name=$fallbackName.' '.($index+1);
    $class=(string)($unit['unit_class']??$unit['class']??$enemy['class']??'infantry');
    $level=max(1,(int)($unit['level']??1));
    $combat=max(1,(int)($unit['combat']??($unit['power']??20)));
    $grade=(string)($unit['grade']??'--');
    $maxHp=msw_auto_battle_fallback_max_hp($unit,$enemy);
    $sourceHp=(int)($unit['hp']??$maxHp);
    // Operations begin from a combat-readiness snapshot. Clamp impossible legacy
    // values, but preserve a real stored staff HP value whenever one exists.
    $hp=max(1,min($maxHp,$sourceHp>0?$sourceHp:$maxHp));
    return [
        'id'=>(int)($unit['id']??($index+1)),
        'name'=>$name,
        'class'=>$class,
        'level'=>$level,
        'combat'=>$combat,
        'grade'=>$grade,
        'sprite'=>(string)$enemy['sprite'],
        'vehicle'=>in_array($class,['vehicle','air'],true),
        'hp'=>$hp,
        'max_hp'=>$maxHp,
        'attack'=>max(1,(int)($unit['attack']??($enemy['atk']??max(8,(int)round($combat*.65))))),
        'defense'=>max(1,(int)($unit['defense']??($enemy['def']??max(6,(int)round($combat*.48))))),
        'speed'=>max(1,(int)($unit['speed']??($enemy['spd']??10))),
    ];
}

function msw_auto_battle_normalize_units(array $units,string $fallbackName='Combat Unit'): array {
    $out=[];
    foreach(array_slice(array_values($units),0,4) as $i=>$unit){
        if(!is_array($unit))continue;
        $out[]=msw_auto_battle_unit($unit,(int)$i,$fallbackName);
    }
    if(!$out)$out[]=msw_auto_battle_unit(['callsign'=>$fallbackName,'unit_class'=>'infantry','level'=>1,'combat'=>20,'grade'=>'--'],0,$fallbackName);
    return $out;
}

/**
 * FOB authority always includes base power and Security, even when a commander
 * has no active-combat staff assigned. For playback we therefore render an
 * explicit base-defense element instead of leaving the entire enemy side blank.
 */
function msw_auto_battle_fob_force(array $snapshot,string $name,bool $defense): array {
    $team=(array)($snapshot['team']??[]);
    if($team)return msw_auto_battle_normalize_units($team,$name.' Unit');

    $security=max(0,(int)($snapshot['security']['score']??0));
    $basePower=max(1,(int)($snapshot['user']['base_power']??1));
    $count=$security>=180?4:($security>=80?3:2);
    $keys=$defense?['shield','rifle','minigun','bazooka']:['rifle','bazooka','minigun','biker'];
    $out=[];
    for($i=0;$i<$count;$i++){
        $key=$keys[$i%count($keys)];
        $meta=msw_enemy_catalog()[$key]??msw_enemy_catalog()['rifle'];
        $combat=max(12,(int)round(($basePower/110)+($security/max(1,$count*4)))+$i*2);
        $level=max(1,(int)round($basePower/900)+1+$i);
        $out[]=[
            'id'=>800000+$i,
            'callsign'=>$defense?'FOB Security '.str_pad((string)($i+1),2,'0',STR_PAD_LEFT):'Assault Element '.str_pad((string)($i+1),2,'0',STR_PAD_LEFT),
            'unit_class'=>(string)$meta['class'],'level'=>$level,'combat'=>$combat,
            'grade'=>(string)($snapshot['security']['grade']??$snapshot['user']['base_grade']??'--'),
            'source_enemy_key'=>$key,
        ];
    }
    return msw_auto_battle_normalize_units($out,$name.($defense?' Defense':' Assault'));
}

function msw_auto_battle_dispatch_opposition(array $definition,int $slots,string $seed=''): array {
    $difficulty=max(1,(int)($definition['difficulty']??50));
    $keys=['rifle','bazooka','minigun','biker'];
    $map=msw_map_catalog()[(string)($definition['map_key']??'')]??null;
    $authored=!empty($definition['enemies'])&&is_array($definition['enemies']);
    if($authored)$keys=array_values($definition['enemies']);
    elseif($map)$keys=array_values($map['encounters']);
    // Target-specific operations commit their lead target and escorts through
    // the catalog. Preserve that authored order, including boss targets.
    // Expansion pools can exceed the four-unit replay limit. Rotate their full
    // local roster deterministically so later contacts (including aircraft) can
    // appear, without rerolling a report or changing settled mission authority.
    // Keep the established selection for legacy pools of four or fewer enemies.
    $offset=$map&&!$authored&&count($keys)>4?msw_auto_battle_hash_int('dispatch-opposition|'.(string)($definition['map_key']??'').'|'.$seed,0,0,count($keys)-1):0;
    $out=[];$count=max(2,min(4,$slots));
    for($i=0;$i<$count;$i++){
        $key=($map||$authored)?$keys[($offset+$i)%count($keys)]:$keys[min(count($keys)-1,(int)floor(($difficulty/120)+$i/2))];
        $meta=msw_enemy_catalog()[$key]??msw_enemy_catalog()['rifle'];
        $level=$map?msw_warzone_enemy_level_floor((int)$map['level'])+$i:max(1,(int)ceil($difficulty/55)+$i);
        $combat=max(15,(int)round($difficulty/$count)+($i*3));
        $out[]=[
            'id'=>900000+$i,
            'name'=>(string)$meta['name'],
            'unit_class'=>(string)$meta['class'],
            'level'=>$level,
            'combat'=>$combat,
            'grade'=>$difficulty>=300?'A':($difficulty>=180?'B':($difficulty>=90?'C':'D')),
            'source_enemy_key'=>$key,
        ];
    }
    return msw_auto_battle_normalize_units($out,'Enemy Unit');
}

function msw_auto_battle_integrity(array $hp,array $units): int {
    if(!$units)return 0;
    $current=0;$maximum=0;
    foreach($units as $i=>$unit){
        $max=max(1,(int)($unit['max_hp']??1));
        $maximum+=$max;$current+=max(0,min($max,(int)($hp[$i]??0)));
    }
    return $maximum>0?max(0,min(100,(int)round(($current/$maximum)*100))):0;
}

function msw_auto_battle_action_for_unit(array $unit): string {
    return match((string)($unit['class']??'')){
        'vehicle'=>'armored assault',
        'heavy_infantry'=>'heavy-weapons burst',
        'air'=>'air-support strike',
        'boss'=>'boss assault',
        default=>'rifle volley',
    };
}

function msw_auto_battle_start_hp(array $units): array {
    return array_map(fn(array $unit):int=>max(1,(int)($unit['hp']??$unit['max_hp']??1)),$units);
}

function msw_auto_battle_alive_indices(array $hp): array {
    $alive=[];foreach($hp as $i=>$value)if((int)$value>0)$alive[]=(int)$i;return $alive;
}

function msw_auto_battle_event_integrity(array $leftHp,array $rightHp,array $left,array $right): array {
    return ['left_integrity'=>msw_auto_battle_integrity($leftHp,$left),'right_integrity'=>msw_auto_battle_integrity($rightHp,$right)];
}

function msw_auto_battle_events(array $left,array $right,string $winner,string $seed,bool $aborted=false): array {
    $leftHp=msw_auto_battle_start_hp($left);$rightHp=msw_auto_battle_start_hp($right);
    $events=[array_merge([
        'type'=>'contact','actor'=>'none','target'=>'none','target_index'=>-1,
        'log'=>$aborted?'Recovery shield detected. Strike team is holding outside the perimeter.':'Both forces deployed. Battle commencing.'
    ],msw_auto_battle_event_integrity($leftHp,$rightHp,$left,$right))];

    if($aborted){
        $events[]=array_merge(['type'=>'shield','actor'=>'right','actor_index'=>0,'target'=>'left','target_index'=>-1,'log'=>'Defender recovery shield blocks the operation before combat contact.'],msw_auto_battle_event_integrity($leftHp,$rightHp,$left,$right));
        $events[]=array_merge(['type'=>'withdraw','actor'=>'left','actor_index'=>0,'target'=>'none','target_index'=>-1,'log'=>'Strike team disengages and returns to Mother Base. No combat losses recorded.'],msw_auto_battle_event_integrity($leftHp,$rightHp,$left,$right));
        return $events;
    }

    $loser=$winner==='left'?'right':'left';
    $exchangeCount=max(8,min(14,(count($left)+count($right))*2+2));
    for($i=0;$i<$exchangeCount;$i++){
        $actor=($i%2===0)?'left':'right';
        if(msw_auto_battle_hash_int($seed,$i,0,100)>72)$actor=$actor==='left'?'right':'left';
        $target=$actor==='left'?'right':'left';
        $actorUnits=$actor==='left'?$left:$right;
        $actorState=$actor==='left'?$leftHp:$rightHp;
        $targetUnits=$target==='left'?$left:$right;
        $targetState=$target==='left'?$leftHp:$rightHp;
        $aliveActors=msw_auto_battle_alive_indices($actorState);
        $aliveTargets=msw_auto_battle_alive_indices($targetState);
        if(!$aliveActors||!$aliveTargets)continue;
        $actorIndex=$aliveActors[msw_auto_battle_hash_int($seed,200+$i,0,count($aliveActors)-1)];
        $targetIndex=$aliveTargets[msw_auto_battle_hash_int($seed,100+$i,0,count($aliveTargets)-1)];
        $targetMax=max(1,(int)$targetUnits[$targetIndex]['max_hp']);
        $pct=$actor===$winner?msw_auto_battle_hash_int($seed,300+$i,14,27):msw_auto_battle_hash_int($seed,300+$i,8,18);
        $damage=max(3,(int)round($targetMax*($pct/100)));
        // Ordinary exchanges show real attrition but do not prematurely remove the
        // winning force or skip the final Peace-Walker-style knockout sequence.
        $floor=$target===$winner?max(1,(int)round($targetMax*.20)):max(1,(int)round($targetMax*.08));
        $new=max($floor,(int)$targetState[$targetIndex]-$damage);
        if($target==='left')$leftHp[$targetIndex]=$new;else $rightHp[$targetIndex]=$new;
        $actorUnit=$actorUnits[$actorIndex];$targetUnit=$targetUnits[$targetIndex];
        $events[]=array_merge([
            'type'=>'exchange','actor'=>$actor,'actor_index'=>$actorIndex,'target'=>$target,'target_index'=>$targetIndex,
            'hp_after'=>$new,'max_hp'=>$targetMax,
            'log'=>$actorUnit['name'].' fires a '.msw_auto_battle_action_for_unit($actorUnit).' — '.$targetUnit['name'].' takes '.$damage.' HP damage.'
        ],msw_auto_battle_event_integrity($leftHp,$rightHp,$left,$right));
    }

    // Finish the losing force one combatant at a time. This keeps enemy health visible
    // and visibly reaches zero instead of teleporting from 100% to a result card.
    $loserUnits=$loser==='left'?$left:$right;
    $loserHp=$loser==='left'?$leftHp:$rightHp;
    $winnerUnits=$winner==='left'?$left:$right;
    $winnerHp=$winner==='left'?$leftHp:$rightHp;
    $aliveWinners=msw_auto_battle_alive_indices($winnerHp);
    foreach(msw_auto_battle_alive_indices($loserHp) as $step=>$targetIndex){
        $actorIndex=$aliveWinners?($aliveWinners[$step%count($aliveWinners)]):0;
        $targetUnit=$loserUnits[$targetIndex];$actorUnit=$winnerUnits[$actorIndex]??$winnerUnits[0];
        $damage=max(1,(int)$loserHp[$targetIndex]);$loserHp[$targetIndex]=0;
        if($loser==='left')$leftHp=$loserHp;else $rightHp=$loserHp;
        $events[]=array_merge([
            'type'=>'knockout','actor'=>$winner,'actor_index'=>$actorIndex,'target'=>$loser,'target_index'=>$targetIndex,
            'hp_after'=>0,'max_hp'=>(int)$targetUnit['max_hp'],
            'log'=>$actorUnit['name'].' lands the decisive hit on '.$targetUnit['name'].' — KO.'
        ],msw_auto_battle_event_integrity($leftHp,$rightHp,$left,$right));
    }

    $events[]=array_merge([
        'type'=>'decisive','actor'=>$winner,'actor_index'=>0,'target'=>$loser,'target_index'=>-1,
        'left_hp'=>$leftHp,'right_hp'=>$rightHp,
        'log'=>($winner==='left'?'Friendly force':'Opposing force').' has eliminated the opposing combat element.'
    ],msw_auto_battle_event_integrity($leftHp,$rightHp,$left,$right));
    return $events;
}

function msw_auto_battle_model_dispatch(array $run,array $units,array $definition): array {
    $left=msw_auto_battle_normalize_units($units,'Dispatch Unit');
    $oppositionSeed='dispatch-force|'.(int)$run['id'].'|'.(string)($run['mission_key']??'');
    $right=msw_auto_battle_dispatch_opposition($definition,(int)($definition['slots']??count($left)),$oppositionSeed);
    $success=(string)($run['result']??'failure')==='success';
    $winner=$success?'left':'right';
    $seed='dispatch|'.(int)$run['id'].'|'.(string)$run['result'].'|'.(int)$run['snapshot_power'];
    return [
        'kind'=>'dispatch','title'=>'DISPATCH // AUTOMATIC BATTLE','subtitle'=>(string)($definition['name']??$run['mission_key']??'Dispatch Mission'),
        'left_label'=>'MSW COMBAT UNIT','right_label'=>'HOSTILE FORCE','left_power'=>(int)($run['snapshot_power']??0),'right_power'=>(int)($definition['difficulty']??0),
        'odds'=>round((float)($run['success_chance']??0)*100,1),'left'=>$left,'right'=>$right,'winner'=>$winner,
        'result_label'=>$success?'MISSION SUCCESS':'MISSION FAILED','result_detail'=>$success?'Objective secured. Your team returns with the rewards shown below.':'The squad was pushed back. Your returning rewards are shown below.',
        'events'=>msw_auto_battle_events($left,$right,$winner,$seed),
    ];
}

function msw_auto_battle_fob_force_power(array $snapshot): int {
    $base=(int)($snapshot['user']['base_power']??0);$team=0;
    foreach((array)($snapshot['team']??[]) as $unit)$team+=(int)($unit['combat']??0)*10;
    $security=(int)($snapshot['security']['score']??0);
    return max(1,$base+$team+$security*4);
}

function msw_auto_battle_model_fob(array $raid,array $attackerSnapshot,array $defenderSnapshot,int $viewerId,string $modeLabel): array {
    $viewerIsAttacker=$viewerId===(int)$raid['attacker_user_id'];
    $leftSnapshot=$viewerIsAttacker?$attackerSnapshot:$defenderSnapshot;
    $rightSnapshot=$viewerIsAttacker?$defenderSnapshot:$attackerSnapshot;
    $leftName=(string)($leftSnapshot['user']['username']??($viewerIsAttacker?$raid['attacker']:$raid['defender']));
    $rightName=(string)($rightSnapshot['user']['username']??($viewerIsAttacker?$raid['defender']:$raid['attacker']));
    $left=msw_auto_battle_fob_force($leftSnapshot,$leftName,!$viewerIsAttacker);
    $right=msw_auto_battle_fob_force($rightSnapshot,$rightName,$viewerIsAttacker);
    $attackerWon=(string)$raid['result']==='attacker_win';
    $viewerWon=$viewerIsAttacker?$attackerWon:!$attackerWon;
    $winner=$viewerWon?'left':'right';
    $seed='fob|'.(int)$raid['id'].'|'.(string)$raid['result'].'|'.$viewerId;
    $chance=null;
    $attackerResolution=(array)($attackerSnapshot['resolution']??[]);
    $defenderResolution=(array)($defenderSnapshot['resolution']??[]);
    if(isset($attackerResolution['success_chance'])){
        $attackerChance=round((float)$attackerResolution['success_chance']*100,1);
        $chance=$viewerIsAttacker?$attackerChance:round(100-$attackerChance,1);
    }
    $attackerPower=isset($attackerResolution['roll'])?(int)$attackerResolution['roll']:msw_auto_battle_fob_force_power($attackerSnapshot);
    $defenderPower=isset($defenderResolution['roll'])?(int)$defenderResolution['roll']:msw_auto_battle_fob_force_power($defenderSnapshot);
    $leftPower=$viewerIsAttacker?$attackerPower:$defenderPower;
    $rightPower=$viewerIsAttacker?$defenderPower:$attackerPower;

    return [
        'kind'=>'fob','title'=>'FOB // AUTOMATIC BATTLE','subtitle'=>$modeLabel.' · RAID #'.(int)$raid['id'],
        'left_label'=>$leftName,'right_label'=>$rightName,'left_power'=>$leftPower,'right_power'=>$rightPower,
        'odds'=>$chance,'left'=>$left,'right'=>$right,'winner'=>$winner,
        'result_label'=>$viewerWon?'MISSION SUCCESS':'MISSION FAILED','result_detail'=>$viewerWon?'Your force broke through the enemy defenses. Raid rewards are shown below.':'Your force was repelled by the enemy defenses. The battle report is shown below.',
        'events'=>msw_auto_battle_events($left,$right,$winner,$seed),
    ];
}

function msw_auto_battle_model_fob_abort(array $dispatch,array $units,string $defenderName): array {
    $left=msw_auto_battle_normalize_units($units,'Strike Unit');
    $right=msw_auto_battle_normalize_units([
        ['callsign'=>$defenderName.' Shield Guard','unit_class'=>'heavy_infantry','level'=>1,'combat'=>24,'grade'=>'--','source_enemy_key'=>'shield'],
        ['callsign'=>$defenderName.' Perimeter Guard','unit_class'=>'infantry','level'=>1,'combat'=>20,'grade'=>'--','source_enemy_key'=>'rifle'],
    ],$defenderName.' Defense');
    $seed='fob_abort|'.(int)$dispatch['id'];
    return [
        'kind'=>'fob','title'=>'FOB // AUTOMATIC BATTLE','subtitle'=>'STAFF STRIKE · OPERATION #'.(int)$dispatch['id'],
        'left_label'=>'STRIKE TEAM','right_label'=>$defenderName,'left_power'=>(int)($dispatch['snapshot_power']??0),'right_power'=>0,'odds'=>round((float)($dispatch['success_chance']??0)*100,1),
        'left'=>$left,'right'=>$right,'winner'=>'none','result_label'=>'OPERATION ABORTED','result_detail'=>'The target recovery shield came online before contact. Your strike team withdrew safely.',
        'events'=>msw_auto_battle_events($left,$right,'left',$seed,true),
    ];
}

function msw_render_auto_battle_unit(array $unit,string $side,int $index): void {
    $maxHp=max(1,(int)($unit['max_hp']??1));$hp=max(0,min($maxHp,(int)($unit['hp']??$maxHp)));
    $pct=(int)round(($hp/$maxHp)*100);
    $classes='fighter auto-battle-fighter '.($side==='left'?'player':'enemy').(!empty($unit['vehicle'])?' auto-battle-vehicle':'');
    // Recovered/dispatch combat sprites use the enemy catalog's native left-facing art.
    // Give each rendered combatant an explicit battlefield-facing class so friendly
    // units always face right toward the opposing force and hostiles remain facing left.
    $facingClass=$side==='left'?'auto-battle-sprite-face-right':'auto-battle-sprite-face-left';
    ?>
    <article class="<?=msw_e($classes)?>" data-auto-unit="<?=msw_e($side)?>-<?=$index?>">
        <div class="fighter-sprite-shell"><img class="<?=msw_e($facingClass)?>" src="<?=msw_e(msw_url((string)$unit['sprite']))?>" alt="" height="<?=!empty($unit['vehicle'])?72:88?>"></div>
        <div class="battle-card">
            <b><?=msw_e((string)$unit['name'])?> · Lv <?=intval($unit['level'])?></b>
            <div class="hpbar"><i data-auto-unit-hp="<?=msw_e($side)?>-<?=$index?>" style="width:<?=$pct?>%"></i></div>
            <small>HP <span data-auto-hp-current="<?=msw_e($side)?>-<?=$index?>"><?=$hp?></span> / <span data-auto-hp-max="<?=msw_e($side)?>-<?=$index?>"><?=$maxHp?></span> · <?=msw_e(strtoupper(str_replace('_',' ',(string)$unit['class'])))?> · <?=msw_e((string)$unit['grade'])?></small>
        </div>
    </article>
    <?php
}

function msw_render_auto_battle(array $model): void {
    $json=json_encode($model,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
    if($json===false)$json='{}';
    ?>
    <section class="panel auto-battle-shell" data-auto-battle data-model="<?=msw_e($json)?>">
        <div class="panel-head auto-battle-head">
            <div><small>BATTLE REPLAY</small><h2><?=msw_e((string)($model['title']??'AUTOMATIC BATTLE'))?></h2><p><?=msw_e((string)($model['subtitle']??''))?></p></div>
            <div class="auto-battle-controls"><button type="button" class="secondary" data-auto-battle-replay>Replay Battle</button><button type="button" class="secondary" data-auto-battle-skip>Skip Battle</button></div>
        </div>
        <div class="auto-battle-scoreboard">
            <div class="auto-battle-score friendly"><small>YOUR FORCE</small><b><?=msw_e((string)($model['left_label']??'FRIENDLY'))?></b><span>PWR <?=number_format((int)($model['left_power']??0))?> · FORCE HP <strong data-auto-integrity="left">100%</strong></span><div class="auto-force-hp"><i data-auto-force-hp="left" style="width:100%"></i></div></div>
            <div class="auto-battle-link"><small>TACTICAL LINK</small><b data-auto-battle-status>STANDBY</b><?php if(($model['odds']??null)!==null):?><span>MISSION CHANCE <?=msw_e((string)$model['odds'])?>%</span><?php else:?><span>BATTLE RECORD</span><?php endif;?></div>
            <div class="auto-battle-score enemy"><small>ENEMY FORCE</small><b><?=msw_e((string)($model['right_label']??'ENEMY'))?></b><span>PWR <?=number_format((int)($model['right_power']??0))?> · FORCE HP <strong data-auto-integrity="right">100%</strong></span><div class="auto-force-hp"><i data-auto-force-hp="right" style="width:100%"></i></div></div>
        </div>
        <div class="battle-scene msw-battle-arena auto-battle-arena" data-auto-battle-stage>
            <div class="battle-side battle-side-player auto-battle-side" data-auto-force="left">
                <div class="auto-battle-side-label"><span>FRIENDLY</span><b><?=msw_e((string)($model['left_label']??'MSW FORCE'))?></b></div>
                <div class="auto-battle-roster">
                    <?php foreach((array)($model['left']??[]) as $i=>$unit)msw_render_auto_battle_unit($unit,'left',(int)$i);?>
                </div>
            </div>
            <div class="battle-vs auto-battle-vs"><span data-auto-battle-event>CONTACT</span><b>VS</b></div>
            <div class="battle-side battle-side-enemy auto-battle-side" data-auto-force="right">
                <div class="auto-battle-side-label enemy"><span>HOSTILE</span><b><?=msw_e((string)($model['right_label']??'ENEMY FORCE'))?></b></div>
                <div class="auto-battle-roster">
                    <?php foreach((array)($model['right']??[]) as $i=>$unit)msw_render_auto_battle_unit($unit,'right',(int)$i);?>
                </div>
            </div>
            <div class="auto-battle-result" data-auto-battle-result><small>AFTER ACTION</small><strong><?=msw_e((string)($model['result_label']??'RESULT'))?></strong><span><?=msw_e((string)($model['result_detail']??''))?></span></div>
        </div>
        <div class="auto-battle-log-wrap"><small>BATTLE LOG</small><div class="auto-battle-log" data-auto-battle-log aria-live="polite"></div></div>
        <p class="auto-battle-doctrine">Watch the battle unfold, or skip ahead to the final result.</p>
    </section>
    <?php
}
