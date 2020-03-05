<?php

/**
 * Edition du profil utilisateur
 *
 * @package PLX
 * @author	Stephane F
 **/

include __DIR__ .'/tags/prepend.php';
use Pluxml\PlxToken;
use Pluxml\PlxUtils;

# Control du token du formulaire
PlxToken::validateFormToken($_POST);

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
include __DIR__ .'/tags/top.php';

$_profil = $plxAdmin->aUsers[$_SESSION['user']];
?>
<div class="adminheader">
	<h2 class="h3-like"><?= L_PROFIL_EDIT_TITLE ?></h2>
</div>

<div class="admin">
	<form action="profil.php" method="post" id="form_profil">

		<?php eval($plxAdmin->plxPlugins->callHook('AdminProfilTop')) # Hook Plugins ?>

		<fieldset class="pln">
			<div class="grid-2-small-1">
				<div>avatar</div>
				<div class="grid-2-small-1">
					<label for="id_name"><?= L_PROFIL_USER ?>&nbsp;:</label>
					<?php PlxUtils::printInput('name', PlxUtils::strCheck($_profil['name']), 'text', '20-255') ?>
					<label for="id_email"><?= L_PROFIL_MAIL ?>&nbsp;:</label>
					<?php PlxUtils::printInput('email', PlxUtils::strCheck($_profil['email']), 'text', '30-255') ?>
					<label for="id_lang"><?= L_PROFIL_ADMIN_LANG ?>&nbsp;:</label>
					<?php PlxUtils::printSelect('lang', PlxUtils::getLangs(), $_profil['lang']) ?>
				</div>
			</div>
			<label for="id_content"><?= L_PROFIL_INFOS ?>&nbsp;:</label><br>
			<?php PlxUtils::printArea('content',PlxUtils::strCheck($_profil['infos']), 0, 5); ?>
		</fieldset>
		<?php eval($plxAdmin->plxPlugins->callHook('AdminProfil')) # Hook Plugins ?>
		<?= PlxToken::getTokenPostMethod() ?>
		<input class="btn--primary" type="submit" name="profil" value="<?= L_PROFIL_UPDATE ?>" />
</form>

	<h3 class="h4-like mtm"><?= L_PROFIL_CHANGE_PASSWORD ?></h3>
	<form action="profil.php" method="post" id="form_password">
		<fieldset class="pln">
			<label for="id_password1"><?= L_PROFIL_PASSWORD ?>&nbsp;:</label>
			<?php PlxUtils::printInput('password1', '', 'password', '20-255', false, '', '', 'onkeyup="pwdStrength(this.id)"') ?>
			<label for="id_password2"><?= L_PROFIL_CONFIRM_PASSWORD ?>&nbsp;:</label>
			<?php PlxUtils::printInput('password2', '', 'password', '20-255') ?>
			<?= PlxToken::getTokenPostMethod() ?>
			<input  class="btn--primary" type="submit" name="password" value="<?= L_PROFIL_UPDATE_PASSWORD ?>" />
		</fieldset>
	</form>
</div>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminProfilFoot'));
# On inclut le footer
include __DIR__ .'/tags/foot.php';
?>