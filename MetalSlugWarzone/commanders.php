<?php
declare(strict_types=1);
require __DIR__.'/includes/battle_engine.php';
require_once __DIR__.'/includes/ui.php';
$user=msw_require_user();
$uid=(int)$user['id'];
$catalog=msw_trainer_catalog();

if(msw_is_post()){
    msw_verify_post();
    $key=(string)($_POST['commander']??'');
    if(isset($catalog[$key])){
        $id=msw_start_encounter($uid,$catalog[$key]['enemy'],'trainer',$key);
        msw_redirect('battle.php?id='.$id);
    }
    msw_flash('Rival commander unavailable.','error');
    msw_redirect('commanders.php');
}

$progress=[];
foreach(msw_all("SELECT mission_key,clears,last_cleared_at FROM mission_progress WHERE user_id=? AND mission_key LIKE 'trainer:%'",'i',[$uid]) as $row){
    $progress[substr((string)$row['mission_key'],8)]=$row;
}
msw_header('Rival Commanders','missions.php');
msw_alert(msw_flash());
msw_resource_strip($uid);
?>
<section class="hero"><div class="eyebrow">RIVAL COMMAND DUELS</div><h1>COMMANDER <span>BATTLES</span></h1><p>Trainer-style NPC command battles provide fixed named rivals and repeatable tactical tests. Their deployed units cannot be Fulton extracted during the duel, keeping command battles distinct from wild recoverable contacts.</p></section>
<div class="grid g3" style="margin-top:18px">
<?php foreach($catalog as $key=>$rival): $enemy=msw_enemy_catalog()[$rival['enemy']];$done=$progress[$key]??null; ?>
<article class="panel"><div class="panel-body"><img class="rival-sprite" src="<?=msw_e(msw_url($enemy['sprite']))?>" alt=""><span class="badge">Threat <?=$rival['level']?></span><h3><?=msw_e($rival['name'])?></h3><small><?=msw_e($rival['title'])?></small><p><?=msw_e($rival['brief'])?></p><p>Victories: <b><?=intval($done['clears']??0)?></b></p><form method="post"><?=msw_csrf_field()?><input type="hidden" name="commander" value="<?=msw_e($key)?>"><button>Issue Challenge</button></form></div></article>
<?php endforeach; ?>
</div>
<?php msw_footer(); ?>
