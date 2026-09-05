<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();$uid=(int)$user['id'];$membership=msw_fob_membership($uid);if(!$membership)msw_redirect('fob_globe.php');
$biomeKey=(string)($_GET['biome']??'');$biomes=msw_fob_biome_catalog();if(!isset($biomes[$biomeKey])){msw_flash('Select a valid invasion theatre.','warning');msw_redirect('fob_globe.php');}
$worlds=msw_fob_world_directory($uid,$biomeKey);$biome=$biomes[$biomeKey];
msw_header('FOB Shard Directory','fob.php');msw_alert(msw_flash());
?>
<section class="hero"><div class="eyebrow">GLOBAL INVASION NETWORK · <?=msw_e(strtoupper($biome['name']))?> THEATRE</div><h1>SELECT AN <span>INVASION SHARD</span></h1><p>These are real populated <?=msw_e($biome['name'])?> overview-world instances. Enter any shard to survey its commanders; protected FOBs remain visible but cannot be attacked until their protection expires.</p></section>
<div class="actions" style="margin-top:14px"><a class="btn" href="<?=msw_e(msw_url('fob.php'))?>">Command Centre</a><a class="btn secondary" href="<?=msw_e(msw_url('fob_globe.php'))?>">← Global Earth</a><a class="btn secondary" href="<?=msw_e(msw_url('fob_world.php'))?>">Home Shard</a></div>
<section class="panel" style="margin-top:18px"><div class="panel-head"><div><small><?=count($worlds)?> POPULATED INSTANCES</small><h2><?=msw_e($biome['theatre'])?> Shard Directory</h2></div><span class="bolts">•••</span></div><div class="panel-body"><div class="grid g3 fob-shard-grid">
<?php foreach($worlds as $world):$isHome=(int)$world['id']===(int)$membership['world_id'];$rivals=max(0,(int)$world['population']-($isHome?1:0));?><article class="fob-shard-card"><div class="actions"><span class="badge"><?=msw_e(msw_fob_world_name($world))?></span><?php if($isHome):?><span class="grade">HOME</span><?php endif;?></div><h3><?=number_format((int)$world['population'])?> / <?=number_format((int)$world['capacity'])?> FOBs</h3><p><small>HUMAN <?=number_format((int)$world['humans'])?> · AI <?=number_format((int)$world['ai'])?> · OPEN TARGETS <?=number_format((int)$world['open_targets'])?></small></p><p class="muted-copy"><?=number_format($rivals)?> rival commander<?=($rivals===1?'':'s')?> currently occupy this shard.</p><a class="btn <?=$rivals>0?'':'secondary'?>" href="<?=msw_e(msw_url('fob_world.php?world='.(int)$world['id']))?>"><?=$rivals>0?'Survey Shard':'View Shard'?></a></article><?php endforeach;?>
</div><?php if(!$worlds):?><div class="empty">No populated shards currently exist in this theatre.</div><?php endif;?></div></section>
<?php msw_footer(); ?>
