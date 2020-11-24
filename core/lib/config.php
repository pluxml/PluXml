<?php

if(!defined('PLX_ROOT')) { exit('Are you crazy ?'); }

const PHP_VERSION_MIN = '7.2.0';

const PLX_DEBUG = false;
const PLX_VERSION = '6.0.0-dev';
const PLX_URL_REPO = 'https://www.pluxml.org';
const PLX_URL_RESSOURCES = 'https://ressources.pluxml.org';
const PLX_RESSOURCES_LINK = '<a href="' . PLX_URL_RESSOURCES . '" target="_blank">' . PLX_URL_RESSOURCES . '</a>';
const PLX_URL_VERSION = PLX_URL_REPO.'/download/latest-version.txt';

# Chargement de PLX_CONFIG_PATH
include PLX_ROOT . 'config.php';

const PLX_CORE = PLX_ROOT . 'core/';
const PLX_ADMIN_PATH = PLX_CORE . 'admin/';

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

# On verifie que PluXml est installé
const SCRIPT_INSTALL = 'install.php';
if(strtolower(basename($_SERVER['SCRIPT_NAME'], '')) != SCRIPT_INSTALL and !file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Location: ' . PLX_ROOT . SCRIPT_INSTALL);
	exit;
}

# Définition de l'encodage => PLX_CHARSET : UTF-8 (conseillé) ou ISO-8859-1
const PLX_CHARSET = 'UTF-8';

# Entête de tous les fichiers .xml
const XML_HEADER = '<?xml version="1.0" encoding="' . PLX_CHARSET . '" ?>' . PHP_EOL;

# Langue par défaut
const DEFAULT_LANG = 'en';

# profils utilisateurs de pluxml. Look at ../top.php for more information
const PROFIL_ADMIN		= 0; // all grants
const PROFIL_MANAGER	= 1; // grants for statiques, comments, categories, articles,
const PROFIL_MODERATOR	= 2; // grants for comments, categories
const PROFIL_EDITOR		= 3; // grants for categories
const PROFIL_WRITER		= 4; // grants only for editing his own articles and managing his own medias if checked in parameters.xml
// For plugins, grants are setting by plxPlugin::setConfigProfil and plxPlugin::setAdminProfil

# taille redimensionnement des images et miniatures
const IMG_REDIM = array('320x200', '500x380', '640x480');
const IMG_THUMB = array('50x50', '75x75', '100x100');

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

# fonction de chargement d'un fichier de langue
function loadLang($filename) {
	if(file_exists($filename)) {
		include_once $filename;

		# Compatibilité avec anciennes versions de PluXml. Deprecated !
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

# Auto-chargement des librairies de classes de PluXml.
# Le nom de la class doit commencer par plx, suivi d'une lettre majuscule.
# Exception avec PlxTemplate
spl_autoload_register(function($aClass) {
	# plxMotor => PLX_CORE . 'lib/class.plx.motor.php'
	return preg_match('@^[pP]lx([A-Z]\w+)$@', $aClass, $matches) and include_once PLX_CORE . 'lib/class.plx.' . strtolower($matches[1]) . '.php';
});
