<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();
$uid=(int)$user['id'];

if(msw_is_post()){
    msw_verify_post();
    $unitId=(int)($_POST['unit_id']??0);
    $assignment=(string)($_POST['assignment']??'reserve');
    $active=isset($_POST['active_combat'])?1:0;
    $allowed=array_merge(['reserve'],array_keys(msw_sectors()));
    if(!in_array($assignment,$allowed,true)) $assignment='reserve';

    $unit=msw_one('SELECT id,assignment,active_combat,dispatched_until FROM units WHERE id=? AND owner_user_id=?','ii',[$unitId,$uid]);
    if($unit){
        if(!empty($unit['dispatched_until']) && strtotime((string)$unit['dispatched_until'])>time()){
            msw_flash('A dispatched unit cannot be reassigned until its mission has concluded.','warning');
            msw_redirect('staff.php');
        }

        if($assignment!==(string)$unit['assignment'] && !msw_sector_has_capacity_for_assignment($uid,$assignment,$unitId)){
            msw_flash('That Mother Base sector is at its current staff capacity. Improve its level before adding more personnel.','warning');
            msw_redirect('staff.php');
        }

        if($active && !(int)$unit['active_combat']){
            $count=msw_one('SELECT COUNT(*) c FROM units WHERE owner_user_id=? AND active_combat=1 AND id<>?','ii',[$uid,$unitId]);
            if((int)($count['c']??0)>=4){
                $active=0;
                msw_flash('Combat Unit supports a maximum of four active members.','warning');
            }
        }

        msw_stmt('UPDATE units SET assignment=?,active_combat=? WHERE id=? AND owner_user_id=?','siii',[$assignment,$active,$unitId,$uid]);
        msw_recalculate_base($uid);
        if(!isset($_SESSION['flash'])) msw_flash('Personnel assignment updated.','success');
    }
    msw_redirect('staff.php');
}

$units=msw_all('SELECT * FROM units WHERE owner_user_id=? ORDER BY active_combat DESC,grade DESC,level DESC,id DESC','i',[$uid]);
msw_header('Staff Management','base.php');
msw_alert(msw_flash());
?>
<section class="hero">
    <div class="eyebrow">PERSONNEL MANAGEMENT</div>
    <h1>STAFF <span>ROSTER</span></h1>
    <p>Every recovered soldier, vehicle and aircraft is a permanent owned unit with independent aptitudes. Sector capacity, dispatch state and the four-member active Combat Unit are enforced by the server.</p>
</section>
<?php msw_panel('Recovered Personnel & Hardware',count($units).' UNITS'); ?>
<?php if(!$units): ?><div class="empty">No recovered units.</div><?php endif; ?>
<?php foreach($units as $unit):
    $stats=['C'=>(int)$unit['combat'],'R'=>(int)$unit['rd'],'S'=>(int)$unit['support'],'I'=>(int)$unit['intel'],'M'=>(int)$unit['medical'],'F'=>(int)$unit['mess'],'D'=>(int)$unit['security']];
    $busy=!empty($unit['dispatched_until']) && strtotime((string)$unit['dispatched_until'])>time();
?>
<div class="unit-card">
    <div><span class="grade"><?=msw_e($unit['grade'])?></span><small style="display:block;margin-top:6px"><?=msw_e($unit['unit_class'])?></small></div>
    <div>
        <b><?=msw_e($unit['callsign'])?></b> · Lv <?=intval($unit['level'])?>
        <?php if($busy): ?><span class="badge">DISPATCHED</span><?php endif; ?>
        <div class="unit-bars"><?php foreach($stats as $key=>$value): ?><span title="<?=msw_e($key.' '.$value)?>"><i style="width:<?=min(100,$value)?>%"></i></span><?php endforeach; ?></div>
        <small>C <?=$stats['C']?> · R&D <?=$stats['R']?> · SUP <?=$stats['S']?> · INT <?=$stats['I']?> · MED <?=$stats['M']?> · MESS <?=$stats['F']?> · SEC <?=$stats['D']?></small>
    </div>
    <form method="post" style="min-width:230px">
        <?=msw_csrf_field()?>
        <input type="hidden" name="unit_id" value="<?=intval($unit['id'])?>">
        <select name="assignment" <?=$busy?'disabled':''?>>
            <?php foreach(array_merge(['reserve'=>['name'=>'Reserve']],msw_sectors()) as $key=>$meta): ?>
                <option value="<?=msw_e($key)?>" <?=$unit['assignment']===$key?'selected':''?>><?=msw_e($meta['name'])?></option>
            <?php endforeach; ?>
        </select>
        <label style="margin:7px 0"><input type="checkbox" style="width:auto" name="active_combat" value="1" <?=(int)$unit['active_combat']?'checked':''?> <?=$busy?'disabled':''?>> Active Combat Unit</label>
        <button class="small" <?=$busy?'disabled':''?>>Apply</button>
    </form>
</div>
<?php endforeach; ?>
<?php msw_panel_end(); msw_footer(); ?>
