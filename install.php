<?php
const PLX_ROOT = './';
define('PLX_CORE', PLX_ROOT . 'core/');

include PLX_ROOT . 'config.php';
include PLX_CORE . 'lib/config.php';

# On démarre la session
session_set_cookie_params(0, "/", $_SERVER['SERVER_NAME'], isset($_SERVER["HTTPS"]), true);
session_start();

# On inclut les librairies nécessaires
foreach(explode(' ', 'timezones date glob utils token') as $aClass) {
	include PLX_CORE . 'lib/class.plx.' . $aClass . '.php';
}

# Chargement des langues
$lang = (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : DEFAULT_LANG;
if(isset($_POST['default_lang'])) $lang=$_POST['default_lang'];
if(!array_key_exists($lang, plxUtils::getLangs())) {
	$lang = DEFAULT_LANG;
}
loadLang(PLX_CORE . 'lang/' . $lang . '/install.php');
loadLang(PLX_CORE . 'lang/' . $lang . '/core.php');

# On vérifie que PHP PHP_VERSION_MIN ou supérieur soit installé
if (version_compare(PHP_VERSION, PHP_VERSION_MIN, '<')) {
    header('Content-Type: text/plain; charset=UTF-8');
    printf(L_WRONG_PHP_VERSION, PHP_VERSION_MIN);
    exit();
}

# On vérifie que PluXml n'est pas déjà installé
if (file_exists(path('XMLFILE_PARAMETERS'))) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo L_ERR_PLUXML_ALREADY_INSTALLED;
    exit();
}

# Controle du token du formulaire
plxToken::validateFormToken($_POST);

# Dossier des données par défaut : data/
$data = 'data' . DIRECTORY_SEPARATOR;
# Les .htaccess
$htaccess = array(
'<Files *>
    Order allow,deny
    Deny from all
</Files>',
'options -indexes
<Files "*">
	SetHandler default-handler
</Files>',
'Options -Indexes'
);

# Vérification de l'existence des dossiers et fichiers principaux et si besoin on les crées
foreach(explode(' ', 'medias articles commentaires configuration statiques templates') AS $folder) {
	if(!is_dir(PLX_ROOT . $data . $folder)) {
		@mkdir(PLX_ROOT . $data . $folder,0755,true);
		if(strpos($folder, 'medias') === FALSE){
			@file_put_contents(PLX_ROOT . $data . $folder . DIRECTORY_SEPARATOR . '.htaccess', $htaccess[0]);
			touch(PLX_ROOT . $data . $folder . DIRECTORY_SEPARATOR . 'index.html');
		}
	}
}
@file_put_contents(PLX_ROOT . $data . '.htaccess', $htaccess[1]);
touch(PLX_ROOT . $data . 'index.html');

# Vérification de l'existence du dossier data/configuration/plugins
if(!is_dir(PLX_ROOT . PLX_CONFIG_PATH . 'plugins')) {
	@mkdir(PLX_ROOT . PLX_CONFIG_PATH . 'plugins',0755,true);
	@file_put_contents(PLX_ROOT . PLX_CONFIG_PATH . 'plugins' . DIRECTORY_SEPARATOR . '.htaccess', $htaccess[2]);
	touch(PLX_ROOT . PLX_CONFIG_PATH . 'plugins' . DIRECTORY_SEPARATOR . 'index.html');
}

# Vérification de l'existence du dossier plugins
if(!is_dir(PLX_ROOT . 'plugins')) {
	@mkdir(PLX_ROOT . 'plugins',0755,true);
	@file_put_contents(PLX_ROOT . 'plugins' . DIRECTORY_SEPARATOR . '.htaccess', $htaccess[2]);
	touch(PLX_ROOT . 'plugins' . DIRECTORY_SEPARATOR . 'index.html');
}

# Initialisation du timezone
$timezone = 'Europe/Paris';
if (isset($_POST['timezone'])) {
	$timezone = $_POST['timezone'];
}
if (! array_key_exists($timezone, plxTimezones::timezones())) {
	$timezone = date_default_timezone_get();
}

