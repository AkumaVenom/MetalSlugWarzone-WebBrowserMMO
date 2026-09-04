<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();
$uid=(int)$user['id'];
msw_bot_simulation_pulse(null,8);

function fob_snapshot_locked(int $id): array {
    $user=msw_one('SELECT id,username,base_power,base_grade FROM users WHERE id=?','i',[$id]);
    $team=msw_all('SELECT id,callsign,unit_class,level,combat,security,grade FROM units WHERE owner_user_id=? AND active_combat=1 ORDER BY combat DESC,id ASC LIMIT 4 FOR UPDATE','i',[$id]);
    $security=msw_one("SELECT score,level,grade FROM base_sectors WHERE user_id=? AND sector_key='security' FOR UPDATE",'i',[$id]);
    return ['user'=>$user,'team'=>$team,'security'=>$security,'captured_at'=>date(DATE_ATOM)];
}

if(msw_is_post()){
    msw_verify_post();
    $defenderId=(int)($_POST['defender_id']??0);
    if($defenderId===$uid||$defenderId<=0){
        msw_flash('Invalid FOB target.','error');
        msw_redirect('fob.php');
    }

    $db=msw_db();
    $db->begin_transaction();
    try{
        $low=min($uid,$defenderId);
        $high=max($uid,$defenderId);
        $lockedUsers=msw_all('SELECT * FROM users WHERE id IN (?,?) ORDER BY id FOR UPDATE','ii',[$low,$high]);
        $users=[];
        foreach($lockedUsers as $lockedUser) $users[(int)$lockedUser['id']]=$lockedUser;
        $attacker=$users[$uid]??null;
        $defender=$users[$defenderId]??null;
        if(!$attacker||!$defender) throw new RuntimeException('Target unavailable.');

        $attackCooldown=max(60,(int)msw_config('fob_attack_cooldown_seconds'));
        if(!empty($attacker['last_fob_attack_at']) && strtotime((string)$attacker['last_fob_attack_at'])>time()-$attackCooldown){
            throw new RuntimeException('FOB assault systems are cooling down. Try again after the attack cooldown expires.');
        }
        if(!empty($defender['fob_protection_until']) && strtotime((string)$defender['fob_protection_until'])>time()){
            throw new RuntimeException('That FOB is under temporary post-raid protection.');
        }

        $resourceRows=msw_all('SELECT * FROM player_resources WHERE user_id IN (?,?) ORDER BY user_id FOR UPDATE','ii',[$low,$high]);
        $resources=[];
        foreach($resourceRows as $row) $resources[(int)$row['user_id']]=$row;
        $attackerResources=$resources[$uid]??null;
        $defenderResources=$resources[$defenderId]??null;
        if(!$attackerResources||!$defenderResources) throw new RuntimeException('Resource ledger unavailable.');

        $attackerSnapshot=fob_snapshot_locked($uid);
        $defenderSnapshot=fob_snapshot_locked($defenderId);
        $defenderSecurity=(int)($defenderSnapshot['security']['score']??0);
        $attackerTeamPower=(int)array_sum(array_column($attackerSnapshot['team'],'combat'));
        $attackerRoll=(int)$attacker['base_power']+($attackerTeamPower*10)+random_int(0,900);
        $defenderRoll=(int)$defender['base_power']+($defenderSecurity*12)+random_int(0,900);
        $win=$attackerRoll>=$defenderRoll;

        $transfer=['common_metal'=>0,'minor_metal'=>0,'precious_metal'=>0,'fuel'=>0,'biological'=>0];
        if($win){
            foreach($transfer as $key=>$_){
                $take=(int)floor((int)$defenderResources[$key]*0.08);
                $take=min($take,$key==='precious_metal'?400:2500);
                $transfer[$key]=$take;
                if($take>0){
                    msw_stmt("UPDATE player_resources SET {$key}={$key}-? WHERE user_id=? AND {$key}>=?",'iii',[$take,$defenderId,$take]);
                    msw_stmt("UPDATE player_resources SET {$key}={$key}+? WHERE user_id=?",'ii',[$take,$uid]);
                }
            }
            $protection=max(300,(int)msw_config('fob_defender_protection_seconds'));
            $protectedUntil=date('Y-m-d H:i:s',time()+$protection);
            msw_stmt('UPDATE users SET fob_protection_until=? WHERE id=?','si',[$protectedUntil,$defenderId]);
        }
        msw_stmt('UPDATE users SET last_fob_attack_at=NOW() WHERE id=?','i',[$uid]);

        $result=$win?'attacker_win':'defender_win';
        $attackerSnapshot['resolution']=['roll'=>$attackerRoll];
        $defenderSnapshot['resolution']=['roll'=>$defenderRoll];
        msw_stmt(
            'INSERT INTO fob_raids(attacker_user_id,defender_user_id,attacker_snapshot_json,defender_snapshot_json,result,transfer_json) VALUES(?,?,?,?,?,?)',
            'iissss',
            [$uid,$defenderId,json_encode($attackerSnapshot,JSON_UNESCAPED_SLASHES),json_encode($defenderSnapshot,JSON_UNESCAPED_SLASHES),$result,json_encode($transfer,JSON_UNESCAPED_SLASHES)]
        );
        $raidId=(int)$db->insert_id;
        $db->commit();
        msw_console_event_for_user($uid,'FOB','RAID','FOB raid against '.(string)$defender['username'].' resolved: '.strtoupper(str_replace('_',' ',$result)).'.',['raid_id'=>$raidId,'defender_id'=>$defenderId,'defender'=>(string)$defender['username'],'result'=>$result,'materials_transferred'=>(int)array_sum($transfer)]);
        msw_redirect('fob_result.php?id='.$raidId);
    }catch(Throwable $e){
        $db->rollback();
        msw_flash($e->getMessage(),'error');
        msw_redirect('fob.php');
    }
}

