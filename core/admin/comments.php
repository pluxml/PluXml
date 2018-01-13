<?php

/**
 * Listing des commentaires en attente de validation
 *
 * @package PLX
 * @author	Stephane F, J.P. Pourrez
 * @verson	2018-01-15
 **/

include(dirname(__FILE__).'/prepend.php');

# Contrôle du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsPrepend'));

# Contrôle de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR);

# validation de l'id de l'article si passé en paramètre.
# On vient de l'édition d'un article
if(isset($_GET['a']) AND !preg_match('/^_?\d{4}$/',$_GET['a'])) {
	plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
	header('Location: index.php');
	exit;
}

# Traitement de $_POST
if(!empty($_POST['idCom']) and !empty($_POST['selection']) and !empty($_POST['btn_ok'])) {
	switch($_POST['selection']) {
		case 'delete' :
			# Suppression des commentaires sélectionnés
			foreach ($_POST['idCom'] as $comId) $plxAdmin->delCommentaire($comId);
			break;
		case 'online' :
			# Validation des commentaires sélectionnés
			foreach ($_POST['idCom'] as $comId) $plxAdmin->modCommentaire($comId, 'online');
			break;
		case 'offline' :
			# Mise hors-ligne des commentaires sélectionnés
			foreach ($_POST['idCom'] as $comId) $plxAdmin->modCommentaire($comId, 'offline');
			break;
		default:
			eval($plxAdmin->plxPlugins->callHook('AdminCommentsPost'));
	}
	header('Location: comments.php'.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
	exit;
}

# Récupération des infos sur l'article attaché au commentaire si passé en paramètre
if(!empty($_GET['a'])) {
	# Infos sur notre article
	if(!$globArt = $plxAdmin->plxGlob_arts->query('/^'.$_GET['a'].'\.(.*)\.xml$/','','sort',0,1)) {
		plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
		header('Location: index.php');
		exit;
	}
	# Infos sur l'article
	$aArt = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$globArt['0']);
	$portee = L_COMMENTS_ARTICLE_SCOPE.' &laquo;'.$aArt['title'].'&raquo;';
}

# On inclut le header
include(dirname(__FILE__).'/top.php');

$comSels = array(
	'online'	=> array('motif' => '/^\d{4}.(.*).xml$/',				'mod' => '', 'h2'			 => L_COMMENTS_ONLINE_LIST),
	'offline'	=> array('motif' => '/^_\d{4}.(.*).xml$/',				'mod' => '_', 'h2'			 => L_COMMENTS_OFFLINE_LIST),
	'all'		=> array('motif' => '/^[[:punct:]]?\d{4}.(.*).xml$/',	'mod' => '[[:punct:]]?', 'h2' => L_COMMENTS_ALL_LIST)
);

# Récupération du type de commentaire à afficher
if(!empty($_GET['sel']) and array_key_exists($_GET['sel'], $comSels))
	$comSel = plxUtils::nullbyteRemove($_GET['sel']);
elseif(!empty($_SESSION['selCom']) and array_key_exists($_SESSION['selCom'], $comSels))
	$comSel = $_SESSION['selCom'];
else
	$comSel = 'all';

if(!empty($_GET['a'])) {
	$comSelMotif = '/^'.$comSels[$comSel].str_replace('_','',$_GET['a']).'\.(.*)\.xml$/';
	$nbComPagination=$plxAdmin->nbComments($comSelMotif);
} else {
	$comSelMotif = $comSels[$comSel]['motif'];
	$nbComPagination=$plxAdmin->nbComments($comSel);
}
$_SESSION['selCom'] = $comSel;
$h2 = "<h2>{$comSels[$comSel]['h2']}</h2>";

$breadcrumbs = array(
	'<li><a '.($comSel=='all'?'class="selected" ':'').'href="comments.php?sel=all&page=1">'.L_ALL.'</a>&nbsp;('.$plxAdmin->nbComments('all').')</li>',
	'<li><a '.($comSel=='online'?'class="selected" ':'').'href="comments.php?sel=online&page=1">'.L_COMMENT_ONLINE.'</a>&nbsp;('.$plxAdmin->nbComments('online').')</li>',
	'<li><a '.($comSel=='offline'?'class="selected" ':'').'href="comments.php?sel=offline&page=1">'.L_COMMENT_OFFLINE.'</a>&nbsp;('.$plxAdmin->nbComments('offline').')</li>'
);
if(!empty($_GET['a'])) {
	$breadcrumbs[] = '<a href="comment_new.php?a='.$_GET['a'].'" title="'.L_COMMENT_NEW_COMMENT_TITLE.'">'.L_COMMENT_NEW_COMMENT.'</a>';
}

function selector($comSel, $id) {
	$selectsList = array(
		'online'	=> array(''=> L_FOR_SELECTION, 'offline' => L_COMMENT_SET_OFFLINE),
		'offline'	=> array(''=> L_FOR_SELECTION, 'online' => L_COMMENT_SET_ONLINE),
		'all'		=> array(''=> L_FOR_SELECTION, 'online' => L_COMMENT_SET_ONLINE, 'offline' => L_COMMENT_SET_OFFLINE)
	);
	$select = $selectsList[$comSel];
	$select['-'] = '-----';
	$select['delete'] = L_COMMENT_DELETE;

	ob_start();
	plxUtils::printSelect('selection', $select, '', false, 'no-margin', $id);
	return ob_get_clean();
}

