<?php

/**
 * Edition des paramètres d'affichage
 *
 * @package PLX
 * @author	Florent MONTHEL
 **/

include 'prepend.php';

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# Control du token du formulaire
plxToken::validateFormToken($_POST);

$email = filter_var($plxAdmin->aUsers[$_SESSION['user']]['email'], FILTER_VALIDATE_EMAIL);
$emailBuild = (is_string($email) and filter_has_var(INPUT_POST, 'sendmail-test'));
if($emailBuild) {
	# body of test e-mail starts here
	ob_start();
} else {
	# direct output
	# administration header
	include __DIR__ .'/top.php';
}

$maj = $plxAdmin->checkMaj();
?>

<div class="inline-form action-bar">
	<h2><?= L_CONFIG_INFOS_TITLE ?></h2>
	<p><?= str_replace('#PLUXML#', '<a href="' . PLX_URL_REPO . '" target="pluxml-official">' . PLX_URL_REPO . '</a>', L_PLUXML_CHECK_VERSION) ?></p>
	<?= $maj; ?>
</div>

<p><?= L_CONFIG_INFOS_DESCRIPTION ?></p>

<p>
	<strong><?= L_PLUXML_VERSION; ?> <?= PLX_VERSION; ?> (<?= L_INFO_CHARSET ?> <?= PLX_CHARSET ?>)</strong>
<?php
	if ($plxAdmin->aConf['version'] != PLX_VERSION) {
?>
	- <?= L_PLUXML_VERSION_DATA ?> : <?= $plxAdmin->aConf['version'] ?>
<?php
	}
?>
</p>
<ul class="unstyled-list">
	<li><?= L_INFO_PHP_VERSION; ?> : <?= phpversion(); ?></li>
<?php if (!empty($_SERVER['SERVER_SOFTWARE'])) { ?>
	<li><?= $_SERVER['SERVER_SOFTWARE'] ?><?= !empty(PHP_SAPI) ? ' - ' . PHP_SAPI : '' ?></li>
<?php } ?>
</ul>
<ul class="unstyled-list">
	<?php plxUtils::testWrite(PLX_ROOT) ?>
	<?php plxUtils::testWrite(PLX_ROOT.PLX_CONFIG_PATH); ?>
	<?php plxUtils::testWrite(PLX_ROOT.PLX_CONFIG_PATH.'plugins/'); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_articles']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_commentaires']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_statiques']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['medias']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_plugins']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_themes']); ?>
	<?php if(function_exists('apache_get_modules')) { plxUtils::testModReWrite(); } ?>
	<?php plxUtils::testLibGD() ?>
	<?php plxUtils::testLibXml() ?>
	<?php
	if(plxUtils::testMail() and is_string($email) and !$emailBuild) {
?>
		<form method="post">
			<?= plxToken::getTokenPostMethod() ?>
			<input type="submit" name="sendmail-test" value="<?= L_MAIL_TEST ?>" />
		</form>
<?php
	}
?>
</ul>
<p><?= L_CONFIG_INFOS_NB_CATS ?> <?= sizeof($plxAdmin->aCats); ?></p>
<p><?= L_CONFIG_INFOS_NB_STATICS ?> <?= sizeof($plxAdmin->aStats); ?></p>
<p><?= L_CONFIG_INFOS_WRITER ?> <?= $plxAdmin->aUsers[$_SESSION['user']]['name'] ?></p>

<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsInfos')) # Hook Plugins ?>

<?php
if($emailBuild) {
	$isPHPMailer = (plxUtils::isPHPMailer() and !empty($plxAdmin->aConf['email_method']) and preg_match('#^smtp(oauth)?$#', $plxAdmin->aConf['email_method']));
	if($isPHPMailer and $plxAdmin->aConf['email_method'] == 'smtpoauth' and empty(trim($plxAdmin->aConf['smtpOauth2_refreshToken']))) {
		# Error
		ob_end_clean();
		plxMsg::Error('Missing token for OAuth2');
		header('Location: ' . basename(__FILE__));
		exit;
	}

	$from = $plxAdmin->aUsers['001']['email']; // Webmaster
	$name = $plxAdmin->aUsers['001']['name']; // Peut être vide pour PHPMailer
	$subject = sprintf(L_MAIL_TEST_SUBJECT, $plxAdmin->aConf['title'] . ' (via ' . ($isPHPMailer ? $plxAdmin->aConf['email_method'] : 'sendmail') . ')');
	$content = ob_get_clean();
	$head = <<< HEAD
<!DOCTYPE html>
<html lang="en"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>{$subject}</title>
</head><body>
HEAD;
	$foot = '</body></html>';
	$body = $head . $content . $foot;

	// On est prêt à envoyer le mail

	if($plxAdmin->isPHPMailerDisabled()) {
		# fonction mail() intrinséque à PHP
		if(plxUtils::sendMail('', '', $email, $subject, $body, 'html')) {
			plxMsg::Info(sprintf(L_MAIL_TEST_SENT_TO, $email));
		} else {
			plxMsg::Error(L_MAIL_TEST_FAILURE);
		}
		header('Location: ' . basename(__FILE__));
		exit;
	}

	# module externe PHPMailer -

	# On va ouvrir une fenêtre de log pour afficher la conversation avec le serveur SMTP.
	unset($_SESSION['error']);
	unset($_SESSION['info']);
	include __DIR__ .'/top.php';
?>
<details class="phpmailer-debug">
	<summary><strong>Log from SMTP server</strong></summary>
	<p>
<?php
	if(plxUtils::sendMailPhpMailer($name, $from, $email, $subject, $head . $body . $foot, true, $plxAdmin->aConf, true)) {
		plxMsg::Info(sprintf(L_MAIL_TEST_SENT_TO, $email));
	} else {
		plxMsg::Error(L_MAIL_TEST_FAILURE);
		$errorPHPMailer = true;
	}
?>
	</p>
</details>
<?php
	echo $content;
	plxMsg::Display();
}

if(preg_match('%class="[^"]*\bred\b[^"]*"%', $maj)) {
	# checkMaj() has failed with curl or allow_url_fopen is off
?>
	<script type="text/javascript">
		(function() {
			'use strict';

<?php
	if(!empty($errorPHPMailer)) {
?>
			const details = document.querySelector('.phpmailer-debug');
			if(details) {
				details.setAttribute('open', '');
			}

<?php
	}
?>
			const currentVersion = '<?= PLX_VERSION ?>';
			const id = 'latest-version';
			const el = document.getElementById(id);
			if(el == null) {
				console.error('Element with id="' + id + '" not found');
				return;
			}
<?php
}

# On inclut le footer
include 'foot.php';
