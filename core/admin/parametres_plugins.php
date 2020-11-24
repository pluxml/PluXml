<?php

/**
 * Gestion des plugins
 *
 * @package PLX
 * @author    Stephane F
 **/

include __DIR__ . '/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

if (isset($_POST['update']) or (isset($_POST['chkAction']) and (isset($_POST['delete']) or isset($_POST['activate']) or isset($_POST['deactivate'])))) {
    $plxAdmin->plxPlugins->saveConfig($_POST);
    header('Location: parametres_plugins.php');
    exit;
}

function pluginsList($plugins, $defaultLang, $type)
{
# plugins		array()		contient la liste des plugins à afficher
# defaultLang	string		langue utilisée dans l'admin
# type			true|false	true=liste des plugins actifs, false=liste des plugins inactifs

    ob_start();
    $plxAdmin = plxAdmin::getInstance();#OR global $plxAdmin;
    if (sizeof($plugins) > 0) {
        $num = 0;
        foreach ($plugins as $plugName => $plugInstance) {
            $ordre = ++$num;
            # détermination de l'icone à afficher
            if (is_file(PLX_PLUGINS . $plugName . '/icon.png'))
                $icon = PLX_PLUGINS . $plugName . '/icon.png';
            elseif (is_file(PLX_PLUGINS . $plugName . '/icon.jpg'))
                $icon = PLX_PLUGINS . $plugName . '/icon.jpg';
            elseif (is_file(PLX_PLUGINS . $plugName . '/icon.gif'))
                $icon = PLX_PLUGINS . $plugName . '/icon.gif';
            else
                $icon = 'theme/images/icon_plugin.png';

            # plugin activé uniquement côté site (<scope> == 'site')
            if (empty($plugInstance) and $plugInstance = $plxAdmin->plxPlugins->getInstance($plugName)) {
                $plugInstance->getInfos();
            }
            ?>
            <tr class="top" data-scope="<?= $plugInstance->getInfo('scope') ?>">
                <td>
                    <input type="hidden" name="plugName[]" value="<?= $plugName ?>"/>
                    <input type="checkbox" name="chkAction[]" value="<?= $plugName ?>"/>
                </td>
                <td><img class="thumb" src="<?= $icon ?>" alt="icon" width="48" height="48"/></td>
                <td class="wrap">
                    <?php
                    # message d'alerte si plugin non configuré
                    if ($type and file_exists(PLX_PLUGINS . $plugName . '/config.php') and !file_exists(PLX_ROOT . PLX_CONFIG_PATH . 'plugins/' . $plugName . '.xml')) {
                        ?>
                        <span style="margin-top:5px" class="alert red float-right"><?= L_PLUGIN_NO_CONFIG ?></span>
                        <?php
                    }
                    # title + version
                    ?>
                    <strong><?= plxUtils::strCheck($plugInstance->getInfo('title')) ?></strong>
                    - <?= L_PLUGINS_VERSION ?>
                    <strong><?= plxUtils::strCheck($plugInstance->getInfo('version')) ?></strong>
                    <?php
                    if ($plugInstance->getInfo('date') != '') {
                        ?>
                        (<?= plxUtils::strCheck($plugInstance->getInfo('date')) ?>)
                        <?php
                    }
                    ?>
                    <br/><?= nl2br(plxUtils::strCheck($plugInstance->getInfo('description'))) ?>
                    <br/><?= L_AUTHOR ?> : <?= plxUtils::strCheck($plugInstance->getInfo('author')) ?>
                    <?php
                    if ($plugInstance->getInfo('site') != '') {
                        ?>
                        -
                        <a href="<?= plxUtils::strCheck($plugInstance->getInfo('site')) ?>"><?= plxUtils::strCheck($plugInstance->getInfo('site')) ?></a>
                        <?php
                    }
                    ?>
                </td>
                <?php
                # colonne pour trier les plugins
                if ($type) {
                    ?>
                    <td>
                        <input size="2" maxlength="3" type="number" name="plugOrdre[<?= $plugName ?>]"
                               value="<?= $ordre ?>"/>
                    </td>
                    <?php
                }

                # affichage des liens du plugin
                ?>
                <td class="right">
                    <?php
                    # lien configuration
                    if (is_file(PLX_PLUGINS . $plugName . '/config.php')) {
                        ?>
                        <a title="<?= L_PLUGINS_CONFIG_TITLE ?>"
                           href="parametres_plugin.php?p=<?= urlencode($plugName) ?>"><?= L_PLUGINS_CONFIG ?></a><br/>
                        <?php
                    }
                    # lien pour code css
                    ?>
                    <a title="<?= L_PLUGINS_CSS_TITLE ?>"
                       href="parametres_plugincss.php?p=<?= urlencode($plugName) ?>"><?= L_PLUGINS_CSS ?></a><br/>
                    <?php
                    if (is_file(PLX_PLUGINS . $plugName . '/lang/' . $defaultLang . '-help.php')) {
                        # lien aide
                        ?>
                        <a title="<?= L_HELP_TITLE ?>"
                           href="parametres_help.php?help=plugin&page=<?= urlencode($plugName) ?>"><?= L_HELP ?></a>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td colspan="<?= ($_SESSION['selPlugins'] == '1') ? 5 : 4 ?>" class="txtcenter"><?= L_NO_PLUGIN ?></td>
        </tr>
        <?php
    }
    return ob_get_clean();
}

# récuperation de la liste des plugins inactifs
$aInactivePlugins = $plxAdmin->plxPlugins->getInactivePlugins();
# nombre de plugins actifs
$nbActivePlugins = sizeof($plxAdmin->plxPlugins->aPlugins);
# nombre de plugins inactifs
$nbInactivePlugins = sizeof($aInactivePlugins);
# récuperation du type de plugins à afficher
$_GET['sel'] = isset($_GET['sel']) ? intval(plxUtils::nullbyteRemove($_GET['sel'])) : '';
$session = isset($_SESSION['selPlugins']) ? $_SESSION['selPlugins'] : '1';
$sel = (in_array($_GET['sel'], array('0', '1')) ? $_GET['sel'] : $session);
$_SESSION['selPlugins'] = $sel;
if ($sel == '1') {
    $aSelList = array('' => L_FOR_SELECTION, 'deactivate' => L_PLUGINS_DEACTIVATE, '-' => '-----', 'delete' => L_DELETE);
    $plugins = pluginsList($plxAdmin->plxPlugins->aPlugins, $plxAdmin->aConf['default_lang'], true);
} else {
    $aSelList = array('' => L_FOR_SELECTION, 'activate' => L_PLUGINS_ACTIVATE, '-' => '-----', 'delete' => L_DELETE);
    $plugins = pluginsList($aInactivePlugins, $plxAdmin->aConf['default_lang'], false);
}

$data_rows_num = ($sel == '1') ? 'data-rows-num=\'name^="plugOrdre"\'' : false;

# On inclut le header
include __DIR__ . '/top.php';

?>

    <div class="adminheader">
        <h2 class="h3-like"><?= L_PLUGINS_TITLE ?></h2>
        <span data-scope="admin">Admin</span>
        <span data-scope="site">Site</span>
        <?php /* fil d'ariane  */ ?>
        <ul>
            <li <?= ($_SESSION['selPlugins'] == '1') ? 'class="selected" ' : '' ?>><a
                        href="parametres_plugins.php?sel=1"><?= L_PLUGINS_ACTIVE_LIST ?></a>&nbsp;<span
                        class="tag"><?= $nbActivePlugins ?></span></li>
            <li <?= ($_SESSION['selPlugins'] == '0') ? 'class="selected" ' : '' ?>><a
                        href="parametres_plugins.php?sel=0"><?= L_PLUGINS_INACTIVE_LIST ?></a>&nbsp;<span
                        class="tag"><?= $nbInactivePlugins ?></span></li>
        </ul>
    </div>

    <div class="admin mtm">
        <form action="parametres_plugins.php" method="post" id="form_plugins" data-chk="chkAction[]">

            <?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsTop')) # Hook Plugins ?>

            <?php if ($sel == 1): ?>
                <div class="mtm pas tableheader">
                    <?= PlxToken::getTokenPostMethod() ?>
                    <input class="btn--primary" type="submit" name="update" value="<?= L_PLUGINS_APPLY_BUTTON ?>"/>
                    <input type="text" id="plugins-search" onkeyup="plugFilter()" placeholder="<?= L_SEARCH ?>..."
                           title="<?= L_SEARCH ?>"/>
                </div>
            <?php endif; ?>

            <div class="scrollable-table">
                <table id="plugins-table" class="table mb0" <?= !empty($data_rows_num) ? $data_rows_num : '' ?>>
                    <thead>
                    <tr>
                        <th><input type="checkbox"/></th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <?php if ($_SESSION['selPlugins'] == '1') : ?>
                            <th><?= L_PLUGINS_LOADING_SORT ?></th>
                        <?php endif; ?>
                        <th><?= L_ACTION ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?= $plugins ?>
                    </tbody>
                </table>
            </div>
            <div class="mtm pas tableheader">
                <button class="submit btn--warning" name="delete" disabled data-lang="<?= L_CONFIRM_DELETE ?>"><i
                            class="icon-trash"></i><?= L_DELETE ?></button>
                <button class="submit btn--primary"
                        name="<?= ($_SESSION['selPlugins'] == '1') ? 'deactivate' : 'activate' ?>" disabled
                        data-lang="<?= ($_SESSION['selPlugins'] == '1') ? L_CONFIRM_DEACTIVATE : L_CONFIRM_ACTIVATE ?>">
                    <i class="<?= ($_SESSION['selPlugins'] == '1') ? 'icon-lock' : 'icon-unlock' ?>"></i><?= ($_SESSION['selPlugins'] == '1') ? L_PLUGINS_DEACTIVATE : L_PLUGINS_ACTIVATE ?>
                </button>
            </div>
        </form>
    </div>

    <script>
        function plugFilter() {
            var input, filter, table, tr, td, i;
            filter = document.getElementById("plugins-search").value;
            table = document.getElementById("plugins-table");
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[2];
                if (td != undefined) {
                    if (td.innerHTML.toLowerCase().indexOf(filter.toLowerCase()) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
            if (typeof (Storage) !== "undefined" && filter !== "undefined") {
                localStorage.setItem("plugins_search", filter);
            }
        }

        if (typeof (Storage) !== "undefined" && localStorage.getItem("plugins_search") !== "undefined") {
            input = document.getElementById("plugins-search");
            input.value = localStorage.getItem("plugins_search");
            plugFilter();
        }
    </script>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsFoot'));

# On inclut le footer
include __DIR__ . '/foot.php';
