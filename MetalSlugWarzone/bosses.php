<?php
declare(strict_types=1);
require __DIR__.'/includes/battle_engine.php';
require_once __DIR__.'/includes/ui.php';
$u=msw_require_user();
$uid=(int)$u['id'];
if(msw_is_post()){
    msw_verify_post();
    $key=(string)($_POST['boss']??'');
    $cat=msw_boss_catalog();
    if(isset($cat[$key])){
        $id=msw_start_encounter($uid,$cat[$key]['enemy'],'boss',$key);
        msw_redirect('battle.php?id='.$id);
    }
    msw_redirect('bosses.php');
}
msw_header('Boss Operations','bosses.php');
msw_resource_strip($uid);
?>
<section class="hero"><div class="eyebrow">HIGH-VALUE TARGET NETWORK</div><h1>BOSS <span>OPERATIONS</span></h1><p>Dedicated high-threat engagements against signature war machines and organisms. Boss targets cannot be Fulton recovered; victory yields major base resources and persistent Commander XP.</p></section>
<div class="grid g2 boss-grid" style="margin-top:18px">
<?php foreach(msw_boss_catalog() as $key=>$b): $e=msw_enemy_catalog()[$b['enemy']]; ?>
<article class="panel boss-card">
    <div class="panel-body boss-card-body">
        <div class="boss-art-frame"><img class="boss-art" src="<?=msw_e(msw_url($e['sprite']))?>" alt="<?=msw_e($b['name'])?>"></div>
        <div class="boss-card-copy"><span class="grade"><?=msw_e($b['threat'])?></span><h3><?=msw_e($b['name'])?></h3><p><?=msw_e($b['brief'])?></p><form method="post"><?=msw_csrf_field()?><input type="hidden" name="boss" value="<?=msw_e($key)?>"><button>Engage Boss</button></form></div>
    </div>
</article>
<?php endforeach; ?>
</div>
<?php msw_footer(); ?>
