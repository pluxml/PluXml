<?php

/**
 * Edition des paramètres de base
 *
 * @package PLX
 * @author    Florent MONTHEL, Stephane F, Philippe-M, Pedro "P3ter" CADETE"
 **/

include 'prepend.php';
include PLX_CORE . 'lib/class.plx.timezones.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if (!empty($_POST)) {
    $plxAdmin->editConfiguration($_POST);
    header('Location: parametres_base.php');
    exit;
}

# On inclut le header
include 'top.php';
?>

<form action="parametres_base.php" method="post" id="form_settings">
    <div class="adminheader">
        <div class="mbm">
            <h2 class="h3-like"><?= L_CONFIG_BASE ?></h2>
            <input class="inbl btn--primary" type="submit" name="profil" role="button"
                   value="<?= L_CONFIG_BASE_UPDATE ?>"/>
        </div>
    </div>

    <?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseTop')) # Hook Plugins ?>

    <fieldset class="config">
        <div class="grid-2">
            <div>
                <label for="id_title"><?php echo L_CONFIG_BASE_SITE_TITLE ?>&nbsp;:</label>
            </div>
            <div>
                <?php plxUtils::printInput('title', plxUtils::strCheck($plxAdmin->aConf['title'])); ?>
            </div>
            <div>
                <label for="id_description"><?php echo L_CONFIG_BASE_SITE_SLOGAN ?>&nbsp;:</label>
            </div>
            <div>
                <?php plxUtils::printInput('description', plxUtils::strCheck($plxAdmin->aConf['description'])); ?>
            </div>
            <div>
                <label for="id_meta_description"><?php echo L_CONFIG_META_DESCRIPTION ?>&nbsp;:</label>
            </div>
            <div>
                <?php plxUtils::printInput('meta_description', plxUtils::strCheck($plxAdmin->aConf['meta_description'])); ?>
            </div>
            <div>
                <label for="id_meta_keywords"><?php echo L_CONFIG_META_KEYWORDS ?>&nbsp;:</label>
            </div>
            <div>
                <?php plxUtils::printInput('meta_keywords', plxUtils::strCheck($plxAdmin->aConf['meta_keywords'])); ?>
            </div>
            <div>
                <label for="id_default_lang"><?php echo L_CONFIG_BASE_DEFAULT_LANG ?>&nbsp;:</label>
            </div>
            <div>
                <?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $plxAdmin->aConf['default_lang']) ?>
            </div>
            <div>
                <label for="id_timezone"><?php echo L_TIMEZONE ?>&nbsp;:</label>
            </div>
            <div>
                <?php plxUtils::printSelect('timezone', plxTimezones::timezones(), $plxAdmin->aConf['timezone']); ?>
            </div>
            <div>
                <label for="id_allow_com"><?php echo L_ALLOW_COMMENTS ?>&nbsp;:</label>
            </div>
            <div>
                <?php plxUtils::printSelect('allow_com', array('1' => L_YES, '0' => L_NO), $plxAdmin->aConf['allow_com']); ?>
            </div>
            <div>
                <label for="id_mod_com"><?php echo L_CONFIG_BASE_MODERATE_COMMENTS ?>&nbsp;:</label>
            </div>
            <div>
                <?php plxUtils::printSelect('mod_com', array('1' => L_YES, '0' => L_NO), $plxAdmin->aConf['mod_com']); ?>
            </div>
            <div>
                <label for="id_mod_art"><?php echo L_CONFIG_BASE_MODERATE_ARTICLES ?>&nbsp;:</label>
            </div>
            <div>
                <?php plxUtils::printSelect('mod_art', array('1' => L_YES, '0' => L_NO), $plxAdmin->aConf['mod_art']); ?>
            </div>
            <div>
                <label for="id_enable_rss"><?php echo L_CONFIG_BASE_ENABLE_RSS ?>&nbsp;:</label>
            </div>
            <div>
                <?php plxUtils::printSelect('enable_rss', array('1' => L_YES, '0' => L_NO), $plxAdmin->aConf['enable_rss']); ?>
            </div>
        </div>
    </fieldset>
    <?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBase')) # Hook Plugins ?>
    <?php echo plxToken::getTokenPostMethod() ?>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseFoot'));
# On inclut le footer
include 'foot.php';
?>
