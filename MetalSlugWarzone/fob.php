<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();$uid=(int)$user['id'];
msw_fob_resolve_due_dispatches($uid,20);
msw_bot_simulation_pulse(null,8);
$membership=msw_fob_membership($uid);
if(!$membership)msw_redirect('fob_globe.php');

if(msw_is_post()){
    msw_verify_post();
    $action=(string)($_POST['action']??'');
    if($action==='staff_dispatch'){
        $defenderId=(int)($_POST['defender_id']??0);$worldId=(int)($_POST['world_id']??0);
        try{
            $target=msw_fob_target_row($uid,$defenderId,$worldId>0?$worldId:null);
            if(!$target)throw new RuntimeException('Selected command-centre target is no longer valid.');
            $dispatchId=msw_fob_launch_staff_dispatch($uid,$defenderId,(array)($_POST['units']??[]));
            msw_flash('Operation #'.$dispatchId.' launched against '.$target['username'].'. The staff strike is persisted and will resolve from its server timestamp.','success');
        }catch(Throwable $e){msw_flash($e->getMessage(),'error');}
        msw_redirect('fob.php');
    }
    msw_redirect('fob.php');
}

$membership=msw_fob_membership($uid);$homeWorld=msw_fob_world_row((int)$membership['world_id']);
$protectionUntil=msw_fob_commander_protection($uid);$isProtected=$protectionUntil!==null;
$counts=msw_fob_command_counts($uid);
$activeOps=msw_fob_active_operations($uid,24);
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
    <div class="eyebrow">FORWARD OPERATING BASE · STRATEGIC COMMAND CENTRE</div>
    <h1>INVASION <span>COMMAND CENTRE</span></h1>
    <p>Plan and execute global invasions, coordinate multiple persisted staff strike teams, monitor inbound threats, and issue one-use retaliation orders from a single server-authoritative command surface. Your FOB remains anchored to <?=msw_e($homeWorld?msw_fob_world_name($homeWorld):'HOME SHARD')?> while offensive operations can reach any valid global shard.</p>
    <div class="fob-command-hero-actions">
        <a class="btn" href="<?=msw_e(msw_url('fob_globe.php'))?>">Global Theatre Map</a>
        <a class="btn secondary" href="<?=msw_e(msw_url('fob_world.php'))?>">Home FOB Shard</a>
        <a class="btn secondary" href="<?=msw_e(msw_url('fob_infiltration.php'))?>">Raid Ledger</a>
        <a class="btn secondary" href="<?=msw_e(msw_url('fob_dispatch.php'))?>">Strike Ledger</a>
    </div>
</section>

<section class="fob-command-status-grid">
    <div class="fob-command-status <?=$isProtected?'protected':'open'?>">
        <small>RECOVERY SHIELD</small><strong><?=$isProtected?'PROTECTED':'COMBAT OPEN'?></strong>
        <?php if($isProtected):?><span>Expires in <b data-countdown="<?=msw_e(date(DATE_ATOM,strtotime((string)$protectionUntil)))?>"></b></span><em>Any successfully committed invasion or retaliation immediately removes this remaining protection.</em><?php else:?><span>No active defender protection</span><em>Incoming resolved attacks will grant the normal post-invasion recovery shield.</em><?php endif;?>
    </div>
    <div class="fob-command-status"><small>ACTIVE INVASIONS</small><strong><?=number_format((int)$counts['active_outbound'])?></strong><span>Persisted staff teams in flight</span><em><?=number_format(count($units))?> staff available · up to <?=$parallelCapacity?> additional 2-person teams</em></div>
    <div class="fob-command-status <?=count($incomingOps)?'warning':''?>"><small>INBOUND STRIKES</small><strong><?=number_format((int)$counts['active_inbound'])?></strong><span>Detected staff incursions</span><em>Arrival still revalidates your protection state under lock.</em></div>
    <div class="fob-command-status <?=$retaliationReady?'warning':''?>"><small>RETALIATION ORDERS</small><strong><?=number_format($retaliationOpen)?></strong><span>Unconsumed incoming-raid authorizations</span><em><?=number_format($retaliationReady)?> ready now · each incident can authorize one retaliatory direct strike.</em></div>
    <div class="fob-command-status"><small>GLOBAL TARGETS</small><strong><?=number_format($openTargets)?></strong><span>Open FOBs across populated shards</span><em><?=number_format(count($worldDirectory))?> populated invasion shards visible to command.</em></div>
</section>

