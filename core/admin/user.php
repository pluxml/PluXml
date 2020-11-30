<?php

/**
 * Edition des options d'un utilisateur
 *
 * @package PLX
 * @author    Stephane F.
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminUserPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la page statique
if (!empty($_POST) and isset($plxAdmin->aUsers[$_POST['id']])) {
    $plxAdmin->editUser($_POST);
    header('Location: user.php?p=' . $_POST['id']);
    exit;
} elseif (!empty($_GET['p'])) { # On vérifie l'existence de l'utilisateur
    $id = plxUtils::strCheck(plxUtils::nullbyteRemove($_GET['p']));
    if (!array_key_exists($id, $plxAdmin->aUsers)) {
        plxMsg::Error(L_USER_UNKNOWN);
        header('Location: parametres_users.php');
        exit;
    }
} else { # Sinon, on redirige
    header('Location: parametres_users.php');
    exit;
}

# On inclut le header
include 'top.php';
?>

<div class="admin">
    <form method="post" id="form_user">
	    <?= plxToken::getTokenPostMethod() ?>
		<?php plxUtils::printInput('id', $id, 'hidden'); ?>
        <div class="adminheader">
			<div>
	            <h2><?= L_USER_PAGE_TITLE ?> "<?= plxUtils::strCheck($plxAdmin->aUsers[$id]['name']); ?>"</h2>
	            <p><a class="back" href="parametres_users.php"><?= L_USER_BACK_TO_PAGE ?></a></p>
			</div>
			<div>
	            <input type="submit" class="button--primary" value="<?= L_SAVE ?>"/>
			</div>
        </div>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminUserTop')) ;
?>
        <fieldset>
			<div class="label-expanded">
				<label for="id_lang"><?= L_USER_LANG ?></label>
<?php plxUtils::printSelect('lang', plxUtils::getLangs(), $plxAdmin->aUsers[$id]['lang']) ?>
			</div>
			<div class="label-expanded">
				<label for="id_email"><?= L_MAIL_ADDRESS ?></label>
				<input type="email" name="email" value="<?= plxUtils::strCheck($plxAdmin->aUsers[$id]['email']) ?>" id="id_email" />
			</div>
			<div>
	            <label for="id_content"><?= L_INFOS ?></label>
	            <textarea name="content" rows="8" id="id_content"><?= plxUtils::strCheck($plxAdmin->aUsers[$id]['infos']) ?></textarea>
			</div>
        </fieldset>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminUser'))
?>
    </form>
</div>

<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminUserFoot'));

# On inclut le footer
include 'foot.php';
