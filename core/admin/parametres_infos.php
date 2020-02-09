<?php

/**
 * Edition des paramètres d'affichage
 *
 * @package PLX
 * @author	Florent MONTHEL
 **/

include __DIR__ .'/prepend.php';

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

?>

<div class="inline-form action-bar">
	<h2><?php echo L_CONFIG_INFOS_TITLE ?></h2>
	<p><?php echo L_PLUXML_CHECK_VERSION ?></p>
	<?php $maj = $plxAdmin->checkMaj(); echo $maj; ?>
</div>

<p><?php echo L_CONFIG_INFOS_DESCRIPTION ?></p>

<p><strong><?php echo L_PLUXML_VERSION; ?> <?php echo PLX_VERSION; ?> (<?php echo L_INFO_CHARSET ?> <?php echo PLX_CHARSET ?>)</strong></p>
<ul class="unstyled-list">
	<li><?php echo L_INFO_PHP_VERSION; ?> : <?php echo phpversion(); ?></li>
	<?php if (!empty($_SERVER['SERVER_SOFTWARE'])) { ?>
	<li><?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
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
	<?php plxUtils::testModReWrite() ?>
	<?php plxUtils::testLibGD() ?>
	<?php plxUtils::testLibXml() ?>
	<?php
	if(plxUtils::testMail() and is_string($email) and !$emailBuild) {
?>
		<form method="post">
			<?php echo plxToken::getTokenPostMethod() ?>
			<input type="submit" name="sendmail-test" value="<?= L_MAIL_TEST ?>" />
		</form>
<?php
	}
?>
</ul>
<p><?php echo L_CONFIG_INFOS_NB_CATS ?> <?php echo sizeof($plxAdmin->aCats); ?></p>
<p><?php echo L_CONFIG_INFOS_NB_STATICS ?> <?php echo sizeof($plxAdmin->aStats); ?></p>
<p><?php echo L_CONFIG_INFOS_WRITER ?> <?php echo $plxAdmin->aUsers[$_SESSION['user']]['name'] ?></p>

<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsInfos')) # Hook Plugins ?>

<?php
if($emailBuild) {
	$content = ob_get_clean();
	$head = <<< HEAD
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8" />
<title>sans titre</title>
</head><body>
HEAD;
	$foot = '</body></html>';
	$subject = sprintf(L_MAIL_TEST_SUBJECT, $plxAdmin->aConf['title']);

	// Webmaster
	$name = $plxAdmin->aUsers['001']['name']; // Peut être vide pour PHPMailer
	$from = $plxAdmin->aUsers['001']['email'];

	if(empty($plxAdmin->aConf['email_method']) or $plxAdmin->aConf['email_method'] == 'sendmail' or !method_exists('plxUtils', 'sendMailPhpMailer')) {
		# fonction mail() intrinséque à PHP
		$method = '<p style="font-size: 80%;"><em>mail() function from PHP</em></p>';
		$body = $head . $content . $method . $foot;
		if(plxUtils::sendMail('', '', $email, $subject, $body, 'html')) {
			plxMsg::Info(sprintf(L_MAIL_TEST_SENT_TO, $email));
		} else {
			plxMsg::Error(L_MAIL_TEST_FAILURE);
		}
	} else {
		# module externe PHPMailer -
		$method = '<p style="font-size: 80%;"><em>' . $plxAdmin->aConf['email_method'] . ' via PHPMailer</em></p>';
		$body = $head . $content . $method . $foot;

		if(plxUtils::sendMailPhpMailer($name, $from, $email, $subject, $head . $body . $foot, true, $plxAdmin->aConf)) {
			plxMsg::Info(sprintf(L_MAIL_TEST_SENT_TO, $email));
		} else {
			plxMsg::Error(L_MAIL_TEST_FAILURE);
		}
	}

	header('Location: ' . basename(__FILE__));
	exit;
}
if(preg_match('%class="[^"]*\bred\b[^"]*"%', $maj)) {
	# checkMaj() has failed with curl or allow_url_fopen is off
?>
	<script type="text/javascript">
		(function() {
			'use strict';
			const currentVersion = '<?= PLX_VERSION ?>';
			const id = 'latest-version';
			const el = document.getElementById(id);
			if(el == null) {
				console.error('Element with id="' + id + '" not found');
				return;
			}

			function compareVersion(v1, v2) {
				if(typeof v1 != 'string' || typeof v2 != 'string') { return; }

				const t1 = v1.split('.');
				const t2 = v2.split('.');
				for(let i=0, iMax=(t1.length < t2.length) ? t1.length : t2.length; i<iMax; i++) {
					const n1 = parseInt(t1[i]);
					const n2 = parseInt(t2[i]);
					if(n1 == n2) { continue; }
					return (n1 < n2) ? -1 : 1;
				}
				return (t1.length == t2.length) ? 0 : (t1.length < t2.length) ? -1 : 1;
			}

			const xhr = new XMLHttpRequest();
			xhr.onreadystatechange = function() {
				if (this.readyState === XMLHttpRequest.DONE) {
					if(this.status === 200) {
						console.log('Available version :', this.responseText);
						el.classList.remove('red');
						if(compareVersion(currentVersion, this.responseText) < 0) {
							el.innerHTML = '<?= $plxAdmin->update_link ?>';
							el.classList.add('orange');
						} else {
							el.innerHTML = "<?= L_PLUXML_UPTODATE.' ('.PLX_VERSION.')' ?>";
							el.classList.add('green');
						}
						return;
					}
					console.error('[check update]', this.status, this.statusText);
				}
			};
			xhr.open('GET', '<?= PLX_URL_VERSION ?>');
			xhr.send();
		})();
	</script>
<?php
}
# On inclut le footer
include __DIR__ .'/foot.php';
?>
