<?php
/**
 * Edition des utilisateurs
 *
 * @package PLX
 * @author	Stephane F.
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

$requireMail = boolval($plxAdmin->aConf['lostpassword']);
?>

<form action="parametres_users.php" method="post" id="form_users">

	<div class="inline-form action-bar">
		<h2><?= L_CONFIG_USERS_TITLE; ?></h2>
		<p>&nbsp;</p>
		<?php plxUtils::printSelect('selection', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, 'no-margin', 'id_selection') ?>
		<input type="submit" name="submit" value="<?= L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idUser[]', '<?= L_CONFIRM_DELETE ?>')" />
		<?= plxToken::getTokenPostMethod() ?>
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		<input type="submit" name="update" value="<?= L_CONFIG_USERS_UPDATE ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminUsersTop')); # Hook Plugins ?>

	<div class="scrollable-table">
	<table id="users-table" class="full-width">
	<thead>
		<tr>
			<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idUser[]')" /></th>
			<th title="<?= L_USER_LANG ?>"><?= L_ID ?></th>
			<th class="required"><?= L_PROFIL_USER ?></th>
			<th class="required"><?= L_PROFIL_LOGIN ?></th>
			<th><?= L_PASSWORD ?></th>
			<th<?= ($requireMail? ' class="required"': '') ?>><?= L_PROFIL_MAIL ?></th>
			<th><?= L_PROFIL ?></th>
			<th><?= L_CONFIG_USERS_ACTIVE ?></th>
			<th><?= L_LAST_CONNEXION_ON ?></th>
			<th><?= L_CONFIG_USERS_ACTION ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	# Initialisation de l'ordre
	$num = 0;
	if($plxAdmin->aUsers) {
		foreach($plxAdmin->aUsers as $_userid => $_user) {
			if (!empty($_user['delete'])) {
				continue;
			}
?>
		<tr>
			<td>
				<input type="checkbox" name="idUser[]" value="<?= $_userid ?>" />
			</td>
			<td><?= $_userid ?> <span class="flag" title="<?= $_user['lang'] ?>"><?= FLAGS[$_user['lang']] ?></span></td>
			<td><?php plxUtils::printInput('users[' . $_userid . '][name]', plxUtils::strCheck($_user['name']), 'text', '', false, '', '', '', true); ?></td>
			<td><?php plxUtils::printInput('users[' . $_userid . '][login]', plxUtils::strCheck($_user['login']), 'text', '', false, '', '', '', true); ?></td>
			<td><?php plxUtils::printInput('users[' . $_userid . '][password]', '', 'password', ''); ?></td>
			<td><?php plxUtils::printInput('users[' . $_userid . '][email]', plxUtils::strCheck($_user['email']), 'email', '', false, '', '', '', $requireMail); ?></td>
			<td>
<?php
			if($_userid=='001') {
				// plxUtils::printInput($_userid.'_profil', $_user['profil'], 'hidden');
				// plxUtils::printInput($_userid.'_active', $_user['active'], 'hidden');
				plxUtils::printSelect('users[' . $_userid . '][profil]', PROFIL_NAMES, $_user['profil'], true, 'readonly');
?>
			</td>
			<td><?php plxUtils::printSelect('users[' . $_userid . '][active]', array('1'=>L_YES,'0'=>L_NO), $_user['active'], true, 'readonly'); ?>
<?php
			} else {
				plxUtils::printSelect('users[' . $_userid . '][profil]', PROFIL_NAMES, $_user['profil']);
?>
			</td>
			<td><?php plxUtils::printSelect('users[' . $_userid . '][active]', array('1'=>L_YES,'0'=>L_NO), $_user['active']); ?>
<?php
			}
?>
			</td>
			<td><?= !empty($_user['last_connexion']) ? plxDate::formatDate($_user['last_connexion'], '#num_day/#num_month/#num_year(4) #time') : '' ?></td>
			<td><a href="user.php?p=<?= $_userid ?>"><?= L_OPTIONS ?></a></td>
		</tr>
<?php
		}
		# On récupère le dernier identifiant
		$a = array_keys($plxAdmin->aUsers);
		rsort($a);
	} else {
		$a['0'] = 0;
	}

	# newuser
	$new_userid = str_pad($a['0']+1, 3, '0', STR_PAD_LEFT);
?>
		<tr class="new" data-userid="<?= $new_userid ?>">
			<td colspan="2"><?= L_CONFIG_USERS_NEW ?></td>
			<td>
				<?php plxUtils::printInput('users[' . $new_userid . '][name]', '', 'text', ''); ?>
			</td>
			<td><?php plxUtils::printInput('users[' . $new_userid . '][login]', '', 'text', ''); ?></td>
			<td><?php plxUtils::printInput('users[' . $new_userid . '][password]', '', 'password', ''); ?></td>
			<td><?php plxUtils::printInput('users[' . $new_userid . '][email]', '', 'email', ''); ?></td>
			<td><?php plxUtils::printSelect('users[' . $new_userid . '][profil]', PROFIL_NAMES, PROFIL_WRITER); ?></td>
			<td><?php plxUtils::printSelect('users[' . $new_userid . '][active]', array('1'=>L_YES,'0'=>L_NO), '1'); ?></td>
			<td colspan="2">&nbsp;</td>
		</tr>
	</tbody>
	</table>
	</div>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminUsersFoot'));

# On inclut le footer
include 'foot.php';
