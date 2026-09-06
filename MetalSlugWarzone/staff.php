<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();$uid=(int)$user['id'];msw_recalculate_base($uid);

if(msw_is_post()){
    msw_verify_post();$mode=(string)($_POST['mode']??'assignment');
    if($mode==='backup'){
        $slot=(int)($_POST['slot_index']??0);$unitId=(int)($_POST['backup_unit_id']??0);
        try{msw_set_security_backup_slot($uid,$slot,$unitId);msw_console_event_for_user($uid,'BASE','SECURITY_ESCORT',$unitId>0?'Security backup slot '.$slot.' updated.':'Security backup slot '.$slot.' cleared.',['slot'=>$slot,'unit_id'=>$unitId]);msw_flash($unitId>0?'Security backup updated.':'Security backup slot cleared.','success');}
        catch(Throwable $e){msw_flash($e->getMessage(),'error');}
        msw_redirect('staff.php');
    }

    $unitId=(int)($_POST['unit_id']??0);$assignment=(string)($_POST['assignment']??'reserve');$active=isset($_POST['active_combat'])?1:0;
    $allowed=array_merge(['reserve'],array_keys(msw_sectors()));if(!in_array($assignment,$allowed,true))$assignment='reserve';
    $unit=msw_one('SELECT id,callsign,assignment,active_combat,dispatched_until FROM units WHERE id=? AND owner_user_id=?','ii',[$unitId,$uid]);
    if($unit){
        $fighterBefore=msw_commander_fighter($uid);
        if(!empty($unit['dispatched_until'])&&strtotime((string)$unit['dispatched_until'])>time()){msw_flash('A dispatched unit cannot be reassigned until its mission has concluded.','warning');msw_redirect('staff.php');}
        if($assignment!==(string)$unit['assignment']&&!msw_sector_has_capacity_for_assignment($uid,$assignment,$unitId)){msw_flash('That Mother Base sector is at its current staff capacity. Improve its level before adding more personnel.','warning');msw_redirect('staff.php');}
        if($active&&!(int)$unit['active_combat']){$count=msw_one('SELECT COUNT(*) c FROM units WHERE owner_user_id=? AND active_combat=1 AND id<>?','ii',[$uid,$unitId]);if((int)($count['c']??0)>=4){$active=0;msw_flash('Combat Unit supports a maximum of four active members.','warning');}}
        msw_stmt('UPDATE units SET assignment=?,active_combat=? WHERE id=? AND owner_user_id=?','siii',[$assignment,$active,$unitId,$uid]);
        if($assignment!=='security')msw_clear_security_backup_for_unit($uid,$unitId);
        msw_recalculate_base($uid);
        $fighterAfter=msw_commander_fighter($uid);
        $deltaLabels=[];
        foreach(['max_hp'=>'HP','attack'=>'ATK','defense'=>'DEF','speed'=>'SPD'] as $stat=>$label){
            $delta=(int)$fighterAfter[$stat]-(int)$fighterBefore[$stat];
            if($delta!==0)$deltaLabels[]=$label.' '.($delta>0?'+':'').$delta;
        }
        $sectorLabels=[];
        if(isset(msw_sectors()[$assignment])){
            $contribution=(array)($fighterAfter['sector_contributions'][$assignment]['bonuses']??[]);
            foreach(['max_hp'=>'HP','attack'=>'ATK','defense'=>'DEF','speed'=>'SPD'] as $stat=>$label){
                $gain=(float)($contribution[$stat]??0.0);
                if($gain>0.01)$sectorLabels[]=$label.' +'.number_format($gain,1);
            }
        }
        $sectorName=isset(msw_sectors()[$assignment])?(string)msw_sectors()[$assignment]['name']:'Reserve';
        msw_console_event_for_user($uid,'BASE','STAFF',($unit['callsign']??('Unit #'.$unitId)).' assigned to '.strtoupper($assignment).($active?' · active combat':'').'.',['unit_id'=>$unitId,'assignment'=>$assignment,'active_combat'=>(bool)$active,'commander_stat_delta'=>$deltaLabels,'sector_contribution'=>$sectorLabels]);
        if(!isset($_SESSION['flash'])){
            $detail=$sectorLabels?' '.$sectorName.' contribution: '.implode(' · ',$sectorLabels).'.':'';
            $detail.=$deltaLabels?' Commander total change: '.implode(' · ',$deltaLabels).'.':' Commander totals recalculated.';
            msw_flash('Personnel assignment updated.'.$detail,'success');
        }
    }
    msw_redirect('staff.php');
}

