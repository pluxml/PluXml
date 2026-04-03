<?php
const PLX_ROOT = '../';
const PLX_CORE = PLX_ROOT .'core/';

include(PLX_ROOT.'config.php');
include(PLX_CORE.'lib/config.php');

# On démarre la session
# session_set_cookie_params(0, "/", $_SERVER['SERVER_NAME'], isset($_SERVER["HTTPS"]), true);
# session_start();
$session_site['name'] = 'PLX_INSTALL';
session_start($session_site);

# On inclut les librairies nécessaires
include(PLX_CORE.'lib/class.plx.timezones.php');
include(PLX_CORE.'lib/class.plx.date.php');
include(PLX_CORE.'lib/class.plx.glob.php');
include(PLX_CORE.'lib/class.plx.utils.php');
include(PLX_CORE.'lib/class.plx.token.php');

# Chargement des langues
if(!empty($_POST)){
	$lang = $_POST['default_lang'];
} elseif(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
} else {
	$lang = DEFAULT_LANG;
}
if(!array_key_exists($lang, plxUtils::getLangs())) {
	$lang = DEFAULT_LANG;
}
loadLang(PLX_CORE.'lang/'.$lang.'/install.php');
loadLang(PLX_CORE.'lang/'.$lang.'/core.php');

# On vérifie que PHP 5 ou superieur soit installé
if(version_compare(PHP_VERSION, '5.0.0', '<')){
	header('Content-Type: text/plain; charset=' . PLX_CHARSET);
	echo L_WRONG_PHP_VERSION;
	exit;
}

# On vérifie que PluXml n'est pas déjà installé
if(file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Content-Type: text/plain; charset=' . PLX_CHARSET);
	echo L_ERR_PLUXML_ALREADY_INSTALLED;
	exit;
}

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Echappement des caractères
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$_POST = plxUtils::unSlash($_POST);
}

# Initialisation du timezone
$timezone = 'Europe/Paris';
if(isset($_POST['timezone'])) $timezone=$_POST['timezone'];
if(!array_key_exists($timezone, plxTimezones::timezones())) {
	$timezone = date_default_timezone_get();
}

# Configuration de base
$data_folder = dirname(PLX_CONFIG_PATH) . '/';
$config = array(
	'title'=>'PluXml',
	'description'=>plxUtils::strRevCheck(L_SITE_DESCRIPTION),
	'meta_description'=>'',
	'meta_keywords'=>'',
	'timezone'=>$timezone,
	'allow_com'=>1,
	'mod_com'=>0,
	'mod_art'=>0,
	'enable_rss'=>1,
	'enable_rss_comment'=>1,
	'capcha'=>1,
	'lostpassword'=>1,
	'style'=>'defaut',
	'clef'=>plxUtils::charAleatoire(15),
	'bypage'=>5,
	'bypage_archives'=>5,
	'bypage_tags'=>5,
	'bypage_admin'=>10,
	'bypage_admin_coms'=>10,
	'bypage_feed'=>8,
	'tri'=>'desc',
	'tri_coms'=>'asc',
	'images_l'=>800,
	'images_h'=>600,
	'miniatures_l'=>200,
	'miniatures_h'=>100,
	'thumbs'=>0,
	'medias'=>$data_folder . 'medias/',
	'racine_articles'=>$data_folder . 'articles/',
	'racine_commentaires'=>$data_folder . 'commentaires/',
	'racine_statiques'=>$data_folder . 'statiques/',
	'racine_themes'=>'themes/',
	'racine_plugins'=>'plugins/',
	'homestatic'=>'',
	'hometemplate'=>'home.php',
	'urlrewriting'=>0,
	'gzip'=>0,
	'feed_chapo'=>0,
	'feed_footer'=>'',
	'version'=>PLX_VERSION,
	'default_lang'=>$lang,
	'userfolders'=>0,
	'display_empty_cat'=>0,
	'custom_admincss_file'=>'',
	'email_method' => 'sendmail',
	'smtp_server' => '',
	'smtp_username' => '',
	'smtp_password' => '',
	'smtp_port' => '465',
	'smtp_security' => 'ssl',
	'smtpOauth2_emailAdress' => '',
	'smtpOauth2_clientId'=> '',
	'smtpOauth2_clientSecret' => '',
	'smtpOauth2_refreshToken' => ''
);

