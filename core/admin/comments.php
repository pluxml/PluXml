<?php

/**
 * Listing des commentaires en attente de validation
 *
 * @package PLX
 * @author	Stephane F
 **/

include 'prepend.php';

# Contrôle du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsPrepend'));

# Contrôle de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR);

# validation de l'id de l'article si passé en paramètre
if(isset($_GET['a']) AND !preg_match('/^_?\d{4}$/',$_GET['a'])) {
	plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
	header('Location: index.php');
	exit;
}

# Suppression des commentaires sélectionnés
if(isset($_POST['selection']) AND !empty($_POST['btn_ok']) AND ($_POST['selection']=='delete') AND isset($_POST['idCom'])) {
	foreach ($_POST['idCom'] as $k => $v) $plxAdmin->delCommentaire($v);
	header('Location: comments.php'.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
	exit;
}
# Validation des commentaires sélectionnés
elseif(isset($_POST['selection']) AND !empty($_POST['btn_ok']) AND ($_POST['selection']=='online') AND isset($_POST['idCom'])) {
	foreach ($_POST['idCom'] as $k => $v) $plxAdmin->modCommentaire($v, 'online');
	header('Location: comments.php'.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
	exit;
}
# Mise hors-ligne des commentaires sélectionnés
elseif (isset($_POST['selection']) AND !empty($_POST['btn_ok']) AND ($_POST['selection']=='offline') AND isset($_POST['idCom'])) {
	foreach ($_POST['idCom'] as $k => $v) $plxAdmin->modCommentaire($v, 'offline');
	header('Location: comments.php'.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
	exit;
}

# Récupération des infos sur l'article attaché au commentaire si passé en paramètre
if(!empty($_GET['a'])) {
	# Infos sur notre article
	if(!$globArt = $plxAdmin->plxGlob_arts->query('/^'.$_GET['a'].'\..*\.xml$/','','sort',0,1)) {
		plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
		header('Location: index.php');
		exit;
	}
	# Infos sur l'article
	$aArt = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$globArt['0']);
	$portee = !empty($aArt['title']) ? L_COMMENTS_ARTICLE_SCOPE . ' &laquo;' . $aArt['title'] . '&raquo;' : '';
} else { # Commentaires globaux
	$portee = '';
}

# On inclut le header
include 'top.php';

$mods = array(
	'all'		=> '_?',
	'online'	=> '',
	'offline'	=> '_',
);

# Récupération du type de commentaire à afficher
$_GET['sel'] = !empty($_GET['sel']) ? $_GET['sel'] : '';
if(array_key_exists($_GET['sel'], $mods)) {
	$comSel = $_GET['sel'];
} else {
	$comSel = ((isset($_SESSION['selCom']) AND !empty($_SESSION['selCom'])) ? $_SESSION['selCom'] : 'all');
}

$_SESSION['selCom'] = $comSel;
$artMotif = (!empty($_GET['a']) and preg_match('#^_?(\d{4})$#', $_GET['a'], $matches)) ? $matches[1] : '\d{4}';

$options = array(
	'all'		=> L_ALL,
	'online'	=> L_COMMENT_ONLINE,
	'offline'	=> L_COMMENT_OFFLINE,
);
$h2 = <<< EOT
<h2>${options[$comSel]}</h2>
EOT;

$comSelMotif = '/^' . $mods[$comSel] . $artMotif . '\..*\.xml$/';
$nbComPagination=$plxAdmin->nbComments($comSelMotif);

function selector($comSel, $id) {
	$options = array(
		'' => L_FOR_SELECTION,
	);
	switch($comSel) {
		case 'online': $options['offline'] = L_COMMENT_SET_OFFLINE; break;
		case 'offline' : $options['online'] = L_COMMENT_SET_ONLINE; break;
		default :
			$options['online'] = L_COMMENT_SET_ONLINE;
			$options['offline'] = L_COMMENT_SET_OFFLINE;
	}
	$options['-'] = '-----';
	$options['delete'] = L_COMMENT_DELETE;

	ob_start();
	plxUtils::printSelect('selection', $options, '', false, 'no-margin', $id);
	return ob_get_clean();
}

$selector = selector($comSel, 'id_selection');

?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminCommentsTop')) # Hook Plugins ?>

<form action="comments.php<?= !empty($_GET['a'])?'?a='.$_GET['a']:'' ?>" method="post" id="form_comments">

	<div class="inline-form action-bar">
		<?= $h2 ?>
		<ul class="menu">
<?php
$options = array(
	'all'		=> L_ALL,
	'online'	=> L_COMMENT_ONLINE,
	'offline'	=> L_COMMENT_OFFLINE,
);
$req = ($artMotif == '\d{4}') ? '' : '&a='. $_GET['a'];
foreach($options as $status => $caption) {
	$commentsCount = $plxAdmin->nbComments('/^' . $mods[$status] . $artMotif . '\..*\.xml$/');
?>
			<li><a <?= ($_SESSION['selCom'] == $status) ? ' class="selected"' : '' ?> href="comments.php?sel=<?= $status . $req ?>&page=1"><?= $caption ?></a>&nbsp;(<?= $commentsCount ?>)</li>
<?php
}

if(!empty($_GET['a'])) {
?>
			<li><a href="comment_new.php?a=<?= $_GET['a'] ?>" title="<?= L_COMMENT_NEW_COMMENT_TITLE ?>"><?= L_COMMENT_NEW_COMMENT ?></a></li>
<?php
}
?>
		</ul>
		<?= $selector ?>
		<?= plxToken::getTokenPostMethod() ?>
		<input type="submit" name="btn_ok" value="<?= L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idCom[]', '<?= L_CONFIRM_DELETE ?>')" />
	</div>
<?php
if(!empty($portee)) {
?>
	<h3><a href="article.php?a=<?= $_GET['a'] ?>" title="<?= L_COMMENT_ARTICLE_LINKED_TITLE ?>"><?= $portee ?></a></h3>
<?php
}
?>
	<div class="scrollable-table">
		<table id="comments-table" class="full-width">
			<thead>
				<tr>
					<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idCom[]')" /></th>
					<th class="datetime"><?= L_COMMENTS_LIST_DATE ?></th>
<?php
			$all = ($_SESSION['selCom'] == 'all');
			if($all) {
?>
					<th class="status"><?= L_COMMENT_STATUS_FIELD ?></th>
<?php
			}
?>
					<th class="message"><?= L_COMMENTS_LIST_MESSAGE ?></th>
					<th class="author"><?= L_COMMENTS_LIST_AUTHOR ?> <?= L_COMMENT_EMAIL_FIELD ?></th>
					<th class="site"><?= L_COMMENT_SITE_FIELD ?></th>
					<th class="action"><?= L_COMMENTS_LIST_ACTION ?></th>
<?php
			if(empty($portee)) {
?>
					<th class="action"><?= L_COMMENT_ARTICLE_LINKED ?></th>
<?php
}
?>
				</tr>
			</thead>
			<tbody>

<?php
			# On va récupérer les commentaires
			$plxAdmin->getPage();
			$start = $plxAdmin->aConf['bypage_admin_coms']*($plxAdmin->page-1);
			$coms = $plxAdmin->getCommentaires($comSelMotif,'rsort',$start,$plxAdmin->aConf['bypage_admin_coms'],'all');
			if($coms) {
				while($plxAdmin->plxRecord_coms->loop()) { # On boucle
					$artId = $plxAdmin->plxRecord_coms->f('article');
					$status = $plxAdmin->plxRecord_coms->f('status');
					$id = $status.$artId.'.'.$plxAdmin->plxRecord_coms->f('numero');
					$query = 'c=' . $id;
					if(isset($_GET['a'])) {
						$query .= '&a=' . $_GET['a'];
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
?>
					<td class="wrap"><?= nl2br($plxAdmin->plxRecord_coms->f('content')) ?></td>
					<td class="author"><?php
					$author = $plxAdmin->plxRecord_coms->f('author');
					$mail = $plxAdmin->plxRecord_coms->f('mail');
					if(!empty($mail)) {
?><a href="mailto:<?= $mail ?>"><?= $author ?></a><?php
					} else {
						echo $author;
					}
?></td>
					<td class="site"><?php
					$site = $plxAdmin->plxRecord_coms->f('site');
					if(!empty($site)) {
?><a href="<?= $site ?>" target="_blank"><?= $site ?></a><?php
					} else {
						echo '&nbsp;';
					}
?></td>
					<td class="action">
						<a href="comment_new.php?<?= $query ?>" title="<?= L_COMMENT_ANSWER ?>"><?= L_COMMENT_ANSWER ?></a>
						<a href="comment.php?<?= $query ?>" title="<?= L_COMMENT_EDIT_TITLE ?>"><?= L_COMMENT_EDIT ?></a>
					</td>
<?php
			if(empty($portee)) {
?>
					<td class="action text-right"><a href="article.php?a=<?= $artId ?>" title="<?= L_COMMENT_ARTICLE_LINKED_TITLE ?>"><?= ltrim($artId, '0') ?></a></td>
<?php
}
?>
				</tr>
<?php
				}
			} else { # Pas de commentaires
				$colspan = $all ? 7 : 6;
				if(empty($portee)) {
					$colspan++;
				}
?>
				<tr>
					<td colspan="<?= $colspan ?>" class="text-center"><?= L_NO_COMMENT ?></td>
				</tr>
<?php
			}
			?>
			</tbody>
		</table>
	</div>

</form>

<div id="pagination">
<?php
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminCommentsPagination'));

	# Affichage de la pagination
	if($coms) {
		$sel = '&amp;sel='.$_SESSION['selCom'].(!empty($_GET['a'])?'&amp;a='.$_GET['a']:'');
		plxUtils::printPagination($nbComPagination, $plxAdmin->aConf['bypage_admin_coms'], $plxAdmin->page, 'comments.php?page=%d' . $sel);
	}
?>
</div>

<?php
if(!empty($plxAdmin->aConf['clef'])) {
?>
<ul class="unstyled-list">
	<li><?= L_COMMENTS_PRIVATE_FEEDS ?> :</li>
<?php
	$options = array(
		'hors-ligne'	=> L_COMMENT_OFFLINE_FEEDS,
		'en-ligne'		=> L_COMMENT_ONLINE_FEEDS,
	);

	$baseUrl = $plxAdmin->racine . 'feed.php?admin' . $plxAdmin->aConf['clef'] . '/commentaires/';
	foreach($options as $k=>$caption) {
?>
	<li><a href="<?= $baseUrl . $k ?>" title="<?= $caption ?>" download><?= $caption ?></a></li>
<?php
}
?>
</ul>
<?php
}

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsFoot'));

# On inclut le footer
include 'foot.php';
