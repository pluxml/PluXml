<?php

/**
 * Listing des commentaires en attente de validation
 *
 * @package PLX
 * @author	Stephane F
 **/

require 'prepend.php';

if(!isset($plxAdmin)) {
	redirectLogin();
}

# Contrôle de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsPrepend'));

# validation de l'id de l'article si passé en paramètre
if(isset($_GET['a']) AND !preg_match('/^_?\d{4}$/',$_GET['a'])) {
	plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
	redirectAdmin();
}

# Suppression des commentaires sélectionnés
if(isset($_POST['selection']) AND !empty($_POST['btn_ok'])) {
	if(!empty($_POST['idCom'])) {
		switch($_POST['selection']) {
			case 'delete':
				foreach ($_POST['idCom'] as $k => $v) {
					$plxAdmin->delCommentaire($v);
				}
				break;
			case 'online':
			case 'offline':
				foreach ($_POST['idCom'] as $k => $v) {
					$plxAdmin->modCommentaire($v, $_POST['selection']);
				}
				break;
			default:
				redirectAdmin();
		}
	}
	reloadPage();
}

# Récupération des infos sur l'article attaché au commentaire si passé en paramètre
if(!empty($_GET['a'])) {
	# Infos sur notre article
	if(!$globArt = $plxAdmin->plxGlob_arts->query('/^'.$_GET['a'].'.(.*).xml$/','','sort',0,1)) {
		plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
		redirectAdmin();
	}

	# Infos sur l'article
	$aArt = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$globArt['0']);
}

# On inclut le header
require 'top.php';

# Récupération du type de commentaire à afficher
$_GET['sel'] = !empty($_GET['sel']) ? $_GET['sel'] : '';
if(in_array($_GET['sel'], array('online', 'offline', 'all')))
	$comSel = plxUtils::nullbyteRemove($_GET['sel']);
else
	$comSel = ((isset($_SESSION['selCom']) AND !empty($_SESSION['selCom'])) ? $_SESSION['selCom'] : 'all');

$h2 = L_COMMENTS_ALL_LIST;
$_SESSION['selCom'] = 'all';
$comSelMotif = '/^[[:punct:]]?\d{4}.(.*).xml$/';
$nbComPagination = 0;
if(!empty($_GET['a']) and preg_match('#^_?(\d{4})$#', $_GET['a'], $matches)) {
	switch ($comSel) {
		case 'online':
			$mod = '';
			break;
		case 'offline':
			$mod = '_';
			break;
		default:
			$mod = '_?';
	}
	$artId = $matches[1];
	$comSelMotif = '/^' . $mod . $artId . '\..*\.xml$/';
	$nbComPagination=$plxAdmin->nbComments($comSelMotif, 'all', $artId);
}
elseif($comSel=='online') {
	$_SESSION['selCom'] = $comSel;
	$comSelMotif = '/^\d{4}.(.*).xml$/';
	$nbComPagination=$plxAdmin->nbComments('online');
	$h2 = L_COMMENTS_ONLINE_LIST;
}
elseif($comSel=='offline') {
	$_SESSION['selCom'] = $comSel;
	$comSelMotif = '/^_\d{4}.(.*).xml$/';
	$nbComPagination=$plxAdmin->nbComments('offline');
	$h2 = L_COMMENTS_OFFLINE_LIST;
}
elseif($comSel=='all') { // all
	$nbComPagination=$plxAdmin->nbComments('all');
}

function selector($comSel, $id) {
	ob_start();
	if($comSel=='online')
		plxUtils::printSelect('selection', array(''=> L_FOR_SELECTION, 'offline' => L_COMMENT_SET_OFFLINE, '-'=>'-----', 'delete' => L_COMMENT_DELETE), '', false,'no-margin',$id);
	elseif($comSel=='offline')
		plxUtils::printSelect('selection', array(''=> L_FOR_SELECTION, 'online' => L_COMMENT_SET_ONLINE, '-'=>'-----', 'delete' => L_COMMENT_DELETE), '', false,'no-margin',$id);
	elseif($comSel=='all')
		plxUtils::printSelect('selection', array(''=> L_FOR_SELECTION, 'online' => L_COMMENT_SET_ONLINE, 'offline' => L_COMMENT_SET_OFFLINE,  '-'=>'-----','delete' => L_COMMENT_DELETE), '', false,'no-margin',$id);
	return ob_get_clean();
}

