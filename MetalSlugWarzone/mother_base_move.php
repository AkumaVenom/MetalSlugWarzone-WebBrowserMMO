<?php
declare(strict_types=1);
require __DIR__.'/includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
$user=msw_require_user();$uid=(int)$user['id'];msw_verify_post();
$ownerId=max(1,(int)($_POST['owner_id']??0));
if(!msw_mb_can_visit($uid,$ownerId)){http_response_code(403);echo json_encode(['error'=>'Mother Base access revoked.']);exit;}
$owner=msw_one('SELECT id,mother_base_key FROM users WHERE id=?','i',[$ownerId]);
if(!$owner){http_response_code(404);echo json_encode(['error'=>'Mother Base unavailable.']);exit;}
$baseKey=(string)$owner['mother_base_key'];if(!isset(msw_mother_base_catalog()[$baseKey]))$baseKey='land_dirt';
$presence=msw_mb_presence_row($uid);
if(!$presence || (int)$presence['base_owner_user_id']!==$ownerId || (string)$presence['base_key']!==$baseKey){
    http_response_code(409);echo json_encode(['error'=>'Mother Base deployment changed.','reload'=>msw_url('mother_base.php?owner='.$ownerId)]);exit;
}
$minimumMs=max(50,min(1000,(int)msw_config('movement_min_interval_ms')));$now=microtime(true);$previous=(float)($_SESSION['last_mb_move_request_at']??0.0);
if($previous>0&&(($now-$previous)*1000)<$minimumMs){$elapsed=(int)floor(($now-$previous)*1000);http_response_code(429);echo json_encode(['error'=>'Movement request rate exceeded.','retry_ms'=>max(10,$minimumMs-$elapsed)]);exit;}
$_SESSION['last_mb_move_request_at']=$now;
$direction=(string)($_POST['direction']??'');if(!in_array($direction,['up','down','left','right'],true)){http_response_code(400);echo json_encode(['error'=>'Bad direction']);exit;}
[$x,$y]=msw_mb_safe_position($baseKey,(int)$presence['x'],(int)$presence['y']);$toX=$x;$toY=$y;$step=18;
if($direction==='left')$toX-=$step;if($direction==='right')$toX+=$step;if($direction==='up')$toY-=$step;if($direction==='down')$toY+=$step;
$blocked=msw_mb_path_collision($baseKey,$x,$y,$toX,$toY);
if($blocked!==null){msw_mb_presence_touch($uid,$ownerId,$baseKey,$x,$y,$direction);echo json_encode(['x'=>$x,'y'=>$y,'facing'=>$direction,'blocked'=>true,'reason'=>$blocked]);exit;}
$x=$toX;$y=$toY;msw_mb_presence_touch($uid,$ownerId,$baseKey,$x,$y,$direction);echo json_encode(['x'=>$x,'y'=>$y,'facing'=>$direction,'blocked'=>false]);
