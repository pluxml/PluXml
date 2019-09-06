<!DOCTYPE html>
<html lang="<?php echo $plxAdmin->aConf['default_lang'] ?>">
<head>
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
	<meta name="robots" content="noindex, nofollow" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET); ?>" />
	<title>PluXml - <?php echo L_AUTH_PAGE_TITLE ?></title>
	<link rel="stylesheet" type="text/css" href="<?= $plxLayoutDir.'css/knacss.css'?>" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?= $plxLayoutDir.'css/theme.css'?>" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?= $plxLayoutDir.'fonts/fontello.css'?>" media="screen" />
	<?php if(is_file($plxAdmin->aConf['custom_admincss_file'])) echo '<link rel="stylesheet" type="text/css" href="'.$plxAdmin->aConf['custom_admincss_file'].'" media="screen" />'."\n" ?>
	<?php
	if(file_exists($plxAdmin->aConf['racine_plugins'].'admin.css'))
		echo '<link rel="stylesheet" type="text/css" href="'.$plxAdmin->aConf['racine_plugins'].'admin.css" media="screen" />'."\n";
	?>
	<link rel="icon" href="theme/images/favicon.png" />
	<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthEndHead')) ?>
	<script src="theme/js/visual.js"></script>
</head>

<body id="auth">

	<main class="flex-container mtl">
		<section class="item-center w350p">
			<div class="logo"></div>
				<div class="auth">
                		<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTop')) ?>
                		<form action="auth/login<?php echo !empty($redirect)?'?p='.$plxUtils->strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
                			<fieldset>
                				<?php echo $plxToken->getTokenPostMethod() ?>
                				<h1 class="h5-like txtcenter"><?php echo L_LOGIN_PAGE ?></h1>
                				<?php (!empty($msg))?$plxUtils->showMsg($msg, $css):''; ?>
               					<label class="w100">
               						<?php echo L_AUTH_LOGIN_FIELD ?>
               						<?php $plxUtils->printInput('login', (!empty($_POST['login']))?$plxUtils->strCheck($_POST['login']):'', 'text', '',false,'w100','','autofocus');?>
               					</label>
               					<label class="w100">
               						<?php echo L_AUTH_PASSWORD_FIELD ?>
               						<?php $plxUtils->printInput('password', '', 'password','',false, 'w100');?>
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
               		<p class="mas">←&nbsp;<a href="<?php echo $plxUtils->getRacine() ?>"><?php echo L_BACK_TO_SITE ?></a></p>

<!-- 
			<?php
			switch ($_GET['action']){
			    case 'lostpassword':
			        # Affichage du formulaire d'envoi du mail de changement de mot de passe
            ?>
    				<div class="auth">
                		<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTop')) ?>
                		<form action="admin/lostPassword<?php echo !empty($redirect)?'?p='.$plxUtils->strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
                			<fieldset>
                				<?php echo $plxToken->getTokenPostMethod() ?>
                				<h1 class="h5-like txtcenter"><?php echo L_LOST_PASSWORD ?></h1>
               					<label class="w100">
               						<?php echo L_AUTH_LOST_FIELD ?>
               						<?php $plxUtils->printInput('lostpassword_id', (!empty($_POST['lostpassword_id']))?$plxUtils->strCheck($_POST['lostpassword_id']):'', 'text', '', false, 'w100', '', 'autofocus');?>
               					</label>
             					<p><a href="?p=/core/admin"><?php echo L_LOST_PASSWORD_LOGIN ?></a></p>
                				<?php eval($plxAdmin->plxPlugins->callHook('AdminAuth')) ?>
                				<div class="txtcenter">
               						<input role="button" class="btn--primary" type="submit" value="<?php echo L_SUBMIT_BUTTON ?>" />
                				</div>
                			</fieldset>
                		</form>
                	</div>
                   	<p class="mas">←&nbsp;<a href="<?php echo $plxUtils->getRacine() ?>"><?php echo L_BACK_TO_SITE ?></a></p>
           	<?php                         
                break;
                case 'changepassword':
                    # Affichage du formulaire de changement de mot passe
                    $lostPasswordToken = $_GET['token'];
                    if ($plxAdmin->verifyLostPasswordToken($lostPasswordToken)) {
            ?>
    					<div class="auth">
                    		<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTop')) ?>
                    		<form action="admin/changePassword<?php echo !empty($redirect)?'?p='.$plxUtils->strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
                    			<fieldset>
                    				<?php echo $plxToken->getTokenPostMethod() ?>
                    				<input name="lostPasswordToken" value="<?php echo $lostPasswordToken ?>" type="hidden" />
                    				<h1 class="h5-like txtcenter"><?php echo L_PROFIL_CHANGE_PASSWORD ?></h1>
                   					<label class="w100">
                   						<?php echo L_PROFIL_PASSWORD ?>
                   						<?php $plxUtils->printInput('password1', '', 'password', '', false, 'w100', '', 'onkeyup="pwdStrength(this.id)"') ?>
                   					</label>
                   					<label class="w100">
                   						<?php echo L_PROFIL_CONFIRM_PASSWORD ?>
                   						<?php $plxUtils->printInput('password2', '', 'password', '', false, 'w100') ?>
                   					</label>
               						<p><a href="?p=/core/admin"><?php echo L_LOST_PASSWORD_LOGIN ?></a></p>
                    				<?php eval($plxAdmin->plxPlugins->callHook('AdminAuth')) ?>
                    				<div class="txtcenter">
   										<input role="button" class="btn--primary" type="submit" name="editpassword" value="<?php echo L_PROFIL_UPDATE_PASSWORD ?>" />
                    				</div>
                    			</fieldset>
                    		</form>
                    	</div>
                   		<p class="mas">←&nbsp;<a href="<?php echo $plxUtils->getRacine() ?>"><?php echo L_BACK_TO_SITE ?></a></p>
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
                   		<p class="mas">←&nbsp;<a href="<?php echo $plxUtils->getRacine() ?>"><?php echo L_BACK_TO_SITE ?></a></p>
			<?php
                    }
                break;
                default:
                    # Affichage du formulaire de connexion à l'administration
			?>
                	<div class="auth">
                		<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTop')) ?>
                		<form action="auth/login<?php echo !empty($redirect)?'?p='.$plxUtils->strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
                			<fieldset>
                				<?php echo $plxToken->getTokenPostMethod() ?>
                				<h1 class="h5-like txtcenter"><?php echo L_LOGIN_PAGE ?></h1>
                				<?php (!empty($msg))?$plxUtils->showMsg($msg, $css):''; ?>
               					<label class="w100">
               						<?php echo L_AUTH_LOGIN_FIELD ?>
               						<?php $plxUtils->printInput('login', (!empty($_POST['login']))?$plxUtils->strCheck($_POST['login']):'', 'text', '',false,'w100','','autofocus');?>
               					</label>
               					<label class="w100">
               						<?php echo L_AUTH_PASSWORD_FIELD ?>
               						<?php $plxUtils->printInput('password', '', 'password','',false, 'w100');?>
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
               		<p class="mas">←&nbsp;<a href="<?php echo $plxUtils->getRacine() ?>"><?php echo L_BACK_TO_SITE ?></a></p>
			<?php 
            }
			?>
-->
		</section>
	</main>

<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthEndBody')) ?>
</body>
</html>
