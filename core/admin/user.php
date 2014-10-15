<?php

/**
 * Edition des options d'un utilisateur
 *
 * @package PLX
 * @author	Stephane F.
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminUserPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la page statique
if(!empty($_POST) AND isset($plxAdmin->aUsers[ $_POST['id'] ])) {
	$plxAdmin->editUser($_POST);
	header('Location: user.php?p='.$_POST['id']);
	exit;
}
elseif(!empty($_GET['p'])) { # On vérifie l'existence de l'utilisateur
	$id = plxUtils::strCheck(plxUtils::nullbyteRemove($_GET['p']));
	if(!isset($plxAdmin->aUsers[ $id ])) {
		plxMsg::Error(L_USER_UNKNOWN);
		header('Location: parametres_users.php');
		exit;
	}
} else { # Sinon, on redirige
	header('Location: parametres_users.php');
	exit;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<p class="back"><a href="parametres_users.php"><?php echo L_USER_BACK_TO_PAGE ?></a></p>

<h2><?php echo L_USER_PAGE_TITLE ?> "<?php echo plxUtils::strCheck($plxAdmin->aUsers[$id]['name']); ?>"</h2>

<?php eval($plxAdmin->plxPlugins->callHook('AdminUserTop')) # Hook Plugins ?>

<form action="user.php" method="post" id="form_user">
	<fieldset>
		<?php plxUtils::printInput('id', $id, 'hidden');?>
		<p class="field"><label for="id_lang"><?php echo L_USER_LANG ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('lang', plxUtils::getLangs(), $plxAdmin->aUsers[$id]['lang']) ?>
		<p class="field"><label for="id_email"><?php echo L_USER_MAIL ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('email', plxUtils::strCheck($plxAdmin->aUsers[$id]['email']), 'text', '30-255') ?>
		<p id="p_content"><label for="id_content"><?php echo L_USER_INFOS ?>&nbsp;:</label></p>
		<?php plxUtils::printArea('content',plxUtils::strCheck($plxAdmin->aUsers[$id]['infos']),95,8) ?>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminUser')) ?>
    <p class="center">
		<?php echo plxToken::getTokenPostMethod() ?>
		<input type="submit" value="<?php echo L_USER_UPDATE ?>"/>
	</p>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminUserFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>