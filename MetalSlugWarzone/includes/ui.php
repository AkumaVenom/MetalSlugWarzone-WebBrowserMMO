<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function msw_header(string $title,string $active=''): void {
    $u=msw_user(); $chars=msw_character_catalog();
    $script=basename((string)($_SERVER['SCRIPT_NAME']??'index.php'),'.php');
    $pageClass='page-'.preg_replace('/[^a-z0-9]+/','-',strtolower($script));
    if($u && $script!=='mother_base') msw_mb_presence_leave((int)$u['id']);
    ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#0b0c0d"><title><?=msw_e($title)?> · Metal Slug Warzone</title><link rel="stylesheet" href="<?=msw_e(msw_url('assets/css/msw.css'))?>"></head><body class="<?=msw_e($pageClass)?>">
    <div class="scanline" aria-hidden="true"></div><header class="topbar"><a class="brand" href="<?=msw_e(msw_url($u?'dashboard.php':'index.php'))?>"><span class="brand-mark">MSW</span><span><b>METAL SLUG</b><em>WARZONE // COMMAND NETWORK</em></span></a>
    <div class="network-state" aria-label="Command network online"><i></i><span>COMMAND LINK</span><b>ONLINE</b></div>
    <?php if($u): $c=$chars[$u['character_key']]??reset($chars);$p=msw_user_progress($u); ?><div class="commander-chip"><span class="commander-sprite-shell"><img src="<?=msw_e(msw_url($c['sprite']))?>" alt=""></span><span class="commander-chip-copy"><b><?=msw_e($u['username'])?></b><small>LV <?=intval($p['level'])?> · <?=msw_e($u['base_grade'])?> · PWR <?=number_format((int)$u['base_power'])?></small><span class="commander-xp-row"><i><em style="width:<?=round((float)$p['percent'],2)?>%"></em></i><small>XP <?=number_format((int)$p['current_xp'])?> / <?=number_format((int)$p['required_xp'])?></small></span></span></div><?php endif; ?></header>
    <?php if($u): ?><nav class="mainnav"><?php foreach([
        'dashboard.php'=>'Command','map_select.php'=>'Warzone','base.php'=>'Mother Base','missions.php'=>'Operations','bosses.php'=>'Bosses','dispatch.php'=>'Dispatch','fob.php'=>'FOB','pvp.php'=>'Live PvP','ai_commanders.php'=>'AI Network','community.php'=>'Network','rankings.php'=>'Rankings'
    ] as $href=>$label): ?><a class="<?=$active===$href?'active':''?>" href="<?=msw_e(msw_url($href))?>"><?=msw_e($label)?></a><?php endforeach; ?><a href="<?=msw_e(msw_url('logout.php'))?>">Logout</a></nav><?php endif; ?>
    <main class="shell"><?php
}
function msw_footer(): void { ?><footer class="footer"><span>METAL SLUG WARZONE // SERVER-AUTHORITATIVE MMO FOUNDATION</span><span>v<?=msw_e((string)msw_config()['version'])?></span></footer></main><script src="<?=msw_e(msw_url('assets/js/msw.js'))?>"></script></body></html><?php }
function msw_alert(?array $flash): void { if(!$flash)return; ?><div class="alert <?=msw_e($flash['kind']??'info')?>"><?=msw_e($flash['message']??'')?></div><?php }
function msw_panel(string $title,string $kicker=''): void { ?><section class="panel"><div class="panel-head"><div><small><?=msw_e($kicker)?></small><h2><?=msw_e($title)?></h2></div><span class="bolts">•••</span></div><div class="panel-body"><?php }
function msw_panel_end(): void { ?></div></section><?php }
function msw_stat(string $label,string|int $value,string $sub=''): void { $tone='stat-'.preg_replace('/[^a-z0-9]+/','-',strtolower($label)); ?><div class="stat <?=msw_e($tone)?>"><small><?=msw_e($label)?></small><strong><?=msw_e($value)?></strong><?php if($sub!==''):?><em><?=msw_e($sub)?></em><?php endif;?></div><?php }
function msw_resource_strip(int $uid): void { $r=msw_resources($uid); ?><div class="resource-strip"><?php foreach(['common_metal'=>'Common Metal','minor_metal'=>'Minor Metal','precious_metal'=>'Precious','fuel'=>'Fuel','biological'=>'Bio','strategic_devices'=>'Strategic'] as $k=>$n):?><span class="resource resource-<?=msw_e($k)?>"><small><?=msw_e($n)?></small><b><?=number_format((int)($r[$k]??0))?></b><i aria-hidden="true"></i></span><?php endforeach;?></div><?php }
