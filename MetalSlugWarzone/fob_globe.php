<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();$uid=(int)$user['id'];$membership=msw_fob_membership($uid);$biomes=msw_fob_biome_catalog();$recentDefenses=$membership?msw_all('SELECT r.id,r.result,r.created_at,a.username attacker,a.is_bot FROM fob_raids r JOIN users a ON a.id=r.attacker_user_id WHERE r.defender_user_id=? ORDER BY r.id DESC LIMIT 5','i',[$uid]):[];
if(!$membership&&msw_is_post()){
    msw_verify_post();$biome=(string)($_POST['biome']??'');
    if(!isset($biomes[$biome])){msw_flash('Choose one of the available FOB regions.','error');msw_redirect('fob_globe.php');}
    $_SESSION['fob_pending_biome']=$biome;msw_redirect('fob_skin.php');
}
msw_header($membership?'Global FOB Map':'Global FOB Deployment','fob.php');msw_alert(msw_flash());
?>
<section class="hero"><div class="eyebrow"><?=$membership?'GLOBAL INVASION MAP · CHOOSE A REGION':'GLOBAL FOB DEPLOYMENT · STEP 1 OF 2'?></div><h1><?=$membership?'SELECT AN <span>INVASION REGION</span>':'SELECT YOUR <span>FOB REGION</span>'?></h1><p><?=$membership?'Your home FOB stays where you deployed it, but you can attack worldwide. Choose a region, open a populated shard, then select a rival FOB to invade.':'Your first FOB deployment becomes your permanent home. Choose a region now, then pick a Mother Base style designed to match that environment.'?></p></section>
<section class="fob-globe-shell" style="margin-top:18px">
    <div class="fob-globe-stage"><img src="<?=msw_e(msw_url(msw_fob_globe_image()))?>" width="1254" height="1254" alt="Global FOB war map">
        <?php $pins=msw_fob_globe_hotspots();foreach($pins as $key=>$pos):$entry=$biomes[$key];?>
        <?php if($membership):?><form method="get" action="<?=msw_e(msw_url('fob_shards.php'))?>" class="fob-globe-pin pin-<?=msw_e($key)?>" style="left:<?=$pos[0]?>%;top:<?=$pos[1]?>%"><input type="hidden" name="biome" value="<?=msw_e($key)?>"><button type="submit" aria-label="Browse <?=msw_e($entry['name'])?> invasion shards"><b><?=msw_e($entry['globe_label'])?></b><small><?=msw_e($entry['theatre'])?></small></button></form>
        <?php else:?><form method="post" class="fob-globe-pin pin-<?=msw_e($key)?>" style="left:<?=$pos[0]?>%;top:<?=$pos[1]?>%"><?=msw_csrf_field()?><input type="hidden" name="biome" value="<?=msw_e($key)?>"><button type="submit" aria-label="Deploy to <?=msw_e($entry['name'])?> FOB theatre"><b><?=msw_e($entry['globe_label'])?></b><small><?=msw_e($entry['theatre'])?></small></button></form><?php endif;?>
        <?php endforeach;?>
    </div>
    <aside class="fob-globe-brief"><div class="eyebrow"><?=$membership?'GLOBAL INVASION GUIDE':'HOME FOB GUIDE'?></div><h2><?=$membership?'Invade Other Shards':'Choose Your Home Region'?></h2>
        <?php if($membership):?><p>Your home FOB never moves. Use the globe to choose any region, enter a populated shard, then launch a direct invasion or send a staff strike team against an open rival.</p><div class="fob-rule-list"><span><b>01</b> Choose region</span><span><b>02</b> Choose populated shard</span><span><b>03</b> Select valid rival FOB</span><span><b>04</b> Launch the invasion</span></div><div class="actions"><a class="btn" href="<?=msw_e(msw_url('fob.php'))?>">Command Centre</a><a class="btn secondary" href="<?=msw_e(msw_url('fob_world.php'))?>">Home Shard</a></div>
        <?php else:?><p>Each region contains multiple FOB shards. A shard supports up to <?=msw_fob_world_capacity()?> FOB bases; when one fills up, a new shard opens automatically.</p><div class="fob-rule-list"><span><b>01</b> Choose region</span><span><b>02</b> Choose a matching Mother Base</span><span><b>03</b> Claim your permanent home slot</span><span><b>04</b> Unlock worldwide invasions</span></div><p class="muted-copy">Your home FOB location is permanent after deployment. You can invade other shards without moving your own base.</p><?php endif;?>
    </aside>
</section>
<?php if($membership&&$recentDefenses):?><section class="panel" style="margin-top:18px"><div class="panel-head"><div><small>RECENT FOB ATTACKS</small><h2>Recent Attacks on Your FOB</h2></div><span class="bolts">•••</span></div><div class="panel-body"><table><thead><tr><th>Attacker</th><th>Type</th><th>Result</th><th>Report</th></tr></thead><tbody><?php foreach($recentDefenses as $raid):?><tr><td><?=msw_e($raid['attacker'])?></td><td><?=(int)$raid['is_bot']===1?'<span class="ai-mark">AI COMPETITOR</span>':'PLAYER'?></td><td><?=msw_e(strtoupper(msw_fob_result_label((string)$raid['result'])))?></td><td><a class="btn small secondary" href="<?=msw_e(msw_url('fob_result.php?id='.(int)$raid['id']))?>">View Report</a></td></tr><?php endforeach;?></tbody></table></div></section><?php endif;?>
<?php msw_footer(); ?>
