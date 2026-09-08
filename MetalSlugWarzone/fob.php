<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();$uid=(int)$user['id'];
msw_fob_resolve_due_dispatches($uid,20,true);

$membership=msw_fob_membership($uid);
if(!$membership)msw_redirect('fob_globe.php');

if(msw_is_post()){
    msw_verify_post();
    $action=(string)($_POST['action']??'');
    if($action==='staff_dispatch'){
        $defenderId=(int)($_POST['defender_id']??0);$worldId=(int)($_POST['world_id']??0);
        try{
            $target=msw_fob_target_row($uid,$defenderId,$worldId>0?$worldId:null);
            if(!$target)throw new RuntimeException('That FOB target is no longer available.');
            $dispatchId=msw_fob_launch_staff_dispatch($uid,$defenderId,(array)($_POST['units']??[]));
            msw_flash('Operation #'.$dispatchId.' launched against '.$target['username'].'. Your strike team is now en route.','success');
        }catch(Throwable $e){msw_flash($e->getMessage(),'error');}
        msw_redirect('fob.php');
    }
    msw_redirect('fob.php');
}

$membership=msw_fob_membership($uid);$homeWorld=msw_fob_world_row((int)$membership['world_id']);
$protectionUntil=msw_fob_commander_protection($uid);$isProtected=$protectionUntil!==null;
$counts=msw_fob_command_counts($uid);
$activeOps=msw_fob_active_operations($uid,24);
$nextActiveOpId=0;$nextActiveOpAt=PHP_INT_MAX;foreach($activeOps as $candidate){$at=strtotime((string)$candidate['finish_at'])?:PHP_INT_MAX;if($at<$nextActiveOpAt){$nextActiveOpAt=$at;$nextActiveOpId=(int)$candidate['id'];}}
$incomingOps=msw_fob_incoming_operations($uid,16);
$retaliations=msw_fob_retaliation_rows($uid,16);
$targets=msw_fob_command_targets($uid,18,false);
$plannerTargets=msw_fob_command_targets($uid,80,true);
$units=msw_fob_available_dispatch_units($uid);
$recentOutgoing=msw_fob_recent_outgoing_raids($uid,12);
$worldDirectory=msw_fob_world_directory($uid);$openTargets=0;foreach($worldDirectory as $wr)$openTargets+=(int)($wr['open_targets']??0);
$parallelCapacity=(int)floor(count($units)/2);
$retaliationOpen=0;$retaliationReady=0;foreach($retaliations as $row){if(!empty($row['retaliation_raid_id']))continue;$retaliationOpen++;$targetShield=!empty($row['fob_protection_until'])&&strtotime((string)$row['fob_protection_until'])>time();if(!$targetShield)$retaliationReady++;}
$biomes=msw_fob_biome_catalog();

msw_header('FOB Command Centre','fob.php');msw_alert(msw_flash());msw_resource_strip($uid);
?>
<section class="hero fob-command-hero">
    <div class="eyebrow">FORWARD OPERATING BASE · INVASION COMMAND</div>
    <h1>INVASION <span>COMMAND CENTRE</span></h1>
    <p>Plan worldwide invasions, send multiple staff strike teams, watch incoming threats, and answer enemy raids with retaliation orders from one command screen. Your home FOB stays at <?=msw_e($homeWorld?msw_fob_world_name($homeWorld):'HOME SHARD')?> while your forces can attack open rivals across the global war map.</p>
    <div class="fob-command-hero-actions">
        <a class="btn" href="<?=msw_e(msw_url('fob_globe.php'))?>">Global War Map</a>
        <a class="btn secondary" href="<?=msw_e(msw_url('fob_world.php'))?>">Home Shard</a>
        <a class="btn secondary" href="<?=msw_e(msw_url('fob_infiltration.php'))?>">Raid History</a>
        <a class="btn secondary" href="<?=msw_e(msw_url('fob_dispatch.php'))?>">Strike Operations</a>
    </div>
</section>

