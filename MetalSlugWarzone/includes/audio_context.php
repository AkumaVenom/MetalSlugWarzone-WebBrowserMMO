<?php
declare(strict_types=1);

/**
 * Presentation-only sound routing. Never changes encounters, outcomes or rewards.
 * Only persisted combat state is passed to these helpers. Stable event IDs let
 * the audio controller suppress duplicate effects after a refresh or polling.
 */
function msw_audio_map_track(string $mapKey,bool $battle=false): string {
    if($mapKey==='lunar_outpost')return $battle?'battle_lunar':'map_lunar';
    $group=match($mapKey){
        'industrial_rail','desert_line','neon_district','scrapyard_depot','subterranean_terminus'=>2,
        'coastal_breach','fortress_approach','canyon_missile_base','alpine_radar','offshore_platform','volcanic_forge','containment_laboratory'=>3,
        default=>1,
    };
    return ($battle?'battle_':'map_').$group;
}

function msw_audio_page_context(): array {
    if(isset($GLOBALS['msw_audio_context'])&&is_array($GLOBALS['msw_audio_context']))return $GLOBALS['msw_audio_context'];
    $page=basename((string)($_SERVER['SCRIPT_NAME']??'index.php'),'.php');
    $track=match($page){
        'map_select','missions','bosses','sidequests','dispatch','dispatch_result'=>'missions_select',
        'pvp','pvp_match','commanders'=>'versus',
        'fob','fob_globe','fob_world','fob_shards','fob_target','fob_dispatch','fob_infiltration','fob_result','fob_dispatch_result','strategic','strike_forces'=>'extra_mother_base',
        'staff','rd','ai_commanders','community','friends','messages','rankings','profile'=>'extra_mother_base',
        default=>'mother_base',
    };
    return ['track'=>$track,'loop'=>true];
}

/** Mission definitions carry their deployment map; bosses take music priority. */
function msw_audio_battle_map(array $state): string {
    $context=(string)($state['context']??'field');
    $key=(string)($state['context_key']??'');
    if($context==='field')return $key;
    $definition=function_exists('msw_context_definition')?msw_context_definition($context,$key):[];
    return (string)($definition['map_key']??'');
}

function msw_audio_battle_context(array $state,string $status): array {
    $kind=(string)($state['context']??'field');
    $map=msw_audio_battle_map($state);
    $isBoss=$kind==='boss'||(string)($state['enemy']['class']??'')==='boss';
    $fallback=$kind==='field'?msw_audio_map_track($map):($kind==='trainer'?'versus':'missions_select');
    if($status!=='active')return ['track'=>$fallback,'loop'=>true];
    $track=$isBoss?'boss_battle':($map!==''?msw_audio_map_track($map,true):($kind==='trainer'?'versus':($kind==='sidequest'?'battle_3':'battle_1')));
    return ['track'=>$track,'loop'=>true];
}

/** Weapon launch at the lunge; impacts align with the existing 240ms hit frame. */
function msw_audio_attack_cues(string $move,bool $hit,int $delay=0,string $type=''): array {
    if($type==='')$type=match($move){'grenade'=>'explosive','armor_piercer'=>'anti_armor','close_quarters'=>'melee',default=>'ballistic'};
    if(in_array($type,['melee','organic'],true))return $hit?[['sound'=>'bullet','delay'=>$delay+240]]:[];
    if($move==='grenade')return [['sound'=>'explosion','delay'=>$delay+240]];
    if(in_array($type,['anti_armor','energy','explosive'],true)){
        $cues=[['sound'=>'rocket','delay'=>$delay]];
        if($hit)$cues[]=['sound'=>'explosion','delay'=>$delay+240];
        return $cues;
    }
    if($type==='fire')return [['sound'=>'explosion','delay'=>$delay+240]];
    $cues=[['sound'=>'rifle','delay'=>$delay]];
    if($hit)$cues[]=['sound'=>'bullet','delay'=>$delay+240];
    return $cues;
}

