<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';

$me=msw_require_user();$uid=(int)$me['id'];
$activeBattle=msw_active_encounter($uid);
if($activeBattle) msw_redirect('battle.php?id='.(int)$activeBattle['id']);

$ownerId=max(1,(int)($_GET['owner']??$uid));
$owner=msw_one('SELECT id,username,character_key,mother_base_key,level,xp,command_rank,base_power,base_grade FROM users WHERE id=?','i',[$ownerId]);
if(!$owner){http_response_code(404);exit('Mother Base not found.');}
$ownerFob=msw_fob_membership($ownerId);if(!$ownerFob){if($ownerId===$uid)msw_redirect('fob_globe.php');http_response_code(404);exit('This commander has not deployed a global FOB yet.');}
$relation=msw_mb_access_relation($uid,$ownerId);
if($relation===null){http_response_code(403);exit('Mother Base access restricted to the owner, established friends, and Strike Force members.');}
$bases=msw_mother_base_catalog();
$baseKey=(string)$owner['mother_base_key'];
if(!isset($bases[$baseKey])) $baseKey='land_dirt';
$base=$bases[$baseKey];

$presence=msw_mb_presence_row($uid);
if($presence && (int)$presence['base_owner_user_id']===$ownerId && (string)$presence['base_key']===$baseKey){
    $x=(int)$presence['x'];$y=(int)$presence['y'];$facing=(string)$presence['facing'];
}else{
    [$x,$y]=msw_mb_spawn($baseKey);$facing='right';
}
[$x,$y]=msw_mb_safe_position($baseKey,$x,$y);
if(!in_array($facing,['up','down','left','right'],true)) $facing='right';
msw_mb_presence_touch($uid,$ownerId,$baseKey,$x,$y,$facing);
msw_mb_advance_staff($ownerId,$baseKey);
$staff=msw_mb_staff_state($ownerId,$baseKey);
$visitors=msw_mb_visitors($uid,$ownerId,$baseKey);

$chars=msw_character_catalog();$myChar=$chars[$me['character_key']]??reset($chars);
$spriteR=(string)($myChar['sprite_r']??$myChar['sprite']);$spriteL=(string)($myChar['sprite_l']??$spriteR);
$initialSprite=$facing==='left'?$spriteL:$spriteR;
$vehicleCount=0;foreach($staff as $s) if(!empty($s['vehicle'])) $vehicleCount++;
$personnelCount=count($staff)-$vehicleCount;
$relationLabel=['owner'=>'Your Mother Base','friend'=>'Friend Access','strike_force'=>'Strike Force Access'][$relation]??'Authorized Access';

msw_header($ownerId===$uid?'Your Mother Base':$owner['username'].' Mother Base','base.php');
?>
<div class="warzone-toolbar mother-base-toolbar">
    <a class="btn secondary" href="<?=msw_e(msw_url('base.php'))?>">← Base Management</a>
    <?php if($ownerId!==$uid):?><a class="btn secondary" href="<?=msw_e(msw_url('profile.php?id='.$ownerId))?>">Commander Profile</a><?php endif;?>
    <span class="badge live-dot">Shared Base Presence: Online</span>
    <span class="badge">Native <?=intval($base['w'])?> × <?=intval($base['h'])?></span>
    <span class="badge">Server Collision: Active</span>
