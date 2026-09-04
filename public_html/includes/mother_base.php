<?php
declare(strict_types=1);

function msw_mb_catalog_entry(string $baseKey): ?array {
    return msw_mother_base_catalog()[$baseKey] ?? null;
}

function msw_mb_collision_reason(string $baseKey,int $x,int $y,int $padding=9): ?string {
    $bases=msw_mother_base_catalog();
    $profiles=msw_mother_base_collision_catalog();
    if(!isset($bases[$baseKey])) return 'Mother Base boundary';
    $base=$bases[$baseKey];
    $profile=$profiles[$baseKey]??[];
    $bounds=$profile['bounds']??[24,42,(int)$base['w']-24,(int)$base['h']-18];
    [$minX,$minY,$maxX,$maxY]=array_map('intval',$bounds);
    if($x<$minX || $x>$maxX || $y<$minY || $y>$maxY) return 'Mother Base perimeter';
    foreach(($profile['rects']??[]) as $rect){
        if(count($rect)<4) continue;
        $x1=(int)$rect[0]-$padding;$y1=(int)$rect[1]-$padding;
        $x2=(int)$rect[2]+$padding;$y2=(int)$rect[3]+$padding;
        if($x>=$x1 && $x<=$x2 && $y>=$y1 && $y<=$y2) return (string)($rect[4]??'solid base structure');
    }
    return null;
}

function msw_mb_path_collision(string $baseKey,int $fromX,int $fromY,int $toX,int $toY,int $padding=9): ?string {
    $distance=max(abs($toX-$fromX),abs($toY-$fromY));
    $samples=max(1,(int)ceil($distance/4));
    for($i=1;$i<=$samples;$i++){
        $t=$i/$samples;
        $x=(int)round($fromX+(($toX-$fromX)*$t));
        $y=(int)round($fromY+(($toY-$fromY)*$t));
        $reason=msw_mb_collision_reason($baseKey,$x,$y,$padding);
        if($reason!==null) return $reason;
    }
    return null;
}

function msw_mb_spawn(string $baseKey): array {
    $base=msw_mb_catalog_entry($baseKey);
    if(!$base) return [835,650];
    $spawn=$base['spawn']??[(int)floor((int)$base['w']/2),(int)floor((int)$base['h']/2)];
    return [(int)$spawn[0],(int)$spawn[1]];
}

function msw_mb_safe_position(string $baseKey,int $x,int $y): array {
    $base=msw_mb_catalog_entry($baseKey);
    if(!$base) return [$x,$y];
    $profile=msw_mother_base_collision_catalog()[$baseKey]??[];
    $bounds=$profile['bounds']??[24,42,(int)$base['w']-24,(int)$base['h']-18];
    [$minX,$minY,$maxX,$maxY]=array_map('intval',$bounds);
    $x=max($minX,min($maxX,$x));
    $y=max($minY,min($maxY,$y));
    if(msw_mb_collision_reason($baseKey,$x,$y)===null) return [$x,$y];
    for($radius=18;$radius<=360;$radius+=18){
        for($dx=-$radius;$dx<=$radius;$dx+=18){
            foreach([-$radius,$radius] as $dy){
                $cx=max($minX,min($maxX,$x+$dx));$cy=max($minY,min($maxY,$y+$dy));
                if(msw_mb_collision_reason($baseKey,$cx,$cy)===null) return [$cx,$cy];
            }
        }
        for($dy=-$radius+18;$dy<=$radius-18;$dy+=18){
            foreach([-$radius,$radius] as $dx){
                $cx=max($minX,min($maxX,$x+$dx));$cy=max($minY,min($maxY,$y+$dy));
                if(msw_mb_collision_reason($baseKey,$cx,$cy)===null) return [$cx,$cy];
            }
        }
    }
    [$sx,$sy]=msw_mb_spawn($baseKey);
    return msw_mb_collision_reason($baseKey,$sx,$sy)===null?[$sx,$sy]:[$minX,$minY];
}

function msw_mb_access_relation(int $visitorId,int $ownerId): ?string {
    if($visitorId===$ownerId) return 'owner';
    if(msw_one('SELECT 1 FROM friends WHERE user_id=? AND friend_user_id=? LIMIT 1','ii',[$visitorId,$ownerId])) return 'friend';
    $sameForce=msw_one(
        'SELECT 1 FROM strike_force_members a JOIN strike_force_members b ON b.strike_force_id=a.strike_force_id WHERE a.user_id=? AND b.user_id=? LIMIT 1',
        'ii',[$visitorId,$ownerId]
    );
    return $sameForce?'strike_force':null;
}

