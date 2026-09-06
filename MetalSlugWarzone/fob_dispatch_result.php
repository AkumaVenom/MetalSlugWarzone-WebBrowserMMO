<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';

$user=msw_require_user();
$uid=(int)$user['id'];
$id=(int)($_GET['id']??0);
if($id<1){http_response_code(404);exit('That FOB strike report is unavailable.');}

msw_fob_resolve_due_dispatches($uid,20);
$dispatch=msw_one('SELECT d.*,u.username defender FROM fob_strike_dispatches d JOIN users u ON u.id=d.defender_user_id WHERE d.id=? AND d.attacker_user_id=? LIMIT 1','ii',[$id,$uid]);
if(!$dispatch){http_response_code(404);exit('That FOB strike report is unavailable.');}

if((string)$dispatch['result']!=='pending'&&!empty($dispatch['raid_id'])){
    msw_redirect('fob_result.php?id='.(int)$dispatch['raid_id'].'&dispatch='.$id);
}

msw_header('FOB Strike Battle Result','fob.php');
msw_alert(msw_flash());

if((string)$dispatch['result']==='pending'){
    ?>
    <section class="hero"><div class="eyebrow">FOB STAFF STRIKE · OPERATION #<?=$id?></div><h1>STRIKE TEAM <span>IN TRANSIT</span></h1><p>Your selected staff are still moving against <?=msw_e((string)$dispatch['defender'])?>. The battle replay will begin as soon as your strike team reaches the target.</p></section>
    <section class="panel" style="margin-top:18px"><div class="panel-head"><div><small>TACTICAL LINK</small><h2>Strike Team In Transit</h2></div><span class="bolts">•••</span></div><div class="panel-body"><div class="timer" data-countdown="<?=msw_e(date(DATE_ATOM,strtotime((string)$dispatch['finish_at'])))?>" data-auto-result-url="<?=msw_e(msw_url('fob_dispatch_result.php?id='.$id))?>">--:--:--</div><p class="muted-copy">You can leave this page. The strike team keeps moving while you're away.</p><div class="actions"><a class="btn secondary" href="<?=msw_e(msw_url('fob_dispatch.php'))?>">Return to Strike Operations</a></div></div></section>
    <?php
    msw_footer();
    exit;
}

$unitIds=array_values(array_unique(array_filter(array_map('intval',json_decode((string)$dispatch['unit_ids_json'],true)?:[]),fn($v)=>$v>0)));
$units=[];
if($unitIds){
    $in=implode(',',array_map('intval',$unitIds));
    $units=msw_all("SELECT id,source_enemy_key,callsign,unit_class,level,hp,max_hp,attack,defense,speed,combat,security,grade FROM units WHERE owner_user_id=? AND id IN ({$in}) ORDER BY combat DESC,level DESC,id ASC",'i',[$uid]);
}
$model=msw_auto_battle_model_fob_abort($dispatch,$units,(string)$dispatch['defender']);
msw_render_auto_battle($model);
?>
<section class="hero auto-battle-aar-hero"><div class="eyebrow">AFTER ACTION REPORT · STAFF STRIKE #<?=$id?></div><h1>OPERATION <span>ABORTED</span></h1><p><?=msw_e((string)$dispatch['defender'])?> entered recovery protection before your staff arrived. No FOB battle took place. Your strike team withdrew safely and returned to Mother Base.</p></section>
<div class="grid g2" style="margin-top:18px">
<?php msw_panel('Strike Team Status','FOB OPERATION');?><p>Target: <b><?=msw_e((string)$dispatch['defender'])?></b></p><p>Strike Team Power: <b><?=number_format((int)$dispatch['snapshot_power'])?></b></p><p>Strike Chance: <b><?=number_format((float)$dispatch['success_chance']*100,1)?>%</b></p><p>Status: <span class="badge"><?=msw_e(msw_fob_result_label((string)$dispatch['result']))?></span></p><?php msw_panel_end();?>
<?php msw_panel('Outcome','NO COMBAT CONTACT');?><p>No resources were captured or transferred because the defender was protected on arrival.</p><p class="muted-copy">Your selected staff have returned and are available for new assignments.</p><?php msw_panel_end();?>
</div>
<div class="actions"><a class="btn" href="<?=msw_e(msw_url('fob_dispatch.php'))?>">Strike Operations</a><a class="btn secondary" href="<?=msw_e(msw_url('fob.php'))?>">Command Centre</a><a class="btn secondary" href="<?=msw_e(msw_url('fob_world.php?world='.(int)$dispatch['world_id']))?>">Battle Shard</a></div>
<?php msw_footer(); ?>
