<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();$uid=(int)$user['id'];
if(msw_fob_membership($uid)) msw_redirect('fob_world.php');
$biomes=msw_fob_biome_catalog();$allSkins=msw_fob_skin_catalog();$bases=msw_mother_base_catalog();
$biomeKey=(string)($_SESSION['fob_pending_biome']??'');
if(!isset($biomes[$biomeKey])) msw_redirect('fob_globe.php');
$biome=$biomes[$biomeKey];
if(msw_is_post()){
    msw_verify_post();$skin=(string)($_POST['skin']??'');
    try{
        $membership=msw_fob_assign_user($uid,$biomeKey,$skin);
        unset($_SESSION['fob_pending_biome']);
        $world=msw_fob_world_row((int)$membership['world_id']);$base=$bases[$skin]??null;
        msw_console_event_for_user($uid,'FOB','DEPLOY','Global FOB deployed to '.($world?msw_fob_world_name($world):'overview world').'.',['world_id'=>(int)$membership['world_id'],'biome'=>$biomeKey,'skin'=>$skin,'slot'=>(int)$membership['slot_index']]);
        msw_flash('FOB deployment locked. Your Mother Base and overview icon are now synchronized to this theatre.','success');
        msw_redirect('fob_world.php');
    }catch(Throwable $e){msw_flash($e->getMessage(),'error');msw_redirect('fob_skin.php');}
}
msw_header('Select FOB Skin','fob.php');msw_alert(msw_flash());
?>
<section class="hero"><div class="eyebrow">GLOBAL FOB DEPLOYMENT · STEP 2 OF 2</div><h1><?=msw_e(strtoupper($biome['name']))?> <span>BASE SKIN</span></h1><p><?=msw_e($biome['theatre'])?> selected. Only skins designed for this environment are eligible, so your global overview icon and physical Mother Base remain visually coherent.</p></section>
<div class="actions" style="margin-top:14px"><a class="btn secondary" href="<?=msw_e(msw_url('fob_globe.php'))?>">← Change Continent</a><span class="badge"><?=msw_e($biome['climate'])?></span></div>
<div class="fob-skin-grid" style="margin-top:18px">
<?php foreach($biome['skins'] as $skinKey):$skin=$allSkins[$skinKey];$base=$bases[$skinKey];?>
<form method="post" class="fob-skin-card">
    <?=msw_csrf_field()?><input type="hidden" name="skin" value="<?=msw_e($skinKey)?>">
    <div class="fob-skin-icon"><img src="<?=msw_e(msw_url($skin['icon']))?>" width="256" height="171" alt=""></div>
    <div class="fob-skin-preview"><img src="<?=msw_e(msw_url($base['image']))?>" alt=""></div>
    <div class="fob-skin-copy"><div class="eyebrow"><?=msw_e($base['type'])?></div><h2><?=msw_e($base['name'])?></h2><p><?=msw_e($base['climate'])?> · this choice becomes the persistent overview sprite and your physical Mother Base map.</p><button>Deploy This FOB</button></div>
</form>
<?php endforeach;?>
</div>
<?php msw_footer(); ?>
