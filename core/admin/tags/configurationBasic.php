<?php

/**
 * Basic configuration
 * Part of core/admin/parametres_base.php
 * @author	Pedro "P3ter" CADETE"
 **/
use Pluxml\PlxUtils;
use Pluxml\PlxToken;
use Pluxml\PlxTimezones;
?>

<form action="configuration.php" method="post" id="form_settings">
	<div class="autogrid">
		<h3 class="h4-like"><?= L_CONFIG_BASE_CONFIG_TITLE ?></h3>
		<div class="txtright">
			<input class="btn--primary" type="submit" value="<?= L_CONFIG_BASE_UPDATE ?>" />
		</div>
	</div>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseTop')) # Hook Plugins ?>
	<fieldset class="config">
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_title"><?= L_CONFIG_BASE_SITE_TITLE ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printInput('title', PlxUtils::strCheck($plxAdmin->aConf['title'])); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_description"><?= L_CONFIG_BASE_SITE_SLOGAN ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printInput('description', PlxUtils::strCheck($plxAdmin->aConf['description'])); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_meta_description"><?= L_CONFIG_META_DESCRIPTION ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printInput('meta_description', PlxUtils::strCheck($plxAdmin->aConf['meta_description'])); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_meta_keywords"><?= L_CONFIG_META_KEYWORDS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printInput('meta_keywords', PlxUtils::strCheck($plxAdmin->aConf['meta_keywords'])); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_default_lang"><?= L_CONFIG_BASE_DEFAULT_LANG ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printSelect('default_lang', PlxUtils::getLangs(), $plxAdmin->aConf['default_lang']) ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_timezone"><?= L_CONFIG_BASE_TIMEZONE ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printSelect('timezone', PlxTimezones::timezones(), $plxAdmin->aConf['timezone']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_allow_com"><?= L_CONFIG_BASE_ALLOW_COMMENTS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printSelect('allow_com',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['allow_com']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_mod_com"><?= L_CONFIG_BASE_MODERATE_COMMENTS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printSelect('mod_com',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['mod_com']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_mod_art"><?= L_CONFIG_BASE_MODERATE_ARTICLES ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printSelect('mod_art',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['mod_art']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_enable_rss"><?= L_CONFIG_BASE_ENABLE_RSS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printSelect('enable_rss',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['enable_rss']); ?>
			</div>
		</div>		
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBase')) # Hook Plugins ?>
	<?= PlxToken::getTokenPostMethod() ?>
</form>