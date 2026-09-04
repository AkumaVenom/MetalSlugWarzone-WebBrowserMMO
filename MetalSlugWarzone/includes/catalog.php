<?php
declare(strict_types=1);

function msw_grades(): array { return ['E--','E-','E','D','C','B','A','A+','S','S+','S++']; }
function msw_grade_for_score(int $score): string {
    $thresholds = [[15,'E--'],[25,'E-'],[35,'E'],[45,'D'],[55,'C'],[65,'B'],[75,'A'],[84,'A+'],[91,'S'],[97,'S+'],[999,'S++']];
    foreach ($thresholds as [$max,$grade]) if ($score <= $max) return $grade;
    return 'S++';
}
function msw_sectors(): array {
    return [
        'combat'=>['name'=>'Combat Unit','icon'=>'⚔','stat'=>'combat'],
        'rd'=>['name'=>'R&D Team','icon'=>'⚙','stat'=>'rd'],
        'support'=>['name'=>'Support Unit','icon'=>'✚','stat'=>'support'],
        'intel'=>['name'=>'Intel Team','icon'=>'◉','stat'=>'intel'],
        'medical'=>['name'=>'Medical Team','icon'=>'✚','stat'=>'medical'],
        'mess'=>['name'=>'Mess Hall','icon'=>'◆','stat'=>'mess'],
        'security'=>['name'=>'Security Team','icon'=>'⬟','stat'=>'security'],
    ];
}
function msw_character_catalog(): array {
    return [
        'marco'=>['name'=>'Marco Rossi','game'=>'MS3','sprite'=>'assets/sprites/characters/marco_r.png','sprite_r'=>'assets/sprites/characters/marco_r.png','sprite_l'=>'assets/sprites/characters/marco_l.png','mirror_left'=>0],
        'tarma'=>['name'=>'Tarma Roving','game'=>'MS3','sprite'=>'assets/sprites/characters/tarma_r.png','sprite_r'=>'assets/sprites/characters/tarma_r.png','sprite_l'=>'assets/sprites/characters/tarma_l.png','mirror_left'=>0],
        'eri'=>['name'=>'Eri Kasamoto','game'=>'MS3','sprite'=>'assets/sprites/characters/eri_r.png','sprite_r'=>'assets/sprites/characters/eri_r.png','sprite_l'=>'assets/sprites/characters/eri_l.png','mirror_left'=>0],
        'fio'=>['name'=>'Fiolina Germi','game'=>'MS3','sprite'=>'assets/sprites/characters/fio_r.png','sprite_r'=>'assets/sprites/characters/fio_r.png','sprite_l'=>'assets/sprites/characters/fio_l.png','mirror_left'=>0],
        'nadia'=>['name'=>'Nadia Cassel','game'=>'MS4','sprite'=>'assets/sprites/characters/nadia_r.png','sprite_r'=>'assets/sprites/characters/nadia_r.png','sprite_l'=>'assets/sprites/characters/nadia_l.png','mirror_left'=>0],
        'trevor'=>['name'=>'Trevor Spacey','game'=>'MS4','sprite'=>'assets/sprites/characters/trevor_r.png','sprite_r'=>'assets/sprites/characters/trevor_r.png','sprite_l'=>'assets/sprites/characters/trevor_r.png','mirror_left'=>1],
    ];
}

