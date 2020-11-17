<?php

/**
 * Edition des pages statiques
 *
 * @package PLX
 * @author    Stephane F et Florent MONTHEL
 **/

include __DIR__ . '/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticsPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_MANAGER);

# On édite les pages statiques
if (!empty($_POST)) {
    $plxAdmin->editConfiguration(!empty($_POST['homeStatic']) ? array('homestatic' => $_POST['homeStatic'][0]) : array('homestatic' => ''));
    $plxAdmin->editStatiques($_POST);
    header('Location: statiques.php');
    exit;
}

# On inclut le header
include __DIR__ . '/top.php';
?>
<script>
    function checkBox(cb) {
        cbs = document.getElementsByName('homeStatic[]');
        for (var i = 0; i < cbs.length; i++) {
            if (cbs[i].checked == true) {
                cbs[i].checked = ((i + 1) == cb) ? true : false;
            }
        }
    }
</script>

<div class="adminheader">
    <h2 class="h3-like"><?= L_STATICS_PAGE_TITLE ?></h2>
</div>

<div class="admin mtm">
    <form action="statiques.php" method="post" id="form_statics">
        <?php eval($plxAdmin->plxPlugins->callHook('AdminStaticsTop')) # Hook Plugins ?>
        <div class="mtm pas tableheader">
            <?= PlxToken::getTokenPostMethod() ?>
            <input class="btn--primary" type="submit" name="update" value="<?= L_STATICS_UPDATE ?>"/>
        </div>
        <div>
            <table class="table" data-rows-num='name$="_ordre"'>
                <thead>
                <tr>
                    <th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idStatic[]')"/></th>
                    <th>#</th>
                    <th><?= L_HOMEPAGE ?></th>
                    <th><?= L_STATICS_GROUP ?></th>
                    <th class="w100"><?= L_TITLE ?></th>
                    <th><?= L_STATICS_URL ?></th>
                    <th><?= L_ACTIVE ?></th>
                    <th><?= L_ORDER ?></th>
                    <th><?= L_MENU ?></th>
                    <th><?= L_ACTION ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                # Initialisation de l'ordre
                $ordre = 1;
                # Si on a des pages statiques
                if ($plxAdmin->aStats) {
                    foreach ($plxAdmin->aStats as $k => $v) { # Pour chaque page statique
                        echo '<tr>';
                        echo '<td><input type="checkbox" name="idStatic[]" value="' . $k . '" /><input type="hidden" name="staticNum[]" value="' . $k . '" /></td>';
                        echo '<td>' . $k . '</td><td>';
                        $selected = $plxAdmin->aConf['homestatic'] == $k ? ' checked="checked"' : '';
                        echo '<input title="' . L_STATICS_PAGE_HOME . '" type="checkbox" name="homeStatic[]" value="' . $k . '"' . $selected . ' onclick="checkBox(\'' . $ordre . '\')" />';
                        echo '</td><td>';
                        PlxUtils::printInput($k . '_group', PlxUtils::strCheck($v['group']), 'text', '-100');
                        echo '</td><td>';
                        PlxUtils::printInput($k . '_name', PlxUtils::strCheck($v['name']), 'text', '-255', '', 'w100');
                        echo '</td><td>';
                        PlxUtils::printInput($k . '_url', $v['url'], 'text', '-255');
                        echo '</td><td>';
                        PlxUtils::printSelect($k . '_active', array('1' => L_YES, '0' => L_NO), $v['active']);
                        echo '</td><td>';
                        PlxUtils::printInput($k . '_ordre', $ordre, 'text', '2-3');
                        echo '</td><td>';
                        PlxUtils::printSelect($k . '_menu', array('oui' => L_DISPLAY, 'non' => L_HIDE), $v['menu']);
                        echo '</td><td>';
                        $url = $v['url'];
                        if (!PlxUtils::checkSite($url)) {
                            echo '<button><a href="statique.php?p=' . $k . '" title="' . L_STATICS_SRC_TITLE . '"><i class="icon-pencil"></i></a></button>';
                            if ($v['active']) {
                                echo '&nbsp;&nbsp;<button><a href="' . $plxAdmin->urlRewrite('?static' . intval($k) . '/' . $v['url']) . '" title="' . L_STATIC_VIEW_PAGE . ' ' . PlxUtils::strCheck($v['name']) . ' ' . L_STATIC_ON_SITE . '"><i class="icon-eye"></i></a></button>';
                            }
                        } elseif ($v['url'][0] == '?')
                            echo '<button><a href="' . $plxAdmin->urlRewrite($v['url']) . '" title="' . PlxUtils::strCheck($v['name']) . '"><i class="icon-eye"></i></a></button>';
                        else
                            echo '<button><a href="' . $v['url'] . '" title="' . PlxUtils::strCheck($v['name']) . '"><i class="icon-eye"></i></a></button>';
                        echo '</td></tr>';
                        $ordre++;
                    }
                    # On récupère le dernier identifiant
                    $a = array_keys($plxAdmin->aStats);
                    rsort($a);
                } else {
                    $a['0'] = 0;
                }
                $new_staticid = str_pad($a['0'] + 1, 3, "0", STR_PAD_LEFT);
                ?>
                <tr class="new">
                    <td colspan="3"><?= L_STATICS_NEW_PAGE ?></td>
                    <td>
                        <?php
                        echo '<input type="hidden" name="staticNum[]" value="' . $new_staticid . '" />';
                        PlxUtils::printInput($new_staticid . '_group', '', 'text', '-100');
                        echo '</td><td>';
                        PlxUtils::printInput($new_staticid . '_name', '', 'text', '-255', '', 'w100');
                        PlxUtils::printInput($new_staticid . '_template', 'static.php', 'hidden');
                        echo '</td><td>';
                        PlxUtils::printInput($new_staticid . '_url', '', 'text', '-255');
                        echo '</td><td>';
                        PlxUtils::printSelect($new_staticid . '_active', array('1' => L_YES, '0' => L_NO), '0');
                        echo '</td><td>';
                        PlxUtils::printInput($new_staticid . '_ordre', $ordre, 'text', '2-3');
                        echo '</td><td>';
                        PlxUtils::printSelect($new_staticid . '_menu', array('oui' => L_DISPLAY, 'non' => L_HIDE), '1');
                        ?>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="10">
                        <?php if ($_SESSION['profil'] <= PROFIL_MODERATOR) : ?>
                            <input class="btn--warning" name="delete" type="submit" value="<?= L_DELETE ?>"
                                   onclick="return confirmAction(this.form, 'delete', 'idStatic[]', '<?= L_CONFIRM_DELETE ?>')"/>
                        <?php endif; ?>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </form>
</div>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticsFoot'));
# On inclut le footer
include __DIR__ . '/foot.php';
?>
