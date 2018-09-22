<?php
const PLX_ROOT = './';
const PLX_CORE = PLX_ROOT.'core/';
include(PLX_ROOT.'config.php');
include(PLX_CORE.'lib/config.php');
$docRoot = substr(PLX_CONFIG_PATH, 0, -strlen('configuration/'));

# On démarre la session
session_start();

# On inclut les librairies nécessaires
include(PLX_CORE.'lib/class.plx.timezones.php');
include(PLX_CORE.'lib/class.plx.date.php');
include(PLX_CORE.'lib/class.plx.glob.php');
include(PLX_CORE.'lib/class.plx.utils.php');
include(PLX_CORE.'lib/class.plx.token.php');

# Chargement des langues
$lang = DEFAULT_LANG;
if(isset($_POST['default_lang'])) {
	$lang=$_POST['default_lang'];
} elseif(
	filter_has_var(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE') and
	preg_match('@^([a-z]{2})@', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)
) {
	$lang = $matches[1];
}
if(!array_key_exists($lang, plxUtils::getLangs())) {
	$lang = DEFAULT_LANG;
}

loadLang(PLX_CORE.'lang/'.$lang.'/install.php');
loadLang(PLX_CORE.'lang/'.$lang.'/core.php');

# On vérifie que PHP 5 ou superieur soit installé
if(version_compare(PHP_VERSION, '5.0.0', '<')){
	header('Content-Type: text/plain;charset=UTF-8');
	echo utf8_decode(L_WRONG_PHP_VERSION);
	exit;
}

# On vérifie que PluXml n'est pas déjà installé
if(file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Content-Type: text/plain;charset=UTF-8');
	echo utf8_decode(L_ERR_PLUXML_ALREADY_INSTALLED);
	exit;
}

# Control du token du formulaire
plxToken::validateFormToken($_POST);

const FORBIDDEN = <<< FORBIDDEN
<Files "*">
	Order allow,deny
	Deny from all
</Files>\n
FORBIDDEN;

if(is_writable(PLX_ROOT.$docRoot) or mkdir(PLX_ROOT.$docRoot, 0755)) {
	# Vérification de l'existence de tous les dossiers de données du site
	foreach(array(
		'configuration' => true,
		'configuration/plugins' => false,
		'articles' => true,
		'commentaires' => true,
		'statiques' => true,
		'medias' => false,
		'plugins' => false
	) as $folder => $restricted) {
		$target = PLX_ROOT.$docRoot.$folder;
		if(!is_dir($target)) { @mkdir($target, 0755, true); }
		$htaccess = "$target/.htaccess";
		if($restricted and !file_exists($htaccess)) { 
			file_put_contents($htaccess, FORBIDDEN);
		}
	}

	# On vérifie l'existence de quelques fichiers dans le dossier de données
	foreach(array(
		'.htaccess' => "options -indexes\n",
		'index.html' => ''
	) as $filename => $content) {
		$f = PLX_ROOT.$docRoot.$filename;
		if(!file_exists($f)) {
			file_put_contents($f, $content);
		}
	}
}
	
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

function xmlDocSave($filename, $content='', $root='document') {
	$charset = PLX_CHARSET;
	$xml = <<< OUTPUT
<?xml version="1.0" encoding="$charset" ?>
<$root>
$content
</$root>
OUTPUT;
	plxUtils::write($xml, $filename);	
}

# Configuration de base
$config = array(
	'title'=>'PluXml',
	'description'=>plxUtils::strRevCheck(L_SITE_DESCRIPTION),
	'meta_description'=>'',
	'meta_keywords'=>'',
	'timezone'=>$timezone,
	'allow_com'=>1,
	'mod_com'=>0,
	'mod_art'=>0,
	'capcha'=>1,
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
	'medias' =>					$docRoot.'medias/',
	'racine_articles' =>		$docRoot.'articles/',
	'racine_commentaires' =>	$docRoot.'commentaires/',
	'racine_statiques' =>		$docRoot.'statiques/',
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
	'custom_admincss_file'=>''
);

function install($content, $config) {

	# gestion du timezone
	date_default_timezone_set($config['timezone']);

	$artDateStr = date('YmdHi');

	# Création du fichier de configuration
	$xml = '';
	foreach($config  as $k=>$v) {
		$value = (empty($v) or is_numeric($v)) ? $v : '<![CDATA['.plxUtils::cdataCheck($v).']]>';
		$xml .= <<< PARAM
		<parametre name="$k">$value</parametre>\n
PARAM;
	}
	xmlDocSave(path('XMLFILE_PARAMETERS'), $xml);

	# Création du fichier des utilisateurs
	$name = trim($content['name']);
	$login = trim($content['login']);
	$salt = plxUtils::charAleatoire(10);
	$password = sha1($salt.md5(trim($content['pwd'])));
	
	$xml = <<< USERS
	<user number="001" active="1" profil="0" delete="0">
		<login><![CDATA[$login]]></login>
		<name><![CDATA[$name]]></name>
		<infos></infos>
		<password>$password</password>
		<salt>$salt</salt>
		<email></email>
		<lang>${config['default_lang']}</lang>
	</user>
USERS;
	xmlDocSave(path('XMLFILE_USERS'), $xml);

	# Création du fichier des catégories
	$url = L_DEFAULT_CATEGORY_URL;
	$name = plxUtils::strRevCheck(L_DEFAULT_CATEGORY_TITLE);
	$xml = <<< CATS
	<categorie number="001" active="1" homepage="1" tri="${config['tri']}" bypage="${config['bypage']}" menu="oui" url="$url" template="categorie.php">
		<name><![CDATA[$name]]></name>
		<description></description>
		<meta_description></meta_description>
		<meta_keywords></meta_keywords>
		<title_htmltag></title_htmltag>
	</categorie>
CATS;
	xmlDocSave(path('XMLFILE_CATEGORIES'), $xml);

	# Création du fichier des pages statiques
	$url = L_DEFAULT_STATIC_URL;
	$name = plxUtils::strRevCheck(L_DEFAULT_STATIC_TITLE);
	$xml = <<< STATIQUES
	<statique number="001" active="1" menu="oui" url="$url" template="static.php">
		<group></group>
		<name><![CDATA[$name]]></name>
		<meta_description></meta_description>
		<meta_keywords></meta_keywords>
		<title_htmltag></title_htmltag>
		<date_creation>$artDateStr</date_creation>
		<date_update>$artDateStr></date_update>
	</statique>
STATIQUES;
	xmlDocSave(path('XMLFILE_STATICS'), $xml);
	plxUtils::write(
		file_get_contents(PLX_CORE.'/lib/html.static.txt'),
		PLX_ROOT.$config['racine_statiques'].'001.'.L_DEFAULT_STATIC_URL.'.php'
	);

	# Création du premier article
	$html = explode('-----', file_get_contents(PLX_CORE.'/lib/html.article.txt'));
	$artTitle = plxUtils::strRevCheck(L_DEFAULT_ARTICLE_TITLE);
	$url = L_DEFAULT_ARTICLE_URL;
	$xml = <<< FIRST_ARTICLE
	<title><![CDATA[$artTitle]]></title>
	<allow_com>1</allow_com>
	<template>article.php</template>
	<chapo><![CDATA[${html[0]}]]></chapo>
	<content><![CDATA[${html[1]}]]></content>
	<tags>PluXml</tags>
	<meta_description></meta_description>
	<meta_keywords></meta_keywords>
	<title_htmltag></title_htmltag>
	<date_creation>$artDateStr</date_creation>
	<date_update>$artDateStr</date_update>
	<thumbnail>core/admin/theme/images/pluxml.png</thumbnail>
FIRST_ARTICLE;
	$artFilename = PLX_ROOT."${config['racine_articles']}0001.001.001.$artDateStr.$url.xml";
	xmlDocSave($artFilename, $xml);

	# Création du fichier des tags servant de cache
	$xml = <<< TAGS
	<article number="0001" date="$artDateStr" active="1">PluXml</article>
TAGS;
	xmlDocSave(path('XMLFILE_TAGS'), $xml);

	# Création du fichier vide des plugins
	xmlDocSave(path('XMLFILE_PLUGINS'));

	# Création du premier commentaire
	$content = plxUtils::strRevCheck(L_DEFAULT_COMMENT_CONTENT);
	$xml = <<< COMMENTS
	<author><![CDATA[pluxml]]></author>
	<type>normal</type>
	<ip>127.0.0.1</ip>
	<mail>contact@pluxml.org</mail>
	<site>http://www.pluxml.org</site>
	<content><![CDATA[$content]]></content>
COMMENTS;
	$commFilename = PLX_ROOT.$config['racine_commentaires'].'0001.'.date('U').'-1.xml';
	xmlDocSave($commFilename, $xml, 'comment');

}

$msg='';
if(!empty($_POST['install'])) {

	if(trim($_POST['name']=='')) $msg = L_ERR_MISSING_USER;
	elseif(trim($_POST['login']=='')) $msg = L_ERR_MISSING_LOGIN;
	elseif(trim($_POST['pwd']=='')) $msg = L_ERR_MISSING_PASSWORD;
	elseif($_POST['pwd']!=$_POST['pwd2']) $msg = L_ERR_PASSWORD_CONFIRMATION;
	else {
		install($_POST, $config);
		header('Location: '.plxUtils::getRacine());
		exit;
	}
	$name=$_POST['name'];
	$login=$_POST['login'];
}
else {
	$name='';
	$login='';
}
plxUtils::cleanHeaders();
?>
<!DOCTYPE html>
<head>
	<meta charset="<?php echo strtolower(PLX_CHARSET) ?>" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title><?php echo L_PLUXML_INSTALLATION.' '.L_VERSION.' '.PLX_VERSION ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/plucss.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/theme.css" media="screen" />
</head>

<body>

	<main class="main grid">

		<aside class="aside col sml-12 med-3 lrg-2">

		</aside>

		<section class="section col sml-12 med-9 med-offset-3 lrg-10 lrg-offset-2" style="margin-top: 0">

			<header>

				<h1><?php echo L_PLUXML_VERSION.' '.PLX_VERSION ?> - <?php echo L_INSTALL_TITLE ?></h1>

			</header>

			<?php if($msg!='') echo '<div class="alert red">'.$msg.'</div>'; ?>

			<form action="install.php" method="post">

				<fieldset>

					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_default_lang"><?php echo L_SELECT_LANG ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $lang) ?>&nbsp;
							<input type="submit" name="select_lang" value="<?php echo L_INPUT_CHANGE ?>" />
							<?php echo plxToken::getTokenPostMethod() ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_name"><?php echo L_USERNAME ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printInput('name', $name, 'text', '20-255',false,'','','autofocus') ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_login"><?php echo L_LOGIN ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printInput('login', $login, 'text', '20-255') ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_pwd"><?php echo L_PASSWORD ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printInput('pwd', '', 'password', '20-255', false, '', '', 'onkeyup="pwdStrength(this.id, [\''.L_PWD_VERY_WEAK.'\', \''.L_PWD_WEAK.'\', \''.L_PWD_GOOD.'\', \''.L_PWD_STRONG.'\'])"') ?>
							<span id="id_pwd_strenght"></span>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_pwd2"><?php echo L_PASSWORD_CONFIRMATION ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printInput('pwd2', '', 'password', '20-255') ?>
						</div>
					</div>
					<div class="grid">
						<div class="col sml-12 med-5 label-centered">
							<label for="id_timezone"><?php echo L_TIMEZONE ?>&nbsp;:</label>
						</div>
						<div class="col sml-12 med-7">
							<?php plxUtils::printSelect('timezone', plxTimezones::timezones(), $timezone); ?>
						</div>
					</div>

					<input class="blue" type="submit" name="install" value="<?php echo L_INPUT_INSTALL ?>" />
					<?php echo plxToken::getTokenPostMethod() ?>

					<ul class="unstyled-list">
						<li><strong><?php echo L_PLUXML_VERSION; ?> <?php echo PLX_VERSION ?> (<?php echo L_INFO_CHARSET ?> <?php echo PLX_CHARSET ?>)</strong></li>
						<li><?php echo L_INFO_PHP_VERSION.' : '.phpversion() ?></li>
						<?php if (!empty($_SERVER['SERVER_SOFTWARE'])) { ?>
						<li><?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
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

<script src="<?php echo PLX_CORE ?>lib/visual.js"></script>

</body>

</html>
