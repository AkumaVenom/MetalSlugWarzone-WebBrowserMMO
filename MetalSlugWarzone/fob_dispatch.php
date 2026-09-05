<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();$uid=(int)$user['id'];msw_fob_resolve_due_dispatches($uid,12);$membership=msw_fob_membership($uid);if(!$membership)msw_redirect('fob_globe.php');
$rows=msw_fob_dispatches_for_user($uid,30);$pending=0;foreach($rows as $r)if($r['result']==='pending')$pending++;
msw_header('FOB Staff Strike Ledger','fob.php');msw_alert(msw_flash());
?>
<section class="hero"><div class="eyebrow">FOB STAFF INVASION DISPATCH</div><h1>STRIKE <span>LEDGER</span></h1><p>FOB invasion dispatches are separate from standard Combat Unit Dispatch missions, but both reserve the same persistent staff rows. The Command Centre can now coordinate several concurrent cross-shard strike teams; each operation remains individually persisted and resolves exactly once from MySQL timestamps after browser or server restarts.</p></section>
<div class="actions" style="margin-top:14px"><a class="btn" href="<?=msw_e(msw_url('fob.php'))?>">← Command Centre</a><a class="btn secondary" href="<?=msw_e(msw_url('fob_world.php'))?>">Home Shard</a><a class="btn secondary" href="<?=msw_e(msw_url('dispatch.php'))?>">Standard Dispatch Missions</a><span class="badge"><?=$pending?> ACTIVE</span></div>
<?php msw_panel('FOB Invasion Dispatches','PERSISTENT STAFF MISSIONS');?><table><thead><tr><th>ID</th><th>Target</th><th>Team</th><th>Chance</th><th>Status</th><th>Resolution</th></tr></thead><tbody>
<?php foreach($rows as $row):$ids=json_decode((string)$row['unit_ids_json'],true)?:[];?>
<tr><td>#<?=intval($row['id'])?></td><td><a href="<?=msw_e(msw_url('fob_target.php?id='.(int)$row['defender_user_id'].'&world='.(int)$row['world_id']))?>"><?=msw_e($row['defender'])?></a></td><td><?=count($ids)?> staff</td><td><?=number_format((float)$row['success_chance']*100,1)?>%</td><td><span class="badge"><?=msw_e(str_replace('_',' ',$row['result']))?></span></td><td><?php if($row['result']==='pending'):?><span data-countdown="<?=msw_e(date(DATE_ATOM,strtotime((string)$row['finish_at'])))?>"></span><?php elseif(!empty($row['raid_id'])):?><a class="btn small secondary" href="<?=msw_e(msw_url('fob_result.php?id='.(int)$row['raid_id']))?>">Raid Report</a><?php else:?>Target protected at arrival<?php endif;?></td></tr>
<?php endforeach;?></tbody></table><?php if(!$rows):?><div class="empty">No FOB staff invasion dispatches have been launched.</div><?php endif;?><?php msw_panel_end();?></section>
<?php msw_footer(); ?>
