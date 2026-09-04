<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();
$uid=(int)$user['id'];
$catalog=msw_dispatch_catalog();

// Resolve due missions from authoritative database timestamps.
$due=msw_all("SELECT id FROM dispatch_missions WHERE user_id=? AND result='pending' AND finish_at<=NOW() ORDER BY id",'i',[$uid]);
foreach($due as $dueRow){
    $db=msw_db();
    $db->begin_transaction();
    try{
        $mission=msw_one("SELECT * FROM dispatch_missions WHERE id=? AND user_id=? AND result='pending' FOR UPDATE",'ii',[(int)$dueRow['id'],$uid]);
        if(!$mission){$db->rollback();continue;}
        if(strtotime((string)$mission['finish_at'])>time()){$db->rollback();continue;}

        $definition=$catalog[$mission['mission_key']]??null;
        $chance=max(0.0,min(1.0,(float)$mission['success_chance']));
        $success=(random_int(1,10000)/10000)<=$chance;
        $reward=$success&&$definition ? $definition['reward'] : ['gmp'=>120];
        $result=$success?'success':'failure';

        msw_stmt('UPDATE dispatch_missions SET result=?,resolved_at=NOW(),reward_json=? WHERE id=? AND result=\'pending\'','ssi',[$result,json_encode($reward,JSON_UNESCAPED_SLASHES),(int)$mission['id']]);
        $ids=array_values(array_unique(array_map('intval',json_decode((string)$mission['unit_ids_json'],true)?:[])));
        foreach($ids as $unitId){
            msw_add_unit_xp($uid,$unitId,$success?80:25);
            msw_stmt('UPDATE units SET dispatched_until=NULL WHERE id=? AND owner_user_id=?','ii',[$unitId,$uid]);
        }
        msw_grant_resources($uid,$reward);
        msw_recalculate_base($uid);
        $db->commit();
        msw_console_event_for_user($uid,'DISPATCH','RESOLVED',($definition['name']??$mission['mission_key']).' resolved: '.strtoupper($result).'.',['mission_id'=>(int)$mission['id'],'mission_key'=>(string)$mission['mission_key'],'result'=>$result,'units'=>count($ids)]);
    }catch(Throwable $e){
        $db->rollback();
        throw $e;
    }
}

if(msw_is_post()){
    msw_verify_post();
    $key=(string)($_POST['mission']??'');
    if(!isset($catalog[$key])){
        msw_flash('Dispatch mission unavailable.','error');
        msw_redirect('dispatch.php');
    }
    $definition=$catalog[$key];
    $posted=$_POST['units']??[];
    if(!is_array($posted)) $posted=[];
    $ids=array_values(array_unique(array_filter(array_map('intval',$posted),fn($id)=>$id>0)));
    if(count($ids)!==(int)$definition['slots']){
        msw_flash('Select exactly '.$definition['slots'].' eligible units for '.$definition['name'].'.','error');
        msw_redirect('dispatch.php');
    }

    $marks=implode(',',array_fill(0,count($ids),'?'));
    $types='i'.str_repeat('i',count($ids));
    $db=msw_db();
    $db->begin_transaction();
    try{
        $rows=msw_all("SELECT id,combat,level,dispatched_until FROM units WHERE owner_user_id=? AND id IN($marks) FOR UPDATE",$types,[$uid,...$ids]);
        if(count($rows)!==count($ids)) throw new RuntimeException('One or more units are unavailable.');
        foreach($rows as $row){
            if(!empty($row['dispatched_until']) && strtotime((string)$row['dispatched_until'])>time()) throw new RuntimeException('One or more selected units are already deployed.');
        }
        $power=array_sum(array_map(fn($row)=>(int)$row['combat']+(int)$row['level']*3,$rows));
        $chance=max(.18,min(.95,.45+(($power-(int)$definition['difficulty'])/600)));
        $finish=date('Y-m-d H:i:s',time()+(int)$definition['duration']);
        msw_stmt('INSERT INTO dispatch_missions(user_id,mission_key,unit_ids_json,snapshot_power,success_chance,started_at,finish_at) VALUES(?,?,?,?,?,NOW(),?)','issids',[$uid,$key,json_encode($ids),$power,$chance,$finish]);
        foreach($ids as $unitId) msw_stmt('UPDATE units SET dispatched_until=? WHERE id=? AND owner_user_id=?','sii',[$finish,$unitId,$uid]);
        $db->commit();
        msw_console_event_for_user($uid,'DISPATCH','DEPLOY',$definition['name'].' dispatched with '.count($ids).' units.',['mission_key'=>$key,'units'=>count($ids),'power'=>$power,'success_percent'=>(int)round($chance*100)]);
        msw_flash($definition['name'].' dispatched with '.count($ids).' units. Completion remains anchored to the MySQL server clock.','success');
    }catch(Throwable $e){
        $db->rollback();
        msw_flash($e->getMessage(),'error');
    }
    msw_redirect('dispatch.php');
}

