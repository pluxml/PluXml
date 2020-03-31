<?php
/**
 * Edition des utilisateurs
 *
 * @package PLX
 * @author	Stephane F.
 **/

include __DIR__ .'/prepend.php';

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
include __DIR__ .'/top.php';
?>

<form action="parametres_users.php" method="post" id="form_users">

	<div class="inline-form action-bar">
		<h2><?php echo L_CONFIG_USERS_TITLE; ?></h2>
		<p>&nbsp;</p>
		<?php plxUtils::printSelect('selection', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, 'no-margin', 'id_selection') ?>
		<input type="submit" name="submit" value="<?php echo L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idUser[]', '<?php echo L_CONFIRM_DELETE ?>')" />
		<?php echo plxToken::getTokenPostMethod() ?>
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		<input type="submit" name="update" value="<?php echo L_CONFIG_USERS_UPDATE ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminUsersTop')) # Hook Plugins ?>

	<div class="scrollable-table">
	<table id="users-table" class="full-width">
	<thead>
		<tr>
			<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idUser[]')" /></th>
			<th><?php echo L_ID ?></th>
			<th><?php echo L_PROFIL_USER ?></th>
			<th><?php echo L_PROFIL_LOGIN ?></th>
			<th><?php echo L_PROFIL_PASSWORD ?></th>
			<th><?php echo L_PROFIL_MAIL ?></th>
			<th><?php echo L_PROFIL ?></th>
			<th><?php echo L_CONFIG_USERS_ACTIVE ?></th>
			<th><?php echo L_CONFIG_USERS_ACTION ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	# Initialisation de l'ordre
	$num = 0;
	if($plxAdmin->aUsers) {
		foreach($plxAdmin->aUsers as $_userid => $_user)	{
			if (!$_user['delete']) {
				echo '<tr>';
				echo '<td><input type="checkbox" name="idUser[]" value="'.$_userid.'" /><input type="hidden" name="userNum[]" value="'.$_userid.'" /></td>';
				echo '<td>'.$_userid.'</td><td>';
				plxUtils::printInput($_userid.'_name', plxUtils::strCheck($_user['name']), 'text', '');
				echo '</td><td>';
				plxUtils::printInput($_userid.'_login', plxUtils::strCheck($_user['login']), 'text', '');
				echo '</td><td>';
				plxUtils::printInput($_userid.'_password', '', 'password', '', false, '', '', 'onkeyup="pwdStrength(this.id)"');
				echo '</td><td>';
				plxUtils::printInput($_userid.'_email', plxUtils::strCheck($_user['email']), 'email', '');
				echo '</td><td>';
				if($_userid=='001') {
					plxUtils::printInput($_userid.'_profil', $_user['profil'], 'hidden');
					plxUtils::printInput($_userid.'_active', $_user['active'], 'hidden');
					plxUtils::printSelect($_userid.'__profil', PROFIL_NAMES, $_user['profil'], true, 'readonly');
					echo '</td><td>';
					plxUtils::printSelect($_userid.'__active', array('1'=>L_YES,'0'=>L_NO), $_user['active'], true, 'readonly');
				} else {
					plxUtils::printSelect($_userid.'_profil', PROFIL_NAMES, $_user['profil']);
					echo '</td><td>';
					plxUtils::printSelect($_userid.'_active', array('1'=>L_YES,'0'=>L_NO), $_user['active']);
				}
				echo '</td>';
				echo '<td><a href="user.php?p='.$_userid.'">'.L_OPTIONS.'</a></td>';
				echo '</tr>';
			}
		}
		# On récupère le dernier identifiant
		$a = array_keys($plxAdmin->aUsers);
		rsort($a);
	} else {
		$a['0'] = 0;
	}
	$new_userid = str_pad($a['0']+1, 3, "0", STR_PAD_LEFT);
	?>
		<tr class="new">
			<td colspan="2"><?php echo L_CONFIG_USERS_NEW; ?></td>
			<td>
			<?php
				echo '<input type="hidden" name="userNum[]" value="'.$new_userid.'" />';
				plxUtils::printInput($new_userid.'_newuser', 'true', 'hidden');
				plxUtils::printInput($new_userid.'_name', '', 'text', '');
				plxUtils::printInput($new_userid.'_infos', '', 'hidden');
				echo '</td><td>';
				plxUtils::printInput($new_userid.'_login', '', 'text', '');
				echo '</td><td>';
				plxUtils::printInput($new_userid.'_password', '', 'password', '', false, '', '', 'onkeyup="pwdStrength(this.id)"');
				echo '</td><td>';
				plxUtils::printInput($new_userid.'_email', '', 'email', '');
				echo '</td><td>';
				plxUtils::printSelect($new_userid.'_profil', PROFIL_NAMES, PROFIL_WRITER);
				echo '</td><td>';
				plxUtils::printSelect($new_userid.'_active', array('1'=>L_YES,'0'=>L_NO), '1');
				echo '</td>';
			?>
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
include __DIR__ .'/foot.php';
?>
