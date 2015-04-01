<?php

/**
 * Edition du profil utilisateur
 *
 * @package PLX
 * @author	Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

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
include(dirname(__FILE__).'/top.php');

$_profil = $plxAdmin->aUsers[$_SESSION['user']];
?>

<form action="profil.php" method="post" id="form_profil">

	<div class="inline-form action-bar">
		<h2><?php echo L_PROFIL_EDIT_TITLE ?></h2>
		<p><label><?php echo L_PROFIL_LOGIN ?>&nbsp;:&nbsp;<strong><?php echo plxUtils::strCheck($_profil['login']) ?></strong></label></p>
		<input type="submit" name="profil" value="<?php echo L_PROFIL_UPDATE ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminProfilTop')) # Hook Plugins ?>

	<fieldset>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_name"><?php echo L_PROFIL_USER ?>&nbsp;:</label>
				<?php plxUtils::printInput('name', plxUtils::strCheck($_profil['name']), 'text', '20-255') ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_email"><?php echo L_PROFIL_MAIL ?>&nbsp;:</label>
				<?php plxUtils::printInput('email', plxUtils::strCheck($_profil['email']), 'text', '30-255') ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_lang"><?php echo L_PROFIL_ADMIN_LANG ?>&nbsp;:</label>
				<?php plxUtils::printSelect('lang', plxUtils::getLangs(), $_profil['lang']) ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_content"><?php echo L_PROFIL_INFOS ?>&nbsp;:</label>
				<?php plxUtils::printArea('content',plxUtils::strCheck($_profil['infos']),140,5,false,'full-width'); ?>
			</div>
		</div>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminProfil')) # Hook Plugins ?>
	<?php echo plxToken::getTokenPostMethod() ?>

</form>

<h3><?php echo L_PROFIL_CHANGE_PASSWORD ?></h3>
<form action="profil.php" method="post" id="form_password">
	<fieldset>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_password1"><?php echo L_PROFIL_PASSWORD ?>&nbsp;:</label>
				<?php plxUtils::printInput('password1', '', 'password', '20-255') ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_password2"><?php echo L_PROFIL_CONFIRM_PASSWORD ?>&nbsp;:</label>
				<?php plxUtils::printInput('password2', '', 'password', '20-255') ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<?php echo plxToken::getTokenPostMethod() ?>
				<input type="submit" name="password" value="<?php echo L_PROFIL_UPDATE_PASSWORD ?>" />
			</div>
		</div>
	</fieldset>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminProfilFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>