$selector=selector($comSel, 'id_selection');

eval($plxAdmin->plxPlugins->callHook('AdminCommentsTop')) # Hook Plugins
?>

<form action="comments.php<?php echo !empty($_GET['a'])?'?a='.$_GET['a']:'' ?>" method="post" id="form_comments">
	<div class="inline-form action-bar">
		<?php echo $h2 ?>
		<ul class="menu">
			<?php echo implode($breadcrumbs); ?>
		</ul>
		<?php echo $selector ?>
		<?php echo plxToken::getTokenPostMethod() ?>
		<input type="submit" name="btn_ok" value="<?php echo L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idCom[]', '<?php echo L_CONFIRM_DELETE ?>')" />
	</div>
	<?php if(!empty($portee)) echo "<h3>$portee</h3>"; ?>
	<div class="scrollable-table">
		<table id="comments-table" class="full-width<?php if(function_exists('geoip_country_code_by_name')) echo ' flag'; ?>">
			<thead>
				<tr>
					<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idCom[]')" /></th>
					<th class="datetime"><?php echo L_COMMENTS_LIST_DATE ?></th>
					<th class="message"><?php echo L_COMMENTS_LIST_MESSAGE ?></th>
					<th class="ip-address"><?php echo L_COMMENTS_LIST_IP_ADDRESS ?></th>
					<th class="author"><?php echo L_COMMENTS_LIST_AUTHOR ?></th>
					<th class="action"><?php echo L_COMMENTS_LIST_ACTION ?></th>
				</tr>
			</thead>
			<tbody>
