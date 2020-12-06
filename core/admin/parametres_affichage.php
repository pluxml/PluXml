<?php

/**
 * Edition des paramètres d'affichage
 *
 * @package PLX
 * @author    Florent MONTHEL, Stephane F
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if (isset($_POST['display'])) {
    $_POST['feed_footer'] = $_POST['content'];
    $_POST['images_l'] = plxUtils::getValue($_POST['images_l'], 800);
    $_POST['images_h'] = plxUtils::getValue($_POST['images_h'], 600);
    $_POST['miniatures_l'] = plxUtils::getValue($_POST['miniatures_l'], 200);
    $_POST['miniatures_h'] = plxUtils::getValue($_POST['miniatures_h'], 100);
    unset($_POST['content']);

    $plxAdmin->editConfiguration($_POST);
    header('Location: parametres_affichage.php');
    exit;
}

# On récupère les templates de la page d'accueil
$aTemplates = $plxAdmin->getTemplatesCurrentTheme('home', L_NONE1);

# Tableau du tri
$aTriArts = array(
    'desc'		=> L_SORT_DESCENDING_DATE,
    'asc'		=> L_SORT_ASCENDING_DATE,
    'alpha'		=> L_SORT_ALPHABETICAL,
    'ralpha'	=> L_SORT_REVERSE_ALPHABETICAL,
    'random'	=> L_SORT_RANDOM,
);

$aTriComs = array('desc' => L_SORT_DESCENDING_DATE, 'asc' => L_SORT_ASCENDING_DATE);

# On va tester les variables pour les images et miniatures
if (!is_numeric($plxAdmin->aConf['images_l'])) $plxAdmin->aConf['images_l'] = 800;
if (!is_numeric($plxAdmin->aConf['images_h'])) $plxAdmin->aConf['images_h'] = 600;
if (!is_numeric($plxAdmin->aConf['miniatures_l'])) $plxAdmin->aConf['miniatures_l'] = 200;
if (!is_numeric($plxAdmin->aConf['miniatures_h'])) $plxAdmin->aConf['miniatures_h'] = 100;

# On inclut le header
include 'top.php';
?>

<form method="post" id="form_display" class="first-level">
    <?= plxToken::getTokenPostMethod() ?>

    <div class="adminheader">
        <div>
            <h2 class="h3-like"><?= L_CONFIG_VIEW ?></h2>
        </div>
        <div>
			<div>
	            <input class="inbl btn--primary" type="submit" name="config-display" role="button" value="<?= L_SAVE ?>"/>
			</div>
        </div>
    </div>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsDisplayTop'))
?>
    <fieldset>
		<div>
			<label for="id_hometemplate"><?= L_CONFIG_HOMETEMPLATE ?></label>
<?php plxUtils::printSelect('hometemplate', $aTemplates, $plxAdmin->aConf['hometemplate']) ?>
		</div>
		<div>
			<label for="id_tri"><?= L_ARTICLES_SORT ?></label>
<?php plxUtils::printSelect('tri', $aTriArts, $plxAdmin->aConf['tri']); ?>
		</div>
		<div>
			<label for="id_bypage"><?= L_CONFIG_VIEW_BYPAGE ?></label>
			<input type="number" name="bypage" value="<?= $plxAdmin->aConf['bypage'] ?>" id="id_bypage" />
		</div>
		<div>
			<label for="id_bypage_tags"><?= L_CONFIG_VIEW_BYPAGE_TAGS ?></label>
			<input type="number" name="bypage_tags" value="<?= $plxAdmin->aConf['bypage_tags'] ?>" id="id_bypage_tags" />
		</div>
		<div>
			<label for="id_bypage_archives"><?= L_CONFIG_VIEW_BYPAGE_ARCHIVES ?></label>
			<input type="number" name="bypage_archives" value="<?= $plxAdmin->aConf['bypage_archives'] ?>" id="id_bypage_archives" />
		</div>
		<div>
			<label for="id_bypage_admin"><?= L_CONFIG_VIEW_BYPAGE_ADMIN ?></label>
			<input type="number" name="bypage_admin" value="<?= $plxAdmin->aConf['bypage_admin'] ?>" id="id_bypage_admin" />
		</div>
		<div>
			<label for="id_tri_coms"><?= L_CONFIG_VIEW_SORT_COMS ?></label>
<?php plxUtils::printSelect('tri_coms', $aTriComs, $plxAdmin->aConf['tri_coms']); ?>
		</div>
		<div>
			<label for="id_bypage_admin_coms"><?= L_CONFIG_VIEW_BYPAGE_ADMIN_COMS ?></label>
			<input type="number" name="bypage_admin_coms" value="<?= $plxAdmin->aConf['bypage_admin_coms'] ?>" id="id_bypage_admin_coms" />
		</div>
		<div>
			<label for="id_display_empty_cat"><?= L_CONFIG_VIEW_DISPLAY_EMPTY_CAT ?></label>
			<input type="checkbox" name="display_empty_cat" value="1" class="switch" <?= !empty($plxAdmin->aConf['display_empty_cat']) ? 'checked' : '' ?> id="id_display_empty_cat" />
		</div>
		<div>
			<label><?= L_CONFIG_VIEW_IMAGES ?></label>
			<input type="number" name="images_l" value="<?= $plxAdmin->aConf['images_l'] ?>" /><span class="surface">X</span>
			<input type="number" name="images_h" value="<?= $plxAdmin->aConf['images_h'] ?>" />
		</div>
		<div>
			<label><?= L_CONFIG_VIEW_THUMBS ?></label>
			<input type="number" name="miniatures_l" value="<?= $plxAdmin->aConf['miniatures_l'] ?>" /><span class="surface">X</span>
			<input type="number" name="miniatures_h" value="<?= $plxAdmin->aConf['miniatures_h'] ?>" />
		</div>
		<div>
			<label for="id_thumbs"><?= L_MEDIAS_THUMBS ?></label>
			<input type="checkbox" name="thumbs" value="1" class="switch" <?= !empty($plxAdmin->aConf['thumbs']) ? 'checked' : '' ?> id="id_display_empty_cat" />
		</div>
<?php plxUtils::printThumbnail($plxAdmin->aConf); ?>
		<div>
			<label for="id_bypage_feed"><?= L_CONFIG_VIEW_BYPAGE_FEEDS ?></label>
			<input type="number" name="bypage_feed" value="<?= $plxAdmin->aConf['bypage_feed'] ?>" id="id_bypage_feed" />
		</div>
		<div>
			<label for="id_feed_chapo"><?= L_CONFIG_VIEW_FEEDS_HEADLINE ?></label>
			<!-- div class="tooltip icon-help-circled">
				<span class="tooltiptext"><?= L_CONFIG_VIEW_FEEDS_HEADLINE_HELP ?></span>
			</div -->
			<input type="checkbox" name="feed_chapo" value="1" class="switch" <?= !empty($plxAdmin->aConf['feed_chapo']) ? 'checked' : '' ?> id="id_feed_chapo" />
		</div>
		<div class="large">
            <label for="id_content"><?= L_CONFIG_VIEW_FEEDS_FOOTER ?></label>
            <textarea name="content" rows="5"><?= plxUtils::strCheck($plxAdmin->aConf['feed_footer']) ?></textarea>
        </div>
    </fieldset>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsDisplay'))
?>
</form>
<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsDisplayFoot'));

# On inclut le footer
include 'foot.php';
