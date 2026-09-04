<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();$uid=(int)$user['id'];
if(msw_fob_membership($uid)) msw_redirect('fob_world.php');
$biomes=msw_fob_biome_catalog();
if(msw_is_post()){
    msw_verify_post();
    $biome=(string)($_POST['biome']??'');
    if(!isset($biomes[$biome])){msw_flash('Select a valid FOB continent type.','error');msw_redirect('fob_globe.php');}
    $_SESSION['fob_pending_biome']=$biome;
    msw_redirect('fob_skin.php');
}
msw_header('Global FOB Deployment','fob.php');msw_alert(msw_flash());
?>
<section class="hero"><div class="eyebrow">GLOBAL FOB DEPLOYMENT · STEP 1 OF 2</div><h1>SELECT YOUR <span>CONTINENT TYPE</span></h1><p>Your first FOB deployment is permanent to one overview-world instance. Choose the environmental theatre first; the next screen will only expose Mother Base skins coherent with that biome.</p></section>
<section class="fob-globe-shell" style="margin-top:18px">
    <div class="fob-globe-stage">
        <img src="<?=msw_e(msw_url(msw_fob_globe_image()))?>" width="1254" height="1254" alt="Global Earth FOB overview">
        <?php $pins=msw_fob_globe_hotspots(); foreach($pins as $key=>$pos):$entry=$biomes[$key];?>
        <form method="post" class="fob-globe-pin pin-<?=msw_e($key)?>" style="left:<?=$pos[0]?>%;top:<?=$pos[1]?>%">
            <?=msw_csrf_field()?><input type="hidden" name="biome" value="<?=msw_e($key)?>">
            <button type="submit" aria-label="Deploy to <?=msw_e($entry['name'])?> FOB theatre"><b><?=msw_e($entry['globe_label'])?></b><small><?=msw_e($entry['theatre'])?></small></button>
        </form>
        <?php endforeach;?>
    </div>
    <aside class="fob-globe-brief">
        <div class="eyebrow">WORLD SHARD RULES</div><h2>Persistent Placement</h2>
        <p>Each biome owns an unlimited sequence of overview-world instances. A world contains up to <?=msw_fob_world_capacity()?> collision-free FOB slots; when all slots are occupied, the server creates the next instance automatically.</p>
        <div class="fob-rule-list"><span><b>01</b> Choose biome</span><span><b>02</b> Choose coherent base skin</span><span><b>03</b> Receive permanent world + slot</span><span><b>04</b> Invade commanders in that same overview instance</span></div>
        <p class="muted-copy">World placement cannot be manually rerolled or switched after deployment. This prevents duplicate presence and keeps the persistent overview authoritative.</p>
    </aside>
</section>
<?php msw_footer(); ?>
