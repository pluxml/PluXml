<?php
const PLX_ROOT = '../';
const PLX_CORE = PLX_ROOT . 'core/';
include PLX_ROOT . 'config.php';
include PLX_CORE . 'lib/config.php';
require_once PLX_ROOT . 'vendor/autoload.php';

const PLX_UPDATER = true;

# On verifie que PluXml est installé
if(!file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Location: '.PLX_ROOT.'install.php');
	exit;
}

// use Pluxml\PlxDate;
// use Pluxml\PlxGlob;
use Pluxml\PlxUtils;
use Pluxml\PlxMsg;
// use Pluxml\PlxRecord;
// use Pluxml\PlxMotor;
// use Pluxml\PlxAdmin;
// use Pluxml\PlxEncrypt;
// use Pluxml\PlxPlugins;
use Pluxml\PlxToken;

include 'versions.php';
include 'class.plx.updater.php';

# Chargement des langues
$lang = (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : DEFAULT_LANG;
if(isset($_POST['default_lang'])) $lang=$_POST['default_lang'];
if(!array_key_exists($lang, PlxUtils::getLangs())) {
	$lang = DEFAULT_LANG;
}

foreach(array('core', 'admin', 'update') as $ctx) {
	loadLang(PLX_CORE . 'lang/' . $lang . '/' . $ctx . '.php');
};

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
	<title><?php echo L_UPDATE_TITLE.' '.plxUtils::strCheck($plxUpdater->newVersion) ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/css/knacss.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/css/theme.css" media="screen" />
	<link rel="icon" href="<?php echo PLX_CORE ?>admin/theme/images/pluxml.png" />
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
							<div class="col sml-9 med-7 label-centered">
								<label for="id_default_lang"><?php echo L_SELECT_LANG ?></label>
							</div>
							<div class="col sml-3 med-2">
								<?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $lang) ?>&nbsp;
							</div>
							<div class="col med-3">
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
