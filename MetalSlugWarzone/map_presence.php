<?php
declare(strict_types=1);
require __DIR__.'/includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$user=msw_require_user();
$uid=(int)$user['id'];
$map=(string)($user['active_map']??'');
if($map==='' || !isset(msw_map_catalog()[$map])){
    echo json_encode(['players'=>[]]);
    exit;
}

// Advance a bounded local batch plus a smaller global batch. The local share keeps
// the viewed warzone lively while the global share lets commanders elsewhere keep
// progressing even when no human currently has their warzone open. Lease and
// next_action_at checks prevent extra browser clients from accelerating a bot.
msw_bot_simulation_pulse($map,12);
msw_bot_simulation_pulse(null,6);

// Keep the polling player legal if a map revision changed beneath an already
// open browser session, then refresh mandatory presence at that safe position.
[$selfX,$selfY]=msw_map_safe_position($map,(int)$user['map_x'],(int)$user['map_y']);
msw_presence_touch($uid,$map,$selfX,$selfY,(string)$user['facing']);

$characters=msw_character_catalog();
$out=[];
foreach(msw_presence($uid,$map) as $player){
    $character=$characters[$player['character_key']]??reset($characters);
    $spriteR=(string)($character['sprite_r']??$character['sprite']);
    $spriteL=(string)($character['sprite_l']??$spriteR);
    [$px,$py]=msw_map_safe_position($map,(int)$player['map_x'],(int)$player['map_y']);
    $out[]=[
        'id'=>(int)$player['id'],
        'name'=>(string)$player['username'],
        'grade'=>(string)$player['base_grade'],
        'x'=>$px,
        'y'=>$py,
        'facing'=>(string)$player['facing'],
        'sprite'=>msw_url(((string)$player['facing']==='left')?$spriteL:$spriteR),
        'sprite_r'=>msw_url($spriteR),
        'sprite_l'=>msw_url($spriteL),
        'mirror_left'=>!empty($character['mirror_left'])?1:0,
        'is_bot'=>(int)($player['is_bot']??0),
        'bot_index'=>(int)($player['bot_index']??0),
        'activity'=>(string)($player['activity']??''),
        'profile_url'=>msw_url('profile.php?id='.(int)$player['id']),
    ];
}
echo json_encode(['players'=>$out],JSON_UNESCAPED_SLASHES);