function msw_audio_battle_event(array $row,array $state,int $uid): array {
    $fx=(array)($state['fx']??[]);
    $seq=(int)($fx['seq']??0);
    $cues=[];
    if((string)($fx['action']??'')==='attack')$cues=msw_audio_attack_cues((string)($fx['move']??'rifle_burst'),!empty($fx['player_hit']));
    foreach(array_slice((array)($fx['backup_slots']??[]),0,2) as $index=>$_slot)$cues[]=['sound'=>'rifle','delay'=>350+(int)$index*90];
    if(!empty($fx['enemy_counter']))$cues=array_merge($cues,msw_audio_attack_cues('',!empty($fx['enemy_hit']),520,(string)($state['enemy']['type']??'ballistic')));
    $event=['id'=>'pve:'.$uid.':'.(int)($row['id']??0).':fx:'.$seq,'cues'=>$cues];
    $status=(string)($row['status']??'active');
    if($status==='active'||$status==='retreated')return $event;
    $fallback=msw_audio_battle_context($state,$status)['track'];
    $isBoss=(string)($state['context']??'')==='boss'||(string)($state['enemy']['class']??'')==='boss';
    $event['music']=$status==='lost'?'battle_lost':($isBoss&&$status==='won'?'boss_win':'mission_complete');
    $event['loop']=false;$event['fallback']=$fallback;
    if($status==='lost'){
        $event['cues'][]=['sound'=>'death_1','delay'=>920];
        $event['cues'][]=['sound'=>'enemy_winner','delay'=>1450];
    }elseif($status==='won'){
        $hardware=in_array((string)($state['enemy']['class']??''),['vehicle','air','boss'],true);
        $event['cues'][]=['sound'=>$hardware?'explosion':'death_2','delay'=>!empty($fx['backup_slots'])?750:500];
        // First-turn, undamaged victories are provably flawless. A full HP bar
        // after medical recovery alone does not prove a damage-free battle.
        $flawless=(int)($state['round']??0)===1&&(int)($state['player']['hp']??0)>=(int)($state['player']['max_hp']??1)&&empty($fx['enemy_hit']);
        foreach((array)($state['backups']??[]) as $backup)if((int)($backup['hp']??0)<(int)($backup['max_hp']??1))$flawless=false;
        if($flawless)$event['cues'][]=['sound'=>'flawless_win','delay'=>1100];
    }
    return $event;
}

function msw_audio_pvp_event(array $match,array $state,int $uid): array {
    $fx=(array)($state['fx']??[]);
    $cues=(string)($fx['kind']??'')==='attack'?msw_audio_attack_cues((string)($fx['move']??'rifle_burst'),!empty($fx['hit'])):[];
    $event=['id'=>'pvp:'.$uid.':'.(int)($match['id']??0).':fx:'.(int)($fx['seq']??0),'cues'=>$cues];
    $status=(string)($match['status']??'active');
    if($status==='active')return $event;
    $winner=match($status){'player1_win'=>(int)($match['player1_id']??0),'player2_win'=>(int)($match['player2_id']??0),default=>0};
    if($winner===0)return $event;
    $won=$winner===$uid;
    $event['music']=$won?'mission_complete':'battle_lost';$event['loop']=false;$event['fallback']='versus';
    $event['cues'][]=['sound'=>$won?'death_2':'death_1','delay'=>500];
    if(!$won)$event['cues'][]=['sound'=>'enemy_winner','delay'=>1000];
    elseif((int)($state['round']??0)===1&&(int)($state['fighters'][(string)$uid]['hp']??0)>=(int)($state['fighters'][(string)$uid]['max_hp']??1))$event['cues'][]=['sound'=>'flawless_win','delay'=>1000];
    return $event;
}

function msw_audio_event_marker(array $event): void {
    $json=json_encode($event,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
    if($json===false)return;
    echo '<span hidden data-msw-audio-event="'.htmlspecialchars($json,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').'"></span>';
}
