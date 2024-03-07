<?php
const PLX_ROOT = './';
const PLX_CORE = PLX_ROOT .'core/';
const HTACCESS_CONTENT = <<< EOT
Options -Indexes

<Files *>
    Order allow,deny
    Deny from all
</Files>

EOT;
const HTACCESS_PLUGINS_CONTENT = <<< EOT
Options -Indexes

<Files "*.php">
	Order Allow,Deny
	Deny from all
</Files>

EOT;
const HTACCESS_MEDIAS_CONTENT = <<< EOT
Options -Indexes

EOT;

include PLX_CORE.'lib/config.php';

# On démarre la session
plx_session_start();

# Chargement des langues
$lang = (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : DEFAULT_LANG;
if(!empty($_POST) AND $_POST['default_lang'] != DEFAULT_LANG ){
	$lang = $_POST['default_lang'];
}
if(!array_key_exists($lang, array_keys(plxUtils::getLangs()))) {
	$lang = DEFAULT_LANG;
}
loadLang(PLX_CORE.'lang/'.$lang.'/install.php');
loadLang(PLX_CORE.'lang/'.$lang.'/core.php');

# On vérifie la version minimale de PHP
if(version_compare(PHP_VERSION, PHP_VERSION_MIN, '<')){
	header('Content-Type: text/plain; charset=UTF-8');
	echo L_WRONG_PHP_VERSION;
	exit;
}

# On vérifie que PluXml n'est pas déjà installé
if(file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Content-Type: text/plain; charset=UTF-8');
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
$config = DEFAULT_CONFIG;
$config['description'] = plxUtils::strRevCheck(L_SITE_DESCRIPTION);
$config['timezone'] = $timezone;
$config['clef'] = plxUtils::charAleatoire(15);
$config['default_lang'] = $lang;

# Vérification de l'existence des dossiers
foreach(array(
	'medias',
	'racine_articles',
	'racine_commentaires',
	'racine_statiques',
	'racine_plugins',
) as $folder) {
	$target = PLX_ROOT . $config[$folder];
	if(!is_dir($target)) {
		@mkdir($target, 0755, true);
	}

	if(is_writable($target)) {
		switch($folder) {
			case 'racine_plugins':
				file_put_contents($target . '.htaccess', HTACCESS_PLUGINS_CONTENT);
				break;
			case 'medias':
				file_put_contents($target . '.htaccess', HTACCESS_MEDIAS_CONTENT);
				break;
			default:
				file_put_contents($target . '.htaccess', HTACCESS_CONTENT);
		}
	}
}

# Vérification d'autres dossiers
foreach(array(
	PLX_ROOT . PLX_CONFIG_PATH,
	PLX_ROOT.PLX_CONFIG_PATH.'plugins',
	PLX_ROOT.'data/templates',
) as $target) {
	if(!is_dir($target)) {
		@mkdir($target, 0755, true);
	}
}

const CDATA_EXCLUDE = array(
	'timezone',
	'style',
	'clef',
	'tri',
	'tri_coms',
	'medias',
	'racine_articles',
	'racine_commentaires',
	'racine_statiques',
	'racine_themes',
	'racine_plugins',
	'hometemplate',
	'version',
	'default_lang',
	'email_method',
	'smtp_security',
);

function install($content, $config) {

	if(!is_writable(PLX_ROOT . PLX_CONFIG_PATH)) {
		return;
	}

	# gestion du timezone
	date_default_timezone_set($config['timezone']);

	# Création du fichier de configuration
	ob_start();
?>
<document>
<?php
	foreach($config  as $k=>$v) {
?>
	<parametre name="<?= $k ?>"><?= plxUtils::strCheck($v, !is_numeric($v) and !in_array($k, CDATA_EXCLUDE)) ?></parametre>
<?php
	}
?>
</document>
<?php
	plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_PARAMETERS'));

	# Création du fichier des utilisateurs
	ob_start();
	$salt = plxUtils::charAleatoire(10);
?>
<document>
	<user number="001" active="1" profil="0" delete="0">
		<login><![CDATA[<?= trim($content['login']) ?>]]></login>
		<name><![CDATA[<?= $content['name'] ?>]]></name>
		<infos><![CDATA[]]></infos>
		<password><![CDATA[<?= sha1($salt . md5(trim($content['pwd']))) ?>]]></password>
		<salt><![CDATA[<?= $salt ?>]]></salt>
		<email><![CDATA[<?= trim($content['email'])  ?>]]></email>
		<lang><![CDATA[<?= $config['default_lang'] ?>]]></lang>
	</user>
</document>
<?php
	plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_USERS'));

	# Création du fichier des categories
	ob_start();
?>
<document>
<?php
	if($content['data']>0) {
?>
	<categorie number="001" active="1" homepage="1" tri="<?= $config['tri'] ?>" bypage="<?= $config['bypage'] ?>" menu="oui" url="<?= L_DEFAULT_CATEGORY_URL ?>" template="categorie.php">
		<name><![CDATA[<?= plxUtils::strRevCheck(L_DEFAULT_CATEGORY_TITLE) ?>]]></name>
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
	if($content['data'] > 0) {
		$now = date('YmdHi');
?>
	<statique number="001" active="1" menu="oui" url="<?= L_DEFAULT_STATIC_URL ?>" template="static.php">
		<group></group>
		<name><![CDATA[<?= plxUtils::strRevCheck(L_DEFAULT_STATIC_TITLE) ?>]]></name>
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
	if($content['data'] > 0) {
		plxUtils::write(file_get_contents(PLX_CORE.'/templates/install-page.txt'),PLX_ROOT.$config['racine_statiques'].'001.'.L_DEFAULT_STATIC_URL.'.php');
	}

	if($content['data'] > 0){
		# Création du premier article
		$html = explode('-----', file_get_contents(PLX_CORE.'/templates/install-article.txt'));
		ob_start();
?>
<document>
	<title><![CDATA[<?= plxUtils::strRevCheck(L_DEFAULT_ARTICLE_TITLE) ?>]]></title>
	<allow_com>1</allow_com>
	<template>article.php</template>
	<chapo><![CDATA[<?= $html[0] ?>]]></chapo>
	<content><![CDATA[<?= $html[1] ?>]]></content>
	<tags><![CDATA[PluXml]]></tags>
	<meta_description></meta_description>
	<meta_keywords><![CDATA[pluxml,cms,xml]]></meta_keywords>
	<title_htmltag></title_htmltag>
	<date_creation><?= $now ?></date_creation>
	<date_update><?= $now ?></date_update>
	<thumbnail>core/admin/theme/images/pluxml.png</thumbnail>
	<thumbnail_alt><![CDATA[PluXml logo]]></thumbnail_alt>
	<thumbnail_title><![CDATA[PluXml]]></thumbnail_title>
</document>
<?php
		plxUtils::write(XML_HEADER . ob_get_clean(), PLX_ROOT.$config['racine_articles'].'0001.001.001.'.date('YmdHi').'.'.L_DEFAULT_ARTICLE_URL.'.xml');
	}

	# Création du fichier des tags servant de cache
	ob_start();
?>
<document>
<?php
	if($content['data'] > 0) {
?>
	<article number="0001" date="<?= $now ?>" active="1"><![CDATA[PluXml]]></article>
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

	if($content['data']>0) {
		# Création du premier commentaire
		ob_start();
?>
<comment>
	<author><![CDATA[pluxml]]></author>
	<type>normal</type>
	<ip>127.0.0.1</ip>
	<mail><![CDATA[contact@pluxml.org]]></mail>
	<site><![CDATA[<?= PLX_URL_REPO ?>]]></site>
	<content><![CDATA[<?= plxUtils::strRevCheck(L_DEFAULT_COMMENT_CONTENT) ?>]]></content>
</comment>
<?php
		plxUtils::write(XML_HEADER . ob_get_clean(), PLX_ROOT . $config['racine_commentaires'] . '0001.' . date('U') . '-1.xml');
	}

} # end of function install($content, $config)

$msg='';
if(!empty($_POST['install'])) {

	if(trim($_POST['name']=='')) $msg = L_ERR_MISSING_USER;
	elseif(trim($_POST['login']=='')) $msg = L_ERR_MISSING_LOGIN;
	elseif(trim($_POST['pwd']=='')) $msg = L_ERR_MISSING_PASSWORD;
	elseif($_POST['pwd']!=$_POST['pwd2']) $msg = L_ERR_PASSWORD_CONFIRMATION;
	elseif(trim($_POST['email']=='')) $msg = L_ERR_MISSING_EMAIL;
	else {
		install($_POST, $config);
		header('Location: '.plxUtils::getRacine());
		exit;
	}
	$name=$_POST['name'];
	$login=$_POST['login'];
	$email=$_POST['email'];
	$data=$_POST['data'];
}
else {
	$name='';
	$login='';
	$email='';
	$data='1';
}

plxUtils::cleanHeaders();

function passwordDict() {
	$words = array(
		L_PWD_VERY_WEAK,
		L_PWD_WEAK,
		L_PWD_GOOD,
		L_PWD_STRONG,
	);
	return implode('|', $words);
}

?>
<!DOCTYPE html>
<head>
	<meta charset="<?= strtolower(PLX_CHARSET) ?>" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title><?= L_PLUXML_INSTALLATION.' '.L_VERSION.' '.PLX_VERSION ?></title>
	<link rel="stylesheet" type="text/css" href="<?= PLX_CORE ?>admin/theme/plucss.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?= PLX_CORE ?>admin/theme/theme.css" media="screen" />
</head>

<body>
	<main class="main grid">
		<aside class="aside col sml-12 med-3 lrg-2">
		</aside>
		<section class="section col sml-12 med-9 med-offset-3 lrg-10 lrg-offset-2" style="margin-top: 0">
			<header>
				<h1><?= L_PLUXML_VERSION.' '.PLX_VERSION ?> - <?= L_INSTALL_TITLE ?></h1>
			</header>
<?php if(!empty(trim($msg))) { ?>
			<div class="alert red">
				<?= $msg ?>
			</div>
<?php } ?>

<?php
	if(
		is_writable(PLX_ROOT . PLX_CONFIG_PATH) and
		is_writable(PLX_ROOT . PLX_ROOT.dirname($config['racine_articles'])) and
		is_writable(PLX_ROOT . PLX_ROOT.$config['racine_plugins']) and
		is_writable(PLX_ROOT . PLX_ROOT.$config['racine_themes']) and
		function_exists('xml_parser_create')
	) {
?>
			<form method="post">
				<fieldset>
					<?= plxToken::getTokenPostMethod() ?>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_default_lang"><?= L_SELECT_LANG ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $lang) ?>&nbsp;
							<input type="submit" name="select_lang" value="<?= L_INPUT_CHANGE ?>" />
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
							<?php plxUtils::printInput('name', $name, 'text', '20-48',false,'','','autofocus', true) ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_login"><?= L_LOGIN ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printInput('login', $login, 'text', '20-48', '', '', '', '', true) ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_pwd"><?= L_PASSWORD ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printInput('pwd', '', 'password', '20-48', false, '', '', '', true) ?>
							<span data-lang="<?= passwordDict() ?>"></span>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_pwd2"><?= L_PASSWORD_CONFIRMATION ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printInput('pwd2', '', 'password', '20-48', false, '', '', '', true) ?>
							<span data-lang="❌|✅"></span>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_email"><?= L_EMAIL ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printInput('email', $email, 'email', '20-48', '', '', '', '', true) ?>
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
				</fieldset>
			</form>
<?php
	}
?>

			<ul class="unstyled-list">
				<li><strong><?= L_PLUXML_VERSION; ?> <?= PLX_VERSION ?> (<?= L_INFO_CHARSET ?> <?= PLX_CHARSET ?>)</strong></li>
				<li><?= L_INFO_PHP_VERSION.' : '.phpversion() ?></li>
<?php if (!empty($_SERVER['SERVER_SOFTWARE'])) { ?>
				<li><?= $_SERVER['SERVER_SOFTWARE']; ?></li>
<?php } ?>
				<?php plxUtils::testWrite(PLX_ROOT.'config.php') ?>
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
		</section>
	</main>
	<script src="<?= PLX_CORE ?>admin/js/visual.js"></script>
</body>
</html>