# Vérification de l'existence des dossiers
$aFolders = array(
	PLX_ROOT . $config['medias'], # médias
	PLX_ROOT . PLX_CONFIG_PATH . 'plugins', # configuration/plugins
	PLX_ROOT . dirname(PLX_CONFIG_PATH) . '/templates', # templates
);
foreach($aFolders as $folder) {
	if(!is_dir($folder)) {
		@mkdir($folder, 0755, true);
	}
}

$filename = PLX_ROOT . PLX_CONFIG_PATH . 'index.html';
if(!file_exists($filename)) {
	touch($filename);
}

function install_datas($content, $config) {

	# gestion du timezone
	date_default_timezone_set($config['timezone']);

	$install_datas = ($content['data'] == '1');
	$now = date('YmdHi');

	# Création du fichier de configuration
	ob_start();
?>
<document>
<?php
	foreach($config  as $k=>$v) {
		$value = is_numeric($v) ? $v : '<![CDATA[' . $v . ']]>';
?>
	<parametre name="<?= $k ?>"><?= $value ?></parametre>
<?php
	}
?>
</document>
<?php
	plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_PARAMETERS'));

	# Création du fichier des utilisateurs
	$salt = plxUtils::charAleatoire(10);
	ob_start();
?>
<document>
	<user number="001" active="1" profil="0" delete="0">
		<login><?= $content['login'] ?></login>
		<name><?= $content['fullname'] ?></name>
		<infos>Webmaster</infos>
		<password><![CDATA[<?= sha1($salt . md5(trim($content['password']))) ?>]]></password>
		<salt><?= $salt ?></salt>
		<email><?= $content['email'] ?></email>
		<lang><?= $config['default_lang'] ?></lang>
	</user>
</document>
<?php
	plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_USERS'));

	# Création du fichier des categories
	ob_start();
?>
<document>
<?php
	if($install_datas) {
?>
	<categorie number="001" active="1" homepage="1" tri="<?= $config['tri'] ?>" bypage="<?= $config['bypage'] ?>" menu="oui" url="<?= L_DEFAULT_CATEGORY_URL ?>" template="categorie.php">
		<name><?= plxUtils::strRevCheck(L_DEFAULT_CATEGORY_TITLE) ?></name>
		<description></description>
		<meta_description></meta_description>
		<meta_keywords></meta_keywords>
		<title_htmltag></title_htmltag>
		<thumbnail></thumbnail>
		<thumbnail_title></thumbnail_title>
		<thumbnail_alt></thumbnail_alt>
	</categorie>
<?php
	}
?>
</document>
<?php
	plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_CATEGORIES'));

	# Création du fichier des pages statiques
	ob_start();
?>
<document>
<?php
	if($install_datas) {
		plxUtils::write(file_get_contents(PLX_CORE.'/templates/install-page.txt'),PLX_ROOT.$config['racine_statiques'].'001.'.L_DEFAULT_STATIC_URL.'.php');
?>
	<statique number="001" active="1" menu="oui" url="<?= L_DEFAULT_STATIC_URL ?>" template="static.php">
		<group></group>
		<name><?= plxUtils::strRevCheck(L_DEFAULT_STATIC_TITLE) ?></name>
		<meta_description></meta_description>
		<meta_keywords></meta_keywords>
		<title_htmltag></title_htmltag>
		<date_creation><?= $now ?></date_creation>
		<date_update><?= $now ?></date_update>
	</statique>
<?php
	}
?>
</document>
<?php
	plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_STATICS'));

	if($install_datas){
		# Création du premier article

		$thumbnail = '';
		$srcs = glob('*/*/theme/images/pluxml.png');
		if(!empty($srcs)) {
			$thumbnail = $config['medias'] . 'pluxml.png';
			copy($srcs[0], PLX_ROOT . $thumbnail);
		}

		$html = explode('-----', file_get_contents(PLX_CORE.'/templates/install-article.txt'));
		ob_start();
?>
<document>
	<title><?= plxUtils::strRevCheck(L_DEFAULT_ARTICLE_TITLE) ?></title>
	<allow_com>1</allow_com>
	<template>article.php</template>
	<chapo><![CDATA[<?= $html[0] ?>]]></chapo>
	<content><![CDATA[<?= $html[1] ?>]]></content>
	<tags>PluXml></tags>
	<meta_description></meta_description>
	<meta_keywords></meta_keywords>
	<title_htmltag></title_htmltag>
	<date_creation><?= $now ?></date_creation>
	<date_update><?= $now ?></date_update>
	<thumbnail><?= $thumbnail ?></thumbnail>
	<thumbnail_alt>PluXml logo</thumbnail_alt>
	<thumbnail_title>PluXml</thumbnail_title>
</document>
<?php
		plxUtils::write(XML_HEADER . ob_get_clean(), PLX_ROOT . $config['racine_articles'] . '0001.001.001.' . $now . '.' . L_DEFAULT_ARTICLE_URL . '.xml');
	}

	# Création du fichier des tags servant de cache
		ob_start();
?>
<document>
<?php
	if($install_datas) {
?>
	<article number="0001" date="<?= $now ?>" active="1">PluXml</article>
<?php
	}
?>
</document>
<?php
	plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_TAGS'));

	# Création du fichier des plugins
	ob_start();
?>
<document>
</document>
<?php
	plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_PLUGINS'));

	if($install_datas) {
		# Création du premier commentaire
		ob_start();
?>
<comment>
	<author><![CDATA[pluxml]]></author>
	<type>normal</type>
	<ip>127.0.0.1</ip>
	<mail>contact@pluxml.org</mail>
	<site>PLX_URL_REPO></site>
	<content><?= plxUtils::strRevCheck(L_DEFAULT_COMMENT_CONTENT) ?></content>
</comment>
<?php
		plxUtils::write(XML_HEADER . ob_get_clean(), PLX_ROOT . $config['racine_commentaires'] . '0001.' . date('U') . '-1.xml');
	}
}

