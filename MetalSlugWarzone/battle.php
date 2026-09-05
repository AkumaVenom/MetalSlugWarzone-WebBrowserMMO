<?php
declare(strict_types=1);
require __DIR__.'/includes/battle_engine.php';
require_once __DIR__.'/includes/ui.php';
$u=msw_require_user();$uid=(int)$u['id'];$id=(int)($_GET['id']??$_POST['id']??0);if($id<=0)msw_redirect('map_select.php');$flash=null;

if(msw_is_post()){
    msw_verify_post();$version=(int)($_POST['version']??0);$db=msw_db();$db->begin_transaction();$consoleEvents=[];
    try{
        $row=msw_one("SELECT * FROM encounters WHERE id=? AND user_id=? FOR UPDATE",'ii',[$id,$uid]);
        if(!$row||$row['status']!=='active'||(int)$row['version']!==$version){$db->rollback();msw_flash('Battle state changed in another request. Refreshed safely.','warning');msw_redirect('battle.php?id='.$id);}
        $s=json_decode((string)$row['state_json'],true,512,JSON_THROW_ON_ERROR);
        msw_sync_commander_battle_state($uid,$s);msw_sync_enemy_runtime_state($s);msw_sync_battle_support_state($uid,$s);
        $action=(string)($_POST['action']??'');
        if($action==='attack'){
            $moveKey=(string)($_POST['move']??'rifle_burst');$moves=msw_move_catalog();$moveName=(string)($moves[$moveKey]['name']??$moves['rifle_burst']['name']??'Attack');
            msw_battle_attack($s,$moveKey);
            $consoleEvents[]=['COMBAT','ATTACK',$moveName.' against '.(string)($s['enemy']['name']??'target').'.',['encounter_id'=>$id,'move'=>$moveName,'enemy_hp'=>(int)($s['enemy']['hp']??0),'round'=>(int)($s['round']??0),'backup_hits'=>count((array)($s['fx']['backup_slots']??[]))]];
        }elseif($action==='medical'){
            $itemKey=(string)($_POST['medical_item']??'field_medkit');$medical=msw_battle_item_catalog();$itemName=(string)($medical[$itemKey]['name']??$itemKey);
            [$ok,$msg]=msw_use_battle_item($uid,$s,$itemKey);$flash=['message'=>$msg,'kind'=>$ok?'success':'warning'];
            if($ok)$consoleEvents[]=['MEDICAL','FIELD_USE',$itemName.' used during combat.',['encounter_id'=>$id,'item'=>$itemKey,'healed'=>(int)($s['fx']['heal']??0),'player_hp'=>(int)($s['player']['hp']??0)]];
        }elseif($action==='recover'){
            $itemKey=(string)($_POST['item']??'fulton');$fultonCatalog=msw_fulton_catalog();$systemName=(string)($fultonCatalog[$itemKey]['name']??$itemKey);
            [$ok,$msg]=msw_try_recovery($uid,$s,$itemKey);$flash=['message'=>$msg,'kind'=>$ok?'success':'warning'];
            if(str_contains($msg,'Recovery'))$consoleEvents[]=['RECOVERY',$ok?'SUCCESS':'FAILED',($ok?'Recovered ':'Recovery attempt on ').(string)($s['enemy']['name']??'target').' using '.$systemName.'.',['encounter_id'=>$id,'target'=>(string)($s['enemy']['name']??'target'),'system'=>$systemName,'success'=>$ok]];
        }elseif($action==='retreat'){
            if($s['context']==='field'){$s['finished']=true;$s['result']='retreated';msw_battle_fx($s,'retreat');$consoleEvents[]=['COMBAT','RETREAT','Retreated from '.(string)($s['enemy']['name']??'field engagement').'.',['encounter_id'=>$id]];}
            else $flash=['message'=>'Retreat is unavailable in this operation.','kind'=>'error'];
        }
        $resolved=null;
        if(!empty($s['finished'])){$resolved=(string)($s['result']??'lost');msw_finalize_battle($uid,$id,$s);}else msw_stmt('UPDATE encounters SET state_json=?,version=version+1 WHERE id=? AND user_id=?','sii',[json_encode($s,JSON_UNESCAPED_SLASHES),$id,$uid]);
        $db->commit();
        if($resolved!==null)$consoleEvents[]=['COMBAT','RESOLVED','Engagement '.$resolved.' against '.(string)($s['enemy']['name']??'target').'.',['encounter_id'=>$id,'result'=>$resolved,'context'=>(string)($s['context']??'field'),'context_key'=>(string)($s['context_key']??''),'enemy'=>(string)($s['enemy']['name']??'target')]];
        foreach($consoleEvents as $evt)msw_console_event_for_user($uid,$evt[0],$evt[1],$evt[2],$evt[3]);
    }catch(Throwable $e){$db->rollback();throw $e;}
}

