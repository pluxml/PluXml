<?php
/**
 * Edition des paramètres avancés
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE
 **/

use Pluxml\PlxToken;
use Pluxml\PlxUtils;

# Control du token du formulaire
PlxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if(!empty($_POST)) {
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	unset($_SESSION['medias']); # réinit de la variable de session medias (pour medias.php) au cas si changmt de chemin medias
	header('Location: configuration.php');
	exit;
}

?>

<form action="configuration.php" method="post" id="form_settings">
	<div class="autogrid panel-header">
		<h3 class="h4-like"><?= L_CONFIG_ADVANCED_DESC ?></h3>
		<div class="txtright">
			<?= PlxToken::getTokenPostMethod() ?>
			<input class="btn--primary" type="submit" value="<?= L_CONFIG_ADVANCED_UPDATE ?>" />
		</div>
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvancedTop')) # Hook Plugins ?>

	<fieldset>
		<label for="id_urlrewriting"><?= L_CONFIG_ADVANCED_URL_REWRITE ?>&nbsp;:</label>
		<?php if(PlxUtils::testModRewrite(false)) : ?>
			<?php PlxUtils::printSelect('urlrewriting',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['urlrewriting']);?>
			<?php if(is_file(PLX_ROOT.'.htaccess') AND $plxAdmin->aConf['urlrewriting']==0) { ?>
				<br /><span class="text-red"><?= L_CONFIG_ADVANCED_URL_REWRITE_ALERT ?></span>
			<?php } ?>
		<?php else: ?>
			<?= L_MODREWRITE_NOT_AVAILABLE ?>
		<?php endif; ?>
		<label for="id_gzip"><?= L_CONFIG_ADVANCED_GZIP ?>&nbsp;:</label>
		<?php PlxUtils::printSelect('gzip',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['gzip']);?>
		<a class="hint"><span><?= L_CONFIG_ADVANCED_GZIP_HELP ?></span></a>
		<label for="id_lostpassword"><?= L_CONFIG_ADVANCED_LOSTPASSWORD ?>&nbsp;:</label>
		<?php PlxUtils::printSelect('lostpassword',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['lostpassword']);?>
		<label for="id_capcha"><?= L_CONFIG_ADVANCED_CAPCHA ?>&nbsp;:</label>
		<?php PlxUtils::printSelect('capcha',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['capcha']);?>
		<label for="id_userfolders"><?= L_CONFIG_ADVANCED_USERFOLDERS ?>&nbsp;:</label>
		<?php PlxUtils::printSelect('userfolders',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['userfolders']);?>
		<label for="id_clef"><?= L_CONFIG_ADVANCED_ADMIN_KEY ?>&nbsp;:</label>
		<?php PlxUtils::printInput('clef', $plxAdmin->aConf['clef'], 'text', '30-30'); ?>
		<a class="hint"><span><?= L_CONFIG_ADVANCED_KEY_HELP ?></span></a>
		<label for="id_config_path"><?= L_CONFIG_ADVANCED_CONFIG_FOLDER ?>&nbsp;:</label>
		<?php PlxUtils::printInput('config_path', PLX_CONFIG_PATH) ?>
		<a class="hint"><span><?= L_HELP_SLASH_END ?></span></a>
		<label for="id_racine_articles"><?= L_CONFIG_ADVANCED_ARTS_FOLDER ?>&nbsp;:</label>
		<?php PlxUtils::printInput('racine_articles', $plxAdmin->aConf['racine_articles']); ?>
		<a class="hint"><span><?= L_HELP_SLASH_END ?></span></a>
		<label for="id_racine_commentaires"><?= L_CONFIG_ADVANCED_COMS_FOLDER ?>&nbsp;:</label>
		<?php PlxUtils::printInput('racine_commentaires', $plxAdmin->aConf['racine_commentaires']); ?>
		<a class="hint"><span><?= L_HELP_SLASH_END ?></span></a>
		<label for="id_racine_statiques"><?= L_CONFIG_ADVANCED_STATS_FOLDER ?>&nbsp;:</label>
		<?php PlxUtils::printInput('racine_statiques', $plxAdmin->aConf['racine_statiques']); ?>
		<a class="hint"><span><?= L_HELP_SLASH_END ?></span></a>
		<label for="id_medias"><?= L_CONFIG_ADVANCED_MEDIAS_FOLDER ?>&nbsp;:</label>
		<?php PlxUtils::printInput('medias', $plxAdmin->aConf['medias']); ?>
		<a class="hint"><span><?= L_HELP_SLASH_END ?></span></a>
		<label for="id_racine_themes"><?= L_CONFIG_ADVANCED_THEMES_FOLDER ?>&nbsp;:</label>
		<?php PlxUtils::printInput('racine_themes', $plxAdmin->aConf['racine_themes']); ?>
		<a class="hint"><span><?= L_HELP_SLASH_END ?></span></a>
		<label for="id_racine_plugins"><?= L_CONFIG_ADVANCED_PLUGINS_FOLDER ?>&nbsp;:</label>
		<?php PlxUtils::printInput('racine_plugins', $plxAdmin->aConf['racine_plugins']); ?>
		<a class="hint"><span><?= L_HELP_SLASH_END ?></span></a>
		<label for="id_custom_admincss_file"><?= L_CONFIG_CUSTOM_CSSADMIN_PATH ?>&nbsp;:</label>
		<?php PlxUtils::printInput('custom_admincss_file', $plxAdmin->aConf['custom_admincss_file']); ?>
		<h4><?= L_CONFIG_ADVANCED_EMAIL_SENDING_TITLE ?>&nbsp;:</h4>
		<p><small><?= L_CONFIG_ADVANCED_EMAIL_SENDING_TITLE_HELP ?></small></p>
		<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_EMAIL_METHOD ?>&nbsp;:</label>
		<small><?= L_CONFIG_ADVANCED_EMAIL_METHOD_HELP ?></small>
		<?php PlxUtils::printInputRadio('email_method', array('sendmail'=>'sendmail', 'smtp'=>'SMTP', 'smtpoauth'=>'OAUTH2'), $plxAdmin->aConf['email_method']); ?>
		<h4><?= L_CONFIG_ADVANCED_SMTP_TITLE ?></h4>
		<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_SERVER ?>&nbsp;:</label>
		<?php PlxUtils::printInput('smtp_server', $plxAdmin->aConf['smtp_server']); ?>
		<a class="hint"><span><?= L_CONFIG_ADVANCED_SMTP_SERVER_HELP ?></span></a>
		<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_USERNAME ?>&nbsp;:</label>
		<?php PlxUtils::printInput('smtp_username', $plxAdmin->aConf['smtp_username']); ?>
		<a class="hint"><span><?= L_CONFIG_ADVANCED_SMTP_USERNAME_HELP ?></span></a>
		<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_PASSWORD ?>&nbsp;:</label>
		<?php PlxUtils::printInput('smtp_password', $plxAdmin->aConf['smtp_password'], 'password'); ?>
		<a class="hint"><span><?= L_CONFIG_ADVANCED_SMTP_PASSWORD_HELP ?></span></a>
		<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_PORT ?>&nbsp;:</label>
		<?php PlxUtils::printInput('smtp_port', $plxAdmin->aConf['smtp_port']); ?>
		<a class="hint"><span><?= L_CONFIG_ADVANCED_SMTP_PORT_HELP ?></span></a>
		<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_SECURITY ?>&nbsp;:</label>
		<?php PlxUtils::printInputRadio('smtp_security', array('0'=>L_NONE1,'ssl'=>'SSL', 'tls'=>'TLS'), $plxAdmin->aConf['smtp_security']); ?>
		<h4><?= L_CONFIG_ADVANCED_SMTPOAUTH_TITLE ?></h4>
		<p><small><?= L_CONFIG_ADVANCED_SMTPOAUTH_TITLE_HELP ?></small></p>
		<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTPOAUTH_EMAIL ?>&nbsp;:</label>
		<?php PlxUtils::printInput('smtpOauth2_emailAdress', $plxAdmin->aConf['smtpOauth2_emailAdress']); ?>
		<a class="hint"><span><?= L_CONFIG_ADVANCED_SMTPOAUTH_EMAIL_HELP ?></span></a>
		<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTPOAUTH_CLIENTID ?>&nbsp;:</label>
		<?php PlxUtils::printInput('smtpOauth2_clientId', $plxAdmin->aConf['smtpOauth2_clientId']); ?>
		<a class="hint"><span><?= L_CONFIG_ADVANCED_SMTPOAUTH_CLIENTID_HELP ?></span></a>
		<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTPOAUTH_SECRETKEY ?>&nbsp;:</label>
		<?php PlxUtils::printInput('smtpOauth2_clientSecret', $plxAdmin->aConf['smtpOauth2_clientSecret']); ?>
		<a class="hint"><span><?= L_CONFIG_ADVANCED_SMTPOAUTH_SECRETKEY_HELP ?></span></a>
		<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTPOAUTH_TOKEN ?>&nbsp;:</label>
		<small><?= L_CONFIG_ADVANCED_SMTPOAUTH_TOKEN_HELP ?></small>
		<?php PlxUtils::printInput('smtpOauth2_refreshToken', $plxAdmin->aConf['smtpOauth2_refreshToken'], 'text', '', true); ?>
		<?php
			if (empty($plxAdmin->aConf['smtpOauth2_clientSecret']) AND empty($plxAdmin->aConf['smtpOauth2_clientId']) and empty($plxAdmin->aConf['smtpOauth2_emailAdress'])) {
				$disabled = "disabled";
			}
		?>
		<a href="get_oauth_token.php?provider=Google"><button type="button" <?= $disabled ?>><?= L_CONFIG_ADVANCED_SMTPOAUTH_GETTOKEN ?></button></a>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvanced')) # Hook Plugins ?>
</form>
