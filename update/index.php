<?php
const PLX_UPDATER = true;
const PLX_ROOT = '../';
const PLX_CORE = PLX_ROOT . 'core/';

include PLX_CORE.'lib/config.php';

# On verifie que PluXml est installé
if(!file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Location: '.PLX_ROOT.'install.php');
	exit;
}

# Chargement des langues
$lang = (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : DEFAULT_LANG;
if(isset($_POST['default_lang'])) $lang=$_POST['default_lang'];
if(!array_key_exists($lang, plxUtils::getLangs())) {
	$lang = DEFAULT_LANG;
}

loadLang(PLX_CORE.'lang/'.$lang.'/core.php');
loadLang(PLX_CORE.'lang/'.$lang.'/admin.php');
loadLang(PLX_CORE.'lang/'.$lang.'/update.php');

# On vérifie la version minimale de PHP
if(version_compare(PHP_VERSION, PHP_VERSION_MIN, '<')){
	header('Content-Type: text/plain charset=UTF-8');
	echo utf8_decode(L_WRONG_PHP_VERSION);
	exit;
}

# Echappement des caractères
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$_POST = plxUtils::unSlash($_POST);
}

# Création de l'objet principal et lancement du traitement

include 'class.plx.updater.php';
$plxUpdater = new plxUpdater();

plxUtils::cleanHeaders();
session_set_cookie_params(0, "/", $_SERVER['SERVER_NAME'], isset($_SERVER["HTTPS"]), true);
session_start();
# Control du token du formulaire
plxToken::validateFormToken($_POST);
?>
<!DOCTYPE html>
<head>
	<meta name="robots" content="noindex, nofollow" />
	<meta charset="<?php echo strtolower(PLX_CHARSET) ?>" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title><?= L_UPDATE_TITLE . ' ' . plxUtils::strCheck($plxUpdater->newVersion) ?></title>
	<link rel="stylesheet" type="text/css" href="<?= PLX_CORE ?>admin/theme/plucss.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?= PLX_CORE ?>admin/theme/theme.css" media="screen" />
	<link rel="icon" href="<?= PLX_CORE ?>admin/theme/images/pluxml.gif" />
</head>
<body>
	<main class="main grid">
		<aside class="aside col sml-12 med-3 lrg-2"></aside>
		<section class="section col sml-12 med-9 med-offset-3 lrg-10 lrg-offset-2" style="margin-top: 0">
			<header>
				<h1><?= L_UPDATE_TITLE.' '.plxUtils::strCheck($plxUpdater->newVersion) ?></h1>
			</header>
<?php

if(empty($_POST['submit'])) {
	if(!empty($plxUpdater->oldVersion) and version_compare($plxUpdater->oldVersion, $plxUpdater->newVersion, '>=')) {
?>
				<p><strong><?= L_UPDATE_UPTODATE ?></strong></p>
				<p><?= L_UPDATE_NOT_AVAILABLE ?></p>
				<p><a href="<?= PLX_ROOT; ?>" title="<?= L_UPDATE_BACK ?>"><button><?= L_UPDATE_BACK ?></button></a></p>
<?php
	} else {
?>
				<form action="index.php" method="post">
					<fieldset>
						<div class="grid">
							<div class="col sml-9 med-7 label-centered">
								<label for="id_default_lang"><?= L_SELECT_LANG ?></label>
							</div>
							<div class="col sml-3 med-2">
								<?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $lang) ?>&nbsp;
							</div>
							<div class="col med-3">
								<input type="submit" name="select_lang" value="<?= L_INPUT_CHANGE ?>" />
								<?= plxToken::getTokenPostMethod() ?>
							</div>
						</div>
					</fieldset>
					<fieldset>
						<p><strong><?= L_UPDATE_WARNING1.' '.$plxUpdater->oldVersion ?></strong></p>
<?php
		if(empty($plxUpdater->oldVersion)) {
?>
						<p><?= L_UPDATE_SELECT_VERSION ?></p>
						<p><?php plxUtils::printSelect('version',array_keys($versions),''); ?></p>
						<p><?= L_UPDATE_WARNING2 ?></p>
<?php
		}
?>
						<p><?php printf(L_UPDATE_WARNING3, preg_replace('@^([^/]+).*@', '$1', $plxUpdater->plxAdmin->aConf['racine_articles'])); ?></p>
						<p><input type="submit" name="submit" value="<?= L_UPDATE_START ?>" /></p>
					</fieldset>
				</form>
<?php
	}
} else {
	# process to update !
	$plxUpdater->startUpdate(!empty($_POST['version']) ? $_POST['version'] : $plxUpdater->oldVersion);
?>
			<p><a href="<?= PLX_ROOT; ?>" title="<?= L_UPDATE_BACK ?>"><button><?= L_UPDATE_BACK ?></button></a></p>
<?php
}
?>
		</section>
	</main>
</body>
</html>
