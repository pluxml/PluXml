<?php
define('PLX_ROOT', './');
define('PLX_CORE', PLX_ROOT.'core/');
include(PLX_ROOT.'config.php');
include(PLX_CORE.'lib/config.php');

# On démarre la session
session_start();

# On inclut les librairies nécessaires
include(PLX_CORE.'lib/class.plx.timezones.php');
include(PLX_CORE.'lib/class.plx.date.php');
include(PLX_CORE.'lib/class.plx.glob.php');
include(PLX_CORE.'lib/class.plx.utils.php');
include(PLX_CORE.'lib/class.plx.token.php');

# Chargement des langues et initialisation du timezone
if(
	isset($_POST['default_lang']) and
	array_key_exists($_POST['default_lang'], plxUtils::getLangs())
) {
	$lang=$_POST['default_lang'];
	$timezone = plxTimezones::get_timezone($lang);
} else {
	$lang = DEFAULT_LANG;
	if(
		isset($_POST['timezone']) &&
		array_key_exists($_POST['timezone'], plxTimezones::timezones())
	) {
		$timezone=$_POST['timezone'];
	} else {
		$timezone = plxTimezones::get_timezone($lang);
	}
}

loadLang(PLX_CORE.'lang/'.$lang.'/install.php');
loadLang(PLX_CORE.'lang/'.$lang.'/core.php');

# On vérifie que PHP 5 ou superieur soit installé
if(version_compare(PHP_VERSION, '5.0.0', '<')){
	header('Content-Type: text/plain charset=UTF-8');
	echo utf8_decode(L_WRONG_PHP_VERSION);
	exit;
}

# On vérifie que PluXml n'est pas déjà installé
if(file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Content-Type: text/plain charset=UTF-8');
	echo utf8_decode(L_ERR_PLUXML_ALREADY_INSTALLED);
	exit;
}

# Control du token du formulaire
plxToken::validateFormToken($_POST);

const HTACCESS_CONTENT = "<Files *>\n\tOrder allow,deny\n\tDeny from all\n</Files>";

# Vérification de l'existence de quelques dossiers
foreach(explode(' ', 'articles commentaires configuration medias statiques') as $fd) {
	$folder = PLX_ROOT.'data/'.$fd;
	if(!is_dir($folder)) {
		@mkdir($folder, 0755, true);
	}
	if($fd != 'medias') {
		plxUtils::write('', $folder.'/index.html');
		plxUtils::write(HTACCESS_CONTENT, $folder.'/.htaccess');
	}
}

# Vérification de la présence du fichier data/.htaccess
$htaccess = PLX_ROOT.'data/.htaccess';
if(!file_exists($htaccess)) {
	plxUtils::write('options -indexes', $htaccess);
}

# Vérification de l'existence du dossier data/configuration/plugins
if(!is_dir(PLX_ROOT.PLX_CONFIG_PATH.'plugins')) {
	@mkdir(PLX_ROOT.PLX_CONFIG_PATH.'plugins',0755,true);
}

# Echappement des caractères
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$_POST = plxUtils::unSlash($_POST);
}