# Configuration de base
$root = dirname(PLX_CONFIG_PATH) . '/';
$config = array(
	'version' => PLX_VERSION,
	'title' => 'PluXml',
	'description' => plxUtils::strRevCheck(L_SITE_DESCRIPTION),
	'meta_description' => 'A flat CMS with XML',
	'meta_keywords' => 'cms',
	'timezone' => date_default_timezone_get(),
	'allow_com' => 1,
	'mod_com' => 0,
	'mod_art' => 0,
	'enable_rss' => 1,
	'capcha' => 1,
	'lostpassword' => 1,
	'style' => 'defaut',
	'clef' => plxUtils::charAleatoire(15),
	'bypage' => 5,
	'bypage_archives' => 5,
	'bypage_tags' => 5,
	'bypage_admin' => 10,
	'bypage_admin_coms' => 10,
	'bypage_feed' => 8,
	'tri' => 'desc',
	'tri_coms' => 'asc',
	'images_l' => 800,
	'images_h' => 600,
	'miniatures_l' => 200,
	'miniatures_h' => 100,
	'thumbs' => 0,
	'medias' => $root . 'medias/',
	'racine_articles' => $root . 'articles/',
	'racine_commentaires' => $root . 'commentaires/',
	'racine_statiques' => $root . 'statiques/',
	'racine_themes' => 'themes/',
	'racine_plugins' => 'plugins/',
	'custom_admincss_file' => '',
	'homestatic' => '',
	'hometemplate' => 'home.php',
	'urlrewriting' => 0,
	'cleanurl' => 0,
	'gzip' => 0,
	'feed_chapo' => 0,
	'feed_footer' => '',
	'default_lang' => $lang,
	'userfolders' => 0,
	'display_empty_cat' => 0,
	'email_method' => 'sendmail',
	'smtp_server' => '',
	'smtp_username' => '',
	'smtp_password' => '',
	'smtp_port' => '465',
	'smtp_security' => 'ssl',
	'smtpOauth2_emailAdress' => '',
	'smtpOauth2_clientId' => '',
	'smtpOauth2_clientSecret' => '',
	'smtpOauth2_refreshToken' => ''
);

# Vérification de l'existence des dossiers médias, configuration/plugins et templates
$folders = array(
	PLX_ROOT . $config['medias'],
	PLX_ROOT . PLX_CONFIG_PATH . 'plugins',
	PLX_ROOT . dirname(PLX_CONFIG_PATH) . '/templates'
);
foreach ($folders as $f) {
	if (! is_dir($f)) {
		@mkdir($f, 0755, true);
	}
}