$row=msw_one('SELECT * FROM encounters WHERE id=? AND user_id=?','ii',[$id,$uid]);if(!$row){http_response_code(404);exit('Battle not found.');}
$s=json_decode((string)$row['state_json'],true);if($row['status']==='active'){msw_sync_commander_battle_state($uid,$s);msw_sync_enemy_runtime_state($s);msw_sync_battle_support_state($uid,$s);}
$inv=msw_inventory($uid);$enemy=$s['enemy'];$player=$s['player'];$systems=(array)($s['systems']??msw_battle_system_snapshot($uid));$backups=(array)($s['backups']??[]);$fx=(array)($s['fx']??[]);
$fxClasses=['msw-battle-arena','fx-action-'.preg_replace('/[^a-z0-9_-]/','',(string)($fx['action']??'contact'))];
if(!empty($fx['player_hit']))$fxClasses[]='fx-player-hit';if(!empty($fx['enemy_counter']))$fxClasses[]='fx-enemy-counter';if(!empty($fx['enemy_hit']))$fxClasses[]='fx-enemy-hit';if(!empty($fx['backup_slots']))$fxClasses[]='fx-backup';if(!empty($fx['recovery_success']))$fxClasses[]='fx-recovery-success';
$character=msw_character_catalog()[$u['character_key']]??reset(msw_character_catalog());$recommended=msw_battle_recommended_move($s);
msw_header('Combat Engagement');if(!$flash)$flash=msw_flash();msw_alert($flash);
?>
<div class="grid g2 battle-layout">
<section><?php msw_panel(ucfirst((string)$s['context']).' Engagement','TURN '.(int)$s['round'].' · LIVE CHOREOGRAPHY'); ?>
<div class="battle-scene <?=msw_e(implode(' ',$fxClasses))?>" data-battle-fx-seq="<?=intval($fx['seq']??0)?>">
    <div class="battle-side battle-side-player">
        <div class="fighter player">
            <div class="fighter-sprite-shell"><img src="<?=msw_e(msw_url($character['sprite']))?>" alt=""></div>
            <div class="battle-card"><b><?=msw_e($player['name'])?> · Lv <?=intval($player['level'])?></b><div class="hpbar"><i style="width:<?=max(0,min(100,round(100*$player['hp']/max(1,$player['max_hp']))))?>%"></i></div><small>HP <?=intval($player['hp'])?> / <?=intval($player['max_hp'])?> · <?=msw_e($player['class'])?></small></div>
        </div>
        <?php if($backups): ?><div class="security-backup-line" aria-label="Security backup detail">
            <?php foreach($backups as $backup):$slot=(int)($backup['slot']??0);$hit=in_array($slot,(array)($fx['backup_slots']??[]),true);?><div class="security-backup <?=$hit?'assist-hit':''?>" title="Security backup slot <?=$slot?> · controlled low-damage assist"><img src="<?=msw_e(msw_url((string)$backup['sprite']))?>" alt=""><span><b><?=msw_e($backup['name'])?></b><small>SLOT <?=$slot?> · <?=msw_e($backup['grade'])?></small></span></div><?php endforeach; ?>
        </div><?php else: ?><div class="security-backup-empty"><small>No Security backup detail selected · configure two staff in Staff Management.</small></div><?php endif; ?>
    </div>
    <div class="battle-vs">VS</div>
    <div class="battle-side battle-side-enemy">
        <div class="fighter enemy enemy-<?=msw_e((string)$enemy['class'])?>">
            <div class="fighter-sprite-shell"><img src="<?=msw_e(msw_url($enemy['sprite']))?>" alt=""></div>
            <div class="battle-card"><b><?=msw_e($enemy['name'])?> · Lv <?=intval($enemy['level'])?></b><div class="hpbar"><i style="width:<?=max(0,min(100,round(100*$enemy['hp']/max(1,$enemy['max_hp']))))?>%"></i></div><small>HP <?=intval($enemy['hp'])?> / <?=intval($enemy['max_hp'])?> · <?=msw_e($enemy['class'])?></small></div>
        </div>
    </div>
</div>
<?php msw_panel_end(); ?>

