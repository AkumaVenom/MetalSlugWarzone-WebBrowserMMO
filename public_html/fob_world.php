<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();$uid=(int)$user['id'];
msw_fob_resolve_due_dispatches($uid,8);msw_bot_simulation_pulse(null,10);
$membership=msw_fob_membership($uid);if(!$membership)msw_redirect('fob_globe.php');
$world=msw_fob_world_row((int)$membership['world_id']);if(!$world)throw new RuntimeException('FOB world unavailable.');
$biome=msw_fob_biome_catalog()[(string)$world['biome_key']]??null;if(!$biome)throw new RuntimeException('FOB biome unavailable.');
$skins=msw_fob_skin_catalog();$members=msw_fob_world_members((int)$world['id']);$population=count($members);$protected=!empty($user['fob_protection_until'])&&strtotime((string)$user['fob_protection_until'])>time();
$pending=msw_one("SELECT COUNT(*) c FROM fob_strike_dispatches WHERE attacker_user_id=? AND result='pending'",'i',[$uid]);
msw_header('FOB Overview World','fob.php');msw_alert(msw_flash());msw_resource_strip($uid);
?>
<section class="hero"><div class="eyebrow">GLOBAL FOB OVERVIEW · <?=msw_e(msw_fob_world_name($world))?></div><h1><?=msw_e(strtoupper($biome['theatre']))?> <span>COMMAND GRID</span></h1><p>Your FOB occupies one permanent collision-free slot in this world instance. Human and autonomous commanders shown here are real persisted members of the same shard; selecting an enemy FOB opens its invasion command screen.</p></section>
<div class="fob-world-toolbar">
    <span class="badge">INSTANCE <?=msw_e(msw_fob_world_name($world))?></span><span class="badge"><?=number_format($population)?> / <?=number_format((int)$world['capacity'])?> FOBs</span><span class="badge"><?=$protected?'YOUR FOB PROTECTED':'YOUR FOB OPEN'?></span><span class="badge"><?=intval($pending['c']??0)?> STAFF STRIKES ACTIVE</span>
    <a class="btn secondary" href="<?=msw_e(msw_url('fob_infiltration.php'))?>">Infiltration Network</a><a class="btn secondary" href="<?=msw_e(msw_url('fob_dispatch.php'))?>">Staff Strike Ledger</a>
</div>
<section class="fob-world-console">
    <div class="fob-world-console-head"><div><small>NATIVE 2000 × 2000 OVERVIEW</small><strong><?=msw_e($biome['name'])?> World · Shard <?=str_pad((string)(int)$world['shard_index'],3,'0',STR_PAD_LEFT)?></strong></div><div class="warzone-readout"><span>Persistent world slot <?=intval($membership['slot_index'])+1?></span><b>NO MANUAL SHARD SWITCHING</b></div></div>
    <div class="fob-world-viewport" data-fob-world-viewport data-own-x="<?=intval($membership['x'])?>" data-own-y="<?=intval($membership['y'])?>">
        <div class="fob-world-map" style="width:<?=intval($biome['w'])?>px;height:<?=intval($biome['h'])?>px">
            <img class="fob-overview-level" src="<?=msw_e(msw_url($biome['image']))?>" width="<?=intval($biome['w'])?>" height="<?=intval($biome['h'])?>" alt="<?=msw_e($biome['name'])?> FOB overview">
            <?php foreach($members as $member):$skin=$skins[$member['skin_key']]??null;if(!$skin)continue;$isMe=(int)$member['id']===$uid;$isProtected=!empty($member['fob_protection_until'])&&strtotime((string)$member['fob_protection_until'])>time();$tag=(int)$member['is_bot']===1?'AI':'PLAYER';?>
                <?php if($isMe):?><div class="fob-world-marker own" data-own-fob style="left:<?=intval($member['x'])?>px;top:<?=intval($member['y'])?>px">
                <?php else:?><a class="fob-world-marker enemy <?=$isProtected?'protected':''?>" href="<?=msw_e(msw_url('fob_target.php?id='.(int)$member['id']))?>" style="left:<?=intval($member['x'])?>px;top:<?=intval($member['y'])?>px" title="<?=msw_e($member['username'])?> · <?=msw_e($member['base_grade'])?> · PWR <?=number_format((int)$member['base_power'])?>">
                <?php endif;?>
                    <img src="<?=msw_e(msw_url($skin['icon']))?>" width="256" height="171" alt=""><span><b><?=msw_e($isMe?'YOUR FOB':$member['username'])?></b><small><?=$isMe?'COMMAND NODE':$tag.' · '.msw_e($member['base_grade']).($isProtected?' · PROTECTED':'')?></small></span>
                <?=$isMe?'</div>':'</a>'?>
            <?php endforeach;?>
        </div>
    </div>
    <div class="warzone-scroll-hint"><span>◀</span><span>Native overview · camera centers on your persistent FOB · scroll to survey rivals</span><span>▶</span></div>
</section>
<?php msw_footer(); ?>
