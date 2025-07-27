<?php

/**
 * Edition des catégories
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include 'prepend.php';

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
$aTri = array(
	'desc'		=> L_SORT_DESCENDING_DATE,
	'asc'		=> L_SORT_ASCENDING_DATE,
	'alpha'		=> L_SORT_ALPHABETICAL,
	'ralpha'	=> L_SORT_REVERSE_ALPHABETICAL,
	'random'	=> L_SORT_RANDOM
);

# On inclut le header
include 'top.php';
?>

<form action="categories.php" method="post" id="form_categories">

	<div class="inline-form action-bar">
		<h2><?= L_CAT_TITLE ?></h2>
		<p>&nbsp;</p>
		<?php plxUtils::printSelect('selection', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, 'no-margin', 'id_selection') ?>
		<input type="submit" name="submit" value="<?= L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idCategory[]', '<?= L_CONFIRM_DELETE ?>')" />
		<?= plxToken::getTokenPostMethod() ?>
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		<input type="submit" name="update" value="<?= L_CAT_APPLY_BUTTON ?>" />
	</div>

<?php eval($plxAdmin->plxPlugins->callHook('AdminCategoriesTop')); # Hook Plugins ?>

	<div class="scrollable-table">
		<table id="categories-table" class="full-width" data-rows-num='name$="_ordre"'>
			<thead>
				<tr>
					<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idCategory[]')" /></th>
					<th><?= L_ID ?> / <?= L_CAT_LIST_ARTS ?></th>
					<th class="required"><?= L_CAT_LIST_NAME ?></th>
					<th><?= L_CAT_LIST_URL ?></th>
					<th><?= L_EDITCAT_TEMPLATE ?></th>
					<th><?= L_CAT_LIST_ACTIVE ?></th>
					<th><?= L_CAT_LIST_SORT ?></th>
					<th><?= L_CAT_LIST_BYPAGE ?></th>
					<th data-id="order"><?= L_CAT_LIST_ORDER ?></th>
					<th><?= L_CAT_LIST_MENU ?></th>
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
?>
					<tr>
						<td><input type="checkbox" name="idCategory[]" value="<?= $k ?>" /><input type="hidden" name="catNum[]" value="<?=  $k ?>" /></td>
						<td><?= $k ?><span><?= $v['articles'] ?></span></td>
						<td>
							<?php plxUtils::printInput($k.'_name', plxUtils::strCheck($v['name']), 'text', '-50', false, '', '', '', true); ?>
						</td><td>
							<?php plxUtils::printInput($k.'_url', $v['url'], 'text', '-50'); ?>
						</td><td>
							<?php plxUtils::printSelect($k.'_template', $plxAdmin->getTemplatesTheme('categorie'), $v['template']); ?>
						</td><td>
							<?php plxUtils::printSelect($k.'_active', array('1'=>L_YES,'0'=>L_NO), $v['active']); ?>
						</td><td>
							<?php plxUtils::printSelect($k.'_tri', $aTri, $v['tri']); ?>
						</td><td>
							<?php plxUtils::printInput($k.'_bypage', $v['bypage'], 'text', '-3'); ?>
						</td><td>
							<?php plxUtils::printInput($k.'_ordre', $ordre, 'text', '-3'); ?>
						</td><td>
							<?php plxUtils::printSelect($k.'_menu', array('oui'=>L_DISPLAY,'non'=>L_HIDE), $v['menu']); ?>
						</td>
						<td><a href="categorie.php?p=<?= $k ?>"><?= L_OPTIONS ?></a></td>
					</tr>
<?php
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
					<td colspan="2"><?= L_NEW_CATEGORY ?></td>
					<td>
						<input type="hidden" name="catNum[]" value="<?= $new_catid ?>" />
						<?php plxUtils::printInput($new_catid.'_template', 'categorie.php', 'hidden'); ?>
						<?php plxUtils::printInput($new_catid.'_name', '', 'text', '-50'); ?>
					</td><td>
						<?php plxUtils::printInput($new_catid.'_url', '', 'text', '-50'); ?>
					</td><td>
						<?php plxUtils::printSelect($new_catid.'_template', $plxAdmin->getTemplatesTheme('categorie'), ''); ?>
					</td><td>
						<?php plxUtils::printSelect($new_catid.'_active', array('1'=>L_YES,'0'=>L_NO), '1'); ?>
					</td><td>
						<?php plxUtils::printSelect($new_catid.'_tri', $aTri, $plxAdmin->aConf['tri']); ?>
					</td><td>
						<?php plxUtils::printInput($new_catid.'_bypage', $plxAdmin->aConf['bypage'], 'text', '-3'); ?>
					</td><td>
						<?php plxUtils::printInput($new_catid.'_ordre', $ordre, 'text', '-3'); ?>
					</td><td>
						<?php plxUtils::printSelect($new_catid.'_menu', array('oui'=>L_DISPLAY,'non'=>L_HIDE), '1'); ?>
					</td>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
	</div>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoriesFoot'));

# On inclut le footer
include 'foot.php';