function msw_mb_can_visit(int $visitorId,int $ownerId): bool {
    return msw_mb_access_relation($visitorId,$ownerId)!==null;
}

function msw_mb_presence_leave(int $uid): void {
    msw_stmt('DELETE FROM mother_base_presence WHERE user_id=?','i',[$uid]);
}

function msw_mb_presence_touch(int $uid,int $ownerId,string $baseKey,int $x,int $y,string $facing): void {
    if(!in_array($facing,['up','down','left','right'],true)) $facing='right';
    msw_stmt(
        'INSERT INTO mother_base_presence(user_id,base_owner_user_id,base_key,x,y,facing,last_seen) VALUES(?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE base_owner_user_id=VALUES(base_owner_user_id),base_key=VALUES(base_key),x=VALUES(x),y=VALUES(y),facing=VALUES(facing),last_seen=NOW()',
        'iisiis',[$uid,$ownerId,$baseKey,$x,$y,$facing]
    );
    // A commander can be present in one physical field context at a time.
    msw_stmt('UPDATE users SET active_map=NULL,last_seen=NULL WHERE id=?','i',[$uid]);
}

function msw_mb_presence_row(int $uid): ?array {
    return msw_one('SELECT * FROM mother_base_presence WHERE user_id=? LIMIT 1','i',[$uid]);
}

function msw_mb_visitors(int $viewerId,int $ownerId,string $baseKey): array {
    $ttl=max(10,min(300,(int)msw_config('presence_ttl_seconds')));
    return msw_all(
        "SELECT p.user_id id,u.username,u.character_key,u.base_grade,p.x,p.y,p.facing FROM mother_base_presence p JOIN users u ON u.id=p.user_id WHERE p.user_id<>? AND p.base_owner_user_id=? AND p.base_key=? AND p.last_seen>=DATE_SUB(NOW(),INTERVAL {$ttl} SECOND) ORDER BY u.username LIMIT 100",
        'iis',[$viewerId,$ownerId,$baseKey]
    );
}

function msw_mb_is_vehicle_class(string $class): bool {
    return in_array($class,['vehicle','air'],true);
}

function msw_mb_runtime_enemy_key(array $unit): string {
    $catalog=msw_enemy_catalog();
    $key=(string)($unit['source_enemy_key']??'');
    $legacy=['girida'=>'biker','girida_o'=>'biker','di_cokka'=>'biker','dicokka'=>'biker','r_shobu'=>'biker','rshobu'=>'biker'];
    $key=$legacy[$key]??$key;
    if(isset($catalog[$key]) && ($catalog[$key]['class']??'')!=='boss') return $key;
    return msw_mb_is_vehicle_class((string)($unit['unit_class']??''))?'biker':'rifle';
}

function msw_mb_layout_slots(string $baseKey,bool $vehicle): array {
    $profile=msw_mother_base_collision_catalog()[$baseKey]??null;
    $base=msw_mb_catalog_entry($baseKey);
    if(!$profile||!$base) return [msw_mb_spawn($baseKey)];
    [$minX,$minY,$maxX,$maxY]=array_map('intval',$profile['bounds']);
    $slots=[];
    if($vehicle){
        // Park hardware at the lower/deck edges; hardware never roams.
        for($y=$maxY-35;$y>=$minY+65;$y-=82){
            for($x=$minX+45;$x<=$maxX-45;$x+=92){
                if(msw_mb_collision_reason($baseKey,$x,$y,16)===null) $slots[]=[$x,$y];
            }
        }
    }else{
        // Personnel occupy the open parade/deck grid with generous separation.
        for($y=$minY+42;$y<=$maxY-45;$y+=42){
            for($x=$minX+30;$x<=$maxX-30;$x+=36){
                if(msw_mb_collision_reason($baseKey,$x,$y,12)===null) $slots[]=[$x,$y];
            }
        }
    }
    if(!$slots) $slots[] = msw_mb_spawn($baseKey);
    return $slots;
}

function msw_mb_seed_position(string $baseKey,int $unitId,bool $vehicle,int $ordinal=0): array {
    $slots=msw_mb_layout_slots($baseKey,$vehicle);
    $count=count($slots);
    $index=$count?abs(($unitId*37)+($ordinal*17))%$count:0;
    return $slots[$index]??msw_mb_spawn($baseKey);
}

