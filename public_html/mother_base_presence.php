<?php
declare(strict_types=1);
require __DIR__.'/includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
$me=msw_require_user();$uid=(int)$me['id'];$ownerId=max(1,(int)($_GET['owner']??0));
if(!msw_mb_can_visit($uid,$ownerId)){http_response_code(403);echo json_encode(['error'=>'Mother Base access revoked.']);exit;}
$owner=msw_one('SELECT id,mother_base_key FROM users WHERE id=?','i',[$ownerId]);if(!$owner){http_response_code(404);echo json_encode(['error'=>'Mother Base unavailable.']);exit;}
$baseKey=(string)$owner['mother_base_key'];if(!isset(msw_mother_base_catalog()[$baseKey]))$baseKey='land_dirt';
$presence=msw_mb_presence_row($uid);
if(!$presence || (int)$presence['base_owner_user_id']!==$ownerId || (string)$presence['base_key']!==$baseKey){echo json_encode(['reload'=>msw_url('mother_base.php?owner='.$ownerId)]);exit;}
[$sx,$sy]=msw_mb_safe_position($baseKey,(int)$presence['x'],(int)$presence['y']);msw_mb_presence_touch($uid,$ownerId,$baseKey,$sx,$sy,(string)$presence['facing']);
msw_mb_advance_staff($ownerId,$baseKey);
$chars=msw_character_catalog();$visitors=[];
foreach(msw_mb_visitors($uid,$ownerId,$baseKey) as $player){$c=$chars[$player['character_key']]??reset($chars);$r=(string)($c['sprite_r']??$c['sprite']);$l=(string)($c['sprite_l']??$r);$visitors[]=['id'=>(int)$player['id'],'name'=>(string)$player['username'],'grade'=>(string)$player['base_grade'],'x'=>(int)$player['x'],'y'=>(int)$player['y'],'facing'=>(string)$player['facing'],'sprite'=>msw_url((string)$player['facing']==='left'?$l:$r),'sprite_r'=>msw_url($r),'sprite_l'=>msw_url($l),'mirror_left'=>!empty($c['mirror_left'])?1:0];}
echo json_encode(['base_key'=>$baseKey,'staff'=>msw_mb_staff_state($ownerId,$baseKey),'visitors'=>$visitors],JSON_UNESCAPED_SLASHES);
