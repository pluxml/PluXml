<?php
const PLX_ROOT = '../../';
const PLX_CORE = PLX_ROOT .'core/';

require PLX_CORE . 'lib/config.php';

const REDIRECT_ADMIN = array(
	'categorie'				=> 'categories.php',
	'comment_new'			=> 'comments.php',
	'parametres_edittpl'	=> 'parametres_affichage.php',
	'parametres_plugin'		=> 'parametres_plugins.php',
	'statique'				=> 'statiques.php',
	'user'					=> 'parametres_users.php',
);

# On regarde si on est sur la page de login
$is_login = (basename($_SERVER['SCRIPT_NAME']) == PAGE_LOGIN);

# Si on n'est pas sur la page de login ou si on veut se déconnecter, on ouvre une session pour admin
if(($is_login and isset($_GET['d'])) or !empty($_COOKIE['PLX_ADMIN'])) {
	# On est sur la page de login
	open_session('PLX_ADMIN');
} else {
	open_session();
}

function redirectLogin($query='') {
	$redirect = PAGE_LOGIN;
	if(!empty($query)) {
		$redirect .= '?' . $query;
	}
	header('Location: ' . $redirect );
	exit;
}

function redirectAdmin($context=true) {
	$location = 'index.php';
	if($context) {
		$scriptName = basename($_SERVER['SCRIPT_NAME'], '.php');
		if(array_key_exists($scriptName, REDIRECT_ADMIN)) {
			$location = REDIRECT_ADMIN[$scriptName];
		}
	}
	header('Location: ' . $location);
	exit;
}

if($is_login) {
	# on est sur la page de login.
	if(isset($_GET['d'])) {
		# Déconnexion
		if(isset($_SESSION['preview'])) {
			# On supprime les articles en "preview"
			foreach(glob($_SESSION['preview']) as $f) {
				unlink($f);
			}
		}
		close_session();
		redirectLogin();
	}
} else {
	# On vérifie qu'on est connecté
	if(
		!isset($_SESSION['domain']) or
		$_SESSION['domain'] != __DIR__ or
		!isset($_SESSION['user']) or
		preg_match('#^\d{3}$#', $_SESSION['user']) != 1
	) {
		# Authentification incorrecte
		close_session();
		redirectLogin('p=' . htmlentities($_SERVER['REQUEST_URI']));
	}
}

function reloadPage() {
	# header('Refresh: 0');
	$redirect = basename($_SERVER['SCRIPT_NAME']);
	if(!empty($_SERVER['QUERY_STRING'])) {
		$redirect .= '?' . $_SERVER['QUERY_STRING'];
	}
	header('Location: ' . $redirect);
	exit;
}

# Creation de l'objet principal et premier traitement
$plxAdmin = plxAdmin::getInstance();

# Détermination de la langue à utiliser (modifiable par le hook AdminPrepend)
$lang = $plxAdmin->aConf['default_lang'];

# Si désactivé ou supprimé par un admin, hors page de login. (!PLX_AUTHPAGE)
if(!$is_login) {
	$userId = $_SESSION['user'];
	if(empty($userId) or !isset($plxAdmin->aUsers[$userId]) or !$plxAdmin->aUsers[$userId]['active'] or $plxAdmin->aUsers[$userId]['delete']) {
		# Déconnecte l'utilisateur
		redirectLogin('d=1');
	}

	$lang = $plxAdmin->aUsers[$userId]['lang'];
	# Force le profil d'utilisateur dès sa prochaine action, hors page de login. (!PLX_AUTHPAGE)
	$_SESSION['profil'] = $plxAdmin->aUsers[$userId]['profil'];
}

# Echappement des caractères
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$_POST = plxUtils::unSlash($_POST);

	# Control du token du formulaire dans article.php
	# Les plugins valident le token pour leur config et leur admin
	if(
		!isset($_POST['preview']) and
		!in_array(basename($_SERVER['SCRIPT_NAME'], '.php'), array('parametres_plugin', 'plugin'))
	) {
		plxToken::validateFormToken($_POST);
	}
}

# On impose le charset
header('Content-Type: text/html; charset='.PLX_CHARSET);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminPrepend'));

# Chargement des fichiers de langue en fonction du profil de l'utilisateur connecté
loadLang(PLX_CORE.'lang/'.$lang.'/admin.php');
loadLang(PLX_CORE.'lang/'.$lang.'/core.php');

# on stocke la langue utilisée pour l'affichage de la zone d'administration en variable de session
# nb: la langue peut etre modifiée par le hook AdminPrepend via des plugins
if(!$is_login) {
	$_SESSION['admin_lang'] = $lang;
	if(preg_match('#^(parametres|user)#', basename($_SERVER['SCRIPT_NAME']))) {
		# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
		$plxAdmin->checkProfil(PROFIL_ADMIN);
	}
}
