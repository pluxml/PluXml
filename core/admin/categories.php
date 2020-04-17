<?php

/**
 * Edition des catégories
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include __DIR__ .'/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoriesPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_EDITOR);

# On édite les catégories
if(!empty($_POST)) {
	$plxAdmin->editCategories($_POST);
	header('Location: categories.php');
	exit;
}

# Tableau du tri
$aTri = array(
	'desc'		=> L_SORT_DESCENDING_DATE,
	'asc'		=> L_SORT_ASCENDING_DATE,
	'alpha'		=> L_SORT_ALPHABETICAL,
	'ralpha'	=> L_SORT_REVERSE_ALPHABETICAL,
	'random'	=> L_SORT_RANDOM
);

# On inclut le header
include __DIR__ .'/top.php';
?>

<form action="categories.php" method="post" id="form_categories">

	<div class="inline-form action-bar">
		<h2><?php echo L_CAT_TITLE ?></h2>
		<p><a class="back" href="index.php"><?php echo L_BACK_TO_ARTICLES ?></a></p>
		<?php plxUtils::printSelect('selection', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, 'no-margin', 'id_selection') ?>
		<input type="submit" name="submit" value="<?php echo L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idCategory[]', '<?php echo L_CONFIRM_DELETE ?>')" />
		<?php echo plxToken::getTokenPostMethod() ?>
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		<input type="submit" name="update" value="<?php echo L_CAT_APPLY_BUTTON ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminCategoriesTop')) # Hook Plugins ?>

	<div class="scrollable-table">
		<table id="categories-table" class="full-width" data-rows-num='name$="_ordre"'>
			<thead>
				<tr>
					<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idCategory[]')" /></th>
					<th>#</th>
					<th><?php echo L_CAT_LIST_NAME ?></th>
					<th><?php echo L_URL ?></th>
					<th><?= L_ACTIVE ?></th>
					<th><?php echo L_ARTICLES_SORT ?></th>
					<th><?php echo L_CAT_LIST_BYPAGE ?></th>
					<th data-id="order"><?php echo L_ORDER ?></th>
					<th><?php echo L_MENU ?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			<?php
			# Initialisation de l'ordre
			$ordre = 1;
			# Si on a des catégories
			if($plxAdmin->aCats) {
				foreach($plxAdmin->aCats as $k=>$v) { # Pour chaque catégorie
					echo '<tr>';
					echo '<td><input type="checkbox" name="idCategory[]" value="'.$k.'" /><input type="hidden" name="catNum[]" value="'.$k.'" /></td>';
					echo '<td>'.$k.'</td><td>';
					plxUtils::printInput($k.'_name', plxUtils::strCheck($v['name']), 'text', '-50');
					echo '</td><td>';
					plxUtils::printInput($k.'_url', $v['url'], 'text', '-50');
					echo '</td><td>';
					plxUtils::printSelect($k.'_active', array('1'=>L_YES,'0'=>L_NO), $v['active']);
					echo '</td><td>';
					plxUtils::printSelect($k.'_tri', $aTri, $v['tri']);
					echo '</td><td>';
					plxUtils::printInput($k.'_bypage', $v['bypage'], 'text', '-3');
					echo '</td><td>';
					plxUtils::printInput($k.'_ordre', $ordre, 'text', '-3');
					echo '</td><td>';
					plxUtils::printSelect($k.'_menu', array('oui'=>L_DISPLAY,'non'=>L_HIDE), $v['menu']);
					echo '</td>';
					echo '<td><a href="categorie.php?p='.$k.'">'.L_OPTIONS.'</a></td>';
					echo '</tr>';
					$ordre++;
				}
				# On récupère le dernier identifiant
				$a = array_keys($plxAdmin->aCats);
				rsort($a);
			} else {
				$a['0'] = 0;
			}
			$new_catid = str_pad($a['0']+1, 3, "0", STR_PAD_LEFT);
			?>
				<tr class="new">
					<td colspan="2"><?php echo L_NEW_CATEGORY ?></td>
					<td>
					<?php
						echo '<input type="hidden" name="catNum[]" value="'.$new_catid.'" />';
						plxUtils::printInput($new_catid.'_template', 'categorie.php', 'hidden');
						plxUtils::printInput($new_catid.'_name', '', 'text', '-50');
						echo '</td><td>';
						plxUtils::printInput($new_catid.'_url', '', 'text', '-50');
						echo '</td><td>';
						plxUtils::printSelect($new_catid.'_active', array('1'=>L_YES,'0'=>L_NO), '1');
						echo '</td><td>';
						plxUtils::printSelect($new_catid.'_tri', $aTri, $plxAdmin->aConf['tri']);
						echo '</td><td>';
						plxUtils::printInput($new_catid.'_bypage', $plxAdmin->aConf['bypage'], 'text', '-3');
						echo '</td><td>';
						plxUtils::printInput($new_catid.'_ordre', $ordre, 'text', '-3');
						echo '</td><td>';
						plxUtils::printSelect($new_catid.'_menu', array('oui'=>L_DISPLAY,'non'=>L_HIDE), '1');
						echo '</td><td>&nbsp;';
					?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoriesFoot'));
# On inclut le footer
include __DIR__ .'/foot.php';
?>
