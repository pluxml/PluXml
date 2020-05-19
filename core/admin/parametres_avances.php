<?php
/**
 * Edition des paramètres avancés
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE
 **/

include __DIR__ .'/prepend.php';

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
include __DIR__ .'/top.php';
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
				<label for="id_lostpassword"><?php echo L_CONFIG_ADVANCED_LOSTPASSWORD ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('lostpassword',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['lostpassword']);?>
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
		<div>
			<h2><?php echo L_CONFIG_ADVANCED_EMAIL_SENDING_TITLE ?>&nbsp;:</h2>
			<p><small><?php echo L_CONFIG_ADVANCED_EMAIL_SENDING_TITLE_HELP ?></small></p>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_custom_admincss_file"><?php echo L_CONFIG_ADVANCED_EMAIL_METHOD ?>&nbsp;:</label>
				<small><?php echo L_CONFIG_ADVANCED_EMAIL_METHOD_HELP ?></small>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInputRadio('email_method', array('sendmail'=>'sendmail', 'smtp'=>'SMTP', 'smtpoauth'=>'OAUTH2'), $plxAdmin->aConf['email_method']); ?>
			</div>
		</div>
		<div><h3><?php echo L_CONFIG_ADVANCED_SMTP_TITLE ?></h3></div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_custom_admincss_file"><?php echo L_CONFIG_ADVANCED_SMTP_SERVER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('smtp_server', $plxAdmin->aConf['smtp_server']); ?>
				<a class="hint"><span><?php echo L_CONFIG_ADVANCED_SMTP_SERVER_HELP ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_custom_admincss_file"><?php echo L_CONFIG_ADVANCED_SMTP_USERNAME ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('smtp_username', $plxAdmin->aConf['smtp_username']); ?>
				<a class="hint"><span><?php echo L_CONFIG_ADVANCED_SMTP_USERNAME_HELP ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_custom_admincss_file"><?php echo L_CONFIG_ADVANCED_SMTP_PASSWORD ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('smtp_password', $plxAdmin->aConf['smtp_password'], 'password'); ?>
				<a class="hint"><span><?php echo L_CONFIG_ADVANCED_SMTP_PASSWORD_HELP ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_custom_admincss_file"><?php echo L_CONFIG_ADVANCED_SMTP_PORT ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('smtp_port', $plxAdmin->aConf['smtp_port']); ?>
				<a class="hint"><span><?php echo L_CONFIG_ADVANCED_SMTP_PORT_HELP ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_custom_admincss_file"><?php echo L_CONFIG_ADVANCED_SMTP_SECURITY ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInputRadio('smtp_security', array('0'=>L_NONE1,'ssl'=>'SSL', 'tls'=>'TLS'), $plxAdmin->aConf['smtp_security']); ?>
			</div>
		</div>
		<div>
			<h3><?php echo L_CONFIG_ADVANCED_SMTPOAUTH_TITLE ?></h3>
			<p><small><?php echo L_CONFIG_ADVANCED_SMTPOAUTH_TITLE_HELP ?></small></p>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_custom_admincss_file"><?php echo L_CONFIG_ADVANCED_SMTPOAUTH_EMAIL ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('smtpOauth2_emailAdress', $plxAdmin->aConf['smtpOauth2_emailAdress']); ?>
				<a class="hint"><span><?php echo L_CONFIG_ADVANCED_SMTPOAUTH_EMAIL_HELP ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_custom_admincss_file"><?php echo L_CONFIG_ADVANCED_SMTPOAUTH_CLIENTID ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('smtpOauth2_clientId', $plxAdmin->aConf['smtpOauth2_clientId']); ?>
				<a class="hint"><span><?php echo L_CONFIG_ADVANCED_SMTPOAUTH_CLIENTID_HELP ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_custom_admincss_file"><?php echo L_CONFIG_ADVANCED_SMTPOAUTH_SECRETKEY ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('smtpOauth2_clientSecret', $plxAdmin->aConf['smtpOauth2_clientSecret']); ?>
				<a class="hint"><span><?php echo L_CONFIG_ADVANCED_SMTPOAUTH_SECRETKEY_HELP ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_custom_admincss_file"><?php echo L_CONFIG_ADVANCED_SMTPOAUTH_TOKEN ?>&nbsp;:</label>
				<small><?php echo L_CONFIG_ADVANCED_SMTPOAUTH_TOKEN_HELP ?></small>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('smtpOauth2_refreshToken', $plxAdmin->aConf['smtpOauth2_refreshToken'], 'text', '', true); ?>
<?php
	$disabled = (empty($plxAdmin->aConf['smtpOauth2_clientSecret']) AND empty($plxAdmin->aConf['smtpOauth2_clientId']) and empty($plxAdmin->aConf['smtpOauth2_emailAdress'])) ? 'disabled' : '';
?>
				<a href="get_oauth_token.php?provider=Google"><button type="button" <?php echo $disabled ?>><?php echo L_CONFIG_ADVANCED_SMTPOAUTH_GETTOKEN ?></button></a>
			</div>
		</div>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvanced')) # Hook Plugins ?>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvancedFoot'));
# On inclut le footer
include __DIR__ .'/foot.php';
?>
