<?php

if(!defined('PLX_ROOT')) {
	header('Content-Type: text/plain; charset=utf-8');
	die('Unknown PLX_ROOT constant');
}

const PHP_VERSION_MIN = '5.6.34';
const PLX_DEBUG = true;
const PLX_VERSION = '5.8.9';
const PLX_VERSION_DATA = '5.8.1';
const PLX_URL_REPO = 'https://www.pluxml.org';
const PLX_URL_VERSION = PLX_URL_REPO.'/download/latest-version.txt';

# Gestion des erreurs PHP
if(PLX_DEBUG) error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

# Fonction qui retourne le timestamp UNIX actuel avec les microsecondes
function getMicrotime() {
	$t = explode(' ',microtime());
	return $t[0]+$t[1];
}

# Initialisation du timer d'execution
define('PLX_MICROTIME', getMicrotime());

include PLX_ROOT . 'config.php';

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

# Langue par défaut
const DEFAULT_LANG = 'en';

# profils utilisateurs de pluxml
const PROFIL_ADMIN = 0;
const PROFIL_MANAGER = 1;
const PROFIL_MODERATOR	= 2;
const PROFIL_EDITOR	= 3;
const PROFIL_WRITER	= 4;

# taille redimensionnement des images et miniatures
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

# fonction de chargement d'un fichier de langue
function loadLang($filename) {
	if(file_exists($filename)) {
		$LANG = array();
		include_once $filename;
		foreach($LANG as $key => $value) {
			if(!defined($key)) define($key,$value);
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

/*
 * Auto-chargement des librairies de classes de PluXml.
 * Le nom de la class doit commencer par plx, suivi d'une lettre majuscule.
 * Exception avec PlxTemplate
 * */
spl_autoload_register(
	function($aClass) {
       # plxMotor => PLX_CORE . 'lib/class.plx.motor.php'
       return preg_match('@^[pP]lx([A-Z]\w+)$@', $aClass, $matches) and include_once __DIR__ . '/class.plx.' . strtolower($matches[1]) . '.php';
	},
	true,
	true # PluXml first !
);

