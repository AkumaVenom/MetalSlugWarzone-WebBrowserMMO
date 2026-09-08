<?php
declare(strict_types=1);
require __DIR__.'/includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$user=msw_user();
if(!$user||(int)$user['is_bot']!==0){http_response_code(401);echo '{"error":"session_expired"}';exit;}
msw_verify_post();
$uid=(int)$user['id'];
$view=(string)($_POST['view']??'');
$watch=array_slice(array_values(array_unique(array_filter(array_map('intval',explode(',',substr((string)($_POST['watch']??''),0,512))),fn($id)=>$id>0))),0,16);
// Background work must never hold the player's PHP session lock over navigation.
session_write_close();

try{
    $pulse=msw_world_pulse($uid);
    $payload=['server_time_ms'=>(int)round(microtime(true)*1000),'next_poll_ms'=>msw_world_interval_ms(),'status'=>$pulse['status']];
    if($view==='rankings'){
        $payload['rankings']=msw_all('SELECT id,username,level,base_power,base_grade,is_bot FROM users ORDER BY base_power DESC,xp DESC,id ASC LIMIT 100');
        foreach($payload['rankings'] as &$row)$row['profile_url']=msw_url('profile.php?id='.(int)$row['id']);unset($row);
    }elseif($view==='fob'){
        $payload['incoming']=[];
        foreach(msw_fob_incoming_operations($uid,16) as $op){
            $payload['incoming'][]=['id'=>(int)$op['id'],'attacker'=>(string)$op['attacker'],'grade'=>(string)$op['attacker_grade'],'world'=>msw_fob_world_name($op),'chance'=>(float)$op['success_chance'],'finish_ms'=>(int)strtotime((string)$op['finish_at'])*1000];
        }
        $payload['incoming_count']=(int)(msw_one("SELECT COUNT(*) c FROM fob_strike_dispatches WHERE defender_user_id=? AND result='pending'",'i',[$uid])['c']??0);
        $payload['completed']=[];
        if($watch){
            $in=implode(',',$watch);
            $rows=msw_all("SELECT d.id,d.result,d.raid_id,u.username attacker FROM fob_strike_dispatches d JOIN users u ON u.id=d.attacker_user_id WHERE d.id IN ({$in}) AND d.defender_user_id=? AND d.result<>'pending' ORDER BY d.id DESC",'i',[$uid]);
            foreach($rows as $row)$payload['completed'][]=['id'=>(int)$row['id'],'attacker'=>(string)$row['attacker'],'result'=>(string)$row['result'],'report_url'=>empty($row['raid_id'])?null:msw_url('fob_result.php?id='.(int)$row['raid_id'])];
        }
    }
    echo json_encode($payload,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
}catch(Throwable $e){
    error_log('[MSW world pulse] '.$e->getMessage());
    http_response_code(503);echo '{"error":"temporarily_unavailable","next_poll_ms":5000}';
}
