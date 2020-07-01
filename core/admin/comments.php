<?php

/**
 * Listing des commentaires en attente de validation
 *
 * @package PLX
 * @author    Stephane F
 **/

include __DIR__ . '/prepend.php';

# Contrôle du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsPrepend'));

# Contrôle de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_MODERATOR);

# Les commentaires ne sont pas autorisés
if (empty($plxAdmin->aConf['allow_com'])) {
    plxMsg::Error(L_COMMENTS_CLOSED);
    header('Location: index.php');
    exit;
}

# Les commentaires ne sont pas autorisés
if (empty($plxAdmin->aConf['allow_com'])) {
    plxMsg::Error(L_COMMENTS_CLOSED);
    header('Location: index.php');
    exit;
}

# validation de l'id de l'article si passé en paramètre
if (isset($_GET['a']) and !preg_match('/^_?\d{4}$/', $_GET['a'])) {
    plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
    header('Location: index.php');
    exit;
}

# Suppression des commentaires sélectionnés
if (isset($_POST['selection']) and !empty($_POST['btn_ok']) and ($_POST['selection'] == 'delete') and isset($_POST['idCom'])) {
    foreach ($_POST['idCom'] as $k => $v) $plxAdmin->delCommentaire($v);
    header('Location: comments.php' . (!empty($_GET['a']) ? '?a=' . $_GET['a'] : ''));
    exit;
} # Validation des commentaires sélectionnés
elseif (isset($_POST['selection']) and !empty($_POST['btn_ok']) and ($_POST['selection'] == 'online') and isset($_POST['idCom'])) {
    foreach ($_POST['idCom'] as $k => $v) $plxAdmin->modCommentaire($v, 'online');
    header('Location: comments.php' . (!empty($_GET['a']) ? '?a=' . $_GET['a'] : ''));
    exit;
} # Mise hors-ligne des commentaires sélectionnés
elseif (isset($_POST['selection']) and !empty($_POST['btn_ok']) and ($_POST['selection'] == 'offline') and isset($_POST['idCom'])) {
    foreach ($_POST['idCom'] as $k => $v) $plxAdmin->modCommentaire($v, 'offline');
    header('Location: comments.php' . (!empty($_GET['a']) ? '?a=' . $_GET['a'] : ''));
    exit;
}

# Récupération des infos sur l'article attaché au commentaire si passé en paramètre
if (!empty($_GET['a'])) {
    # Infos sur notre article
    if (!$globArt = $plxAdmin->plxGlob_arts->query('/^' . $_GET['a'] . '.(.*).xml$/', '', 'sort', 0, 1)) {
        plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
        header('Location: index.php');
        exit;
    }
    # Infos sur l'article
    $aArt = $plxAdmin->parseArticle(PLX_ROOT . $plxAdmin->aConf['racine_articles'] . $globArt['0']);
    $portee = ucfirst(L_ARTICLE) . ' &laquo;' . $aArt['title'] . '&raquo;';
} else { # Commentaires globaux
    $portee = '';
}

# On inclut le header
include __DIR__ . '/top.php';

# Récupération du type de commentaire à afficher
$_GET['sel'] = !empty($_GET['sel']) ? $_GET['sel'] : '';
if (in_array($_GET['sel'], array('online', 'offline', 'all')))
    $comSel = plxUtils::nullbyteRemove($_GET['sel']);
else
    $comSel = ((isset($_SESSION['selCom']) and !empty($_SESSION['selCom'])) ? $_SESSION['selCom'] : 'all');

