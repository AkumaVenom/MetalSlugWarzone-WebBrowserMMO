<?php
declare(strict_types=1);
require __DIR__.'/includes/battle_engine.php';
require_once __DIR__.'/includes/ui.php';
$u=msw_require_user();$uid=(int)$u['id'];$catalog=msw_mission_catalog();
if(msw_is_post()){
    msw_verify_post();$key=is_string($_POST['mission']??null)?$_POST['mission']:'';
    if(isset($catalog[$key])){
        $id=msw_start_encounter($uid,$catalog[$key]['enemy'],'mission',$key);
        msw_redirect('battle.php?id='.$id);
    }
    msw_flash('Mission unavailable.','error');msw_redirect('missions.php');
}
$progress=[];
foreach(msw_all('SELECT * FROM mission_progress WHERE user_id=?','i',[$uid]) as $p)$progress[$p['mission_key']]=$p;
$level=max(1,(int)$u['level']);
msw_header('Operations','missions.php');msw_alert(msw_flash());msw_resource_strip($uid);
?>
<section class="hero"><div class="eyebrow">TACTICAL OPERATIONS</div><h1>COMBAT <span>MISSIONS</span></h1><p>Repeatable combat missions reward the materials you need to expand Mother Base, manufacture Fulton gear and fund major projects. Your combat roster is restored after each finished battle.</p></section>
<div class="actions" style="margin:14px 0 18px"><a class="btn secondary" href="<?=msw_e(msw_url('sidequests.php'))?>">Field Contracts</a><a class="btn secondary" href="<?=msw_e(msw_url('commanders.php'))?>">Rival Commanders</a></div>
<div class="grid g2" style="margin-top:18px">
<?php foreach($catalog as $key=>$m):$p=$progress[$key]??null;?>
<article class="panel" data-operation="<?=msw_e($key)?>"><div class="panel-body">
<span class="badge">Threat <?=intval($m['level'])?></span>
<h3><?=msw_e($m['name'])?></h3><p><?=msw_e($m['brief'])?></p>
<?php if(isset($m['map_key'])):
    $window=msw_enemy_level_window($level,(int)$m['level'],'mission');
    $readiness=msw_warzone_readiness_pressure($level,(int)$m['level'],'mission');
    $minimum=max(1,$level+(int)$window['min_offset']);$maximum=max(1,$level+(int)$window['max_offset']);
?>
<p><strong>Enemy Lv <?=$minimum?>–<?=$maximum?></strong><br><small>Recommended Commander Lv <?=intval($readiness['recommended_level'])?></small></p>
<?php endif;?>
<p><small>REWARD · <?php foreach($m['reward'] as $rk=>$rv):?><?=msw_e(strtoupper(str_replace('_',' ',$rk)))?> <?=number_format((int)$rv)?> &nbsp; <?php endforeach;?></small></p>
<p><small>COMMANDER XP +<?=number_format((int)($m['commander_xp']??180))?></small></p>
<p>Clears: <b><?=intval($p['clears']??0)?></b></p>
<form method="post"><?=msw_csrf_field()?><input type="hidden" name="mission" value="<?=msw_e($key)?>"><button type="submit">Start Mission</button></form>
</div></article>
<?php endforeach;?></div>
<?php msw_footer();?>