$available=msw_all('SELECT * FROM units WHERE owner_user_id=? AND (dispatched_until IS NULL OR dispatched_until<=NOW()) ORDER BY combat DESC,level DESC,id ASC LIMIT 40','i',[$uid]);
$runs=msw_all('SELECT * FROM dispatch_missions WHERE user_id=? ORDER BY id DESC LIMIT 12','i',[$uid]);
msw_header('Combat Dispatch','dispatch.php');
msw_alert(msw_flash());
?>
<section class="hero"><div class="eyebrow">COMBAT UNIT DEPLOYMENT</div><h1>DISPATCH <span>MISSIONS</span></h1><p>Build a dispatch team with explicit unit cards—no modifier-key multi-selects. Team power is snapshotted at deployment; completion and results remain authoritative in MySQL across browser, Apache and machine restarts.</p></section>
<div class="grid g2 dispatch-layout" style="margin-top:18px">
<section><?php msw_panel('Available Dispatches','COMBAT UNIT'); ?>
<?php foreach($catalog as $key=>$definition): $slots=(int)$definition['slots']; ?>
<form method="post" class="dispatch-mission" data-dispatch-form data-slots="<?=$slots?>">
    <?=msw_csrf_field()?><input type="hidden" name="mission" value="<?=msw_e($key)?>">
    <div class="dispatch-mission-head"><div><b><?=msw_e($definition['name'])?></b><small>Duration <?=gmdate('H:i:s',(int)$definition['duration'])?> · Difficulty <?=intval($definition['difficulty'])?></small></div><span class="dispatch-slot-count"><strong data-selected-count>0</strong> / <?=$slots?> selected</span></div>
    <?php if(count($available)<$slots): ?>
        <div class="empty">This mission needs <?=$slots?> eligible units; only <?=count($available)?> are currently available.</div>
    <?php else: ?>
        <div class="dispatch-unit-grid">
        <?php foreach($available as $unit): ?>
            <label class="dispatch-unit-card">
                <input type="checkbox" name="units[]" value="<?=intval($unit['id'])?>">
                <span class="dispatch-check">✓</span>
                <span class="dispatch-unit-main"><b><?=msw_e($unit['callsign'])?></b><small>Lv <?=intval($unit['level'])?> · <?=msw_e(str_replace('_',' ',(string)$unit['unit_class']))?></small></span>
                <span class="grade"><?=msw_e($unit['grade'])?></span>
                <span class="dispatch-power">C <?=intval($unit['combat'])?></span>
            </label>
        <?php endforeach; ?>
        </div>
        <div class="dispatch-footer"><span data-dispatch-help>Select exactly <?=$slots?> units.</span><button type="submit" disabled data-dispatch-submit>Dispatch <?=$slots?> Units</button></div>
    <?php endif; ?>
</form>
<?php endforeach; ?>
<?php msw_panel_end(); ?></section>
<section><?php msw_panel('Mission Ledger','RECENT RESULTS'); ?>
<?php if(!$runs): ?><div class="empty">No dispatch records yet. Build your first team from the mission cards.</div><?php else: ?>
<table><thead><tr><th>Mission</th><th>Status</th><th>Power</th><th>Time / Result</th></tr></thead><tbody>
<?php foreach($runs as $run): ?>
<tr><td><?=msw_e($catalog[$run['mission_key']]['name']??$run['mission_key'])?></td><td><span class="badge"><?=msw_e($run['result'])?></span></td><td><?=number_format((int)$run['snapshot_power'])?></td><td>
<?php if($run['result']==='pending'): ?><span data-countdown="<?=msw_e(date(DATE_ATOM,strtotime((string)$run['finish_at'])))?>">--:--:--</span>
<?php else: $reward=json_decode((string)$run['reward_json'],true)?:[]; ?><?=msw_e(implode(', ',array_map(fn($k,$v)=>str_replace('_',' ',$k).' +'.$v,array_keys($reward),$reward)))?><?php endif; ?>
</td></tr>
<?php endforeach; ?>
</tbody></table><?php endif; ?>
<?php msw_panel_end(); ?></section>
</div>
<?php msw_footer(); ?>