<section class="fob-command-status-grid">
    <div class="fob-command-status <?=$isProtected?'protected':'open'?>">
        <small>FOB SHIELD</small><strong><?=$isProtected?'PROTECTED':'COMBAT OPEN'?></strong>
        <?php if($isProtected):?><span>Expires in <b data-countdown="<?=msw_e(date(DATE_ATOM,strtotime((string)$protectionUntil)))?>"></b></span><em>Launching an invasion or retaliation immediately drops the rest of this shield.</em><?php else:?><span>Your FOB can be attacked</span><em>After an enemy attack finishes, your FOB gains a temporary recovery shield.</em><?php endif;?>
    </div>
    <div class="fob-command-status"><small>ACTIVE INVASIONS</small><strong><?=number_format((int)$counts['active_outbound'])?></strong><span>Strike teams en route</span><em><?=number_format(count($units))?> staff available · up to <?=$parallelCapacity?> additional 2-person teams</em></div>
    <div class="fob-command-status <?=count($incomingOps)?'warning':''?>"><small>INBOUND STRIKES</small><strong data-world-incoming-count><?=number_format((int)$counts['active_inbound'])?></strong><span>Enemy strike teams detected</span><em>Your shield is checked again when the enemy team arrives.</em></div>
    <div class="fob-command-status <?=$retaliationReady?'warning':''?>"><small>RETALIATION ORDERS</small><strong><?=number_format($retaliationOpen)?></strong><span>Available counterattacks</span><em><?=number_format($retaliationReady)?> ready now · each enemy raid can be answered once.</em></div>
    <div class="fob-command-status"><small>GLOBAL TARGETS</small><strong><?=number_format($openTargets)?></strong><span>Open FOBs across populated shards</span><em><?=number_format(count($worldDirectory))?> populated FOB shards available on the war map.</em></div>
</section>

<div class="fob-command-layout">
<section class="panel fob-command-primary">
    <div class="panel-head"><div><small>GLOBAL TARGET SCANNER</small><h2>Priority Invasion Targets</h2></div><span class="bolts">•••</span></div>
    <div class="panel-body">
        <div class="fob-command-section-copy">Targets closest to your Base Power are shown first. Player and AI FOBs follow the same invasion rules, and shielded bases remain visible until they become attackable.</div>
        <div class="fob-target-matrix">
        <?php foreach($targets as $target):$targetProtected=!empty($target['fob_protection_until'])&&strtotime((string)$target['fob_protection_until'])>time();$worldLabel=msw_fob_world_name($target);?>
            <article class="fob-command-target <?=$targetProtected?'protected':''?>">
                <div class="fob-command-target-top"><span class="grade"><?=msw_e((string)$target['base_grade'])?></span><?php if((int)$target['is_bot']===1):?><span class="ai-mark">AI #<?=intval($target['bot_index']??0)?></span><?php else:?><span class="badge">HUMAN</span><?php endif;?></div>
                <h3><?=msw_e((string)$target['username'])?></h3>
                <div class="fob-command-target-meta"><span>PWR <b><?=number_format((int)$target['base_power'])?></b></span><span><?=msw_e($worldLabel)?></span></div>
                <?php if($targetProtected):?>
                    <div class="fob-command-lock">PROTECTED · <span data-countdown="<?=msw_e(date(DATE_ATOM,strtotime((string)$target['fob_protection_until'])))?>"></span></div>
                    <a class="btn small secondary" href="<?=msw_e(msw_url('fob_target.php?id='.(int)$target['id'].'&world='.(int)$target['world_id']))?>">Scout Target</a>
                <?php else:?>
                    <div class="actions">
                        <form method="post" action="<?=msw_e(msw_url('fob_attack.php'))?>" <?=$isProtected?'data-breaks-protection="1"':''?>><?=msw_csrf_field()?><input type="hidden" name="defender_id" value="<?=intval($target['id'])?>"><input type="hidden" name="world_id" value="<?=intval($target['world_id'])?>"><input type="hidden" name="return" value="fob.php"><button class="small"><?=$isProtected?'Invade · Drop Shield':'Invade Now'?></button></form>
                        <a class="btn small secondary" href="<?=msw_e(msw_url('fob_target.php?id='.(int)$target['id'].'&world='.(int)$target['world_id']))?>">Full Target Intel</a>
                    </div>
                <?php endif;?>
            </article>
        <?php endforeach;?>
        </div>
        <?php if(!$targets):?><div class="empty">No rival FOBs are open for attack right now.</div><?php endif;?>
        <div class="actions" style="margin-top:14px"><a class="btn secondary" href="<?=msw_e(msw_url('fob_globe.php'))?>">Browse All Regions</a></div>
    </div>
