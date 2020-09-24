<?php
/**
 * Edition des paramètres avancés
 *
 * @package PLX
 * @author    Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE
 **/

include __DIR__ . '/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if (!empty($_POST)) {
    $plxAdmin->editConfiguration($_POST);
    # réinit de la variable de session medias (pour medias.php) au cas si changmt de chemin medias
    unset($_SESSION['medias']);
    header('Location: parametres_avances.php');
    exit;
}

# On inclut le header
include __DIR__ . '/top.php';
?>

<form action="parametres_avances.php" method="post" id="form_settings">
    <div class="adminheader">
        <div class="mbm">
            <h2 class="h3-like"><?= L_CONFIG_ADVANCED ?></h2>
            <input class="inbl btn--primary" type="submit" name="profil" role="button"
                   value="<?= L_CONFIG_ADVANCED_UPDATE ?>"/>
        </div>
    </div>

    <?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvancedTop')) # Hook Plugins ?>

    <fieldset>
        <div class="grid-2">
            <div>
                <label for="id_urlrewriting"><?= L_CONFIG_ADVANCED_URL_REWRITE ?></label>
                <?php if (is_file(PLX_ROOT . '.htaccess') and $plxAdmin->aConf['urlrewriting'] == 0): ?>
                    <p><small><?= L_CONFIG_ADVANCED_URL_REWRITE_ALERT ?></small></p>
                <?php endif; ?>
            </div>
            <div>
                <?php if (plxUtils::testModRewrite(false)): ?>
                    <?php plxUtils::printSelect('urlrewriting', array('1' => L_YES, '0' => L_NO), $plxAdmin->aConf['urlrewriting']); ?>
                <?php else: ?>
                    <?= L_MODREWRITE_NOT_AVAILABLE ?>
                <?php endif; ?>
            </div>
            <div>
                <label for="id_gzip"><?= L_CONFIG_CLEAN_URLS ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_CONFIG_CLEAN_URLS_HELP ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printSelect('cleanurl', array('1' => L_YES, '0' => L_NO), $plxAdmin->aConf['cleanurl']); ?>
            </div>
            <div>
                <label for="id_gzip"><?= L_CONFIG_ADVANCED_GZIP ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_CONFIG_ADVANCED_GZIP_HELP ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printSelect('gzip', array('1' => L_YES, '0' => L_NO), $plxAdmin->aConf['gzip']); ?>
            </div>
            <div>
                <label for="id_lostpassword"><?= L_CONFIG_ADVANCED_LOSTPASSWORD ?></label>
            </div>
            <div>
                <?php plxUtils::printSelect('lostpassword', array('1' => L_YES, '0' => L_NO), $plxAdmin->aConf['lostpassword']); ?>
            </div>
            <div>
                <label for="id_capcha"><?= L_CONFIG_ADVANCED_CAPCHA ?></label>
            </div>
            <div>
                <?php plxUtils::printSelect('capcha', array('1' => L_YES, '0' => L_NO), $plxAdmin->aConf['capcha']); ?>
            </div>
            <div>
                <label for="id_userfolders"><?= L_CONFIG_ADVANCED_USERFOLDERS ?></label>
            </div>
            <div>
                <?php plxUtils::printSelect('userfolders', array('1' => L_YES, '0' => L_NO), $plxAdmin->aConf['userfolders']); ?>
            </div>
            <div>
                <label for="id_clef"><?= L_CONFIG_ADVANCED_ADMIN_KEY ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_CONFIG_ADVANCED_KEY_HELP ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('clef', $plxAdmin->aConf['clef'], 'text', '30-30'); ?>
            </div>
            <div>
                <label for="id_config_path"><?= L_CONFIG_ADVANCED_CONFIG_FOLDER ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_SLASH_END_REQUIRED ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('config_path', PLX_CONFIG_PATH) ?>
            </div>
            <div>
                <label for="id_racine_articles"><?= L_CONFIG_ADVANCED_ARTS_FOLDER ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_SLASH_END_REQUIRED ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('racine_articles', $plxAdmin->aConf['racine_articles']); ?>
            </div>
            <div>
                <label for="id_racine_commentaires"><?= L_CONFIG_ADVANCED_COMS_FOLDER ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_SLASH_END_REQUIRED ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('racine_commentaires', $plxAdmin->aConf['racine_commentaires']); ?>
            </div>
            <div>
                <label for="id_racine_statiques"><?= L_CONFIG_ADVANCED_STATS_FOLDER ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_SLASH_END_REQUIRED ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('racine_statiques', $plxAdmin->aConf['racine_statiques']); ?>
            </div>
            <div>
                <label for="id_medias"><?= L_CONFIG_ADVANCED_MEDIAS_FOLDER ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_SLASH_END_REQUIRED ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('medias', $plxAdmin->aConf['medias']); ?>
            </div>
            <div>
                <label for="id_racine_themes"><?= L_CONFIG_ADVANCED_THEMES_FOLDER ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_SLASH_END_REQUIRED ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('racine_themes', $plxAdmin->aConf['racine_themes']); ?>
            </div>
            <div>
                <label for="id_racine_plugins"><?= L_CONFIG_ADVANCED_PLUGINS_FOLDER ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_SLASH_END_REQUIRED ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('racine_plugins', $plxAdmin->aConf['racine_plugins']); ?>
            </div>
            <div>
                <label for="id_custom_admincss_file"><?= L_CONFIG_CUSTOM_CSSADMIN_PATH ?></label>
            </div>
            <div>
                <?php plxUtils::printInput('custom_admincss_file', $plxAdmin->aConf['custom_admincss_file']); ?>
            </div>
        </div>
        <div>
            <h2><?= L_CONFIG_ADVANCED_EMAIL_SENDING_TITLE ?>&nbsp;:</h2>
            <p><small><?= L_CONFIG_ADVANCED_EMAIL_SENDING_TITLE_HELP ?></small></p>
        </div>
        <div class="grid-2">
            <div>
                <label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_EMAIL_METHOD ?></label>
                <small><?= L_CONFIG_ADVANCED_EMAIL_METHOD_HELP ?></small>
            </div>
            <div
            <?php plxUtils::printInputRadio('email_method', array('sendmail' => 'sendmail', 'smtp' => 'SMTP', 'smtpoauth' => 'OAUTH2'), $plxAdmin->aConf['email_method']); ?>
        </div>
        </div>
        <div><h3><?= L_CONFIG_ADVANCED_SMTP_TITLE ?></h3></div>
        <div class="grid-2">
            <div>
                <label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_SERVER ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_CONFIG_ADVANCED_SMTP_SERVER_HELP ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('smtp_server', $plxAdmin->aConf['smtp_server']); ?>
            </div>
            <div>
                <label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_USERNAME ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_CONFIG_ADVANCED_SMTP_USERNAME_HELP ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('smtp_username', $plxAdmin->aConf['smtp_username']); ?>
            </div>
            <div>
                <label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_PASSWORD ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_CONFIG_ADVANCED_SMTP_PASSWORD_HELP ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('smtp_password', $plxAdmin->aConf['smtp_password'], 'password', '', false, '', '', 'autocomplete="new-password"'); ?>
            </div>
            <div>
                <label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_PORT ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_CONFIG_ADVANCED_SMTP_PORT_HELP ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('smtp_port', $plxAdmin->aConf['smtp_port']); ?>
            </div>
            <div>
                <label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTP_SECURITY ?></label>
            </div>
            <div>
                <?php plxUtils::printInputRadio('smtp_security', array('0' => L_NONE1, 'ssl' => 'SSL', 'tls' => 'TLS'), $plxAdmin->aConf['smtp_security']); ?>
            </div>
        </div>
        <div>
            <h3><?= L_CONFIG_ADVANCED_SMTPOAUTH_TITLE ?></h3>
            <p><small><?= L_CONFIG_ADVANCED_SMTPOAUTH_TITLE_HELP ?></small></p>
        </div>
        <div class="grid-2">
            <div>
                <label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTPOAUTH_EMAIL ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_CONFIG_ADVANCED_SMTPOAUTH_EMAIL_HELP ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('smtpOauth2_emailAdress', $plxAdmin->aConf['smtpOauth2_emailAdress']); ?>
            </div>
            <div>
                <label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTPOAUTH_CLIENTID ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_CONFIG_ADVANCED_SMTPOAUTH_CLIENTID_HELP ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('smtpOauth2_clientId', $plxAdmin->aConf['smtpOauth2_clientId']); ?>
            </div>
            <div>
                <label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTPOAUTH_SECRETKEY ?></label>
                <div class="tooltip icon-help-circled">
                    <span class="tooltiptext"><?= L_CONFIG_ADVANCED_SMTPOAUTH_SECRETKEY_HELP ?></span>
                </div>
            </div>
            <div>
                <?php plxUtils::printInput('smtpOauth2_clientSecret', $plxAdmin->aConf['smtpOauth2_clientSecret']); ?>
            </div>
            <div>
                <label for="id_custom_admincss_file"><?= L_CONFIG_ADVANCED_SMTPOAUTH_TOKEN ?></label>
                <small><?= L_CONFIG_ADVANCED_SMTPOAUTH_TOKEN_HELP ?></small>
            </div>
            <div>
                <?php plxUtils::printInput('smtpOauth2_refreshToken', $plxAdmin->aConf['smtpOauth2_refreshToken'], 'text', '', true); ?>
                <?php
                if (empty($plxAdmin->aConf['smtpOauth2_clientSecret']) and empty($plxAdmin->aConf['smtpOauth2_clientId']) and empty($plxAdmin->aConf['smtpOauth2_emailAdress'])) {
                    $disabled = "disabled";
                }
                ?>
                <a href="get_oauth_token.php?provider=Google">
                    <button type="button" <?= $disabled ?>><?= L_CONFIG_ADVANCED_SMTPOAUTH_GETTOKEN ?></button>
                </a>
            </div>
        </div>
    </fieldset>
    <?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvanced')) # Hook Plugins ?>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvancedFoot'));
# On inclut le footer
include __DIR__ . '/foot.php';
?>
