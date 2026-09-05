<?php
declare(strict_types=1);
require __DIR__.'/includes/battle_engine.php';
require_once __DIR__.'/includes/ui.php';
$user=msw_require_user();
$uid=(int)$user['id'];
$catalog=msw_sidequest_catalog();

if(msw_is_post()){
    msw_verify_post();
    $key=(string)($_POST['sidequest']??'');
    if(isset($catalog[$key])){
        $id=msw_start_encounter($uid,$catalog[$key]['enemy'],'sidequest',$key);
        msw_redirect('battle.php?id='.$id);
    }
    msw_flash('Field contract unavailable.','error');
    msw_redirect('sidequests.php');
}

$progress=[];
foreach(msw_all("SELECT mission_key,clears,last_cleared_at FROM mission_progress WHERE user_id=? AND mission_key LIKE 'sidequest:%'",'i',[$uid]) as $row){
    $progress[substr((string)$row['mission_key'],10)]=$row;
}
msw_header('Field Contracts','missions.php');
msw_alert(msw_flash());
msw_resource_strip($uid);
?>
<section class="hero"><div class="eyebrow">OPTIONAL FIELD CONTRACTS</div><h1>SIDE <span>OPERATIONS</span></h1><p>Short repeatable contracts layer extra objectives onto the warzone loop. These engagements use the same server-owned combat and Fulton systems, while victories can return resources and recovery equipment.</p></section>
<div class="grid g3" style="margin-top:18px">
<?php foreach($catalog as $key=>$contract): $done=$progress[$key]??null; ?>
<article class="panel"><div class="panel-body"><span class="badge">Threat <?=$contract['level']?></span><h3><?=msw_e($contract['name'])?></h3><p><?=msw_e($contract['brief'])?></p><p><small>Clears <?=intval($done['clears']??0)?><?php if(!empty($done['last_cleared_at'])): ?> · Last <?=msw_e($done['last_cleared_at'])?><?php endif; ?></small></p><form method="post"><?=msw_csrf_field()?><input type="hidden" name="sidequest" value="<?=msw_e($key)?>"><button>Accept Contract</button></form></div></article>
<?php endforeach; ?>
</div>
<?php msw_footer(); ?>
