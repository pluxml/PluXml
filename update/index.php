<?php
define('PLX_ROOT', '../');
define('PLX_CORE', PLX_ROOT.'core/');
include(PLX_ROOT.'config.php');
include(PLX_CORE.'lib/config.php');

define('PLX_UPDATER', true);

# On verifie que PluXml est installé
if(!file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Location: '.PLX_ROOT.'install.php');
	exit;
}

# On inclut les librairies nécessaires
include(PLX_CORE.'lib/class.plx.date.php');
include(PLX_CORE.'lib/class.plx.glob.php');
include(PLX_CORE.'lib/class.plx.utils.php');
include(PLX_CORE.'lib/class.plx.msg.php');
include(PLX_CORE.'lib/class.plx.record.php');
include(PLX_CORE.'lib/class.plx.motor.php');
include(PLX_CORE.'lib/class.plx.admin.php');
include(PLX_CORE.'lib/class.plx.encrypt.php');
include(PLX_CORE.'lib/class.plx.plugins.php');
include(PLX_CORE.'lib/class.plx.token.php');
include(PLX_ROOT.'update/versions.php');
include(PLX_ROOT.'update/class.plx.updater.php');

# Chargement des langues
$lang = DEFAULT_LANG;
if(isset($_POST['default_lang'])) $lang=$_POST['default_lang'];
if(!array_key_exists($lang, plxUtils::getLangs())) {
	$lang = DEFAULT_LANG;
}
loadLang(PLX_CORE.'lang/'.$lang.'/core.php');
loadLang(PLX_CORE.'lang/'.$lang.'/admin.php');
loadLang(PLX_CORE.'lang/'.$lang.'/update.php');

# On vérifie que PHP 5 ou superieur soit installé
if(version_compare(PHP_VERSION, '5.0.0', '<')){
	header('Content-Type: text/plain charset=UTF-8');
	echo utf8_decode(L_WRONG_PHP_VERSION);
	exit;
}

# Echappement des caractères
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$_POST = plxUtils::unSlash($_POST);
}

# Création de l'objet principal et lancement du traitement
$plxUpdater = new plxUpdater($versions);

?>
<?php
plxUtils::cleanHeaders();
session_start();
# Control du token du formulaire
plxToken::validateFormToken($_POST);
?>
<!DOCTYPE html>
<head>
	<meta name="robots" content="noindex, nofollow" />
	<meta charset="<?php echo strtolower(PLX_CHARSET) ?>" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title><?php echo L_UPDATE_TITLE.' '.plxUtils::strCheck($plxUpdater->newVersion) ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/plucss.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/theme.css" media="screen" />
	<link rel="icon" href="<?php echo PLX_CORE ?>admin/theme/images/pluxml.gif" />
</head>

<body>

	<main class="main grid">

		<aside class="aside col sml-12 med-3 lrg-2">

		</aside>

		<section class="section col sml-12 med-9 med-offset-3 lrg-10 lrg-offset-2" style="margin-top: 0">

			<header>

				<h1><?php echo L_UPDATE_TITLE.' '.plxUtils::strCheck($plxUpdater->newVersion) ?></h1>

			</header>

			<?php if(empty($_POST['submit'])) : ?>
				<?php if($plxUpdater->oldVersion==$plxUpdater->newVersion) : ?>
				<p><strong><?php echo L_UPDATE_UPTODATE ?></strong></p>
				<p><?php echo L_UPDATE_NOT_AVAILABLE ?></p>
				<p><a href="<?php echo PLX_ROOT; ?>" title="<?php echo L_UPDATE_BACK ?>"><?php echo L_UPDATE_BACK ?></a></p>
				<?php else: ?>
				<form action="index.php" method="post">
					<fieldset>
						<div class="grid">
							<div class="col sml-12 med-5 label-centered">
								<label for="id_default_lang"><?php echo L_SELECT_LANG ?></label>
							</div>
							<div class="col sml-12 med-7">
								<?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $lang) ?>&nbsp;
							</div>
						</div>
						<div class="grid">
							<div class="col sml-12">
								<input type="submit" name="select_lang" value="<?php echo L_INPUT_CHANGE ?>" />
								<?php echo plxToken::getTokenPostMethod() ?>
							</div>
						</div>
					</fieldset>
					<fieldset>
						<p><strong><?php echo L_UPDATE_WARNING1.' '.$plxUpdater->oldVersion ?></strong></p>
						<?php if(empty($plxUpdater->oldVersion)) : ?>
						<p><?php echo L_UPDATE_SELECT_VERSION ?></p>
						<p><?php plxUtils::printSelect('version',array_keys($versions),''); ?></p>
						<p><?php echo L_UPDATE_WARNING2 ?></p>
						<?php endif; ?>
						<p><?php echo L_UPDATE_WARNING3 ?></p>
						<p><input type="submit" name="submit" value="<?php echo L_UPDATE_START ?>" /></p>
					</fieldset>
				</form>
				<?php endif; ?>
			<?php else: ?>
			<?php
			$version = isset($_POST['version']) ? $_POST['version'] : $plxUpdater->oldVersion;
			$plxUpdater->startUpdate($version);
			?>
			<p><a href="<?php echo PLX_ROOT; ?>" title="<?php echo L_UPDATE_BACK ?>"><?php echo L_UPDATE_BACK ?></a></p>
			<?php endif; ?>
		</section>

	</main>

</body>

</html>