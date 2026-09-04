<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$u=msw_require_user();$uid=(int)$u['id'];$id=(int)($_GET['id']??0);
$r=msw_one('SELECT r.*,a.username attacker,d.username defender FROM fob_raids r JOIN users a ON a.id=r.attacker_user_id JOIN users d ON d.id=r.defender_user_id WHERE r.id=? AND (r.attacker_user_id=? OR r.defender_user_id=?)','iii',[$id,$uid,$uid]);
if(!$r){http_response_code(404);exit('Raid report not found.');}
$tr=json_decode((string)$r['transfer_json'],true)?:[];$as=json_decode((string)$r['attacker_snapshot_json'],true)?:[];$ds=json_decode((string)$r['defender_snapshot_json'],true)?:[];
$mode=(string)($as['resolution']['mode']??'direct');$modeLabel=['direct'=>'Immediate Infiltration','staff_dispatch'=>'Staff Dispatch Invasion','autonomous'=>'Autonomous FOB Raid'][$mode]??ucwords(str_replace('_',' ',$mode));
$defenderId=(int)$r['defender_user_id'];$targetVisible=$uid===(int)$r['attacker_user_id']&&msw_fob_target_row($uid,$defenderId)!==null;
msw_header('FOB Raid Report','fob.php');
?>
<section class="hero"><div class="eyebrow">AFTER ACTION REPORT #<?=$id?> · <?=msw_e(strtoupper($modeLabel))?></div><h1><?=msw_e(strtoupper(str_replace('_',' ',$r['result'])))?></h1><p><?=msw_e($r['attacker'])?> infiltrated <?=msw_e($r['defender'])?>. Combat state was snapshotted under server authority and the resource ledger was settled atomically; the defender then entered post-invasion protection.</p></section>
<div class="grid g2" style="margin-top:18px">
<?php msw_panel('Resource Transfer','EXACT LEDGER DELTA');?><table><tbody><?php foreach($tr as $k=>$v):?><tr><td><?=msw_e(ucwords(str_replace('_',' ',$k)))?></td><td><?=number_format((int)$v)?></td></tr><?php endforeach;?></tbody></table><p class="muted-copy">Transfer occurs only on an attacker win. A repelled invasion still applies defender protection so the same base cannot be spammed repeatedly.</p><?php msw_panel_end();?>
<?php msw_panel('Defender Snapshot','SECURITY STATE');?><p>Base: <b><?=msw_e($ds['user']['base_grade']??'--')?></b> · Power <?=number_format((int)($ds['user']['base_power']??0))?></p><p>Security: <?=number_format((int)($ds['security']['score']??0))?> · <?=msw_e($ds['security']['grade']??'--')?></p><p>Combat defenders: <?=count($ds['team']??[])?></p><p>Mode: <b><?=msw_e($modeLabel)?></b></p><?php msw_panel_end();?>
</div>
<div class="actions"><a class="btn" href="<?=msw_e(msw_url('fob_world.php'))?>">Return to Overview World</a><?php if($targetVisible):?><a class="btn secondary" href="<?=msw_e(msw_url('fob_target.php?id='.$defenderId))?>">Target Command Screen</a><?php endif;?><a class="btn secondary" href="<?=msw_e(msw_url('fob_infiltration.php'))?>">Raid Ledger</a></div>
<?php msw_footer(); ?>