function msw_mother_base_catalog(): array {
    return [
        'land_dirt'=>[
            'name'=>'Continental Mother Base','type'=>'Land FOB','climate'=>'Dirt Highlands',
            'image'=>'assets/maps/motherbase/MotherBase_Land_Dirt.png','w'=>1672,'h'=>941,
            'spawn'=>[835,650],
        ],
        'land_forest'=>[
            'name'=>'Forest Mother Base','type'=>'Land FOB','climate'=>'Forest Frontier',
            'image'=>'assets/maps/motherbase/MotherBase_Land_Forest.png','w'=>1672,'h'=>941,
            'spawn'=>[835,655],
        ],
        'land_desert'=>[
            'name'=>'Desert Mother Base','type'=>'Land FOB','climate'=>'Desert Frontier',
            'image'=>'assets/maps/motherbase/MotherBase_Land_Desert.png','w'=>1672,'h'=>941,
            'spawn'=>[835,655],
        ],
        'land_snow'=>[
            'name'=>'Arctic Mother Base','type'=>'Land FOB','climate'=>'Snow Frontier',
            'image'=>'assets/maps/motherbase/MotherBase_Land_Snow.png','w'=>1672,'h'=>941,
            'spawn'=>[835,650],
        ],
        'sea_fob1'=>[
            'name'=>'Offshore Mother Base Alpha','type'=>'Sea FOB','climate'=>'Open Ocean',
            'image'=>'assets/maps/motherbase/MotherBase_Sea_FOB1.png','w'=>1672,'h'=>941,
            'spawn'=>[860,590],
        ],
        'sea_fob2'=>[
            'name'=>'Offshore Mother Base Bravo','type'=>'Sea FOB','climate'=>'Open Ocean',
            'image'=>'assets/maps/motherbase/MotherBase_Sea_FOB2.png','w'=>1672,'h'=>941,
            'spawn'=>[850,575],
        ],
        'sea_fob3'=>[
            'name'=>'Maritime Fortress Mother Base','type'=>'Sea FOB','climate'=>'Open Ocean',
            'image'=>'assets/maps/motherbase/MotherBase_Sea_FOB3.png','w'=>1774,'h'=>887,
            'spawn'=>[905,585],
        ],
    ];
}

/**
 * Physical Mother Base collision profiles.
 * Coordinates use the visitor/staff foot point in native image pixels.
 * The open courtyards/decks remain traversable while buildings, walls, cliffs,
 * machinery, parked cargo and ocean edges remain solid under server authority.
 */
function msw_mother_base_collision_catalog(): array {
    $landCommon = [
        'bounds'=>[225,455,1535,825],
        'rects'=>[
            [205,90,755,505,'western command structures'],
            [905,80,1515,500,'eastern command structures'],
            [105,430,305,650,'western perimeter structures'],
            [1460,405,1600,685,'eastern perimeter structures'],
            [240,690,430,835,'southwest cargo and barricades'],
            [520,745,705,850,'southern equipment'],
            [800,760,925,855,'southern equipment'],
            [1115,735,1265,845,'southeast equipment'],
            [1380,690,1555,830,'southeast perimeter'],
        ],
    ];
    return [
        'land_dirt'=>$landCommon,
        'land_forest'=>$landCommon,
        'land_desert'=>$landCommon,
        'land_snow'=>$landCommon,
        'sea_fob1'=>[
            'bounds'=>[250,405,1515,815],
            'rects'=>[
                [215,105,790,430,'western offshore command block'],
                [1080,235,1560,430,'eastern offshore structure'],
                [55,365,315,610,'western platform machinery'],
                [1440,405,1600,655,'eastern platform machinery'],
                [355,685,510,805,'southwest crates'],
                [960,610,1215,790,'parked armored machinery'],
                [1280,700,1490,820,'southeast cargo'],
                [0,820,1672,941,'ocean edge'],
            ],
        ],
        'sea_fob2'=>[
            'bounds'=>[175,390,1510,815],
            'rects'=>[
                [105,230,770,430,'western offshore command block'],
                [1040,240,1545,430,'eastern offshore structure'],
                [65,415,260,620,'western service bay'],
                [1460,415,1605,660,'eastern service bay'],
                [280,680,465,815,'southwest cargo'],
                [960,615,1195,785,'parked armored machinery'],
                [1300,690,1510,815,'southeast cargo'],
                [0,820,1672,941,'ocean edge'],
            ],
        ],
        'sea_fob3'=>[
            'bounds'=>[180,390,1625,735],
            'rects'=>[
                [205,150,875,430,'western maritime command block'],
                [1030,205,1625,465,'eastern maritime structure'],
                [70,375,235,610,'western platform equipment'],
                [1535,420,1690,650,'eastern platform equipment'],
                [330,625,520,745,'southwest cargo'],
                [1180,625,1390,745,'southeast cargo'],
                [0,745,1774,887,'ocean edge'],
            ],
        ],
    ];
}

