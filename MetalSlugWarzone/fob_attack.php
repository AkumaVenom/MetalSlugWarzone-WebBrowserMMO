<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();$uid=(int)$user['id'];if(!msw_is_post()){http_response_code(405);exit('Method Not Allowed');}
msw_verify_post();
$defenderId=(int)($_POST['defender_id']??0);
$worldId=(int)($_POST['world_id']??0);
$retaliationRaidId=(int)($_POST['retaliation_raid_id']??0);
$return=(string)($_POST['return']??'fob_infiltration.php');
if(!in_array($return,['fob.php','fob_infiltration.php','fob_world.php','fob_target.php'],true))$return='fob.php';
try{
    $target=msw_fob_target_row($uid,$defenderId,$worldId>0?$worldId:null);
    if(!$target)throw new RuntimeException('Invalid FOB target for the selected global shard.');
    if($retaliationRaidId>0){
        $source=msw_fob_retaliation_source($uid,$retaliationRaidId);
        if(!$source||(int)$source['target_id']!==$defenderId)throw new RuntimeException('That retaliation authorization is no longer valid.');
        if(!empty($source['retaliation_raid_id']))throw new RuntimeException('That incoming raid has already been retaliated against.');
        $raidId=msw_fob_resolve_direct_raid($uid,$defenderId,'retaliation',$retaliationRaidId);
    }else{
        $raidId=msw_fob_resolve_direct_raid($uid,$defenderId,'direct');
    }
    msw_redirect('fob_result.php?id='.$raidId);
}catch(Throwable $e){
    msw_flash($e->getMessage(),'error');
    if($return==='fob_target.php')msw_redirect('fob_target.php?id='.$defenderId.'&world='.$worldId);
    if($return==='fob_world.php')msw_redirect('fob_world.php?world='.$worldId);
    if($return==='fob_infiltration.php')msw_redirect('fob_infiltration.php?world='.$worldId);
    msw_redirect('fob.php');
}