function msw_mb_sync_unit_positions(int $ownerId,string $baseKey): void {
    if(!isset(msw_mother_base_catalog()[$baseKey])) return;
    $units=msw_all('SELECT id,unit_class FROM units WHERE owner_user_id=? ORDER BY id','i',[$ownerId]);
    $existing=[];$taken=[];
    foreach(msw_all('SELECT unit_id,base_key,x,y FROM mother_base_unit_positions WHERE owner_user_id=?','i',[$ownerId]) as $row){
        $existing[(int)$row['unit_id']]=$row;
        if((string)$row['base_key']===$baseKey && msw_mb_collision_reason($baseKey,(int)$row['x'],(int)$row['y'],12)===null){
            $taken[(int)$row['x'].':'.(int)$row['y']]=true;
        }
    }
    foreach($units as $ordinal=>$unit){
        $id=(int)$unit['id'];$vehicle=msw_mb_is_vehicle_class((string)$unit['unit_class']);
        $row=$existing[$id]??null;
        $needs=!$row || (string)$row['base_key']!==$baseKey || msw_mb_collision_reason($baseKey,(int)$row['x'],(int)$row['y'],12)!==null;
        if(!$needs) continue;
        $slots=msw_mb_layout_slots($baseKey,$vehicle);$count=max(1,count($slots));$preferred=abs(($id*37)+($ordinal*17))%$count;
        [$x,$y]=$slots[$preferred]??msw_mb_spawn($baseKey);
        for($offset=0;$offset<$count;$offset++){
            $candidate=$slots[($preferred+$offset)%$count]??[$x,$y];$slotKey=(int)$candidate[0].':'.(int)$candidate[1];
            if(!isset($taken[$slotKey])){[$x,$y]=[(int)$candidate[0],(int)$candidate[1]];break;}
        }
        $taken[$x.':'.$y]=true;
        $facing=($id%2===0)?'left':'right';
        $next=$vehicle?null:date('Y-m-d H:i:s',time()+random_int(5,14));
        msw_stmt(
            'INSERT INTO mother_base_unit_positions(unit_id,owner_user_id,base_key,x,y,facing,anchor_x,anchor_y,next_move_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE owner_user_id=VALUES(owner_user_id),base_key=VALUES(base_key),x=VALUES(x),y=VALUES(y),facing=VALUES(facing),anchor_x=VALUES(anchor_x),anchor_y=VALUES(anchor_y),next_move_at=VALUES(next_move_at),updated_at=NOW()',
            'iisiisiis',[$id,$ownerId,$baseKey,$x,$y,$facing,$x,$y,$next]
        );
    }
    // Delete stale layout rows for units no longer owned by this commander.
    msw_stmt('DELETE p FROM mother_base_unit_positions p LEFT JOIN units u ON u.id=p.unit_id WHERE p.owner_user_id=? AND u.id IS NULL','i',[$ownerId]);
}