<div class="fob-command-layout">
<section class="panel fob-command-primary">
    <div class="panel-head"><div><small>GLOBAL TARGETING MATRIX</small><h2>Priority Invasion Targets</h2></div><span class="bolts">•••</span></div>
    <div class="panel-body">
        <div class="fob-command-section-copy">Targets are prioritized toward comparable Base Power while keeping human and autonomous commanders inside the same global authority model. Protected FOBs remain visible for planning but cannot be attacked until their shield expires.</div>
        <div class="fob-target-matrix">
        <?php foreach($targets as $target):$targetProtected=!empty($target['fob_protection_until'])&&strtotime((string)$target['fob_protection_until'])>time();$worldLabel=msw_fob_world_name($target);?>
            <article class="fob-command-target <?=$targetProtected?'protected':''?>">
                <div class="fob-command-target-top"><span class="grade"><?=msw_e((string)$target['base_grade'])?></span><?php if((int)$target['is_bot']===1):?><span class="ai-mark">AI #<?=intval($target['bot_index']??0)?></span><?php else:?><span class="badge">HUMAN</span><?php endif;?></div>
                <h3><?=msw_e((string)$target['username'])?></h3>
                <div class="fob-command-target-meta"><span>PWR <b><?=number_format((int)$target['base_power'])?></b></span><span><?=msw_e($worldLabel)?></span></div>
                <?php if($targetProtected):?>
                    <div class="fob-command-lock">PROTECTED · <span data-countdown="<?=msw_e(date(DATE_ATOM,strtotime((string)$target['fob_protection_until'])))?>"></span></div>
                    <a class="btn small secondary" href="<?=msw_e(msw_url('fob_target.php?id='.(int)$target['id'].'&world='.(int)$target['world_id']))?>">Target Intel</a>
                <?php else:?>
                    <div class="actions">
                        <form method="post" action="<?=msw_e(msw_url('fob_attack.php'))?>" <?=$isProtected?'data-breaks-protection="1"':''?>><?=msw_csrf_field()?><input type="hidden" name="defender_id" value="<?=intval($target['id'])?>"><input type="hidden" name="world_id" value="<?=intval($target['world_id'])?>"><input type="hidden" name="return" value="fob.php"><button class="small"><?=$isProtected?'Invade · Drop Shield':'Invade Now'?></button></form>
                        <a class="btn small secondary" href="<?=msw_e(msw_url('fob_target.php?id='.(int)$target['id'].'&world='.(int)$target['world_id']))?>">Full Intel</a>
                    </div>
                <?php endif;?>
            </article>
        <?php endforeach;?>
        </div>
        <?php if(!$targets):?><div class="empty">No rival FOBs are currently available in the global network.</div><?php endif;?>
        <div class="actions" style="margin-top:14px"><a class="btn secondary" href="<?=msw_e(msw_url('fob_globe.php'))?>">Browse All Theatres</a></div>
    </div>
</section>

<aside class="fob-command-side">
<section class="panel">
    <div class="panel-head"><div><small>MULTI-INVASION OPERATIONS</small><h2>Staff Strike Planner</h2></div><span class="bolts">•••</span></div>
    <div class="panel-body">
        <p class="fob-command-section-copy">Launch one persisted 2–4 member strike, then assign remaining staff to another target. Multiple operations run concurrently because every team owns its own durable dispatch record and staff reservation.</p>
        <?php if(count($units)<2):?><div class="empty">At least two non-dispatched staff are required before another operation can launch.</div>
        <?php elseif(!$plannerTargets):?><div class="empty">No globally open FOB target is available right now.</div>
        <?php else:?>
        <form method="post" data-fob-dispatch-form data-min="2" data-max="4" <?=$isProtected?'data-breaks-protection="1"':''?>>
            <?=msw_csrf_field()?><input type="hidden" name="action" value="staff_dispatch">
            <div class="field"><label>Operation Target</label><select name="defender_id" data-command-target-select required>
                <?php foreach($plannerTargets as $target):?><option value="<?=intval($target['id'])?>" data-world="<?=intval($target['world_id'])?>"><?=msw_e((string)$target['username'])?> · <?=msw_e(msw_fob_world_name($target))?> · <?=msw_e((string)$target['base_grade'])?> · PWR <?=number_format((int)$target['base_power'])?></option><?php endforeach;?>
            </select><input type="hidden" name="world_id" value="<?=intval($plannerTargets[0]['world_id'])?>" data-command-world-input></div>
            <div class="fob-command-mini-label">ASSIGN 2–4 AVAILABLE STAFF</div>
            <div class="dispatch-unit-grid fob-command-unit-grid">
                <?php foreach($units as $unit):?><label class="dispatch-unit-card"><input type="checkbox" name="units[]" value="<?=intval($unit['id'])?>"><span><b><?=msw_e((string)$unit['callsign'])?></b><small><?=msw_e((string)$unit['unit_class'])?> · LV <?=intval($unit['level'])?> · <?=msw_e((string)$unit['grade'])?></small><em>COMBAT <?=intval($unit['combat'])?> · SEC <?=intval($unit['security'])?></em></span></label><?php endforeach;?>
            </div>
            <div class="dispatch-submit-row"><span><b data-fob-selected-count>0</b> selected · <?=count($activeOps)?> operations already active</span><button data-fob-dispatch-submit disabled><?=$isProtected?'Launch · Drop Shield':'Launch Operation'?></button></div>
        </form>
        <?php endif;?>
    </div>