$selector=selector($comSel, 'id_selection');

eval($plxAdmin->plxPlugins->callHook('AdminCommentsTop'));

?>
<form action="comments.php<?= !empty($_GET['a'])?'?a='.$_GET['a']:'' ?>" method="post" id="form_comments">

	<div class="inline-form action-bar">
		<h2><?= $h2 ?></h2>
		<ul class="menu">
<?php
foreach(array(
	'all'		=> L_ALL,
	'online'	=> L_COMMENT_ONLINE,
	'offline'	=> L_COMMENT_OFFLINE,
) as $k=>$caption) {
	$selected = ($_SESSION['selCom'] == $k) ? 'class="selected"' : '';
?>
			<li><a <?= $selected ?> href="comments.php?sel=<?= $k ?>&page=1"><?= $caption ?></a>&nbsp;(<?= $plxAdmin->nbComments($k, 'all', !empty($artId) ? $artId : '\d{4}') ?>)</li>
<?php
}

if(!empty($aArt)) {
?>
			<li><a href="comment_new.php?a=<?= $aArt['numero'] ?>" title="<?= L_COMMENT_NEW_COMMENT_TITLE ?>"><?= L_COMMENT_NEW_COMMENT ?></a></li>
<?php
}
?>
		</ul>
		<?= $selector ?>
		<?= plxToken::getTokenPostMethod() ?>
		<input type="submit" name="btn_ok" value="<?= L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idCom[]', '<?= L_CONFIRM_DELETE ?>')" />
	</div>
<?php
if(!empty($aArt)) {
?>
	<h3><?= L_COMMENTS_ARTICLE_SCOPE ?> &laquo;<a href="article.php?a=<?= $aArt['numero'] ?>" target="plxArt"><?= $aArt['title'] ?></a>&raquo;</h3>
<?php
}
?>
	<div class="scrollable-table">
		<table id="comments-table" class="full-width">
			<thead>
				<tr>
					<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idCom[]')" /></th>
					<th class="datetime"><?= L_COMMENTS_LIST_DATE ?></th>
					<th class="message"><?= L_COMMENTS_LIST_MESSAGE ?></th>
					<th class="author"><?= L_COMMENTS_LIST_AUTHOR ?> <?= L_COMMENT_EMAIL_FIELD ?></th>
					<th class="site"><?= L_COMMENT_SITE_FIELD ?></th>
<?php
			$all = ($_SESSION['selCom'] == 'all');
			if($all) {
?>
					<th class="status"><?= L_COMMENT_STATUS_FIELD ?></th>
<?php
			}
?>
					<th class="action"><?= L_COMMENTS_LIST_ACTION ?></th>
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
				<tr class="top type-<?= $plxAdmin->plxRecord_coms->f('type') ?><?php if($all and $status=='_') { echo ' offline'; } ?>">
					<td><input type="checkbox" name="idCom[]" value="<?= $id ?>" /></td>
					<td class="datetime"><?= plxDate::formatDate($plxAdmin->plxRecord_coms->f('date')) ?></td>
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
?>
</td>
<?php
				if($all) {
?>
					<td class="status"><?= empty($status) ? L_COMMENT_ONLINE : L_COMMENT_OFFLINE ?></td>
<?php
				}
?>
					<td class="action">
						<a href="comment_new.php?<?= $query ?>" title="<?= L_COMMENT_ANSWER ?>"><?= L_COMMENT_ANSWER ?></a>
						<a href="comment.php?<?= $query ?>" title="<?= L_COMMENT_EDIT_TITLE ?>"><?= L_COMMENT_EDIT ?></a>
