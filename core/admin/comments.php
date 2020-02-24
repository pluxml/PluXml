<?php

/**
 * Listing des commentaires en attente de validation
 *
 * @package PLX
 * @author	Stephane F
 **/

include __DIR__ .'/prepend.php';
use Pluxml\PlxDate;
use Pluxml\PlxMsg;
use Pluxml\PlxToken;
use Pluxml\PlxUtils;

# Contrôle du token du formulaire
PlxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsPrepend'));

# Contrôle de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR);

# validation de l'id de l'article si passé en paramètre
if(isset($_GET['a']) AND !preg_match('/^_?[0-9]{4}$/',$_GET['a'])) {
	PlxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
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
	if(!$globArt = $plxAdmin->plxGlob_arts->query('/^'.$_GET['a'].'.(.*).xml$/','','sort',0,1)) {
		plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
		header('Location: index.php');
		exit;
	}
	# Infos sur l'article
	$aArt = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$globArt['0']);
	$portee = L_COMMENTS_ARTICLE_SCOPE.' &laquo;'.$aArt['title'].'&raquo;';
} else { # Commentaires globaux
	$portee = '';
}

# On inclut le header
include __DIR__ .'/top.php';

# Récupération du type de commentaire à afficher
$_GET['sel'] = !empty($_GET['sel']) ? $_GET['sel'] : '';
if(in_array($_GET['sel'], array('online', 'offline', 'all')))
	$comSel = PlxUtils::nullbyteRemove($_GET['sel']);
else
	$comSel = ((isset($_SESSION['selCom']) AND !empty($_SESSION['selCom'])) ? $_SESSION['selCom'] : 'all');

if(!empty($_GET['a'])) {
	
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
	$comSelMotif = '/^'.$mod.str_replace('_','',$_GET['a']).'.(.*).xml$/';
	$_SESSION['selCom'] = 'all';
	$nbComPagination=$plxAdmin->nbComments($comSelMotif);
}
elseif($comSel=='online') {
	$comSelMotif = '/^[0-9]{4}.(.*).xml$/';
	$_SESSION['selCom'] = 'online';
	$nbComPagination=$plxAdmin->nbComments('online');
}
elseif($comSel=='offline') {
	$comSelMotif = '/^_[0-9]{4}.(.*).xml$/';
	$_SESSION['selCom'] = 'offline';
	$nbComPagination=$plxAdmin->nbComments('offline');
}
elseif($comSel=='all') { // all
	$comSelMotif = '/^[[:punct:]]?[0-9]{4}.(.*).xml$/';
	$_SESSION['selCom'] = 'all';
	$nbComPagination=$plxAdmin->nbComments('all');
}

if($portee!='') {
	$h3 = '<h3>'.$portee.'</h3>';
}

$breadcrumbs = array();
$breadcrumbs[] = '<li '.($_SESSION['selCom']=='all'?'class="selected" ':'').'><a href="comments.php?sel=all&amp;page=1">'.L_ALL.'</a>&nbsp;<span class="tag">'.$plxAdmin->nbComments('all').'</span></li>';
$breadcrumbs[] = '<li '.($_SESSION['selCom']=='online'?'class="selected" ':'').'><a href="comments.php?sel=online&amp;page=1">'.L_COMMENT_ONLINE.'</a>&nbsp;<span class="tag">'.$plxAdmin->nbComments('online').'</span></li>';
$breadcrumbs[] = '<li '.($_SESSION['selCom']=='offline'?'class="selected" ':'').'><a href="comments.php?sel=offline&amp;page=1">'.L_COMMENT_OFFLINE.'</a>&nbsp;<span class="tag">'.$plxAdmin->nbComments('offline').'</span></li>';
if(!empty($_GET['a'])) {
	$breadcrumbs[] = '<a href="comment_new.php?a='.$_GET['a'].'" title="'.L_COMMENT_NEW_COMMENT_TITLE.'">'.L_COMMENT_NEW_COMMENT.'</a>';
}

# On va récupérer les commentaires
$plxAdmin->getPage();
$start = $plxAdmin->aConf['bypage_admin_coms']*($plxAdmin->page-1);
$coms = $plxAdmin->getCommentaires($comSelMotif,'rsort',$start,$plxAdmin->aConf['bypage_admin_coms'],'all');

