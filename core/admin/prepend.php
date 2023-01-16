<?php
const PLX_ROOT = '../../';
const PLX_CORE = PLX_ROOT . 'core/';

const SESSION_DOMAIN = __DIR__ ;

include PLX_CORE . 'lib/config.php';

# On démarre la session
plx_session_start();

if(!defined('PLX_AUTHPAGE') OR PLX_AUTHPAGE !== true){ # si on est pas sur la page de login
	# Test sur le domaine et sur l'identification
	if(empty($_SESSION['domain'] or $_SESSION['domain'] != SESSION_DOMAIN) or (!isset($_SESSION['user']) or $_SESSION['user']=='')){
		header('Location: auth.php?p='.htmlentities($_SERVER['REQUEST_URI']));
		exit;
	}
}

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

# Tableau des profils
const PROFIL_NAMES = array(
	PROFIL_ADMIN => L_PROFIL_ADMIN,
	PROFIL_MANAGER => L_PROFIL_MANAGER,
	PROFIL_MODERATOR => L_PROFIL_MODERATOR,
	PROFIL_EDITOR => L_PROFIL_EDITOR,
	PROFIL_WRITER => L_PROFIL_WRITER,
	PROFIL_SUBSCRIBER => L_PROFIL_SUBSCRIBER,
);

const ALLOW_COM_OPTIONS = [
	0 => L_NO,
	1 => L_EVERY_BODY,
	2 => L_SUBSCRIBERS_ONLY,
];

const ALLOW_COM_SUBSCRIBERS = [
	0 => L_NO,
	2 => L_SUBSCRIBERS_ONLY,
];
