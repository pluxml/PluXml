<?php

/**
 * Edition des paramètres de base
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F, Philippe-M, Pedro "P3ter" CADETE"
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if(!empty($_POST)) {
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	header('Location: parametres_base.php');
	exit;
}

# On inclut le header
include 'top.php';
?>

<form action="parametres_base.php" method="post" id="form_settings">

	<div class="inline-form action-bar">
		<h2><?= L_CONFIG_BASE_CONFIG_TITLE ?></h2>
		<p>&nbsp;</p>
		<input type="submit" value="<?= L_CONFIG_BASE_UPDATE ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseTop')) # Hook Plugins ?>

	<fieldset class="config">
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_title"><?= L_CONFIG_BASE_SITE_TITLE ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('title', plxUtils::strCheck($plxAdmin->aConf['title'])); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_description"><?= L_CONFIG_BASE_SITE_SLOGAN ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('description', plxUtils::strCheck($plxAdmin->aConf['description'])); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_meta_description"><?= L_CONFIG_META_DESCRIPTION ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('meta_description', plxUtils::strCheck($plxAdmin->aConf['meta_description'])); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_meta_keywords"><?= L_CONFIG_META_KEYWORDS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('meta_keywords', plxUtils::strCheck($plxAdmin->aConf['meta_keywords'])); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_default_lang"><?= L_CONFIG_BASE_DEFAULT_LANG ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $plxAdmin->aConf['default_lang']) ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_timezone"><?= L_CONFIG_BASE_TIMEZONE ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('timezone', plxTimezones::timezones(), $plxAdmin->aConf['timezone']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_allow_com"><?= L_CONFIG_BASE_ALLOW_COMMENTS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('allow_com', ALLOW_COM_OPTIONS, $plxAdmin->aConf['allow_com']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_mod_com"><?= L_CONFIG_BASE_MODERATE_COMMENTS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('mod_com',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['mod_com']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_mod_art"><?= L_CONFIG_BASE_MODERATE_ARTICLES ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('mod_art',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['mod_art']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_enable_rss"><?= L_CONFIG_BASE_ENABLE_RSS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('enable_rss',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['enable_rss']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_enable_rss_comment"><?= L_CONFIG_BASE_ENABLE_RSS_COMMENT ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('enable_rss_comment',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['enable_rss_comment']); ?>
			</div>
		</div>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBase')) # Hook Plugins ?>
	<?= plxToken::getTokenPostMethod() ?>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseFoot'));

# On inclut le footer
include 'foot.php';
