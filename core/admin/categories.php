<?php

/**
 * Edition des catégories
 *
 * @package PLX
 * @author    Stephane F et Florent MONTHEL
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoriesPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_EDITOR);

# On édite les catégories
if (!empty($_POST)) {
    $plxAdmin->editCategories($_POST);
    header('Location: categories.php');
    exit;
}

# Tableau du tri
$sortList = array(
    'desc'		=> L_SORT_DESCENDING_DATE,
    'asc'		=> L_SORT_ASCENDING_DATE,
    'alpha'		=> L_SORT_ALPHABETICAL,
    'ralpha'	=> L_SORT_REVERSE_ALPHABETICAL,
    'random'	=> L_SORT_RANDOM
);

# On inclut le header
include 'top.php';
?>

<div class="adminheader">
    <h2 class="h3-like"><?= L_CAT_TITLE ?></h2>
    <p><a class="back" href="articles.php"><?= L_BACK_TO_ARTICLES ?></a></p>
</div>

<div class="admin">
    <form id="form_categories" method="post" id="form_categories" data-chk="idCategory[]">
        <div class="tableheader has-spacer">
            <?= PlxToken::getTokenPostMethod() ?>
            <button class="btn--primary" name="update"><?= L_SAVE ?></button>
            <span class="spacer">&nbsp;</span>
			<button class="submit btn--warning" name="delete" data-lang="<?= L_CONFIRM_DELETE ?>" disabled><i class="icon-trash"></i><?= L_DELETE ?></button>
        </div>

<?php eval($plxAdmin->plxPlugins->callHook('AdminCategoriesTop')) # Hook Plugins ?>
		<div class="scrollable-table">
	        <table id="categories-table" class="table mb0" data-rows-num='name^="order"'>
	            <thead>
		            <tr>
		                <th class="checkbox"><input type="checkbox" /></th>
		                <th>#</th>
		                <th><?= L_CAT_LIST_NAME ?></th>
		                <th><?= L_URL ?></th>
		                <th><?= L_ACTIVE ?></th>
		                <th><?= L_ARTICLES_SORT ?></th>
		                <th><?= L_CAT_LIST_BYPAGE ?></th>
		                <th data-id="order"><?= L_ORDER ?></th>
		                <th><?= L_MENU ?></th>
		                <th>&nbsp;</th>
		            </tr>
	            </thead>
	            <tbody>
<?php

if ($plxAdmin->aCats) {
	$order = 1;

	# On boucle sur les categories.
	foreach ($plxAdmin->aCats as $catId => $v) {
		$id = 'id_' . $catId;
?>
					<tr>
	                    <td>
							<input type="checkbox" name="idCategory[]" value="<?= $catId ?>" id="<?= $id ?>" />
						</td>
	                    <td><label for="<?= $id ?>"><?= $catId ?></label></td>
	                    <td><input type="text" name="name[<?= $catId ?>]" value="<?= PlxUtils::strCheck($v['name']) ?>" maxlength="50" required /></td>
	                    <td><input type="text" name="url[<?= $catId ?>]" value="<?= PlxUtils::strCheck($v['url']) ?>" maxlength="50" /></td>
	                    <td><input type="checkbox" name="active[<?= $catId ?>]" value="1" class="switch" <?= !empty($v['active']) ? 'checked' : '' ?> /></td>
	                    <td><?php PlxUtils::printSelect('tri[' . $catId . ']', $sortList, $v['tri']); ?></td>
	                    <td><input type="number" name="bypage[<?= $catId ?>]" value="<?= $v['bypage'] ?>" maxlength="3" /></td>
	                    <td><input type="number" name="order[<?= $catId ?>]" value="<?= $order ?>" maxlength="3" /></td>
	                    <td><input type="checkbox" name="menu[<?= $catId ?>]" value="1" class="switch" <?= !empty($v['menu']) ? 'checked' : '' ?> /></td>
	                    <td><button><a href="categorie.php?p=<?= $catId ?>"><i class="icon-cog-1"></i></a></button></td>
					</tr>
<?php
		$order++;
	}

	# On récupère le dernier identifiant
    $catIds = array_keys($plxAdmin->aCats);
    rsort($catIds);
} else {
	$catIds = array(0);
}

$newCatId = str_pad($catIds[0] + 1, 3, "0", STR_PAD_LEFT);
?>
		            <tr class="new">
		                <td colspan="2"><?= L_NEW_CATEGORY ?></td>
	                    <td><input type="text" name="name[<?= $newCatId ?>]" value="" maxlength="50" /></td><?php /* not required for a new item */ ?>
	                    <td><input type="text" name="url[<?= $newCatId ?>]" value="" maxlength="50" /></td>
	                    <td><input type="checkbox" name="active[<?= $newCatId ?>]" value="1" class="switch" /></td>
	                    <td><?php PlxUtils::printSelect('tri[' . $newCatId . ']', $sortList, $plxAdmin->aConf['tri']); ?></td>
	                    <td><input type="number" name="bypage[<?= $newCatId ?>]" value="<?= $plxAdmin->aConf['bypage'] ?>" maxlength="3" /></td>
	                    <td><input type="number" name="order[<?= $newCatId ?>]" value="<?= $order ?>" maxlength="3" /></td>
	                    <td><input type="checkbox" name="menu[<?= $newCatId ?>]" value="1" class="switch" /></td>
	                    <td>&nbsp;</td>
		            </tr>
	            </tbody>
	        </table>
		</div>
    </form>
</div>
<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoriesFoot'));

# On inclut le footer
include 'foot.php';