if(!empty($_POST['install'])) {
	$msg = plxUtils::checkProfil($_POST);
	if($msg === true) {
		if(preg_match('#^\w[\w-]+/?$#', $_POST['data_folder']) and rtrim($_POST['data_folder'], '/') . '/' != $data_folder) {
			# Renommage du dossier des données (data)
			$target = rtrim($_POST['data_folder'], '/');
			if(rename(PLX_ROOT. rtrim($data_folder, '/'), PLX_ROOT . $target)) {
				# Mise à jour de config.php
				$target .= '/';
				$plx_config_path = $target . 'configuration/';
				$content =
					'<?php' . PHP_EOL .
					'const PLX_CONFIG_PATH = \'' . $plx_config_path . '\';' . PHP_EOL;
				$filename = PLX_ROOT . 'config.php';
				file_put_contents($filename, $content);
				opcache_invalidate($filename);

				$CONSTS = array(
					'XMLFILE_PARAMETERS'	=> PLX_ROOT . $plx_config_path . 'parametres.xml',
					'XMLFILE_CATEGORIES'	=> PLX_ROOT . $plx_config_path . 'categories.xml',
					'XMLFILE_STATICS'		=> PLX_ROOT . $plx_config_path . 'statiques.xml',
					'XMLFILE_USERS'			=> PLX_ROOT . $plx_config_path . 'users.xml',
					'XMLFILE_PLUGINS'		=> PLX_ROOT . $plx_config_path . 'plugins.xml',
					'XMLFILE_TAGS'			=> PLX_ROOT . $plx_config_path . 'tags.xml',
				);

				foreach(array('medias', 'racine_articles', 'racine_commentaires', 'racine_statiques') as $f) {
					$config[$f] = preg_replace('#^' . $data_folder . '\b#', $target, $config[$f]);
				}
			}
		}

		install_datas($_POST, $config);
		header('Location: ' . plxUtils::getRacine());
		exit;
	}

	$name=$_POST['fullname'];
	$login=$_POST['login'];
	$email=$_POST['email'];
	$data=$_POST['data'];
	$data_folder=$_POST['data_folder'];
} else {
	$name='';
	$login='';
	$email='';
	$data='1';
	$data_folder=$data_folder;
}
plxUtils::cleanHeaders();

