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

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminAuthPrepend'));

# Initialisation variable erreur
$error = '';
$msg = '';

# Control et filtrage du parametre $_GET['p']
$redirect=$plxAdmin->aConf['racine'].'core/admin/';
if(!empty($_GET['p'])) {
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
if(!empty($_POST['login']) AND !empty($_POST['password'])) {
	$connected = false;
	foreach($plxAdmin->aUsers as $userid => $user) {
		if ($_POST['login']==$user['login'] AND sha1($user['salt'].md5($_POST['password']))===$user['password'] AND $user['active'] AND !$user['delete']) {
			$_SESSION['user'] = $userid;
			$_SESSION['profil'] = $user['profil'];
			$_SESSION['hash'] = plxUtils::charAleatoire(10);
			$_SESSION['domain'] = $session_domain;
			$_SESSION['lang'] = $user['lang'];
			$connected = true;
		}
	}
	if($connected) {
		header('Location: '.htmlentities($redirect));
		exit;
	} else {
		$msg = L_ERR_WRONG_PASSWORD;
		$error = 'error';
	}
}
plxUtils::cleanHeaders();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $plxAdmin->aConf['default_lang'] ?>" lang="<?php echo $plxAdmin->aConf['default_lang'] ?>">
<head>
	<meta name="robots" content="noindex, nofollow" />
	<title>PluXml - <?php echo L_AUTH_PAGE_TITLE ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/auth.css" media="screen" />
	<link rel="icon" href="<?php echo PLX_CORE ?>admin/theme/images/pluxml.gif" />
	<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthEndHead')) ?>
</head>

<body id="auth">
<div id="login">
	<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTop')) ?>
	<form action="auth.php<?php echo !empty($redirect)?'?p='.plxUtils::strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
	<fieldset>
		<?php echo plxToken::getTokenPostMethod() ?>
		<div class="title"><?php echo L_LOGIN_PAGE ?></div>
		<?php (!empty($msg))?plxUtils::showMsg($msg, $error):''; ?>
		<label for="id_login"><?php echo L_AUTH_LOGIN_FIELD ?>&nbsp;:</label>
		<?php plxUtils::printInput('login', (!empty($_POST['login']))?plxUtils::strCheck($_POST['login']):'', 'text', '18-255');?>
		<label for="id_password"><?php echo L_AUTH_PASSWORD_FIELD ?>&nbsp;:</label>
		<?php plxUtils::printInput('password', '', 'password','18-255');?>
		<?php eval($plxAdmin->plxPlugins->callHook('AdminAuth')) ?>
		<p><input class="button submit" type="submit" value="<?php echo L_SUBMIT_BUTTON ?>" /></p>
	</fieldset>
	</form>
	<p><a href="<?php echo PLX_ROOT; ?>"><?php echo L_BACK_TO_SITE ?></a> | <?php echo L_POWERED_BY ?></p>
</div>
<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthEndBody')) ?>
</body>
</html>