</section>

<aside class="fob-command-side">
<section class="panel">
    <div class="panel-head"><div><small>MULTI-STRIKE OPERATIONS</small><h2>Staff Strike Planner</h2></div><span class="bolts">•••</span></div>
    <div class="panel-body">
        <p class="fob-command-section-copy">Send 2–4 staff against one FOB, then use your remaining personnel against another target. Each strike team runs independently, so you can attack several FOBs at once.</p>
        <?php if(count($units)<2):?><div class="empty">At least two non-dispatched staff are required before another operation can launch.</div>
        <?php elseif(!$plannerTargets):?><div class="empty">No enemy FOB is open for a staff strike right now.</div>
        <?php else:?>
        <form method="post" data-fob-dispatch-form data-min="2" data-max="4" <?=$isProtected?'data-breaks-protection="1"':''?>>
            <?=msw_csrf_field()?><input type="hidden" name="action" value="staff_dispatch">
            <div class="field"><label>Operation Target</label><select name="defender_id" data-command-target-select required>
                <?php foreach($plannerTargets as $target):?><option value="<?=intval($target['id'])?>" data-world="<?=intval($target['world_id'])?>"><?=msw_e((string)$target['username'])?> · <?=msw_e(msw_fob_world_name($target))?> · <?=msw_e((string)$target['base_grade'])?> · PWR <?=number_format((int)$target['base_power'])?></option><?php endforeach;?>
            </select><input type="hidden" name="world_id" value="<?=intval($plannerTargets[0]['world_id'])?>" data-command-world-input></div>
            <div class="fob-command-mini-label">CHOOSE 2–4 AVAILABLE STAFF</div>
            <div class="dispatch-unit-grid fob-command-unit-grid">
                <?php foreach($units as $unit):?><label class="dispatch-unit-card"><input type="checkbox" name="units[]" value="<?=intval($unit['id'])?>"><span><b><?=msw_e((string)$unit['callsign'])?></b><small><?=msw_e((string)$unit['unit_class'])?> · LV <?=intval($unit['level'])?> · <?=msw_e((string)$unit['grade'])?></small><em>COMBAT <?=intval($unit['combat'])?> · SEC <?=intval($unit['security'])?></em></span></label><?php endforeach;?>
            </div>
            <div class="dispatch-submit-row"><span><b data-fob-selected-count>0</b> selected · <?=count($activeOps)?> strikes already active</span><button data-fob-dispatch-submit disabled><?=$isProtected?'Launch · Drop Shield':'Launch Operation'?></button></div>
        </form>
        <?php endif;?>
    </div>
</section>

<section class="panel">
    <div class="panel-head"><div><small>FOB SHIELD RULES</small><h2>How FOB Shields Work</h2></div><span class="bolts">•••</span></div>
    <div class="panel-body"><div class="fob-doctrine-list"><span><b>01</b> After an enemy invasion finishes, the defender receives a temporary shield.</span><span><b>02</b> Shielded FOBs cannot be attacked until their protection expires.</span><span><b>03</b> Launching any invasion immediately removes your own shield.</span><span><b>04</b> Retaliation follows the same rule. A blocked or invalid attack does not remove your shield.</span></div></div>
</section>
</aside>
</div>

