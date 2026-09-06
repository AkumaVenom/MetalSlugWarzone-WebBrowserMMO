<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';

$user=msw_require_user();
$uid=(int)$user['id'];
msw_fob_resolve_due_dispatches($uid,12);
$membership=msw_fob_membership($uid);
if(!$membership)msw_redirect('fob_globe.php');

$rows=msw_fob_dispatches_for_user($uid,30);
$pending=0;$nextPendingId=0;$nextPendingAt=PHP_INT_MAX;
foreach($rows as $row){
    if((string)$row['result']!=='pending')continue;
    $pending++;
    $at=strtotime((string)$row['finish_at'])?:PHP_INT_MAX;
    if($at<$nextPendingAt){$nextPendingAt=$at;$nextPendingId=(int)$row['id'];}
}

msw_header('FOB Strike Operations','fob.php');
msw_alert(msw_flash());
?>
<section class="hero"><div class="eyebrow">FOB STAFF STRIKE OPERATIONS</div><h1>STRIKE <span>OPERATIONS</span></h1><p>FOB strike teams are separate from normal Dispatch Missions, but any staff you send are unavailable for both until they return. You can run several invasions at once, and every operation keeps progressing while you are away. Completed strikes now open the same automatic battle replay used by FOB raid reports.</p></section>
<div class="actions" style="margin-top:14px"><a class="btn" href="<?=msw_e(msw_url('fob.php'))?>">← Command Centre</a><a class="btn secondary" href="<?=msw_e(msw_url('fob_world.php'))?>">Home Shard</a><a class="btn secondary" href="<?=msw_e(msw_url('dispatch.php'))?>">Normal Dispatch Missions</a><span class="badge"><?=$pending?> ACTIVE</span></div>
<?php msw_panel('FOB Invasion Dispatches','STAFF STRIKE HISTORY');?>
<table><thead><tr><th>ID</th><th>Target</th><th>Team</th><th>Chance</th><th>Status</th><th>Resolution</th></tr></thead><tbody>
<?php foreach($rows as $row):$ids=json_decode((string)$row['unit_ids_json'],true)?:[];?>
<tr>
    <td>#<?=intval($row['id'])?></td>
    <td><a href="<?=msw_e(msw_url('fob_target.php?id='.(int)$row['defender_user_id'].'&world='.(int)$row['world_id']))?>"><?=msw_e($row['defender'])?></a></td>
    <td><?=count($ids)?> staff</td>
    <td><?=number_format((float)$row['success_chance']*100,1)?>%</td>
    <td><span class="badge"><?=msw_e(msw_fob_result_label((string)$row['result']))?></span></td>
    <td>
        <?php if((string)$row['result']==='pending'):?>
            <span data-countdown="<?=msw_e(date(DATE_ATOM,strtotime((string)$row['finish_at'])))?>"<?=(int)$row['id']===$nextPendingId?' data-auto-result-url="'.msw_e(msw_url('fob_dispatch_result.php?id='.(int)$row['id'])).'"':''?>>--:--:--</span>
        <?php else:?>
            <div class="dispatch-result-cell"><a class="btn small secondary" href="<?=msw_e(msw_url('fob_dispatch_result.php?id='.(int)$row['id']))?>"><?=empty($row['raid_id'])?'Mission Report':'Battle Replay'?></a><?php if(!empty($row['raid_id'])):?><a class="btn small secondary" href="<?=msw_e(msw_url('fob_result.php?id='.(int)$row['raid_id']))?>">AAR</a><?php endif;?></div>
        <?php endif;?>
    </td>
</tr>
<?php endforeach;?></tbody></table>
<?php if(!$rows):?><div class="empty">No FOB staff strike teams have been launched yet.</div><?php endif;?>
<?php msw_panel_end();?>
<?php msw_footer(); ?>