<?php
			# On va récupérer les commentaires
			$plxAdmin->getPage();
			$start = $plxAdmin->aConf['bypage_admin_coms']*($plxAdmin->page-1);
			$coms = $plxAdmin->getCommentaires($comSelMotif,'rsort',$start,$plxAdmin->aConf['bypage_admin_coms'],'all');
			if($coms) {
				# $num=0;
				$titles = array(
					'answer'	=> L_COMMENT_ANSWER,
					'edit'		=> L_COMMENT_EDIT_TITLE,
					'artLink'	=> L_COMMENT_ARTICLE_LINKED_TITLE
				);
				$captions = array(
					'answer'	=> L_COMMENT_ANSWER,
					'edit'		=> L_COMMENT_EDIT,
					'artLink'	=> L_COMMENT_ARTICLE_LINKED
				);
				while($plxAdmin->plxRecord_coms->loop()) { # On boucle
					$artId = $plxAdmin->plxRecord_coms->f('article');
					$status = $plxAdmin->plxRecord_coms->f('status');
					$idCom = $status.$artId.'.'.$plxAdmin->plxRecord_coms->f('numero');
					$content = nl2br($plxAdmin->plxRecord_coms->f('content'));
					$ipAddr = $plxAdmin->plxRecord_coms->f('ip');
					/* *
					 * L'utilisation de la fonction geoip_region_by_name() nécessite la base de donnees GeoIPRegion.dat.
					 * Celle-ci n'est pas installée automatiquement avec le module PHP GeoIp (Ubuntu 17.04).
					 * */
					$flag = '';
					if(!empty($ipAddr) and function_exists('geoip_country_code_by_name')) {
						$country = geoip_country_code_by_name($ipAddr);
						if(!empty($country)) {
							$flag = '<br /><img class="flag" src="'.PLX_FLAGS_32_PATH.$country.'.png" alt="'.$country.'" title="'.$country.'" />';
						}
					}
					$author = $plxAdmin->plxRecord_coms->f('author');
					$mail = $plxAdmin->plxRecord_coms->f('mail');
					if(!empty($mail)) {
						$author = '<a href="mailto:'.$mail.'" title="'.$mail.'">'.$author.'</a>';
					}
					$site = trim($plxAdmin->plxRecord_coms->f('site'));
					if(!empty($site)) {
						$site = '<br /><a href="'.$site.'" rel="nofollow noreferrer" target="_blank" title="'.$site.'">'.L_COMMENTS_SITE.'</a>';
					}
					if($_SESSION['selCom']=='all') {
						$content = '<strong>'.($status==''?L_COMMENT_ONLINE:L_COMMENT_OFFLINE).'</strong>&nbsp;-&nbsp;'.$content;
					}
					# On génère notre ligne
					$type = $plxAdmin->plxRecord_coms->f('type');
					$dateCom = plxDate::formatDate($plxAdmin->plxRecord_coms->f('date'));
					$a = (!empty($_GET['a'])) ? '&a='.$_GET['a'] : ''; # On revient de l'édition d'un article
					$artIdNum = intval($artId);

					echo <<< ROW
				<tr class="top type-$type">
					<td><input type="checkbox" name="idCom[]" value="$idCom" /></td>
					<td class="datetime">$dateCom</td>
					<td class="content wrap"><div>
$content
					</div></td>
					<td class="ip-address" data-ip="$ipAddr">$ipAddr$flag</td>
					<td class="author">$author$site</td>
					<td class="action">
					   <a href="comment_new.php?c=$idCom.$a" title="{$titles['answer']}">{$captions['answer']}</a>
					   <a href="comment.php?c=$idCom.$a" title="{$titles['edit']}">{$captions['edit']}</a>
					   <a href="article.php?a=$artId" title="{$titles['artLink']}">{$captions['artLink']}</a> (<em>n° $artIdNum</em>)
					</td>
				</tr>\n
ROW;
				} # fin de boucle pour les commentaires
			} else { # Pas de commentaires
				echo '<tr><td colspan="6" class="center">'.L_NO_COMMENT.'</td></tr>';
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
		$sel = '&sel='.$_SESSION['selCom'].(!empty($_GET['a'])?'&a='.$_GET['a']:'');
		$p_url = 'comments.php?page='.($plxAdmin->page-1).$sel;
		$n_url = 'comments.php?page='.($plxAdmin->page+1).$sel;
		$l_url = 'comments.php?page='.$last_page.$sel;
		$f_url = 'comments.php?page=1'.$sel;
		# Affichage des liens de pagination
		printf('<span class="p_page">'.L_PAGINATION.'</span>', '<input style="text-align:right;width:35px" onchange="window.location.href=\'comments.php?page=\'+this.value+\''.$sel.'\'" value="'.$plxAdmin->page.'" />', $last_page);
		$s = $plxAdmin->page>2 ? '<a href="'.$f_url.'" title="'.L_PAGINATION_FIRST_TITLE.'" accesskey="b">&laquo;</a>' : '&laquo;';
		echo '<span class="p_first">'.$s.'</span>';
		$s = $plxAdmin->page>1 ? '<a href="'.$p_url.'" title="'.L_PAGINATION_PREVIOUS_TITLE.'" accesskey="p">&lsaquo;</a>' : '&lsaquo;';
		echo '<span class="p_prev">'.$s.'</span>';
		for($i=$start;$i<=$stop;$i++) {
			$s = $i==$plxAdmin->page ? $i : '<a href="'.('comments.php?page='.$i.$sel).'" title="'.$i.'">'.$i.'</a>';
			echo '<span class="p_current">'.$s.'</span>';
		}
		$s = $plxAdmin->page<$last_page ? '<a href="'.$n_url.'" title="'.L_PAGINATION_NEXT_TITLE.'" accesskey="n">&rsaquo;</a>' : '&rsaquo;';
		echo '<span class="p_next">'.$s.'</span>';
		$s = $plxAdmin->page<($last_page-1) ? '<a href="'.$l_url.'" title="'.L_PAGINATION_LAST_TITLE.'" accesskey="e">&raquo;</a>' : '&raquo;';
		echo '<span class="p_last">'.$s.'</span>';
?>
<script type="text/javascript">
	(function() {
		'use strict';

		function gotoPage(accessKey) {
			const anchor = document.body.querySelector('#pagination a[accesskey="' + accessKey + '"]');
			if(anchor != null) {
				anchor.click();
				return true;
			}
			return false;
		}

		// https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/code
		window.addEventListener('keydown', function(keyboardEvent) {
			if (keyboardEvent.preventDefaulted) {
			    return; // Do nothing if event already handled
			}

			if(!keyboardEvent.ctrlKey && !keyboardEvent.metaKey && !keyboardEvent.shiftKey) {
				switch(keyboardEvent.code) {
					case 'ArrowLeft':	if (gotoPage('p')) { keyboardEvent.preventDefault(); }  break;
					case 'ArrowRight':	if (gotoPage('n')) { keyboardEvent.preventDefault(); }  break;
					case 'Home':		if (gotoPage('b')) { keyboardEvent.preventDefault(); }  break;
					case 'End':			if (gotoPage('e')) { keyboardEvent.preventDefault(); }  break;
					default: // console.log('A key is depressed: ' + keyboardEvent.code);
				}
			}
		});

	})();
</script>
<?php
	}
?>
</p>

<?php if(!empty($plxAdmin->aConf['clef'])) : ?>

<ul class="unstyled-list">
	<li><?php echo L_COMMENTS_PRIVATE_FEEDS ?> :</li>
	<?php $urlp_hl = $plxAdmin->racine.'feed.php?admin'.$plxAdmin->aConf['clef'].'/commentaires/hors-ligne'; ?>
	<li><a href="<?php echo $urlp_hl ?>" title="<?php echo L_COMMENT_OFFLINE_FEEDS_TITLE ?>"><?php echo L_COMMENT_OFFLINE_FEEDS ?></a></li>
	<?php $urlp_el = $plxAdmin->racine.'feed.php?admin'.$plxAdmin->aConf['clef'].'/commentaires/en-ligne'; ?>
	<li><a href="<?php echo $urlp_el ?>" title="<?php echo L_COMMENT_ONLINE_FEEDS_TITLE ?>"><?php echo L_COMMENT_ONLINE_FEEDS ?></a></li>
</ul>

<?php endif; ?>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>