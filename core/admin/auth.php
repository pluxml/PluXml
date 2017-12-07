<?php

/**
 * Page d'authentification
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

# Variable pour retrouver la page d'authentification
define('PLX_AUTHPAGE', true);

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Protection anti brute force
$maxlogin['counter'] = 3; # nombre de tentative de connexion autorisé dans la limite de temps autorisé
$maxlogin['timer'] = 3 * 60; # temps d'attente limite si nombre de tentative de connexion atteint (en minutes)

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminAuthPrepend'));

# Initialisation variable erreur
$error = '';
$msg = '';

if(isset($_SESSION['maxtry'])) {
	if( intval($_SESSION['maxtry']['counter']) >= $maxlogin['counter'] AND (time() < $_SESSION['maxtry']['timer'] + $maxlogin['timer']) ) {
		# écriture dans les logs du dépassement des 3 tentatives successives de connexion
		@error_log("PluXml: Max login failed. IP : ".plxUtils::getIp());
		# message à affiche sur le mire de connexion
		$msg = sprintf(L_ERR_MAXLOGIN, ($maxlogin['timer']/60));
		$error = 'error';
	}
	if( time() > ($_SESSION['maxtry']['timer'] + $maxlogin['timer']) ) {
		# on réinitialise le control brute force quand le temps d'attente limite est atteint
		$_SESSION['maxtry']['counter'] = 0;
		$_SESSION['maxtry']['timer'] = time();
	}
} else {
	# initialisation de la variable qui compte les tentatives de connexion
	$_SESSION['maxtry']['counter'] = 0;
	$_SESSION['maxtry']['timer'] = time();
}

# Control et filtrage du parametre $_GET['p']
$redirect=$plxAdmin->aConf['racine'].'core/admin/';
if(!empty($_GET['p']) AND $error=='') {

	# on incremente la variable de session qui compte les tentatives de connexion
	$_SESSION['maxtry']['counter']++;

	$racine = parse_url($plxAdmin->aConf['racine']);
	$get_p = parse_url(urldecode($_GET['p']));
	$error = (!$get_p OR (isset($get_p['host']) AND $racine['host']!=$get_p['host']));
	if(!$error AND !empty($get_p['path']) AND file_exists(PLX_ROOT.'core/admin/'.basename($get_p['path']))) {
		# filtrage des parametres de l'url
		$query='';
		if(isset($get_p['query'])) {
			$query=strtok($get_p['query'],'=');
			$query=($query[0]!='d'?'?'.$get_p['query']:'');
		}
		# url de redirection
		$redirect=$get_p['path'].$query;
	}
}

# Déconnexion
if(!empty($_GET['d']) AND $_GET['d']==1) {

	$_SESSION = array();
	session_destroy();
	header('Location: auth.php');
	exit;

	$formtoken = $_SESSION['formtoken']; # sauvegarde du token du formulaire
	$_SESSION = array();
	session_destroy();
	session_start();
	$msg = L_LOGOUT_SUCCESSFUL;
	$_GET['p']='';
	$_SESSION['formtoken']=$formtoken; # restauration du token du formulaire
	unset($formtoken);
}

# Authentification
if(!empty($_POST['login']) AND !empty($_POST['password']) AND $error=='') {

	$connected = false;
	foreach($plxAdmin->aUsers as $userid => $user) {
		if ($_POST['login']==$user['login'] AND sha1($user['salt'].md5($_POST['password']))===$user['password'] AND $user['active'] AND !$user['delete']) {
			$_SESSION['user'] = $userid;
			$_SESSION['profil'] = $user['profil'];
			$_SESSION['hash'] = plxUtils::charAleatoire(10);
			$_SESSION['domain'] = $session_domain;
			# on définit $_SESSION['admin_lang'] pour stocker la langue à utiliser la 1ere fois dans le chargement des plugins une fois connecté à l'admin
			# ordre des traitements:
			# page administration : chargement fichier prepend.php
			# => creation instance plxAdmin : chargement des plugins, chargement des prefs utilisateurs
			# => chargement des langues en fonction du profil de l'utilisateur connecté déterminé précédemment
			$_SESSION['admin_lang'] = $user['lang'];
			$connected = true;
			break;
		}
	}
	if($connected) {
		unset($_SESSION['maxtry']);
		header('Location: '.htmlentities($redirect));
		exit;
	} else {
		$msg = L_ERR_WRONG_PASSWORD;
		$error = 'error';
	}
}
plxUtils::cleanHeaders();
?>
<!DOCTYPE html>
<html lang="<?php echo $plxAdmin->aConf['default_lang'] ?>">
<head>
	<meta name="robots" content="noindex, nofollow" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title>PluXml - <?php echo L_AUTH_PAGE_TITLE ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/plucss.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/theme.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/fonts/fontello.css" media="screen" />
	<?php if(is_file(PLX_ROOT.$plxAdmin->aConf['custom_admincss_file'])) echo '<link rel="stylesheet" type="text/css" href="'.PLX_ROOT.$plxAdmin->aConf['custom_admincss_file'].'" media="screen" />'."\n" ?>
	<?php
	if(file_exists(PLX_ROOT.$plxAdmin->aConf['racine_plugins'].'admin.css'))
		echo '<link rel="stylesheet" type="text/css" href="'.PLX_ROOT.$plxAdmin->aConf['racine_plugins'].'admin.css" media="screen" />'."\n";
	?>
	<link rel="icon" href="<?php echo PLX_CORE ?>admin/theme/images/favicon.png" />
	<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthEndHead')) ?>
</head>

<body id="auth">

	<main class="container">
		<section class="grid">
			<div class="logo"></div>
			<div class="auth col sml-12 sml-centered med-5 lrg-3">
				<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTop')) ?>
				<form action="auth.php<?php echo !empty($redirect)?'?p='.plxUtils::strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
					<fieldset>
						<?php echo plxToken::getTokenPostMethod() ?>
						<h1 class="h5 text-center"><strong><?php echo L_LOGIN_PAGE ?></strong></h1>
						<?php (!empty($msg))?plxUtils::showMsg($msg, $error):''; ?>
						<div class="grid">
							<div class="col sml-12">
								<i class="ico icon-user"></i>
								<?php plxUtils::printInput('login', (!empty($_POST['login']))?plxUtils::strCheck($_POST['login']):'', 'text', '10-255',false,'full-width',L_AUTH_LOGIN_FIELD,'autofocus');?>
							</div>
						</div>
						<div class="grid">
							<div class="col sml-12">
								<i class="ico icon-lock"></i>
								<?php plxUtils::printInput('password', '', 'password','10-255',false,'full-width', L_AUTH_PASSWORD_FIELD);?>
							</div>
						</div>
						<?php eval($plxAdmin->plxPlugins->callHook('AdminAuth')) ?>
						<div class="grid">
							<div class="col sml-12 text-center">
								<input class="blue" type="submit" value="<?php echo L_SUBMIT_BUTTON ?>" />
							</div>
						</div>
					</fieldset>
				</form>
				<p class="text-center">
					<small><a class="back" href="<?php echo PLX_ROOT; ?>"><?php echo L_BACK_TO_SITE ?></a> - <?php echo L_POWERED_BY ?></small>
				</p>
			</div>
		</section>
	</main>

<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthEndBody')) ?>
</body>
</html>