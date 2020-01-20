<?php

/**
 * Edition des paramètres de base
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F, Philippe-M, Pedro "P3ter" CADETE"
 **/

include __DIR__ .'/prepend.php';
use Pluxml\PlxTimezones;
use Pluxml\PlxToken;
use Pluxml\PlxUtils;

# Control du token du formulaire
PlxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if(!empty($_POST)) {
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	header('Location: parametres_base.php');
	exit;
}

# On inclut le header
include __DIR__ .'/top.php';
?>

<form action="parametres_base.php" method="post" id="form_settings">

	<div class="inline-form action-bar">
		<h2><?php echo L_CONFIG_BASE_CONFIG_TITLE ?></h2>
		<p>&nbsp;</p>
		<input type="submit" value="<?php echo L_CONFIG_BASE_UPDATE ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseTop')) # Hook Plugins ?>

	<fieldset class="config">
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_title"><?php echo L_CONFIG_BASE_SITE_TITLE ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printInput('title', PlxUtils::strCheck($plxAdmin->aConf['title'])); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_description"><?php echo L_CONFIG_BASE_SITE_SLOGAN ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printInput('description', PlxUtils::strCheck($plxAdmin->aConf['description'])); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_meta_description"><?php echo L_CONFIG_META_DESCRIPTION ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printInput('meta_description', PlxUtils::strCheck($plxAdmin->aConf['meta_description'])); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_meta_keywords"><?php echo L_CONFIG_META_KEYWORDS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printInput('meta_keywords', PlxUtils::strCheck($plxAdmin->aConf['meta_keywords'])); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_default_lang"><?php echo L_CONFIG_BASE_DEFAULT_LANG ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printSelect('default_lang', PlxUtils::getLangs(), $plxAdmin->aConf['default_lang']) ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_timezone"><?php echo L_CONFIG_BASE_TIMEZONE ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printSelect('timezone', PlxTimezones::timezones(), $plxAdmin->aConf['timezone']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_allow_com"><?php echo L_CONFIG_BASE_ALLOW_COMMENTS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printSelect('allow_com',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['allow_com']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_mod_com"><?php echo L_CONFIG_BASE_MODERATE_COMMENTS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printSelect('mod_com',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['mod_com']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_mod_art"><?php echo L_CONFIG_BASE_MODERATE_ARTICLES ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printSelect('mod_art',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['mod_art']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_enable_rss"><?php echo L_CONFIG_BASE_ENABLE_RSS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php PlxUtils::printSelect('enable_rss',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['enable_rss']); ?>
			</div>
		</div>		
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBase')) # Hook Plugins ?>
	<?php echo PlxToken::getTokenPostMethod() ?>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseFoot'));
# On inclut le footer
include __DIR__ .'/foot.php';
?>
