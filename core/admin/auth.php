<?php

/**
 * Page d'authentification
 *
 * @package PLX
 * @author	Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

# Constante pour retrouver la page d'authentification
const PLX_AUTHPAGE = true;

include __DIR__ .'/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Protection anti brute force
$maxlogin['counter'] = 99; # nombre de tentative de connexion autorisé dans la limite de temps autorisé
$maxlogin['timer'] = 3 * 60; # temps d'attente limite si nombre de tentative de connexion atteint (en minutes)

# Initialiser les messages d'alerte
$msg = '';
$css = '';

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminAuthPrepend'));


# Identifier une erreur de connexion 
if(isset($_SESSION['maxtry'])) {
	if( intval($_SESSION['maxtry']['counter']) >= $maxlogin['counter'] AND (time() < $_SESSION['maxtry']['timer'] + $maxlogin['timer']) ) {
		# écriture dans les logs du dépassement des 3 tentatives successives de connexion
		@error_log("PluXml: Max login failed. IP : ".plxUtils::getIp());
		# message à affiche sur le mire de connexion
		$msg = sprintf(L_ERR_MAXLOGIN, ($maxlogin['timer']/60));
		$css = 'alert--danger';
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

# Incrémente le nombre de tentative
$redirect=$plxAdmin->aConf['racine'].'core/admin/';
if(!empty($_GET['p']) AND $css=='') {

	# on incremente la variable de session qui compte les tentatives de connexion
	$_SESSION['maxtry']['counter']++;

	$racine = parse_url($plxAdmin->aConf['racine']);
	$get_p = parse_url(urldecode($_GET['p']));
	$css = (!$get_p OR (isset($get_p['host']) AND $racine['host']!=$get_p['host']));
	if(!$css AND !empty($get_p['path']) AND file_exists(PLX_ROOT.'core/admin/'.basename($get_p['path']))) {
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

# Déconnexion (paramètre url : ?d=1)
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
if(!empty($_POST['login']) AND !empty($_POST['password']) AND $css=='') {

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
		$css = 'alert--danger';
	}
}

# Send lost password e-mail
if(!empty($_POST['lostpassword_id'])) {
    
    if (!empty($plxAdmin->sendLostPasswordEmail($_POST['lostpassword_id']))) {
        $msg = L_LOST_PASSWORD_SUCCESS;
        $css = 'alert--success';
    }
    else {
        @error_log("Lost password error. ID : ".$_POST['lostpassword_id']." IP : ".plxUtils::getIp());
        $msg = L_UNKNOWN_ERROR;
        $css = 'alert--danger';
    }
}

# Change password
if(!empty($_POST['editpassword'])){
    
    unset($_SESSION['error']);
    unset($_SESSION['info']);
    
    $plxAdmin->editPassword($_POST);
    
    if (!empty($msg = $_SESSION['error'])) {
        $css = 'alert--danger';
    }
    else {
        if (!empty($msg = $_SESSION['info'])) {
            $css = 'alert--success';
        }
    }
    
    unset($_SESSION['error']);
    unset($_SESSION['info']);
}

# Construction de la page HTML
plxUtils::cleanHeaders();
?>

<!DOCTYPE html>
<html lang="<?php echo $plxAdmin->aConf['default_lang'] ?>">
<head>
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
	<meta name="robots" content="noindex, nofollow" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET); ?>" />
	<title>PluXml - <?php echo L_AUTH_PAGE_TITLE ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/css/knacss.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/css/theme.css" media="screen" />
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

	<main class="flex-container mtl">
		<section class="item-center w350p">
			<div class="logo"></div>

			<?php
			switch ($_GET['action']){
			    case 'lostpassword':
			        # Affichage du formulaire d'envoi du mail de changement de mot de passe
            ?>
    				<div class="auth">
                		<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTop')) ?>
                		<form action="auth.php<?php echo !empty($redirect)?'?p='.plxUtils::strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
                			<fieldset>
                				<?php echo plxToken::getTokenPostMethod() ?>
                				<h1 class="h5-like txtcenter"><?php echo L_LOST_PASSWORD ?></h1>
               					<label>
               						<?php echo L_AUTH_LOST_FIELD ?>
               						<?php plxUtils::printInput('lostpassword_id', (!empty($_POST['lostpassword_id']))?plxUtils::strCheck($_POST['lostpassword_id']):'', 'text', '', false, 'w100', '', 'autofocus');?>
               					</label>
             					<p><a href="?p=/core/admin"><?php echo L_LOST_PASSWORD_LOGIN ?></a></p>
                				<?php eval($plxAdmin->plxPlugins->callHook('AdminAuth')) ?>
                				<div class="txtcenter">
               						<input role="button" class="btn--primary" type="submit" value="<?php echo L_SUBMIT_BUTTON ?>" />
                				</div>
                			</fieldset>
                		</form>
                	</div>
                   	<p class="mas">←&nbsp;<a href="<?php echo PLX_ROOT; ?>"><?php echo L_BACK_TO_SITE ?></a></p>
           	<?php                         
                break;
                case 'changepassword':
                    # Affichage du formulaire de changement de mot passe
                    $lostPasswordToken = $_GET['token'];
                    if ($plxAdmin->verifyLostPasswordToken($lostPasswordToken)) {
            ?>
    					<div class="auth">
                    		<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTop')) ?>
                    		<form action="auth.php<?php echo !empty($redirect)?'?p='.plxUtils::strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
                    			<fieldset>
                    				<?php echo plxToken::getTokenPostMethod() ?>
                    				<input name="lostPasswordToken" value="<?php echo $lostPasswordToken ?>" type="hidden" />
                    				<h1 class="h5-like txtcenter"><?php echo L_PROFIL_CHANGE_PASSWORD ?></h1>
                   					<label>
                   						<?php echo L_PROFIL_PASSWORD ?>
                   						<?php plxUtils::printInput('password1', '', 'password', '', false, 'w100', '', 'onkeyup="pwdStrength(this.id)"') ?>
                   					</label>
                   					<label>
                   						<?php echo L_PROFIL_CONFIRM_PASSWORD ?>
                   						<?php plxUtils::printInput('password2', '', 'password', '', false, 'w100') ?>
                   					</label>
               						<p><a href="?p=/core/admin"><?php echo L_LOST_PASSWORD_LOGIN ?></a></p>
                    				<?php eval($plxAdmin->plxPlugins->callHook('AdminAuth')) ?>
                    				<div class="txtcenter">
   										<input role="button" class="btn--primary" type="submit" name="editpassword" value="<?php echo L_PROFIL_UPDATE_PASSWORD ?>" />
                    				</div>
                    			</fieldset>
                    		</form>
                    	</div>
                   		<p class="mas">←&nbsp;<a href="<?php echo PLX_ROOT; ?>"><?php echo L_BACK_TO_SITE ?></a></p>
            <?php
                    }
                    else {
            ?>
                        <div class="auth pam">
                        <?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTop')) ?>
                        	<h1 class="h5-like txtcenter"><?php echo L_PROFIL_CHANGE_PASSWORD ?></h1>
                    		<div class="alert--danger">
                    			<?php echo L_LOST_PASSWORD_ERROR ?>
							</div>
							<p class="mts"><a href="?p=/core/admin"><?php echo L_LOST_PASSWORD_LOGIN ?></a></p>
							<?php eval($plxAdmin->plxPlugins->callHook('AdminAuth')) ?>
                    	</div>
                   		<p class="mas">←&nbsp;<a href="<?php echo PLX_ROOT; ?>"><?php echo L_BACK_TO_SITE ?></a></p>
			<?php
                    }
                break;
                default:
                    # Affichage du formulaire de connexion à l'administration
			?>
                	<div class="auth">
                		<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTop')) ?>
                		<form action="auth.php<?php echo !empty($redirect)?'?p='.plxUtils::strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
                			<fieldset>
                				<?php echo plxToken::getTokenPostMethod() ?>
                				<h1 class="h5-like txtcenter"><?php echo L_LOGIN_PAGE ?></h1>
                				<?php (!empty($msg))?plxUtils::showMsg($msg, $css):''; ?>
               					<label>
               						<?php echo L_AUTH_LOGIN_FIELD ?>
               						<?php plxUtils::printInput('login', (!empty($_POST['login']))?plxUtils::strCheck($_POST['login']):'', 'text', '',false,'w100','','autofocus');?>
               					</label>
               					<label>
               						<?php echo L_AUTH_PASSWORD_FIELD ?>
               						<?php plxUtils::printInput('password', '', 'password','',false, 'w100');?>
               					</label>
                				<?php 
                				if ($plxAdmin->aConf['lostpassword']) {
                				?>
               						<p><a href="?action=lostpassword"><?php echo L_LOST_PASSWORD ?></a></p>
                    			<?php 
                				}
                                eval($plxAdmin->plxPlugins->callHook('AdminAuth'))
                                ?>
               					<div class="txtcenter">
               						<input role="button" class="btn--primary" type="submit" value="<?php echo L_SUBMIT_BUTTON ?>" />
                				</div>
                			</fieldset>
                		</form>
                	</div>
               		<p class="mas">←&nbsp;<a href="<?php echo PLX_ROOT; ?>"><?php echo L_BACK_TO_SITE ?></a></p>
			<?php 
            }
			?>
		</section>
	</main>

<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthEndBody')) ?>
</body>
</html>
