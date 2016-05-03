<?php
define('PLX_DEBUG', false);
define('PLX_VERSION', '5.5');

# Gestion des erreurs PHP
if(PLX_DEBUG) error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

# Fonction qui retourne le timestamp UNIX actuel avec les microsecondes
function getMicrotime() {
	$t = explode(' ',microtime());
	return $t[0]+$t[1];
}

# Initialisation du timer d'execution
define('PLX_MICROTIME', getMicrotime());

$CONSTS = array(
	'XMLFILE_PARAMETERS' 	=> PLX_ROOT.PLX_CONFIG_PATH.'parametres.xml',
	'XMLFILE_CATEGORIES' 	=> PLX_ROOT.PLX_CONFIG_PATH.'categories.xml',
	'XMLFILE_STATICS' 		=> PLX_ROOT.PLX_CONFIG_PATH.'statiques.xml',
	'XMLFILE_USERS' 		=> PLX_ROOT.PLX_CONFIG_PATH.'users.xml',
	'XMLFILE_PLUGINS' 		=> PLX_ROOT.PLX_CONFIG_PATH.'plugins.xml',
	'XMLFILE_TAGS' 			=> PLX_ROOT.PLX_CONFIG_PATH.'tags.xml',
);

# Définition de l'encodage => PLX_CHARSET : UTF-8 (conseillé) ou ISO-8859-1
define('PLX_CHARSET', 'UTF-8');

# Langue par défaut
define('DEFAULT_LANG', 'fr');

# profils utilisateurs de pluxml
define('PROFIL_ADMIN', 0);
define('PROFIL_MANAGER', 1);
define('PROFIL_MODERATOR', 2);
define('PROFIL_EDITOR', 3);
define('PROFIL_WRITER', 4);

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
		include_once($filename);
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
?>