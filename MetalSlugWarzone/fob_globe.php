<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();$uid=(int)$user['id'];$membership=msw_fob_membership($uid);$biomes=msw_fob_biome_catalog();$recentDefenses=$membership?msw_all('SELECT r.id,r.result,r.created_at,a.username attacker,a.is_bot FROM fob_raids r JOIN users a ON a.id=r.attacker_user_id WHERE r.defender_user_id=? ORDER BY r.id DESC LIMIT 5','i',[$uid]):[];
if(!$membership&&msw_is_post()){
    msw_verify_post();$biome=(string)($_POST['biome']??'');
    if(!isset($biomes[$biome])){msw_flash('Select a valid FOB continent type.','error');msw_redirect('fob_globe.php');}
    $_SESSION['fob_pending_biome']=$biome;msw_redirect('fob_skin.php');
}
msw_header($membership?'Global Invasion Network':'Global FOB Deployment','fob.php');msw_alert(msw_flash());
?>
<section class="hero"><div class="eyebrow"><?=$membership?'GLOBAL INVASION NETWORK · CROSS-SHARD THEATRE SELECT':'GLOBAL FOB DEPLOYMENT · STEP 1 OF 2'?></div><h1><?=$membership?'SELECT AN <span>INVASION THEATRE</span>':'SELECT YOUR <span>CONTINENT TYPE</span>'?></h1><p><?=$membership?'Your own FOB remains permanently anchored to its home shard, but invasion authority is now global. Select any continent on the same Earth command globe, then choose a populated shard and a valid rival FOB.':'Your first FOB deployment is permanent to one overview-world instance. Choose the environmental theatre first; the next screen will only expose Mother Base skins coherent with that biome.'?></p></section>
<section class="fob-globe-shell" style="margin-top:18px">
    <div class="fob-globe-stage"><img src="<?=msw_e(msw_url(msw_fob_globe_image()))?>" width="1254" height="1254" alt="Global Earth FOB overview">
        <?php $pins=msw_fob_globe_hotspots();foreach($pins as $key=>$pos):$entry=$biomes[$key];?>
        <?php if($membership):?><form method="get" action="<?=msw_e(msw_url('fob_shards.php'))?>" class="fob-globe-pin pin-<?=msw_e($key)?>" style="left:<?=$pos[0]?>%;top:<?=$pos[1]?>%"><input type="hidden" name="biome" value="<?=msw_e($key)?>"><button type="submit" aria-label="Browse <?=msw_e($entry['name'])?> invasion shards"><b><?=msw_e($entry['globe_label'])?></b><small><?=msw_e($entry['theatre'])?></small></button></form>
        <?php else:?><form method="post" class="fob-globe-pin pin-<?=msw_e($key)?>" style="left:<?=$pos[0]?>%;top:<?=$pos[1]?>%"><?=msw_csrf_field()?><input type="hidden" name="biome" value="<?=msw_e($key)?>"><button type="submit" aria-label="Deploy to <?=msw_e($entry['name'])?> FOB theatre"><b><?=msw_e($entry['globe_label'])?></b><small><?=msw_e($entry['theatre'])?></small></button></form><?php endif;?>
        <?php endforeach;?>
    </div>
    <aside class="fob-globe-brief"><div class="eyebrow"><?=$membership?'GLOBAL INVASION RULES':'WORLD SHARD RULES'?></div><h2><?=$membership?'Cross-Shard Operations':'Persistent Placement'?></h2>
        <?php if($membership):?><p>Your home FOB never migrates. The globe is now a theatre browser: choose a continent, select any populated shard with rivals, then launch immediate or staff-dispatch invasions under the same server-authoritative protection and resource rules.</p><div class="fob-rule-list"><span><b>01</b> Choose continent</span><span><b>02</b> Choose populated shard</span><span><b>03</b> Select valid rival FOB</span><span><b>04</b> Invade across shard boundaries</span></div><div class="actions"><a class="btn secondary" href="<?=msw_e(msw_url('fob_world.php'))?>">Return to Home Shard</a></div>
        <?php else:?><p>Each biome owns an unlimited sequence of overview-world instances. A world contains up to <?=msw_fob_world_capacity()?> collision-free FOB slots; when all slots are occupied, the server creates the next instance automatically.</p><div class="fob-rule-list"><span><b>01</b> Choose biome</span><span><b>02</b> Choose coherent base skin</span><span><b>03</b> Receive permanent world + slot</span><span><b>04</b> Unlock global invasion access</span></div><p class="muted-copy">World placement cannot be manually rerolled or switched after deployment. Invasion access does not alter your persistent home placement.</p><?php endif;?>
    </aside>
</section>
<?php if($membership&&$recentDefenses):?><section class="panel" style="margin-top:18px"><div class="panel-head"><div><small>RECENT DEFENSIVE CONTACTS</small><h2>Incoming FOB Activity</h2></div><span class="bolts">•••</span></div><div class="panel-body"><table><thead><tr><th>Attacker</th><th>Type</th><th>Result</th><th>Report</th></tr></thead><tbody><?php foreach($recentDefenses as $raid):?><tr><td><?=msw_e($raid['attacker'])?></td><td><?=(int)$raid['is_bot']===1?'<span class="ai-mark">AI COMPETITOR</span>':'PLAYER'?></td><td><?=msw_e(strtoupper(str_replace('_',' ',(string)$raid['result'])))?></td><td><a class="btn small secondary" href="<?=msw_e(msw_url('fob_result.php?id='.(int)$raid['id']))?>">Open AAR</a></td></tr><?php endforeach;?></tbody></table></div></section><?php endif;?>
<?php msw_footer(); ?>
