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

if (isset($_POST['update']) or (isset($_POST['selection']) and in_array($_POST['selection'], array('delete', 'activate', 'deactivate')))) {
    $plxAdmin->plxPlugins->saveConfig($_POST);
    header('Location: parametres_plugins.php');
    exit;
}

function pluginsList($plugins, $defaultLang, $type)
{
# plugins		array()		contient la liste des plugins à afficher
# defaultLang	string		langue utilisée dans l'admin
# type			true|false	true=liste des plugins actifs, false=liste des plugins inactifs
    $output = '';
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
            $output .= '<tr class="top" data-scope="' . $plugInstance->getInfo('scope') . '">' . "\n";

            # checkbox
            $output .= '<td>';
            $output .= '<input type="hidden" name="plugName[]" value="' . $plugName . '" />';
            $output .= '<input type="checkbox" name="chkAction[]" value="' . $plugName . '" />';
            $output .= "</td>\n";
            # icon
            $output .= '<td><img src="' . $icon . '" alt="" /></td>';

            # plugin infos
            $output .= '<td class="wrap">';
            # message d'alerte si plugin non configuré
            if ($type and file_exists(PLX_PLUGINS . $plugName . '/config.php') and !file_exists(PLX_ROOT . PLX_CONFIG_PATH . 'plugins/' . $plugName . '.xml')) $output .= '<span style="margin-top:5px" class="alert red float-right">' . L_PLUGIN_NO_CONFIG . '</span>';
            # title + version
            $output .= '<strong>' . plxUtils::strCheck($plugInstance->getInfo('title')) . '</strong> - ' . L_PLUGINS_VERSION . ' <strong>' . plxUtils::strCheck($plugInstance->getInfo('version')) . '</strong>';
            # date
            if ($plugInstance->getInfo('date') != '') $output .= ' (' . plxUtils::strCheck($plugInstance->getInfo('date')) . ')';
            # description
            $output .= '<br />' . plxUtils::strCheck($plugInstance->getInfo('description')) . '<br />';
            # author
            $output .= L_AUTHOR . ' : ' . plxUtils::strCheck($plugInstance->getInfo('author'));
            # site
            if ($plugInstance->getInfo('site') != '') $output .= ' - <a href="' . plxUtils::strCheck($plugInstance->getInfo('site')) . '">' . plxUtils::strCheck($plugInstance->getInfo('site')) . '</a>';
            $output .= "</td>\n";

            # colonne pour trier les plugins
            if ($type) {
                $output .= '<td>';
                $output .= '<input size="2" maxlength="3" type="text" name="plugOrdre[' . $plugName . ']" value="' . $ordre . '" />';
                $output .= "</td>\n";
            }

            # affichage des liens du plugin
            $output .= '<td class="right">';
            # lien configuration
            if (is_file(PLX_PLUGINS . $plugName . '/config.php')) {
                $output .= '<a title="' . L_PLUGINS_CONFIG_TITLE . '" href="parametres_plugin.php?p=' . urlencode($plugName) . '">' . L_PLUGINS_CONFIG . '</a><br />';
            }
            # lien pour code css
            $output .= '<a title="' . L_PLUGINS_CSS_TITLE . '" href="parametres_plugincss.php?p=' . urlencode($plugName) . '">' . L_PLUGINS_CSS . '</a><br />';
            # lien aide
            if (is_file(PLX_PLUGINS . $plugName . '/lang/' . $defaultLang . '-help.php'))
                $output .= '<a title="' . L_HELP_TITLE . '" href="parametres_help.php?help=plugin&amp;page=' . urlencode($plugName) . '">' . L_HELP . '</a>';
            $output .= "</td>\n";
            $output .= "</tr>\n";
        }
    } else {
        $colspan = $_SESSION['selPlugins'] == '1' ? 5 : 4;
        $output .= '<tr><td colspan="' . $colspan . '" class="center">' . L_NO_PLUGIN . '</td></tr>';
    }
    return $output;
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
# fil d'ariane
$breadcrumbs = array();
$breadcrumbs[] = '<li ' . ($_SESSION['selPlugins'] == '1' ? 'class="selected" ' : '') . ' ><a href="parametres_plugins.php?sel=1">' . L_PLUGINS_ACTIVE_LIST . '</a>&nbsp;<span class="tag">' . $nbActivePlugins . '</span></li>';
$breadcrumbs[] = '<li ' . ($_SESSION['selPlugins'] == '0' ? 'class="selected" ' : '') . ' ><a href="parametres_plugins.php?sel=0">' . L_PLUGINS_INACTIVE_LIST . '</a>&nbsp;<span class="tag">' . $nbInactivePlugins . '</span></li>';

$data_rows_num = ($sel == '1') ? 'data-rows-num=\'name^="plugOrdre"\'' : false;

# On inclut le header
include __DIR__ . '/top.php';

?>

<div class="adminheader">
    <h2 class="h3-like"><?= L_PLUGINS_TITLE ?></h2>
    <span data-scope="admin">Admin</span>
    <span data-scope="site">Site</span>
    <ul>
        <?= implode($breadcrumbs); ?>
    </ul>
</div>

<div class="admin mtm">
    <form action="parametres_plugins.php" method="post" id="form_plugins">

        <?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsTop')) # Hook Plugins ?>

        <?php if ($sel == 1): ?>
            <div class="mtm pas tableheader">
                <?= PlxToken::getTokenPostMethod() ?>
                <input class="btn--primary" type="submit" name="update" value="<?= L_PLUGINS_APPLY_BUTTON ?>"/>
                <input type="text" id="plugins-search" onkeyup="plugFilter()" placeholder="<?= L_SEARCH ?>..."
                       title="<?= L_SEARCH ?>"/>
            </div>
        <?php endif; ?>

        <div>
            <table id="plugins-table" class="table" <?php if (!empty($data_rows_num)) echo $data_rows_num; ?>>
                <thead>
                <tr>
                    <th><input type="checkbox" onclick="checkAll(this.form, 'chkAction[]')"/></th>
                    <th class="w100">&nbsp;</th>
                    <?php if ($_SESSION['selPlugins'] == '1') : ?>
                        <th><?= L_PLUGINS_LOADING_SORT ?></th>
                    <?php endif; ?>
                    <th><?= L_ACTION ?></th>
                </tr>
                </thead>
                <tbody>
                <?= $plugins ?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="10">
                        <input class="btn--warning" name="delete" type="submit" value="<?= L_DELETE ?>"
                               onclick="return confirmAction(this.form, 'id_selection', 'delete', 'chkAction[]', '<?= L_CONFIRM_DELETE ?>')"/>
                    </td>
                </tr>
                </tfoot>
            </table>
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
?>
