<?php

/**
 * Edition des paramètres de base
 *
 * @package PLX
 * @author    Florent MONTHEL, Stephane F, Philippe-M, Pedro "P3ter" CADETE"
 **/

include 'prepend.php';

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

    <form method="post" id="form_base_settings" class="first-level">
        <?= plxToken::getTokenPostMethod() ?>
        <div class="adminheader">
            <div>
                <h2 class="h3-like"><?= L_CONFIG_BASE ?></h2>
            </div>
            <div>
                <div>
                    <input class="btn--primary" type="submit" name="config-base" role="button" value="<?= L_SAVE ?>"/>
                </div>
            </div>
        </div>
        <?php
        # Hook Plugins
        eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseTop'));
        ?>
        <fieldset>
            <div>
                <label for="id_title"><?= L_CONFIG_BASE_SITE_TITLE ?></label>
                <?php plxUtils::printInput('title', plxUtils::strCheck($plxAdmin->aConf['title'])); ?>
            </div>
            <div>
                <label for="id_description"><?= L_CONFIG_BASE_SITE_SLOGAN ?></label>
                <?php plxUtils::printInput('description', plxUtils::strCheck($plxAdmin->aConf['description'])); ?>
            </div>
            <div>
                <label for="id_meta_description"><?= L_CONFIG_META_DESCRIPTION ?></label>
                <?php plxUtils::printInput('meta_description', plxUtils::strCheck($plxAdmin->aConf['meta_description'])); ?>
            </div>
            <div>
                <label for="id_meta_keywords"><?= L_CONFIG_META_KEYWORDS ?></label>
                <?php plxUtils::printInput('meta_keywords', plxUtils::strCheck($plxAdmin->aConf['meta_keywords'])); ?>
            </div>
            <div>
                <label for="id_default_lang"><?= L_CONFIG_BASE_DEFAULT_LANG ?></label>
                <?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $plxAdmin->aConf['default_lang']) ?>
            </div>
            <div>
                <label for="id_timezone"><?= L_TIMEZONE ?></label>
                <?php plxUtils::printSelect('timezone', plxTimezones::timezones(), $plxAdmin->aConf['timezone']); ?>
            </div>
            <div>
                <label for="id_allow_com"><?= L_ALLOW_COMMENTS ?></label>
                <input type="checkbox" name="allow_com" value="1" class="switch"
                    <?= !empty($plxAdmin->aConf['allow_com']) ? 'checked' : '' ?> id="id_allow_com"/>
            </div>
            <div>
                <label for="id_mod_com"><?= L_CONFIG_BASE_MODERATE_COMMENTS ?></label>
                <input type="checkbox" name="mod_com" value="1" class="switch"
                    <?= !empty($plxAdmin->aConf['mod_com']) ? 'checked' : '' ?> id="id_mod_com"/>
            </div>
            <div>
                <label for="id_mod_art"><?= L_CONFIG_BASE_MODERATE_ARTICLES ?></label>
                <input type="checkbox" name="mod_art" value="1" class="switch"
                    <?= !empty($plxAdmin->aConf['mod_art']) ? 'checked' : '' ?> id="id_mod_art"/>
            </div>
            <div>
                <label for="id_enable_rss"><?= L_CONFIG_BASE_ENABLE_RSS ?></label>
                <input type="checkbox" name="enable_rss" value="1" class="switch"
                    <?= !empty($plxAdmin->aConf['enable_rss']) ? 'checked' : '' ?> id="id_enable_rss"/>
            </div>
            <div>
                <label for="id_enable_rss_comment"><?= L_CONFIG_BASE_ENABLE_RSS_COMMENT ?></label>
                <input type="checkbox" name="enable_rss_comment" value="1" class="switch"
                    <?= !empty($plxAdmin->aConf['enable_rss_comment']) ? 'checked' : '' ?> id="id_enable_rss_comment"/>
            </div>
        </fieldset>
        <?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBase')) # Hook Plugins ?>
        <?= plxToken::getTokenPostMethod() ?>
    </form>

<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseFoot'));

# On inclut le footer
include 'foot.php';