# Configuration de base
$config = array('title'=>'PluXml',
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
				'medias'=>'data/medias/',
				'racine_articles'=>'data/articles/',
				'racine_commentaires'=>'data/commentaires/',
				'racine_statiques'=>'data/statiques/',
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

	# Création du fichier de configuration
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>'."\n";
	foreach($config  as $k=>$v) {
		if(is_numeric($v))
			$xml .= "\t<parametre name=\"$k\">".$v."</parametre>\n";
		else
			$xml .= "\t<parametre name=\"$k\"><![CDATA[".plxUtils::cdataCheck($v)."]]></parametre>\n";
	}
	$xml .= '</document>';
	plxUtils::write($xml,path('XMLFILE_PARAMETERS'));

	# Création du fichier des utilisateurs
	$salt = plxUtils::charAleatoire(10);
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= "<document>\n";
	$xml .= "\t".'<user number="001" active="1" profil="0" delete="0">'."\n";
	$xml .= "\t\t".'<login><![CDATA['.trim($content['login']).']]></login>'."\n";
	$xml .= "\t\t".'<name><![CDATA['.trim($content['name']).']]></name>'."\n";
	$xml .= "\t\t".'<infos><![CDATA[]]></infos>'."\n";
	$xml .= "\t\t".'<password><![CDATA['.sha1($salt.md5(trim($content['pwd']))).']]></password>'."\n";
	$xml .= "\t\t".'<salt><![CDATA['.$salt.']]></salt>'."\n";
	$xml .= "\t\t".'<email><![CDATA[]]></email>'."\n";
	$xml .= "\t\t".'<lang><![CDATA['.$config['default_lang'].']]></lang>'."\n";
	$xml .= "\t</user>\n";
	$xml .= "</document>";
	plxUtils::write($xml,path('XMLFILE_USERS'));

	# Création du fichier des categories
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>'."\n";
	$xml .= "\t".'<categorie number="001" active="1" homepage="1" tri="'.$config['tri'].'" bypage="'.$config['bypage'].'" menu="oui" url="'.L_DEFAULT_CATEGORY_URL.'" template="categorie.php"><name><![CDATA['.plxUtils::strRevCheck(L_DEFAULT_CATEGORY_TITLE).']]></name><description><![CDATA[]]></description><meta_description><![CDATA[]]></meta_description><meta_keywords><![CDATA[]]></meta_keywords><title_htmltag><![CDATA[]]></title_htmltag></categorie>'."\n";
	$xml .= '</document>';
	plxUtils::write($xml,path('XMLFILE_CATEGORIES'));

	# Création du fichier des pages statiques
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>'."\n";
	$xml .= "\t".'<statique number="001" active="1" menu="oui" url="'.L_DEFAULT_STATIC_URL.'" template="static.php"><group><![CDATA[]]></group><name><![CDATA['.plxUtils::strRevCheck(L_DEFAULT_STATIC_TITLE).']]></name><meta_description><![CDATA[]]></meta_description><meta_keywords><![CDATA[]]></meta_keywords><title_htmltag><![CDATA[]]></title_htmltag><date_creation><![CDATA['.date('YmdHi').']]></date_creation><date_update><![CDATA['.date('YmdHi').']]></date_update></statique>'."\n";
	$xml .= '</document>';
	plxUtils::write($xml,path('XMLFILE_STATICS'));
	plxUtils::write(file_get_contents(PLX_CORE.'/lib/html.static.txt'),PLX_ROOT.$config['racine_statiques'].'001.'.L_DEFAULT_STATIC_URL.'.php');

	# Création du premier article
	$html = explode('-----', file_get_contents(PLX_CORE.'/lib/html.article.txt'));
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>
	<title><![CDATA['.plxUtils::strRevCheck(L_DEFAULT_ARTICLE_TITLE).']]></title>
	<allow_com>1</allow_com>
	<template><![CDATA[article.php]]></template>
	<chapo><![CDATA['.$html[0].']]></chapo>
	<content><![CDATA['.$html[1].']]></content>
	<tags><![CDATA[PluXml]]></tags>
	<meta_description><![CDATA[]]></meta_description>
	<meta_keywords><![CDATA[]]></meta_keywords>
	<title_htmltag><![CDATA[]]></title_htmltag>
	<date_creation><![CDATA['.date('YmdHi').']]></date_creation>
	<date_update><![CDATA['.date('YmdHi').']]></date_update>
	<thumbnail><![CDATA[core/admin/theme/images/pluxml.png]]></thumbnail>
</document>';
	plxUtils::write($xml,PLX_ROOT.$config['racine_articles'].'0001.001.001.'.date('YmdHi').'.'.L_DEFAULT_ARTICLE_URL.'.xml');

	# Création du fichier des tags servant de cache
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>'."\n";
	$xml .= "\t".'<article number="0001" date="'.date('YmdHi').'" active="1"><![CDATA[PluXml]]></article>'."\n";
	$xml .= '</document>';
	plxUtils::write($xml,path('XMLFILE_TAGS'));

	# Création du fichier des plugins
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>'."\n";
	$xml .= '</document>';
	plxUtils::write($xml,path('XMLFILE_PLUGINS'));

	# Création du premier commentaire
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<comment>
	<author><![CDATA[pluxml]]></author>
		<type>normal</type>
		<ip>127.0.0.1</ip>
		<mail><![CDATA[contact@pluxml.org]]></mail>
		<site><![CDATA[http://www.pluxml.org]]></site>
		<content><![CDATA['.plxUtils::strRevCheck(L_DEFAULT_COMMENT_CONTENT).']]></content>
	</comment>';
	plxUtils::write($xml,PLX_ROOT.$config['racine_commentaires'].'0001.'.date('U').'-1.xml');

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
	<script src="<?php echo PLX_CORE ?>lib/visual.js"></script>
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
							<label for="id_default_lang">
								<?php echo L_SELECT_LANG ?>&nbsp;:
								<br />
<?php
/* Google translations, except french */
	$select_langs = array(
		'de' => 'Stellen Sie diese Seite auf Deutsch',
		'en' => 'Set this site in English',
		'es' => 'Utilice este sitio en español',
		'fr' => 'Régler le site en français',
		'it' => 'Utilizar este sitio en italiano',
		'nl' => 'Gebruik deze site in het Nederlands',
		'oc' => 'Seleccionatz vòstra lenga',
		'pl' => 'Użyj tej strony w języku polskim',
		'pt' => 'Use este site em português',
		'ro' => 'Folosiți acest site în limba română',
		'ru' => 'Использовать этот сайт на русском языке'
	);
	foreach(plxUtils::getLangs() as $lg) {
		$title = (array_key_exists($lang, $select_langs)) ? $select_langs[$lg] : '';
		$active = ($lg == $lang) ? ' class="active"' : '';
		echo <<< EOT
<button$active title="$title" data-lang="$lg">&nbsp;</button>

EOT;
	}
?>
							</label>
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
						<?php plxUtils::testModReWrite() ?>
						<?php plxUtils::testLibGD() ?>
						<?php plxUtils::testLibXml() ?>
						<?php plxUtils::testMail() ?>
					</ul>

				</fieldset>

			</form>

		</section>

	</main>
	<script type="text/javascript">
		(function() {
			'use strict';

			function changeLang(event) {
				if(event.target.tagName == 'BUTTON' && event.target.hasAttribute('data-lang')) {
					event.preventDefault();
					const select = document.querySelector('select[name="default_lang"]');
					if(select != null) {
						select.value = event.target.getAttribute('data-lang');
						select.form.submit();
					}
				}
			}

			const node = document.querySelector('label[for="id_default_lang"]');
			if(node != null) {
				node.addEventListener('click', changeLang);
			}

		})();
	</script>

</body>

</html>