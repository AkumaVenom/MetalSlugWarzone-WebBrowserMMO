<?php
declare(strict_types=1);
// Started by Windows automatic scheduling using php-win.exe. Never an HTTP job.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/world_background.php';
$local=msw_background_read(msw_background_config_path());
if(!$local||empty($local['installed'])||($local['game_root']??'')!==dirname(__DIR__))exit(2);
$lock=fopen(__DIR__.'/world_service.local.lock','c');
if(!$lock||!flock($lock,LOCK_EX|LOCK_NB))exit(0);
if(!msw_background_apache_running((int)$local['apache_port']))exit(0);
foreach(['host','port','name','user'] as $key)putenv('MSW_DB_'.strtoupper($key).'='.(string)$local['db'][$key]);
$generation=(string)$local['generation'];$failureCount=0;$reconnect=false;$completedPulse=false;
$report=static function(string $state,string $message)use($generation):void{
    msw_background_write(msw_background_status_path(),['generation'=>$generation,'at'=>time(),'pid'=>getmypid(),'state'=>$state,'message'=>$message]);
};
try{
    require __DIR__.'/bootstrap.php';
    if(session_status()===PHP_SESSION_ACTIVE)session_write_close();
    // A deployed update is picked up between batches without interrupting a
    // transaction. The recurring task then launches the new PHP source.
    $sources=[__FILE__,__DIR__.'/world_runtime.php',__DIR__.'/bots.php',__DIR__.'/fob_world.php',__DIR__.'/game.php',__DIR__.'/../config/app.php'];
    $signature=static function()use($sources):string{clearstatcache();$parts=[];foreach($sources as $path)$parts[]=hash_file('sha256',$path);return implode(':',$parts);};
    $original=$signature();$nextSourceCheck=0;
    do{
        $current=msw_background_read(msw_background_config_path());
        if(($current['generation']??'')!==$generation)break;
        if(!msw_background_apache_running((int)$local['apache_port'])){
            $report('waiting','Waiting for XAMPP Apache. Automatic startup will resume when it returns.');break;
        }
        if(time()>=$nextSourceCheck){
            if($signature()!==$original){$report('starting','Loading the updated world server automatically…');break;}
            $nextSourceCheck=time()+15;
        }
        try{
            if($reconnect){msw_db(true);$reconnect=false;}
            $pulse=msw_world_pulse();$failureCount=0;
            if(in_array($pulse['status'],['ok','retrying'],true))$completedPulse=true;
            if($pulse['failed']>0)$report('retrying','Background world is running and retrying delayed operations.');
            elseif(!$completedPulse)$report('starting','Background process started. Waiting for its first world update…');
            else $report('running','Running automatically · AI commanders and strike arrivals continue with players offline.');
        }catch(Throwable $e){
            $reconnect=true;$failureCount++;
            error_log('[MSW automatic world] '.$e->getMessage());
            $report('retrying','Background world is reconnecting. Check that XAMPP MySQL is running.');
        }
        // A short sliced wait permits Apache stop/configuration changes to be
        // noticed promptly, including during a temporary database outage.
        $delay=$reconnect?min(30,2**min(5,$failureCount)):msw_world_interval_ms()/1000;
        $until=microtime(true)+$delay;
        do{
            usleep(250000);
            if((msw_background_read(msw_background_config_path())['generation']??'')!==$generation)break 2;
        }while(microtime(true)<$until);
    }while(true);
}catch(Throwable $e){
    error_log('[MSW automatic world startup] '.$e->getMessage());
    try{$report('error','Automatic world could not start. Use Update / Repair to check this installation.');}catch(Throwable $_){}
    exit(1);
}finally{flock($lock,LOCK_UN);fclose($lock);}