$admin = 'core/admin/';
if(!file_exists(PLX_ROOT . $admin)) {
	$auths = glob(PLX_ROOT . '*/*/auth.php');

	if(!empty($auths)) {
		$admin = preg_replace('#.*/([^\/]+/\w[\w-]+/)auth\.php$#', '$1', $auths[0]);
	}
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
	<meta charset="<?= strtolower(PLX_CHARSET) ?>" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title><?= L_PLUXML_INSTALLATION.' '.L_VERSION.' '.PLX_VERSION ?></title>
<?php plxUtils::printLinkCss(); ?>
</head>

<body>

	<main class="main grid">

		<aside class="aside col sml-12 med-3 lrg-2">

		</aside>

		<section class="section col sml-12 med-9 med-offset-3 lrg-10 lrg-offset-2" style="margin-top: 0">

			<header>

				<h1><?= L_PLUXML_VERSION.' '.PLX_VERSION ?> - <?= L_INSTALL_TITLE ?></h1>

			</header>

<?php
if(!empty($msg)) {
?>
			<div class="alert red"><?= $msg ?></div>
<?php
}
?>

			<form method="post">

				<fieldset>

					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_default_lang"><?= L_SELECT_LANG ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $lang) ?>&nbsp;
							<input type="submit" name="select_lang" value="<?= L_INPUT_CHANGE ?>" />
							<?= plxToken::getTokenPostMethod() ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_default_lang"><?= L_INSTALL_DATA ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printSelect('data', array('1' => L_YES, '0' => L_NO), $data) ?>&nbsp;
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_name"><?= L_USERNAME ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printInput('fullname', $name, 'text', '20-64',false, '', '', 'autofocus', true); ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_login"><?= L_LOGIN ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printInput('login', $login, 'text', '20-64', false, '', '', '', true); ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_password"><?= L_PASSWORD ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printInput('password', '', 'password', '20-64'); ?>
							<?php plxUtils::printPasswordStrength('password'); ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_password2"><?= L_PASSWORD_CONFIRMATION ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printInput('password2', '', 'password', '20-64'); ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_email"><?= L_EMAIL ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printInput('email', $email, 'email', '20-64', false, '', '', '', true); ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_name"><?= L_DATA_FOLDER ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printInput('data_folder', $data_folder, 'text', '20-64',false, '', '', '', true); ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_timezone"><?= L_TIMEZONE ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printSelect('timezone', plxTimezones::timezones(), $timezone); ?>
						</div>
					</div>

					<input class="blue" type="submit" name="install" value="<?= L_INPUT_INSTALL ?>" />
					<?= plxToken::getTokenPostMethod() ?>

					<ul class="unstyled-list">
						<li><strong><?= L_PLUXML_VERSION; ?> <?= PLX_VERSION ?> (<?= L_INFO_CHARSET ?> <?= PLX_CHARSET ?>)</strong></li>
						<li><?= L_INFO_PHP_VERSION.' : '.phpversion() ?></li>
						<?php if (!empty($_SERVER['SERVER_SOFTWARE'])) { ?>
						<li><?= $_SERVER['SERVER_SOFTWARE']; ?></li>
						<?php } ?>
						<?php plxUtils::testWrite(PLX_ROOT) ?>
						<?php plxUtils::testWrite(PLX_ROOT.PLX_CONFIG_PATH) ?>
						<?php plxUtils::testWrite(PLX_ROOT.PLX_CONFIG_PATH.'plugins/') ?>
						<?php plxUtils::testWrite(PLX_ROOT.$config['racine_articles']) ?>
						<?php plxUtils::testWrite(PLX_ROOT.$config['racine_commentaires']) ?>
						<?php plxUtils::testWrite(PLX_ROOT.$config['racine_statiques']) ?>
						<?php plxUtils::testWrite(PLX_ROOT.$config['medias']) ?>
						<?php plxUtils::testWrite(PLX_ROOT.$config['racine_plugins']) ?>
						<?php plxUtils::testWrite(PLX_ROOT.$config['racine_themes']) ?>
						<?php plxUtils::testModReWrite() ?>
						<?php plxUtils::testLibGD() ?>
						<?php plxUtils::testLibXml() ?>
						<?php plxUtils::testMail() ?>
					</ul>

				</fieldset>

			</form>

		</section>

	</main>
	<script src="<?= PLX_ROOT . $admin; ?>js/visual.js"></script>
</body>

</html>

