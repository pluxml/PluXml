<?php

/**
 * PluXml administration authentification page
 * @author	Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

const PLX_AUTHPAGE = true;

include __DIR__ .'/prepend.php';
use Pluxml\PlxToken;
use Pluxml\PlxUtils;
use Pluxml\PlxVueData;

//Form token validation
PlxToken::validateFormToken($_POST);

//Brut force protection
$maxlogin['counter'] = 99; # connexion maximum attempt number in the time limit
$maxlogin['timer'] = 3 * 60; # wait time (ine minutes) until the next attempt if maximum is exceeded

//Alert messages initialisation
$msg = '';
$css = '';

//Plugins Hook
eval($plxAdmin->plxPlugins->callHook('AdminAuthPrepend'));

//Identifying connexion error
if(isset($_SESSION['maxtry'])) {
	if( intval($_SESSION['maxtry']['counter']) >= $maxlogin['counter'] AND (time() < $_SESSION['maxtry']['timer'] + $maxlogin['timer']) ) {
		//write in the logs if thee unsucessfull connexion attempts
		@error_log("PluXml: Max login failed. IP : ".PlxUtils::getIp());
		//alert to display
		$msg = sprintf(L_ERR_MAXLOGIN, ($maxlogin['timer']/60));
		$css = 'alert red';
	}
	if( time() > ($_SESSION['maxtry']['timer'] + $maxlogin['timer']) ) {
		//reset brut force control if wait time is passed
		$_SESSION['maxtry']['counter'] = 0;
		$_SESSION['maxtry']['timer'] = time();
	}
} else {
	//attempt count initialisation
	$_SESSION['maxtry']['counter'] = 0;
	$_SESSION['maxtry']['timer'] = time();
}

//Attempt number incrimentation
$redirect=$plxAdmin->aConf['racine'].'core/admin/';
if(!empty($_GET['p']) AND $css=='') {
	$_SESSION['maxtry']['counter']++;
	$racine = parse_url($plxAdmin->aConf['racine']);
	$get_p = parse_url(urldecode($_GET['p']));
	$css = (!$get_p OR (isset($get_p['host']) AND $racine['host']!=$get_p['host']));
	if(!$css AND !empty($get_p['path']) AND file_exists(PLX_ROOT.'core/admin/'.basename($get_p['path']))) {
		//URL parameters filter
		$query='';
		if(isset($get_p['query'])) {
			$query=strtok($get_p['query'],'=');
			$query=($query[0]!='d'?'?'.$get_p['query']:'');
		}
		// redirect URL
		$redirect=$get_p['path'].$query;
	}
}

//Disconnection (URL parameter is "?d=1")
if(!empty($_GET['d']) AND $_GET['d']==1) {
	$_SESSION = array();
	session_destroy();
	header('Location: auth.php');
	exit;
}

//Authentification
if(!empty($_POST['login']) AND !empty($_POST['password']) AND $css=='') {
	$connected = false;
	foreach($plxAdmin->aUsers as $userid => $user) {
		if ($_POST['login']==$user['login'] AND sha1($user['salt'].md5($_POST['password']))===$user['password'] AND $user['active'] AND !$user['delete']) {
			$_SESSION['user'] = $userid;
			$_SESSION['profil'] = $user['profil'];
			$_SESSION['hash'] = PlxUtils::charAleatoire(10);
			$_SESSION['domain'] = $session_domain;
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
		$css = 'alert red';
	}
}

//Send lost password e-mail
if(!empty($_POST['lostpassword_id'])) {
	if (!empty($plxAdmin->sendLostPasswordEmail($_POST['lostpassword_id']))) {
		$msg = L_LOST_PASSWORD_SUCCESS;
		$css = 'alert green';
	}
	else {
		@error_log("Lost password error. ID : ".$_POST['lostpassword_id']." IP : ".PlxUtils::getIp());
		$msg = L_UNKNOWN_ERROR;
		$css = 'alert red';
	}
}

//Change password
if(!empty($_POST['editpassword'])){
	unset($_SESSION['error']);
	unset($_SESSION['info']);
	$plxAdmin->editPassword($_POST);
	if (!empty($msg = isset($_SESSION['error']) ? $_SESSION['error'] : '')) {
		$css = 'alert red';
	}
	else {
		if (!empty($msg = isset($_SESSION['info']) ? $_SESSION['info'] : '')) {
			$css = 'alert green';
		}
	}
	unset($_SESSION['error']);
	unset($_SESSION['info']);
}

//View beginning
PlxUtils::cleanHeaders();

//Vue.js datas initialisation
$builkDatas = array(
		'lostPasswordActivated' => ($plxAdmin->aConf['lostpassword']) ? true : false,
		'lostpassword' => false,
		'changepassword' => isset($_GET['action']) && $_GET['action'] == 'changepassword' ? true : false,
		'verifyLostPasswordToken' => $plxAdmin->verifyLostPasswordToken(isset($_GET['token']) ? $_GET['token'] : null)
	);
//$vueDatas = new PlxVueData($builkDatas);
//$datas = $vueDatas->getJsonDatas();
$datas = json_encode($builkDatas);
?>

<!DOCTYPE html>
<html lang="<?= $plxAdmin->aConf['default_lang'] ?>">
<head>
	<meta name="robots" content="noindex, nofollow" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title>PluXml - <?= L_AUTH_PAGE_TITLE ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?= strtolower(PLX_CHARSET); ?>" />
	<link rel="stylesheet" type="text/css" href="<?= PLX_CORE ?>admin/theme/css/knacss.css?v=<?= PLX_VERSION ?>" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?= PLX_CORE ?>admin/theme/css/theme.css?v=<?= PLX_VERSION ?>" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?= PLX_CORE ?>admin/theme/fonts/fontello.css?v=<?= PLX_VERSION ?>" media="screen" />
	<link rel="icon" href="<?= PLX_CORE ?>admin/theme/images/favicon.png" />
	<?php
		PlxUtils::printLinkCss($plxAdmin->aConf['custom_admincss_file'], true);
		PlxUtils::printLinkCss($plxAdmin->aConf['racine_plugins'].'admin.css', true);
		eval($plxAdmin->plxPlugins->callHook('AdminAuthEndHead'));
	?>
	<script src="<?= PLX_CORE ?>lib/visual.js?v=<?= PLX_VERSION ?>"></script>
	<script src="<?= PLX_CORE ?>lib/vue.js"></script>
</head>
<body id="auth">
	<main id="vue" class="auth flex-container--column">
		<section class="item-center">
			<div class="logo item-center"></div>
			<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthBegin')) ?>
			<div v-if="lostpassword" class="form">
				<? eval($plxAdmin->plxPlugins->callHook('AdminAuthTopLostPassword')); ?>
				<form action="auth.php<?php echo !empty($redirect)?'?p='.plxUtils::strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
					<fieldset>
						<?= PlxToken::getTokenPostMethod() ?>
						<h1><strong><?= L_LOST_PASSWORD ?></strong>	</h1>
						<?php PlxUtils::printInput('lostpassword_id', (!empty($_POST['lostpassword_id']))?PlxUtils::strCheck($_POST['lostpassword_id']):'', 'text', '10-255',false,'full-width',L_AUTH_LOST_FIELD,'autofocus');?>
						<small><a v-on:click="lostpassword = false" href="#"><?= L_LOST_PASSWORD_LOGIN ?></a></small>
						<?php eval ( $plxAdmin->plxPlugins->callHook ( 'AdminAuthLostPassword' ) ); ?>
						<input class="blue" type="submit" value="<?= L_SUBMIT_BUTTON ?>" />
					</fieldset>
				</form>
			</div>
			<div v-else-if="changepassword" class="form">
				<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTopChangePassword')); ?>
				<div v-if="verifyLostPasswordToken">
					<form action="auth.php<?= !empty($redirect)?'?p='.PlxUtils::strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
						<fieldset>
							<?= PlxToken::getTokenPostMethod() ?>
							<input name="lostPasswordToken" value="<?= $lostPasswordToken ?>" type="hidden" />
							<h1 class="h5 text-center"><strong><?= L_PROFIL_CHANGE_PASSWORD ?></strong></h1>
							<?php PlxUtils::printInput('password1', '', 'password', '10-255',false,'full-width', L_PROFIL_PASSWORD, 'onkeyup="pwdStrength(this.id)"') ?>
							<?php PlxUtils::printInput('password2', '', 'password', '10-255',false,'full-width', L_PROFIL_CONFIRM_PASSWORD) ?>
							<small><a v-on:click="changepassword = false" href="#"><?= L_LOST_PASSWORD_LOGIN ?></a></small>
							<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthChangePassword'));	?>
							<input type="submit" name="editpassword" value="<?= L_PROFIL_UPDATE_PASSWORD ?>" />
						</fieldset>
					</form>
				</div>
				<div v-else>
					<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTopChangePasswordError')); ?>
					<h1 class="h5 text-center"><strong><?= L_PROFIL_CHANGE_PASSWORD ?></strong></h1>
					<div class="alert red"><?= L_LOST_PASSWORD_ERROR ?></div>
					<small><a v-on:click="changepassword = false" href="#"><?= L_LOST_PASSWORD_LOGIN ?></a></small>
					<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthChangePasswordError')) ?>
				</div>
			</div>
			<div v-else class="form">
				<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTop')) ?>
				<form action="auth.php<?= !empty($redirect)?'?p='.PlxUtils::strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
					<fieldset>
						<?= PlxToken::getTokenPostMethod() ?>
						<h1 class="h5 text-center"><strong><?= L_LOGIN_PAGE ?></strong></h1>
						<?php (!empty($msg))?PlxUtils::showMsg($msg, $css):''; ?>
						<?php PlxUtils::printInput('login', (!empty($_POST['login']))?PlxUtils::strCheck($_POST['login']):'', 'text', '10-255',false,'full-width',L_AUTH_LOGIN_FIELD,'autofocus');?>
						<?php PlxUtils::printInput('password', '', 'password','10-255',false,'full-width', L_AUTH_PASSWORD_FIELD);?>
						<small v-if="lostPasswordActivated"><a v-on:click="lostpassword = true" href="#"><?= L_LOST_PASSWORD ?></a></small>
						<?php eval($plxAdmin->plxPlugins->callHook('AdminAuth')); ?>
						<input class="blue" type="submit" value="<?= L_SUBMIT_BUTTON ?>" />
					</fieldset>
				</form>
			</div>
			<p><small><a class="back" href="<?= PLX_ROOT; ?>"><?= L_BACK_TO_SITE ?></a> - <?= L_POWERED_BY ?></small></p>
		</section>
	</main>
	<script>
	new Vue({
		el: '#vue',
		data: <?= $datas ?>
	})
	</script>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthEndBody')) ?>
</body>