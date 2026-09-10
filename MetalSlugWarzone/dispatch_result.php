<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';

$user=msw_require_user();
$uid=(int)$user['id'];
$id=(int)($_GET['id']??0);
if($id<1){http_response_code(404);exit('That dispatch report is unavailable.');}

// Result authority remains server-side. Visiting the playback route settles only
// missions whose persisted MySQL finish time is already due.
msw_dispatch_resolve_due_for_user($uid,50,false);
$run=msw_one('SELECT * FROM dispatch_missions WHERE id=? AND user_id=? LIMIT 1','ii',[$id,$uid]);
if(!$run){http_response_code(404);exit('That dispatch report is unavailable.');}
$catalog=msw_dispatch_catalog();
$definition=$catalog[(string)$run['mission_key']]??[
    'name'=>ucwords(str_replace('_',' ',(string)$run['mission_key'])),
    'difficulty'=>max(1,(int)$run['snapshot_power']),
    'slots'=>max(1,count(json_decode((string)$run['unit_ids_json'],true)?:[])),
    'reward'=>[],
];

msw_header('Dispatch Battle Result','dispatch.php');
msw_alert(msw_flash());

if((string)$run['result']==='pending'){
    ?>
    <section class="hero"><div class="eyebrow">COMBAT UNIT · MISSION #<?=$id?></div><h1>MISSION <span>IN PROGRESS</span></h1><p><?=msw_e((string)$definition['name'])?> is still active. The battle replay will begin as soon as the mission timer reaches zero.</p></section>
    <section class="panel" style="margin-top:18px"><div class="panel-head"><div><small>TACTICAL LINK</small><h2>Mission In Progress</h2></div><span class="bolts">•••</span></div><div class="panel-body"><div class="timer" data-countdown="<?=msw_e(date(DATE_ATOM,strtotime((string)$run['finish_at'])))?>" data-auto-result-url="<?=msw_e(msw_url('dispatch_result.php?id='.$id))?>">--:--:--</div><p class="muted-copy">You can leave this page. Your team keeps fighting while you're away.</p><div class="actions"><a class="btn secondary" href="<?=msw_e(msw_url('dispatch.php'))?>">Return to Dispatch</a></div></div></section>
    <?php
    msw_footer();
    exit;
}

$ids=array_values(array_unique(array_filter(array_map('intval',json_decode((string)$run['unit_ids_json'],true)?:[]),fn($v)=>$v>0)));
$units=[];
if($ids){
    $in=implode(',',array_map('intval',$ids));
    $units=msw_all("SELECT id,source_enemy_key,callsign,unit_class,level,hp,max_hp,attack,defense,speed,combat,security,grade FROM units WHERE owner_user_id=? AND id IN ({$in}) ORDER BY combat DESC,level DESC,id ASC",'i',[$uid]);
}
$model=msw_auto_battle_model_dispatch($run,$units,$definition);
msw_render_auto_battle($model);

$reward=json_decode((string)($run['reward_json']??''),true)?:[];
$resultLabel=msw_dispatch_status_label((string)$run['result']);
?>
<section class="hero auto-battle-aar-hero"><div class="eyebrow">AFTER ACTION REPORT · DISPATCH #<?=$id?></div><h1><?=msw_e(strtoupper($resultLabel))?></h1><p><?=msw_e((string)$definition['name'])?> is complete. The battle above shows how the mission played out, and your rewards are listed below.</p></section>
<div class="grid g2" style="margin-top:18px">
<?php msw_panel('Mission Result','COMBAT UNIT'); ?>
<p>Mission: <b><?=msw_e((string)$definition['name'])?></b></p>
<p>Team Power: <b><?=number_format((int)$run['snapshot_power'])?></b></p>
<p>Mission Chance: <b><?=number_format((float)$run['success_chance']*100,1)?>%</b></p>
<p>Status: <span class="badge"><?=msw_e($resultLabel)?></span></p>
<?php $reportMap=msw_map_catalog()[(string)($definition['map_key']??'')]??null;if($reportMap):?><p>Warzone: <b><?=msw_e($reportMap['name'])?></b> · Threat <?=intval($reportMap['level'])?></p><?php endif;?>
<p class="muted-copy">Started <?=msw_e((string)$run['started_at'])?> · Resolved <?=msw_e((string)($run['resolved_at']??'--'))?></p>
<?php msw_panel_end(); ?>
<?php msw_panel('Mission Rewards','AFTER ACTION'); ?>
<p>Staff XP: <b>+<?=number_format(msw_dispatch_staff_xp($definition,(string)$run['result']==='success'))?></b> per assigned unit.</p>
<?php if($reward):?><table><tbody><?php foreach($reward as $key=>$amount):?><tr><td><?=msw_e(ucwords(str_replace('_',' ',(string)$key)))?></td><td>+<?=number_format((int)$amount)?></td></tr><?php endforeach;?></tbody></table><?php else:?><div class="empty">No rewards were earned on this mission.</div><?php endif;?>
<?php msw_panel_end(); ?>
</div>
<div class="actions"><a class="btn" href="<?=msw_e(msw_url('dispatch.php'))?>">Return to Dispatch</a><a class="btn secondary" href="<?=msw_e(msw_url('base.php'))?>">Mother Base</a></div>
<?php msw_footer(); ?>
