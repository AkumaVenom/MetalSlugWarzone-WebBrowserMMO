<?php
declare(strict_types=1);

/** Shared automatic server/browser updates. The browser supplies no simulation authority. */
function msw_world_meta(string $key): ?string {
    $row=msw_one('SELECT meta_value FROM schema_meta WHERE meta_key=?','s',[$key]);
    return $row?(string)$row['meta_value']:null;
}

function msw_world_set_meta(string $key,string $value): void {
    msw_stmt('INSERT INTO schema_meta(meta_key,meta_value) VALUES(?,?) ON DUPLICATE KEY UPDATE meta_value=VALUES(meta_value)','ss',[$key,$value]);
}

function msw_world_lock_name(): string {
    return 'msw_world_'.substr(hash('sha256',(string)msw_config('db')['name']),0,40);
}

function msw_world_interval_ms(): int {
    return max(1000,min(10000,(int)(msw_config('world_pulse_interval_ms')??2000)));
}

function msw_world_activate(): void {
    $now=time();$checked=(int)(msw_world_meta('world_schedule_checked_at')??0);
    // Recheck throughout the world's lifetime, including after a host clock
    // correction. A one-time upgrade marker cannot recover a later stale lease.
    if(msw_world_meta('world_runtime_v0843')==='1'&&$checked<=$now&&$checked>$now-60)return;
    $db=msw_db();$db->begin_transaction();
    try{
        // Old SQL/PHP timezone mismatches could leave leases/actions hours ahead.
        // Preserve valid schedules and all earned progress; repair only values
        // outside the maximum supported normal/backoff scheduling horizon.
        $horizon=max(60,3*max(11,(int)msw_config('bot_action_max_seconds')));
        msw_stmt('UPDATE bot_commanders SET next_action_at=DATE_ADD(NOW(),INTERVAL (2+MOD(bot_index*7,12)) SECOND) WHERE enabled=1 AND next_action_at>DATE_ADD(NOW(),INTERVAL ? SECOND)','i',[$horizon]);
        msw_stmt('UPDATE bot_commanders SET lease_until=NULL WHERE lease_until<NOW() OR lease_until>DATE_ADD(NOW(),INTERVAL ? SECOND)','i',[$horizon]);
        msw_world_set_meta('world_runtime_v0843','1');
        msw_world_set_meta('world_schedule_checked_at',(string)$now);
        $db->commit();
    }catch(Throwable $e){$db->rollback();throw $e;}
}

function msw_world_arrival_ready_sql(): string {
    return "d.result='pending' AND d.finish_at<=NOW() AND (retry.meta_value IS NULL OR CAST(retry.meta_value AS UNSIGNED)<=UNIX_TIMESTAMP() OR CAST(retry.meta_value AS UNSIGNED)>UNIX_TIMESTAMP()+60)";
}

function msw_world_arrival_rows(int $cursor,int $limit): array {
    $limit=max(1,min(8,$limit));$ready=msw_world_arrival_ready_sql();
    $join="LEFT JOIN schema_meta retry ON retry.meta_key=CONCAT('fob_arrival_retry_',d.id)";
    $rows=msw_all("SELECT d.id FROM fob_strike_dispatches d {$join} WHERE {$ready} AND d.id>? ORDER BY d.id LIMIT {$limit}",'i',[$cursor]);
    if(count($rows)<$limit&&$cursor>0){
        $remaining=$limit-count($rows);
        $rows=array_merge($rows,msw_all("SELECT d.id FROM fob_strike_dispatches d {$join} WHERE {$ready} AND d.id<=? ORDER BY d.id LIMIT {$remaining}",'i',[$cursor]));
    }
    return $rows;
}

function msw_world_settle_arrival(int $id,array &$stats): void {
    $retryKey='fob_arrival_retry_'.$id;
    try{
        if(msw_fob_resolve_staff_dispatch($id))$stats['arrivals']++;
        msw_stmt('DELETE FROM schema_meta WHERE meta_key=?','s',[$retryKey]);
    }catch(Throwable $e){
        // Leave the mission pending for an atomic retry. Do not discard it,
        // manufacture a result, or let it monopolize every subsequent batch.
        $stats['failed']++;
        msw_world_set_meta($retryKey,(string)(time()+60));
        error_log('[MSW world arrival '.$id.'] '.$e->getMessage());
    }
}

