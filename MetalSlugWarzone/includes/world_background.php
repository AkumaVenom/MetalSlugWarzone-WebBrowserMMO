<?php
declare(strict_types=1);

// Local setup owns registration. Public gameplay never executes OS commands.
const MSW_WORLD_LOCAL_GUARD = "<?php http_response_code(404); exit; ?>\n";
function msw_background_config_path(): string {return __DIR__.'/world_server.local.php';}
function msw_background_status_path(): string {return __DIR__.'/world_status.local.php';}
function msw_background_attempt_path(): string {return __DIR__.'/world_install.local.php';}
function msw_background_read(string $path): ?array {
    if(!is_file($path))return null;
    $text=@file_get_contents($path);
    if(!is_string($text)||!str_starts_with($text,MSW_WORLD_LOCAL_GUARD))return null;
    $data=json_decode(substr($text,strlen(MSW_WORLD_LOCAL_GUARD)),true);
    return is_array($data)?$data:null;
}
function msw_background_write(string $path,array $data): void {
    $temporary=tempnam(dirname($path),'msw-world-');
    if($temporary===false)throw new RuntimeException('Cannot write the automatic world settings in the game includes folder.');
    try{
        $bytes=MSW_WORLD_LOCAL_GUARD.json_encode($data,JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE|JSON_THROW_ON_ERROR)."\n";
        if(file_put_contents($temporary,$bytes,LOCK_EX)!==strlen($bytes)||!rename($temporary,$path)){
            throw new RuntimeException('Could not save the automatic world settings.');
        }
    }finally{if(is_file($temporary))@unlink($temporary);}
}
function msw_background_task_name(string $sid=''): string {
    // An old task created by an elevated/different account must not prevent the
    // current account from registering its own least-privilege task.
    $identity=strtolower(str_replace('\\','/',dirname(__DIR__))).'|'.$sid;
    return 'MetalSlugWarzone-'.substr(hash('sha256',$identity),0,24);
}
function msw_background_account_mode(string $sid,string $groups): string {
    if(in_array($sid,['S-1-5-18','S-1-5-19','S-1-5-20'],true))return 'ServiceAccount';
    // A console-launched XAMPP already has an interactive Windows session.
    // It does not need a separate batch logon to run unattended game updates.
    return preg_match('/(?<![0-9-])S-1-5-6(?![0-9-])/',$groups)?'S4U':'InteractiveToken';
}
function msw_background_find_php(): array {
    $ini=php_ini_loaded_file();
    if(!$ini||!is_file($ini))throw new RuntimeException('The active XAMPP php.ini could not be located.');
    $folders=[dirname($ini),PHP_BINDIR,dirname(PHP_BINARY),dirname((string)($_SERVER['DOCUMENT_ROOT']??'')).'/php'];
    foreach(array_unique($folders) as $folder){
        $exe=rtrim($folder,'/\\').DIRECTORY_SEPARATOR.'php-win.exe';
        if(is_file($exe))return [$exe,$ini];
    }
    throw new RuntimeException('XAMPP php-win.exe is missing. Restore that file from your matching XAMPP installation; no console launcher is used.');
}
function msw_background_command(array $command): array {
    if(!function_exists('proc_open'))throw new RuntimeException('PHP process execution is disabled, so local setup cannot register automatic startup.');
    $out=tempnam(sys_get_temp_dir(),'msw-out-');$err=tempnam(sys_get_temp_dir(),'msw-err-');
    if($out===false||$err===false)throw new RuntimeException('Cannot create temporary setup output files.');
    $process=null;
    try{
        $process=proc_open($command,[0=>['file',PHP_OS_FAMILY==='Windows'?'NUL':'/dev/null','r'],1=>['file',$out,'w'],2=>['file',$err,'w']],$pipes,null,null,['bypass_shell'=>true,'suppress_errors'=>true,'create_new_console'=>false]);
        if(!is_resource($process))throw new RuntimeException('Windows could not start its automatic task registration tool.');
        $deadline=microtime(true)+15;
        do{
            $state=proc_get_status($process);
            if(!$state['running'])break;
            if(microtime(true)>=$deadline){proc_terminate($process);throw new RuntimeException('Windows automatic task registration timed out.');}
            usleep(50000);
        }while(true);
        $exit=(int)$state['exitcode'];$closed=proc_close($process);$process=null;
        if($exit<0)$exit=$closed;
        return ['exit'=>$exit,'output'=>trim((string)file_get_contents($out).' '.(string)file_get_contents($err))];
    }finally{
        if(is_resource($process)){proc_terminate($process);proc_close($process);}
        @unlink($out);@unlink($err);
    }
}
function msw_background_task_xml(string $php,string $ini,string $script,string $sid,string $start,string $mode='InteractiveToken'): string {
    if(!preg_match('/^S-1-\d+(?:-\d+)+$/',$sid))throw new RuntimeException('Invalid Windows account identity.');
    foreach([$php,$ini,$script] as $path)if(strpbrk($path,"\r\n\"")!==false)throw new RuntimeException('Invalid automatic startup path.');
    $xml=fn(string $s):string=>htmlspecialchars($s,ENT_QUOTES|ENT_XML1,'UTF-8');
    $args='-c "'.$ini.'" -f "'.$script.'"';
    if(!in_array($mode,['InteractiveToken','S4U','ServiceAccount'],true))throw new RuntimeException('Invalid automatic startup logon type.');
    $service=in_array($sid,['S-1-5-18','S-1-5-19','S-1-5-20'],true);
    if($mode==='ServiceAccount'&&!$service)throw new RuntimeException('Invalid Windows service identity.');
    $logon=$service?'':'<LogonType>'.$mode.'</LogonType>';
    $working=str_replace('/','\\',dirname(str_replace('\\','/',$php)));
    return '<?xml version="1.0" encoding="UTF-16"?>'."\n".
        '<Task version="1.3" xmlns="http://schemas.microsoft.com/windows/2004/02/mit/task">'.
        '<RegistrationInfo><Description>Metal Slug Warzone automatic world updates while XAMPP is running.</Description></RegistrationInfo>'.
        '<Triggers><TimeTrigger><Enabled>true</Enabled><StartBoundary>'.$xml($start).'</StartBoundary><Repetition><Interval>PT1M</Interval><StopAtDurationEnd>false</StopAtDurationEnd></Repetition></TimeTrigger></Triggers>'.
        '<Principals><Principal id="WorldAccount"><UserId>'.$xml($sid).'</UserId>'.$logon.'<RunLevel>LeastPrivilege</RunLevel></Principal></Principals>'.
        '<Settings><MultipleInstancesPolicy>IgnoreNew</MultipleInstancesPolicy><DisallowStartIfOnBatteries>false</DisallowStartIfOnBatteries><StopIfGoingOnBatteries>false</StopIfGoingOnBatteries><StartWhenAvailable>true</StartWhenAvailable><RunOnlyIfNetworkAvailable>false</RunOnlyIfNetworkAvailable><AllowStartOnDemand>true</AllowStartOnDemand><Enabled>true</Enabled><Hidden>true</Hidden><ExecutionTimeLimit>PT0S</ExecutionTimeLimit><Priority>7</Priority></Settings>'.
        '<Actions Context="WorldAccount"><Exec><Command>'.$xml($php).'</Command><Arguments>'.$xml($args).'</Arguments><WorkingDirectory>'.$xml($working).'</WorkingDirectory></Exec></Actions></Task>';
}
function msw_background_task_command(string $system,string $action,string $task,string $xml,string $sid,string $mode): array {
    if(!in_array($action,['register','run'],true))throw new RuntimeException('Invalid automatic startup action.');
    // Use the documented Task Scheduler API under the caller's own identity.
    // wscript is windowless; neither CMD nor a PowerShell window is launched.
    $host=$system.'wscript.exe';
    if(is_file($host)){
        $output=tempnam(sys_get_temp_dir(),'msw-result-');
        if($output===false)throw new RuntimeException('Cannot create the automatic startup result file.');
        try{
            $result=msw_background_command([$host,'//B','//Nologo',__DIR__.'/world_task.js',$action,$task,$xml,$output,$sid,$mode]);
            $bytes=(string)file_get_contents($output);
            if(str_starts_with($bytes,"\xFF\xFE"))$bytes=mb_convert_encoding(substr($bytes,2),'UTF-8','UTF-16LE');
            $data=json_decode($bytes,true);
            if(is_array($data)&&isset($data['exit'],$data['output']))return ['exit'=>(int)$data['exit'],'output'=>(string)$data['output']];
            // Some hosts disable Windows Script Host. The built-in command
            // adapter uses the same account and XML, without changing rights.
        }finally{@unlink($output);}
    }
    return msw_background_command($action==='register'
        ?[$system.'schtasks.exe','/Create','/TN',$task,'/XML',$xml,'/F']
        :[$system.'schtasks.exe','/Run','/TN',$task]);
}
function msw_background_setup(array $config): array {
    // Database installation has already completed. A host startup failure must
    // never turn that committed work into a failed Fresh Install / Repair.
    try{
        msw_background_install($config);
        if(PHP_OS_FAMILY==='Windows'||is_file(msw_background_config_path())){
            msw_background_write(msw_background_attempt_path(),['at'=>time(),'error'=>null]);
        }
        return ['ok'=>true,'message'=>'Automatic startup requested. Waiting for a confirmed background update.'];
    }catch(Throwable $e){
        $detail='Background startup needs attention. '.$e->getMessage();
        if(preg_match('/access.*denied|0x80070005/i',$detail)){
            $detail.=' Windows denied task registration for this XAMPP account. Close the XAMPP Control Panel, reopen it with Run as administrator, restart Apache, then click Enable / Retry. Do not reinstall or reset the database.';
        }
        try{msw_background_write(msw_background_attempt_path(),['at'=>time(),'error'=>$detail]);}catch(Throwable $_){}
        return ['ok'=>false,'message'=>$detail];
    }
}
function msw_background_install(array $config): void {
    if(PHP_OS_FAMILY!=='Windows')return; // This installer targets the supplied Windows XAMPP world.
    $lock=fopen(__DIR__.'/world_setup.local.lock','c');
    if(!$lock||!flock($lock,LOCK_EX|LOCK_NB))throw new RuntimeException('Automatic world setup is already running. Wait for it to finish.');
    $temporary=null;
    try{
        [$php,$ini]=msw_background_find_php();
        $windows=(string)(getenv('SystemRoot')?:($_SERVER['SystemRoot']??''));
        if($windows==='')throw new RuntimeException('The Windows system folder could not be located.');
        $system=$windows.'\\System32\\';
        $identity=msw_background_command([$system.'whoami.exe','/user','/fo','csv','/nh']);
        if($identity['exit']!==0||!preg_match('/S-1-\d+(?:-\d+)+/',$identity['output'],$match))throw new RuntimeException('Cannot identify the Windows account running XAMPP.');
        $port=(int)($_SERVER['SERVER_PORT']??0);
        if($port<1||$port>65535)throw new RuntimeException('The XAMPP Apache port could not be determined.');
        $groups=msw_background_command([$system.'whoami.exe','/groups','/fo','csv','/nh']);
        if($groups['exit']!==0)throw new RuntimeException('Cannot determine whether XAMPP is running as a Windows service. '.$groups['output']);
        $sid=$match[0];$mode=msw_background_account_mode($sid,$groups['output']);
        $task=msw_background_task_name($sid);$root=dirname(__DIR__);
        $settings=['game_root'=>$root,'task_name'=>$task,'apache_port'=>$port,'version'=>(string)$config['version'],'logon_type'=>$mode,'account_sid'=>$sid,
            'db'=>array_intersect_key($config['db'],array_flip(['host','port','name','user']))];
        $old=msw_background_read(msw_background_config_path());
        $fingerprint=hash('sha256',json_encode($settings,JSON_THROW_ON_ERROR));
        $same=$old&&($old['fingerprint']??'')===$fingerprint;
        $settings+=['fingerprint'=>$fingerprint,'generation'=>$same?$old['generation']:bin2hex(random_bytes(16)),
            'installed_at'=>time(),'installed'=>true];
        $xml=msw_background_task_xml($php,$ini,__DIR__.'/world_service.php',$sid,date('c',time()+10),$mode);
        $temporary=tempnam(sys_get_temp_dir(),'msw-task-');
        if($temporary===false)throw new RuntimeException('Cannot create the automatic startup definition.');
        $bytes="\xFF\xFE".mb_convert_encoding($xml,'UTF-16LE','UTF-8');
        if(file_put_contents($temporary,$bytes)!==strlen($bytes))throw new RuntimeException('Cannot save the automatic startup definition.');
        $registered=msw_background_task_command($system,'register',$task,$temporary,$sid,$mode);
        if($registered['exit']!==0)throw new RuntimeException('Automatic startup was not installed: '.$registered['output']);
        msw_background_write(msw_background_config_path(),$settings);
        $started=msw_background_task_command($system,'run',$task,$temporary,$sid,$mode);
        if($started['exit']!==0)throw new RuntimeException('Automatic startup was registered, but Windows could not start it: '.$started['output']);
    }finally{
        if(is_string($temporary)&&is_file($temporary))@unlink($temporary);
        flock($lock,LOCK_UN);fclose($lock);
    }
}
function msw_background_status(): array {
    $config=msw_background_read(msw_background_config_path());
    $attempt=msw_background_read(msw_background_attempt_path());
    if(!$config||empty($config['installed']))return !empty($attempt['error'])?['state'=>'error','message'=>$attempt['error']]:['state'=>'missing','message'=>'Automatic startup is not installed. Use Enable / Retry below.'];
    $status=msw_background_read(msw_background_status_path());$now=time();
    if($status&&($status['generation']??'')===($config['generation']??'')&&(int)($status['at']??0)<=$now+5&&(int)($status['at']??0)>=$now-40){
        return ['state'=>(string)$status['state'],'message'=>(string)$status['message'],'last_update'=>(int)$status['at']];
    }
    if(!empty($attempt['error']))return ['state'=>'error','message'=>$attempt['error']];
    $installed=(int)($config['installed_at']??0);
    if($installed<=$now+5&&$installed>=$now-120)return ['state'=>'starting','message'=>'Automatic startup is installed. Waiting for the first server update…'];
    return ['state'=>'error','message'=>'No recent background update. Keep Apache and MySQL running, then use Enable / Retry below to check automatic startup.'];
}
function msw_background_apache_running(int $port): bool {
    if($port<1||$port>65535)return false;
    // No HTTP request or player session is needed to keep the service alive.
    foreach(['127.0.0.1','[::1]'] as $host){
        $socket=@stream_socket_client('tcp://'.$host.':'.$port,$errno,$error,0.4);
        if($socket){fclose($socket);return true;}
    }
    return false;
}
