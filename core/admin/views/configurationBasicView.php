<?php 
/**
 * Basic configuration view
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE
 **/

use Pluxml\PlxToken;
use Pluxml\PlxUtils;
use Pluxml\PlxTimezones;

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
		<form action="configurationBasic.php" method="post" id="form_settings">
			<div class="autogrid panel-header">
				<h3 class="h4-like"><?= L_CONFIG_BASE_CONFIG_TITLE ?></h3>
				<div class="txtright">
					<?= PlxToken::getTokenPostMethod() ?>
					<input class="btn--primary" type="submit" value="<?= L_CONFIG_BASE_UPDATE ?>" />
				</div>
			</div>
			<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseTop')) ?>
			<fieldset>
				<label for="id_title"><?= L_CONFIG_BASE_SITE_TITLE ?>&nbsp;:</label>
				<?php PlxUtils::printInput('title', PlxUtils::strCheck($plxAdmin->aConf['title'])); ?>
				<label for="id_description"><?= L_CONFIG_BASE_SITE_SLOGAN ?>&nbsp;:</label>
				<?php PlxUtils::printInput('description', PlxUtils::strCheck($plxAdmin->aConf['description'])); ?>
				<label for="id_meta_description"><?= L_CONFIG_META_DESCRIPTION ?>&nbsp;:</label>
				<?php PlxUtils::printInput('meta_description', PlxUtils::strCheck($plxAdmin->aConf['meta_description'])); ?>
				<label for="id_meta_keywords"><?= L_CONFIG_META_KEYWORDS ?>&nbsp;:</label>
				<?php PlxUtils::printInput('meta_keywords', PlxUtils::strCheck($plxAdmin->aConf['meta_keywords'])); ?>
				<label for="id_default_lang"><?= L_CONFIG_BASE_DEFAULT_LANG ?>&nbsp;:</label>
				<?php PlxUtils::printSelect('default_lang', PlxUtils::getLangs(), $plxAdmin->aConf['default_lang']) ?>
				<label for="id_timezone"><?= L_CONFIG_BASE_TIMEZONE ?>&nbsp;:</label>
				<?php PlxUtils::printSelect('timezone', PlxTimezones::timezones(), $plxAdmin->aConf['timezone']); ?>
				<label for="id_allow_com"><?= L_CONFIG_BASE_ALLOW_COMMENTS ?>&nbsp;:</label>
				<?php PlxUtils::printSelect('allow_com',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['allow_com']); ?>
				<label for="id_mod_com"><?= L_CONFIG_BASE_MODERATE_COMMENTS ?>&nbsp;:</label>
				<?php PlxUtils::printSelect('mod_com',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['mod_com']); ?>
				<label for="id_mod_art"><?= L_CONFIG_BASE_MODERATE_ARTICLES ?>&nbsp;:</label>
				<?php PlxUtils::printSelect('mod_art',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['mod_art']); ?>
				<label for="id_enable_rss"><?= L_CONFIG_BASE_ENABLE_RSS ?>&nbsp;:</label>
				<?php PlxUtils::printSelect('enable_rss',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['enable_rss']); ?>
			</fieldset>
			<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBase')) ?>
		</form>
		<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseFoot')); ?>
	</div>
</div>

<?php
// Footer
include __DIR__ .'/../tags/foot.php';
?>