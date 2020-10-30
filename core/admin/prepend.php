<?php
const PLX_ROOT = '../../';
include PLX_ROOT . 'core/lib/config.php';

# On vérifie que PHP 5 ou superieur soit installé
if(version_compare(PHP_VERSION, PHP_VERSION_MIN, '<')){
	header('Content-Type: text/plain charset=UTF-8');
	printf(L_WRONG_PHP_VERSION, PHP_VERSION_MIN);
	exit;
}

const SESSION_LIFETIME = 7200;

# use session_set_cookie_params() before session_start() - See https://www.php.net
$path1 = preg_replace('@/(core|plugins)/(.*)$@', '/', dirname($_SERVER['SCRIPT_NAME']));
if(version_compare(phpversion(), '7.3.1', '>=')) {
	session_set_cookie_params(array(
		'lifetime'	=> SESSION_LIFETIME,
		'path'		=> $path1,
		'domain'	=> $_SERVER['SERVER_NAME'],
		'secure'	=> isset($_SERVER["HTTPS"]),
		'httponly'	=> true,
		'samesite'	=> 'Strict',
	));
} else {
	# No support for samesite option
	session_set_cookie_params(SESSION_LIFETIME, $path1, $_SERVER['SERVER_NAME'], isset($_SERVER["HTTPS"]), true);
}
# On démarre la session
session_start();

$session_domain = __DIR__ ;

if(!defined('PLX_AUTHPAGE') OR PLX_AUTHPAGE !== true){ # si on n'est pas sur la page de login
	# Test sur le domaine et sur l'identification
	if(empty($_SESSION['domain']) OR $_SESSION['domain'] != $session_domain OR empty($_SESSION['user'])) {
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

if(defined('PLX_AUTHPAGE')) {
	$lang = $plxAdmin->aConf['default_lang'];
} else {
	# Si désactivé ou supprimé par un admin, hors page de login. (!PLX_AUTHPAGE)
	if(empty($_SESSION['user']) OR !$plxAdmin->aUsers[$_SESSION['user']]['active'] OR $plxAdmin->aUsers[$_SESSION['user']]['delete']) {
		header('Location: auth.php?d=1');# Déconnecte l'utilisateur a la prochaine demande,
		exit;
	}

	# Détermination de la langue à utiliser (modifiable par le hook AdminPrepend)
	$lang = $plxAdmin->aUsers[$_SESSION['user']]['lang'];

	# Change le Profil d'utilisateur dès sa prochaine action, hors page de login. (!PLX_AUTHPAGE)
	if(!array_key_exists('profil', $_SESSION) || $plxAdmin->aUsers[$_SESSION['user']]['profil'] != $_SESSION['profil'])
		$_SESSION['profil'] = $plxAdmin->aUsers[$_SESSION['user']]['profil'];
}


# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminPrepend'));

# Chargement des fichiers de langue en fonction du profil de l'utilisateur connecté
loadLang(PLX_CORE.'lang/'.$lang.'/admin.php');
loadLang(PLX_CORE.'lang/'.$lang.'/core.php');

# Tableau des profils
const PROFIL_NAMES = array(
	PROFIL_ADMIN		=> L_PROFIL_ADMIN,
	PROFIL_MANAGER		=> L_PROFIL_MANAGER,
	PROFIL_MODERATOR	=> L_PROFIL_MODERATOR,
	PROFIL_EDITOR		=> L_PROFIL_EDITOR,
	PROFIL_WRITER		=> L_PROFIL_WRITER
);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminPrepend'));

# on stocke la langue utilisée pour l'affichage de la zone d'administration en variable de session
# nb: la langue peut etre modifiée par le hook AdminPrepend via des plugins
$_SESSION['admin_lang'] = $lang;

# utilisé par top.php et index.php. Limite l'accès aux articles si besoin
$artsUserId = ($_SESSION['profil'] < PROFIL_EDITOR) ? '\d{3}' : $_SESSION['user'];
$isModerator = ($_SESSION['profil'] < PROFIL_EDITOR);

?>
