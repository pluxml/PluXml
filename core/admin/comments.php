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
$breadcrumbs[] = '<li ' . ($_SESSION['selCom'] == 'all' ? 'class="selected" ' : '') . '><a href="comments.php?sel=all&amp;page=1">' . L_ALL . '</a>&nbsp;(' . $plxAdmin->nbComments('all') . ')</li>';
$breadcrumbs[] = '<li ' . ($_SESSION['selCom'] == 'online' ? 'class="selected" ' : '') . '><a href="comments.php?sel=online&amp;page=1">' . L_COMMENT_ONLINE . '</a>&nbsp;(' . $plxAdmin->nbComments('online') . ')</li>';
$breadcrumbs[] = '<li ' . ($_SESSION['selCom'] == 'offline' ? 'class="selected" ' : '') . '><a href="comments.php?sel=offline&amp;page=1">' . L_COMMENT_OFFLINE . '</a>&nbsp;(' . $plxAdmin->nbComments('offline') . ')</li>';
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

    <form action="comments.php<?= !empty($_GET['a']) ? '?a=' . $_GET['a'] : '' ?>" method="post" id="form_comments" data-chk="idCom[]">

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

        <div class="table-scrollable">
            <table id="comments-table" class="table mb0">
                <thead>
	                <tr>
	                    <th class="checkbox"><input type="checkbox" /></th>
	                    <th><?= L_DATE ?></th>
<?php
$all = ($_SESSION['selCom'] == 'all');
if($all) {
?>
						<th><?= L_COMMENT_STATUS_FIELD ?></th>
<?php
}
?>
	                    <th class="w100"><?= L_COMMENTS_LIST_MESSAGE ?></th>
	                    <th><?= L_AUTHOR ?></th>
	                    <th><?= L_COMMENT_SITE_FIELD ?></th>
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
?>
					<tr class="top type-<?= $plxAdmin->plxRecord_coms->f('type') ?>">
						<td><input type="checkbox" name="idCom[]" value="<?= $id ?>" /></td>
						<td class="datetime"><?= plxDate::formatDate($plxAdmin->plxRecord_coms->f('date')) ?></td>
<?php
		if($all) {
?>
						<td class="status"><?= empty($status) ? L_COMMENT_ONLINE : L_COMMENT_OFFLINE ?></td>
<?php
		}

		$fAuthor = $plxAdmin->plxRecord_coms->f('author');
		$fMail = $plxAdmin->plxRecord_coms->f('mail');
		$author = !empty($fMail) ? '<a href="mailto:' . $fMail . '">' . $fAuthor . '</a>' : $fAuthor;

		$fSite = $plxAdmin->plxRecord_coms->f('site');
		$site =  !empty($site) ? '<a href="' . $fSite . '" target="_blank">' . $fSite . '</a>' : '&nbsp;';
?>
						<td class="wrap"><?= nl2br($plxAdmin->plxRecord_coms->f('content')) ?></td>
						<td class="author"><?= $author ?></td>
						<td class="site"><?=  $site ?></td>
						<td>
							<button><a href="comment_new.php?c=<?= $id . (!empty($_GET['a']) ? '&a=' . $_GET['a'] : '') ?>" title="<?= L_COMMENT_ANSWER ?>"><i class="icon-reply-1"></i></a></button>
							<button><a href="comment.php?c=<?= $id . (!empty($_GET['a']) ? '&a=' . $_GET['a'] : '') ?>" title="<?= L_COMMENT_EDIT_TITLE ?>"><i class="icon-pencil"></i></a></button>
							<button><a href="article.php?a=<?= $artId ?>" title="<?= L_COMMENT_ARTICLE_LINKED_TITLE ?>"><i class="icon-doc-inv"></i></a></button>
						</td>
					</tr>
<?php
				}
                } else {
					# Pas de commentaires
?>
                    <tr>
						<td colspan="5" class="txtcenter"><?= L_NO_COMMENT ?></td>
					</tr>
<?php
                }
?>
                </tbody>
            </table>
		</div>
<?php if ($coms): ?>
        <div class="pas grid-2-small-1 tablefooter">
			<div>
				<button class="submit btn--warning" name="delete" data-lang="<?= L_CONFIRM_DELETE ?>" disabled><i class="icon-trash"></i><?= L_DELETE ?></button>
			</div>
			<div class="pagination right">
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsPagination'));

if($coms) {
	$sel = '&sel=' . $_SESSION['selCom'] . (!empty($_GET['a']) ? '&a=' . $_GET['a'] : '');
	plxUtils::printPagination($nbComPagination, $plxAdmin->aConf['bypage_admin_coms'], $plxAdmin->page, 'comments.php?page=%d' . $sel);
}
?>
			</div>
		</div>
<?php endif ?>
    </form>

<?php
if (!empty($plxAdmin->aConf['clef'])) {
	$href = $plxAdmin->racine . 'feed.php?admin' . $plxAdmin->aConf['clef'] . '/commentaires';
?>
	<p><?= L_COMMENTS_PRIVATE_FEEDS ?> :</p>
	<ul class="unstyled-list">
		<li><a href="<?= $href ?>/hors-ligne" title="<?= L_COMMENT_OFFLINE_FEEDS_TITLE ?>"><?= L_COMMENT_OFFLINE_FEEDS ?></a></li>
		<li><a href="<?= $href ?>/en-ligne" title="<?= L_COMMENT_ONLINE_FEEDS_TITLE ?>"><?= L_COMMENT_ONLINE_FEEDS ?></a></li>
	</ul>
<?php
}
?>
</div>
<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsFoot'));

# On inclut le footer
include __DIR__ . '/foot.php';
