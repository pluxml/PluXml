<?php

/**
 * Edition des catégories
 *
 * @package PLX
 * @author    Stephane F et Florent MONTHEL
 **/

include __DIR__ . '/prepend.php';

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
$aTri = array(
    'desc' => L_SORT_DESCENDING_DATE,
    'asc' => L_SORT_ASCENDING_DATE,
    'alpha' => L_SORT_ALPHABETICAL,
    'ralpha' => L_SORT_REVERSE_ALPHABETICAL,
    'random' => L_SORT_RANDOM
);

# On inclut le header
include __DIR__ . '/top.php';
?>

<div class="adminheader">
    <h2 class="h3-like"><?= L_CAT_TITLE ?></h2>
    <p><a class="back" href="articles.php"><?= L_BACK_TO_ARTICLES ?></a></p>
</div>

<div class="admin">
    <form action="categories.php" method="post" id="form_categories">
        <div class="mtm pas tableheader">
            <?= PlxToken::getTokenPostMethod() ?>
            <!--<input type="submit" name="update" value="<?= L_CAT_APPLY_BUTTON ?>" />-->
            <button class="btn--primary" type="submit"><?= L_CAT_APPLY_BUTTON ?></button>
        </div>

        <?php eval($plxAdmin->plxPlugins->callHook('AdminCategoriesTop')) # Hook Plugins ?>

        <table id="categories-table" class="table" data-rows-num='name$="_ordre"'>
            <thead>
            <tr>
                <th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idCategory[]')"/></th>
                <th>#</th>
                <th class="w100"><?= L_CAT_LIST_NAME ?></th>
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
            <?php $ordre = 1; ?>
            <?php if ($plxAdmin->aCats): ?>
                <?php foreach ($plxAdmin->aCats as $k => $v): ?>
                    <tr>
                    <td><input type="checkbox" name="idCategory[]" value="<?= $k ?>" /><input type="hidden" name="catNum[]" value="<?= $k ?>" /></td>
                    <td><?= $k ?></td><td>
                    <?php PlxUtils::printInput($k . '_name', PlxUtils::strCheck($v['name']), 'text', '-50'); ?>
                    </td><td>
                    <?php PlxUtils::printInput($k . '_url', $v['url'], 'text', '-50'); ?>
                    </td><td>
                    <?php PlxUtils::printSelect($k . '_active', array('1' => L_YES, '0' => L_NO), $v['active']); ?>
                    </td><td>
                    <?php PlxUtils::printSelect($k . '_tri', $aTri, $v['tri']); ?>
                    </td><td>
                    <?php PlxUtils::printInput($k . '_bypage', $v['bypage'], 'text', '-3'); ?>
                    </td><td>
                    <?php PlxUtils::printInput($k . '_ordre', $ordre, 'text', '-3'); ?>
                    </td><td>
                    <?php PlxUtils::printSelect($k . '_menu', array('oui' => L_DISPLAY, 'non' => L_HIDE), $v['menu']); ?>
                    </td>
                    <td><a href="categorie.php?p=<?= $k ?>"><?= L_OPTIONS ?></a></td>
                    </tr>
                    <?php $ordre++; ?>
                <?php endforeach; ?>
                <?php # On récupère le dernier identifiant
                $a = array_keys($plxAdmin->aCats);
                rsort($a); ?>
            <?php else: ?>
                $a['0'] = 0;
            <?php endif; ?>
            <?php $new_catid = str_pad($a['0'] + 1, 3, "0", STR_PAD_LEFT); ?>
            <tr>
                <td colspan="2"><?= L_NEW_CATEGORY ?></td>
                <td>
                    <?php
                    echo '<input type="hidden" name="catNum[]" value="' . $new_catid . '" />';
                    PlxUtils::printInput($new_catid . '_template', 'categorie.php', 'hidden');
                    PlxUtils::printInput($new_catid . '_name', '', 'text', '-50');
                    echo '</td><td>';
                    PlxUtils::printInput($new_catid . '_url', '', 'text', '-50');
                    echo '</td><td>';
                    PlxUtils::printSelect($new_catid . '_active', array('1' => L_YES, '0' => L_NO), '1');
                    echo '</td><td>';
                    PlxUtils::printSelect($new_catid . '_tri', $aTri, $plxAdmin->aConf['tri']);
                    echo '</td><td>';
                    PlxUtils::printInput($new_catid . '_bypage', $plxAdmin->aConf['bypage'], 'text', '-3');
                    echo '</td><td>';
                    PlxUtils::printInput($new_catid . '_ordre', $ordre, 'text', '-3');
                    echo '</td><td>';
                    PlxUtils::printSelect($new_catid . '_menu', array('oui' => L_DISPLAY, 'non' => L_HIDE), '1');
                    echo '</td><td>&nbsp;';
                    ?>
                </td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="10">
                    <?php PlxUtils::printSelect('selection', array('' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, 'no-margin', 'id_selection') ?>
                    <input class="btn" type="submit" name="submit" value="<?= L_OK ?>"
                           onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idCategory[]', '<?= L_CONFIRM_DELETE ?>')"/>
                </td>
            </tr>
            </tfoot>
        </table>
    </form>
</div>


<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoriesFoot'));
# On inclut le footer
include __DIR__ . '/foot.php';
?>
