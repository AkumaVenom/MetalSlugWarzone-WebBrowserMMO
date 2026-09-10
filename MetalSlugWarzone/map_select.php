<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
require_once __DIR__.'/includes/battle_engine.php';
$u=msw_require_user();$maps=msw_map_catalog();$level=max(1,(int)$u['level']);
msw_header('Warzone Select','map_select.php');msw_resource_strip((int)$u['id']);
?>
<section class="hero">
    <div class="eyebrow">WORLD OPERATIONS · <?=count($maps)?> WARZONES</div>
    <h1>SELECT <span>WARZONE</span></h1>
    <p>Deploy into a shared battlefield. Higher threats bring higher-level enemies and heavier combat pressure. Each new warzone has a minimum enemy level, with further scaling as your Commander advances. Develop Mother Base and prepare your recovery gear before pushing deeper.</p>
</section>
<div class="map-list" style="margin-top:18px">
<?php foreach($maps as $key=>$m):
    $window=msw_enemy_level_window($level,(int)$m['level']);
    $readiness=msw_warzone_readiness_pressure($level,(int)$m['level']);
    $minimum=max(1,$level+(int)$window['min_offset']);$maximum=max(1,$level+(int)$window['max_offset']);
?>
    <article class="map-card">
        <div class="preview"><img src="<?=msw_e(msw_url($m['thumbnail']??$m['image']))?>" width="<?=intval($m['w'])?>" height="<?=intval($m['h'])?>" loading="lazy" decoding="async" alt="<?=msw_e($m['name'])?> battlefield preview"></div>
        <div class="info">
            <span class="badge">Threat Lv <?=number_format((int)$m['level'])?></span>
            <h3><?=msw_e($m['name'])?></h3>
            <p><?=msw_e($m['region'])?> · <?=number_format((int)$m['w'])?>×<?=number_format((int)$m['h'])?> battlefield</p>
            <p><strong>Enemy Lv <?=$minimum?>–<?=$maximum?></strong><br>Recommended Commander Lv <?=intval($readiness['recommended_level'])?></p>
            <a class="btn" href="<?=msw_e(msw_url('map.php?zone='.urlencode($key)))?>" aria-label="Deploy to <?=msw_e($m['name'])?>">Deploy</a>
        </div>
    </article>
<?php endforeach;?>
</div>
<?php msw_footer();?>