if (!empty($_GET['a'])) {

    switch ($comSel) {
        case 'online':
            $mod = '';
            break;
        case 'offline':
            $mod = '_';
            break;
        default:
            $mod = '[[:punct:]]?';
    }
    $comSelMotif = '/^' . $mod . str_replace('_', '', $_GET['a']) . '.(.*).xml$/';
    $_SESSION['selCom'] = 'all';
    $nbComPagination = $plxAdmin->nbComments($comSelMotif);
    $h2 = '<h2>' . L_COMMENTS_ALL_LIST . '</h2>';
} elseif ($comSel == 'online') {
    $comSelMotif = '/^\d{4}.(.*).xml$/';
    $_SESSION['selCom'] = 'online';
    $nbComPagination = $plxAdmin->nbComments('online');
    $h2 = '<h2>' . L_COMMENTS_ONLINE_LIST . '</h2>';
} elseif ($comSel == 'offline') {
    $comSelMotif = '/^_\d{4}.(.*).xml$/';
    $_SESSION['selCom'] = 'offline';
    $nbComPagination = $plxAdmin->nbComments('offline');
    $h2 = '<h2>' . L_COMMENTS_OFFLINE_LIST . '</h2>';
} elseif ($comSel == 'all') { // all
    $comSelMotif = '/^[[:punct:]]?\d{4}.(.*).xml$/';
    $_SESSION['selCom'] = 'all';
    $nbComPagination = $plxAdmin->nbComments('all');
    $h2 = '<h2>' . L_COMMENTS_ALL_LIST . '</h2>';
}

if ($portee != '') {
    $h3 = '<h3>' . $portee . '</h3>';
}

$breadcrumbs = array();
$breadcrumbs[] = '<li><a ' . ($_SESSION['selCom'] == 'all' ? 'class="selected" ' : '') . 'href="comments.php?sel=all&amp;page=1">' . L_ALL . '</a>&nbsp;(' . $plxAdmin->nbComments('all') . ')</li>';
$breadcrumbs[] = '<li><a ' . ($_SESSION['selCom'] == 'online' ? 'class="selected" ' : '') . 'href="comments.php?sel=online&amp;page=1">' . L_COMMENT_ONLINE . '</a>&nbsp;(' . $plxAdmin->nbComments('online') . ')</li>';
$breadcrumbs[] = '<li><a ' . ($_SESSION['selCom'] == 'offline' ? 'class="selected" ' : '') . 'href="comments.php?sel=offline&amp;page=1">' . L_COMMENT_OFFLINE . '</a>&nbsp;(' . $plxAdmin->nbComments('offline') . ')</li>';
if (!empty($_GET['a'])) {
    $breadcrumbs[] = '<a href="comment_new.php?a=' . $_GET['a'] . '" title="' . L_COMMENT_NEW_COMMENT_TITLE . '">' . L_COMMENT_NEW_COMMENT . '</a>';
}

# On va récupérer les commentaires
$plxAdmin->getPage();
$start = $plxAdmin->aConf['bypage_admin_coms'] * ($plxAdmin->page - 1);
$coms = $plxAdmin->getCommentaires($comSelMotif, 'rsort', $start, $plxAdmin->aConf['bypage_admin_coms'], 'all');

function selector($comSel, $id)
{
    ob_start();
    if ($comSel == 'online')
        plxUtils::printSelect('selection', array('' => L_FOR_SELECTION, 'offline' => L_SET_OFFLINE, '-' => '-----', 'delete' => L_DELETE), '', false, 'no-margin', $id);
    elseif ($comSel == 'offline')
        plxUtils::printSelect('selection', array('' => L_FOR_SELECTION, 'online' => L_COMMENT_SET_ONLINE, '-' => '-----', 'delete' => L_DELETE), '', false, 'no-margin', $id);
    elseif ($comSel == 'all')
        plxUtils::printSelect('selection', array('' => L_FOR_SELECTION, 'online' => L_COMMENT_SET_ONLINE, 'offline' => L_SET_OFFLINE, '-' => '-----', 'delete' => L_DELETE), '', false, 'no-margin', $id);
    return ob_get_clean();
}

$selector = selector($comSel, 'id_selection');

?>

<div class="adminheader">
    <h2 class="h3-like"><?= L_COMMENTS_ALL_LIST ?></h2>
    <ul>
        <?= implode($breadcrumbs); ?>
    </ul>
</div>

