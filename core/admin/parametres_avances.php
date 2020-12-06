<?php

/**
 * Edition des paramètres avancés
 *
 * @package PLX
 * @author    Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if (!empty($_POST)) {
    $plxAdmin->editConfiguration($_POST);
    # réinit de la variable de session medias (pour medias.php) au cas si changmt de chemin medias
    unset($_SESSION['medias']);
    header('Location: parametres_avances.php');
    exit;
}

# On inclut le header
include 'top.php';
?>

<form method="post" id="form_advanced_settings" class="first-level">
    <div class="adminheader">
        <div>
            <h2 class="h3-like"><?= L_CONFIG_ADVANCED ?></h2>
        </div>
        <div>
			<div>
				<button class="btn--primary" name="config-more" role="button"><?= L_SAVE ?></button>
			</div>
        </div>
    </div>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvancedTop'));
?>
    <fieldset>
<?php

# one batch of checkboxes
foreach(array(
	'urlrewriting'			=> array(true,	L_CONFIG_ADVANCED_URL_REWRITE),
	'cleanurl'				=> array(true,	L_CONFIG_CLEAN_URLS, L_CONFIG_CLEAN_URLS_HELP),
	'gzip'					=> array(true,	L_CONFIG_ADVANCED_GZIP, L_CONFIG_ADVANCED_GZIP_HELP),
	'lostpassword'			=> array(true,	L_CONFIG_ADVANCED_LOSTPASSWORD),
	'capcha'				=> array(true,	L_CONFIG_ADVANCED_CAPCHA),
	'userfolders'			=> array(true,	L_CONFIG_ADVANCED_USERFOLDERS),

	'clef'					=> array(false,	L_CONFIG_ADVANCED_ADMIN_KEY),
	'config_path'			=> array(false,	L_CONFIG_ADVANCED_CONFIG_FOLDER, L_SLASH_END_REQUIRED),
	'racine_articles'		=> array(false,	L_CONFIG_ADVANCED_ARTS_FOLDER, L_SLASH_END_REQUIRED),
	'racine_commentaires'	=> array(false,	L_CONFIG_ADVANCED_COMS_FOLDER, L_SLASH_END_REQUIRED),
	'racine_statiques'		=> array(false,	L_CONFIG_ADVANCED_STATS_FOLDER, L_SLASH_END_REQUIRED),
	'medias'				=> array(false,	L_CONFIG_ADVANCED_MEDIAS_FOLDER, L_SLASH_END_REQUIRED),
	'racine_themes'			=> array(false,	L_CONFIG_ADVANCED_THEMES_FOLDER, L_SLASH_END_REQUIRED),
	'racine_plugins'		=> array(false,	L_CONFIG_ADVANCED_ARTS_FOLDER, L_SLASH_END_REQUIRED),
	'custom_admincss_file'	=> array(false,	L_CONFIG_CUSTOM_CSSADMIN_PATH, L_CONFIG_CUSTOM_CSSADMIN_PATH_HELP),
) as $k=>$infos) {
	$id = 'id_' . $k;
	if($k != 'urlrewriting' or plxUtils::testModRewrite(false)) {
?>
		<div <?= $infos[0] ? 'class="bool"' : '' ?>>
            <div>
                <label for="<?= $id ?>"><?= $infos[1] ?></label>
<?php
		if(isset($infos[2])) {
			# tooltip
			switch($k) {
				case 'cleanurl' :
				case 'config_path':
				case 'custom_admincss_file': $extra = ' left'; break;
				# case 'custom_admincss_file': $extra = ' right'; break;
				default: $extra = '';
			}
?>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext<?= $extra ?>"><?= $infos[2] ?></span>
                </div>
<?php
		}
?>
            </div>
<?php
		if($infos[0]) {
			# boolean value
			$checked= (!empty($plxAdmin->aConf[$k])) ? ' checked' : '';
?>
			<input type="checkbox" name="<?= $k ?>" value="1" id="<?= $id ?>"<?= $checked ?> />
<?php
		} else {
			# textual value
			$value = ($k != 'config_path') ? $plxAdmin->aConf[$k] : PLX_CONFIG_PATH;
?>
			<input type="text" name="<?= $k ?>" value="<?= $value ?>" id="<?= $id ?>" />
<?php
		}
?>
		</div>
<?php
		if($k == 'urlrewriting' and is_file(PLX_ROOT . '.htaccess')) {
?>
		<p class="<?= $checked ? 'active' : '' ?>">
			<?= L_CONFIG_ADVANCED_URL_REWRITE_ALERT ?>
		</p>
<?php
		}
	}
?>
<?php
}

# ------- config for mail ------

const GOOGLE = '<a href="https://cloud.google.com" target="_blank">GMAIL (Google)</a>';
const WIKI = '<a href="'. PLX_URL_WIKI .'/personnaliser/personnalisation/#envoi-de-mails" target="_blank">' . L_HELP_TITLE . '</a>';
?>
        <div id="email-config">
            <div>
				<label>
	                <span><?= L_CONFIG_ADVANCED_EMAIL_METHOD ?></span>
					<?= WIKI ?>
				</label>
            </div>
            <div id="email-config-tabs">
<?php
foreach(array(
	'sendmail' => L_CONFIG_ADVANCED_EMAIL_METHOD_HELP,
	'smtp'		=> array(
		'_server'	=> array(L_CONFIG_ADVANCED_SMTP_SERVER, L_CONFIG_ADVANCED_SMTP_SERVER_HELP),
		'_username'	=> array(L_CONFIG_ADVANCED_SMTP_USERNAME, L_CONFIG_ADVANCED_SMTP_USERNAME_HELP),
		'_password'	=> array(L_CONFIG_ADVANCED_SMTP_PASSWORD, L_CONFIG_ADVANCED_SMTP_PASSWORD_HELP),
		'_port'		=> array(L_CONFIG_ADVANCED_SMTP_PORT, L_CONFIG_ADVANCED_SMTP_PORT_HELP),
		'_security'	=> array(L_CONFIG_ADVANCED_SMTP_SECURITY, false, array(
			'0' => L_NONE1, 'ssl' => 'SSL', 'tls' => 'TLS',
		)),
	),
	'smtpOauth'	=> array(
		'2_emailAdress'		=> array(L_MAIL_ADDRESS),
		'2_clientId'		=> array(L_CONFIG_ADVANCED_SMTPOAUTH_CLIENTID, L_CONFIG_ADVANCED_SMTPOAUTH_CLIENTID_HELP),
		'2_clientSecret'	=> array(L_CONFIG_ADVANCED_SMTPOAUTH_SECRETKEY, L_CONFIG_ADVANCED_SMTPOAUTH_SECRETKEY_HELP),
		'2_refreshToken'	=> array(L_CONFIG_ADVANCED_SMTPOAUTH_TOKEN, L_CONFIG_ADVANCED_SMTPOAUTH_TOKEN_HELP),
	),
) as $v=>$infosPlus) {
	$checked = ($v == $plxAdmin->aConf['email_method']) ? ' checked' : '';
	$id0 = 'email-by-' . $v;
?>
				<div> <!-- ====== <?= $v ?> ====== -->
					<input type="radio" name="email_method" value="<?= $v ?>" id="<?= $id0 ?>"<?= $checked ?> />&nbsp;<label for="<?= $id0 ?>"><?= ucfirst($v) ?></label>
					<div id="email-config-tab-<?= $v ?>" class="tabs">
<?php
	if(is_string($infosPlus)) {
?>
						<p class="txtcenter"><?= $infosPlus ?></p>
<?php
	} else {
		if($v == 'smtpOauth') {
			# message pour information
?>
			<p><?= strtr(L_CONFIG_ADVANCED_SMTPOAUTH_TITLE_HELP, array(
				'GOOGLE'	=> GOOGLE,
				'WIKI'		=> WIKI,
			)) ?></p>
<?php
		}

		foreach($infosPlus as $k=>$infos) {
			$id1 = 'id_' . $v . $k;
			$value = $plxAdmin->aConf[$v . $k];
			$className = ($v. $k == 'smtp_port') ? 'class="bool"' : '';
?>
						<div <?= $className ?>> <!-- <?= $v . $k ?> input -->
							<div>
								<label for="<?= $id1 ?>"><?= $infos[0] ?></label>
<?php
// --------
			if(!empty($infos[1])) {
				# tooltip
				switch($v . $k) {
					# case 'cleanurl' : $extra = ' left'; break;
					case 'smtp_server':
					case 'smtp_port':
					case 'smtpOauth2_clientId':
					case 'smtpOauth2_refreshToken': $extra = ' right'; break;
					default: $extra = '';
				}
?>
								<div class="tooltip icon-help-circled">
				                    <span class="tooltiptext<?= $extra ?>"><?= $infos[1] ?></span>
				                </div>
<?php
			}
?>
							</div>
<?php
		if($v . $k == 'smtp_security') {
?>
							<div class="txtcenter"> <!-- input[type="radio"] -->
<?php
			foreach($infos[2] as $r) {
				$idR = $v . $r;
				$checked = ($r == $value) ? ' checked' : '';
?>
								<div class="inbl">
									<input type="radio" name="<?= $v . $k ?>" value="<?= $r ?>" id="<?= $idR ?>" <?= $checked ?> />
									<label for="<?= $idR ?>"><?= $r ?></label>
								</div>
<?php
			}
?>
							</div>
<?php
		} elseif($v . $k == 'smtpOauth2_refreshToken') {
?>
				            <div id="oauth-token"> <!-- input with link (Google) -->
								<input type="text" name="<?= $v . $k ?>" value="<?= $value ?>" id="<? $id1 ?>" />
<?php
			$disabled = (
				empty($plxAdmin->aConf['smtpOauth2_clientSecret']) &&
				empty($plxAdmin->aConf['smtpOauth2_clientId']) &&
				empty($plxAdmin->aConf['smtpOauth2_emailAdress'])
			) ? 'disabled' : '';
?>
				                <a href="get_oauth_token.php?provider=Google">
				                    <button type="button" <?= $disabled ?>><?= L_CONFIG_ADVANCED_SMTPOAUTH_GETTOKEN ?></button>
				                </a>
				            </div>
<?php
		} else {
			switch($v . $k) { # select one type for input
				case 'smtp_port' : $type = 'number'; break;
				case 'smtp_password' : $type ='password'; break;
				case 'smtpOauth2_emailAdress': $type = 'email'; break;
				default : $type = 'text';
			}
?>
							<input type="<?= $type ?>" name="<?= $v . $k ?>" value="<?= $value ?>" id="<?= $id1 ?>" />
<?php
		}
?>
						</div>
<?php
		}
	}
?>
					</div>
				</div>
<?php
}
?>
			</div>
		</div>
   </fieldset>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvanced'));
?>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvancedFoot'));

# On inclut le footer
include 'foot.php';
