<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();
$uid=(int)$user['id'];
$selected=trim((string)($_GET['to']??$_POST['to']??''));

if(msw_is_post()){
    msw_verify_post();
    $body=trim((string)($_POST['body']??''));
    $target=msw_one('SELECT id,username FROM users WHERE username=? AND is_bot=0','s',[$selected]);
    if(!$target||(int)$target['id']===$uid){
        msw_flash('Recipient unavailable.','error');
    }elseif(!msw_one('SELECT 1 FROM friends WHERE user_id=? AND friend_user_id=?','ii',[$uid,(int)$target['id']])){
        msw_flash('Direct messages are limited to established friends.','error');
    }elseif($body===''||mb_strlen($body)>1000){
        msw_flash('Message must contain 1–1000 characters.','error');
    }else{
        $recent=msw_one('SELECT COUNT(*) c FROM direct_messages WHERE sender_user_id=? AND created_at>=DATE_SUB(NOW(),INTERVAL 60 SECOND)','i',[$uid]);
        if((int)($recent['c']??0)>=20){
            msw_flash('Transmission rate limit reached. Try again shortly.','warning');
        }else{
            msw_stmt('INSERT INTO direct_messages(sender_user_id,receiver_user_id,body) VALUES(?,?,?)','iis',[$uid,(int)$target['id'],$body]);
            msw_flash('Transmission sent.','success');
        }
    }
    msw_redirect('messages.php?to='.urlencode($selected));
}

$friends=msw_all('SELECT u.id,u.username FROM friends f JOIN users u ON u.id=f.friend_user_id WHERE f.user_id=? ORDER BY u.username','i',[$uid]);
$thread=[];
$target=null;
if($selected!==''){
    $candidate=msw_one('SELECT id,username FROM users WHERE username=? AND is_bot=0','s',[$selected]);
    if($candidate && msw_one('SELECT 1 FROM friends WHERE user_id=? AND friend_user_id=?','ii',[$uid,(int)$candidate['id']])){
        $target=$candidate;
        $thread=msw_all(
            'SELECT m.*,s.username sender FROM direct_messages m JOIN users s ON s.id=m.sender_user_id WHERE (m.sender_user_id=? AND m.receiver_user_id=?) OR (m.sender_user_id=? AND m.receiver_user_id=?) ORDER BY m.id DESC LIMIT 80',
            'iiii',
            [$uid,(int)$target['id'],(int)$target['id'],$uid]
        );
        msw_stmt('UPDATE direct_messages SET read_at=COALESCE(read_at,NOW()) WHERE sender_user_id=? AND receiver_user_id=?','ii',[(int)$target['id'],$uid]);
    }
}

msw_header('Direct Messages','community.php');
msw_alert(msw_flash());
?>
<div class="grid g2">
<section><?php msw_panel('Secure Comms','FRIENDS'); ?>
<div class="actions"><?php foreach($friends as $friend): ?><a class="btn small secondary" href="<?=msw_e(msw_url('messages.php?to='.urlencode($friend['username'])))?>"><?=msw_e($friend['username'])?></a><?php endforeach; ?></div>
<?php if(!$friends): ?><div class="empty">Establish a friend link before opening direct comms.</div><?php endif; ?>
<?php msw_panel_end(); ?></section>
<section><?php msw_panel($target?'Channel: '.$target['username']:'Select a Commander','DIRECT MESSAGE'); ?>
<?php if($target): ?>
<div class="battle-log" style="max-height:360px"><?php foreach(array_reverse($thread) as $message): ?><div><b><?=msw_e($message['sender'])?>:</b> <?=msw_e($message['body'])?> <small><?=msw_e($message['created_at'])?></small></div><?php endforeach; ?></div>
<form method="post" style="margin-top:10px"><?=msw_csrf_field()?><input type="hidden" name="to" value="<?=msw_e($target['username'])?>"><textarea name="body" maxlength="1000" rows="4" required placeholder="Transmission…"></textarea><button style="margin-top:8px">Send Message</button></form>
<?php else: ?><div class="empty">Choose an established friend to open a persistent message channel.</div><?php endif; ?>
<?php msw_panel_end(); ?></section>
</div>
<?php msw_footer(); ?>
