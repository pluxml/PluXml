<?php

/**
 * Edition des options d'un utilisateur
 *
 * @package PLX
 * @author	Stephane F.
 **/

require  'prepend.php';

if(!isset($plxAdmin)) {
	redirectLogin();
}

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminUserPrepend'));

# On édite la page statique
if(!empty($_POST) AND isset($plxAdmin->aUsers[ $_POST['id'] ])) {
	$plxAdmin->editUser($_POST);
	header('Location: user.php?p='.$_POST['id']);
	exit;
}

if(empty($_GET['p']) or !preg_match('#^\d{3}$#', $_GET['p'])) {
	plxMsg::Error(L_USER_UNKNOWN);
	redirectAdmin();
}

# On vérifie l'existence de l'utilisateur
$id = plxUtils::strCheck(plxUtils::nullbyteRemove($_GET['p']));
if(!isset($plxAdmin->aUsers[ $id ])) {
	plxMsg::Error(L_USER_UNKNOWN);
	redirectAdmin();
}

# On inclut le header
require 'top.php';
?>

<form method="post" id="form_user">

	<div class="inline-form action-bar">
		<h2><?= L_USER_PAGE_TITLE ?> "<?= plxUtils::strCheck($plxAdmin->aUsers[$id]['name']); ?>"</h2>
		<p><a class="back" href="parametres_users.php"><?= L_USER_BACK_TO_PAGE ?></a></p>
		<?= plxToken::getTokenPostMethod() ?>
		<input type="submit" value="<?= L_USER_UPDATE ?>"/>
	</div>
<?php eval($plxAdmin->plxPlugins->callHook('AdminUserTop')) # Hook Plugins ?>
	<fieldset>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<?php plxUtils::printInput('id', $id, 'hidden');?>
				<label for="id_lang"><?= L_USER_LANG ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('lang', plxUtils::getLangs(), $plxAdmin->aUsers[$id]['lang']) ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_email"><?= L_USER_MAIL ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('email', plxUtils::strCheck($plxAdmin->aUsers[$id]['email']), 'email', '30-255') ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_content"><?= L_USER_INFOS ?>&nbsp;:</label>
				<?php plxUtils::printArea('content',plxUtils::strCheck($plxAdmin->aUsers[$id]['infos']), 0, 8) ?>
			</div>
		</div>
	</fieldset>
<?php eval($plxAdmin->plxPlugins->callHook('AdminUser')) # Hook Plugins ?>
</form>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminUserFoot'));

# On inclut le footer
require 'foot.php';