$units=msw_all('SELECT * FROM units WHERE owner_user_id=? ORDER BY active_combat DESC,grade DESC,level DESC,id DESC','i',[$uid]);
$fighter=msw_commander_fighter($uid);
$securityLevel=msw_sector_level($uid,'security');$backupCapacity=msw_security_backup_capacity($uid);$backupSlots=msw_security_backup_slots($uid);$backupCandidates=msw_security_backup_candidates($uid);
msw_header('Staff Management','base.php');msw_alert(msw_flash());
?>
<section class="hero"><div class="eyebrow">PERSONNEL MANAGEMENT · SECURITY TEAM LV <?=intval($securityLevel)?></div><h1>STAFF <span>ROSTER</span></h1><p>Recovered soldiers and hardware stay in your roster. Assign personnel to Security, then choose up to two soldiers as battle backup. They provide covering fire and intercept part of incoming Commander damage until they are knocked out.</p></section>

<section class="panel" style="margin-top:18px"><div class="panel-head"><div><small>LIVE COMMANDER POWER</small><h2>Personnel Combat Contribution</h2></div><span class="bolts">•••</span></div><div class="panel-body"><p class="muted-copy">Staff assignments recalculate these stats immediately. Sector score contributes between whole Mother Base levels, so you do not need to wait for the next 120-point threshold before seeing a combat benefit.</p><div class="grid g4"><?php msw_stat('Commander HP',number_format((int)$fighter['max_hp']),'+'.number_format((int)($fighter['mother_base_bonuses']['max_hp']??0)).' base');msw_stat('Commander ATK',number_format((int)$fighter['attack']),'+'.number_format((int)($fighter['mother_base_bonuses']['attack']??0)).' base');msw_stat('Commander DEF',number_format((int)$fighter['defense']),'+'.number_format((int)($fighter['mother_base_bonuses']['defense']??0)).' base');msw_stat('Commander SPD',number_format((int)$fighter['speed']),'+'.number_format((int)($fighter['mother_base_bonuses']['speed']??0)).' base');?></div></div></section>

