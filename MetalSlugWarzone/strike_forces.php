<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$u=msw_require_user();$uid=(int)$u['id'];
$membership=msw_one('SELECT m.*,s.name,s.tag,s.description,s.owner_user_id FROM strike_force_members m JOIN strike_forces s ON s.id=m.strike_force_id WHERE m.user_id=?','i',[$uid]);
if(msw_is_post()){
    msw_verify_post();$action=(string)($_POST['action']??'');
    if($action==='create'&&!$membership){
        $name=trim((string)($_POST['name']??''));$tag=strtoupper(trim((string)($_POST['tag']??'')));$desc=trim((string)($_POST['description']??''));
        if(!preg_match('/^[A-Za-z0-9 _-]{3,40}$/',$name)||!preg_match('/^[A-Z0-9]{2,6}$/',$tag)) msw_flash('Use a 3–40 character name and 2–6 character alphanumeric tag.','error');
        else{try{$db=msw_db();$db->begin_transaction();msw_stmt('INSERT INTO strike_forces(name,tag,owner_user_id,description) VALUES(?,?,?,?)','ssis',[$name,$tag,$uid,mb_substr($desc,0,500)]);$sid=(int)$db->insert_id;msw_stmt("INSERT INTO strike_force_members(strike_force_id,user_id,role) VALUES(?,?,'commander')",'ii',[$sid,$uid]);$db->commit();msw_console_event_for_user($uid,'SOCIAL','FORCE CREATE','Strike Force ['.$tag.'] '.$name.' established.',['strike_force_id'=>$sid,'tag'=>$tag]);msw_flash('Strike Force established.','success');}catch(Throwable $e){try{msw_db()->rollback();}catch(Throwable $_){}msw_flash('Name or tag is already in use.','error');}}
    }elseif($action==='join'&&!$membership){
        $tag=strtoupper(trim((string)($_POST['tag']??'')));$s=msw_one('SELECT id FROM strike_forces WHERE tag=?','s',[$tag]);
        if(!$s)msw_flash('Strike Force tag not found.','error');else{try{msw_stmt("INSERT INTO strike_force_members(strike_force_id,user_id,role) VALUES(?,?,'member')",'ii',[(int)$s['id'],$uid]);msw_console_event_for_user($uid,'SOCIAL','FORCE JOIN','Joined Strike Force ['.$tag.'].',['strike_force_id'=>(int)$s['id'],'tag'=>$tag]);msw_flash('Joined Strike Force.','success');}catch(Throwable $e){msw_flash('Unable to join Strike Force.','error');}}
    }elseif($action==='leave'&&$membership&&$membership['role']!=='commander'){
        msw_stmt('DELETE FROM strike_force_members WHERE user_id=?','i',[$uid]);msw_console_event_for_user($uid,'SOCIAL','FORCE LEAVE','Left Strike Force ['.(string)$membership['tag'].'].',['strike_force_id'=>(int)$membership['strike_force_id'],'tag'=>(string)$membership['tag']]);msw_flash('Strike Force membership ended.','success');
    }
    msw_redirect('strike_forces.php');
}
$membership=msw_one('SELECT m.*,s.name,s.tag,s.description,s.owner_user_id FROM strike_force_members m JOIN strike_forces s ON s.id=m.strike_force_id WHERE m.user_id=?','i',[$uid]);
$forces=msw_all('SELECT s.*,COUNT(m.user_id) members,COALESCE(SUM(u.base_power),0) power FROM strike_forces s LEFT JOIN strike_force_members m ON m.strike_force_id=s.id LEFT JOIN users u ON u.id=m.user_id GROUP BY s.id ORDER BY power DESC LIMIT 30');
$members=$membership?msw_all('SELECT u.id,u.username,u.base_grade,u.base_power,u.mother_base_key,m.role FROM strike_force_members m JOIN users u ON u.id=m.user_id WHERE m.strike_force_id=? ORDER BY FIELD(m.role,\'commander\',\'officer\',\'member\'),u.username','i',[(int)$membership['strike_force_id']]):[];
$bases=msw_mother_base_catalog();
msw_header('Strike Forces','community.php');msw_alert(msw_flash());
?>
<section class="hero"><div class="eyebrow">ALLIANCE COMMAND STRUCTURE</div><h1>STRIKE <span>FORCES</span></h1><p>Persistent player organizations for commanders operating under a shared banner. Members can visit one another's live Mother Bases and observe synchronized recovered staff and hardware.</p></section>
<div class="grid g2" style="margin-top:18px"><section><?php msw_panel('Your Strike Force','MEMBERSHIP');if($membership):?><span class="grade">[<?=msw_e($membership['tag'])?>]</span><h3><?=msw_e($membership['name'])?></h3><p><?=msw_e($membership['description'])?></p><p>Role: <b><?=msw_e($membership['role'])?></b></p><?php if($membership['role']!=='commander'):?><form method="post"><?=msw_csrf_field()?><button class="danger" name="action" value="leave">Leave Strike Force</button></form><?php endif;?>
<div class="force-member-list"><?php foreach($members as $member):$mb=$bases[$member['mother_base_key']]??$bases['land_dirt'];?><div class="force-member"><div><b><?=msw_e($member['username'])?></b><small><?=msw_e(strtoupper($member['role']))?> · <?=msw_e($member['base_grade'])?> · PWR <?=number_format((int)$member['base_power'])?> · <?=msw_e($mb['name'])?></small></div><div class="actions"><a class="btn small secondary" href="<?=msw_e(msw_url('profile.php?id='.(int)$member['id']))?>">Profile</a><a class="btn small" href="<?=msw_e(msw_url('mother_base.php?owner='.(int)$member['id']))?>"><?=$member['id']===$uid?'Enter Base':'Visit Base'?></a></div></div><?php endforeach;?></div>
<?php else:?><form method="post"><?=msw_csrf_field()?><div class="field"><label>Create Name</label><input name="name" maxlength="40"></div><div class="field"><label>Tag</label><input name="tag" maxlength="6"></div><div class="field"><label>Description</label><textarea name="description" maxlength="500"></textarea></div><button name="action" value="create">Establish Strike Force</button></form><hr style="border-color:#334034"><form method="post"><?=msw_csrf_field()?><div class="field"><label>Join by Tag</label><input name="tag" maxlength="6"></div><button class="secondary" name="action" value="join">Join Force</button></form><?php endif;msw_panel_end();?></section>
<section><?php msw_panel('Strike Force Ranking','NETWORK');?><table><thead><tr><th>Force</th><th>Members</th><th>Power</th></tr></thead><tbody><?php foreach($forces as $s):?><tr><td>[<?=msw_e($s['tag'])?>] <?=msw_e($s['name'])?></td><td><?=intval($s['members'])?></td><td><?=number_format((int)$s['power'])?></td></tr><?php endforeach;?></tbody></table><?php msw_panel_end();?></section></div>
<?php msw_footer(); ?>
