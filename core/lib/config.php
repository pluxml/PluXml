<?php
const PLX_DEBUG = false;
const PLX_VERSION = '5.10.0';
const PLX_URL_REPO = 'https://www.pluxml.org';
const PLX_URL_VERSION = PLX_URL_REPO.'/download/latest-version.txt';
const PLX_URL_LAST_RELEASE_GITHUB = 'https://api.github.com/repos/pluxml/PluXml/releases/latest';

const SESSION_LIFETIME = 7200;
const PAGE_LOGIN = 'auth.php';

const PLX_INSTALL_PATH = 'install/index.php';

# Gestion des erreurs PHP
if(PLX_DEBUG) error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

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
const XML_HEADER = '<?xml version="1.0" encoding="' . PLX_CHARSET .'"?>' . PHP_EOL;

# Langue par défaut
const DEFAULT_LANG = 'en';

# profils utilisateurs de pluxml
const PROFIL_ADMIN = 0;
const PROFIL_MANAGER = 1;
const PROFIL_MODERATOR	= 2;
const PROFIL_EDITOR	= 3;
const PROFIL_WRITER	= 4;
#  Flag Sequences :
# 🇦 🇧 🇨 🇩 🇪 🇫 🇬 🇭 🇮 🇯 🇰 🇱 🇲 🇳 🇴 🇵 🇶 🇷 🇸 🇹 🇺 🇻 🇼 🇽 🇾 🇿
const FLAGS = array(
	'de' => '🇩🇪',
	'en' => '🇬🇧', # GB
	'es' => '🇪🇸',
	'fr' => '🇫🇷',
	'gr' => '🇬🇷',
	'it' => '🇮🇹',
	'nl' => '🇳🇱',
	'oc' => '🇫🇷',
	'pl' => '🇵🇱',
	'pt' => '🇵🇹',
	'ro' => '🇷🇴',
	'ru' => '🇷🇺',
	'ua' => '🇺🇦',
);

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

# fonction qui retourne le chemin de la racine du site avec ou sans redirection d'url
function get_root_url() {
	$path1 = preg_replace('#/(?:\w[\w-]+\.php)$#', '', !empty($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['PHP_SELF']) . '/';
	$level = count(explode('../', PLX_ROOT)) - 1;
	if($level > 0) {
		$path1 = preg_replace('#(?:\w[\w-]+/){' . $level . '}$#', '', $path1);
	}
	return $path1;
}

$session_site = array(
	'name'				=> 'PLX_SITE',
	'cookie_lifetime'	=> SESSION_LIFETIME,
	'cookie_samesite'	=> 'Strict',
	'cookie_secure'		=> isset($_SERVER['HTTPS']),
	'cookie_httponly'	=> true,
	'cookie_path'		=> !empty($_SERVER['REDIRECT_URL']) ? preg_replace('#(?:/|/\w[\w-]+\w\.(?:php|html?))$#', '', $_SERVER['REDIRECT_URL']) : dirname($_SERVER['PHP_SELF']),
	'use_strict_mode'	=> true,
	'cookie_domain'		=> $_SERVER['SERVER_NAME'],
);

