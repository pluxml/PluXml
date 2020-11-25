<?php

/**
 * Themes administration
 *
 * @package PLX
 * @author  Stephane F
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if (!empty($_POST)) {
    $plxAdmin->editConfiguration($_POST);
    header('Location: parametres_themes.php');
    exit;
}

# On inclut le header
include 'top.php';

$plxThemes = new plxThemes(PLX_ROOT . $plxAdmin->aConf['racine_themes'], $plxAdmin->aConf['style']);

?>
<form method="post" id="form_themes">
    <div class="adminheader autogrid">
        <div>
            <h2 class="h3-like"><?= L_CONFIG_VIEW_SKIN_SELECT ?></h2>
            <p><?php printf(L_CONFIG_VIEW_PLUXML_RESSOURCES, PLX_RESSOURCES_LINK); ?></p>
        </div>
        <div class="mtm txtright">
            <a class="inbl button btn--primary" href="parametres_edittpl.php"><?= L_TEMPLATES_EDIT ?></a>
        </div>
    </div>

    <?php eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayTop')) # Hook Plugins ?>

    <div class="admin mtm">
        <div class="grid-4 has-gutter-l themes">
<?php
if ($plxThemes->themesList):
	foreach ($plxThemes->themesList as $theme):
        $currentTheme = ($theme == $plxAdmin->aConf['style']) ? 'activeTheme' : '';
?>
                    <button type="radio" name="style" value="<?= $theme ?>" class="<?= ($theme == $plxThemes->activeTheme) ? 'active' : ''; ?>">
                        <div class="theme">
                            <p><?= $plxThemes->getImgPreview($theme) ?></p>
<?php
		if ($aInfos = $plxThemes->getInfos($theme)):?>
                            <div class="themeOverlay">
                                <div class="themeDetails">
                                    Version : <strong><?= $aInfos['version'] ?></strong> (<?= $aInfos['date'] ?>)<br/>
                                    <?= L_AUTHOR ?>&nbsp;:&nbsp;<?= $aInfos['author'] ?><br>
                                    <a href="<?= $aInfos['site'] ?>" title="" target="_blank"><?= $aInfos['site'] ?></a><br>
                                    <?= $aInfos['description'] ?>
                                </div>
                            </div>
                        </div>
                        <p>
                            <strong><?= $aInfos['title'] ?></strong>
<?php
			if (is_file(PLX_ROOT . $plxAdmin->aConf['racine_themes'] . $theme . '/lang/' . $plxAdmin->aConf['default_lang'] . '-help.php')):
?>
                            <a title="<?= L_HELP_TITLE ?>"
                                  href="parametres_help.php?help=theme&amp;page=<?= urlencode($theme) ?>"> - <?= L_HELP ?></a>
                        </p>
<?php
			endif;
		else:
?>
                            <strong><?= $theme ?></strong>
<?php
		endif;
?>
                    </button>
<?php
	endforeach;
else:
?>
                <?= L_NONE1 ?>
<?php
endif;
?>
        </div>
    </div>

<?php eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplay')) # Hook Plugins ?>

					<?= plxToken::getTokenPostMethod() ?>
</form>

<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayFoot'));

# On inclut le footer
include 'foot.php';