$targets=msw_all('SELECT id,username,base_power,base_grade,fob_protection_until,is_bot FROM users WHERE id<>? ORDER BY is_bot ASC,base_power DESC LIMIT 40','i',[$uid]);
$history=msw_all('SELECT r.*,a.username attacker,d.username defender FROM fob_raids r JOIN users a ON a.id=r.attacker_user_id JOIN users d ON d.id=r.defender_user_id WHERE r.attacker_user_id=? OR r.defender_user_id=? ORDER BY r.id DESC LIMIT 12','ii',[$uid,$uid]);
msw_header('FOB Infiltration','fob.php');
msw_alert(msw_flash());
msw_resource_strip($uid);
?>
<section class="hero"><div class="eyebrow">FORWARD OPERATING BASE NETWORK</div><h1>FOB <span>INFILTRATION</span></h1><p>Human and autonomous FOB defense is resolved from immutable server snapshots of Base Power, Security Team and active Combat Unit. A successful infiltration transfers actual stored materials in one transaction; post-raid protection and an attacker cooldown prevent rapid repeated draining.</p></section>
<div class="grid g2" style="margin-top:18px">
<section><?php msw_panel('Available FOB Targets','SERVER SNAPSHOTS'); ?>
<table><thead><tr><th>Commander</th><th>Grade</th><th>Power</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach($targets as $target): $protected=!empty($target['fob_protection_until'])&&strtotime((string)$target['fob_protection_until'])>time(); ?>
<tr><td><?=msw_e($target['username'])?> <?=((int)($target['is_bot']??0)===1)?'<span class="ai-mark">AI FOB</span>':''?></td><td><span class="grade"><?=msw_e($target['base_grade'])?></span></td><td><?=number_format((int)$target['base_power'])?></td><td><?=$protected?'PROTECTED':'OPEN'?></td><td><form method="post"><?=msw_csrf_field()?><input type="hidden" name="defender_id" value="<?=intval($target['id'])?>"><button class="small" <?=$protected?'disabled':''?>>Infiltrate</button></form></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php if(!$targets): ?><div class="empty">No rival FOBs exist yet.</div><?php endif; ?>
<?php msw_panel_end(); ?></section>
<section><?php msw_panel('Raid Ledger','RECENT ACTIVITY'); ?>
<table><thead><tr><th>Match</th><th>Result</th><th>Report</th></tr></thead><tbody>
<?php foreach($history as $raid): ?><tr><td><?=msw_e($raid['attacker'])?> → <?=msw_e($raid['defender'])?></td><td><span class="badge"><?=msw_e(str_replace('_',' ',$raid['result']))?></span></td><td><a class="btn small secondary" href="<?=msw_e(msw_url('fob_result.php?id='.(int)$raid['id']))?>">Report</a></td></tr><?php endforeach; ?>
</tbody></table>
<?php msw_panel_end(); ?></section>
</div>
<?php msw_footer(); ?>