<?php if((int)($systems['intel']??1)>=2): ?>
<section class="panel battle-intel-panel" style="margin-top:14px"><div class="panel-head"><div><small>INTEL TEAM LV <?=intval($systems['intel'])?></small><h2>Tactical Threat Lens</h2></div><span class="bolts">•••</span></div><div class="panel-body">
<div class="intel-stat-grid"><span>ATK <b><?=intval($enemy['attack'])?></b></span><span>DEF <b><?=intval($enemy['defense'])?></b></span><span>SPD <b><?=intval($enemy['speed'])?></b></span><?php if((int)$systems['intel']>=8):?><span>COUNTER <b>−6pp ACC</b></span><?php endif;?></div>
<?php if($recommended):?><p class="muted-copy">Weakness Matrix recommends <b><?=msw_e($recommended['name'])?></b> at <?=number_format((float)$recommended['multiplier'],2)?>× class effectiveness.</p><?php elseif((int)$systems['intel']<4):?><p class="muted-copy">Weakness Matrix unlocks at Intel Team Lv 4.</p><?php endif;?>
</div></section>
<?php endif; ?>
</section>

<section><?php msw_panel('Combat Console','SERVER AUTHORITY'); ?>
<div class="battle-log"><?php foreach(array_reverse(array_slice($s['log'],-14)) as $line):?><div><?=msw_e($line)?></div><?php endforeach;?></div>
<?php if($row['status']==='active'): ?>
<form method="post" style="margin-top:12px" class="battle-command-form">
    <?=msw_csrf_field()?><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="version" value="<?=intval($row['version'])?>">
    <div class="field"><label>Attack Pattern</label><select name="move"><?php foreach(msw_move_catalog() as $k=>$m):$mult=msw_type_multiplier((string)$m['type'],(string)$enemy['class']);?><option value="<?=msw_e($k)?>"><?=msw_e($m['name'])?> · <?=msw_e($m['type'])?> · PWR <?=intval($m['power'])?><?=(int)$systems['intel']>=4?' · '.number_format($mult,2).'×':''?><?=$recommended&&$recommended['key']===$k?' · RECOMMENDED':''?></option><?php endforeach;?></select></div>
    <div class="actions"><button name="action" value="attack">Execute Attack</button><?php if($s['context']==='field'):?><button class="danger" name="action" value="retreat">Retreat</button><?php endif;?></div>

    <div class="battle-command-module"><div class="field"><label>Battlefield Medical Supply</label><select name="medical_item"><?php foreach(msw_battle_item_catalog() as $k=>$item):$unlocked=msw_requirements_met($uid,(array)$item['requirements']);?><option value="<?=msw_e($k)?>" <?=$unlocked?'':'disabled'?>><?=msw_e($item['name'])?> · x<?=intval($inv[$k]??0)?> · HEAL <?=intval($item['heal'])?><?=$unlocked?'':' · LOCKED '.msw_requirement_label((array)$item['requirements'])?></option><?php endforeach;?></select><small>Using a medical supply consumes your action. Support Lv 3 / 6 boosts healing to +15% / +25%.</small></div><button class="secondary" name="action" value="medical">Use Medical Supply</button></div>

    <?php if((int)$enemy['recruitable']):?><div class="battle-command-module"><div class="field"><label>Fulton System</label><select name="item"><?php foreach(msw_fulton_catalog() as $k=>$f):$chance=((int)$systems['intel']>=6&&in_array((string)$enemy['class'],(array)$f['classes'],true))?(int)round(msw_recovery_chance($s,$f)*100):null;?><option value="<?=msw_e($k)?>"><?=msw_e($f['name'])?> · x<?=intval($inv[$k]??0)?> · R&D <?=intval($f['rd'])?><?=$chance!==null?' · '.$chance.'%':''?></option><?php endforeach;?></select><small><?=((int)$systems['intel']>=6)?'Intel Fulton Forecast is active: displayed percentages are the exact current server-calculated chance.':'Recovery probability rises as target HP falls. Intel Lv 6 unlocks exact pre-use forecasts.'?></small></div><button class="secondary" name="action" value="recover">Attempt Recovery</button></div><?php endif;?>
</form>
<?php else: ?>
<?php $resultKind=$row['status']==='lost'?'warning':'success';$continuePage=match((string)$s['context']){'field'=>'map.php?zone='.urlencode((string)$s['context_key']),'boss'=>'bosses.php','sidequest'=>'sidequests.php','trainer'=>'commanders.php',default=>'missions.php'}; ?>
<div class="alert <?=$resultKind?>" style="margin-top:12px">Engagement complete: <?=msw_e(strtoupper((string)$row['status']))?>. Your operative and recovered roster return fully combat-ready after the engagement.</div><div class="actions"><a class="btn" href="<?=msw_e(msw_url($continuePage))?>">Continue</a><a class="btn secondary" href="<?=msw_e(msw_url('base.php'))?>">Mother Base</a></div>
<?php endif; ?>
<?php msw_panel_end(); ?></section>
</div>
<?php msw_footer(); ?>