</section>

<section class="panel">
    <div class="panel-head"><div><small>PROTECTION DOCTRINE</small><h2>Recovery Shield Rules</h2></div><span class="bolts">•••</span></div>
    <div class="panel-body"><div class="fob-doctrine-list"><span><b>01</b> Every completed incoming invasion grants defender protection.</span><span><b>02</b> Passive protected commanders cannot be selected as attack targets.</span><span><b>03</b> Successfully launching any invasion voluntarily removes your own protection.</span><span><b>04</b> Retaliation follows the same rule; blocked or invalid attempts do not consume your shield.</span></div></div>
</section>
</aside>
</div>

<div class="grid g2 fob-command-lower">
<section class="panel">
    <div class="panel-head"><div><small>LIVE OPERATION BOARD</small><h2>Active Outbound Invasions</h2></div><span class="badge"><?=count($activeOps)?> ACTIVE</span></div>
    <div class="panel-body">
    <?php if($activeOps):?><div class="fob-operation-list"><?php foreach($activeOps as $op):$ids=json_decode((string)$op['unit_ids_json'],true)?:[];?><article class="fob-operation-row"><div><small>OPERATION #<?=intval($op['id'])?> · <?=msw_e(msw_fob_world_name($op))?></small><h3><?=msw_e((string)$op['defender'])?> <span><?=msw_e((string)$op['defender_grade'])?></span></h3><p><?=count($ids)?> staff committed · projected success <?=number_format((float)$op['success_chance']*100,1)?>%</p></div><div class="fob-operation-eta"><small>ARRIVAL</small><b data-countdown="<?=msw_e(date(DATE_ATOM,strtotime((string)$op['finish_at'])))?>"></b><a class="btn small secondary" href="<?=msw_e(msw_url('fob_target.php?id='.(int)$op['defender_user_id'].'&world='.(int)$op['world_id']))?>">Target</a></div></article><?php endforeach;?></div>
    <?php else:?><div class="empty">No staff invasion teams are currently in flight. The planner above can launch multiple concurrent operations as staff become available.</div><?php endif;?>
    </div>
</section>

<section class="panel">
    <div class="panel-head"><div><small>EARLY WARNING NETWORK</small><h2>Inbound Staff Threats</h2></div><span class="badge"><?=count($incomingOps)?> DETECTED</span></div>
    <div class="panel-body">
    <?php if($incomingOps):?><div class="fob-operation-list inbound"><?php foreach($incomingOps as $op):?><article class="fob-operation-row"><div><small>INBOUND #<?=intval($op['id'])?> · <?=msw_e(msw_fob_world_name($op))?></small><h3><?=msw_e((string)$op['attacker'])?> <span><?=msw_e((string)$op['attacker_grade'])?></span></h3><p>Detected staff team · stored strike chance <?=number_format((float)$op['success_chance']*100,1)?>%</p></div><div class="fob-operation-eta"><small>ETA</small><b data-countdown="<?=msw_e(date(DATE_ATOM,strtotime((string)$op['finish_at'])))?>"></b></div></article><?php endforeach;?></div>
    <?php else:?><div class="empty">No persisted staff strike teams are currently detected inbound to your FOB.</div><?php endif;?>
    </div>
</section>
</div>

