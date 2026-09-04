<?php
declare(strict_types=1);
require __DIR__.'/includes/pvp_engine.php';
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
$user=msw_require_user();$uid=(int)$user['id'];$id=(int)($_GET['id']??0);
$allowed=msw_one('SELECT id FROM pvp_matches WHERE id=? AND (player1_id=? OR player2_id=?)','iii',[$id,$uid,$uid]);if(!$allowed){http_response_code(404);echo json_encode(['error'=>'Match not found']);exit;}
msw_pvp_process_bot_turn($id,false);
$match=msw_one('SELECT id,current_turn_user_id,status,version,updated_at,match_mode FROM pvp_matches WHERE id=?','i',[$id]);
echo json_encode(['id'=>(int)$match['id'],'version'=>(int)$match['version'],'status'=>(string)$match['status'],'match_mode'=>(string)$match['match_mode'],'current_turn_user_id'=>(int)$match['current_turn_user_id'],'updated_at'=>(string)$match['updated_at']],JSON_UNESCAPED_SLASHES);