function msw_map_catalog(): array {
    return [
        'jungle_front'=>['name'=>'Jungle Front','region'=>'Outer Warzone','image'=>'assets/maps/jungle_front.png','w'=>1448,'h'=>1086,'level'=>1,'spawn'=>[720,900],'encounters'=>['rifle','bazooka','shield']],
        'industrial_rail'=>['name'=>'Industrial Railhead','region'=>'Occupied Corridor','image'=>'assets/maps/industrial_rail.png','w'=>1402,'h'=>1122,'level'=>3,'spawn'=>[720,820],'encounters'=>['rifle','minigun','biker']],
        'ruined_temple'=>['name'=>'Ruined Temple','region'=>'Ancient Front','image'=>'assets/maps/ruined_temple.png','w'=>1448,'h'=>1086,'level'=>5,'spawn'=>[720,1000],'encounters'=>['bazooka','shield','biker']],
        'desert_line'=>['name'=>'Occupied City','region'=>'Urban Warzone','image'=>'assets/maps/desert_line.png','w'=>1254,'h'=>1254,'level'=>7,'spawn'=>[645,1035],'encounters'=>['rifle','biker','minigun']],
        'coastal_breach'=>['name'=>'Coastal Breach','region'=>'Eastern Harbour','image'=>'assets/maps/coastal_breach.png','w'=>1448,'h'=>1086,'level'=>9,'spawn'=>[700,820],'encounters'=>['minigun','biker','shield']],
        'fortress_approach'=>['name'=>'Iron Citadel Interior','region'=>'Fortress Warzone','image'=>'assets/maps/fortress_approach.png','w'=>1448,'h'=>1086,'level'=>12,'spawn'=>[710,900],'encounters'=>['shield','minigun','biker']],
    ];
}

/**
 * Authored server collision for the v3 overhead warzones.
 * Coordinates use the player's foot point in native map pixels. Rectangles are
 * intentionally slightly generous around solid art so the sprite never appears
 * embedded in walls, cliffs, fences, machinery or prop clusters.
 */
