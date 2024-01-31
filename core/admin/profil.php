<?php

/**
 * Edition du profil utilisateur
 *
 * @package PLX
 * @author	Stephane F
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminProfilPrepend'));

# On édite la configuration
if(!empty($_POST)) {

	if(!empty($_POST['profil']))
		$plxAdmin->editProfil($_POST);
	elseif(!empty($_POST['password']))
		$plxAdmin->editPassword($_POST);

	header('Location: profil.php');
	exit;

}

# On inclut le header
include 'top.php';

$_profil = $plxAdmin->aUsers[$_SESSION['user']];
$requireMail = boolval($plxAdmin->aConf['lostpassword']);
?>

<form action="profil.php" method="post" id="form_profil">

	<div class="inline-form action-bar">
		<h2><?= L_PROFIL_EDIT_TITLE ?></h2>
		<p><label><?= L_PROFIL_LOGIN ?>&nbsp;:&nbsp;<strong><?= plxUtils::strCheck($_profil['login']) ?></strong></label></p>
		<input type="submit" name="profil" value="<?= L_PROFIL_UPDATE ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminProfilTop')) # Hook Plugins ?>

	<fieldset>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_name" class="required"><?= L_PROFIL_USER ?>&nbsp;:</label>
				<?php plxUtils::printInput('name', plxUtils::strCheck($_profil['name']), 'text', '20-255', false, '', '', '', true) ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_email"<?= ($requireMail? ' class="required"': '') ?>><?= L_PROFIL_MAIL ?>&nbsp;:</label>
				<?php plxUtils::printInput('email', plxUtils::strCheck($_profil['email']), 'email', '', false, '', '', '', $requireMail) ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_lang"><?= L_PROFIL_ADMIN_LANG ?>&nbsp;:</label>
				<?php plxUtils::printSelect('lang', plxUtils::getLangs(), $_profil['lang']) ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_content"><?= L_PROFIL_INFOS ?>&nbsp;:</label>
				<?php plxUtils::printArea('content',plxUtils::strCheck($_profil['infos']), 0, 5, false ,'full-width', 'placeholder=" "'); ?>
			</div>
		</div>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminProfil')) # Hook Plugins ?>
	<?= plxToken::getTokenPostMethod() ?>

</form>

<h3><?= L_PROFIL_CHANGE_PASSWORD ?></h3>
<form action="profil.php" method="post" id="form_password">
	<fieldset>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_password1" class="required"><?= L_PROFIL_PASSWORD ?>&nbsp;:</label>
				<i class="ico icon-lock"></i>
				<?php plxUtils::printInput('password1', '', 'password', '20-255', false, '', '', '', true) ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_password2" class="required"><?= L_PROFIL_CONFIRM_PASSWORD ?><span data-lang="&nbsp;❌|&nbsp;✅"></span>&nbsp;:</label>
				<i class="ico icon-lock"></i>
				<?php plxUtils::printInput('password2', '', 'password', '20-255', false, '', '', '', true) ?>

			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<?= plxToken::getTokenPostMethod() ?>
				<input type="submit" name="password" value="<?= L_PROFIL_UPDATE_PASSWORD ?>" />
			</div>
		</div>
	</fieldset>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminProfilFoot'));

# On inclut le footer
include 'foot.php';
