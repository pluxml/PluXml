<?php 
/**
 * Basic configuration view
 * @author	Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE
 **/


use Pluxml\PlxToken;
use Pluxml\PlxUtils;

// Header
include __DIR__ .'/../tags/top.php';
?>

<div class="adminheader">
	<h2 class="h3-like"><?= L_MENU_CONFIG ?></h2>
</div>

<div class="admin mtm grid-6">
	<div class="col-1 mtl">
		<?php include __DIR__ .'/../tags/configurationMenu.php'; ?>
	</div>
	<div class="panel col-5">
		<form action="configurationDisplay.php" method="post" id="form_settings">
			<div class="autogrid panel-header">
				<div>
					<h3 class="h4-like"><?= L_CONFIG_VIEW_FIELD ?></h3>
					<p><?= L_CONFIG_VIEW_PLUXML_RESSOURCES ?></p>
				</div>
				<div class="txtright">
					<?= PlxToken::getTokenPostMethod() ?>
					<input class="btn--primary" type="submit" value="<?= L_CONFIG_VIEW_UPDATE ?>" />
				</div>
			</div>
			<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsDisplayTop')) # Hook Plugins ?>
			<fieldset>
				<label for="id_hometemplate"><?= L_CONFIG_HOMETEMPLATE ?>&nbsp;:</label>
				<?php PlxUtils::printSelect('hometemplate', $aTemplates, $plxAdmin->aConf['hometemplate']) ?>
				<label for="id_tri"><?= L_CONFIG_VIEW_SORT ?>&nbsp;:</label>
				<?php PlxUtils::printSelect('tri', $aTriArts, $plxAdmin->aConf['tri']); ?>
				<label for="id_bypage"><?= L_CONFIG_VIEW_BYPAGE ?>&nbsp;:</label>
				<?php PlxUtils::printInput('bypage', $plxAdmin->aConf['bypage'], 'text', '2-4',false,'fieldnum'); ?>
				<label for="id_bypage_archives"><?= L_CONFIG_VIEW_BYPAGE_TAGS ?>&nbsp;:</label>
				<?php PlxUtils::printInput('bypage_tags', $plxAdmin->aConf['bypage_tags'], 'text', '2-4',false,'fieldnum'); ?>
				<label for="id_bypage_archives"><?= L_CONFIG_VIEW_BYPAGE_ARCHIVES ?>&nbsp;:</label>
				<?php PlxUtils::printInput('bypage_archives', $plxAdmin->aConf['bypage_archives'], 'text', '2-4',false,'fieldnum'); ?>
				<label for="id_bypage_admin"><?= L_CONFIG_VIEW_BYPAGE_ADMIN ?>&nbsp;:</label>
				<?php PlxUtils::printInput('bypage_admin', $plxAdmin->aConf['bypage_admin'], 'text', '2-4',false,'fieldnum'); ?>
				<label for="id_tri_coms"><?= L_CONFIG_VIEW_SORT_COMS ?>&nbsp;:</label>
				<?php PlxUtils::printSelect('tri_coms', $aTriComs, $plxAdmin->aConf['tri_coms']); ?>
				<label for="id_bypage_admin_coms"><?= L_CONFIG_VIEW_BYPAGE_ADMIN_COMS ?>&nbsp;:</label>
				<?php PlxUtils::printInput('bypage_admin_coms', $plxAdmin->aConf['bypage_admin_coms'], 'text', '2-4',false,'fieldnum'); ?>
				<label for="id_display_empty_cat"><?= L_CONFIG_VIEW_DISPLAY_EMPTY_CAT ?>&nbsp;:</label>
				<?php PlxUtils::printSelect('display_empty_cat',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['display_empty_cat']);?>
				<label><?= L_CONFIG_VIEW_IMAGES ?>&nbsp;:</label>
				<?php PlxUtils::printInput('images_l', $plxAdmin->aConf['images_l'], 'text', '4-4',false,'no-margin'); ?>
				&nbsp;x&nbsp;
				<?php PlxUtils::printInput('images_h', $plxAdmin->aConf['images_h'], 'text', '4-4',false,'no-margin'); ?>
				<label><?= L_CONFIG_VIEW_THUMBS ?>&nbsp;:</label>
				<?php PlxUtils::printInput('miniatures_l', $plxAdmin->aConf['miniatures_l'], 'text', '4-4',false,'no-margin'); ?>
				&nbsp;x&nbsp;
				<?php PlxUtils::printInput('miniatures_h', $plxAdmin->aConf['miniatures_h'], 'text', '4-4',false,'no-margin'); ?>
				<label for="id_thumbs"><?= L_MEDIAS_THUMBS ?>&nbsp;:</label>
				<?php PlxUtils::printSelect('thumbs',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['thumbs']);?>
				<label for="id_bypage_feed"><?= L_CONFIG_VIEW_BYPAGE_FEEDS ?>&nbsp;:</label>
				<?php PlxUtils::printInput('bypage_feed', $plxAdmin->aConf['bypage_feed'], 'text', '2-2',false,'fieldnum'); ?>
				<label for="id_feed_chapo"><?= L_CONFIG_VIEW_FEEDS_HEADLINE ?>&nbsp;:</label>
				<?php PlxUtils::printSelect('feed_chapo',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['feed_chapo']);?>
				<a class="hint"><span><?= L_CONFIG_VIEW_FEEDS_HEADLINE_HELP ?></span></a>
				<label for="id_content"><?= L_CONFIG_VIEW_FEEDS_FOOTER ?>&nbsp;:</label>
				<?php PlxUtils::printArea('content',PlxUtils::strCheck($plxAdmin->aConf['feed_footer']),140,5,false,'full-width'); ?>
			</fieldset>
			<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsDisplay')) # Hook Plugins ?>
		</form>
		<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsDisplayFoot')); ?>
	</div>
</div>

<?php
// Footer
include __DIR__ .'/../tags/foot.php';
?>