function msw_map_collision_catalog(): array {
    return [
        'jungle_front'=>[
            'bounds'=>[28,185,1420,985],
            'rects'=>[
                [315,90,410,235,'watch tower'],
                [420,105,690,225,'northern cliff and jungle'],
                [760,105,1180,285,'bunker and northern cliff'],
                [1260,205,1385,300,'supply crates'],
                [25,275,105,395,'western cliff'],
                [150,280,335,390,'western cliff'],
                [355,365,435,455,'rock shelf'],
                [495,355,785,455,'central cliff shelf'],
                [790,330,850,470,'stone pillar'],
                [905,315,980,465,'stone pillar'],
                [1230,330,1405,465,'eastern cliff shelf'],
                [625,500,720,625,'central rock formation'],
                [70,570,350,675,'western lower cliff'],
                [385,635,565,735,'southern cliff shelf'],
                [625,650,760,735,'southern cliff shelf'],
                [825,640,1085,735,'southern cliff shelf'],
                [1160,615,1410,805,'eastern lower cliff'],
                [205,775,405,890,'field camp'],
                [455,820,595,955,'sandbag barricade'],
                [900,820,1035,955,'lower rock shelf'],
                [1105,850,1408,970,'lower eastern cliff'],
            ],
        ],
        'industrial_rail'=>[
            'bounds'=>[35,215,1368,1065],
            'rects'=>[
                [40,215,215,295,'northwest cargo stack'],
                [190,300,310,470,'covered crane platform'],
                [40,340,190,510,'western loading platform'],
                [500,315,560,395,'freight crate'],
                [570,395,865,525,'central cargo stack'],
                [960,350,1005,405,'barrier'],
                [1160,385,1368,520,'east service building'],
                [350,520,420,605,'cargo crate'],
                [318,615,548,715,'western blast wall'],
                [455,735,545,840,'broken wall'],
                [785,710,990,805,'wreckage and cargo'],
                [865,645,1090,715,'eastern blast wall'],
                [45,790,170,905,'military truck'],
                [265,865,345,955,'crate stack'],
                [390,865,455,965,'crate stack'],
                [440,865,910,1055,'southern gatehouse'],
                [1060,850,1245,970,'southeast cargo stack'],
                [1270,630,1368,800,'east cargo stacks'],
            ],
        ],
        'ruined_temple'=>[
            'bounds'=>[18,185,1428,1050],
            'rects'=>[
                [290,0,1135,290,'northern manor'],
                [65,175,290,385,'northwest ruins'],
                [1135,175,1415,390,'northeast ruins'],
                [20,375,350,685,'west ruined wing'],
                [1090,375,1418,690,'east ruined wing'],
                [485,285,585,490,'west courtyard wall'],
                [855,285,945,490,'east courtyard wall'],
                [590,190,850,305,'manor stair and barricade'],
                [565,395,625,475,'barrels'],
                [780,535,890,690,'wreckage'],
                [330,480,385,650,'west inner wall'],
                [1030,490,1090,665,'east inner wall'],
                [20,690,345,890,'southwest ruined yard'],
                [1215,690,1418,900,'southeast ruined yard'],
                [18,865,595,925,'southern perimeter wall'],
                [800,865,1095,925,'southern perimeter wall'],
                [1235,860,1428,925,'southern perimeter wall'],
            ],
        ],
        'desert_line'=>[
            'bounds'=>[18,22,1235,1135],
            'rects'=>[
                [0,0,220,245,'northwest city block'],
                [355,0,905,250,'northern city block'],
                [1090,0,1254,255,'northeast city block'],
                [0,245,230,760,'western city block'],
                [300,300,455,505,'walled supply yard'],
                [505,300,735,585,'central park'],
                [710,265,1055,505,'east-central city block'],
                [1090,250,1254,795,'eastern city block'],
                [300,590,515,785,'southwest warehouse'],
                [525,625,735,845,'southern park'],
                [815,535,965,665,'fountain monument'],
                [835,720,1015,850,'supply island'],
                [190,825,515,1020,'southern warehouse'],
                [690,930,915,1025,'southern cargo barricade'],
                [1065,790,1254,1060,'southeast city block'],
                [0,1065,565,1145,'seawall'],
                [735,1065,1254,1145,'seawall'],
                [0,1135,1254,1254,'harbour water'],
            ],
        ],
        'coastal_breach'=>[
            'bounds'=>[18,220,1428,975],
            'rects'=>[
                [20,55,535,285,'western harbour buildings'],
                [585,0,1428,285,'eastern harbour buildings'],
                [15,285,105,390,'west fence and cargo'],
                [280,295,375,380,'cargo cluster'],
                [600,315,710,395,'cargo cluster'],
                [875,315,980,405,'cargo cluster'],
                [1240,275,1428,485,'east fence and cargo'],
                [15,390,105,585,'west cargo stack'],
                [330,440,455,530,'cargo and floodlight'],
                [550,435,645,525,'cargo stack'],
                [755,425,885,535,'cargo stack'],
                [1005,455,1115,545,'cargo and barrels'],
                [1335,400,1428,565,'east cargo stack'],
                [15,605,105,735,'west cargo stack'],
                [300,625,395,705,'cargo stack'],
                [610,615,755,715,'cargo and barrels'],
                [930,660,1085,755,'cargo cluster'],
                [1280,650,1428,810,'east fence and cargo'],
                [1050,700,1295,1000,'harbour crane and pier'],
                [0,965,1448,1086,'harbour water'],
                [575,880,825,1086,'flooded loading gate'],
            ],
        ],
        'fortress_approach'=>[
            'bounds'=>[28,165,1420,965],
            'rects'=>[
                [20,70,1428,195,'northern bulkhead'],
                [20,230,255,455,'northwest machinery bay'],
                [335,220,735,420,'north-central machinery'],
                [945,225,1415,415,'northeast machinery bay'],
                [700,415,865,565,'central reactor and cargo'],
                [255,490,445,675,'west maintenance pit'],
                [1035,500,1225,685,'east maintenance pit'],
                [350,590,450,690,'west crate stack'],
                [785,670,890,760,'central crate stack'],
                [20,735,610,925,'southwest service rooms'],
                [805,720,970,905,'south-central machinery'],
                [970,745,1418,925,'southeast service rooms'],
                [20,900,610,975,'southern bulkhead'],
                [805,900,1420,975,'southern bulkhead'],
            ],
        ],
    ];
}
function msw_enemy_catalog(): array {
    return [
        'rifle'=>['name'=>'Rebel Rifleman','class'=>'infantry','type'=>'ballistic','sprite'=>'assets/sprites/enemies/rifle.png','hp'=>54,'atk'=>16,'def'=>10,'spd'=>14,'recruitable'=>1],
        'bazooka'=>['name'=>'Rebel Bazooka Trooper','class'=>'heavy_infantry','type'=>'explosive','sprite'=>'assets/sprites/enemies/bazooka.png','hp'=>68,'atk'=>22,'def'=>12,'spd'=>9,'recruitable'=>1],
        'shield'=>['name'=>'Rebel Shield Trooper','class'=>'heavy_infantry','type'=>'ballistic','sprite'=>'assets/sprites/enemies/shield.png','hp'=>82,'atk'=>15,'def'=>22,'spd'=>7,'recruitable'=>1],
        'minigun'=>['name'=>'Rebel Heavy Gunner','class'=>'heavy_infantry','type'=>'heavy','sprite'=>'assets/sprites/enemies/minigun.png','hp'=>76,'atk'=>24,'def'=>14,'spd'=>8,'recruitable'=>1],
        'biker'=>['name'=>'Rebel Biker','class'=>'vehicle','type'=>'ballistic','sprite'=>'assets/sprites/enemies/biker.png','hp'=>95,'atk'=>23,'def'=>17,'spd'=>19,'recruitable'=>1],
        'huge_hermit'=>['name'=>'Huge Hermit','class'=>'boss','type'=>'organic','sprite'=>'assets/sprites/enemies/huge_hermit.png','hp'=>420,'atk'=>42,'def'=>32,'spd'=>8,'recruitable'=>0],
        'rootmars'=>['name'=>'Rootmars','class'=>'boss','type'=>'energy','sprite'=>'assets/sprites/enemies/rootmars.png','hp'=>560,'atk'=>48,'def'=>38,'spd'=>12,'recruitable'=>0],
    ];
}
function msw_fulton_catalog(): array {
    return [
        'fulton'=>['name'=>'Fulton Recovery','rd'=>1,'bonus'=>0.00,'classes'=>['infantry','heavy_infantry']],
        'fulton_plus'=>['name'=>'Fulton+ Balloon','rd'=>4,'bonus'=>0.12,'classes'=>['infantry','heavy_infantry']],
        'cargo_fulton'=>['name'=>'Cargo Fulton','rd'=>8,'bonus'=>0.08,'classes'=>['infantry','heavy_infantry','vehicle']],
        'wormhole_fulton'=>['name'=>'Wormhole Fulton','rd'=>15,'bonus'=>0.22,'classes'=>['infantry','heavy_infantry','vehicle','air']],
    ];
}
function msw_move_catalog(): array {
    return [
        'rifle_burst'=>['name'=>'Rifle Burst','type'=>'ballistic','power'=>18,'accuracy'=>95],
        'heavy_burst'=>['name'=>'Heavy Burst','type'=>'heavy','power'=>23,'accuracy'=>90],
        'grenade'=>['name'=>'Grenade','type'=>'explosive','power'=>27,'accuracy'=>86],
        'armor_piercer'=>['name'=>'Armor Piercer','type'=>'anti_armor','power'=>29,'accuracy'=>88],
        'close_quarters'=>['name'=>'Close Quarters','type'=>'melee','power'=>21,'accuracy'=>98],
    ];
}
function msw_type_multiplier(string $moveType, string $targetClass): float {
    $strong = [
        'ballistic'=>['infantry'=>1.25,'heavy_infantry'=>0.85,'vehicle'=>0.55,'air'=>0.80,'boss'=>0.75],
        'heavy'=>['infantry'=>1.05,'heavy_infantry'=>1.15,'vehicle'=>0.85,'air'=>1.20,'boss'=>0.90],
        'explosive'=>['infantry'=>1.15,'heavy_infantry'=>1.35,'vehicle'=>1.20,'air'=>0.75,'boss'=>1.00],
        'anti_armor'=>['infantry'=>0.75,'heavy_infantry'=>1.00,'vehicle'=>1.60,'air'=>1.15,'boss'=>1.15],
        'melee'=>['infantry'=>1.30,'heavy_infantry'=>0.80,'vehicle'=>0.35,'air'=>0.10,'boss'=>0.55],
        'energy'=>['infantry'=>1.00,'heavy_infantry'=>1.00,'vehicle'=>1.10,'air'=>1.10,'boss'=>1.05],
        'fire'=>['infantry'=>1.20,'heavy_infantry'=>1.00,'vehicle'=>0.70,'air'=>0.70,'boss'=>0.90],
    ];
    return (float)($strong[$moveType][$targetClass] ?? 1.0);
}