function msw_mb_advance_staff(int $ownerId,string $baseKey): void {
    msw_mb_sync_unit_positions($ownerId,$baseKey);
    $db=msw_db();
    $db->begin_transaction();
    try{
        $due=msw_all(
            "SELECT p.*,u.unit_class FROM mother_base_unit_positions p JOIN units u ON u.id=p.unit_id WHERE p.owner_user_id=? AND p.base_key=? AND u.unit_class NOT IN ('vehicle','air') AND p.next_move_at IS NOT NULL AND p.next_move_at<=NOW() ORDER BY p.next_move_at,p.unit_id LIMIT 16 FOR UPDATE",
            'is',[$ownerId,$baseKey]
        );
        if($due){
            $positions=msw_all('SELECT unit_id,x,y FROM mother_base_unit_positions WHERE owner_user_id=? AND base_key=?','is',[$ownerId,$baseKey]);
            $occupied=[];
            foreach($positions as $p) $occupied[(int)$p['unit_id']]=[(int)$p['x'],(int)$p['y']];
            $dirs=[[0,-10,'up'],[10,0,'right'],[0,10,'down'],[-10,0,'left']];
            foreach($due as $row){
                $id=(int)$row['unit_id'];$x=(int)$row['x'];$y=(int)$row['y'];
                $ax=(int)$row['anchor_x'];$ay=(int)$row['anchor_y'];$facing=(string)$row['facing'];
                // Not every due tick causes movement; staff naturally pause between short patrol steps.
                if(random_int(1,100)<=24){
                    $next=date('Y-m-d H:i:s',time()+random_int(8,18));
                    msw_stmt('UPDATE mother_base_unit_positions SET next_move_at=?,updated_at=NOW() WHERE unit_id=? AND owner_user_id=?','sii',[$next,$id,$ownerId]);
                    continue;
                }
                $choices=$dirs;
                // Natural wandering: random direction order, but gently return toward the unit's assigned anchor.
                usort($choices,function(array $a,array $b) use($x,$y,$ax,$ay,$id): int {
                    $da=abs(($x+$a[0])-$ax)+abs(($y+$a[1])-$ay);
                    $db=abs(($x+$b[0])-$ax)+abs(($y+$b[1])-$ay);
                    if(max(abs($x-$ax),abs($y-$ay))>72) return $da<=>$db;
                    return (($id+(int)$a[0]*3+(int)$a[1]*5+time())%17) <=> (($id+(int)$b[0]*3+(int)$b[1]*5+time())%17);
                });
                $moved=false;
                foreach($choices as [$dx,$dy,$face]){
                    $nx=$x+(int)$dx;$ny=$y+(int)$dy;
                    if(max(abs($nx-$ax),abs($ny-$ay))>92) continue;
                    if(msw_mb_path_collision($baseKey,$x,$y,$nx,$ny,10)!==null) continue;
                    $clear=true;
                    foreach($occupied as $otherId=>$pos){
                        if($otherId===$id) continue;
                        if(abs($pos[0]-$nx)<18 && abs($pos[1]-$ny)<18){$clear=false;break;}
                    }
                    if(!$clear) continue;
                    unset($occupied[$id]);$occupied[$id]=[$nx,$ny];
                    $x=$nx;$y=$ny;$facing=(string)$face;$moved=true;break;
                }
                $next=date('Y-m-d H:i:s',time()+random_int(7,16));
                msw_stmt('UPDATE mother_base_unit_positions SET x=?,y=?,facing=?,next_move_at=?,updated_at=NOW() WHERE unit_id=? AND owner_user_id=?','iissii',[$x,$y,$facing,$next,$id,$ownerId]);
            }
        }
        $db->commit();
    }catch(Throwable $e){
        $db->rollback();
        throw $e;
    }
}

function msw_mb_staff_state(int $ownerId,string $baseKey): array {
    msw_mb_sync_unit_positions($ownerId,$baseKey);
    $rows=msw_all(
        'SELECT u.id,u.callsign,u.source_enemy_key,u.unit_class,u.level,u.grade,u.assignment,p.x,p.y,p.facing FROM units u JOIN mother_base_unit_positions p ON p.unit_id=u.id WHERE u.owner_user_id=? AND p.base_key=? ORDER BY u.id',
        'is',[$ownerId,$baseKey]
    );
    $enemies=msw_enemy_catalog();$sectors=msw_sectors();$out=[];
    foreach($rows as $row){
        $runtimeKey=msw_mb_runtime_enemy_key($row);
        $enemy=$enemies[$runtimeKey]??$enemies['rifle'];
        $vehicle=msw_mb_is_vehicle_class((string)$row['unit_class']);
        $assignment=(string)$row['assignment'];
        $out[]=[
            'id'=>(int)$row['id'],'name'=>(string)$row['callsign'],'level'=>(int)$row['level'],'grade'=>(string)$row['grade'],
            'class'=>(string)$row['unit_class'],'assignment'=>$assignment,
            'assignment_name'=>$assignment==='reserve'?'Reserve':(string)($sectors[$assignment]['name']??ucwords(str_replace('_',' ',$assignment))),
            'x'=>(int)$row['x'],'y'=>(int)$row['y'],'facing'=>(string)$row['facing'],
            'sprite'=>msw_url((string)$enemy['sprite']),'mobile'=>$vehicle?0:1,'vehicle'=>$vehicle?1:0,
        ];
    }
    return $out;
}

function msw_mb_reset_layout(int $ownerId): void {
    msw_stmt('DELETE FROM mother_base_unit_positions WHERE owner_user_id=?','i',[$ownerId]);
    msw_stmt('DELETE FROM mother_base_presence WHERE base_owner_user_id=?','i',[$ownerId]);
}
