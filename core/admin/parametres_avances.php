<?php
/**
 * Edition des paramètres avancés
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if(!empty($_POST)) {
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	unset($_SESSION['medias']); # réinit de la variable de session medias (pour medias.php) au cas si changmt de chemin medias
	header('Location: parametres_avances.php');
	exit;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<form action="parametres_avances.php" method="post" id="form_settings">

	<div class="inline-form action-bar">
		<h2><?php echo L_CONFIG_ADVANCED_DESC ?></h2>
		<p>&nbsp;</p>
		<?php echo plxToken::getTokenPostMethod() ?>
		<input type="submit" value="<?php echo L_CONFIG_ADVANCED_UPDATE ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvancedTop')) # Hook Plugins ?>

	<fieldset>
		<div class="grid">
			<div class="col sml-12 med-5">
				<label for="id_urlrewriting"><?php echo L_CONFIG_ADVANCED_URL_REWRITE ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php if(plxUtils::testModRewrite(false)) : ?>
					<?php plxUtils::printSelect('urlrewriting',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['urlrewriting']);?>
					<?php if(is_file(PLX_ROOT.'.htaccess') AND $plxAdmin->aConf['urlrewriting']==0) { ?>
						<br /><span class="text-red"><?php echo L_CONFIG_ADVANCED_URL_REWRITE_ALERT ?></span>
					<?php } ?>
				<?php else: ?>
					<?php echo L_MODREWRITE_NOT_AVAILABLE ?>
				<?php endif; ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_gzip"><?php echo L_CONFIG_ADVANCED_GZIP ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('gzip',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['gzip']);?>
				<a class="hint"><span><?php echo L_CONFIG_ADVANCED_GZIP_HELP ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_capcha"><?php echo L_CONFIG_ADVANCED_CAPCHA ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('capcha',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['capcha']);?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_userfolders"><?php echo L_CONFIG_ADVANCED_USERFOLDERS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('userfolders',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['userfolders']);?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_clef"><?php echo L_CONFIG_ADVANCED_ADMIN_KEY ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('clef', $plxAdmin->aConf['clef'], 'text', '30-30'); ?>
				<a class="hint"><span><?php echo L_CONFIG_ADVANCED_KEY_HELP ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_config_path"><?php echo L_CONFIG_ADVANCED_CONFIG_FOLDER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('config_path', PLX_CONFIG_PATH) ?>
				<a class="hint"><span><?php echo L_HELP_SLASH_END ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_racine_articles"><?php echo L_CONFIG_ADVANCED_ARTS_FOLDER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('racine_articles', $plxAdmin->aConf['racine_articles']); ?>
				<a class="hint"><span><?php echo L_HELP_SLASH_END ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_racine_commentaires"><?php echo L_CONFIG_ADVANCED_COMS_FOLDER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('racine_commentaires', $plxAdmin->aConf['racine_commentaires']); ?>
				<a class="hint"><span><?php echo L_HELP_SLASH_END ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_racine_statiques"><?php echo L_CONFIG_ADVANCED_STATS_FOLDER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('racine_statiques', $plxAdmin->aConf['racine_statiques']); ?>
				<a class="hint"><span><?php echo L_HELP_SLASH_END ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_medias"><?php echo L_CONFIG_ADVANCED_MEDIAS_FOLDER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('medias', $plxAdmin->aConf['medias']); ?>
				<a class="hint"><span><?php echo L_HELP_SLASH_END ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_racine_themes"><?php echo L_CONFIG_ADVANCED_THEMES_FOLDER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('racine_themes', $plxAdmin->aConf['racine_themes']); ?>
				<a class="hint"><span><?php echo L_HELP_SLASH_END ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_racine_plugins"><?php echo L_CONFIG_ADVANCED_PLUGINS_FOLDER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('racine_plugins', $plxAdmin->aConf['racine_plugins']); ?>
				<a class="hint"><span><?php echo L_HELP_SLASH_END ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_custom_admincss_file"><?php echo L_CONFIG_CUSTOM_CSSADMIN_PATH ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('custom_admincss_file', $plxAdmin->aConf['custom_admincss_file']); ?>
			</div>
		</div>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvanced')) ?>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvancedFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>