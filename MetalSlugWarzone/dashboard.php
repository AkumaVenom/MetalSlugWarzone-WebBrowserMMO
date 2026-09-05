<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$u=msw_require_user();
$uid=(int)$u['id'];
msw_recalculate_base($uid);
$u=msw_user();
$units=msw_one('SELECT COUNT(*) c,COALESCE(MAX(level),0) maxlvl FROM units WHERE owner_user_id=?','i',[$uid])?:['c'=>0,'maxlvl'=>0];
$rank=msw_one('SELECT COUNT(*)+1 r FROM users WHERE base_power>?','i',[(int)$u['base_power']]);
$progress=msw_user_progress($u);
$fighter=msw_commander_fighter($uid);
$character=msw_character_catalog()[$u['character_key']]??reset(msw_character_catalog());
$fob=msw_fob_membership($uid);$fobWorld=$fob?msw_fob_world_row((int)$fob['world_id']):null;
msw_header('Command Center','dashboard.php');
msw_alert(msw_flash());
msw_resource_strip($uid);
?>
<section class="hero"><div class="eyebrow">COMMANDER <?=msw_e($u['username'])?> // LV <?=intval($progress['level'])?> // <?=msw_e($u['base_grade'])?></div><h1>MOTHER BASE <span>COMMAND</span></h1><p>Your command network is persistent and online. Deploy your selected operative, improve the recovered-unit roster, advance R&D, run dispatch operations, or challenge another base.</p></section>
<section class="commander-progression" style="margin-top:18px">
    <div class="commander-portrait"><img src="<?=msw_e(msw_url($character['sprite']))?>" alt=""><span>SELECTED OPERATIVE</span></div>
    <div class="commander-progress-main">
        <div class="eyebrow">COMMANDER PROGRESSION</div>
        <div class="commander-progress-title"><h2><?=msw_e($character['name'])?></h2><span class="grade">LV <?=intval($progress['level'])?></span><span class="badge">COMMAND RANK <?=intval($progress['command_rank'])?></span></div>
        <div class="xpbar commander-xpbar-large"><i style="width:<?=round((float)$progress['percent'],2)?>%"></i></div>
        <div class="commander-xp-numbers"><b><?=number_format((int)$progress['current_xp'])?> / <?=number_format((int)$progress['required_xp'])?> XP</b><span>Total Command XP <?=number_format((int)$progress['total_xp'])?> · Next level at <?=number_format((int)$progress['next_xp'])?> total</span></div>
    </div>
    <div class="commander-combat-stats"><div><small>HP</small><b><?=intval($fighter['max_hp'])?></b></div><div><small>ATK</small><b><?=intval($fighter['attack'])?></b></div><div><small>DEF</small><b><?=intval($fighter['defense'])?></b></div><div><small>SPD</small><b><?=intval($fighter['speed'])?></b></div></div>
</section>
<div class="grid g4" style="margin-top:18px"><?php msw_stat('Base Power',number_format((int)$u['base_power']),'Global #'.(int)($rank['r']??1));msw_stat('Base Grade',$u['base_grade'],'E-- → S++');msw_stat('Recovered Units',(int)$units['c'],'Highest unit Lv '.(int)$units['maxlvl']);msw_stat('GMP',number_format((int)$u['gmp']),'Operational currency');?></div>
<div class="grid g2" style="margin-top:18px"><section><?php msw_panel('Sector Readiness','MOTHER BASE');?><table><thead><tr><th>Sector</th><th>Level</th><th>Score</th><th>Grade</th></tr></thead><tbody><?php foreach(msw_all('SELECT * FROM base_sectors WHERE user_id=? ORDER BY sector_key','i',[$uid]) as $s):$meta=msw_sectors()[$s['sector_key']]??['name'=>$s['sector_key']];?><tr><td><?=msw_e($meta['name'])?></td><td><?=number_format((int)$s['level'])?></td><td><?=number_format((int)$s['score'])?></td><td><span class="grade"><?=msw_e($s['grade'])?></span></td></tr><?php endforeach;?></tbody></table><?php msw_panel_end();?></section><section><?php msw_panel('Immediate Actions','THEATRE CONTROL');?><div class="feature"><b>Deploy to Warzone</b><small>Mandatory multiplayer presence remains active while deployed.</small><div class="actions" style="margin-top:8px"><a class="btn" href="<?=msw_e(msw_url('map_select.php'))?>">Select Combat Zone</a></div></div><?php if($fob):?><div class="feature"><b>Global FOB · <?=msw_e($fobWorld?msw_fob_world_name($fobWorld):'ONLINE')?></b><small>Your persistent overview-world slot is active. Survey nearby human and autonomous FOBs, infiltrate directly, or dispatch staff invasion teams.</small><div class="actions" style="margin-top:8px"><a class="btn" href="<?=msw_e(msw_url('fob_world.php'))?>">Open FOB World</a></div></div><?php else:?><div class="feature"><b>Global FOB Deployment Required</b><small>Choose your continent type and coherent base skin to receive a permanent overview-world instance and Mother Base identity.</small><div class="actions" style="margin-top:8px"><a class="btn" href="<?=msw_e(msw_url('fob_globe.php'))?>">Deploy Global FOB</a></div></div><?php endif;?><div class="feature"><b>Enter Mother Base</b><small>Walk your selected physical base, inspect synchronized captured staff and vehicles, and receive authorized friends or Strike Force members.</small><div class="actions" style="margin-top:8px"><a class="btn" href="<?=msw_e(msw_url('mother_base.php'))?>">Enter Physical Base</a></div></div><div class="feature"><b>Staff Management</b><small>Assign recovered personnel to the sector where their aptitude produces the greatest value.</small><div class="actions" style="margin-top:8px"><a class="btn secondary" href="<?=msw_e(msw_url('staff.php'))?>">Manage Staff</a></div></div><div class="feature"><b>R&D / Strategic Projects</b><small>Unlock stronger recovery systems and persistent real-time projects.</small><div class="actions" style="margin-top:8px"><a class="btn secondary" href="<?=msw_e(msw_url('rd.php'))?>">Open R&D</a><a class="btn secondary" href="<?=msw_e(msw_url('strategic.php'))?>">Strategic</a></div></div><?php msw_panel_end();?></section></div>
<?php msw_footer();?>
