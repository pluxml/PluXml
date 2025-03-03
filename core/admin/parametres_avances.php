<?php
/**
 * Edition des paramètres avancés
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE, Jean-Pierre Pourrez "bazooka07"
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if(!empty($_POST)) {
	if(isset($_POST['robots'])) {
		$plxAdmin->editRobots();
		$redirect = 'parametres_avances.php';
	} else {
		$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
		unset($_SESSION['medias']); # réinit de la variable de session medias (pour medias.php) au cas si changmt de chemin medias
		$redirect = $plxAdmin->o_auth_token_required($_POST) ? 'get_oauth_token.php' : 'parametres_avances.php';
	}

	header('Location: ' . $redirect);
	exit;
}

# On inclut le header
include 'top.php';
?>

<form action="parametres_avances.php" method="post" id="form_settings">
	<?= plxToken::getTokenPostMethod() ?>
	<div class="inline-form action-bar">
		<h2><?= L_CONFIG_ADVANCED_DESC ?></h2>
		<p>&nbsp;</p>
		<div class="grid">
			<div class="col med-4">
				<input type="submit" value="<?= L_CONFIG_ADVANCED_UPDATE ?>" />
			</div>
<?php /* -------- robots.txt ------- */ ?>
			<div class="col med-4 med-offset-4">
				<span>/robots.txt</span> :
				<input type="submit" name="robots" value="<?= L_BUILD ?>" />
<?php
if (file_exists('robots.txt')) {
?>
				<a href="/robots.txt?rand=<?= dechex(time() & 0xffff) ?>" target="_blank" class="button"><?= L_DISPLAY ?></a>
<?php
}
?>
			</div>
		</div>
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvancedTop')) # Hook Plugins ?>

	<fieldset>
		<div class="grid">
			<div class="col sml-12 med-5">
				<label for="id_urlrewriting"><?= L_CONFIG_ADVANCED_URL_REWRITE ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('urlrewriting',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['urlrewriting']);?>
				<?php if(is_file(PLX_ROOT.'.htaccess') AND $plxAdmin->aConf['urlrewriting']==0) { ?>
					<br /><span class="text-red"><?= str_replace('. ', '.<br />', L_CONFIG_ADVANCED_URL_REWRITE_ALERT) ?></span>
				<?php } ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_gzip"><?= L_CONFIG_ADVANCED_GZIP ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('gzip',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['gzip']);?>
				<a class="hint"><span><?= L_CONFIG_ADVANCED_GZIP_HELP ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_lostpassword"><?= L_CONFIG_ADVANCED_LOSTPASSWORD ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('lostpassword',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['lostpassword']);?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_capcha"><?= L_CONFIG_ADVANCED_CAPCHA ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('capcha',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['capcha']);?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_userfolders"><?= L_CONFIG_ADVANCED_USERSFOLDERS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
<?php
$usersOptions = array(
	0 => ucFirst(L_NONE1),
	2 => L_PROFIL_MANAGER,
	3 => L_PROFIL_MODERATOR,
	4 => L_PROFIL_EDITOR,
	1 => L_PROFIL_WRITER, // Rétro-compatibilité pour PluXml version < 5.9.0
);

plxUtils::printSelect('userfolders', $usersOptions, $plxAdmin->aConf['userfolders']);
?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_clef"><?= L_CONFIG_ADVANCED_ADMIN_KEY ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('clef', $plxAdmin->aConf['clef'], 'text', '32-32'); ?>
				<a class="hint"><span><?= L_CONFIG_ADVANCED_KEY_HELP ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_config_path"><?= L_CONFIG_ADVANCED_CONFIG_FOLDER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('config_path', PLX_CONFIG_PATH) ?>
				<a class="hint"><span><?= L_HELP_SLASH_END ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_racine_articles"><?= L_CONFIG_ADVANCED_ARTS_FOLDER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('racine_articles', $plxAdmin->aConf['racine_articles']); ?>
				<a class="hint"><span><?= L_HELP_SLASH_END ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_racine_commentaires"><?= L_CONFIG_ADVANCED_COMS_FOLDER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('racine_commentaires', $plxAdmin->aConf['racine_commentaires']); ?>
				<a class="hint"><span><?= L_HELP_SLASH_END ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_racine_statiques"><?= L_CONFIG_ADVANCED_STATS_FOLDER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('racine_statiques', $plxAdmin->aConf['racine_statiques']); ?>
				<a class="hint"><span><?= L_HELP_SLASH_END ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_medias"><?= L_CONFIG_ADVANCED_MEDIAS_FOLDER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('medias', $plxAdmin->aConf['medias']); ?>
				<a class="hint"><span><?= L_HELP_SLASH_END ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_racine_themes"><?= L_CONFIG_ADVANCED_THEMES_FOLDER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('racine_themes', $plxAdmin->aConf['racine_themes']); ?>
				<a class="hint"><span><?= L_HELP_SLASH_END ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_racine_plugins"><?= L_CONFIG_ADVANCED_PLUGINS_FOLDER ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('racine_plugins', $plxAdmin->aConf['racine_plugins']); ?>
				<a class="hint"><span><?= L_HELP_SLASH_END ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_custom_admincss_file"><?= L_CONFIG_CUSTOM_CSSADMIN_PATH ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('custom_admincss_file', $plxAdmin->aConf['custom_admincss_file']); ?>
			</div>
		</div>
