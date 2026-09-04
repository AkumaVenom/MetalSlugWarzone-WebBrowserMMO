<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();
$uid=(int)$user['id'];

function msw_friend_link(int $a,int $b): void {
    msw_stmt('INSERT IGNORE INTO friends(user_id,friend_user_id) VALUES(?,?),(?,?)','iiii',[$a,$b,$b,$a]);
}

if(msw_is_post()){
    msw_verify_post();
    $action=(string)($_POST['action']??'');

    if($action==='send'){
        $name=trim((string)($_POST['username']??''));
        $target=msw_one('SELECT id,username FROM users WHERE username=? AND is_bot=0','s',[$name]);
        if(!$target||(int)$target['id']===$uid){
            msw_flash('Commander not found.','error');
        }elseif(msw_one('SELECT 1 FROM friends WHERE user_id=? AND friend_user_id=?','ii',[$uid,(int)$target['id']])){
            msw_flash('Commander is already a friend.','warning');
        }else{
            $targetId=(int)$target['id'];
            $reciprocal=msw_one("SELECT id FROM friend_requests WHERE sender_user_id=? AND receiver_user_id=? AND status='pending'",'ii',[$targetId,$uid]);
            if($reciprocal){
                $db=msw_db();
                $db->begin_transaction();
                try{
                    msw_stmt("UPDATE friend_requests SET status='accepted' WHERE id=?",'i',[(int)$reciprocal['id']]);
                    msw_friend_link($uid,$targetId);
                    $db->commit();
                    msw_console_event_for_user($uid,'SOCIAL','FRIEND', 'Friend link established with '.(string)$target['username'].'.',['friend_id'=>$targetId,'friend'=>(string)$target['username']]);msw_flash('Mutual friend request detected. Friend link established.','success');
                }catch(Throwable $e){$db->rollback();throw $e;}
            }else{
                try{
                    msw_stmt("INSERT INTO friend_requests(sender_user_id,receiver_user_id,status) VALUES(?,?,'pending') ON DUPLICATE KEY UPDATE status='pending',created_at=NOW()",'ii',[$uid,$targetId]);
                    msw_console_event_for_user($uid,'SOCIAL','FRIEND REQ','Friend request sent to '.(string)$target['username'].'.',['target_id'=>$targetId,'target'=>(string)$target['username']]);msw_flash('Friend request transmitted.','success');
                }catch(Throwable $e){
                    msw_flash('Request could not be sent.','error');
                }
            }
        }
    }elseif($action==='accept'){
        $requestId=(int)($_POST['request_id']??0);
        $request=msw_one("SELECT * FROM friend_requests WHERE id=? AND receiver_user_id=? AND status='pending'",'ii',[$requestId,$uid]);
        if($request){
            $db=msw_db();
            $db->begin_transaction();
            try{
                msw_stmt("UPDATE friend_requests SET status='accepted' WHERE id=?",'i',[$requestId]);
                msw_friend_link($uid,(int)$request['sender_user_id']);
                $db->commit();
                $friendUser=msw_one('SELECT username FROM users WHERE id=? AND is_bot=0','i',[(int)$request['sender_user_id']]);
                msw_console_event_for_user($uid,'SOCIAL','FRIEND', 'Friend request accepted'.($friendUser?' from '.(string)$friendUser['username']:'').'.',['friend_id'=>(int)$request['sender_user_id']]);
                msw_flash('Friend link established.','success');
            }catch(Throwable $e){$db->rollback();throw $e;}
        }
    }elseif($action==='decline'){
        $declineId=(int)($_POST['request_id']??0);
        $declined=msw_one("SELECT sender_user_id FROM friend_requests WHERE id=? AND receiver_user_id=? AND status='pending'",'ii',[$declineId,$uid]);
        msw_stmt("UPDATE friend_requests SET status='declined' WHERE id=? AND receiver_user_id=?",'ii',[$declineId,$uid]);
        if($declined) msw_console_event_for_user($uid,'SOCIAL','DECLINE','Friend request declined.',['sender_id'=>(int)$declined['sender_user_id']]);
        msw_flash('Friend request declined.','info');
    }
    msw_redirect('friends.php');
}

$requests=msw_all("SELECT r.id,u.username FROM friend_requests r JOIN users u ON u.id=r.sender_user_id WHERE r.receiver_user_id=? AND r.status='pending' ORDER BY r.id DESC",'i',[$uid]);
$friends=msw_all('SELECT u.id,u.username,u.base_grade,u.base_power,u.mother_base_key,u.last_seen FROM friends f JOIN users u ON u.id=f.friend_user_id WHERE f.user_id=? ORDER BY u.username','i',[$uid]);
msw_header('Friends','community.php');
msw_alert(msw_flash());
?>
<div class="grid g2">
<?php msw_panel('Friend Network',count($friends).' LINKS'); ?>
<form method="post" class="actions" style="margin-bottom:14px"><?=msw_csrf_field()?><input name="username" maxlength="24" placeholder="Commander username" style="max-width:280px"><button name="action" value="send">Send Request</button></form>
<table><thead><tr><th>Commander</th><th>Grade</th><th>Power</th><th>Mother Base</th><th>Actions</th></tr></thead><tbody>
<?php $mbCatalog=msw_mother_base_catalog(); foreach($friends as $friend): $mb=$mbCatalog[$friend['mother_base_key']]??$mbCatalog['land_dirt']; ?><tr><td><a href="<?=msw_e(msw_url('profile.php?id='.(int)$friend['id']))?>" style="color:#e8d59a"><?=msw_e($friend['username'])?></a></td><td><?=msw_e($friend['base_grade'])?></td><td><?=number_format((int)$friend['base_power'])?></td><td><?=msw_e($mb['name'])?></td><td><div class="actions"><a class="btn small" href="<?=msw_e(msw_url('mother_base.php?owner='.(int)$friend['id']))?>">Visit Base</a><a class="btn small secondary" href="<?=msw_e(msw_url('messages.php?to='.urlencode($friend['username'])))?>">Message</a></div></td></tr><?php endforeach; ?>
</tbody></table>
<?php msw_panel_end(); ?>
<?php msw_panel('Incoming Requests','PENDING'); ?>
<?php foreach($requests as $request): ?><div class="feature"><b><?=msw_e($request['username'])?></b><form method="post" class="actions" style="margin-top:8px"><?=msw_csrf_field()?><input type="hidden" name="request_id" value="<?=intval($request['id'])?>"><button class="small" name="action" value="accept">Accept</button><button class="small danger" name="action" value="decline">Decline</button></form></div><?php endforeach; ?>
<?php if(!$requests): ?><div class="empty">No pending friend requests.</div><?php endif; ?>
<?php msw_panel_end(); ?>
</div>
<?php msw_footer(); ?>
