<?php

/**
 * Edition des catégories
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoriesPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR, PROFIL_EDITOR);

# On édite les catégories
if(!empty($_POST)) {
	$plxAdmin->editCategories($_POST);
	header('Location: categories.php');
	exit;
}

# Tableau du tri
$aTri = array('desc'=>L_SORT_DESCENDING_DATE, 'asc'=>L_SORT_ASCENDING_DATE, 'alpha'=>L_SORT_ALPHABETICAL,'random'=>L_SORT_RANDOM);

# On inclut le header
include(dirname(__FILE__).'/top.php');
$yes_no = array('1'=>L_YES,'0'=>L_NO);
$display_hide = array('oui'=>L_DISPLAY,'non'=>L_HIDE);
?>

<form action="categories.php" method="post" id="form_categories">

	<div class="inline-form action-bar">
		<h2><?php echo L_CAT_TITLE ?></h2>
		<p><a class="back" href="index.php"><?php echo L_BACK_TO_ARTICLES ?></a></p>
		<div class="flex-line">
			<?php plxUtils::printSelect('selection', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, 'no-margin', 'id_selection') ?>
			<input type="submit" name="submit" value="<?php echo L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idCategory[]', '<?php echo L_CONFIRM_DELETE ?>')" />
			<?php echo plxToken::getTokenPostMethod() ?>
			<span class="spacer">&nbsp;</span>
			<input type="submit" name="update" value="<?php echo L_CAT_APPLY_BUTTON ?>" />
		</div>
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminCategoriesTop')) # Hook Plugins ?>

	<div class="scrollable-table">
		<table id="categories-table" class="full-width">
			<thead>
				<tr>
					<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idCategory[]')" /></th>
					<th><?php echo L_ID ?></th>
					<th><?php echo L_CAT_LIST_NAME ?></th>
					<th><?php echo L_CAT_LIST_URL ?></th>
					<th><?php echo L_CAT_LIST_ACTIVE ?></th>
					<th><?php echo L_CAT_LIST_SORT ?></th>
					<th><?php echo L_CAT_LIST_BYPAGE ?></th>
					<th data-id="order"><?php echo L_CAT_LIST_ORDER ?></th>
					<th><?php echo L_CAT_LIST_MENU ?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			<?php
			# Initialisation de l'ordre
			$ordre = 0;
			# Si on a des catégories
			if($plxAdmin->aCats) {
				foreach($plxAdmin->aCats as $catId=>$infos) { # Pour chaque catégorie
					$ordre++;
?>
				<tr>
					<td>
						<input type="checkbox" name="idCategory[]" value="<?php echo $catId; ?>" />
						<input type="hidden" name="catNum[]" value="<?php echo $catId; ?>" />
					</td>
					<td><?php echo $catId; ?></td>
					<td><?php plxUtils::printInput($catId.'_name', plxUtils::strCheck($infos['name']), 'text', '-50'); ?></td>
					<td><?php plxUtils::printInput($catId.'_url', $infos['url'], 'text', '-50'); ?></td>
					<td><?php plxUtils::printSelect($catId.'_active', $yes_no, $infos['active']); ?></td>
					<td><?php plxUtils::printSelect($catId.'_tri', $aTri, $infos['tri']); ?></td>
					<td><?php plxUtils::printInput($catId.'_bypage', $infos['bypage'], 'text', '-3'); ?></td>
					<td><?php plxUtils::printInput($catId.'_ordre', $ordre, 'text', '-3'); ?></td>
					<td><?php plxUtils::printSelect($catId.'_menu', $display_hide, $infos['menu']); ?></td>
					<td><a href="categorie.php?p=<?php echo $catId; ?>"><?php echo L_OPTIONS; ?></a></td>
				</tr>
<?php
				}
				# On récupère le dernier identifiant
				$a = array_keys($plxAdmin->aCats);
				rsort($a);
			} else {
				$a['0'] = 0;
			}
			$new_catId = str_pad($a['0']+1, 3, "0", STR_PAD_LEFT);
			$ordre++;
?>
				<tr class="new"><?php /* Nouvelle catégorie */ ?>
					<td colspan="2"><?php echo L_NEW_CATEGORY ?></td>
					<td>
						<input type="hidden" name="catNum[]" value="<?php echo $new_catId; ?>" />
						<?php plxUtils::printInput($new_catId.'_template', 'categorie.php', 'hidden'); ?>
						<?php plxUtils::printInput($new_catId.'_name', '', 'text', '-50'); ?>
					</td>
					<td><?php plxUtils::printInput($new_catId.'_url', '', 'text', '-50'); ?></td>
					<td><?php plxUtils::printSelect($new_catId.'_active', $yes_no, 1); ?></td>
					<td><?php plxUtils::printSelect($new_catId.'_tri', $aTri, $plxAdmin->aConf['tri']); ?></td>
					<td><?php plxUtils::printInput($new_catId.'_bypage', $plxAdmin->aConf['bypage'], 'text', '-3'); ?></td>
					<td><?php plxUtils::printInput($new_catId.'_ordre', $ordre, 'text', '-3'); ?></td>
					<td><?php plxUtils::printSelect($new_catId.'_menu', $display_hide, 1); ?></td>
					<td>&nbsp;</a></td>
				</tr>
			</tbody>
		</table>
	</div>

</form>
<script type="text/javascript">
	dragAndDrop('#categories-table tbody tr:not(.new)', '#categories-table tbody tr:not(.new) input[name$="_ordre"]');
</script>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoriesFoot'));

# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>