<?php
use models\PlxTimezonesModel;
?>
<!DOCTYPE html>
<head>
	<meta charset="<?= strtolower($charset) ?>" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title><?= L_PLUXML_INSTALLATION.' '.L_VERSION.' '.$version ?></title>
	<link rel="stylesheet" type="text/css" href="<?= $plxLayoutDir.'css/plucss.css' ?>" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?= $plxLayoutDir.'css/theme.css' ?>" media="screen" />
	<script src="<?= $plxLayoutDir.'js/visual.js' ?>"></script>
</head>

<body>

	<main class="main grid">

		<aside class="aside col sml-12 med-3 lrg-2">

		</aside>

		<section class="section col sml-12 med-9 med-offset-3 lrg-10 lrg-offset-2" style="margin-top: 0">

			<header>

				<h1><?= L_PLUXML_VERSION.' '.$version ?> - <?= L_INSTALL_TITLE ?></h1>

			</header>

			<?php if($msg!='') echo '<div class="alert red">'.$msg.'</div>'; ?>

			<form action="install.php" method="post">

				<fieldset>

					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_default_lang"><?= L_SELECT_LANG ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php $plxUtils->printSelect('default_lang', $plxUtils->getLangs(), $lang) ?>&nbsp;
							<input type="submit" name="select_lang" value="<?= L_INPUT_CHANGE ?>" />
							<?= $plxToken->getTokenPostMethod() ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_name"><?= L_USERNAME ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php $plxUtils->printInput('name', $name, 'text', '20-255',false,'','','autofocus', '', '', '', '', 'required') ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_login"><?= L_LOGIN ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php $plxUtils->printInput('login', $login, 'text', '20-255', '', '', '', '', 'required') ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_pwd"><?= L_PASSWORD ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php $plxUtils->printInput('pwd', '', 'password', '20-255', false, '', '', 'onkeyup="pwdStrength(this.id, [\''.L_PWD_VERY_WEAK.'\', \''.L_PWD_WEAK.'\', \''.L_PWD_GOOD.'\', \''.L_PWD_STRONG.'\'])"', 'required') ?>
							<span id="id_pwd_strenght"></span>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_pwd2"><?= L_PASSWORD_CONFIRMATION ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php $plxUtils->printInput('pwd2', '', 'password', '20-255', '', '', '', '', 'required') ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_email"><?= L_EMAIL ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php $plxUtils->printInput('email', $email, 'email', '20-255', '', '', '', '', 'required') ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_timezone"><?= L_TIMEZONE ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php $plxUtils->printSelect('timezone', $allTimezones, $timezone); ?>
						</div>
					</div>

					<input class="blue" type="submit" name="install" value="<?= L_INPUT_INSTALL ?>" />
					<?= $plxToken->getTokenPostMethod() ?>

					<ul class="unstyled-list">
						<li><strong><?= L_PLUXML_VERSION; ?> <?= $version ?> (<?= L_INFO_CHARSET ?> <?= $charset ?>)</strong></li>
						<li><?= L_INFO_PHP_VERSION.' : '.phpversion() ?></li>
						<?php if (!empty($_SERVER['SERVER_SOFTWARE'])) { ?>
						<li><?= $_SERVER['SERVER_SOFTWARE']; ?></li>
						<?php } ?>
						<?php $plxUtils->testWrite(PLX_ROOT) ?>
						<?php $plxUtils->testWrite(PLX_ROOT.PLX_CONFIG_PATH) ?>
						<?php $plxUtils->testWrite(PLX_ROOT.PLX_CONFIG_PATH.'plugins/') ?>
						<?php $plxUtils->testWrite(PLX_ROOT.$config['racine_articles']) ?>
						<?php $plxUtils->testWrite(PLX_ROOT.$config['racine_commentaires']) ?>
						<?php $plxUtils->testWrite(PLX_ROOT.$config['racine_statiques']) ?>
						<?php $plxUtils->testWrite(PLX_ROOT.$config['medias']) ?>
						<?php $plxUtils->testWrite(PLX_ROOT.$config['racine_plugins']) ?>
						<?php $plxUtils->testWrite(PLX_ROOT.$config['racine_themes']) ?>
						<?php $plxUtils->testModReWrite() ?>
						<?php $plxUtils->testLibGD() ?>
						<?php $plxUtils->testLibXml() ?>
						<?php $plxUtils->testMail() ?>
					</ul>

				</fieldset>

			</form>

		</section>

	</main>

</body>

</html>