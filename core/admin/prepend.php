<?php
include(preg_replace('#core/admin/$#', '', dirname(__FILE__).'/').'config.php');
include(PLX_ROOT.PLX_LIB.'config.php');

# On verifie que PluXml est installé
if(!file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Location: ../../install.php');
	exit;
}

# On démarre la session
session_start();

$session_domain = dirname(__FILE__);

if(!defined('PLX_AUTHPAGE') OR PLX_AUTHPAGE !== true){ # si on est pas sur la page de login
	# Test sur le domaine et sur l'identification
	if((isset($_SESSION['domain']) AND $_SESSION['domain']!=$session_domain) OR (!isset($_SESSION['user']) OR $_SESSION['user']=='')){
		header('Location: auth.php?p='.htmlentities($_SERVER['REQUEST_URI']));
		exit;
	}
}

# On inclut les librairies nécessaires
include_once(PLX_ROOT.PLX_LIB.'class.plx.date.php');
include_once(PLX_ROOT.PLX_LIB.'class.plx.glob.php');
include_once(PLX_ROOT.PLX_LIB.'class.plx.utils.php');
include_once(PLX_ROOT.PLX_LIB.'class.plx.msg.php');
include_once(PLX_ROOT.PLX_LIB.'class.plx.record.php');
include_once(PLX_ROOT.PLX_LIB.'class.plx.motor.php');
include_once(PLX_ROOT.PLX_LIB.'class.plx.admin.php');
include_once(PLX_ROOT.PLX_LIB.'class.plx.encrypt.php');
include_once(PLX_ROOT.PLX_LIB.'class.plx.medias.php');
include_once(PLX_ROOT.PLX_LIB.'class.plx.plugins.php');
include_once(PLX_ROOT.PLX_LIB.'class.plx.token.php');

# Echappement des caractères
if($_SERVER['REQUEST_METHOD'] == 'POST') $_POST = plxUtils::unSlash($_POST);

# On impose le charset
header('Content-Type: text/html; charset='.PLX_CHARSET);

# Creation de l'objet principal et premier traitement
$plxAdmin = plxAdmin::getInstance();

# Chargement des fichiers de langue en fonction du profil de l'utilisateur connecté
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : $plxAdmin->aConf['default_lang'];
loadLang(PLX_CORE.'lang/'.$lang.'/admin.php');

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminPrepend'));

?>