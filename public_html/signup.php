<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
if(msw_user()) msw_redirect('dashboard.php');
$error='';

if(msw_is_post()){
    msw_verify_post();
    $name=trim((string)($_POST['username']??''));
    $password=(string)($_POST['password']??'');
    $character=(string)($_POST['character']??'marco');
    $characters=msw_character_catalog();

    if(!preg_match('/^[A-Za-z0-9_]{3,24}$/',$name)){
        $error='Username must be 3–24 letters, numbers or underscores.';
    }elseif(strlen($password)<10 || strlen($password)>200){
        $error='Password must contain 10–200 characters.';
    }elseif(!isset($characters[$character])){
        $error='Invalid operative selection.';
    }else{
        $db=msw_db();
        $db->begin_transaction();
        try{
            $hash=password_hash($password,PASSWORD_DEFAULT);
            if($hash===false) throw new RuntimeException('Password hashing unavailable.');
            msw_stmt('INSERT INTO users(username,password_hash,character_key) VALUES(?,?,?)','sss',[$name,$hash,$character]);
            $uid=(int)$db->insert_id;
            msw_initialize_player($uid);
            $db->commit();
            session_regenerate_id(true);
            $_SESSION['uid']=$uid;
            msw_redirect('fob_globe.php');
        }catch(mysqli_sql_exception $e){
            $db->rollback();
            $error=$e->getCode()===1062?'That username is already deployed.':'Account creation failed.';
        }catch(Throwable $e){
            $db->rollback();
            $error='Account creation failed.';
        }
    }
}

msw_header('Create Commander');
?>
<div class="authbox">
<?php msw_panel('Create Commander Identity','ENLISTMENT'); ?>
<?php if($error): ?><div class="alert error"><?=msw_e($error)?></div><?php endif; ?>
<form method="post">
    <?=msw_csrf_field()?>
    <div class="field"><label>Commander Username</label><input name="username" maxlength="24" required autocomplete="username"></div>
    <div class="field"><label>Password</label><input type="password" name="password" minlength="10" maxlength="200" required autocomplete="new-password"></div>
    <label>Field Operative Skin</label>
    <div class="character-grid">
    <?php foreach(msw_character_catalog() as $key=>$entry): ?>
        <label class="char-card"><input type="radio" name="character" value="<?=msw_e($key)?>" <?=$key==='marco'?'checked':''?>><img src="<?=msw_e(msw_url($entry['sprite']))?>" alt=""><b><?=msw_e($entry['name'])?></b><small><?=msw_e($entry['game'])?></small></label>
    <?php endforeach; ?>
    </div>
    <div class="alert info" style="margin-top:18px">After account creation, Global FOB Deployment will open automatically. You will choose a continent type on the Earth overview, then select only a base skin coherent with that environment.</div>
    <div class="actions" style="margin-top:16px"><button>Create Account</button><a class="btn secondary" href="<?=msw_e(msw_url('login.php'))?>">Existing Commander</a></div>
</form>
<?php msw_panel_end(); ?>
</div>
<?php msw_footer(); ?>
