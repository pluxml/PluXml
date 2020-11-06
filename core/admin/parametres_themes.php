<?php

/**
 * Themes administration
 *
 * @package PLX
 * @author  Stephane F
 **/

include __DIR__ . '/prepend.php';
include PLX_CORE . 'lib/PlxThemes.php';

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
include __DIR__ . '/top.php';

$plxThemes = new PlxThemes(PLX_ROOT . $plxAdmin->aConf['racine_themes'], $plxAdmin->aConf['style']);

?>
<form action="parametres_themes.php" method="post" id="form_settings">
    <div class="adminheader autogrid">
        <div>
            <h2 class="h3-like"><?= L_CONFIG_VIEW_SKIN_SELECT ?></h2>
            <p><?php printf(L_CONFIG_VIEW_PLUXML_RESSOURCES, PLX_RESSOURCES_LINK); ?></p>
        </div>
        <div class="mtm txtright">
            <input class="inbl btn--primary" onclick="window.location.assign('parametres_edittpl.php');return false"
                   type="submit"
                   value="<?php echo L_TEMPLATES_EDIT ?>"/>
        </div>
    </div>

    <?php eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayTop')) # Hook Plugins ?>

    <div class="admin mtm">
        <div class="grid-4 has-gutter-l themes">
            <? if ($plxThemes->themesList): ?>
                <?php foreach ($plxThemes->themesList as $theme): ?>
                    <?php $currentTheme = $theme == $plxAdmin->aConf['style'] ? 'activeTheme' : ''; ?>
                    <button type="radio" name="style" value="<?= $theme ?>">
                        <div class="theme">
                            <p><?= $plxThemes->getImgPreview($theme) ?></p>
                            <? if ($aInfos = $plxThemes->getInfos($theme)): ?>
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
                            <? if (is_file(PLX_ROOT . $plxAdmin->aConf['racine_themes'] . $theme . '/lang/' . $plxAdmin->aConf['default_lang'] . '-help.php')): ?>
                            <a title="<?= L_HELP_TITLE ?>"
                                  href="parametres_help.php?help=theme&amp;page=<?= urlencode($theme) ?>"> - <?= L_HELP ?></a>
                        </p>
                        <? endif; ?>
                        <? else: ?>
                            <strong><?= $theme ?></strong>
                        <? endif; ?>
                    </button>
                <? endforeach; ?>
            <? else: ?>
                <? L_NONE1 ?>
            <? endif; ?>
        </div>
    </div>

    <?php eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplay')) # Hook Plugins ?>
    <?php echo plxToken::getTokenPostMethod() ?>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayFoot'));
# On inclut le footer
include __DIR__ . '/foot.php';
?>
