<?php

/**
 * Edition du profil utilisateur
 *
 * @package PLX
 * @author    Stephane F, Pedro "P3ter" CADETE
 **/

include __DIR__ . '/prepend.php';

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
include __DIR__ . '/top.php';

$_profil = $plxAdmin->aUsers[$_SESSION['user']];
?>

    <form action="profil.php" method="post" id="form_profil">
        <div class="adminheader grid-6">
            <div class="col-2 mbm">
                <h2 class="h3-like"><?= L_PROFIL_EDIT_TITLE ?></h2>
                <input class="inbl btn--primary" type="submit" name="profil" role="button"
                       value="<?= L_PROFIL_UPDATE ?>"/>
            </div>
        </div>

        <?php eval($plxAdmin->plxPlugins->callHook('AdminProfilTop')) # Hook Plugins ?>

        <fieldset>
            <p class="inbl"><label><?= L_PROFIL_LOGIN ?>
                    &nbsp;:&nbsp;<strong><?= plxUtils::strCheck($_profil['login']) ?></strong></label></p>
            <div class="grid-2">
                <label for="id_name"><?php echo L_PROFIL_USER ?>&nbsp;:</label>
                <?php plxUtils::printInput('name', plxUtils::strCheck($_profil['name']), 'text', '20-255') ?>
                <label for="id_email"><?php echo L_USER_MAIL ?>&nbsp;:</label>
                <?php plxUtils::printInput('email', plxUtils::strCheck($_profil['email']), 'text', '30-255') ?>
                <label for="id_lang"><?php echo L_USER_LANG ?>&nbsp;:</label>
                <?php plxUtils::printSelect('lang', plxUtils::getLangs(), $_profil['lang']) ?>
            </div>
            <label for="id_content"><?php echo L_INFOS ?>&nbsp;:</label>
            <?php plxUtils::printArea('content', plxUtils::strCheck($_profil['infos']), 0, 5); ?>
        </fieldset>
        <?php eval($plxAdmin->plxPlugins->callHook('AdminProfil')) # Hook Plugins ?>
        <?php echo plxToken::getTokenPostMethod() ?>
    </form>

    <form action="profil.php" method="post" id="form_password">
        <fieldset>
            <h3><?php echo L_PROFIL_CHANGE_PASSWORD ?></h3>
            <div class="grid-2">
                <label for="id_password1"><?php echo L_PASSWORD ?>&nbsp;:</label>
                <?php plxUtils::printInput('password1', '', 'password', '20-255', false, '', '', 'onkeyup="pwdStrength(this.id)"') ?>
                <label for="id_password2"><?php echo L_CONFIRM_PASSWORD ?>&nbsp;:</label>
                <?php plxUtils::printInput('password2', '', 'password', '20-255') ?>
                <?php echo plxToken::getTokenPostMethod() ?>
            </div>
            <input class="btn--primary" type="submit" name="password" role="button" value="<?php echo L_PROFIL_UPDATE_PASSWORD ?>"/>
        </fieldset>
    </form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminProfilFoot'));
# On inclut le footer
include __DIR__ . '/foot.php';
?>