<?php
if(!plxUtils::isPHPMailer()) {
?>
		<input type="hidden" name="email_method" value="sendmail" >
<?php
} else {
	$smtp_security = array(
		'' => L_NONE1,
		'ssl' => 'SSL/TLS', # PHPMailer::ENCRYPTION_SMTPS
		'tls' => 'TLS', # PHPMailer::ENCRYPTION_STARTTLS
	);

	$email_methods = array(
		'sendmail'	=> 'sendmail',
		'smtp'		=>'SMTP',
	);
	if(plxUtils::isOauth2Enabled()) {
		$email_methods['smtpoauth'] = 'OAUTH2';
	}
?>
		<div>
			<h2><?= L_CONFIG_ADVANCED_EMAIL_SENDING_TITLE ?>&nbsp;:</h2>
			<p>
				<small><?= L_CONFIG_ADVANCED_EMAIL_SENDING_TITLE_HELP ?></small>
				<a class="hint"><span><?= L_CONFIG_ADVANCED_EMAIL_METHOD_HELP ?></span></a>
			</p>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label><?= L_CONFIG_ADVANCED_EMAIL_METHOD ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7 inputradio">
<?php
	$email_methods = array(
		'sendmail'	=> 'sendmail',
		'smtp'		=>'SMTP',
	);
	if(plxUtils::isOauth2Enabled()) {
		$email_methods['smtpoauth'] = 'OAUTH2';
	}
?>
				<?php plxUtils::printInputRadio('email_method', $email_methods, $plxAdmin->aConf['email_method']); ?>
			</div>
		</div>
		<fieldset class="with-border">
			<legend><h3><?= L_CONFIG_ADVANCED_SMTP_TITLE ?></h3></legend>
			<div class="grid">
				<div class="col sml-12 med-5 label-centered">
					<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_SERVER ?>&nbsp;:</label>
				</div>
				<div class="col sml-12 med-7">
					<?php plxUtils::printInput('smtp_server', $plxAdmin->aConf['smtp_server']); ?>
					<a class="hint"><span><?= L_CONFIG_ADVANCED_SMTP_SERVER_HELP ?></span></a>
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12 med-5 label-centered">
					<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_USERNAME ?>&nbsp;:</label>
				</div>
				<div class="col sml-12 med-7">
					<?php plxUtils::printInput('smtp_username', $plxAdmin->aConf['smtp_username']); ?>
					<a class="hint"><span><?= L_CONFIG_ADVANCED_SMTP_USERNAME_HELP ?></span></a>
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12 med-5 label-centered">
					<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_PASSWORD ?>&nbsp;:</label>
				</div>
				<div class="col sml-12 med-7">
					<?php plxUtils::printInput('smtp_password', $plxAdmin->aConf['smtp_password'], 'password'); ?>
					<a class="hint"><span><?= L_CONFIG_ADVANCED_SMTP_PASSWORD_HELP ?></span></a>
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12 med-5 label-centered">
					<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_PORT ?>&nbsp;:</label>
				</div>
				<div class="col sml-12 med-7">
					<?php plxUtils::printInput('smtp_port', $plxAdmin->aConf['smtp_port']); ?>
					<a class="hint"><span><?= L_CONFIG_ADVANCED_SMTP_PORT_HELP ?></span></a>
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12 med-5 label-centered">
					<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_SECURITY ?>&nbsp;:</label>
				</div>
				<div class="col sml-12 med-7 inputradio">
					<?php plxUtils::printInputRadio('smtp_security', $smtp_security, $plxAdmin->aConf['smtp_security']); ?>
				</div>
			</div>
		</fieldset>
<?php
	if(array_key_exists('smtpoauth', $email_methods)) {
		$disabled = (empty($plxAdmin->aConf['smtpOauth2_clientSecret']) AND empty($plxAdmin->aConf['smtpOauth2_clientId']) and empty($plxAdmin->aConf['smtpOauth2_emailAdress'])) ? 'disabled' : '';
?>
		<fieldset class="with-border">
			<legend>
				<h3><?= L_CONFIG_ADVANCED_SMTPOAUTH_TITLE ?></h3>
			</legend>
			<p><small><?= L_CONFIG_ADVANCED_SMTPOAUTH_TITLE_HELP ?></small></p>
			<div class="grid">
				<div class="col sml-12 med-5 label-centered">
					<label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTPOAUTH_EMAIL ?>&nbsp;:</label>
				</div>
				<div class="col sml-12 med-7">
					<?php plxUtils::printInput('smtpOauth2_emailAdress', $plxAdmin->aConf['smtpOauth2_emailAdress']); ?>
					<a class="hint"><span><?= L_CONFIG_ADVANCED_SMTPOAUTH_EMAIL_HELP ?></span></a>
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12 med-5 label-centered">
					<a href="get_oauth_token.php"><button type="button" <?= $disabled ?>><?= L_CONFIG_ADVANCED_SMTPOAUTH_GETTOKEN ?></button></a>
				</div>
				<div class="col sml-12 med-7">
					<?php plxUtils::printInput('smtpOauth2_refreshToken', $plxAdmin->aConf['smtpOauth2_refreshToken'], 'text', '32-', true); ?>
				</div>
			</div>
		</fieldset>
<?php
	}
}
?>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvanced')) # Hook Plugins ?>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvancedFoot'));

# On inclut le footer
include 'foot.php';