function install($content, $config) {

	# Initialisation du timezone
	if (isset($_POST['timezone'])) {
		$timezone = $_POST['timezone'];
		if (array_key_exists($timezone, plxTimezones::timezones())) {
			$config['timezone'] = $timezone;
		}
	}
	# gestion du timezone
	date_default_timezone_set($config['timezone']);

	# Création du fichier de configuration
	ob_start();
?>
<document>
<?php
	foreach ($config as $k => $v) {
?>
	<parametre name="<?= $k ?>"><?= plxUtils::cdataCheck($v) ?></parametre>
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
		<login><?= trim($content['login']) ?></login>
		<name><?= plxUtils::cdataCheck(trim($content['name'])) ?></name>
		<infos></infos>
		<password><?= sha1($salt.md5(trim($content['pwd']))) ?></password>
		<salt><?= $salt ?></salt>
		<email><?= trim($content['email']) ?></email>
		<lang><?= $config['default_lang'] ?></lang>
	</user>
</document>
<?php
    $xml = XML_HEADER . ob_get_clean();
    plxUtils::write($xml, path('XMLFILE_USERS'));

	# Création du fichier des categories
	ob_start();
?>
<document>
<?php
    if ($content['data'] > 0) {
?>
	<categorie number="001" active="1" homepage="1" tri="<?= $config['tri'] ?>" bypage="<?= $config['bypage'] ?>" menu="oui" url="<?= L_DEFAULT_CATEGORY_URL ?>" template="categorie.php">
		<name><?= plxUtils::cdataCheck(plxUtils::strRevCheck(L_DEFAULT_CATEGORY_TITLE)) ?></name>
		<description>Sample</description>
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
	if ($content['data'] > 0) {
?>
	<statique number="001" active="1" menu="oui" url="<?= L_DEFAULT_STATIC_URL ?>" template="static.php">
		<group></group>
		<name><?= plxUtils::cdataCheck(plxUtils::strRevCheck(L_DEFAULT_STATIC_TITLE)) ?></name>
		<meta_description></meta_description>
		<meta_keywords></meta_keywords>
		<title_htmltag>plxUtils::cdataCheck('Sample of a static page')</title_htmltag>
		<date_creation><?= date('YmdHi') ?></date_creation>
		<date_update><?= date('YmdHi') ?></date_update>
	</statique>
<?php
	}
?>
</document>
<?php
	$xml = XML_HEADER . ob_get_clean();
	plxUtils::write($xml, path('XMLFILE_STATICS'));

	if($content['data'] > 0) {
		plxUtils::write(
			file_get_contents(PLX_CORE.'/templates/install-page.txt'),
			PLX_ROOT . $config['racine_statiques'] . '001.' . L_DEFAULT_STATIC_URL . '.php'
		);
	}

	if($content['data'] > 0) {
		# Création du premier article
		list($chapo, $article) = explode('-----', file_get_contents(PLX_CORE.'/templates/install-article.txt'));
?>
<document>
	<title><?= plxUtils::cdataCheck(plxUtils::strRevCheck(L_DEFAULT_ARTICLE_TITLE)) ?></title>
	<allow_com>1</allow_com>
	<template>article.php</template>
	<chapo><?= plxUtils::cdataCheck($chapo) ?></chapo>
	<content><?= plxUtils::cdataCheck($article) ?></content>
	<tags>PluXml</tags>
	<meta_description>A sample of article</meta_description>
	<meta_keywords>pluxml</meta_keywords>
	<title_htmltag></title_htmltag>
	<date_creation><?= date('YmdHi') ?></date_creation>
	<date_update><?= date('YmdHi') ?></date_update>
	<thumbnail>core/admin/theme/images/pluxml.png</thumbnail>
</document>
<?php
		$xml = XML_HEADER . ob_get_clean();
		plxUtils::write($xml, PLX_ROOT . $config['racine_articles'] . '0001.001.001.' . date('YmdHi') . '.' . L_DEFAULT_ARTICLE_URL . '.xml');
	}

	# Création du fichier des tags servant de cache
	ob_start();
?>
<document>
<?php
    if ($content['data'] > 0) {
?>
	<article number="0001" date="<?= date('YmdHi') ?>" active="1">PluXml</article>
<?php
    }
?>
</document>
<?php
	$xml = XML_HEADER . ob_get_clean();
	plxUtils::write($xml, path('XMLFILE_TAGS'));

	# Création du fichier des plugins
	ob_start();
?>
<document>
</document>
<?php
	$xml = XML_HEADER . ob_get_clean();
	plxUtils::write($xml, path('XMLFILE_PLUGINS'));

	if($content['data'] > 0) {
		# Création du premier commentaire
		ob_start();
?>
<document>
	<comment>
		<author>pluxml</author>
		<type>normal</type>
		<ip>127.0.0.1</ip>
		<mail>contact@pluxml.org</mail>
		<site><?= PLX_URL_REPO ?></site>
		<content><?= plxUtils::cdataCheck(plxUtils::strRevCheck(L_DEFAULT_COMMENT_CONTENT)) ?></content>
	</comment>
</document>
<?php
        $xml = XML_HEADER . ob_get_clean();
        plxUtils::write($xml, PLX_ROOT . $config['racine_commentaires'] . '0001.' . date('U') . '-1.xml');
    }
}

$msg = '';
if (! empty($_POST['install'])) {

    if (trim($_POST['name'] == '')) {

        $msg = L_ERR_MISSING_USER;
    } elseif (trim($_POST['login'] == '')) {

        $msg = L_ERR_MISSING_LOGIN;
    } elseif (trim($_POST['pwd'] == '')) {

        $msg = L_ERR_MISSING_PASSWORD;
    } elseif ($_POST['pwd'] != $_POST['pwd2']) {

        $msg = L_ERR_PASSWORD_CONFIRMATION;
    } elseif (trim($_POST['email'] == '')) {

        $msg = L_ERR_MISSING_EMAIL;
    } else {
        install($_POST, $config);
        header('Location: ' . plxUtils::getRacine());
        exit();
    }

    $name = $_POST['name'];
    $login = $_POST['login'];
    $email = $_POST['email'];
    $data = $_POST['data'];
} else {
    $name = '';
    $login = '';
    $email = '';
    $data = '1';
}
plxUtils::cleanHeaders();
?>
<!DOCTYPE html>
<head>
	<meta charset="<?= strtolower(PLX_CHARSET) ?>" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title><?= L_PLUXML_INSTALLATION . ' ' . L_VERSION . ' ' . PLX_VERSION ?></title>
	<link rel="stylesheet" type="text/css" href="<?= PLX_CORE ?>admin/theme/plucss.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?= PLX_CORE ?>admin/theme/theme.css" media="screen" />
	<script src="<?= PLX_CORE ?>lib/visual.js"></script>
</head>
<body>

	<main class="main grid">

		<aside class="aside col med-3 lrg-2">

		</aside>

		<section class="section col med-9 med-offset-3 lrg-10 lrg-offset-2" style="margin-top: 0">

			<header>

				<h1><?= L_PLUXML_VERSION.' '.PLX_VERSION ?> - <?= L_INSTALL_TITLE ?></h1>

			</header>

			<?php if($msg!='') echo '<div class="alert red">'.$msg.'</div>'; ?>

			<form method="post">

				<fieldset>

					<div class="grid">
						<div class="col med-5 label-centered">
							<label for="id_default_lang"><?php echo L_SELECT_LANG ?>&nbsp;:</label>
						</div>
						<div class="col med-7">
							<?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $lang) ?>&nbsp;
							<input type="button" name="select_lang" value="<?php echo L_INPUT_CHANGE ?>" onclick="var x=document.getElementsByTagName('input'); for(i=0;i<x.length;i++){x[i].required=false;}this.type='submit';"/>
							<?php echo plxToken::getTokenPostMethod() ?>
						</div>
					</div>
					<div class="grid">
						<div class="col med-5 label-centered">
							<label for="id_default_lang"><?php echo L_INSTALL_DATA ?>&nbsp;:</label>
						</div>
						<div class="col med-7">
							<?php plxUtils::printSelect('data', array('1' => L_YES, '0' => L_NO), $data) ?>
						</div>
					</div>
					<div class="grid">
						<div class="col med-5 label-centered">
							<label for="id_name"><?php echo L_USERNAME ?>&nbsp;:</label>
						</div>
						<div class="col med-7">
							<?php plxUtils::printInput('name', $name, 'text', '20-255',false,'','','autofocus required') ?>
						</div>
					</div>
					<div class="grid">
						<div class="col med-5 label-centered">
							<label for="id_login"><?php echo L_LOGIN ?>&nbsp;:</label>
						</div>
						<div class="col med-7">
							<?php plxUtils::printInput('login', $login, 'text', '20-255', '', '', '', 'required') ?>
						</div>
					</div>
					<div class="grid">
						<div class="col med-5 label-centered">
							<label for="id_pwd"><?php echo L_PASSWORD ?>&nbsp;:</label>
						</div>
						<div class="col med-7">
							<?php
							list($very, $weak, $good, $strong) = array(L_PWD_VERY_WEAK, L_PWD_WEAK, L_PWD_GOOD, L_PWD_STRONG);
							$extras = <<< EOT
onkeyup="pwdStrength(this.id, ['$very', '$weak', '$good', '$strong']);" required
EOT;
							plxUtils::printInput('pwd', '', 'password', '20-255', false, '', '', $extras) ?>
							<span id="id_pwd_strenght"></span>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_pwd2"><?php echo L_CONFIRM_PASSWORD ?>&nbsp;:</label>
						</div>
						<div class="col med-7">
							<?php plxUtils::printInput('pwd2', '', 'password', '20-255', '', '', '', 'required') ?>
						</div>
					</div>
					<div class="grid">
						<div class="col med-5 label-centered">
							<label for="id_email"><?php echo L_EMAIL ?>&nbsp;:</label>
						</div>
						<div class="col med-7">
							<?php plxUtils::printInput('email', $email, 'email', '20-255', '', '', '', 'required') ?>
						</div>
					</div>
					<div class="grid">
						<div class="col med-5 label-centered">
							<label for="id_timezone"><?php echo L_TIMEZONE ?>&nbsp;:</label>
						</div>
						<div class="col med-7">
							<?php plxUtils::printSelect('timezone', plxTimezones::timezones(), $timezone); ?>
						</div>
					</div>

					<input class="blue" type="submit" name="install" value="<?php echo L_INPUT_INSTALL ?>" />
					<?= plxToken::getTokenPostMethod() ?>

					<ul class="unstyled-list">
						<li><strong><?php echo L_PLUXML_VERSION; ?> <?= PLX_VERSION ?> (<?= L_INFO_CHARSET ?> <?= PLX_CHARSET ?>)</strong></li>
						<li><?= L_INFO_PHP_VERSION.' : ' . phpversion() ?></li>
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
</body>
</html>