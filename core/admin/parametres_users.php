<?php
/**
 * Edition des utilisateurs
 *
 * @package PLX
 * @author    Stephane F.
 **/

include __DIR__ . '/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# Edition des utilisateurs
if (!empty($_POST)) {
    $plxAdmin->editUsers($_POST);
    header('Location: parametres_users.php');
    exit;
}

# On inclut le header
include __DIR__ . '/top.php';
?>

    <div class="adminheader">
        <h2 class="h3-like"><?= L_CONFIG_USERS_TITLE; ?></h2>
    </div>

    <div class="admin">
        <form method="post" id="form_users" data-chk="idUser[]">
            <?php eval($plxAdmin->plxPlugins->callHook('AdminUsersTop')) # Hook Plugins ?>
            <div class="mtm pas tableheader">
                <?= PlxToken::getTokenPostMethod() ?>
                <input class="btn--primary" type="submit" name="update" value="<?= L_CONFIG_USERS_UPDATE ?>"/>
            </div>
            <div class="scrollable-table">
                <table id="users-table" class="table mb0">
                    <thead>
                    <tr>
                        <th class="checkbox"><input type="checkbox"/></th>
                        <th>#</th>
                        <th><?= L_PROFIL_USER ?></th>
                        <th><?= L_PROFIL_LOGIN ?></th>
                        <th><?= L_PASSWORD ?></th>
                        <th><?= L_MAIL_ADDRESS ?></th>
                        <th><?= L_PROFIL ?></th>
                        <th><?= L_CONFIG_USERS_ACTIVE ?></th>
                        <th><?= L_ACTION ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    # Initialisation de l'ordre
                    $num = 0;
                    if ($plxAdmin->aUsers) {
                        foreach ($plxAdmin->aUsers as $_userid => $_user) {
                            if (!$_user['delete']) {
                                $readonly = ($_userid == '001');
                                $className = ($_userid == '001') ? 'readonly' : '';
                                ?>
                                <tr>
                                    <td><input type="checkbox" name="idUser[]" value="<?= $_userid ?>"/><input
                                                type="hidden" name="userNum[]" value="<?= $_userid ?>"/></td>
                                    <td><?= $_userid ?></td>
                                    <td><?php plxUtils::printInput($_userid . '_name', plxUtils::strCheck($_user['name']), 'text', ''); ?></td>
                                    <td><?php plxUtils::printInput($_userid . '_login', plxUtils::strCheck($_user['login']), 'text', ''); ?></td>
                                    <td><?php plxUtils::printInput($_userid . '_password', '', 'password', '', false, '', '', 'autocomplete="new-password" onkeyup="pwdStrength(this.id)"'); ?></td>
                                    <td><?php plxUtils::printInput($_userid . '_email', plxUtils::strCheck($_user['email']), 'email', ''); ?></td>
                                    <td><?php plxUtils::printSelect($_userid . '_profil', PROFIL_NAMES, $_user['profil'], $readonly, $className); ?></td>
                                    <td><?php plxUtils::printSelect($_userid . '_active', array('1' => L_YES, '0' => L_NO), $_user['active'], $readonly, $className); ?></td>
                                    <td>
                                        <button><a href="user.php?p=<?= $_userid ?>"><i class="icon-cog-1"></i></a>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                        }

                        # On récupère le dernier identifiant
                        $a = array_keys($plxAdmin->aUsers);
                        rsort($a);
                    } else {
                        $a['0'] = 0;
                    }

                    $new_userid = str_pad($a['0'] + 1, 3, "0", STR_PAD_LEFT);
                    ?>
                    <tr class="new">
                        <td colspan="2">
                            <?= L_CONFIG_USERS_NEW; ?>
                            <input type="hidden" name="userNum[]" value="<?= $new_userid ?>"/>
                            <?php plxUtils::printInput($new_userid . '_newuser', 'true', 'hidden'); ?>
                            <?php plxUtils::printInput($new_userid . '_infos', '', 'hidden'); ?>
                        </td>
                        <td>
                            <?php plxUtils::printInput($new_userid . '_name', '', 'text', ''); ?>
                        </td>
                        <td><?php plxUtils::printInput($new_userid . '_login', '', 'text', ''); ?></td>
                        <td><?php plxUtils::printInput($new_userid . '_password', '', 'password', '', false, '', '', 'onkeyup="pwdStrength(this.id)"'); ?></td>
                        <td><?php plxUtils::printInput($new_userid . '_email', '', 'email', ''); ?></td>
                        <td><?php plxUtils::printSelect($new_userid . '_profil', PROFIL_NAMES, PROFIL_WRITER); ?></td>
                        <td><?php plxUtils::printSelect($new_userid . '_active', array('1' => L_YES, '0' => L_NO), '1'); ?></td>
                        <td>&nbsp;</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="pas tablefooter">
                <button class="submit btn--warning" name="delete" data-lang="<?= L_CONFIRM_DELETE ?>" disabled><i
                            class="icon-trash"></i><?= L_DELETE ?></button>
            </div>
        </form>
    </div>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminUsersFoot'));
# On inclut le footer
include __DIR__ . '/foot.php';
