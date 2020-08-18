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
    <div class="adminheader grid-6">
        <div class="col-2 mbm">
            <h2 class="h3-like"><?= L_CONFIG_VIEW_SKIN_SELECT ?></h2>
            <input class="inbl btn--primary" type="submit" value="<?php echo L_CONFIG_THEME_UPDATE ?>"/>
            <input class="inbl btn--primary" onclick="window.location.assign('parametres_edittpl.php');return false"
                   type="submit"
                   value="<?php echo L_TEMPLATES_EDIT ?>"/>
        </div>
        <div class="col-4 item-center">
            <p><?php
                $tag = '<a href="' . PLX_URL_RESSOURCES . '" target="_blank">' . PLX_URL_RESSOURCES . '</a>';
                printf(L_CONFIG_VIEW_PLUXML_RESSOURCES, $tag);
                ?>
            </p>
        </div>
    </div>

    <?php eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayTop')) # Hook Plugins ?>

    <div class="admin">
        <div class="grid-6cd">
            <? if ($plxThemes->themesList): ?>
                <?php foreach ($plxThemes->themesList as $theme): ?>
                    <div>
                        <? if ($aInfos = $plxThemes->getInfos($theme)): ?>
                            <strong><?= $aInfos['title'] ?></strong><br/>
                            Version : <strong><?= $aInfos['version'] ?></strong> - (<?= $aInfos['date'] ?>)<br/>
                            <?= L_AUTHOR ?>&nbsp;:&nbsp;<?= $aInfos['author'] ?> - <a href="?<= $aInfos['site'] ?>"
                                                                                      title=""><?= $aInfos['site'] ?></a>
                            <br/><?= $aInfos['description'] ?><br/>
                        <? else: ?>
                            <strong><?= $theme ?></strong>
                        <? endif; ?>

                        <?= $plxThemes->getImgPreview($theme) ?>

                        <? if (is_file(PLX_ROOT . $plxAdmin->aConf['racine_themes'] . $theme . '/lang/' . $plxAdmin->aConf['default_lang'] . '-help.php')): ?>
                            <a title="<?= L_HELP_TITLE ?>"
                               href="parametres_help.php?help=theme&amp;page=<?= urlencode($theme) ?>"><?= L_HELP ?></a>
                        <? endif; ?>
                        <?php $checked = $theme == $plxAdmin->aConf['style'] ? ' checked="checked"' : ''; ?>
                        <input <?= $checked ?> type="radio" name="style" value="' . $theme . '"/>
                    </div>
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