<div class="admin">

    <?php eval($plxAdmin->plxPlugins->callHook('AdminCommentsTop')) # Hook Plugins ?>

    <form action="comments.php<?= !empty($_GET['a']) ? '?a=' . $_GET['a'] : '' ?>" method="post" id="form_comments">

        <div class="mtm pas  tableheader">
            <?= plxToken::getTokenPostMethod() ?>
            <?php if ($comSel == 'online'): ?>
                <button class="submit btn--primary" name="offline" type="submit"><i
                            class="icon-comment"></i><?= L_SET_OFFLINE ?></button>
            <?php elseif ($comSel == 'offline'): ?>
                <button class="submit btn--primary" name="online" type="submit"><i
                            class="icon-comment"></i><?= L_COMMENT_SET_ONLINE ?></button>
            <?php else: ?>
                <button class="submit btn--primary" name="online" type="submit"><i
                            class="icon-comment"></i><?= L_COMMENT_SET_ONLINE ?></button>
                <button class="submit btn--primary" name="offline" type="submit"><i
                            class="icon-comment"></i><?= L_SET_OFFLINE ?></button>
            <?php endif ?>
            <!--<input type="submit" name="btn_ok" value="<?= L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idCom[]', '<?= L_CONFIRM_DELETE ?>')" />-->
        </div>

        <?php if (isset($h3)) echo $h3 ?>

        <div>
            <table id="comments-table" class="table">
                <thead>
                <tr>
                    <th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idCom[]')"/></th>
                    <th><?= L_DATE ?></th>
                    <th class="w100"><?= L_COMMENTS_LIST_MESSAGE ?></th>
                    <th><?= L_AUTHOR ?></th>
                    <th><?= L_ACTION ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                if ($coms) {
                    $num = 0;
                    while ($plxAdmin->plxRecord_coms->loop()) { # On boucle
                        $artId = $plxAdmin->plxRecord_coms->f('article');
                        $status = $plxAdmin->plxRecord_coms->f('status');
                        $id = $status . $artId . '.' . $plxAdmin->plxRecord_coms->f('numero');
                        $content = nl2br($plxAdmin->plxRecord_coms->f('content'));
                        if ($_SESSION['selCom'] == 'all') {
                            $content = $content . ($status != '' ? '<span class="tag--warning">' . L_COMMENT_OFFLINE : '');
                        }
                        # On génère notre ligne
                        echo '<tr class="top type-' . $plxAdmin->plxRecord_coms->f('type') . '">';
                        echo '<td><input type="checkbox" name="idCom[]" value="' . $id . '" /></td>';
                        echo '<td>' . PlxDate::formatDate($plxAdmin->plxRecord_coms->f('date')) . '&nbsp;</td>';
                        echo '<td>' . $content . '&nbsp;</td>';
                        echo '<td>' . $plxAdmin->plxRecord_coms->f('author') . '&nbsp;</td>';
                        echo '<td>';
                        echo '<a href="comment_new.php?c=' . $id . (!empty($_GET['a']) ? '&amp;a=' . $_GET['a'] : '') . '" title="' . L_COMMENT_ANSWER . '">' . L_COMMENT_ANSWER . '</a>&nbsp;&nbsp;';
                        echo '<a href="comment.php?c=' . $id . (!empty($_GET['a']) ? '&amp;a=' . $_GET['a'] : '') . '" title="' . L_COMMENT_EDIT_TITLE . '">' . L_EDIT . '</a>&nbsp;&nbsp;';
                        echo '<a href="article.php?a=' . $artId . '" title="' . L_COMMENT_ARTICLE_LINKED_TITLE . '">' . L_ARTICLE . '</a>';
                        echo '</td></tr>';
                    }
                } else { # Pas de commentaires
                    echo '<tr><td colspan="5" class="center">' . L_NO_COMMENT . '</td></tr>';
                }
                ?>
                </tbody>
                <?php if ($coms): ?>
                    <tfoot>
                    <tr>
                        <td colspan="2">
                            <button class="submit btn--warning" name="delete" type="submit"><i
                                        class="icon-trash-empty"></i><?= L_DELETE ?></button>
                        </td>
                        <td colspan="3" class="pagination right">
                            <?php
                            # Hook Plugins
                            eval($plxAdmin->plxPlugins->callHook('AdminCommentsPagination'));
                            # Affichage de la pagination
                            if ($coms) { # Si on a des commentaires
                                # Calcul des pages
                                $last_page = ceil($nbComPagination / $plxAdmin->aConf['bypage_admin_coms']);
                                $stop = $plxAdmin->page + 2;
                                if ($stop < 5) $stop = 5;
                                if ($stop > $last_page) $stop = $last_page;
                                $start = $stop - 4;
                                if ($start < 1) $start = 1;
                                // URL generation
                                $sel = '&amp;sel=' . $_SESSION['selCom'] . (!empty($_GET['a']) ? '&amp;a=' . $_GET['a'] : '');
                                $p_url = 'comments.php?page=' . ($plxAdmin->page - 1) . $sel;
                                $n_url = 'comments.php?page=' . ($plxAdmin->page + 1) . $sel;
                                $l_url = 'comments.php?page=' . $last_page . $sel;
                                $f_url = 'comments.php?page=1' . $sel;
                                // Display pagination links
                                $s = $plxAdmin->page > 2 ? '<a href="' . $f_url . '" title="' . L_PAGINATION_FIRST_TITLE . '"><span class="btn"><i class="icon-angle-double-left"></i></span></a>' : '<span class="btn"><i class="icon-angle-double-left"></i></span>';
                                echo $s;
                                $s = $plxAdmin->page > 1 ? '<a href="' . $p_url . '" title="' . L_PAGINATION_PREVIOUS_TITLE . '"><span class="btn"><i class="icon-angle-left"></i></span></a>' : '<span class="btn"><i class="icon-angle-left"></i></span>';
                                echo $s;
                                for ($i = $start; $i <= $stop; $i++) {
                                    $s = $i == $plxAdmin->page ? '<span class="current btn">' . $i . '</span>' : '<a href="' . ('comments.php?page=' . $i . $artTitle) . '" title="' . $i . '"><span class="btn">' . $i . '</span></a>';
                                    echo $s;
                                }
                                $s = $plxAdmin->page < $last_page ? '<a href="' . $n_url . '" title="' . L_PAGINATION_NEXT_TITLE . '"><span class="btn"><i class="icon-angle-right"></i></span></a>' : '<span class="btn"><i class="icon-angle-right"></i></span>';
                                echo $s;
                                $s = $plxAdmin->page < ($last_page - 1) ? '<a href="' . $l_url . '" title="' . L_PAGINATION_LAST_TITLE . '"><span class="btn"><i class="icon-angle-double-right"></i></span></a>' : '<span class="btn"><i class="icon-angle-double-right"></i></span>';
                                echo $s;
                            }
                            ?>
                        </td>
                    </tr>
                    </tfoot>
                <?php endif ?>
            </table>
        </div>

    </form>

    <?php if (!empty($plxAdmin->aConf['clef'])) : ?>
        <?= L_COMMENTS_PRIVATE_FEEDS ?> :
        <ul class="unstyled-list">
            <?php $urlp_hl = $plxAdmin->racine . 'feed.php?admin' . $plxAdmin->aConf['clef'] . '/commentaires/hors-ligne'; ?>
            <li><a href="<?= $urlp_hl ?>"
                   title="<?= L_COMMENT_OFFLINE_FEEDS_TITLE ?>"><?= L_COMMENT_OFFLINE_FEEDS ?></a></li>
            <?php $urlp_el = $plxAdmin->racine . 'feed.php?admin' . $plxAdmin->aConf['clef'] . '/commentaires/en-ligne'; ?>
            <li><a href="<?= $urlp_el ?>" title="<?= L_COMMENT_ONLINE_FEEDS_TITLE ?>"><?= L_COMMENT_ONLINE_FEEDS ?></a>
            </li>
        </ul>
    <?php endif; ?>

</div>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsFoot'));
# On inclut le footer
include __DIR__ . '/foot.php';
?>