</div>
<div class="warzone-layout mother-base-layout">
    <section class="warzone-console mother-base-console">
        <div class="warzone-console-head">
            <div><small><?=$ownerId===$uid?'ACTIVE MOTHER BASE':'AUTHORIZED BASE VISIT'?></small><strong><?=msw_e($base['name'])?></strong></div>
            <div class="warzone-readout"><span><?=msw_e($base['type'])?> · <?=msw_e($base['climate'])?></span><b><?=msw_e($owner['username'])?> · <?=msw_e($owner['base_grade'])?></b></div>
        </div>
        <div class="map-stage mother-base-stage"
             data-base-stage
             data-owner-id="<?=$ownerId?>"
             data-base-key="<?=msw_e($baseKey)?>"
             data-csrf="<?=msw_e(msw_csrf())?>"
             data-move-url="<?=msw_e(msw_url('mother_base_move.php'))?>"
             data-presence-url="<?=msw_e(msw_url('mother_base_presence.php?owner='.$ownerId))?>"
             data-move-interval="110"
             style="--map-native-w:<?=intval($base['w'])?>px;--map-native-h:<?=intval($base['h'])?>px">
            <div class="map-world mother-base-world" style="width:<?=intval($base['w'])?>px;height:<?=intval($base['h'])?>px">
                <img class="level" src="<?=msw_e(msw_url($base['image']))?>" width="<?=intval($base['w'])?>" height="<?=intval($base['h'])?>" alt="<?=msw_e($base['name'])?>">
                <?php foreach($staff as $entity):?>
                    <div class="base-entity <?=$entity['vehicle']?'base-vehicle':'base-staff'?>" data-base-npc="<?=$entity['id']?>" data-mobile="<?=$entity['mobile']?>" data-facing="<?=msw_e($entity['facing'])?>" style="left:<?=$entity['x']?>px;top:<?=$entity['y']?>px">
                        <img src="<?=msw_e($entity['sprite'])?>" alt="" loading="eager"><span><?=msw_e($entity['name'])?> · <?=msw_e($entity['grade'])?></span>
                    </div>
                <?php endforeach;?>
                <?php foreach($visitors as $visitor):$vc=$chars[$visitor['character_key']]??reset($chars);$vr=(string)($vc['sprite_r']??$vc['sprite']);$vl=(string)($vc['sprite_l']??$vr);$vs=(string)$visitor['facing']==='left'?$vl:$vr;?>
                    <div class="base-entity base-visitor" data-base-visitor="<?=$visitor['id']?>" data-facing="<?=msw_e($visitor['facing'])?>" data-sprite-left="<?=msw_e(msw_url($vl))?>" data-sprite-right="<?=msw_e(msw_url($vr))?>" data-mirror-left="<?=!empty($vc['mirror_left'])?'1':'0'?>" style="left:<?=$visitor['x']?>px;top:<?=$visitor['y']?>px"><img src="<?=msw_e(msw_url($vs))?>" alt=""><span><?=msw_e($visitor['username'])?> · <?=msw_e($visitor['base_grade'])?></span></div>
                <?php endforeach;?>
                <img class="map-avatar base-local-avatar" data-base-local-avatar data-facing="<?=msw_e($facing)?>" data-sprite-left="<?=msw_e(msw_url($spriteL))?>" data-sprite-right="<?=msw_e(msw_url($spriteR))?>" data-mirror-left="<?=!empty($myChar['mirror_left'])?'1':'0'?>" src="<?=msw_e(msw_url($initialSprite))?>" style="left:<?=$x?>px;top:<?=$y?>px" alt="you">
                <span class="map-label base-local-label" data-base-local-label style="left:<?=$x?>px;top:<?=$y?>px"><?=msw_e($me['username'])?> · YOU</span>
            </div>
        </div>
        <div class="warzone-scroll-hint"><span>◀</span><span>Native-size Mother Base · live staff simulation · camera follows your operative</span><span>▶</span></div>
    </section>
    <aside class="map-sidebar mother-base-sidebar">
        <section class="map-control-panel">
            <div class="eyebrow">BASE MOVEMENT</div><h2>WALK THE PLATFORM</h2>
            <p>Move through the open ground with WASD/Arrow keys. Buildings, walls, machinery, cliffs and ocean edges are authoritative blockers.</p>
            <div class="map-controls" aria-label="Mother Base movement controls">
                <button type="button" class="up" data-base-move="up" aria-label="Move up">▲</button>
                <button type="button" class="left" data-base-move="left" aria-label="Move left">◀</button>
                <button type="button" class="down" data-base-move="down" aria-label="Move down">▼</button>
                <button type="button" class="right" data-base-move="right" aria-label="Move right">▶</button>
            </div>
            <div class="movement-status" data-base-movement-status>Ready · WASD / Arrow Keys</div>
        </section>
        <section class="base-status-card">
            <div class="eyebrow">BASE GARRISON</div><h3><?=msw_e($owner['username'])?></h3>
            <div class="base-status-grid"><span><small>ACCESS</small><b><?=msw_e($relationLabel)?></b></span><span><small>BASE POWER</small><b><?=number_format((int)$owner['base_power'])?></b></span><span><small>PERSONNEL</small><b><?=$personnelCount?></b></span><span><small>VEHICLES</small><b><?=$vehicleCount?></b></span></div>
            <p>Captured personnel roam slowly around assigned open areas. Captured hardware remains parked and synchronized for every authorized visitor.</p>
        </section>
    </aside>
</div>
<?php msw_footer(); ?>
