<?php

/**
 * Users creation and configuration
 * Part of core/admin/configuration.php
 * @author	Stephane F., Pedro "P3ter" CADETE"
 **/

use Pluxml\PlxToken;
use Pluxml\PlxUtils;

# Control du token du formulaire
PlxToken::validateFormToken($_POST);

# Edition des utilisateurs
if (!empty($_POST)) {
	$plxAdmin->editUsers($_POST);
	header('Location: configuration.php');
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

?>

<form action="configuration.php" method="post" id="form_users">
	<div class="autogrid panel-header">
		<h3 class="h4-like"><?= L_CONFIG_USERS_TITLE ?></h3>
		<div class="txtright">
			<input class="btn--primary" type="submit" value="<?= L_CONFIG_USERS_UPDATE ?>" />
			<?php PlxUtils::printSelect('selection', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, 'no-margin', 'id_selection') ?>
			<input type="submit" name="submit" value="<?= L_OK ?>"/>
			<?= PlxToken::getTokenPostMethod() ?>
		</div>
	</div>

	<div class="panel-content">

		<?php eval($plxAdmin->plxPlugins->callHook('AdminUsersTop')) # Hook Plugins ?>
	
		<table id="users-table">
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
			# Initialisation de l'ordre
			$num = 0;
			if($plxAdmin->aUsers) {
				foreach($plxAdmin->aUsers as $_userid => $_user)	{
					if (!$_user['delete']) {
						echo '<tr>';
						echo '<td><input type="checkbox" name="idUser[]" value="'.$_userid.'" /><input type="hidden" name="userNum[]" value="'.$_userid.'" /></td>';
						echo '<td>'.$_userid.'</td><td>';
						PlxUtils::printInput($_userid.'_name', PlxUtils::strCheck($_user['name']), 'text', '');
						echo '</td><td>';
						PlxUtils::printInput($_userid.'_login', PlxUtils::strCheck($_user['login']), 'text', '');
						echo '</td><td>';
						PlxUtils::printInput($_userid.'_password', '', 'password', '', false, '', '', 'onkeyup="pwdStrength(this.id)"');
						echo '</td><td>';
						PlxUtils::printInput($_userid.'_email', PlxUtils::strCheck($_user['email']), 'email', '');
						echo '</td><td>';
						if($_userid=='001') {
							PlxUtils::printInput($_userid.'_profil', $_user['profil'], 'hidden');
							PlxUtils::printInput($_userid.'_active', $_user['active'], 'hidden');
							PlxUtils::printSelect($_userid.'__profil', $aProfils, $_user['profil'], true, 'readonly');
							echo '</td><td>';
							PlxUtils::printSelect($_userid.'__active', array('1'=>L_YES,'0'=>L_NO), $_user['active'], true, 'readonly');
						} else {
							PlxUtils::printSelect($_userid.'_profil', $aProfils, $_user['profil']);
							echo '</td><td>';
							PlxUtils::printSelect($_userid.'_active', array('1'=>L_YES,'0'=>L_NO), $_user['active']);
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
					<td colspan="2"><?= L_CONFIG_USERS_NEW; ?></td>
					<td>
					<?php
						echo '<input type="hidden" name="userNum[]" value="'.$new_userid.'" />';
						PlxUtils::printInput($new_userid.'_newuser', 'true', 'hidden');
						PlxUtils::printInput($new_userid.'_name', '', 'text', '');
						PlxUtils::printInput($new_userid.'_infos', '', 'hidden');
						echo '</td><td>';
						PlxUtils::printInput($new_userid.'_login', '', 'text', '');
						echo '</td><td>';
						PlxUtils::printInput($new_userid.'_password', '', 'password', '', false, '', '', 'onkeyup="pwdStrength(this.id)"');
						echo '</td><td>';
						PlxUtils::printInput($new_userid.'_email', '', 'email', '');
						echo '</td><td>';
						PlxUtils::printSelect($new_userid.'_profil', $aProfils, PROFIL_WRITER);
						echo '</td><td>';
						PlxUtils::printSelect($new_userid.'_active', array('1'=>L_YES,'0'=>L_NO), '1');
						echo '</td>';
					?>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
	</div>
</form>