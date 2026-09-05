<?php
declare(strict_types=1);
require __DIR__.'/includes/battle_engine.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$user=msw_require_user();
$uid=(int)$user['id'];
msw_verify_post();

$active=msw_active_encounter($uid);
if($active){
    http_response_code(409);
    echo json_encode(['error'=>'Active engagement requires resolution.','battle'=>msw_url('battle.php?id='.(int)$active['id'])]);
    exit;
}

$minimumMs=max(50,min(1000,(int)msw_config('movement_min_interval_ms')));
$now=microtime(true);
$previous=(float)($_SESSION['last_move_request_at']??0.0);
if($previous>0 && (($now-$previous)*1000)<$minimumMs){
    $elapsed=(int)floor(($now-$previous)*1000);
    $retryMs=max(10,$minimumMs-$elapsed);
    http_response_code(429);
    header('Retry-After: 1');
    echo json_encode(['error'=>'Movement request rate exceeded.','retry_ms'=>$retryMs]);
    exit;
}
$_SESSION['last_move_request_at']=$now;

$direction=(string)($_POST['direction']??'');
if(!in_array($direction,['up','down','left','right'],true)){
    http_response_code(400);
    echo json_encode(['error'=>'Bad direction']);
    exit;
}

$maps=msw_map_catalog();
$key=(string)($user['active_map']??'');
if(!isset($maps[$key])){
    http_response_code(409);
    echo json_encode(['error'=>'Not deployed']);
    exit;
}

$map=$maps[$key];
$step=18;
[$x,$y]=msw_map_safe_position($key,(int)$user['map_x'],(int)$user['map_y']);
$toX=$x;$toY=$y;
if($direction==='left') $toX-=$step;
if($direction==='right') $toX+=$step;
if($direction==='up') $toY-=$step;
if($direction==='down') $toY+=$step;

$blockedBy=msw_map_path_collision($key,$x,$y,$toX,$toY);
if($blockedBy!==null){
    // Facing still changes when the player pushes into terrain, but authoritative
    // coordinates do not. Blocked movement never rolls a random encounter.
    msw_presence_touch($uid,$key,$x,$y,$direction);
    echo json_encode([
        'x'=>$x,'y'=>$y,'facing'=>$direction,'battle'=>null,
        'blocked'=>true,'reason'=>$blockedBy,
    ],JSON_UNESCAPED_SLASHES);
    exit;
}

$x=$toX;$y=$toY;
msw_presence_touch($uid,$key,$x,$y,$direction);

$battle=null;
$chance=max(0,min(100,(int)msw_config('encounter_chance')));
if($chance>0 && random_int(1,100)<=$chance){
    $enemy=msw_random_enemy_for_map($key);
    $battleId=msw_start_encounter($uid,$enemy,'field',$key);
    $battle=msw_url('battle.php?id='.$battleId);
}

echo json_encode(['x'=>$x,'y'=>$y,'facing'=>$direction,'battle'=>$battle,'blocked'=>false],JSON_UNESCAPED_SLASHES);
