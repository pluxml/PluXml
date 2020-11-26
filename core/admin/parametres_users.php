<?php
/**
 * Edition des utilisateurs
 *
 * @package PLX
 * @author    Stephane F.
 **/

include 'prepend.php';

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
include 'top.php';
?>

<div class="adminheader">
    <h2 class="h3-like"><?= L_CONFIG_USERS_TITLE; ?></h2>
</div>

<div class="admin">
    <form method="post" id="form_users" data-chk="idUser[]">
        <?php eval($plxAdmin->plxPlugins->callHook('AdminUsersTop')) # Hook Plugins ?>
        <div class="tableheader has-spacer">
            <?= PlxToken::getTokenPostMethod() ?>
            <input class="btn--primary" type="submit" name="update" value="<?= L_CONFIG_USERS_UPDATE ?>"/>
            <span class="spacer">&nbsp;</span>
 			<button class="submit btn--warning" name="delete" data-lang="<?= L_CONFIRM_DELETE ?>" disabled><i class="icon-trash"></i><?= L_DELETE ?></button>
       </div>
        <div class="scrollable-table">
            <table id="users-table" class="table mb0">
                <thead>
                <tr>
                    <th class="checkbox"><input type="checkbox" /></th>
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
if ($plxAdmin->aUsers) {
	foreach ($plxAdmin->aUsers as $userId => $infos) {
		$id = 'id_' . $userId;
		if (!$infos['delete']) {
			$readonly = ($userId == '001');
?>
					<tr>
						<td><input type="checkbox" name="idUser[]" value="<?= $userId ?>" id="<?= $id ?>" /></td>
						<td><label for="<?= $id ?>"><?= $userId ?></label></td>
						<td><input type="text" name="name[<?= $userId ?>]" value="<?= plxUtils::strCheck($infos['name']) ?>" maxlength="32" required /></td>
						<td><input type="text" name="login[<?= $userId ?>]" value="<?= plxUtils::strCheck($infos['login']) ?>" maxlength="32" required /></td>
						<td><?php plxUtils::printInput('password[' . $userId . ']', '', 'password', '', false, '', '', 'autocomplete="new-password" onkeyup="pwdStrength(this.id)"'); ?></td>
						<td><input type="email" name="email[<?= $userId ?>]" value="<?= plxUtils::strCheck($infos['email']) ?>" maxlength="64" /></td>
						<td>
<?php plxUtils::printSelect('profil[' . $userId . ']', PROFIL_NAMES, $infos['profil'], $readonly); ?>
						</td>
						<td><input type="checkbox" name="active[<?= $userId ?>]" value="1" <?= !empty($infos['active']) ? 'checked' : '' ?> class="switch" <?= $readonly ? 'disabled' : '' ?> /></td>
						<td><button><a href="user.php?p=<?= $userId ?>"><i class="icon-cog-1"></i></a></button></td>
					</tr>
<?php
		}
	}

	# On récupère le dernier identifiant
	$a = array_keys($plxAdmin->aUsers);
	rsort($a);
} else {
	$a = array(0);
}

$newUserId = str_pad($a[0] + 1, 3, '0', STR_PAD_LEFT);
?>
	                <tr class="new">
	                    <td colspan="2"><?= L_CONFIG_USERS_NEW; ?></td>
						<td><input type="text" name="name[<?= $newUserId ?>]" value="" maxlength="32" /></td>
						<td><input type="text" name="login[<?= $newUserId ?>]" value="" maxlength="32" /></td>
						<td><?php plxUtils::printInput('password[' . $newUserId . ']', '', 'password', '', false, '', '', 'autocomplete="new-password" onkeyup="pwdStrength(this.id)"'); ?></td>
						<td><input type="email" name="email[<?= $newUserId ?>]" value="" maxlength="64" /></td>
						<td>
<?php plxUtils::printSelect('profil[' . $newUserId . ']', PROFIL_NAMES, $infos['profil']); ?>
						</td>
						<td><input type="checkbox" name="active[<?= $newUserId ?>]" value="1" class="switch" /></td>
	                    <td>&nbsp;</td>
	                </tr>
                </tbody>
            </table>
        </div>
    </form>
</div>

<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminUsersFoot'));

# On inclut le footer
include 'foot.php';