<div class="grid g2 fob-command-lower">
<section class="panel">
    <div class="panel-head"><div><small>ACTIVE STRIKE TEAMS</small><h2>Your Active Invasions</h2></div><span class="badge"><?=count($activeOps)?> ACTIVE</span></div>
    <div class="panel-body">
    <?php if($activeOps):?><div class="fob-operation-list"><?php foreach($activeOps as $op):$ids=json_decode((string)$op['unit_ids_json'],true)?:[];?><article class="fob-operation-row"><div><small>OPERATION #<?=intval($op['id'])?> · <?=msw_e(msw_fob_world_name($op))?></small><h3><?=msw_e((string)$op['defender'])?> <span><?=msw_e((string)$op['defender_grade'])?></span></h3><p><?=count($ids)?> staff deployed · estimated success <?=number_format((float)$op['success_chance']*100,1)?>%</p></div><div class="fob-operation-eta"><small>ARRIVAL</small><b data-countdown="<?=msw_e(date(DATE_ATOM,strtotime((string)$op['finish_at'])))?>"<?=(int)$op['id']===$nextActiveOpId?' data-auto-result-url="'.msw_e(msw_url('fob_dispatch_result.php?id='.(int)$op['id'])).'"':''?>></b><a class="btn small secondary" href="<?=msw_e(msw_url('fob_target.php?id='.(int)$op['defender_user_id'].'&world='.(int)$op['world_id']))?>">Target</a></div></article><?php endforeach;?></div>
    <?php else:?><div class="empty">No staff strike teams are currently in flight. Use the planner above to launch attacks as personnel become available.</div><?php endif;?>
    </div>
</section>

<section class="panel">
    <div class="panel-head"><div><small>EARLY WARNING</small><h2>Incoming Strike Teams</h2></div><span class="badge" data-world-incoming-count="badge"><?=number_format((int)$counts['active_inbound'])?> DETECTED</span></div>
    <div class="panel-body">
    <p class="muted-copy" data-world-live-status role="status">Strike reports update automatically.</p>
    <div class="fob-operation-list inbound" data-world-incoming><?php if($incomingOps):?><?php foreach($incomingOps as $op):?><article class="fob-operation-row" data-incoming-id="<?=intval($op['id'])?>"><div><small>INBOUND #<?=intval($op['id'])?> · <?=msw_e(msw_fob_world_name($op))?></small><h3><?=msw_e((string)$op['attacker'])?> <span><?=msw_e((string)$op['attacker_grade'])?></span></h3><p>Enemy staff team · estimated success <?=number_format((float)$op['success_chance']*100,1)?>%</p></div><div class="fob-operation-eta"><small>ETA</small><b data-world-eta="<?=(int)strtotime((string)$op['finish_at'])*1000?>"></b></div></article><?php endforeach;?>
    <?php else:?><div class="empty">No enemy staff strike teams are currently heading toward your FOB.</div><?php endif;?></div>
    </div>
</section>
</div>