function msw_mission_catalog(): array {
    return [
        'supply_breaker'=>['name'=>'Supply Breaker','brief'=>'Sever an armored logistics route and recover battlefield materials.','enemy'=>'bazooka','level'=>2,'reward'=>['common_metal'=>180,'fuel'=>120,'gmp'=>600]],
        'iron_convoy'=>['name'=>'Iron Convoy','brief'=>'Intercept a fast Rebel biker convoy before it reaches the fortress sector.','enemy'=>'biker','level'=>5,'reward'=>['common_metal'=>320,'minor_metal'=>150,'fuel'=>220,'gmp'=>950]],
        'sky_denial'=>['name'=>'Heavy Fire Suppression','brief'=>'Break a mobile heavy-weapons screen controlling the eastern theatre.','enemy'=>'minigun','level'=>8,'reward'=>['minor_metal'=>260,'precious_metal'=>55,'fuel'=>310,'gmp'=>1400]],
        'citadel_cutoff'=>['name'=>'Citadel Cutoff','brief'=>'Break the shielded defensive screen protecting the inner warzone.','enemy'=>'shield','level'=>12,'reward'=>['common_metal'=>520,'minor_metal'=>280,'precious_metal'=>85,'gmp'=>2100]],
    ];
}
function msw_boss_catalog(): array {
    return [
        'huge_hermit'=>['name'=>'Huge Hermit','enemy'=>'huge_hermit','threat'=>'S','brief'=>'Massive biological siege organism detected along the coastal perimeter.'],
        'rootmars'=>['name'=>'Rootmars','enemy'=>'rootmars','threat'=>'S++','brief'=>'Extreme extraterrestrial command target. Full combat readiness advised.'],
    ];
}
function msw_dispatch_catalog(): array {
    return [
        'border_patrol'=>['name'=>'Border Patrol','duration'=>600,'difficulty'=>55,'slots'=>2,'reward'=>['gmp'=>500,'common_metal'=>100]],
        'convoy_escort'=>['name'=>'Convoy Escort','duration'=>1800,'difficulty'=>120,'slots'=>3,'reward'=>['gmp'=>1100,'fuel'=>180,'minor_metal'=>90]],
        'deep_recon'=>['name'=>'Deep Recon','duration'=>3600,'difficulty'=>220,'slots'=>4,'reward'=>['gmp'=>1900,'precious_metal'=>45,'biological'=>120]],
        'black_zone'=>['name'=>'Black Zone Interdiction','duration'=>7200,'difficulty'=>360,'slots'=>4,'reward'=>['gmp'=>3600,'common_metal'=>400,'minor_metal'=>240,'precious_metal'=>70]],
    ];
}
function msw_rd_catalog(): array {
    return [
        'fulton'=>['name'=>'Fulton Recovery Pack','rd'=>1,'cost'=>['common_metal'=>60,'fuel'=>40],'quantity'=>4,'desc'=>'Baseline personnel recovery system. Available from R&D Level 1 so early staff progression cannot deadlock.'],
        'fulton_plus'=>['name'=>'Fulton+ Balloon Pack','rd'=>4,'cost'=>['common_metal'=>120,'fuel'=>80],'quantity'=>3,'desc'=>'Improved personnel recovery envelope.'],
        'cargo_fulton'=>['name'=>'Cargo Fulton Pack','rd'=>8,'cost'=>['common_metal'=>260,'minor_metal'=>100,'fuel'=>160],'quantity'=>2,'desc'=>'Extends recovery to ground vehicles.'],
        'wormhole_fulton'=>['name'=>'Wormhole Fulton','rd'=>15,'cost'=>['minor_metal'=>420,'precious_metal'=>120,'fuel'=>350],'quantity'=>1,'desc'=>'Advanced extraction for personnel, vehicles and aircraft.'],
    ];
}

