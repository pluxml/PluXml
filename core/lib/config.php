<?php
const PLX_DEBUG = false;
const PLX_VERSION = '5.8.3';
const PLX_URL_REPO = 'https://www.pluxml.org';
const PLX_URL_RESSOURCES = 'https://ressources.pluxml.org';
const PHP_VERSION_MIN = '5.5.0';
define('PLX_URL_VERSION', PLX_URL_REPO . '/download/latest-version.txt');#legacy PHP<5.6
define('PLX_TEMPLATES', PLX_CORE . 'templates/');#fix plxMotor class const with php5.5 : Parse error: syntax error, unexpected '.', expecting ',' or ';'
define('PLX_TEMPLATES_DATA', PLX_ROOT . 'data/templates/');#if in const have concatenated values

# Gestion des erreurs PHP
if(PLX_DEBUG){
	error_reporting(E_ALL);
}
else {
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
}

# Fonction qui retourne le timestamp UNIX actuel avec les microsecondes
function getMicrotime() {
	$t = explode(' ',microtime());
	return $t[0]+$t[1];
}

# Initialisation du timer d'execution
define('PLX_MICROTIME', getMicrotime());

$CONSTS = array(
	'XMLFILE_PARAMETERS'	=> PLX_ROOT.PLX_CONFIG_PATH.'parametres.xml',
	'XMLFILE_CATEGORIES'	=> PLX_ROOT.PLX_CONFIG_PATH.'categories.xml',
	'XMLFILE_STATICS'		=> PLX_ROOT.PLX_CONFIG_PATH.'statiques.xml',
	'XMLFILE_USERS'			=> PLX_ROOT.PLX_CONFIG_PATH.'users.xml',
	'XMLFILE_PLUGINS'		=> PLX_ROOT.PLX_CONFIG_PATH.'plugins.xml',
	'XMLFILE_TAGS'			=> PLX_ROOT.PLX_CONFIG_PATH.'tags.xml',
);

# Définition de l'encodage => PLX_CHARSET : UTF-8 (conseillé) ou ISO-8859-1
const PLX_CHARSET = 'UTF-8';

# Entête de tous les fichiers .xml
define('XML_HEADER', '<?xml version="1.0" encoding="' . PLX_CHARSET . '" ?>' . PHP_EOL);#legacy PHP<5.6

# Langue par défaut
const DEFAULT_LANG = 'en';

# profils utilisateurs de pluxml. Look at core/admin/top.php for more information
const PROFIL_ADMIN		= 0; # all grants
const PROFIL_MANAGER	= 1; # grants for statiques, comments, categories, articles,
const PROFIL_MODERATOR	= 2; # grants for comments, categories
const PROFIL_EDITOR		= 3; # grants for categories
const PROFIL_WRITER		= 4; # grants only for editing his own articles and managing his own medias if checked in parameters.xml
# For plugins, grants are setting by plxPlugin::setConfigProfil and plxPlugin::setAdminProfil

# taille redimensionnement des images et miniatures (utilisé ds media.php)
$img_redim = array('320x200', '500x380', '640x480');
$img_thumb = array('50x50', '75x75', '100x100');

# On sécurise notre environnement si dans php.ini: register_globals = On
if (ini_get('register_globals')) {
	$array = array('_REQUEST', '_SESSION', '_SERVER', '_ENV', '_FILES');
	foreach ($array as $value) {
		if(isset($GLOBALS[$value]))  {
			foreach ($GLOBALS[$value] as $key => $var) {
				if (isset($GLOBALS[$key]) AND $var === $GLOBALS[$key]) {
					unset($GLOBALS[$key]);
				}
			}
		}
	}
}

# fonction de chargement d'un fichier de langue (des plugins)
function loadLang($filename) {
	if(file_exists($filename)) {
		include_once $filename;
		if(!empty($LANG)) {
			foreach($LANG as $key => $value) {
				if(!defined($key)) define($key,$value);
			}
		}
	}
}

# fonction qui retourne ou change le chemin des fichiers xml de configuration
function path($s, $newvalue='') {
	global $CONSTS;
	if(!empty($newvalue))
		$CONSTS[$s]=$newvalue;
	if(isset($CONSTS[$s]))
		return $CONSTS[$s];
}