<section class="panel fob-retaliation-panel">
    <div class="panel-head"><div><small>RETALIATION ORDERS</small><h2>Retaliation Targets</h2></div><span class="badge"><?=$retaliationReady?> READY · <?=$retaliationOpen?> OPEN</span></div>
    <div class="panel-body">
        <p class="fob-command-section-copy">Every enemy raid gives you one chance to retaliate against that attacker. You can use it once. You still cannot strike through an enemy FOB shield, and launching retaliation drops your own shield.</p>
        <?php if($retaliations):?><div class="fob-retaliation-grid">
        <?php foreach($retaliations as $r):$used=!empty($r['retaliation_raid_id']);$targetProtected=!empty($r['fob_protection_until'])&&strtotime((string)$r['fob_protection_until'])>time();$tr=json_decode((string)$r['transfer_json'],true)?:[];$loss=array_sum(array_map('intval',$tr));?>
            <article class="fob-retaliation-card <?=$used?'resolved':($targetProtected?'locked':'ready')?>">
                <div class="fob-retaliation-head"><span>INCIDENT #<?=intval($r['source_raid_id'])?></span><time><?=msw_e(date('d M · H:i',strtotime((string)$r['attacked_at'])))?></time></div>
                <h3><?=msw_e((string)$r['username'])?> <?php if((int)$r['is_bot']===1):?><small class="ai-mark">AI #<?=intval($r['bot_index']??0)?></small><?php endif;?></h3>
                <div class="fob-retaliation-meta"><span>Your defense: <b><?=msw_e($r['source_result']==='attacker_win'?'BREACHED':'HELD')?></b></span><span>Material loss: <b><?=number_format((int)$loss)?></b></span><span><?=msw_e(msw_fob_world_name($r))?></span></div>
                <?php if($used):?><div class="fob-retaliation-state complete">RETALIATION COMPLETE · RAID #<?=intval($r['retaliation_raid_id'])?></div><div class="actions"><a class="btn small secondary" href="<?=msw_e(msw_url('fob_result.php?id='.(int)$r['retaliation_raid_id']))?>">Retaliation AAR</a></div>
                <?php elseif($targetProtected):?><div class="fob-retaliation-state">TARGET PROTECTED · <span data-countdown="<?=msw_e(date(DATE_ATOM,strtotime((string)$r['fob_protection_until'])))?>"></span></div><div class="actions"><a class="btn small secondary" href="<?=msw_e(msw_url('fob_target.php?id='.(int)$r['target_id'].'&world='.(int)$r['world_id']))?>">Scout Attacker</a></div>
                <?php else:?><div class="fob-retaliation-state ready">RETALIATION READY</div><div class="actions"><form method="post" action="<?=msw_e(msw_url('fob_attack.php'))?>" <?=$isProtected?'data-breaks-protection="1"':''?>><?=msw_csrf_field()?><input type="hidden" name="defender_id" value="<?=intval($r['target_id'])?>"><input type="hidden" name="world_id" value="<?=intval($r['world_id'])?>"><input type="hidden" name="retaliation_raid_id" value="<?=intval($r['source_raid_id'])?>"><input type="hidden" name="return" value="fob.php"><button class="small danger"><?=$isProtected?'Retaliate · Drop Shield':'Retaliate Now'?></button></form><a class="btn small secondary" href="<?=msw_e(msw_url('fob_result.php?id='.(int)$r['source_raid_id']))?>">Incoming Report</a><a class="btn small secondary" href="<?=msw_e(msw_url('fob_target.php?id='.(int)$r['target_id'].'&world='.(int)$r['world_id']))?>">Scout Target</a></div><?php endif;?>
            </article>
        <?php endforeach;?></div><?php else:?><div class="empty">No enemy FOB raids have been recorded against you yet.</div><?php endif;?>
    </div>
</section>

<section class="panel">
    <div class="panel-head"><div><small>BATTLE ARCHIVE</small><h2>Recent Invasion Reports</h2></div><span class="bolts">•••</span></div>
    <div class="panel-body">
        <?php if($recentOutgoing):?><div class="fob-command-panel-scroll"><table class="fob-command-table"><thead><tr><th>Raid</th><th>Target</th><th>Operation</th><th>Result</th><th>Transfer</th><th>Report</th></tr></thead><tbody>
        <?php foreach($recentOutgoing as $r):$as=json_decode((string)$r['attacker_snapshot_json'],true)?:[];$mode=(string)($as['resolution']['mode']??'direct');$modeLabel=['direct'=>'Immediate','staff_dispatch'=>'Staff Strike','autonomous'=>'AI Raid','retaliation'=>'Retaliation'][$mode]??ucwords(str_replace('_',' ',$mode));$tr=json_decode((string)$r['transfer_json'],true)?:[];?><tr><td>#<?=intval($r['id'])?></td><td><?=msw_e((string)$r['defender'])?> · <?=msw_e((string)$r['defender_grade'])?></td><td><?=msw_e($modeLabel)?></td><td><span class="badge"><?=msw_e(strtoupper(msw_fob_result_label((string)$r['result'])))?></span></td><td><?=number_format((int)array_sum(array_map('intval',$tr)))?></td><td><a class="btn small secondary" href="<?=msw_e(msw_url('fob_result.php?id='.(int)$r['id']))?>">Battle Replay</a></td></tr><?php endforeach;?></tbody></table></div>
        <?php else:?><div class="empty">You have not launched any completed FOB raids yet.</div><?php endif;?>
    </div>
</section>
<?php msw_footer(); ?>
