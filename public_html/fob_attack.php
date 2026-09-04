<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();$uid=(int)$user['id'];
if(!msw_is_post()){http_response_code(405);exit('Method Not Allowed');}
msw_verify_post();$defenderId=(int)($_POST['defender_id']??0);$return=(string)($_POST['return']??'fob_infiltration.php');
if(!in_array($return,['fob_infiltration.php','fob_world.php','fob_target.php'],true))$return='fob_infiltration.php';
try{
    $target=msw_fob_target_row($uid,$defenderId);if(!$target)throw new RuntimeException('Invalid FOB target for this world instance.');
    $raidId=msw_fob_resolve_direct_raid($uid,$defenderId,'direct');
    msw_redirect('fob_result.php?id='.$raidId);
}catch(Throwable $e){
    msw_flash($e->getMessage(),'error');
    if($return==='fob_target.php')msw_redirect('fob_target.php?id='.$defenderId);
    msw_redirect($return);
}
