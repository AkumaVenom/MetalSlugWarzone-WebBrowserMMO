<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';

$user=msw_require_user();
$uid=(int)$user['id'];
msw_mb_presence_leave($uid);
$activeBattle=msw_active_encounter($uid);
if($activeBattle) msw_redirect('battle.php?id='.(int)$activeBattle['id']);

$maps=msw_map_catalog();
$key=(string)($_GET['zone']??$user['active_map']??'');
if(!isset($maps[$key])) msw_redirect('map_select.php');
$map=$maps[$key];

if($user['active_map']===$key){
    $x=(int)$user['map_x'];
    $y=(int)$user['map_y'];
}else{
    [$x,$y]=msw_map_spawn($key);
}
// v3 maps introduce authored collision. Repair any legacy coordinate that now
// falls inside solid terrain and persist the nearest legal foot point.
[$x,$y]=msw_map_safe_position($key,$x,$y);
$facing=in_array((string)($user['facing']??'right'),['up','down','left','right'],true)?(string)$user['facing']:'right';
msw_presence_touch($uid,$key,$x,$y,$facing);
$character=msw_character_catalog()[$user['character_key']]??reset(msw_character_catalog());
$progress=msw_user_progress($user);
$spriteR=(string)($character['sprite_r']??$character['sprite']);
$spriteL=(string)($character['sprite_l']??$spriteR);
$mirrorLeft=!empty($character['mirror_left']);
$initialSprite=$facing==='left'?$spriteL:$spriteR;
$botPopulation=msw_bot_population_summary();

msw_header($map['name'],'map_select.php');
?>
<div class="warzone-toolbar">
    <a class="btn secondary" href="<?=msw_e(msw_url('map_select.php'))?>">← Change Warzone</a>
    <span class="badge live-dot">Presence: Always Online</span>
    <span class="badge">Native <?=intval($map['w'])?> × <?=intval($map['h'])?></span>
    <span class="badge">WASD / Arrow Keys</span>
    <span class="badge">Server Collision: Active</span>
    <span class="badge ai-badge">AI Commanders: <?=number_format((int)$botPopulation['total'])?> Persistent</span>
</div>
<div class="warzone-layout">
    <section class="warzone-console">
        <div class="warzone-console-head">
            <div><small>ACTIVE COMBAT ZONE</small><strong><?=msw_e($map['name'])?></strong></div>
            <div class="warzone-readout"><span><?=msw_e($map['region'])?></span><b>THREAT <?=intval($map['level'])?></b></div>
        </div>
        <div class="map-stage"
             data-map-stage
             data-csrf="<?=msw_e(msw_csrf())?>"
             data-move-url="<?=msw_e(msw_url('map_move.php'))?>"
             data-presence-url="<?=msw_e(msw_url('map_presence.php'))?>"
             data-move-interval="110"
             style="--map-native-w:<?=intval($map['w'])?>px;--map-native-h:<?=intval($map['h'])?>px">
            <div class="map-world" style="width:<?=intval($map['w'])?>px;height:<?=intval($map['h'])?>px">
                <img class="level" src="<?=msw_e(msw_url($map['image']))?>" width="<?=intval($map['w'])?>" height="<?=intval($map['h'])?>" alt="<?=msw_e($map['name'])?>">
                <img class="map-avatar" data-local-avatar data-facing="<?=msw_e($facing)?>" data-sprite-left="<?=msw_e(msw_url($spriteL))?>" data-sprite-right="<?=msw_e(msw_url($spriteR))?>" data-mirror-left="<?=$mirrorLeft?'1':'0'?>" src="<?=msw_e(msw_url($initialSprite))?>" style="left:<?=$x?>px;top:<?=$y?>px" alt="you">
                <span class="map-label" data-local-label style="left:<?=$x?>px;top:<?=$y?>px"><?=msw_e($user['username'])?> · YOU</span>
            </div>
        </div>
        <div class="warzone-scroll-hint"><span>◀</span><span>Native-size v3 battlefield · camera follows your operative</span><span>▶</span></div>
    </section>
    <aside class="map-sidebar">
        <section class="map-control-panel">
            <div class="eyebrow">FIELD MOVEMENT</div>
            <h2>MOVE OPERATIVE</h2>
            <p>Keyboard movement and the command pad remain available outside the scrolling battlefield at all times.</p>
            <div class="map-controls" aria-label="Movement controls">
                <button type="button" class="up" data-move="up" aria-label="Move up">▲</button>
                <button type="button" class="left" data-move="left" aria-label="Move left">◀</button>
                <button type="button" class="down" data-move="down" aria-label="Move down">▼</button>
                <button type="button" class="right" data-move="right" aria-label="Move right">▶</button>
            </div>
            <div class="movement-status" data-movement-status>Ready · WASD / Arrow Keys</div>
        </section>
        <section class="map-operative-card">
            <img src="<?=msw_e(msw_url($character['sprite']))?>" alt="">
            <div><small>DEPLOYED OPERATIVE</small><b><?=msw_e($character['name'])?></b><span>Commander Lv <?=intval($progress['level'])?></span></div>
            <div class="xpbar wide"><i style="width:<?=round((float)$progress['percent'],2)?>%"></i></div>
            <em><?=number_format((int)$progress['current_xp'])?> / <?=number_format((int)$progress['required_xp'])?> XP to next level</em>
        </section>
    </aside>
</div>
<?php msw_footer(); ?>
