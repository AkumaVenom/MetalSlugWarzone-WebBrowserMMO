<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$u=msw_require_user();$uid=(int)$u['id'];$levels=msw_sector_levels($uid);$inv=msw_inventory($uid);
if(msw_is_post()){
    msw_verify_post();$key=(string)($_POST['recipe']??'');$cat=msw_rd_catalog();$levels=msw_sector_levels($uid);
    if(!isset($cat[$key]))msw_flash('Unknown R&D recipe.','error');
    elseif(!msw_rd_recipe_unlocked($uid,$cat[$key],$levels))msw_flash('Manufacturing requirements not met: '.msw_requirement_label(msw_rd_recipe_requirements($cat[$key])).'.','error');
    elseif(!msw_manufacture_item($uid,$key,(int)$cat[$key]['quantity'],$cat[$key]['cost']))msw_flash('Insufficient base materials.','error');
    else{msw_console_event_for_user($uid,'R&D','MANUFACTURE',$cat[$key]['name'].' manufactured x'.(int)$cat[$key]['quantity'].'.',['recipe'=>$key,'quantity'=>(int)$cat[$key]['quantity'],'requirements'=>msw_rd_recipe_requirements($cat[$key])]);msw_flash($cat[$key]['name'].' manufactured.','success');}
    msw_redirect('rd.php');
}
msw_header('R&D Laboratory','base.php');msw_alert(msw_flash());msw_resource_strip($uid);
?>
<section class="hero"><div class="eyebrow">R&D TEAM // LEVEL <?=intval($levels['rd'])?> · MEDICAL // LEVEL <?=intval($levels['medical'])?></div><h1>RESEARCH & <span>DEVELOPMENT</span></h1><p>Manufacturing is now a cross-sector capability matrix. Recovery hardware follows R&D milestones, while battlefield medicine requires both R&D fabrication and Medical Team certification. Materials are still debited atomically before persistent inventory is credited.</p></section>
<div class="grid g3" style="margin-top:18px"><?php foreach(msw_rd_catalog() as $key=>$r):$requirements=msw_rd_recipe_requirements($r);$locked=!msw_rd_recipe_unlocked($uid,$r,$levels);?><article class="panel"><div class="panel-body"><span class="badge"><?=msw_e(msw_requirement_label($requirements))?></span><h3><?=msw_e($r['name'])?></h3><p><?=msw_e($r['desc'])?></p><p><small>COST · <?php foreach($r['cost'] as $k=>$v):?><?=msw_e(strtoupper(str_replace('_',' ',$k)))?> <?=number_format($v)?> &nbsp; <?php endforeach;?></small></p><p>Inventory: <b><?=intval($inv[$key]??0)?></b></p><form method="post"><?=msw_csrf_field()?><input type="hidden" name="recipe" value="<?=msw_e($key)?>"><button <?=$locked?'disabled':''?>><?=$locked?'LOCKED':'Manufacture x'.intval($r['quantity'])?></button></form></div></article><?php endforeach;?></div>
<?php msw_footer(); ?>