function msw_world_pulse(int $viewerId=0): array {
    $stats=['status'=>'busy','arrivals'=>0,'bots'=>0,'failed'=>0];
    $db=msw_db();msw_sync_database_clock($db);
    $lockName=msw_world_lock_name();
    $lock=msw_one('SELECT GET_LOCK(?,0) acquired','s',[$lockName]);
    if((int)($lock['acquired']??0)!==1)return $stats;
    try{
        $now=microtime(true);
        $next=(float)(msw_world_meta('world_next_pulse')??0);
        // A stale future throttle must not suspend the whole world indefinitely
        // after a clock correction. Normal overlapping requests still wait.
        if($next>$now&&$next<=$now+max(10,2*msw_world_interval_ms()/1000)){$stats['status']='waiting';return $stats;}
        // A durable throttle prevents multiple accounts/tabs speeding up the
        // world. The advisory lock is released automatically after a crash.
        msw_world_set_meta('world_next_pulse',(string)($now+msw_world_interval_ms()/1000));
        $db->query('SET SESSION innodb_lock_wait_timeout=2');
        msw_world_activate();
        $budget=max(2,min(8,(int)(msw_config('world_arrival_batch_size')??4)));
        $deadline=microtime(true)+max(40,min(500,(int)(msw_config('world_arrival_time_budget_ms')??120)))/1000;
        $visited=[];
        // A viewer's arriving defense gets prompt attention. Reserve at least
        // half the batch for global work, including offline/disabled attackers.
        if($viewerId>0){
            $priority=intdiv($budget,2);$ready=msw_world_arrival_ready_sql();
            $rows=msw_all("SELECT d.id FROM fob_strike_dispatches d LEFT JOIN schema_meta retry ON retry.meta_key=CONCAT('fob_arrival_retry_',d.id) WHERE {$ready} AND d.defender_user_id=? ORDER BY d.finish_at,d.id LIMIT {$priority}",'i',[$viewerId]);
            foreach($rows as $row){
                if(microtime(true)>=$deadline)break;
                $id=(int)$row['id'];$visited[$id]=true;msw_world_settle_arrival($id,$stats);
            }
        }
        $cursor=max(0,(int)(msw_world_meta('world_arrival_cursor')??0));
        foreach(msw_world_arrival_rows($cursor,$budget) as $row){
            if(count($visited)>=$budget||microtime(true)>=$deadline)break;
            $id=(int)$row['id'];
            msw_world_set_meta('world_arrival_cursor',(string)$id);
            if(isset($visited[$id]))continue;
            $visited[$id]=true;msw_world_settle_arrival($id,$stats);
        }
        // Global oldest-due selection guarantees every map's commanders a turn.
        // Arrival backlog never consumes the separate, bounded career budget.
        $bots=msw_bot_simulation_pulse(null,(int)(msw_config('bot_request_hard_cap')??4));
        $stats['bots']=$bots['processed'];$stats['failed']+=$bots['failed'];
        $stats['status']=$stats['failed']>0?'retrying':'ok';
        msw_world_set_meta('world_last_pulse',json_encode(['at'=>time(),'arrivals'=>$stats['arrivals'],'bots'=>$stats['bots'],'failed'=>$stats['failed']],JSON_THROW_ON_ERROR));
        return $stats;
    }finally{
        msw_stmt('DO RELEASE_LOCK(?)','s',[$lockName]);
    }
}

/** Internal diagnostics; no private roster/snapshot data in browser pulses. */
function msw_world_status(): array {
    $bots=msw_one('SELECT COUNT(*) enabled,COALESCE(SUM(next_action_at<=NOW()),0) due,COALESCE(MAX(TIMESTAMPDIFF(SECOND,next_action_at,NOW())),0) oldest_due_seconds FROM bot_commanders WHERE enabled=1');
    $arrivals=msw_one("SELECT COUNT(*) pending,COALESCE(SUM(finish_at<=NOW()),0) due FROM fob_strike_dispatches WHERE result='pending'");
    return ['server_time'=>date(DATE_ATOM),'last_pulse'=>json_decode(msw_world_meta('world_last_pulse')??'null',true),'bots'=>$bots,'strikes'=>$arrivals];
}
