<?php
if (!defined('PLX_ROOT')) {
	exit;
}

/*
 * Pour afficher une page de contact sur votre site, créer une page statique
 * en mettant comme contenu l'entête de la page
 * et choisir static-contact comme gabarit ou modèle (template)
 * */
const RECIPIENT = '001'; # id de l'utilisateur qui recevra le courriel
/*
 * En cas d'ajout de champs dans le formulaire, mettre à jour FORM_FILTER ci-dessous avec les filtres appropriés.
 * A Minima : FILTER_SANITIZE_STRING
 * Lire les manuels à :
 * https://www.php.net/manual/fr/filter.filters.sanitize.php
 * https://www.php.net/manual/fr/filter.filters.validate.php
 * */
const FORM_FILTER = array(
	'name'          => FILTER_SANITIZE_STRING,
	'phone'         => array(
		'filter'    => FILTER_VALIDATE_REGEXP,
		'options'   => array('regexp' => '@^[\d\s.()+]+$@'),
	),
	'mail'          => FILTER_VALIDATE_EMAIL,
	'content'       => FILTER_SANITIZE_STRING,
	'rep'           => array(
		'filter'    => FILTER_VALIDATE_REGEXP,
		'options'   => array('regexp' => '@^\w$@'),
	),
);
if (!class_exists('plxToken')) {
	include_once PLX_CORE . 'lib/class.plx.token.php';
}
function processContact(&$plxShow)
{
	if (!filter_has_var(INPUT_POST, 'mail')) {
		return false;
	}
	plxToken::validateFormToken();
	$params = filter_input_array(INPUT_POST, FORM_FILTER);
	if (empty($params['mail']) or empty(trim($params['content']))) {
		return 'ERROR_CONTACT_MESSAGE';
	}
	if (!empty($plxShow->plxMotor->aConf['capcha']) and $_SESSION['capcha'] != sha1($params['rep'])) {
		return 'ERROR_CONTACT_CAPCHA';
	}
	if (!defined('RECIPIENT') or empty($plxShow->plxMotor->aUsers[RECIPIENT]['email'])) {
		uasort($plxShow->plxMotor->aUsers, function ($a, $b) {
			if (empty($a['email'])) {
				return 1;
			}
			if (empty($b['email'])) {
				return -1;
			}
			return $a['profil'] - $b['profil'];
		});
		$to = array_values($plxShow->plxMotor->aUsers)[0]['email'];
	} else {
		$to = $plxShow->plxMotor->aUsers[RECIPIENT]['email'];
	}
	if (empty($to)) {
		return 'ERROR_CONTACT_RECIPIENT';
	}
	// Corps du message
	ob_start(); ?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8" />
<style>
	.mail-content { border: 1px solid #444; margin: 1rem 0; padding: 0.5rem 1rem; border-radius: 0.5rem; }
</style>
</head><body>
	<p><strong><em>Site :</em> <?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?></strong></p>
	<?php
	foreach ($params as $field => $value) {
		if (!empty($value) and $field != 'capcha') {
			if (in_array($field, array('content'))) {
				// <textarea>
				?>
	<p><em><?= $plxShow->getLang('MAIL_' . strtoupper($field)) ?> :</em></p>
	<div class="mail-content"><?= nl2br($value) ?></div>
				<?php
			} else {
				?>
	<p><em><?= $plxShow->getLang('MAIL_' . strtoupper($field)) ?> :</em> <?= $value ?></p>
				<?php
			}
		}
	}
	foreach (
		array(
		'Ip Address'        => 'REMOTE_ADDR',
		'User-Agent'        => 'HTTP_USER_AGENT',
		'Accept-Language'   => 'HTTP_ACCEPT_LANGUAGE',
		) as $k => $value
	) {
		?>
	<p><em><?= $k ?> :</em> <?= $_SERVER[$value] ?></p>
		<?php
	} ?>
</body></html>
	<?php
// fin du corps du message
	if (
		!mail(
			$to,
			sprintf($plxShow->getLang('MAIL_SUBJECT'), plxUtils::strCheck($plxShow->plxMotor->aConf['title'])), // Subject
			ob_get_clean(), // Body
			implode("\r\n", array( // Additional headers
			'MIME-Version: 1.0',
			'Content-type: text/html; charset="' . PLX_CHARSET . '"',
			'Content-transfer-encoding: 8bit',
			'Date: ' . date('D, j M Y G:i:s O'),
			))
		)
	) {
		return 'ERROR_CONTACT_DELIVERY';
	}
	return true;
}

/* ---------- Génération de la page HTML à afficher ------------- */
include 'header.php';
?>
<!-- begin of static-contact.php -->
					<article class="static" id="static-page-<?= $plxShow->staticId(); ?>">
						<header class="static-header">
							<h2><?php $plxShow->staticTitle(); ?></h2>
						</header>
						<div>
<?php
$result = processContact($plxShow);
if ($result === true) {
?>
							<p class="contact success"><?= nl2br($plxShow->getLang('MAIL_SUCCESS')) ?></p>
							<p><a href="index.php" class="button green"><?= $plxShow->getLang('BACKTO_HOME') ?></a></p>
<?php
} else {
	if (!empty($result)) {
		// Il y a eu une erreur
?>
							<p class="contact error"><?= $plxShow->getLang($result) ?></p>
<?php
	}

		/* ========== Début du formulaire de contact ============= */
		$plxShow->staticContent();
		echo PHP_EOL;
?>
							<form id="frm-contact" method="post">
<?php
	# Rajouter des champs supplémentaires ci-après
?>
								<div class="grid">
									<div class="col">
										<input type="text" name="name" value="<?= plxUtils::getValue($param['name']) ?>" placeholder="<?= $plxShow->getLang('CONTACT_NAME') ?>" required />
									</div>
								</div>
								<div class="grid infos">
									<div class="col lrg-6">
										<input type="tel" name="phone" class="phone" value="<?= plxUtils::getValue($param['phone']) ?>" placeholder="<?= $plxShow->getLang('CONTACT_PHONE') ?>" />
									</div>
									<div class="col lrg-6">
										<input type="email" name="mail" value="<?= plxUtils::getValue($param['mail']) ?>" placeholder="<?= $plxShow->getLang('CONTACT_MAIL') ?>" required />
									</div>
								</div>
								<div class="grid">
									<div class="col">
										<textarea name="content" placeholder="<?= $plxShow->getLang('CONTACT_CONTENT') ?>" rows="10" required><?= plxUtils::getValue($param['name']) ?></textarea>
									</div>
								</div>
	<?php printCapcha(); ?>
								<div class="grid">
									<div class="col">
									<?= plxToken::getTokenPostMethod();
									echo PHP_EOL; ?>
									<input class="blue" type="submit" value="<?php $plxShow->lang('SEND') ?>" />
									</div>
								</div>
							</form>
<?php
}
/* ========== Fin du formulaire de contact ====================== */
?>
						</div>
					</article>
<!-- end of static-contact.php -->
<?php
include 'footer.php';
