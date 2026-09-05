<?php
declare(strict_types=1);

$remote=(string)($_SERVER['REMOTE_ADDR']??'');
$local=in_array($remote,['127.0.0.1','::1','::ffff:127.0.0.1'],true);
if(!$local){http_response_code(404);exit;}
@set_time_limit(0); // Local-only schema/population repair may seed 1,000 persistent commanders.

$config=require __DIR__.'/config/app.php';
date_default_timezone_set((string)$config['timezone']);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');
session_start(['cookie_httponly'=>true,'cookie_samesite'=>'Strict']);
if(empty($_SESSION['setup_csrf'])) $_SESSION['setup_csrf']=bin2hex(random_bytes(32));
$csrf=(string)$_SESSION['setup_csrf'];
$message='';$kind='info';$report=[];

function setup_server(array $cfg): mysqli {
    mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
    $db=$cfg['db'];
    $mysqli=new mysqli((string)$db['host'],(string)$db['user'],(string)$db['pass'],'',(int)$db['port']);
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}
function setup_db(mysqli $mysqli,string $name): void {
    if(!preg_match('/^[A-Za-z0-9_]+$/',$name)) throw new RuntimeException('Unsafe database name.');
    $mysqli->query("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $mysqli->select_db($name);
}

if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    if(!hash_equals($csrf,(string)($_POST['csrf']??''))){http_response_code(419);exit('Setup session validation failed.');}
    try{
        $mysqli=setup_server($config);
        $name=(string)$config['db']['name'];
        $action=(string)($_POST['action']??'');
        require_once __DIR__.'/includes/schema.php';

        if($action==='fresh'){
            if((string)($_POST['fresh_confirm']??'')!=='RESET') throw new RuntimeException('Fresh Install requires the confirmation word RESET.');
            if(!preg_match('/^[A-Za-z0-9_]+$/',$name)) throw new RuntimeException('Unsafe database name.');
            $mysqli->query("DROP DATABASE IF EXISTS `{$name}`");
            setup_db($mysqli,$name);
            msw_install_schema($mysqli);
            $message='Fresh installation complete. The game database was recreated at schema revision '.MSW_SCHEMA_REVISION.'.';
            $kind='success';
        }elseif($action==='repair'){
            setup_db($mysqli,$name);
            msw_install_schema($mysqli);
            $message='Update / Repair complete. Missing objects and additive migrations were reconciled at schema revision '.MSW_SCHEMA_REVISION.'.';
            $kind='success';
        }elseif($action==='confirm'){
            setup_db($mysqli,$name);
            $required=['schema_meta','users','bot_commanders','player_resources','inventory','base_sectors','units','security_backup_slots','mother_base_presence','mother_base_unit_positions','encounters','mission_progress','dispatch_missions','base_projects','fob_worlds','fob_world_memberships','fob_strike_dispatches','fob_raids','pvp_matches','friend_requests','friends','direct_messages','strike_forces','strike_force_members','login_attempts'];
            foreach($required as $table){
                $safe=$mysqli->real_escape_string($table);
                $result=$mysqli->query("SHOW TABLES LIKE '{$safe}'");
                $report[$table]=$result->num_rows===1?'OK':'MISSING';
            }
            $revision='missing';
            try{
                $row=$mysqli->query("SELECT meta_value FROM schema_meta WHERE meta_key='schema_revision'")->fetch_assoc();
                $revision=$row['meta_value']??'missing';
            }catch(Throwable $_){}
            $report['schema_revision']=$revision;
            $report['expected_revision']=(string)MSW_SCHEMA_REVISION;
            try{$botRow=$mysqli->query("SELECT COUNT(*) c,COUNT(DISTINCT bot_index) indexes,MIN(bot_index) min_i,MAX(bot_index) max_i FROM bot_commanders WHERE enabled=1")->fetch_assoc();$report['autonomous_commanders']=((int)($botRow['c']??0)===1000&&(int)($botRow['indexes']??0)===1000&&(int)($botRow['min_i']??0)===1&&(int)($botRow['max_i']??0)===1000)?'OK · 1000 persistent':'ERROR · '.(int)($botRow['c']??0);}catch(Throwable $_){$report['autonomous_commanders']='MISSING';}
            try{$dist=$mysqli->query("SELECT u.active_map,COUNT(*) c FROM bot_commanders b JOIN users u ON u.id=b.user_id WHERE b.enabled=1 GROUP BY u.active_map ORDER BY u.active_map")->fetch_all(MYSQLI_ASSOC);$counts=array_map(fn($r)=>(int)$r['c'],$dist);$balanced=count($dist)===count(msw_map_catalog())&&array_sum($counts)===1000&&$counts&&(max($counts)-min($counts)<=1);$report['autonomous_distribution']=$balanced?'OK · balanced across '.count($dist).' warzones':'ERROR · distribution';}catch(Throwable $_){$report['autonomous_distribution']='MISSING';}
            try{$skinRows=$mysqli->query("SELECT u.active_map,u.character_key,COUNT(*) c FROM bot_commanders b JOIN users u ON u.id=b.user_id WHERE b.enabled=1 AND b.bot_index BETWEEN 1 AND 1000 GROUP BY u.active_map,u.character_key")->fetch_all(MYSQLI_ASSOC);$expectedSkins=array_keys(msw_character_catalog());$expectedMaps=array_keys(msw_map_catalog());$perMap=[];$global=array_fill_keys($expectedSkins,0);foreach($skinRows as $r){$map=(string)$r['active_map'];$skin=(string)$r['character_key'];$count=(int)$r['c'];$perMap[$map][$skin]=$count;if(isset($global[$skin]))$global[$skin]+=$count;}$mixed=true;$mapDetail=[];foreach($expectedMaps as $map){$counts=[];foreach($expectedSkins as $skin)$counts[$skin]=(int)($perMap[$map][$skin]??0);$total=array_sum($counts);$spread=$counts?(max($counts)-min($counts)):999;$mapOk=$total>0&&min($counts)>0&&$spread<=1;$mixed=$mixed&&$mapOk;$mapDetail[]=$map.' '.$total.' ['.implode('/',array_values($counts)).']';}$globalOk=array_sum($global)===1000&&min($global)>0&&(max($global)-min($global)<=1);$detail=implode(' · ',$mapDetail);$report['autonomous_skin_variety']=($mixed&&$globalOk)?'OK · every warzone mixed · '.$detail:'ERROR · '.$detail;}catch(Throwable $_){$report['autonomous_skin_variety']='MISSING';}
                        try{$fobBots=$mysqli->query("SELECT COUNT(*) c,COUNT(DISTINCT m.user_id) users_count,COUNT(DISTINCT CONCAT(m.world_id,':',m.slot_index)) slots FROM bot_commanders b JOIN fob_world_memberships m ON m.user_id=b.user_id WHERE b.enabled=1 AND b.bot_index BETWEEN 1 AND 1000")->fetch_assoc();$ok=(int)($fobBots['c']??0)===1000&&(int)($fobBots['users_count']??0)===1000&&(int)($fobBots['slots']??0)===1000;$report['autonomous_fob_memberships']=$ok?'OK · 1000 persistent unique slots':'ERROR · '.(int)($fobBots['c']??0).' memberships';}catch(Throwable $_){$report['autonomous_fob_memberships']='MISSING';}
            try{$biomeRows=$mysqli->query("SELECT w.biome_key,COUNT(*) c,COUNT(DISTINCT w.id) shards FROM fob_world_memberships m JOIN fob_worlds w ON w.id=m.world_id JOIN bot_commanders b ON b.user_id=m.user_id WHERE b.enabled=1 AND b.bot_index BETWEEN 1 AND 1000 GROUP BY w.biome_key ORDER BY w.biome_key")->fetch_all(MYSQLI_ASSOC);$expected=array_keys(msw_fob_biome_catalog());$seen=[];$balanced=true;$detail=[];foreach($biomeRows as $r){$seen[]=(string)$r['biome_key'];$count=(int)$r['c'];$balanced=$balanced&&$count===200;$detail[]=$r['biome_key'].' '.$count.' / '.(int)$r['shards'].' shards';}$balanced=$balanced&&count($biomeRows)===count($expected)&&!array_diff($expected,$seen);$report['autonomous_fob_distribution']=$balanced?'OK · '.implode(' · ',$detail):'ERROR · '.implode(' · ',$detail);}catch(Throwable $_){$report['autonomous_fob_distribution']='MISSING';}
            try{$dupe=$mysqli->query("SELECT COUNT(*) c FROM (SELECT world_id,slot_index,COUNT(*) n FROM fob_world_memberships GROUP BY world_id,slot_index HAVING n>1) q")->fetch_assoc();$report['fob_slot_collision_guard']=((int)($dupe['c']??0)===0)?'OK · zero duplicate world slots':'ERROR · duplicate slots';}catch(Throwable $_){$report['fob_slot_collision_guard']='MISSING';}
            try{$badBackup=$mysqli->query("SELECT COUNT(*) c FROM security_backup_slots s LEFT JOIN units u ON u.id=s.unit_id AND u.owner_user_id=s.user_id WHERE s.slot_index NOT IN (1,2) OR u.id IS NULL OR u.assignment<>'security' OR u.unit_class NOT IN ('infantry','heavy_infantry')")->fetch_assoc();$report['security_backup_integrity']=((int)($badBackup['c']??0)===0)?'OK · valid party support slots':'ERROR · invalid security backup rows';}catch(Throwable $_){$report['security_backup_integrity']='MISSING';}
            try{
                $rows=$mysqli->query("SELECT m.world_id,m.slot_index,m.x,m.y,w.biome_key,w.shard_index FROM fob_world_memberships m JOIN fob_worlds w ON w.id=m.world_id ORDER BY m.world_id,m.slot_index")->fetch_all(MYSQLI_ASSOC);
                $layoutOk=true;$clearOk=true;$byWorld=[];$checked=0;[$clearX,$clearY]=msw_fob_slot_clearance();
                foreach($rows as $r){
                    [$expectedX,$expectedY]=msw_fob_slot_position((int)$r['slot_index'],(string)$r['biome_key'],(int)$r['shard_index']);
                    if((int)$r['x']!==$expectedX||(int)$r['y']!==$expectedY)$layoutOk=false;
                    $byWorld[(int)$r['world_id']][]=[(int)$r['x'],(int)$r['y']];$checked++;
                }
                foreach($byWorld as $points){
                    $n=count($points);
                    for($i=0;$i<$n&&$clearOk;$i++)for($j=$i+1;$j<$n;$j++){
                        if(abs($points[$i][0]-$points[$j][0])<$clearX&&abs($points[$i][1]-$points[$j][1])<$clearY){$clearOk=false;break;}
                    }
                    if(!$clearOk)break;
                }
                $report['fob_spatial_distribution']=($layoutOk&&$clearOk)?'OK · '.$checked.' memberships on irregular collision-free anchors':'ERROR · run Update / Repair';
            }catch(Throwable $_){$report['fob_spatial_distribution']='MISSING';}
            $message='Installation confirmation completed.';
            $hasSetupError=false;foreach($report as $value){$text=(string)$value;if($text==='MISSING'||str_starts_with($text,'ERROR')){$hasSetupError=true;break;}}$kind=($hasSetupError||(string)$revision!==(string)MSW_SCHEMA_REVISION)?'error':'success';
        }else{
            throw new RuntimeException('Unknown setup action.');
        }
    }catch(Throwable $e){
        $message='Setup error: '.$e->getMessage();
        $kind='error';
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Metal Slug Warzone · Local Setup</title><link rel="stylesheet" href="assets/css/msw.css"></head><body><main class="shell">
<section class="hero" style="margin-top:35px"><div class="eyebrow">LOCALHOST INSTALLATION CONSOLE</div><h1>METAL SLUG WARZONE <span>SETUP</span></h1><p>This route intentionally returns HTTP 404 to every non-loopback client and does not trust proxy-forwarded IP headers. Database installation controls therefore remain visible only on the machine running PHP.</p></section>
<?php if($message): ?><div class="alert <?=$kind?>" style="margin-top:18px"><?=htmlspecialchars($message,ENT_QUOTES,'UTF-8')?></div><?php endif; ?>
<div class="grid g3" style="margin-top:18px">
<section class="panel"><div class="panel-body"><h3>Fresh Install</h3><p>Destructive local reset. Type <b>RESET</b> to deliberately recreate the configured game database.</p><form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf,ENT_QUOTES,'UTF-8')?>"><input name="fresh_confirm" autocomplete="off" placeholder="RESET" style="margin-bottom:8px"><button class="danger" name="action" value="fresh">Fresh Install</button></form></div></section>
<section class="panel"><div class="panel-body"><h3>Update / Repair</h3><p>Non-destructive reconciliation. Creates missing tables and applies the supported additive schema migration path.</p><form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf,ENT_QUOTES,'UTF-8')?>"><button name="action" value="repair">Update / Repair</button></form></div></section>
<section class="panel"><div class="panel-body"><h3>Confirm Installation</h3><p>Read-only health check for every required production table and the current schema revision.</p><form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf,ENT_QUOTES,'UTF-8')?>"><button class="secondary" name="action" value="confirm">Confirm Installation</button></form></div></section>
</div>
<?php if($report): ?><section class="panel"><div class="panel-body"><table><thead><tr><th>Component</th><th>Status</th></tr></thead><tbody><?php foreach($report as $key=>$value): ?><tr><td><?=htmlspecialchars($key,ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8')?></td></tr><?php endforeach; ?></tbody></table></div></section><?php endif; ?>
<div class="actions"><a class="btn secondary" href="index.php">Open Game</a></div>
</main></body></html>
