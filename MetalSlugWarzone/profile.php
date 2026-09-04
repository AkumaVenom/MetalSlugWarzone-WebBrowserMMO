<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$me=msw_require_user();$uid=(int)$me['id'];

if(msw_is_post()){
    msw_verify_post();
    $action=(string)($_POST['action']??'character');
    if($action==='character'){
        $char=(string)($_POST['character']??'');
        if(isset(msw_character_catalog()[$char])){
            msw_stmt('UPDATE users SET character_key=? WHERE id=?','si',[$char,$uid]);
            $charName=(string)(msw_character_catalog()[$char]['name']??$char);
            msw_console_event_for_user($uid,'PROFILE','OPERATIVE','Field operative changed to '.$charName.'.',['character'=>$char]);
            msw_flash('Field operative updated. Commander level and XP remain attached to your account.','success');
        }
    }elseif($action==='mother_base'){
        $baseKey=(string)($_POST['mother_base']??'');
        if(isset(msw_mother_base_catalog()[$baseKey])){
            $current=msw_one('SELECT mother_base_key FROM users WHERE id=?','i',[$uid]);
            if((string)($current['mother_base_key']??'')!==$baseKey){
                $db=msw_db();$db->begin_transaction();
                try{
                    msw_stmt('UPDATE users SET mother_base_key=? WHERE id=?','si',[$baseKey,$uid]);
                    msw_mb_reset_layout($uid);
                    $db->commit();
                    $baseName=(string)(msw_mother_base_catalog()[$baseKey]['name']??$baseKey);
                    msw_console_event_for_user($uid,'BASE','REDEPLOY','Mother Base deployment changed to '.$baseName.'.',['mother_base'=>$baseKey]);
                    msw_flash('Mother Base deployment changed. Staff and hardware will be safely re-positioned on the selected base without losing any units or progression.','success');
                }catch(Throwable $e){$db->rollback();throw $e;}
            }else msw_flash('That Mother Base is already active.','info');
        }
    }
    msw_redirect('profile.php?id='.$uid);
}

