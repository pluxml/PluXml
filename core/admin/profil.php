<?php

/**
 * Edition du profil utilisateur
 *
 * @package PLX
 * @author    Stephane F, Pedro "P3ter" CADETE
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminProfilPrepend'));

# On édite la configuration
if (!empty($_POST)) {

    if (!empty($_POST['profil']))
        $plxAdmin->editProfil($_POST);
    elseif (!empty($_POST['password']))
        $plxAdmin->editPassword($_POST);

    header('Location: profil.php');
    exit;

}

# On inclut le header
include 'top.php';

$_profil = $plxAdmin->aUsers[$_SESSION['user']];
?>
	<div class="adminheader">
		<div>
			<h2 class="h3-like"><?= L_PROFIL_EDIT_TITLE ?></h2>
		</div>
		<div>
			<div>
				<input class="btn--primary" type="submit" name="profil" role="button" value="<?= L_SAVE ?>"/>
			</div>
		</div>
	</div>
	<div class="admin>">
	    <form method="post" id="form_profil" class="first-level">
	        <?= plxToken::getTokenPostMethod() ?>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminProfilTop'))
?>

	        <fieldset>
	            <div class="inbl">
					<label><?= L_PROFIL_LOGIN ?></label>
	                <strong><?= plxUtils::strCheck($_profil['login']) ?></strong>
				</div>
	            <div class="grid-2">
	                <label for="id_name"><?= L_PROFIL_USER ?></label>
	                <?php plxUtils::printInput('name', plxUtils::strCheck($_profil['name']), 'text', '20-255') ?>
	                <label for="id_email"><?= L_MAIL_ADDRESS ?></label>
	                <?php plxUtils::printInput('email', plxUtils::strCheck($_profil['email']), 'text', '30-255') ?>
	                <label for="id_lang"><?= L_USER_LANG ?></label>
	                <?php plxUtils::printSelect('lang', plxUtils::getLangs(), $_profil['lang']) ?>
	            </div>
	            <label for="id_content"><?= L_INFOS ?></label>
	            <?php plxUtils::printArea('content', plxUtils::strCheck($_profil['infos']), 0, 5); ?>
	        </fieldset>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminProfil'))
?>
	    </form>
	    <form method="post" id="form_password" class="first-level">
			<?= plxToken::getTokenPostMethod(); ?>
	        <fieldset>
	            <h3><?= L_PROFIL_CHANGE_PASSWORD ?></h3>
	            <div class="grid-2">
	                <label for="id_password1"><?= L_PASSWORD ?></label>
	                <?php plxUtils::printInput('password1', '', 'password', '20-255', false, '', '', 'onkeyup="pwdStrength(this.id)"') ?>
	                <label for="id_password2"><?= L_CONFIRM_PASSWORD ?></label>
	                <?php plxUtils::printInput('password2', '', 'password', '20-255') ?>
	            </div>
	            <input class="btn--primary" type="submit" name="password" role="button" value="<?= L_PROFIL_UPDATE_PASSWORD ?>"/>
	        </fieldset>
	    </form>
	</div>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminProfilFoot'));

# On inclut le footer
include 'foot.php';
