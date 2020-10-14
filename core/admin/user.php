<?php

/**
 * Edition des options d'un utilisateur
 *
 * @package PLX
 * @author    Stephane F.
 **/

include __DIR__ . '/prepend.php';

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
    if (!isset($plxAdmin->aUsers[$id])) {
        plxMsg::Error(L_USER_UNKNOWN);
        header('Location: parametres_users.php');
        exit;
    }
} else { # Sinon, on redirige
    header('Location: parametres_users.php');
    exit;
}

# On inclut le header
include __DIR__ . '/top.php';
?>

<div class="admin">
    <form action="user.php" method="post" id="form_user">

        <div class="inline-form action-bar">
            <h2><?php echo L_USER_PAGE_TITLE ?> "<?php echo plxUtils::strCheck($plxAdmin->aUsers[$id]['name']); ?>"</h2>
            <p><a class="back" href="parametres_users.php"><?php echo L_USER_BACK_TO_PAGE ?></a></p>
            <?php echo plxToken::getTokenPostMethod() ?>
            <input type="submit" class="button--primary" value="<?php echo L_USER_UPDATE ?>"/>
        </div>

        <?php eval($plxAdmin->plxPlugins->callHook('AdminUserTop')) # Hook Plugins ?>

        <fieldset>
            <div class="grid-2-small-1">
                <div>
                    <?php plxUtils::printInput('id', $id, 'hidden'); ?>
                    <label for="id_lang"><?php echo L_USER_LANG ?>&nbsp;:</label>
                </div>
                <div>
                    <?php plxUtils::printSelect('lang', plxUtils::getLangs(), $plxAdmin->aUsers[$id]['lang']) ?>
                </div>
                <div>
                    <label for="id_email"><?php echo L_USER_MAIL ?>&nbsp;:</label>
                </div>
                <div>
                    <?php plxUtils::printInput('email', plxUtils::strCheck($plxAdmin->aUsers[$id]['email']), 'email', '30-255') ?>
                </div>
            </div>
            <label for="id_content"><?php echo L_INFOS ?>&nbsp;:</label>
            <?php plxUtils::printArea('content', plxUtils::strCheck($plxAdmin->aUsers[$id]['infos']), 0, 8) ?>
        </fieldset>
        <?php eval($plxAdmin->plxPlugins->callHook('AdminUser')) # Hook Plugins ?>

    </form>
</div>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminUserFoot'));
# On inclut le footer
include __DIR__ . '/foot.php';
?>
