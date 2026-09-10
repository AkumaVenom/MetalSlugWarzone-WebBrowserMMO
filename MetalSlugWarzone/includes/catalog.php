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
/**
 * Per-sector Commander combat growth.
 *
 * Values are additive contribution rates per effective 120 sector-score points.
 * Partial score progress contributes immediately between whole displayed levels,
 * while high development uses a diminishing curve in game.php.
 */
function msw_commander_sector_stat_catalog(): array {
    return [
        // Commander combat growth is intentionally substantial: developing Mother Base
        // must make the player stronger than an equal-level field contact, not merely
        // offset enemy scaling. Values apply per effective 120 sector-score points and
        // also accrue fractionally while staff are progressing toward the next sector level.
        'combat'=>['attack'=>7.00],
        'rd'=>['attack'=>7.00,'speed'=>3.00],
        'support'=>['max_hp'=>8.00,'defense'=>2.00],
        'intel'=>['speed'=>4.00],
        'medical'=>['max_hp'=>18.00],
        'mess'=>['max_hp'=>6.00,'speed'=>2.00],
        'security'=>['defense'=>5.00],
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
 * Persistent global FOB overview biomes. Each biome can spawn an unlimited
 * sequence of database-backed world instances. Overview art is rendered at
 * exact native 2000x2000 pixels; FOB markers occupy authoritative grid slots.
 */
function msw_fob_biome_catalog(): array {
    return [
        'continental'=>[
            'name'=>'Continental','theatre'=>'Continental Interior','climate'=>'Dirt Highlands',
            'globe_label'=>'CONTINENTAL','image'=>'assets/maps/fob_world/Dirt_FOB_Overview_Map.png','w'=>2000,'h'=>2000,
            'skins'=>['land_dirt'],
        ],
        'forest'=>[
            'name'=>'Forest','theatre'=>'Forest Frontier','climate'=>'Temperate Forest',
            'globe_label'=>'FOREST','image'=>'assets/maps/fob_world/Forest_FOB_Overview_Map.png','w'=>2000,'h'=>2000,
            'skins'=>['land_forest'],
        ],
        'desert'=>[
            'name'=>'Desert','theatre'=>'Desert Frontier','climate'=>'Arid Expanse',
            'globe_label'=>'DESERT','image'=>'assets/maps/fob_world/Desert_FOB_Overview_Map.png','w'=>2000,'h'=>2000,
            'skins'=>['land_desert'],
        ],
        'arctic'=>[
            'name'=>'Arctic','theatre'=>'Arctic Frontier','climate'=>'Polar Expanse',
            'globe_label'=>'ARCTIC','image'=>'assets/maps/fob_world/Arctic_FOB_Overview_Map.png','w'=>2000,'h'=>2000,
            'skins'=>['land_snow'],
        ],
        'sea'=>[
            'name'=>'Sea','theatre'=>'Maritime Expanse','climate'=>'Open Ocean',
            'globe_label'=>'SEA','image'=>'assets/maps/fob_world/Sea_FOB_Overview_Map.png','w'=>2000,'h'=>2000,
            'skins'=>['sea_fob1','sea_fob2','sea_fob3'],
        ],
    ];
}

/**
 * Overview-world icon skin metadata. Keys intentionally reuse mother_base_key
 * so the overview sprite and the physical Mother Base remain coherent.
 */
function msw_fob_skin_catalog(): array {
    return [
        'land_dirt'=>['biome'=>'continental','icon'=>'assets/sprites/fob/Continental Mother Base.png'],
        'land_forest'=>['biome'=>'forest','icon'=>'assets/sprites/fob/Forest Mother Base.png'],
        'land_desert'=>['biome'=>'desert','icon'=>'assets/sprites/fob/Desert Mother Base.png'],
        'land_snow'=>['biome'=>'arctic','icon'=>'assets/sprites/fob/Arctic Mother Base.png'],
        'sea_fob1'=>['biome'=>'sea','icon'=>'assets/sprites/fob/Offshore Mother Base Alpha.png'],
        'sea_fob2'=>['biome'=>'sea','icon'=>'assets/sprites/fob/Offshore Mother Base Bravo.png'],
        'sea_fob3'=>['biome'=>'sea','icon'=>'assets/sprites/fob/Maritime Fortress Mother Base.png'],
    ];
}

function msw_fob_globe_image(): string { return 'assets/maps/fob_world/WorldGlobe_FullOverview.png'; }
function msw_fob_world_capacity(): int { return 144; }

/**
 * Normalized globe hotspot centers measured against the supplied 1254x1254
 * production globe. The values deliberately target the actual biome artwork:
 * Arctic ice, Continental Americas, Forest Eurasia, Desert Africa and open sea.
 */
function msw_fob_globe_hotspots(): array {
    return [
        'arctic'=>[50.0,14.0],
        'continental'=>[27.0,35.0],
        'forest'=>[70.5,34.0],
        'desert'=>[62.5,55.5],
        'sea'=>[49.5,76.5],
    ];
}

/**
 * Blue-noise-style FOB anchor constellation for the native 2000x2000 overview.
 *
 * These 144 centers were generated once, validated against the desktop marker
 * footprint, and are intentionally irregular rather than a visible row/column
 * grid. Every pair obeys the rectangular non-overlap envelope used by the UI.
 */
function msw_fob_slot_anchors(): array {
    return [
        [1292,638],
        [162,1860],
        [1872,1866],
        [100,147],
        [995,1501],
        [261,999],
        [1909,142],
        [1882,1103],
        [826,113],
        [824,969],
        [1383,92],
        [524,538],
        [1507,1495],
        [482,1480],
        [1780,617],
        [1286,1102],
        [104,568],
        [723,1871],
        [1261,1856],
        [99,1377],
        [925,492],
        [1902,1486],
        [457,153],
        [1584,923],
        [545,1156],
        [1597,343],
        [1103,244],
        [533,846],
        [1552,1824],
        [1084,840],
        [1013,1191],
        [1601,1217],
        [991,1809],
        [755,1341],
        [440,1778],
        [1245,1395],
        [264,356],
        [716,1609],
        [664,319],
        [1909,844],
        [1344,371],
        [782,698],
        [215,1601],
        [1898,397],
        [319,1256],
        [322,711],
        [1642,99],
        [1529,667],
        [94,817],
        [1724,1645],
        [1212,1637],
        [1354,884],
        [1795,1305],
        [98,1153],
        [1139,462],
        [877,301],
        [1405,1270],
        [716,505],
        [1399,1707],
        [459,354],
        [739,1146],
        [980,682],
        [1201,87],
        [1909,1685],
        [1714,788],
        [1471,1075],
        [644,92],
        [1761,265],
        [1125,1019],
        [282,88],
        [1715,1468],
        [442,1001],
        [1760,969],
        [1481,487],
        [305,1435],
        [337,519],
        [635,992],
        [877,1671],
        [1181,1232],
        [927,1342],
        [91,346],
        [1007,99],
        [585,1326],
        [548,1654],
        [1459,247],
        [1556,1660],
        [96,980],
        [1738,455],
        [614,700],
        [1355,1553],
        [925,847],
        [387,1624],
        [1717,1809],
        [699,839],
        [1080,1365],
        [1042,1654],
        [835,1507],
        [645,1470],
        [345,867],
        [1649,1074],
        [1276,227],
        [1143,625],
        [974,999],
        [287,1764],
        [95,1725],
        [585,1801],
        [1408,1872],
        [1224,783],
        [1140,1495],
        [1613,1365],
        [1642,568],
        [468,695],
        [178,699],
        [318,225],
        [350,1106],
        [877,1133],
        [1391,749],
        [1152,1760],
        [1013,348],
        [1279,491],
        [1744,1174],
        [177,456],
        [1880,714],
        [1195,340],
        [182,1268],
        [740,1740],
        [1563,790],
        [739,210],
        [1404,1399],
        [350,1875],
        [180,247],
        [1904,970],
        [126,1499],
        [1889,517],
        [1907,274],
        [444,1365],
        [597,214],
        [1289,988],
        [1598,216],
        [1439,1172],
        [1898,1207],
        [789,1242],
        [679,602],
        [843,602],
    ];
}

/**
 * Each biome/shard permutes the same validated anchor constellation so a
 * partially populated world does not reproduce the same spatial pattern as the
 * next shard. Slot ownership remains a stable database identity; only its
 * deterministic native-map center is derived here.
 */
function msw_fob_slot_position(int $slotIndex,string $biomeKey='',int $shardIndex=1): array {
    $anchors=msw_fob_slot_anchors();
    $capacity=count($anchors);
    if($capacity!==msw_fob_world_capacity()) throw new RuntimeException('FOB anchor catalog capacity mismatch.');
    $slotIndex=max(0,min($capacity-1,$slotIndex));
    $seed=(int)sprintf('%u',crc32(strtolower($biomeKey).'|'.max(1,$shardIndex).'|blue-noise-v2'));
    $steps=[5,7,11,13,17,19,23,25,29,31,35,37,41,43,47,49,53,55,59,61,65,67,71,73,77,79,83,85,89,91,95,97,101,103,107,109,113,115,119,121,125,127,131,133,137,139,143];
    $offset=$seed%$capacity;
    $step=$steps[intdiv($seed,$capacity)%count($steps)];
    $anchorIndex=($offset+($slotIndex*$step))%$capacity;
    return $anchors[$anchorIndex];
}

/** Desktop marker clearance envelope used by setup validation. */
function msw_fob_slot_clearance(): array { return [136,96]; }

function msw_fob_skin_is_valid_for_biome(string $skinKey,string $biomeKey): bool {
    $biome=msw_fob_biome_catalog()[$biomeKey]??null;
    return $biome!==null && in_array($skinKey,$biome['skins'],true);
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

/** Minimum contact level shared by human encounters and autonomous recovery. */
function msw_warzone_enemy_level_floor(int $threat): int {
    if($threat<=12)return 1;
    return 20+5*(min(23,$threat)-13);
}

function msw_map_catalog(): array {
    return [
        'jungle_front'=>['name'=>'Jungle Front','region'=>'Outer Warzone','image'=>'assets/maps/jungle_front.png','thumbnail'=>'assets/maps/thumbnails/jungle_front.jpg','w'=>1448,'h'=>1086,'level'=>1,'spawn'=>[720,900],'encounters'=>['rifle','bazooka','shield']],
        'industrial_rail'=>['name'=>'Industrial Railhead','region'=>'Occupied Corridor','image'=>'assets/maps/industrial_rail.png','thumbnail'=>'assets/maps/thumbnails/industrial_rail.jpg','w'=>1402,'h'=>1122,'level'=>3,'spawn'=>[720,820],'encounters'=>['rifle','minigun','biker']],
        'ruined_temple'=>['name'=>'Ruined Temple','region'=>'Ancient Front','image'=>'assets/maps/ruined_temple.png','thumbnail'=>'assets/maps/thumbnails/ruined_temple.jpg','w'=>1448,'h'=>1086,'level'=>5,'spawn'=>[720,1000],'encounters'=>['bazooka','shield','biker']],
        'desert_line'=>['name'=>'Occupied City','region'=>'Urban Warzone','image'=>'assets/maps/desert_line.png','thumbnail'=>'assets/maps/thumbnails/desert_line.jpg','w'=>1254,'h'=>1254,'level'=>7,'spawn'=>[645,1035],'encounters'=>['rifle','biker','minigun']],
        'coastal_breach'=>['name'=>'Coastal Breach','region'=>'Eastern Harbour','image'=>'assets/maps/coastal_breach.png','thumbnail'=>'assets/maps/thumbnails/coastal_breach.jpg','w'=>1448,'h'=>1086,'level'=>9,'spawn'=>[700,820],'encounters'=>['minigun','biker','shield']],
        'fortress_approach'=>['name'=>'Iron Citadel Interior','region'=>'Fortress Warzone','image'=>'assets/maps/fortress_approach.png','thumbnail'=>'assets/maps/thumbnails/fortress_approach.jpg','w'=>1448,'h'=>1086,'level'=>12,'spawn'=>[710,900],'encounters'=>['shield','minigun','biker']],
        'swamp_encampment'=>['name'=>'Swamp Encampment','region'=>'Deep Jungle','image'=>'assets/maps/swamp_encampment.png','thumbnail'=>'assets/maps/thumbnails/swamp_encampment.jpg','w'=>1672,'h'=>941,'level'=>13,'spawn'=>[835,775],'encounters'=>['rifle','bazooka','shield']],
        'abandoned_carnival'=>['name'=>'Abandoned Carnival','region'=>'Occupied Fairgrounds','image'=>'assets/maps/abandoned_carnival.png','thumbnail'=>'assets/maps/thumbnails/abandoned_carnival.jpg','w'=>1672,'h'=>941,'level'=>14,'spawn'=>[875,725],'encounters'=>['rifle','biker','bazooka']],
        'neon_district'=>['name'=>'Neon District','region'=>'Rainfall City','image'=>'assets/maps/neon_district.png','thumbnail'=>'assets/maps/thumbnails/neon_district.jpg','w'=>1672,'h'=>941,'level'=>15,'spawn'=>[835,725],'encounters'=>['biker','rifle','minigun']],
        'scrapyard_depot'=>['name'=>'Scrapyard Depot','region'=>'Rebel Salvage Belt','image'=>'assets/maps/scrapyard_depot.png','thumbnail'=>'assets/maps/thumbnails/scrapyard_depot.jpg','w'=>1672,'h'=>941,'level'=>16,'spawn'=>[835,735],'encounters'=>['biker','minigun','bazooka']],
        'canyon_missile_base'=>['name'=>'Canyon Missile Base','region'=>'Redstone Canyon','image'=>'assets/maps/canyon_missile_base.png','thumbnail'=>'assets/maps/thumbnails/canyon_missile_base.jpg','w'=>1672,'h'=>941,'level'=>17,'spawn'=>[835,780],'encounters'=>['bazooka','minigun','shield']],
        'alpine_radar'=>['name'=>'Alpine Radar Station','region'=>'Mountain Command','image'=>'assets/maps/alpine_radar.png','thumbnail'=>'assets/maps/thumbnails/alpine_radar.jpg','w'=>1672,'h'=>941,'level'=>18,'spawn'=>[835,735],'encounters'=>['shield','bazooka','minigun']],
        'offshore_platform'=>['name'=>'Offshore Platform','region'=>'Blackwater Supply Line','image'=>'assets/maps/offshore_platform.png','thumbnail'=>'assets/maps/thumbnails/offshore_platform.jpg','w'=>1672,'h'=>941,'level'=>19,'spawn'=>[835,695],'encounters'=>['minigun','shield','bazooka']],
        'subterranean_terminus'=>['name'=>'Subterranean Terminus','region'=>'Underground Transit','image'=>'assets/maps/subterranean_terminus.png','thumbnail'=>'assets/maps/thumbnails/subterranean_terminus.jpg','w'=>1672,'h'=>941,'level'=>20,'spawn'=>[835,700],'encounters'=>['shield','minigun','biker']],
        'volcanic_forge'=>['name'=>'Volcanic Forge','region'=>'Inferno Production Sector','image'=>'assets/maps/volcanic_forge.png','thumbnail'=>'assets/maps/thumbnails/volcanic_forge.jpg','w'=>1672,'h'=>941,'level'=>21,'spawn'=>[835,715],'encounters'=>['minigun','bazooka','shield']],
        'containment_laboratory'=>['name'=>'Containment Laboratory','region'=>'Blacksite Research','image'=>'assets/maps/containment_laboratory.png','thumbnail'=>'assets/maps/thumbnails/containment_laboratory.jpg','w'=>1672,'h'=>941,'level'=>22,'spawn'=>[915,780],'encounters'=>['shield','minigun','bazooka']],
        'lunar_outpost'=>['name'=>'Lunar Outpost','region'=>'Orbital Warzone','image'=>'assets/maps/lunar_outpost.png','thumbnail'=>'assets/maps/thumbnails/lunar_outpost.jpg','w'=>1672,'h'=>941,'level'=>23,'spawn'=>[980,780],'encounters'=>['bazooka','minigun','shield']],
    ];
}

/**
 * Authored server collision for the original and v4 expansion warzones.
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
        'swamp_encampment'=>[
            'bounds'=>[40,185,1630,900],
            'rects'=>[
                [40,0,530,225,'northern jungle camp'],
                [520,0,915,215,'northern bunker'],
                [915,0,1190,245,'communications bunker'],
                [1180,0,1672,235,'northeastern bunker'],
                [0,225,205,695,'western marsh and guard post'],
                [1460,240,1672,750,'eastern marsh and guard post'],
                [475,320,675,445,'camouflaged supply barricade'],
                [990,305,1155,455,'floodlight and supply barricade'],
                [485,530,655,675,'southern sandbag emplacement'],
                [995,555,1160,675,'covered supply position'],
                [0,685,430,941,'southwestern swamp'],
                [400,825,705,941,'southern swamp'],
                [1110,730,1672,941,'southeastern swamp'],
                [940,825,1120,941,'southern swamp edge'],
            ],
        ],
        'abandoned_carnival'=>[
            'bounds'=>[40,220,1630,905],
            'rects'=>[
                [0,0,1672,220,'northern rides and carnival stalls'],
                [0,220,270,665,'western carnival arch and fence'],
                [1375,250,1672,575,'eastern bumper-car pavilion'],
                [375,280,655,445,'ticket booth and supply barricade'],
                [1030,270,1265,390,'covered cargo stack'],
                [370,575,630,690,'southern supply barricade'],
                [0,775,805,941,'southern fence and gatehouse'],
                [945,745,1672,941,'southern fence and gatehouse'],
            ],
        ],
        'neon_district'=>[
            'bounds'=>[105,205,1570,790],
            'rects'=>[
                [0,0,710,225,'northwestern city block'],
                [925,0,1672,225,'northeastern city block'],
                [0,200,135,790,'western storefront and railing'],
                [1535,200,1672,790,'eastern storefront and railing'],
                [365,280,605,395,'northwestern road barricade'],
                [1035,270,1270,415,'northeastern road barricade'],
                [370,490,605,660,'southwestern road barricade'],
                [1045,545,1275,670,'southeastern road barricade'],
            ],
        ],
        'scrapyard_depot'=>[
            'bounds'=>[125,175,1535,795],
            'rects'=>[
                [0,0,1672,180,'northern workshops and vehicle stacks'],
                [0,175,175,610,'western salvage wall'],
                [0,610,290,795,'southwestern wreck stacks'],
                [1480,175,1672,795,'eastern salvage wall'],
                [380,255,500,455,'inner salvage wall'],
                [440,375,870,490,'armoured salvage barricade'],
                [1060,195,1330,355,'northern scrap pile'],
                [1005,485,1325,660,'excavator and southern scrap pile'],
                [290,760,410,880,'southern forklift'],
                [410,790,1672,941,'southern fence and gate'],
            ],
        ],
        'canyon_missile_base'=>[
            'bounds'=>[45,205,1630,895],
            'rects'=>[
                [0,0,535,245,'northwestern missile hangar'],
                [530,0,1390,210,'northern bunker and missile loading line'],
                [1380,0,1672,270,'northeastern communications building'],
                [0,330,145,410,'western helipad guardrail'],
                [0,410,70,595,'western cliff and stairs'],
                [0,600,235,820,'southwestern guard tower'],
                [1550,320,1672,615,'eastern helipad guardrail'],
                [1430,625,1672,820,'eastern supply truck'],
                [550,325,770,440,'northern rocky supply island'],
                [1025,450,1325,605,'eastern supply island'],
                [280,575,575,735,'southwestern rock shelf'],
                [0,830,740,941,'southern fence'],
                [975,830,1672,941,'southern fence'],
            ],
        ],
        'alpine_radar'=>[
            'bounds'=>[45,180,1625,815],
            'rects'=>[
                [0,0,1672,195,'northern radar and command complex'],
                [0,185,285,270,'northwestern retaining wall'],
                [1490,190,1672,285,'northeastern retaining wall'],
                [0,280,185,365,'western retaining wall'],
                [0,430,155,525,'western supply stack'],
                [0,570,255,760,'southwestern guard post'],
                [1485,605,1672,760,'southeastern military truck'],
                [1590,275,1672,605,'eastern cliff'],
                [625,215,920,425,'northern rock and supply island'],
                [1100,225,1325,420,'northeastern communications barricade'],
                [465,475,720,655,'southwestern rocky emplacement'],
                [1120,465,1395,670,'southeastern rocky emplacement'],
                [0,760,580,941,'southern retaining wall'],
                [580,800,750,941,'southern retaining wall'],
                [925,800,1320,941,'southern retaining wall'],
                [1320,750,1672,941,'southern retaining wall'],
            ],
        ],
        'offshore_platform'=>[
            'bounds'=>[50,225,1625,755],
            'rects'=>[
                [0,0,1672,225,'northern offshore refinery buildings'],
                [0,225,185,290,'northwestern platform stairs'],
                [1485,225,1672,290,'northeastern platform stairs'],
                [0,275,125,615,'western platform pipes and cargo'],
                [1535,275,1672,570,'eastern platform pipes and cargo'],
                [1410,555,1672,820,'southeastern crane and cargo'],
                [0,695,160,820,'southwestern platform cargo'],
                [485,255,770,485,'central refinery machinery'],
                [1080,315,1280,465,'eastern cargo stack'],
                [900,505,1195,675,'southern cable and cargo machinery'],
            ],
        ],
        'subterranean_terminus'=>[
            'bounds'=>[105,175,1565,790],
            'rects'=>[
                [0,0,1672,175,'northern railway station wall'],
                [0,175,170,355,'northwestern channel and cargo'],
                [0,355,125,710,'western water channel and railing'],
                [1515,175,1672,710,'eastern water channel and railing'],
                [455,195,760,370,'ticket kiosk and cargo'],
                [1005,360,1265,500,'bench and supply barricade'],
                [425,530,640,660,'southwestern cargo stack'],
                [0,775,635,941,'southwestern station wall'],
                [1035,775,1672,941,'southeastern station wall'],
                [625,695,705,810,'western stair lamp pedestal'],
                [975,695,1050,810,'eastern stair lamp pedestal'],
            ],
        ],
        'volcanic_forge'=>[
            'bounds'=>[55,225,1615,790],
            'rects'=>[
                [0,0,1672,225,'northern smelter complex'],
                [55,225,335,310,'northwestern ore wagons'],
                [1360,100,1630,325,'northeastern loading crane'],
                [1530,310,1672,400,'eastern ore wagon'],
                [0,315,195,450,'western ore platform'],
                [0,435,130,680,'western lava-side cargo'],
                [1500,480,1672,640,'eastern supply stack'],
                [500,295,735,445,'central western furnace supplies'],
                [965,290,1220,430,'central eastern ore barricade'],
                [445,555,620,655,'southwestern ore wagon'],
                [1000,500,1260,645,'southeastern furnace supplies'],
                [0,745,420,941,'southwestern lava wall'],
                [500,760,720,941,'southern supply wall'],
                [745,770,960,941,'southern gate'],
                [970,760,1210,941,'southern supply wall'],
                [1230,745,1672,941,'southeastern lava wall'],
            ],
        ],
        'containment_laboratory'=>[
            'bounds'=>[55,195,1620,840],
            'rects'=>[
                [0,0,1672,195,'northern containment chambers'],
                [0,195,265,290,'northwestern service stairs and equipment'],
                [1500,195,1672,295,'northeastern service equipment'],
                [0,290,140,730,'western bulkhead and cargo'],
                [1530,310,1672,730,'eastern bulkhead and cargo'],
                [0,730,230,850,'southwestern cargo'],
                [1440,730,1672,850,'southeastern cargo'],
                [435,310,675,450,'western analysis station'],
                [1020,350,1280,510,'eastern analysis station'],
                [650,605,885,740,'southern research station'],
            ],
        ],
        'lunar_outpost'=>[
            'bounds'=>[40,255,1630,900],
            'rects'=>[
                [0,0,735,270,'northern habitation domes'],
                [735,0,1075,205,'northern security gate'],
                [1075,0,1672,295,'northern shuttle hangar'],
                [0,295,160,445,'western rock and floodlight'],
                [1495,370,1672,690,'eastern ridge and cargo'],
                [390,320,680,460,'northwestern oxygen and cargo island'],
                [1060,325,1320,465,'northeastern communications island'],
                [565,505,855,675,'central rocky supply island'],
                [135,610,330,755,'southwestern rock and supply island'],
                [1160,635,1420,775,'southeastern communications island'],
                [0,800,425,941,'southwestern crater and rock rim'],
                [405,850,875,941,'southern rock rim'],
                [1095,850,1500,941,'southeastern rock rim'],
                [1480,755,1672,941,'southeastern perimeter cargo'],
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
        'cargo_fulton'=>['name'=>'Cargo Fulton','rd'=>5,'bonus'=>0.08,'classes'=>['infantry','heavy_infantry','vehicle']],
        'wormhole_fulton'=>['name'=>'Wormhole Fulton','rd'=>8,'bonus'=>0.22,'classes'=>['infantry','heavy_infantry','vehicle','air']],
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
        'marsh_signal_cut'=>['name'=>'Marsh Signal Cut','brief'=>'Defeat the bazooka patrol guarding the encampment\'s supply channel and recover its hidden stores.','enemy'=>'bazooka','level'=>13,'commander_xp'=>240,'reward'=>['common_metal'=>600,'fuel'=>360,'biological'=>140,'gmp'=>2400],'map_key'=>'swamp_encampment'],
        'midway_intercept'=>['name'=>'Midway Intercept','brief'=>'Intercept the Rebel biker courier using the abandoned fairgrounds to move supplies between occupied sectors.','enemy'=>'biker','level'=>14,'commander_xp'=>280,'reward'=>['common_metal'=>680,'minor_metal'=>340,'fuel'=>400,'gmp'=>2700],'map_key'=>'abandoned_carnival'],
        'neon_blackout'=>['name'=>'Neon Blackout','brief'=>'Break the heavy-weapons patrol holding the rain-soaked crossroads and seize its reserve equipment.','enemy'=>'minigun','level'=>15,'commander_xp'=>320,'reward'=>['minor_metal'=>440,'precious_metal'=>115,'fuel'=>470,'gmp'=>3000],'map_key'=>'neon_district'],
        'scrapline_ambush'=>['name'=>'Scrapline Ambush','brief'=>'Engage the bazooka guard protecting the depot\'s salvaged armour and recover usable construction materials.','enemy'=>'bazooka','level'=>16,'commander_xp'=>360,'reward'=>['common_metal'=>900,'minor_metal'=>480,'precious_metal'=>135,'gmp'=>3400],'map_key'=>'scrapyard_depot'],
        'launch_window'=>['name'=>'Launch Window','brief'=>'Defeat the shielded security detail controlling the missile loading line and claim its stockpiled supplies.','enemy'=>'shield','level'=>17,'commander_xp'=>400,'reward'=>['minor_metal'=>520,'precious_metal'=>150,'fuel'=>620,'gmp'=>3800],'map_key'=>'canyon_missile_base'],
        'whiteout_relay'=>['name'=>'Whiteout Relay','brief'=>'Eliminate the bazooka sentry defending the mountain communications perimeter and recover its technical stores.','enemy'=>'bazooka','level'=>18,'commander_xp'=>440,'reward'=>['common_metal'=>1050,'minor_metal'=>580,'precious_metal'=>175,'gmp'=>4200],'map_key'=>'alpine_radar'],
        'blackwater_lockdown'=>['name'=>'Blackwater Lockdown','brief'=>'Break the minigun patrol controlling the offshore loading deck and secure a share of the platform\'s fuel reserves.','enemy'=>'minigun','level'=>19,'commander_xp'=>480,'reward'=>['common_metal'=>1150,'minor_metal'=>600,'fuel'=>820,'gmp'=>4700],'map_key'=>'offshore_platform'],
        'last_train_out'=>['name'=>'Last Train Out','brief'=>'Defeat the Rebel biker escort moving cargo through the underground terminus and recover the shipment.','enemy'=>'biker','level'=>20,'commander_xp'=>520,'reward'=>['common_metal'=>1250,'minor_metal'=>680,'precious_metal'=>210,'gmp'=>5200],'map_key'=>'subterranean_terminus'],
        'forge_breaker'=>['name'=>'Forge Breaker','brief'=>'Overcome the minigun guard defending the forge\'s production stockpile and claim refined war materials.','enemy'=>'minigun','level'=>21,'commander_xp'=>560,'reward'=>['common_metal'=>1400,'minor_metal'=>760,'precious_metal'=>240,'gmp'=>5800],'map_key'=>'volcanic_forge'],
        'protocol_severance'=>['name'=>'Protocol Severance','brief'=>'Defeat the shielded blacksite guard protecting the laboratory\'s research stores and recover specialist materials.','enemy'=>'shield','level'=>22,'commander_xp'=>600,'reward'=>['minor_metal'=>800,'precious_metal'=>265,'biological'=>620,'gmp'=>6400],'map_key'=>'containment_laboratory'],
        'moonfall_directive'=>['name'=>'Moonfall Directive','brief'=>'Engage the elite bazooka defender holding the lunar supply perimeter and seize its orbital reserve cargo.','enemy'=>'bazooka','level'=>23,'commander_xp'=>640,'reward'=>['minor_metal'=>900,'precious_metal'=>300,'fuel'=>1000,'gmp'=>7100],'map_key'=>'lunar_outpost'],
    ];
}
function msw_boss_catalog(): array {
    return [
        'huge_hermit'=>['name'=>'Huge Hermit','enemy'=>'huge_hermit','threat'=>'S','level'=>12,'brief'=>'Massive biological siege organism detected along the coastal perimeter.'],
        'rootmars'=>['name'=>'Rootmars','enemy'=>'rootmars','threat'=>'S++','level'=>15,'brief'=>'Extreme extraterrestrial command target. Full combat readiness advised.'],
    ];
}
function msw_dispatch_catalog(): array {
    return [
        'border_patrol'=>['name'=>'Border Patrol','duration'=>600,'difficulty'=>55,'slots'=>2,'reward'=>['gmp'=>500,'common_metal'=>100]],
        'convoy_escort'=>['name'=>'Convoy Escort','duration'=>1800,'difficulty'=>120,'slots'=>3,'reward'=>['gmp'=>1100,'fuel'=>180,'minor_metal'=>90]],
        'deep_recon'=>['name'=>'Deep Recon','duration'=>3600,'difficulty'=>220,'slots'=>4,'reward'=>['gmp'=>1900,'precious_metal'=>45,'biological'=>120]],
        'black_zone'=>['name'=>'Black Zone Interdiction','duration'=>7200,'difficulty'=>360,'slots'=>4,'reward'=>['gmp'=>3600,'common_metal'=>400,'minor_metal'=>240,'precious_metal'=>70]],
        'marsh_supply_run'=>['name'=>'Marsh Supply Run','brief'=>'Send a veteran squad through the swamp channels to recover abandoned fuel and field supplies.','level'=>13,'duration'=>9000,'difficulty'=>420,'slots'=>4,'staff_xp_success'=>120,'staff_xp_failure'=>35,'reward'=>['gmp'=>4500,'common_metal'=>500,'fuel'=>240,'biological'=>180],'map_key'=>'swamp_encampment','recommended_staff_level'=>20],
        'fairground_sweep'=>['name'=>'Fairground Sweep','brief'=>'Sweep the shuttered carnival stalls for Rebel caches while covering the team\'s extraction route.','level'=>14,'duration'=>10800,'difficulty'=>480,'slots'=>4,'staff_xp_success'=>150,'staff_xp_failure'=>40,'reward'=>['gmp'=>5600,'common_metal'=>620,'minor_metal'=>320,'fuel'=>280],'map_key'=>'abandoned_carnival','recommended_staff_level'=>25],
        'neon_courier_hunt'=>['name'=>'Neon Courier Hunt','brief'=>'Track supply couriers through the district\'s guarded crossroads and recover their technical cargo.','level'=>15,'duration'=>12600,'difficulty'=>540,'slots'=>4,'staff_xp_success'=>180,'staff_xp_failure'=>45,'reward'=>['gmp'=>6800,'minor_metal'=>440,'precious_metal'=>120,'fuel'=>420],'map_key'=>'neon_district','recommended_staff_level'=>30],
        'salvage_belt_recovery'=>['name'=>'Salvage Belt Recovery','brief'=>'Escort a recovery team into the occupied scrapyard to bring usable armour and machinery back to Mother Base.','level'=>16,'duration'=>14400,'difficulty'=>600,'slots'=>4,'staff_xp_success'=>210,'staff_xp_failure'=>50,'reward'=>['gmp'=>8000,'common_metal'=>900,'minor_metal'=>530,'precious_metal'=>145],'map_key'=>'scrapyard_depot','recommended_staff_level'=>35],
        'canyon_stockpile_raid'=>['name'=>'Canyon Stockpile Raid','brief'=>'Dispatch a strike team against the canyon loading depots to recover fuel and missile-grade materials.','level'=>17,'duration'=>16200,'difficulty'=>660,'slots'=>4,'staff_xp_success'=>240,'staff_xp_failure'=>55,'reward'=>['gmp'=>9300,'minor_metal'=>620,'precious_metal'=>180,'fuel'=>620],'map_key'=>'canyon_missile_base','recommended_staff_level'=>40],
        'alpine_relay_recovery'=>['name'=>'Alpine Relay Recovery','brief'=>'Send specialists with armed cover to recover technical equipment from the radar station\'s outer stores.','level'=>18,'duration'=>18000,'difficulty'=>720,'slots'=>4,'staff_xp_success'=>270,'staff_xp_failure'=>60,'reward'=>['gmp'=>10600,'common_metal'=>1150,'minor_metal'=>700,'precious_metal'=>210],'map_key'=>'alpine_radar','recommended_staff_level'=>45],
        'blackwater_fuel_lift'=>['name'=>'Blackwater Fuel Lift','brief'=>'Secure a platform supply shipment and escort recovered fuel through the contested offshore approaches.','level'=>19,'duration'=>19800,'difficulty'=>780,'slots'=>4,'staff_xp_success'=>300,'staff_xp_failure'=>65,'reward'=>['gmp'=>12000,'common_metal'=>1300,'minor_metal'=>760,'fuel'=>950],'map_key'=>'offshore_platform','recommended_staff_level'=>50],
        'tunnel_freight_intercept'=>['name'=>'Tunnel Freight Intercept','brief'=>'Deploy a squad to intercept guarded freight moving through the underground transport network.','level'=>20,'duration'=>21600,'difficulty'=>840,'slots'=>4,'staff_xp_success'=>330,'staff_xp_failure'=>70,'reward'=>['gmp'=>13400,'common_metal'=>1450,'minor_metal'=>830,'precious_metal'=>260],'map_key'=>'subterranean_terminus','recommended_staff_level'=>55],
        'inferno_material_recovery'=>['name'=>'Inferno Material Recovery','brief'=>'Recover refined metal from the forge\'s heavily defended industrial stores and cover the outbound cargo team.','level'=>21,'duration'=>23400,'difficulty'=>900,'slots'=>4,'staff_xp_success'=>360,'staff_xp_failure'=>75,'reward'=>['gmp'=>14900,'common_metal'=>1650,'minor_metal'=>950,'precious_metal'=>295],'map_key'=>'volcanic_forge','recommended_staff_level'=>60],
        'blacksite_research_extraction'=>['name'=>'Blacksite Research Extraction','brief'=>'Escort a specialist recovery team into the containment complex to retrieve research supplies under armed guard.','level'=>22,'duration'=>25200,'difficulty'=>960,'slots'=>4,'staff_xp_success'=>390,'staff_xp_failure'=>80,'reward'=>['gmp'=>16400,'minor_metal'=>1050,'precious_metal'=>330,'biological'=>800],'map_key'=>'containment_laboratory','recommended_staff_level'=>65],
        'orbital_supply_interdiction'=>['name'=>'Orbital Supply Interdiction','brief'=>'Commit an elite squad to intercept the lunar outpost\'s reserve cargo and escort it clear of the defended perimeter.','level'=>23,'duration'=>27000,'difficulty'=>1020,'slots'=>4,'staff_xp_success'=>420,'staff_xp_failure'=>85,'reward'=>['gmp'=>18000,'minor_metal'=>1200,'precious_metal'=>370,'fuel'=>1300],'map_key'=>'lunar_outpost','recommended_staff_level'=>70],
    ];
}
function msw_rd_catalog(): array {
    return [
        'fulton'=>['name'=>'Fulton Recovery Pack','rd'=>1,'requirements'=>['rd'=>1],'cost'=>['common_metal'=>60,'fuel'=>40],'quantity'=>4,'desc'=>'Standard Fulton gear for recovering enemy personnel. Available from R&D Lv 1.'],
        'field_medkit'=>['name'=>'Combat Medkit','rd'=>2,'requirements'=>['rd'=>2,'medical'=>2],'cost'=>['common_metal'=>35,'biological'=>30],'quantity'=>3,'desc'=>'Single-use combat medicine. Restores 35 Commander HP and uses your turn.'],
        'fulton_plus'=>['name'=>'Fulton+ Balloon Pack','rd'=>4,'requirements'=>['rd'=>4],'cost'=>['common_metal'=>120,'fuel'=>80],'quantity'=>3,'desc'=>'Improved Fulton balloons with a higher personnel recovery chance.'],
        'cargo_fulton'=>['name'=>'Cargo Fulton Pack','rd'=>5,'requirements'=>['rd'=>5],'cost'=>['common_metal'=>260,'minor_metal'=>100,'fuel'=>160],'quantity'=>2,'desc'=>'Heavy-duty Fulton gear that can recover ground vehicles.'],
        'trauma_kit'=>['name'=>'Trauma Kit','rd'=>5,'requirements'=>['rd'=>5,'medical'=>5],'cost'=>['common_metal'=>70,'minor_metal'=>35,'biological'=>70],'quantity'=>2,'desc'=>'Advanced combat medicine. Restores 80 Commander HP and uses your turn.'],
        'wormhole_fulton'=>['name'=>'Wormhole Fulton','rd'=>8,'requirements'=>['rd'=>8],'cost'=>['minor_metal'=>420,'precious_metal'=>120,'fuel'=>350],'quantity'=>1,'desc'=>'Top-tier recovery system for personnel, vehicles and aircraft.'],
        'nanomed_injector'=>['name'=>'Nanomed Injector','rd'=>8,'requirements'=>['rd'=>8,'medical'=>8],'cost'=>['minor_metal'=>110,'precious_metal'=>35,'biological'=>140],'quantity'=>1,'desc'=>'Top-tier combat medicine. Restores up to 160 Commander HP and gains extra healing from Support Team bonuses.'],
    ];
}

function msw_battle_item_catalog(): array {
    return [
        'field_medkit'=>['name'=>'Combat Medkit','heal'=>35,'requirements'=>['medical'=>2,'rd'=>2]],
        'trauma_kit'=>['name'=>'Trauma Kit','heal'=>80,'requirements'=>['medical'=>5,'rd'=>5]],
        'nanomed_injector'=>['name'=>'Nanomed Injector','heal'=>160,'requirements'=>['medical'=>8,'rd'=>8]],
    ];
}

/**
 * Mother Base level-gated systems. Every entry below maps to a runtime effect,
 * not a presentation-only badge. The Base screen uses this same catalog to
 * explain what is active and what the next staff milestone will unlock.
 */
function msw_sector_unlock_catalog(): array {
    return [
        'rd'=>[
            ['level'=>1,'name'=>'Personnel Fulton Fabrication','effect'=>'Manufacture standard Fulton Recovery Packs.'],
            ['level'=>4,'name'=>'Fulton+ Envelope','effect'=>'Manufacture higher-success personnel recovery balloons.'],
            ['level'=>5,'name'=>'Cargo Fulton Certification','effect'=>'Manufacture and deploy recovery systems for ground vehicles.'],
            ['level'=>8,'name'=>'Wormhole Extraction','effect'=>'Manufacture the universal personnel / vehicle / aircraft extraction system.'],
        ],
        'medical'=>[
            ['level'=>2,'name'=>'Combat Medkit Protocol','effect'=>'Manufacture and use Combat Medkits during PvE engagements.'],
            ['level'=>5,'name'=>'Trauma Response Protocol','effect'=>'Manufacture and use 80 HP Trauma Kits during PvE engagements.'],
            ['level'=>8,'name'=>'Nanomed Protocol','effect'=>'Manufacture and use 160 HP Nanomed Injectors during PvE engagements.'],
        ],
        'intel'=>[
            ['level'=>2,'name'=>'Tactical Threat Lens','effect'=>'Reveal enemy attack, defense and speed during PvE battles.'],
            ['level'=>4,'name'=>'Weakness Matrix','effect'=>'Show move effectiveness and recommend your strongest attack for the matchup.'],
            ['level'=>6,'name'=>'Fulton Forecast','effect'=>'Show the current Fulton recovery chance before you use the item.'],
            ['level'=>8,'name'=>'Countermeasure Analysis','effect'=>'Enemy counterattack accuracy is reduced by 6%.'],
        ],
        'security'=>[
            ['level'=>1,'name'=>'Security Escort Detail','effect'=>'Choose up to two Security Team soldiers for covering fire and rotating Commander damage interception.'],
            ['level'=>4,'name'=>'Covering Fire Drill','effect'=>'Security backup accuracy improves by 5%.'],
            ['level'=>7,'name'=>'Controlled Burst Doctrine','effect'=>'Security backup shots deal slightly more damage while staying weaker than your Commander.'],
        ],
        'support'=>[
            ['level'=>3,'name'=>'Field Logistics','effect'=>'Battlefield medical items restore 15% additional HP.'],
            ['level'=>6,'name'=>'Rapid Medical Resupply','effect'=>'Battlefield medical items restore a total 25% additional HP.'],
        ],
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