<section class="panel security-party-panel" style="margin-top:18px"><div class="panel-head"><div><small>SECURITY ESCORT DETAIL · <?=intval($backupCapacity)?> SLOTS</small><h2>Security Backup Squad</h2></div><span class="bolts">•••</span></div><div class="panel-body"><p class="muted-copy">Security Lv 1 unlocks two backup slots. Escorts now rotate damage-interception duty, Lv 4 improves backup accuracy, and Lv 7 increases controlled support damage while keeping the Commander as the primary attacker. Staff on dispatch missions automatically sit out until they return.</p>
<div class="grid g2 security-slot-grid">
<?php for($slot=1;$slot<=$backupCapacity;$slot++):$selected=$backupSlots[$slot]??null; ?>
<form method="post" class="security-slot-card"><?=msw_csrf_field()?><input type="hidden" name="mode" value="backup"><input type="hidden" name="slot_index" value="<?=$slot?>"><div><span class="badge">BACKUP SLOT <?=$slot?></span><h3><?=msw_e($selected['callsign']??'Unassigned Escort')?></h3><?php if($selected):?><small><?=msw_e($selected['unit_class'])?> · LV <?=intval($selected['level'])?> · <?=msw_e($selected['grade'])?> · SEC <?=intval($selected['security'])?></small><?php else:?><small>Assign a soldier to the Security Team first, then choose them here.</small><?php endif;?></div><select name="backup_unit_id"><option value="0">— Empty Slot —</option><?php foreach($backupCandidates as $candidate):$busy=!empty($candidate['dispatched_until'])&&strtotime((string)$candidate['dispatched_until'])>time();?><option value="<?=intval($candidate['id'])?>" <?=((int)($selected['unit_id']??0)===(int)$candidate['id'])?'selected':''?> <?=$busy?'disabled':''?>><?=msw_e($candidate['callsign'])?> · <?=msw_e($candidate['grade'])?> · SEC <?=intval($candidate['security'])?><?=$busy?' · DISPATCHED':''?></option><?php endforeach;?></select><button class="small">Assign Backup</button></form>
<?php endfor; ?>
</div>
<?php if(!$backupCandidates):?><div class="empty">No eligible Security staff are available. Assign recovered soldiers to the Security Team below to make them available as backup.</div><?php endif;?>
</div></section>

<?php msw_panel('Recovered Personnel & Hardware',count($units).' UNITS'); ?>
<?php if(!$units): ?><div class="empty">No recovered units.</div><?php endif; ?>
<?php foreach($units as $unit):$stats=['C'=>(int)$unit['combat'],'R'=>(int)$unit['rd'],'S'=>(int)$unit['support'],'I'=>(int)$unit['intel'],'M'=>(int)$unit['medical'],'F'=>(int)$unit['mess'],'D'=>(int)$unit['security']];$busy=!empty($unit['dispatched_until'])&&strtotime((string)$unit['dispatched_until'])>time();$backupSlot=0;foreach($backupSlots as $slot=>$selected)if((int)$selected['unit_id']===(int)$unit['id'])$backupSlot=(int)$slot; ?>
<div class="unit-card">
    <div><span class="grade"><?=msw_e($unit['grade'])?></span><small style="display:block;margin-top:6px"><?=msw_e($unit['unit_class'])?></small><?php if($backupSlot):?><span class="ai-mark">BACKUP <?=$backupSlot?></span><?php endif;?></div>
    <div><b><?=msw_e($unit['callsign'])?></b> · Lv <?=intval($unit['level'])?> <?php if($busy):?><span class="badge">DISPATCHED</span><?php endif;?><div class="unit-bars"><?php foreach($stats as $key=>$value):?><span title="<?=msw_e($key.' '.$value)?>"><i style="width:<?=min(100,$value)?>%"></i></span><?php endforeach;?></div><small>C <?=$stats['C']?> · R&D <?=$stats['R']?> · SUP <?=$stats['S']?> · INT <?=$stats['I']?> · MED <?=$stats['M']?> · MESS <?=$stats['F']?> · SEC <?=$stats['D']?></small></div>
    <form method="post" style="min-width:230px"><?=msw_csrf_field()?><input type="hidden" name="mode" value="assignment"><input type="hidden" name="unit_id" value="<?=intval($unit['id'])?>"><select name="assignment" <?=$busy?'disabled':''?>><?php foreach(array_merge(['reserve'=>['name'=>'Reserve']],msw_sectors()) as $key=>$meta):?><option value="<?=msw_e($key)?>" <?=$unit['assignment']===$key?'selected':''?>><?=msw_e($meta['name'])?></option><?php endforeach;?></select><label style="margin:7px 0"><input type="checkbox" style="width:auto" name="active_combat" value="1" <?=(int)$unit['active_combat']?'checked':''?> <?=$busy?'disabled':''?>> Active Combat</label><button class="small" <?=$busy?'disabled':''?>>Apply</button></form>
</div>
<?php endforeach; ?>
<?php msw_panel_end();msw_footer(); ?>
