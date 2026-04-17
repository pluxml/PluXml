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

if($plxAdmin->aUsers) {
	# On récupère le dernier identifiant
	$a = array_keys($plxAdmin->aUsers);
	rsort($a);
} else {
	$a['0'] = 0;
}
$new_userid = str_pad($a['0']+1, 3, '0', STR_PAD_LEFT);

# Edition des utilisateurs
if (!empty($_POST)) {
	if(!$plxAdmin->editUsers($_POST)) {
		$_SESSION['new_user'] = $_POST['users'][$new_userid];
	} else {
		unset($_SESSION['new_user']);
	}
	header('Location: parametres_users.php');
	exit;
}

# Tableau des profils
$aProfils = array(
	PROFIL_ADMIN => L_PROFIL_ADMIN,
	PROFIL_MANAGER => L_PROFIL_MANAGER,
	PROFIL_MODERATOR => L_PROFIL_MODERATOR,
	PROFIL_EDITOR => L_PROFIL_EDITOR,
	PROFIL_WRITER => L_PROFIL_WRITER
);

# On inclut le header
include 'top.php';

$inputs = array('fullname', 'login', 'password', 'email');
?>

<form method="post" id="form_users">

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
			<th><?= L_ID ?></th>
			<th><?= L_PROFIL_USER ?></th>
			<th><?= L_PROFIL_LOGIN ?></th>
			<th><?= L_PROFIL_PASSWORD ?></th>
			<th><?= L_PROFIL_MAIL ?></th>
			<th><?= L_PROFIL ?></th>
			<th><?= L_CONFIG_USERS_ACTIVE ?></th>
			<th><?= L_CONFIG_USERS_ACTION ?></th>
		</tr>
	</thead>
	<tbody>
<?php
	if($plxAdmin->aUsers) {
		foreach($plxAdmin->aUsers as $_userid => $infos) {
			if (!empty($infos['delete'])) {
				continue;
			}

			$user = 'users[' . $_userid . ']';
?>
		<tr>
			<td>
				<input type="checkbox" name="idUser[]" value="<?= $_userid ?>" />
			</td>
			<td><?= $_userid ?></td>
<?php
	foreach($inputs as $f) {
		$name = $user . '[' . $f . ']';
		switch($f) {
			case 'fullname' : $value = plxUtils::strCheck($infos['name']); $required = true; break;
			case 'password' : $value = ''; $required = false; break;
			default : $value = plxUtils::strCheck($infos[$f]); $required = true;
		}
		$type = in_array($f, array('password', 'email',)) ? $f : 'text';
?>
			<td><?php plxUtils::printInput($name, $value, $type, '', false, '', '', '', $required); ?></td>
<?php
	}
?>
			<td>
<?php

			if($_userid=='001') {
				plxUtils::printSelect($user . '[profil]', $aProfils, $infos['profil'], true, 'readonly');
?>
			</td>
			<td><?php plxUtils::printSelect($user . '[active]', array('1'=>L_YES,'0'=>L_NO), $infos['active'], true, 'readonly'); ?>
<?php
			} else {
				plxUtils::printSelect($user . '[profil]', $aProfils, $infos['profil']);
?>
			</td>
			<td><?php plxUtils::printSelect($user . '[active]', array('1'=>L_YES,'0'=>L_NO), $infos['active']); ?>
<?php
			}
?>
			</td>
			<td><a href="user.php?p=<?= $_userid ?>"><?= L_OPTIONS ?></a></td>
		</tr>
<?php
		}
	}

	# newuser
	$user = 'users[' . $new_userid . ']';
?>
		<tr class="new">
			<td colspan="2"><?= L_CONFIG_USERS_NEW ?></td>
<?php
	foreach($inputs as $f) {
		$name = $user . '[' . $f . ']';
		$value = ($f !='password' and !empty($_SESSION['new_user'][$f])) ? $_SESSION['new_user'][$f] : '';
		$type = in_array($f, array('password', 'email',)) ? $f : 'text';
?>
			<td><?php plxUtils::printInput($name, $value, $type, ''); ?></td>
<?php
	}

	unset($_SESSION['new_user']);
?>
			<td><?php plxUtils::printSelect($user . '[profil]', $aProfils, PROFIL_WRITER); ?></td>
			<td><?php plxUtils::printSelect($user . '[active]', array('1'=>L_YES,'0'=>L_NO), '1'); ?></td>
			<td>&nbsp;</td>
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