<?php
if(empty($aArt)) {
?>
						<a href="article.php?a=<?= $artId ?>" title="<?= L_COMMENT_ARTICLE_LINKED_TITLE ?>"><?= L_COMMENT_ARTICLE_LINKED ?></a>
<?php
}
?>
					</td>
				</tr>
<?php
				}
			} else { # Pas de commentaires
?>
				<tr>
					<td colspan="5" class="center"><?= L_NO_COMMENT ?></td>
				</tr>
<?php
			}
			?>
			</tbody>
		</table>
	</div>

</form>

<p id="pagination">
<?php
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminCommentsPagination'));
	# Affichage de la pagination
	if($coms) { # Si on a des articles (hors page)
		# Calcul des pages
		$last_page = ceil($nbComPagination/$plxAdmin->aConf['bypage_admin_coms']);
		$stop = $plxAdmin->page + 2;
		if($stop<5) $stop=5;
		if($stop>$last_page) $stop=$last_page;
		$start = $stop - 4;
		if($start<1) $start=1;
		# Génération des URLs
		$sel = '&amp;sel='.$_SESSION['selCom'].(!empty($_GET['a'])?'&amp;a='.$_GET['a']:'');
		$p_url = 'comments.php?page='.($plxAdmin->page-1).$sel;
		$n_url = 'comments.php?page='.($plxAdmin->page+1).$sel;
		$l_url = 'comments.php?page='.$last_page.$sel;
		$f_url = 'comments.php?page=1'.$sel;
		# Affichage des liens de pagination
		printf('<span class="p_page">'.L_PAGINATION.'</span>', '<input style="text-align:right;width:35px" onchange="window.location.href=\'comments.php?page=\'+this.value+\''.$sel.'\'" value="'.$plxAdmin->page.'" />', $last_page);
		$s = $plxAdmin->page>2 ? '<a href="'.$f_url.'" title="'.L_PAGINATION_FIRST_TITLE.'">&laquo;</a>' : '&laquo;';
		echo '<span class="p_first">'.$s.'</span>';
		$s = $plxAdmin->page>1 ? '<a href="'.$p_url.'" title="'.L_PAGINATION_PREVIOUS_TITLE.'">&lsaquo;</a>' : '&lsaquo;';
		echo '<span class="p_prev">'.$s.'</span>';
		for($i=$start;$i<=$stop;$i++) {
			$s = $i==$plxAdmin->page ? $i : '<a href="'.('comments.php?page='.$i.$sel).'" title="'.$i.'">'.$i.'</a>';
			echo '<span class="p_current">'.$s.'</span>';
		}
		$s = $plxAdmin->page<$last_page ? '<a href="'.$n_url.'" title="'.L_PAGINATION_NEXT_TITLE.'">&rsaquo;</a>' : '&rsaquo;';
		echo '<span class="p_next">'.$s.'</span>';
		$s = $plxAdmin->page<($last_page-1) ? '<a href="'.$l_url.'" title="'.L_PAGINATION_LAST_TITLE.'">&raquo;</a>' : '&raquo;';
		echo '<span class="p_last">'.$s.'</span>';
	}
?>
</p>

<?php if(!empty($plxAdmin->aConf['clef'])) : ?>

<ul class="unstyled-list">
	<li><?= L_COMMENTS_PRIVATE_FEEDS ?> :</li>
	<?php $urlp_hl = $plxAdmin->racine.'feed.php?admin'.$plxAdmin->aConf['clef'].'/commentaires/hors-ligne'; ?>
	<li><a href="<?= $urlp_hl ?>" title="<?= L_COMMENT_OFFLINE_FEEDS_TITLE ?>"><?= L_COMMENT_OFFLINE_FEEDS ?></a></li>
	<?php $urlp_el = $plxAdmin->racine.'feed.php?admin'.$plxAdmin->aConf['clef'].'/commentaires/en-ligne'; ?>
	<li><a href="<?= $urlp_el ?>" title="<?= L_COMMENT_ONLINE_FEEDS_TITLE ?>"><?= L_COMMENT_ONLINE_FEEDS ?></a></li>
</ul>

<?php endif; ?>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsFoot'));

# On inclut le footer
require 'foot.php';
