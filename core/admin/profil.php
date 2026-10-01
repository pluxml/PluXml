<?php

/**
 * Edition du profil utilisateur
 *
 * @package PLX
 * @author	Stephane F
 **/

require 'prepend.php';

if(!isset($plxAdmin)) {
	redirectLogin();
}

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminProfilPrepend'));

# On édite la configuration
if(!empty($_POST)) {

	if(!empty($_POST['profil']))
		$plxAdmin->editProfil($_POST);
	elseif(!empty($_POST['passwordBtn']))
		$plxAdmin->editPassword($_POST);

	header('Location: profil.php');
	exit;

}

# On inclut le header
require 'top.php';

$_profil = $plxAdmin->aUsers[$_SESSION['user']];
?>

<form method="post" id="form_profil">

	<div class="inline-form action-bar">
		<h2><?= L_PROFIL_EDIT_TITLE ?></h2>
		<p><label><?= L_PROFIL_LOGIN ?>&nbsp;:&nbsp;<strong><?= plxUtils::strCheck($_profil['login']) ?></strong></label></p>
		<input type="submit" name="profil" value="<?= L_PROFIL_UPDATE ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminProfilTop')) # Hook Plugins ?>

	<fieldset>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_name"><?= L_PROFIL_USER ?>&nbsp;:</label>
				<?php plxUtils::printInput('fullname', plxUtils::strCheck($_profil['name']), 'text', '20-64') ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_email"><?= L_PROFIL_MAIL ?>&nbsp;:</label>
				<?php plxUtils::printInput('email', plxUtils::strCheck($_profil['email']), 'email', '30-64') ?>
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
				<?php plxUtils::printArea('content',plxUtils::strCheck($_profil['infos']), 0, 5); ?>
			</div>
		</div>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminProfil')) # Hook Plugins ?>
	<?= plxToken::getTokenPostMethod() ?>

</form>

<h3><?= L_PROFIL_CHANGE_PASSWORD ?></h3>
<form method="post" id="form_password">
	<fieldset>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_password1"><?= L_PROFIL_PASSWORD ?>&nbsp;:</label>
				<?php plxUtils::printInput('password', '', 'password', '20-64'); ?>
				<?php plxUtils::printPasswordStrength('password'); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_password2"><?= L_PROFIL_CONFIRM_PASSWORD ?>&nbsp;:</label>
				<?php plxUtils::printInput('password2', '', 'password', '20-64') ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<?= plxToken::getTokenPostMethod() ?>
				<input type="submit" name="passwordBtn" value="<?= L_PROFIL_UPDATE_PASSWORD ?>" />
			</div>
		</div>
	</fieldset>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminProfilFoot'));
# On inclut le footer
require 'foot.php';