$id=(int)($_GET['id']??$uid);
$u=msw_one('SELECT id,username,character_key,mother_base_key,level,xp,command_rank,base_power,base_grade,is_bot,active_map,map_x,map_y,created_at FROM users WHERE id=?','i',[$id]);
if(!$u){http_response_code(404);exit('Commander not found.');}
$count=msw_one('SELECT COUNT(*) c,SUM(unit_class IN (\'vehicle\',\'air\')) hardware FROM units WHERE owner_user_id=?','i',[$id]);
$chars=msw_character_catalog();$c=$chars[$u['character_key']]??reset($chars);$p=msw_user_progress($u);
$bases=msw_mother_base_catalog();$baseKey=(string)$u['mother_base_key'];if(!isset($bases[$baseKey]))$baseKey='land_dirt';$base=$bases[$baseKey];
$visitRelation=msw_mb_access_relation($uid,$id);$bot=(int)($u['is_bot']??0)===1?msw_bot_row($id):null;
msw_header('Commander Profile');msw_alert(msw_flash());
?>
<div class="grid g2">
<section>
<?php msw_panel('Commander '.$u['username'],'PROFILE');?><div class="profile-commander"><img src="<?=msw_e(msw_url($c['sprite']))?>" alt=""><div class="profile-commander-copy"><div class="actions"><?php if($bot):?><span class="ai-mark">AUTONOMOUS COMMANDER #<?=intval($bot['bot_index'])?></span><?php endif;?><span class="grade"><?=msw_e($u['base_grade'])?></span><span class="badge">LV <?=intval($p['level'])?></span><span class="badge">COMMAND RANK <?=intval($p['command_rank'])?></span></div><h2><?=msw_e($u['username'])?></h2><p><?=msw_e($c['name'])?> · Base Power <?=number_format((int)$u['base_power'])?> · <?=intval($count['c']??0)?> recovered units</p><div class="xpbar wide"><i style="width:<?=round((float)$p['percent'],2)?>%"></i></div><small class="xp-caption"><?=number_format((int)$p['current_xp'])?> / <?=number_format((int)$p['required_xp'])?> XP toward Lv <?=intval($p['level'])+1?> · <?=number_format((int)$p['total_xp'])?> total XP</small><small>Enlisted <?=msw_e($u['created_at'])?></small></div></div><?php msw_panel_end(); ?>
<?php if($bot):?>
<?php msw_panel('Autonomous Commander State','PERSISTENT AI NETWORK');?><div class="grid g3"><?php msw_stat('Field Battles',number_format((int)$bot['field_battles']),'Wins '.number_format((int)$bot['field_wins']));msw_stat('Recoveries',number_format((int)$bot['recoveries']),'Vehicles '.number_format((int)$bot['vehicle_recoveries']));msw_stat('FOB Raids',number_format((int)$bot['fob_attacks']),'Wins '.number_format((int)$bot['fob_wins']));msw_stat('PvP Battles',number_format((int)$bot['pvp_battles']),'Wins '.number_format((int)$bot['pvp_wins']));msw_stat('Warzone',msw_map_catalog()[(string)$u['active_map']]['name']??'Unknown','X '.intval($u['map_x']).' · Y '.intval($u['map_y']));msw_stat('Behavior',ucfirst((string)$bot['personality']),(string)$bot['activity']);?></div><div class="actions" style="margin-top:12px"><form method="post" action="<?=msw_e(msw_url('pvp.php'))?>"><?=msw_csrf_field()?><input type="hidden" name="opponent_id" value="<?=$id?>"><input type="hidden" name="match_mode" value="live_ai"><button>Start Live AI Battle</button></form><form method="post" action="<?=msw_e(msw_url('pvp.php'))?>"><?=msw_csrf_field()?><input type="hidden" name="opponent_id" value="<?=$id?>"><input type="hidden" name="match_mode" value="snapshot"><button class="secondary">Commander Snapshot Battle</button></form></div><?php msw_panel_end(); ?>
<?php endif;?>
<?php msw_panel('Physical Mother Base','FOB DEPLOYMENT');?><div class="profile-base-summary"><img src="<?=msw_e(msw_url($base['image']))?>" alt=""><div><span class="badge"><?=msw_e($base['type'])?></span><h3><?=msw_e($base['name'])?></h3><p><?=msw_e($base['climate'])?> · <?=intval($count['hardware']??0)?> captured hardware units in the roster.</p><?php if($visitRelation!==null):?><a class="btn" href="<?=msw_e(msw_url('mother_base.php?owner='.$id))?>"><?=$id===$uid?'Enter Your Mother Base':'Visit Mother Base'?></a><?php else:?><span class="badge">Friend or Strike Force access required</span><?php endif;?></div></div><?php msw_panel_end(); ?>
</section>
<?php if($id===$uid && !$bot):?><section>
<?php msw_panel('Change Field Operative','ACCOUNT OPTIONS');?><p class="muted-copy">Changing the visible operative does not reset commander level, XP, resources, recovered units or Mother Base progression.</p><form method="post"><?=msw_csrf_field()?><input type="hidden" name="action" value="character"><div class="character-grid"><?php foreach($chars as $key=>$ch):?><label class="char-card"><input type="radio" name="character" value="<?=msw_e($key)?>" <?=$u['character_key']===$key?'checked':''?>><img src="<?=msw_e(msw_url($ch['sprite']))?>" alt=""><b><?=msw_e($ch['name'])?></b><small><?=msw_e($ch['game'])?></small></label><?php endforeach;?></div><button style="margin-top:12px">Apply Character</button></form><?php msw_panel_end(); ?>
<?php msw_panel('Select Mother Base','ACCOUNT OPTIONS');?><p class="muted-copy">Your selection controls the physical base map used by you and every authorized visitor. Changing it never deletes staff, vehicles, resources, sector levels, FOB history or progression.</p><form method="post"><?=msw_csrf_field()?><input type="hidden" name="action" value="mother_base"><div class="mother-base-choice-grid compact"><?php foreach($bases as $key=>$entry):?><label class="mother-base-choice"><input type="radio" name="mother_base" value="<?=msw_e($key)?>" <?=$baseKey===$key?'checked':''?>><img src="<?=msw_e(msw_url($entry['image']))?>" alt=""><span><b><?=msw_e($entry['name'])?></b><small><?=msw_e($entry['type'])?> · <?=msw_e($entry['climate'])?></small></span></label><?php endforeach;?></div><button style="margin-top:12px">Deploy Selected Mother Base</button></form><?php msw_panel_end(); ?>
</section><?php endif;?>
</div>
<?php msw_footer(); ?>
