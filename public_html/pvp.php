<?php
declare(strict_types=1);
require __DIR__.'/includes/pvp_engine.php';
require_once __DIR__.'/includes/ui.php';
$u=msw_require_user();$uid=(int)$u['id'];
msw_bot_simulation_pulse(null,6);
if(msw_is_post()){
    msw_verify_post();$opp=(int)($_POST['opponent_id']??0);$mode=(string)($_POST['match_mode']??'live');
    $target=msw_one('SELECT id,username,is_bot FROM users WHERE id=?','i',[$opp]);
    if($opp<=0||$opp===$uid||!$target){msw_flash('Invalid PvP opponent.','error');msw_redirect('pvp.php');}
    $isBot=(int)$target['is_bot']===1;if(!$isBot)$mode='live';elseif(!in_array($mode,['live_ai','snapshot'],true))$mode='live_ai';
    $existing=msw_one("SELECT id FROM pvp_matches WHERE status='active' AND ((player1_id=? AND player2_id=?) OR (player1_id=? AND player2_id=?)) ORDER BY id DESC LIMIT 1",'iiii',[$uid,$opp,$opp,$uid]);
    if($existing)msw_redirect('pvp_match.php?id='.(int)$existing['id']);
    try{$id=msw_pvp_create_match($uid,$opp,$mode);msw_console_event_for_user($uid,'PVP','MATCH START','PvP match opened against '.(string)$target['username'].'.',['match_id'=>$id,'opponent_id'=>$opp,'opponent'=>(string)$target['username'],'mode'=>$mode]);msw_redirect('pvp_match.php?id='.$id);}catch(Throwable $e){msw_flash($e->getMessage(),'error');msw_redirect('pvp.php');}
}
$players=msw_all("SELECT u.id,u.username,u.base_grade,u.base_power,u.is_bot,b.bot_index,b.activity FROM users u LEFT JOIN bot_commanders b ON b.user_id=u.id WHERE u.id<>? AND (u.is_bot=1 OR u.last_seen>=DATE_SUB(NOW(),INTERVAL 90 SECOND)) ORDER BY u.is_bot ASC,u.last_seen DESC,b.bot_index ASC,u.base_power DESC LIMIT 60",'i',[$uid]);
$matches=msw_all("SELECT p.*,u1.username p1,u2.username p2,u1.is_bot b1,u2.is_bot b2 FROM pvp_matches p JOIN users u1 ON u1.id=p.player1_id JOIN users u2 ON u2.id=p.player2_id WHERE p.player1_id=? OR p.player2_id=? ORDER BY p.id DESC LIMIT 16",'ii',[$uid,$uid]);
msw_header('Live PvP','pvp.php');msw_alert(msw_flash());
?>
<section class="hero"><div class="eyebrow">SYNCHRONIZED COMMANDER BATTLE</div><h1>LIVE <span>PvP</span></h1><p>Human commanders use version-locked alternating turns. Persistent AI commanders support both live server-driven PvP and immutable snapshot battles; their turns are committed by the server and never require a hidden bot login session.</p></section>
<div class="grid g2" style="margin-top:18px">
<section><?php msw_panel('Challenge Commander','ONLINE / AUTONOMOUS NETWORK');?><table><thead><tr><th>Commander</th><th>Grade</th><th>Power</th><th>Battle</th></tr></thead><tbody>
<?php foreach($players as $p):$bot=(int)$p['is_bot']===1;?><tr><td><a href="<?=msw_e(msw_url('profile.php?id='.(int)$p['id']))?>" style="color:#e8d59a"><?=msw_e($p['username'])?></a> <?=$bot?'<span class="ai-mark">AI</span>':''?></td><td><span class="grade"><?=msw_e($p['base_grade'])?></span></td><td><?=number_format((int)$p['base_power'])?></td><td>
<?php if($bot):?><div class="actions"><form method="post"><?=msw_csrf_field()?><input type="hidden" name="opponent_id" value="<?=intval($p['id'])?>"><input type="hidden" name="match_mode" value="live_ai"><button class="small">Live AI</button></form><form method="post"><?=msw_csrf_field()?><input type="hidden" name="opponent_id" value="<?=intval($p['id'])?>"><input type="hidden" name="match_mode" value="snapshot"><button class="small secondary">Snapshot</button></form></div><?php else:?><form method="post"><?=msw_csrf_field()?><input type="hidden" name="opponent_id" value="<?=intval($p['id'])?>"><input type="hidden" name="match_mode" value="live"><button class="small">Open Match</button></form><?php endif;?>
</td></tr><?php endforeach;?></tbody></table><?php msw_panel_end();?></section>
<section><?php msw_panel('PvP Match Ledger','ACTIVE / RECENT');?><table><thead><tr><th>Match</th><th>Mode</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($matches as $m):?><tr><td><?=msw_e($m['p1'])?><?=((int)$m['b1']===1?' [AI]':'')?> vs <?=msw_e($m['p2'])?><?=((int)$m['b2']===1?' [AI]':'')?></td><td><?=msw_e(str_replace('_',' ',(string)$m['match_mode']))?></td><td><?=msw_e($m['status'])?></td><td><a class="btn small secondary" href="<?=msw_e(msw_url('pvp_match.php?id='.(int)$m['id']))?>">View</a></td></tr><?php endforeach;?></tbody></table><?php msw_panel_end();?></section>
</div><?php msw_footer(); ?>
