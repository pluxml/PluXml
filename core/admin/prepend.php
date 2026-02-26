<?php
const PLX_ROOT = '../../';
const PLX_CORE = PLX_ROOT .'core/';

include PLX_ROOT.'config.php';
include PLX_CORE.'lib/config.php';

# On verifie que PluXml est installé
if(!file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Location: ' . PLX_ROOT . PLX_INSTALL_PATH);
	exit;
}

# On regarde si on est sur la page de login
$is_login = (basename($_SERVER['SCRIPT_NAME']) == PAGE_LOGIN);

# Si on n'est pas sur la page de login ou si on veut se déconnecter, on ouvre une session pour admin
if(($is_login and isset($_GET['d'])) or !empty($_COOKIE['PLX_ADMIN'])) {
	# On est sur la page de login
	$session_site['name'] = 'PLX_ADMIN';
}
session_start($session_site);

if($is_login and isset($_GET['d'])) {
	# Déconnexion
	$_SESSION = array();
	$name = session_name();  # normalement est égal à PLX_ADMIN
	session_destroy();
	# Effacement du cookie de session
	setcookie($name, '', 1);
	header('Location: ' . PAGE_LOGIN);
    exit;
}

# On vérifie qu'on n'est pas sur la page de login et qu'on est connecté
if(
	!$is_login and (
		!isset($_SESSION['domain']) or
		$_SESSION['domain'] != __DIR__ or
		!isset($_SESSION['user']) or
		trim($_SESSION['user']) == ''
	)
) {
	if(isset($_COOKIE[$session_admin['name']])) {
		# On supprime le cookie de session admin
		setcookie($session_admin['name'], '', 1);
	}
	header('Location: ' . PAGE_LOGIN . '?p=' . htmlentities($_SERVER['REQUEST_URI']));
	exit;
}

# On inclut les librairies nécessaires
include_once PLX_CORE.'lib/class.plx.date.php';
include_once PLX_CORE.'lib/class.plx.glob.php';
include_once PLX_CORE.'lib/class.plx.utils.php';
include_once PLX_CORE.'lib/class.plx.msg.php';
include_once PLX_CORE.'lib/class.plx.record.php';
include_once PLX_CORE.'lib/class.plx.plugins.php';
include_once PLX_CORE.'lib/class.plx.motor.php';
include_once PLX_CORE.'lib/class.plx.admin.php';

# Creation de l'objet principal et premier traitement
$plxAdmin = plxAdmin::getInstance();

# Détermination de la langue à utiliser (modifiable par le hook AdminPrepend)
$lang = $plxAdmin->aConf['default_lang'];

# Si désactivé ou supprimé par un admin, hors page de login. (!PLX_AUTHPAGE)
if(!$is_login) {
	$userId = $_SESSION['user'];
	if(empty($userId) or !isset($plxAdmin->aUsers[$userId]) or !$plxAdmin->aUsers[$userId]['active'] or $plxAdmin->aUsers[$userId]['delete']) {
		# Déconnecte l'utilisateur
		header('Location: ' . PAGE_LOGIN . '?d=1');
		exit;
	}

	$lang = $plxAdmin->aUsers[$userId]['lang'];
	# Force le profil d'utilisateur dès sa prochaine action, hors page de login. (!PLX_AUTHPAGE)
	$_SESSION['profil'] = $plxAdmin->aUsers[$userId]['profil'];
}

# Echappement des caractères
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$_POST = plxUtils::unSlash($_POST);
}

# On impose le charset
header('Content-Type: text/html; charset='.PLX_CHARSET);

include_once PLX_CORE.'lib/class.plx.encrypt.php';
include_once PLX_CORE.'lib/class.plx.medias.php';
include_once PLX_CORE.'lib/class.plx.token.php';

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminPrepend'));

# Chargement des fichiers de langue en fonction du profil de l'utilisateur connecté
loadLang(PLX_CORE.'lang/'.$lang.'/admin.php');
loadLang(PLX_CORE.'lang/'.$lang.'/core.php');

# on stocke la langue utilisée pour l'affichage de la zone d'administration en variable de session
# nb: la langue peut etre modifiée par le hook AdminPrepend via des plugins
if(!$is_login) {
	$_SESSION['admin_lang'] = $lang;
}