<section class="panel fob-retaliation-panel">
    <div class="panel-head"><div><small>COUNTER-INVASION AUTHORITY</small><h2>Retaliation Command Desk</h2></div><span class="badge"><?=$retaliationReady?> READY · <?=$retaliationOpen?> OPEN</span></div>
    <div class="panel-body">
        <p class="fob-command-section-copy">Every incoming raid is retained as a specific incident. An unconsumed incident authorizes one retaliatory direct strike against that exact attacker. Retaliation does not bypass the target's current defender protection, and issuing the strike drops your own active protection as soon as the attack is successfully committed.</p>
        <?php if($retaliations):?><div class="fob-retaliation-grid">
        <?php foreach($retaliations as $r):$used=!empty($r['retaliation_raid_id']);$targetProtected=!empty($r['fob_protection_until'])&&strtotime((string)$r['fob_protection_until'])>time();$tr=json_decode((string)$r['transfer_json'],true)?:[];$loss=array_sum(array_map('intval',$tr));?>
            <article class="fob-retaliation-card <?=$used?'resolved':($targetProtected?'locked':'ready')?>">
                <div class="fob-retaliation-head"><span>INCIDENT #<?=intval($r['source_raid_id'])?></span><time><?=msw_e(date('d M · H:i',strtotime((string)$r['attacked_at'])))?></time></div>
                <h3><?=msw_e((string)$r['username'])?> <?php if((int)$r['is_bot']===1):?><small class="ai-mark">AI #<?=intval($r['bot_index']??0)?></small><?php endif;?></h3>
                <div class="fob-retaliation-meta"><span>Your defense: <b><?=msw_e($r['source_result']==='attacker_win'?'BREACHED':'HELD')?></b></span><span>Material loss: <b><?=number_format((int)$loss)?></b></span><span><?=msw_e(msw_fob_world_name($r))?></span></div>
                <?php if($used):?><div class="fob-retaliation-state complete">RETALIATION EXECUTED · RAID #<?=intval($r['retaliation_raid_id'])?></div><div class="actions"><a class="btn small secondary" href="<?=msw_e(msw_url('fob_result.php?id='.(int)$r['retaliation_raid_id']))?>">Retaliation AAR</a></div>
                <?php elseif($targetProtected):?><div class="fob-retaliation-state">TARGET PROTECTED · <span data-countdown="<?=msw_e(date(DATE_ATOM,strtotime((string)$r['fob_protection_until'])))?>"></span></div><div class="actions"><a class="btn small secondary" href="<?=msw_e(msw_url('fob_target.php?id='.(int)$r['target_id'].'&world='.(int)$r['world_id']))?>">Track Attacker</a></div>
                <?php else:?><div class="fob-retaliation-state ready">RETALIATION AUTHORIZED</div><div class="actions"><form method="post" action="<?=msw_e(msw_url('fob_attack.php'))?>" <?=$isProtected?'data-breaks-protection="1"':''?>><?=msw_csrf_field()?><input type="hidden" name="defender_id" value="<?=intval($r['target_id'])?>"><input type="hidden" name="world_id" value="<?=intval($r['world_id'])?>"><input type="hidden" name="retaliation_raid_id" value="<?=intval($r['source_raid_id'])?>"><input type="hidden" name="return" value="fob.php"><button class="small danger"><?=$isProtected?'Retaliate · Drop Shield':'Retaliate Now'?></button></form><a class="btn small secondary" href="<?=msw_e(msw_url('fob_result.php?id='.(int)$r['source_raid_id']))?>">Incoming AAR</a><a class="btn small secondary" href="<?=msw_e(msw_url('fob_target.php?id='.(int)$r['target_id'].'&world='.(int)$r['world_id']))?>">Target Intel</a></div><?php endif;?>
            </article>
        <?php endforeach;?></div><?php else:?><div class="empty">No incoming FOB raid incidents have been recorded against this commander.</div><?php endif;?>
    </div>
</section>

<section class="panel">
    <div class="panel-head"><div><small>COMMAND ARCHIVE</small><h2>Recent Outgoing After-Action Reports</h2></div><span class="bolts">•••</span></div>
    <div class="panel-body">
        <?php if($recentOutgoing):?><div class="fob-command-panel-scroll"><table class="fob-command-table"><thead><tr><th>Raid</th><th>Target</th><th>Operation</th><th>Result</th><th>Transfer</th><th>Report</th></tr></thead><tbody>
        <?php foreach($recentOutgoing as $r):$as=json_decode((string)$r['attacker_snapshot_json'],true)?:[];$mode=(string)($as['resolution']['mode']??'direct');$modeLabel=['direct'=>'Immediate','staff_dispatch'=>'Staff Strike','autonomous'=>'Autonomous','retaliation'=>'Retaliation'][$mode]??ucwords(str_replace('_',' ',$mode));$tr=json_decode((string)$r['transfer_json'],true)?:[];?><tr><td>#<?=intval($r['id'])?></td><td><?=msw_e((string)$r['defender'])?> · <?=msw_e((string)$r['defender_grade'])?></td><td><?=msw_e($modeLabel)?></td><td><span class="badge"><?=msw_e(strtoupper(str_replace('_',' ',(string)$r['result'])))?></span></td><td><?=number_format((int)array_sum(array_map('intval',$tr)))?></td><td><a class="btn small secondary" href="<?=msw_e(msw_url('fob_result.php?id='.(int)$r['id']))?>">AAR</a></td></tr><?php endforeach;?></tbody></table></div>
        <?php else:?><div class="empty">No outgoing FOB raid reports have been recorded yet.</div><?php endif;?>
    </div>
</section>
<?php msw_footer(); ?>
