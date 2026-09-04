<?php
declare(strict_types=1);

/**
 * Resolve completed standard Combat Unit Dispatch missions from authoritative
 * MySQL timestamps. FOB staff strikes share units.dispatched_until, so both
 * dispatch systems call this authority before presenting/reusing staff.
 *
 * The conditional reservation release is important: an older completed mission
 * must never clear a newer reservation if two request surfaces race at expiry.
 */
function msw_dispatch_resolve_due_for_user(int $uid,int $limit=20,?bool $botMode=null): int {
    $limit=max(1,min(100,$limit));
    if($botMode===null){
        $identity=msw_one('SELECT is_bot FROM users WHERE id=? LIMIT 1','i',[$uid]);
        $botMode=(int)($identity['is_bot']??0)===1;
    }
    $catalog=msw_dispatch_catalog();
    $due=msw_all("SELECT id FROM dispatch_missions WHERE user_id=? AND result='pending' AND finish_at<=NOW() ORDER BY id LIMIT {$limit}",'i',[$uid]);
    $resolved=0;
    foreach($due as $dueRow){
        $db=msw_db();$db->begin_transaction();
        try{
            $mission=msw_one("SELECT * FROM dispatch_missions WHERE id=? AND user_id=? AND result='pending' FOR UPDATE",'ii',[(int)$dueRow['id'],$uid]);
            if(!$mission||strtotime((string)$mission['finish_at'])>time()){$db->rollback();continue;}

            $definition=$catalog[(string)$mission['mission_key']]??null;
            $chance=max(0.0,min(1.0,(float)$mission['success_chance']));
            $success=(random_int(1,10000)/10000)<=$chance;
            $reward=$success&&$definition?$definition['reward']:['gmp'=>120];
            $result=$success?'success':'failure';
            $st=msw_stmt("UPDATE dispatch_missions SET result=?,resolved_at=NOW(),reward_json=? WHERE id=? AND result='pending'",'ssi',[$result,json_encode($reward,JSON_UNESCAPED_SLASHES),(int)$mission['id']]);
            if($st->affected_rows!==1){$db->rollback();continue;}

            $ids=array_values(array_unique(array_filter(array_map('intval',json_decode((string)$mission['unit_ids_json'],true)?:[]),fn($id)=>$id>0)));sort($ids,SORT_NUMERIC);
            $oldFinish=(string)$mission['finish_at'];
            foreach($ids as $unitId){
                msw_add_unit_xp($uid,$unitId,$success?80:25);
                msw_stmt('UPDATE units SET dispatched_until=NULL WHERE id=? AND owner_user_id=? AND (dispatched_until IS NULL OR dispatched_until<=?)','iis',[$unitId,$uid,$oldFinish]);
            }
            msw_grant_resources($uid,$reward);
            if($botMode)msw_level_up_user($uid,$success?60:15);
            msw_recalculate_base($uid);
            $db->commit();$resolved++;

            $name=(string)($definition['name']??$mission['mission_key']);
            if($botMode){
                if(function_exists('msw_bot_set_activity'))msw_bot_set_activity($uid,$success?'Combat Unit completed '.$name:'Combat Unit returned from '.$name);
            }else{
                msw_console_event_for_user($uid,'DISPATCH','RESOLVED',$name.' resolved: '.strtoupper($result).'.',[
                    'mission_id'=>(int)$mission['id'],'mission_key'=>(string)$mission['mission_key'],'result'=>$result,'units'=>count($ids),
                ]);
            }
        }catch(Throwable $e){$db->rollback();throw $e;}
    }
    return $resolved;
}
