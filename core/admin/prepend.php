<?php
const PLX_ROOT = '../../';
const PLX_CORE = PLX_ROOT .'core/';
const SESSION_LIFETIME = 7200;

include PLX_ROOT.'config.php';
include PLX_CORE.'lib/config.php';

# On verifie que PluXml est installé
if(!file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Location: '.PLX_ROOT.'install.php');
	exit;
}

# On démarre la session
session_start();
setcookie(session_name(),session_id(),time()+SESSION_LIFETIME, "/", $_SERVER['SERVER_NAME'], isset($_SERVER["HTTPS"]), true);

$session_domain = __DIR__ ;

if(!defined('PLX_AUTHPAGE') OR PLX_AUTHPAGE !== true){ # si on est pas sur la page de login
	# Test sur le domaine et sur l'identification
	if((isset($_SESSION['domain']) AND $_SESSION['domain']!=$session_domain) OR (!isset($_SESSION['user']) OR $_SESSION['user']=='')){
		header('Location: auth.php?p='.htmlentities($_SERVER['REQUEST_URI']));
		exit;
	}
}

# On inclut les librairies nécessaires
include_once PLX_CORE.'lib/class.plx.date.php';
include_once PLX_CORE.'lib/class.plx.glob.php';
include_once PLX_CORE.'lib/class.plx.utils.php';
include_once PLX_CORE.'lib/class.plx.msg.php';
include_once PLX_CORE.'lib/class.plx.record.php';
include_once PLX_CORE.'lib/class.plx.motor.php';
include_once PLX_CORE.'lib/class.plx.admin.php';
include_once PLX_CORE.'lib/class.plx.encrypt.php';
include_once PLX_CORE.'lib/class.plx.medias.php';
include_once PLX_CORE.'lib/class.plx.plugins.php';
include_once PLX_CORE.'lib/class.plx.token.php';

# Echappement des caractères
if($_SERVER['REQUEST_METHOD'] == 'POST') $_POST = plxUtils::unSlash($_POST);

# On impose le charset
header('Content-Type: text/html; charset='.PLX_CHARSET);

# Creation de l'objet principal et premier traitement
$plxAdmin = plxAdmin::getInstance();

# Détermination de la langue à utiliser (modifiable par le hook AdminPrepend)
$lang = $plxAdmin->aConf['default_lang'];
if(isset($_SESSION['user'])) {
	$lang = $plxAdmin->aUsers[$_SESSION['user']]['lang'];
	# Si désactivé ou supprimé par un admin, hors page de login. (!PLX_AUTHPAGE)
	if(!$plxAdmin->aUsers[$_SESSION['user']]['active'] OR $plxAdmin->aUsers[$_SESSION['user']]['delete']){
		header('Location: auth.php?d=1');# Déconnecte l'utilisateur a la prochaine demande,
		exit;
	}
	# Change le Profil d'utilisateur dès sa prochaine action, hors page de login. (!PLX_AUTHPAGE)
	if(!isset($_SESSION['profil']) || $plxAdmin->aUsers[$_SESSION['user']]['profil'] != $_SESSION['profil'])
		$_SESSION['profil'] = $plxAdmin->aUsers[$_SESSION['user']]['profil'];
}
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminPrepend'));

# Chargement des fichiers de langue en fonction du profil de l'utilisateur connecté
loadLang(PLX_CORE.'lang/'.$lang.'/admin.php');
loadLang(PLX_CORE.'lang/'.$lang.'/core.php');

# on stocke la langue utilisée pour l'affichage de la zone d'administration en variable de session
# nb: la langue peut etre modifiée par le hook AdminPrepend via des plugins
$_SESSION['admin_lang'] = $lang;

?>