function msw_sidequest_catalog(): array {
    return [
        'pow_beacon'=>[
            'name'=>'Lost POW Beacon',
            'brief'=>'Trace a damaged recovery beacon, break the Rebel cordon and secure the extraction lane.',
            'enemy'=>'rifle','level'=>1,
            'reward'=>['common_metal'=>110,'fuel'=>70,'gmp'=>350],
            'items'=>['fulton'=>1],
        ],
        'ammo_cache'=>[
            'name'=>'Buried Ammunition Cache',
            'brief'=>'Clear the defensive team guarding a hidden stockpile before the cache is relocated.',
            'enemy'=>'shield','level'=>4,
            'reward'=>['common_metal'=>180,'minor_metal'=>75,'gmp'=>650],
            'items'=>['fulton_plus'=>1],
        ],
        'downed_courier'=>[
            'name'=>'Downed Courier',
            'brief'=>'Intercept a fast Rebel courier and recover the intelligence package from the crash sector.',
            'enemy'=>'biker','level'=>7,
            'reward'=>['minor_metal'=>160,'fuel'=>170,'gmp'=>1000],
            'items'=>[],
        ],
    ];
}

function msw_trainer_catalog(): array {
    return [
        'sergeant_raven'=>[
            'name'=>'Sergeant Raven',
            'title'=>'Rebel Rifle Instructor',
            'brief'=>'A disciplined rifle specialist running live-fire drills for frontline troops.',
            'enemy'=>'rifle','level'=>3,
            'reward'=>['common_metal'=>160,'gmp'=>700],
        ],
        'captain_viper'=>[
            'name'=>'Captain Viper',
            'title'=>'Heavy Weapons Commander',
            'brief'=>'A veteran bazooka officer whose combat unit specializes in explosive pressure.',
            'enemy'=>'bazooka','level'=>6,
            'reward'=>['minor_metal'=>150,'fuel'=>120,'gmp'=>1200],
        ],
        'major_ironclad'=>[
            'name'=>'Major Ironclad',
            'title'=>'Mobile Assault Commander',
            'brief'=>'A veteran mobile commander fielding an elite Rebel Biker as the centerpiece of his command challenge.',
            'enemy'=>'biker','level'=>10,
            'reward'=>['common_metal'=>320,'minor_metal'=>220,'precious_metal'=>45,'gmp'=>1900],
        ],
    ];
}
