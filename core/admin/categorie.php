<?php

/**
 * Edition des options d'une catégorie
 *
 * @package PLX
 * @author    Stephane F.
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoryPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_EDITOR);

# On édite la catégorie
if (!empty($_POST) and array_key_exists($_POST['id'], $plxAdmin->aCats)) {
    $plxAdmin->editCategorie($_POST);
    header('Location: categorie.php?p=' . $_POST['id']);
    exit;
} elseif (!empty($_GET['p'])) {
	# On affiche une catégorie
	# On vérifie l'existence de la catégorie
    $id = plxUtils::strCheck($_GET['p']);
    if (!isset($plxAdmin->aCats[$id])) {
        plxMsg::Error(L_CAT_UNKNOWN);
        header('Location: categorie.php');
        exit;
    }
} else {
	# Sinon, on redirige
    header('Location: categories.php');
    exit;
}

# On récupère les templates des catégories
$aTemplates = array();
$files = plxGlob::getInstance(PLX_ROOT . $plxAdmin->aConf['racine_themes'] . $plxAdmin->aConf['style']);
if ($array = $files->query('/^categorie(-[a-z0-9-_]+)?.php$/')) {
    foreach ($array as $k => $v)
        $aTemplates[$v] = $v;
}
if (empty($aTemplates)) $aTemplates[''] = L_NONE1;

# On inclut le header
include 'top.php';
?>

<form method="post" id="form_category">
	<?= plxToken::getTokenPostMethod() ?>
	<?php plxUtils::printInput('id', $id, 'hidden'); ?>
    <div class="adminheader">
        <div>
            <h2 class="h3-like"><?= L_EDITCAT_PAGE_TITLE; ?> "<?= plxUtils::strCheck(trim($plxAdmin->aCats[$id]['name'])); ?>"</h2>
            <p><a href="categorie.php"><?= L_EDITCAT_BACK_TO_PAGE ?></a></p>
        </div>
        <div>
			<div>
	            <input class="btn--primary" type="submit" value="<?= L_SAVE ?>"/>
			</div>
        </div>
    </div>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoryTop'))
?>
    <fieldset>
		<div class="label-expanded">
			<label for="id_homepage"><?= L_EDITCAT_DISPLAY_HOMEPAGE ?></label>
			<input  type="checkbox" name="homepage" value="1" class="switch" <?= !empty($plxAdmin->aCats[$id]['homepage']) ? ' checked' : '' ?> />
		</div>
		<div class="label-expanded">
			<label for="id_template"><?= L_TEMPLATE ?></label>
			<?php plxUtils::printSelect('template', $aTemplates, $plxAdmin->aCats[$id]['template']) ?>
		</div>
		<div>
			<label for="id_content"><?= L_EDITCAT_DESCRIPTION ?></label>
			<textarea name="content" rows="5" id="id_content"><?= plxUtils::strCheck($plxAdmin->aCats[$id]['description']) ?></textarea>
		</div>
		<div>
			<?php plxUtils::printThumbnail($plxAdmin->aCats[$id]); ?>
		</div>
		<div>
			<label for="id_title_htmltag"><?= L_TITLE_HTMLTAG ?></label>
			<?php plxUtils::printInput('title_htmltag', plxUtils::strCheck($plxAdmin->aCats[$id]['title_htmltag']), 'text', '50-255'); ?>
		</div>
		<div>
			<label for="id_meta_description"><?= L_EDITCAT_META_DESCRIPTION ?></label>
			<?php plxUtils::printInput('meta_description', plxUtils::strCheck($plxAdmin->aCats[$id]['meta_description']), 'text', '50-255') ?>
		</div>
		<div>
			<label for="id_meta_keywords"><?= L_EDITCAT_META_KEYWORDS ?></label>
			<?php plxUtils::printInput('meta_keywords', plxUtils::strCheck($plxAdmin->aCats[$id]['meta_keywords']), 'text', '50-255') ?>
		</div>
    </fieldset>
    <?php eval($plxAdmin->plxPlugins->callHook('AdminCategory')) # Hook Plugins ?>
</form>
<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoryFoot'));

# On inclut le footer
include 'foot.php';