//Vue.js datas initialisation
$builkDatas = array(
		'coms' => $coms, # true if there are comments
		'comSel' => $comSel, # comments list filter : all, online, offline
);
$datas = json_encode($builkDatas);

?>

<div class="adminheader">
	<h2 class="h3-like"><?= L_COMMENTS_ALL_LIST ?></h2>
	<ul>
		<?= implode($breadcrumbs); ?>
	</ul>
</div>

<div class="admin">

	<?php eval($plxAdmin->plxPlugins->callHook('AdminCommentsTop')) # Hook Plugins ?>

	<form action="comments.php<?= !empty($_GET['a'])?'?a='.$_GET['a']:'' ?>" method="post" id="form_comments">

		<div class="mtm pas  tableheader">
			<?= plxToken::getTokenPostMethod() ?>
			<button v-if="comSel==='online'" class="submit btn--primary" name="offline" type="submit"><i class="icon-comment"></i><?= L_COMMENT_SET_OFFLINE?></button>
			<button v-else-if="comSel==='offline'" class="submit btn--primary" name="online" type="submit"><i class="icon-comment"></i><?= L_COMMENT_SET_ONLINE?></button>
			<div v-else>
				<button class="submit btn--primary" name="online" type="submit"><i class="icon-comment"></i><?= L_COMMENT_SET_ONLINE?></button>
				<button class="submit btn--primary" name="offline" type="submit"><i class="icon-comment"></i><?= L_COMMENT_SET_OFFLINE?></button>
			</div>
			<!--<input type="submit" name="btn_ok" value="<?= L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idCom[]', '<?= L_CONFIRM_DELETE ?>')" />-->
		</div>

		<?php if(isset($h3)) echo $h3 ?>
	
		<div>
			<table id="comments-table" class="table">
				<thead>
					<tr>
						<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idCom[]')" /></th>
						<th><?= L_COMMENTS_LIST_DATE ?></th>
						<th class="w100"><?= L_COMMENTS_LIST_MESSAGE ?></th>
						<th><?= L_COMMENTS_LIST_AUTHOR ?></th>
						<th><?= L_COMMENTS_LIST_ACTION ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if($coms) {
						$num=0;
						while($plxAdmin->plxRecord_coms->loop()) { # On boucle
							$artId = $plxAdmin->plxRecord_coms->f('article');
							$status = $plxAdmin->plxRecord_coms->f('status');
							$id = $status.$artId.'.'.$plxAdmin->plxRecord_coms->f('numero');
							$content = nl2br($plxAdmin->plxRecord_coms->f('content'));
							if($_SESSION['selCom']=='all') {
								$content = $content.($status!=''?'<span class="tag--warning">'.L_COMMENT_OFFLINE:'');
							}
							# On génère notre ligne
							echo '<tr class="top type-'.$plxAdmin->plxRecord_coms->f('type').'">';
							echo '<td><input type="checkbox" name="idCom[]" value="'.$id.'" /></td>';
							echo '<td>'.PlxDate::formatDate($plxAdmin->plxRecord_coms->f('date')).'&nbsp;</td>';
							echo '<td>'.$content.'&nbsp;</td>';
							echo '<td>'.$plxAdmin->plxRecord_coms->f('author').'&nbsp;</td>';
							echo '<td>';
							echo '<a href="comment_new.php?c='.$id.(!empty($_GET['a'])?'&amp;a='.$_GET['a']:'').'" title="'.L_COMMENT_ANSWER.'">'.L_COMMENT_ANSWER.'</a>&nbsp;&nbsp;';
							echo '<a href="comment.php?c='.$id.(!empty($_GET['a'])?'&amp;a='.$_GET['a']:'').'" title="'.L_COMMENT_EDIT_TITLE.'">'.L_COMMENT_EDIT.'</a>&nbsp;&nbsp;';
							echo '<a href="article.php?a='.$artId.'" title="'.L_COMMENT_ARTICLE_LINKED_TITLE.'">'.L_COMMENT_ARTICLE_LINKED.'</a>';
							echo '</td></tr>';
						}
					} else { # Pas de commentaires
						echo '<tr><td colspan="5" class="center">'.L_NO_COMMENT.'</td></tr>';
					}
					?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="2">
							<button v-if="coms" class="submit btn--warning" name="delete" type="submit"><i class="icon-trash-empty"></i><?= L_DELETE?></button>
						</td>
						<td colspan="3" class="pagination right">
							<?php
								# Hook Plugins
								eval($plxAdmin->plxPlugins->callHook('AdminCommentsPagination'));
								# Affichage de la pagination
								if($coms) { # Si on a des commentaires
									# Calcul des pages
									$last_page = ceil($nbComPagination/$plxAdmin->aConf['bypage_admin_coms']);
									$stop = $plxAdmin->page + 2;
									if($stop<5) $stop=5;
									if($stop>$last_page) $stop=$last_page;
									$start = $stop - 4;
									if($start<1) $start=1;
									// URL generation
									$sel = '&amp;sel='.$_SESSION['selCom'].(!empty($_GET['a'])?'&amp;a='.$_GET['a']:'');
									$p_url = 'comments.php?page='.($plxAdmin->page-1).$sel;
									$n_url = 'comments.php?page='.($plxAdmin->page+1).$sel;
									$l_url = 'comments.php?page='.$last_page.$sel;
									$f_url = 'comments.php?page=1'.$sel;
									// Display pagination links
									$s = $plxAdmin->page>2 ? '<a href="'.$f_url.'" title="'.L_PAGINATION_FIRST_TITLE.'"><span class="btn"><i class="icon-angle-double-left"></i></span></a>' : '<span class="btn"><i class="icon-angle-double-left"></i></span>';
									echo $s;
									$s = $plxAdmin->page>1 ? '<a href="'.$p_url.'" title="'.L_PAGINATION_PREVIOUS_TITLE.'"><span class="btn"><i class="icon-angle-left"></i></span></a>' : '<span class="btn"><i class="icon-angle-left"></i></span>';
									echo $s;
									for($i=$start;$i<=$stop;$i++) {
										$s = $i==$plxAdmin->page ? '<span class="current btn">'.$i.'</span>' : '<a href="'.('comments.php?page='.$i.$artTitle).'" title="'.$i.'"><span class="btn">'.$i.'</span></a>';
										echo $s;
									}
									$s = $plxAdmin->page<$last_page ? '<a href="'.$n_url.'" title="'.L_PAGINATION_NEXT_TITLE.'"><span class="btn"><i class="icon-angle-right"></i></span></a>' : '<span class="btn"><i class="icon-angle-right"></i></span>';
									echo $s;
									$s = $plxAdmin->page<($last_page-1) ? '<a href="'.$l_url.'" title="'.L_PAGINATION_LAST_TITLE.'"><span class="btn"><i class="icon-angle-double-right"></i></span></a>' : '<span class="btn"><i class="icon-angle-double-right"></i></span>';
									echo $s;
								}
							?>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	
	</form>

	<?php if(!empty($plxAdmin->aConf['clef'])) : ?>
	<?= L_COMMENTS_PRIVATE_FEEDS ?> :
	<ul class="unstyled-list">
		<?php $urlp_hl = $plxAdmin->racine.'feed.php?admin'.$plxAdmin->aConf['clef'].'/commentaires/hors-ligne'; ?>
		<li><a href="<?= $urlp_hl ?>" title="<?= L_COMMENT_OFFLINE_FEEDS_TITLE ?>"><?= L_COMMENT_OFFLINE_FEEDS ?></a></li>
		<?php $urlp_el = $plxAdmin->racine.'feed.php?admin'.$plxAdmin->aConf['clef'].'/commentaires/en-ligne'; ?>
		<li><a href="<?= $urlp_el ?>" title="<?= L_COMMENT_ONLINE_FEEDS_TITLE ?>"><?= L_COMMENT_ONLINE_FEEDS ?></a></li>
	</ul>
	<?php endif; ?>

</div>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsFoot'));
# On inclut le footer
include __DIR__ .'/foot.php';
?>
