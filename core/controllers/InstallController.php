<?php

/**
 * PluXml Installation
 * @package PLX
 * @author	Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

namespace controllers;

class InstallController extends AdminController {

    public function __construct() {

        // This page don't need user authentification
        $this->setAuthPage(true);

        parent::__construct();
    }

    /**
     * Index action default view call
     * @author Pedro "P3ter" CADETE
     */
    public function indexAction() {
        $plxAdmin = $this->getPlxAdmin();
        $plxUtils = $this->getPlxUtils();
        $plxToken = $this->getPlxToken();
        $plxLayoutDir = $this->getPlxAdmin()->getViewsLayoutDir();
        $lang = $this->getPlxMotor()->getCoreLang();
        $charset = $this->getConfig()->getConfigIni('PLX_CHARSET');
        $version = $plxAdmin->getPlxConfig()->getConfigIni('PLX_VERSION');

        # Display the view
        $this->getPlxUtils()->cleanHeaders();
        require_once $this->getPlxMotor()->getViewsScriptsDir() . 'installView.php';
    }
}

/*
const PLX_ROOT = './';
const PLX_CORE = PLX_ROOT .'core/';

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

# Chargement des langues
$lang = DEFAULT_LANG;
if(isset($_POST['default_lang'])) $lang=$_POST['default_lang'];
if(!array_key_exists($lang, plxUtils::getLangs())) {
	$lang = DEFAULT_LANG;
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

# Vérification de l'existence des dossiers médias
if(!is_dir(PLX_ROOT.'data/medias')) {
	@mkdir(PLX_ROOT.'data/medias',0755,true);
}

# Vérification de l'existence du dossier data/configuration/plugins
if(!is_dir(PLX_ROOT.PLX_CONFIG_PATH.'plugins')) {
	@mkdir(PLX_ROOT.PLX_CONFIG_PATH.'plugins',0755,true);
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
	$xml .= "\t\t".'<email><![CDATA['.trim($content['email']).']]></email>'."\n";
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
	elseif(trim($_POST['email']=='')) $msg = L_ERR_MISSING_EMAIL;
	else {
		install($_POST, $config);
		header('Location: '.plxUtils::getRacine());
		exit;
	}
	$name=$_POST['name'];
	$login=$_POST['login'];
	$email=$_POST['email'];
}
else {
	$name='';
	$login='';
	$email='';
}
plxUtils::cleanHeaders();
?>

*/