<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function msw_pvp_damage(array $move,array $attacker,array $target): int {
    $mult=msw_type_multiplier((string)$move['type'],(string)$target['class']);
    $raw=((int)$move['power']+((int)$attacker['attack']*.55))-((int)$target['defense']*.35);
    return max(1,(int)floor(max(1,$raw)*$mult*(random_int(90,110)/100)));
}

function msw_pvp_create_match(int $player1,int $player2,string $mode='live'): int {
    if($player1<=0||$player2<=0||$player1===$player2) throw new RuntimeException('Invalid PvP participants.');
    $allowed=['live','live_ai','snapshot'];if(!in_array($mode,$allowed,true))$mode='live';
    $u1=msw_one('SELECT id,is_bot FROM users WHERE id=?','i',[$player1]);$u2=msw_one('SELECT id,is_bot FROM users WHERE id=?','i',[$player2]);
    if(!$u1||!$u2)throw new RuntimeException('PvP participant unavailable.');
    if((int)$u2['is_bot']===0&&$mode!=='live')$mode='live';
    $state=[
        'round'=>1,
        'log'=>[$mode==='snapshot'?'Commander snapshot battle established.':($mode==='live_ai'?'Live AI battle channel established.':'Live battle channel established.')],
        'fighters'=>[(string)$player1=>msw_commander_fighter($player1),(string)$player2=>msw_commander_fighter($player2)],
        'ai_not_before'=>0,
    ];
    msw_stmt('INSERT INTO pvp_matches(player1_id,player2_id,match_mode,current_turn_user_id,state_json) VALUES(?,?,?,?,?)','iisis',[$player1,$player2,$mode,$player1,json_encode($state,JSON_UNESCAPED_SLASHES)]);
    return (int)msw_db()->insert_id;
}

function msw_pvp_best_ai_move(array $target): string {
    $best='rifle_burst';$score=-1.0;
    foreach(msw_move_catalog() as $key=>$move){
        $s=(float)$move['power']*msw_type_multiplier((string)$move['type'],(string)$target['class'])*((int)$move['accuracy']/100);
        if($s>$score){$score=$s;$best=(string)$key;}
    }
    return $best;
}

function msw_pvp_commit_turn(int $matchId,int $actorId,int $expectedVersion,string $moveKey): array {
    $db=msw_db();$db->begin_transaction();
    try{
        $m=msw_one('SELECT * FROM pvp_matches WHERE id=? FOR UPDATE','i',[$matchId]);
        if(!$m||$m['status']!=='active')throw new RuntimeException('PvP match is no longer active.');
        if((int)$m['current_turn_user_id']!==$actorId)throw new RuntimeException('It is not that commander\'s turn.');
        if($expectedVersion>0&&(int)$m['version']!==$expectedVersion)throw new RuntimeException('PvP state changed before the turn was committed.');
        if($actorId!==(int)$m['player1_id']&&$actorId!==(int)$m['player2_id'])throw new RuntimeException('Commander is not a match participant.');
        $target=$actorId===(int)$m['player1_id']?(int)$m['player2_id']:(int)$m['player1_id'];
        $state=json_decode((string)$m['state_json'],true,512,JSON_THROW_ON_ERROR);
        if(!isset($state['fighters'][(string)$actorId],$state['fighters'][(string)$target]))throw new RuntimeException('PvP fighter snapshot is incomplete.');
        $me=&$state['fighters'][(string)$actorId];$foe=&$state['fighters'][(string)$target];
        $moves=msw_move_catalog();$move=$moves[$moveKey]??$moves['rifle_burst'];
        if(random_int(1,100)<=(int)$move['accuracy']){$damage=msw_pvp_damage($move,$me,$foe);$foe['hp']=max(0,(int)$foe['hp']-$damage);$state['log'][]=$me['name'].' used '.$move['name'].' for '.$damage.' damage.';}
        else $state['log'][]=$me['name'].' missed with '.$move['name'].'.';
        $status='active';$next=$target;
        if((int)$foe['hp']<=0){
            $status=$actorId===(int)$m['player1_id']?'player1_win':'player2_win';$next=$actorId;$state['log'][]=$me['name'].' secured the PvP victory.';
            $winner=msw_level_up_user($actorId,180);$loser=msw_level_up_user($target,45);
            $state['log'][]=$me['name'].' gained +'.(int)$winner['gained'].' Command XP.';$state['log'][]=$foe['name'].' gained +'.(int)$loser['gained'].' Command XP.';
            if(!empty($winner['leveled']))$state['log'][]=$me['name'].' advanced to Lv '.(int)$winner['after_level'].'!';
            if(!empty($loser['leveled']))$state['log'][]=$foe['name'].' advanced to Lv '.(int)$loser['after_level'].'!';
            $bots=msw_all('SELECT user_id FROM bot_commanders WHERE enabled=1 AND user_id IN (?,?)','ii',[(int)$m['player1_id'],(int)$m['player2_id']]);
            foreach($bots as $br){$bid=(int)$br['user_id'];msw_stmt('UPDATE bot_commanders SET pvp_battles=pvp_battles+1,pvp_wins=pvp_wins+? WHERE user_id=?','ii',[$bid===$actorId?1:0,$bid]);msw_bot_set_activity($bid,$bid===$actorId?'Won PvP battle':'Completed PvP battle');}
        }else{
            $state['round']=(int)($state['round']??1)+1;
            $nextBot=msw_is_bot_user($target);
            $state['ai_not_before']=$nextBot?microtime(true)+(((string)$m['match_mode']==='snapshot')?0.0:0.75):0;
        }
        msw_stmt('UPDATE pvp_matches SET state_json=?,status=?,current_turn_user_id=?,version=version+1 WHERE id=?','ssii',[json_encode($state,JSON_UNESCAPED_SLASHES),$status,$next,$matchId]);
        $db->commit();
        return ['status'=>$status,'next'=>$next,'mode'=>(string)$m['match_mode'],'version'=>(int)$m['version']+1];
    }catch(Throwable $e){$db->rollback();throw $e;}
}

function msw_pvp_process_bot_turn(int $matchId,bool $force=false): bool {
    $m=msw_one("SELECT * FROM pvp_matches WHERE id=? AND status='active'",'i',[$matchId]);if(!$m)return false;
    $actor=(int)$m['current_turn_user_id'];if(!msw_is_bot_user($actor))return false;
    $state=json_decode((string)$m['state_json'],true);$due=(float)($state['ai_not_before']??0);
    if(!$force&&$due>microtime(true))return false;
    $target=$actor===(int)$m['player1_id']?(int)$m['player2_id']:(int)$m['player1_id'];$targetState=$state['fighters'][(string)$target]??null;if(!$targetState)return false;
    $move=msw_pvp_best_ai_move($targetState);
    try{msw_pvp_commit_turn($matchId,$actor,(int)$m['version'],$move);return true;}catch(Throwable $e){return false;}
}
