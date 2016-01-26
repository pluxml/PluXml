<?php

/**
 * Listing des articles
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexPrepend'));

# Suppression des articles selectionnes
if(isset($_POST['selection']) AND !empty($_POST['sel']) AND ($_POST['selection']=='delete') AND isset($_POST['idArt'])) {
	foreach ($_POST['idArt'] as $k => $v) $plxAdmin->delArticle($v);
	header('Location: index.php');
	exit;
}

# Récuperation de l'id de l'utilisateur
$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '[0-9]{3}' : $_SESSION['user']);

# Récuperation des paramètres
if(!empty($_GET['sel']) AND in_array($_GET['sel'], array('all','published', 'draft','mod'))) {
	$_SESSION['sel_get']=plxUtils::nullbyteRemove($_GET['sel']);
	$_SESSION['sel_cat']='';
}
else
	$_SESSION['sel_get']=(isset($_SESSION['sel_get']) AND !empty($_SESSION['sel_get']))?$_SESSION['sel_get']:'all';

if(!empty($_POST['sel_cat']))
	if(isset($_SESSION['sel_cat']) AND $_SESSION['sel_cat']==$_POST['sel_cat']) # annulation du filtre
		$_SESSION['sel_cat']='all';
	else # prise en compte du filtre
		$_SESSION['sel_cat']=$_POST['sel_cat'];
else
	$_SESSION['sel_cat']=(isset($_SESSION['sel_cat']) AND !empty($_SESSION['sel_cat']))?$_SESSION['sel_cat']:'all';

# Recherche du motif de sélection des articles en fonction des paramètres
$catIdSel = '';
$mod='';
switch ($_SESSION['sel_get']) {
case 'published':
	$catIdSel = '[home|0-9,]*FILTER[home|0-9,]*';
	$mod='';
	break;
case 'draft':
	$catIdSel = '[home|0-9,]*draft,FILTER[home|0-9,]*';
	$mod='_?';
	break;
case 'all':
	$catIdSel = '[home|draft|0-9,]*FILTER[draft|home|0-9,]*';
	$mod='_?';
	break;
case 'mod':
	$catIdSel = '[home|draft|0-9,]*FILTER[draft|home|0-9,]*';
	$mod='_';
	break;
}

switch ($_SESSION['sel_cat']) {
case 'all' :
	$catIdSel = str_replace('FILTER', '', $catIdSel); break;
case '000' :
	$catIdSel = str_replace('FILTER', '000', $catIdSel); break;
case 'home':
	$catIdSel = str_replace('FILTER', 'home', $catIdSel); break;
case preg_match('/^[0-9]{3}$/', $_SESSION['sel_cat'])==1:
	$catIdSel = str_replace('FILTER', $_SESSION['sel_cat'], $catIdSel);
}

# Nombre d'article sélectionnés
$nbArtPagination = $plxAdmin->nbArticles($catIdSel, $userId);

# Récupération du texte à rechercher
$artTitle = (!empty($_GET['artTitle']))?plxUtils::unSlash(trim(urldecode($_GET['artTitle']))):'';
if(empty($artTitle)) {
	 $artTitle = (!empty($_POST['artTitle']))?plxUtils::unSlash(trim(urldecode($_POST['artTitle']))):'';
}
$_GET['artTitle'] = $artTitle;

# On génère notre motif de recherche
$motif = '/^'.$mod.'[0-9]{4}.'.$catIdSel.'.'.$userId.'.[0-9]{12}.(.*)'.plxUtils::title2filename($_GET['artTitle']).'(.*).xml$/';

# Calcul du nombre de page si on fait une recherche
if($_GET['artTitle']!='') {
	if($arts = $plxAdmin->plxGlob_arts->query($motif))
		$nbArtPagination = sizeof($arts);
}

# Traitement
$plxAdmin->prechauffage($motif);
$plxAdmin->getPage();
$arts = $plxAdmin->getArticles('all'); # Recuperation des articles

# Génération de notre tableau des catégories
$aFilterCat['all'] = L_ARTICLES_ALL_CATEGORIES;
$aFilterCat['home'] = L_CATEGORY_HOME;
$aFilterCat['000'] = L_UNCLASSIFIED;
if($plxAdmin->aCats) {
	foreach($plxAdmin->aCats as $k=>$v) {
		$aCat[$k] = plxUtils::strCheck($v['name']);
		$aFilterCat[$k] = plxUtils::strCheck($v['name']);
	}
	$aAllCat[L_CATEGORIES_TABLE] = $aCat;
}
$aAllCat[L_SPECIFIC_CATEGORIES_TABLE]['home'] = L_CATEGORY_HOME_PAGE;
$aAllCat[L_SPECIFIC_CATEGORIES_TABLE]['draft'] = L_DRAFT;
$aAllCat[L_SPECIFIC_CATEGORIES_TABLE][''] = L_ALL_ARTICLES_CATEGORIES_TABLE;

# On inclut le header
include(dirname(__FILE__).'/top.php');

?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminIndexTop')) # Hook Plugins ?>

<form action="index.php" method="post" id="form_articles">

<div class="inline-form action-bar">
	<h2><?php echo L_ARTICLES_LIST ?></h2>
	<ul class="menu">
		<li><a <?php echo ($_SESSION['sel_get']=='all')?'class="selected" ':'' ?>href="index.php?sel=all&amp;page=1"><?php echo L_ALL ?></a><?php echo '&nbsp;('.$plxAdmin->nbArticles('all', $userId).')' ?></li>
		<li><a <?php echo ($_SESSION['sel_get']=='published')?'class="selected" ':'' ?>href="index.php?sel=published&amp;page=1"><?php echo L_ALL_PUBLISHED ?></a><?php echo '&nbsp;('.$plxAdmin->nbArticles('published', $userId, '').')' ?></li>
		<li><a <?php echo ($_SESSION['sel_get']=='draft')?'class="selected" ':'' ?>href="index.php?sel=draft&amp;page=1"><?php echo L_ALL_DRAFTS ?></a><?php echo '&nbsp;('.$plxAdmin->nbArticles('draft', $userId).')' ?></li>
		<li><a <?php echo ($_SESSION['sel_get']=='mod')?'class="selected" ':'' ?>href="index.php?sel=mod&amp;page=1"><?php echo L_ALL_AWAITING_MODERATION ?></a><?php echo '&nbsp;('.$plxAdmin->nbArticles('all', $userId, '_').')' ?></li>
	</ul>
	<?php
	echo plxToken::getTokenPostMethod();
	if($_SESSION['profil']<=PROFIL_MODERATOR) {
		plxUtils::printSelect('selection', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, false, 'id_selection');
		echo '<input name="sel" type="submit" value="'.L_OK.'" onclick="return confirmAction(this.form, \'id_selection\', \'delete\', \'idArt[]\', \''.L_CONFIRM_DELETE.'\')" />&nbsp;&nbsp;&nbsp;';
	}
	?>
	<?php plxUtils::printInput('page',1,'hidden'); ?>
</div>

<div class="grid">
	<div class="col sml-6">
		<?php plxUtils::printSelect('sel_cat', $aFilterCat, $_SESSION['sel_cat']) ?>
		<input class="<?php echo $_SESSION['sel_cat']!='all'?' select':'' ?>" type="submit" name="submit" value="<?php echo L_ARTICLES_FILTER_BUTTON ?>" />
	</div>
	<div class="col sml-6 text-right">
		<input type="text" name="artTitle" value="<?php echo plxUtils::strCheck($_GET['artTitle']) ?>" />
		<input class="<?php echo (!empty($_GET['artTitle'])?' select':'') ?>" type="submit" value="<?php echo L_ARTICLES_SEARCH_BUTTON ?>" />
	</div>
</div>

<div class="scrollable-table">
	<table id="articles-table" class="full-width">
		<thead>
			<tr>
				<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idArt[]')" /></th>
				<th><?php echo L_ARTICLE_ID.' '.L_ARTICLE ?></th>
				<th><?php echo L_ARTICLE_LIST_DATE ?></th>
				<th><?php echo L_ARTICLE_LIST_TITLE ?></th>
				<th><?php echo L_ARTICLE_LIST_CATEGORIES ?></th>
				<th><?php echo L_ARTICLE_LIST_NBCOMS ?></th>
				<th><?php echo L_ARTICLE_LIST_AUTHOR ?></th>
				<th class="action"><?php echo L_ARTICLE_LIST_ACTION ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		# On va lister les articles
		if($arts) { # On a des articles
			# Initialisation de l'ordre
			$num=0;
			$datetime = date('YmdHi');
			while($plxAdmin->plxRecord_arts->loop()) { # Pour chaque article
				$author = plxUtils::getValue($plxAdmin->aUsers[$plxAdmin->plxRecord_arts->f('author')]['name']);
				$publi =  (boolean)!($plxAdmin->plxRecord_arts->f('date') > $datetime);
				# Catégories : liste des libellés de toutes les categories
				$draft='';
				$libCats='';
				$catIds = explode(',', $plxAdmin->plxRecord_arts->f('categorie'));
				if(sizeof($catIds)>0) {
					$catsName = array();
					foreach($catIds as $catId) {
						if($catId=='home') $catsName[] = L_CATEGORY_HOME;
						elseif($catId=='draft') $draft= ' - <strong>'.L_CATEGORY_DRAFT.'</strong>';
						elseif(!isset($plxAdmin->aCats[$catId])) $catsName[] = L_UNCLASSIFIED;
						else $catsName[] = plxUtils::strCheck($plxAdmin->aCats[$catId]['name']);
					}
					if(sizeof($catsName)>0) {
						$libCats = $catsName[0];
						unset($catsName[0]);
						if(sizeof($catsName)>0) $libCats .= ' <a class="folder"><span>'.implode(', ', $catsName).'</span></a>';
					}
					else $libCats = L_UNCLASSIFIED;
				}
				# en attente de validation ?
				$idArt = $plxAdmin->plxRecord_arts->f('numero');
				$awaiting = $idArt[0]=='_' ? ' - <strong>'.L_AWAITING.'</strong>' : '';
				# Commentaires
				$nbComsToValidate = $plxAdmin->getNbCommentaires('/^_'.$idArt.'.(.*).xml$/','all');
				$nbComsValidated = $plxAdmin->getNbCommentaires('/^'.$idArt.'.(.*).xml$/','all');
				# On affiche la ligne
				echo '<tr>';
				echo '<td><input type="checkbox" name="idArt[]" value="'.$idArt.'" /></td>';
				echo '<td>'.$idArt.'</td>';
				echo '<td>'.plxDate::formatDate($plxAdmin->plxRecord_arts->f('date')).'&nbsp;</td>';
				echo '<td class="wrap"><a href="article.php?a='.$idArt.'" title="'.L_ARTICLE_EDIT_TITLE.'">'.plxUtils::strCheck($plxAdmin->plxRecord_arts->f('title')).'</a>'.$draft.$awaiting.'&nbsp;</td>';
				echo '<td>'.$libCats.'&nbsp;</td>';
				echo '<td><a title="'.L_NEW_COMMENTS_TITLE.'" href="comments.php?sel=offline&amp;a='.$plxAdmin->plxRecord_arts->f('numero').'&amp;page=1">'.$nbComsToValidate.'</a> / <a title="'.L_VALIDATED_COMMENTS_TITLE.'" href="comments.php?sel=online&amp;a='.$plxAdmin->plxRecord_arts->f('numero').'&amp;page=1">'.$nbComsValidated.'</a>&nbsp;</td>';
				echo '<td>'.plxUtils::strCheck($author).'&nbsp;</td>';
				echo '<td>';
				echo '<a href="article.php?a='.$idArt.'" title="'.L_ARTICLE_EDIT_TITLE.'">'.L_ARTICLE_EDIT.'</a>';
				if($publi AND $draft=='') # Si l'article est publié
					echo ' | <a href="'.PLX_ROOT.'?article'.intval($idArt).'/'.$plxAdmin->plxRecord_arts->f('url').'" title="'.L_ARTICLE_VIEW_TITLE.'">'.L_ARTICLE_VIEW.'</a>';
				echo "&nbsp;</td>";
				echo "</tr>";
			}
		} else { # Pas d'article
			echo '<tr><td colspan="8" class="center">'.L_NO_ARTICLE.'</td></tr>';
		}
		?>
		</tbody>
	</table>
</div>

</form>

<p id="pagination">
	<?php
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminIndexPagination'));
	# Affichage de la pagination
	if($arts) { # Si on a des articles (hors page)
		# Calcul des pages
		$last_page = ceil($nbArtPagination/$plxAdmin->bypage);
		$stop = $plxAdmin->page + 2;
		if($stop<5) $stop=5;
		if($stop>$last_page) $stop=$last_page;
		$start = $stop - 4;
		if($start<1) $start=1;
		# Génération des URLs
		$artTitle = (!empty($_GET['artTitle'])?'&amp;artTitle='.urlencode($_GET['artTitle']):'');
		$p_url = 'index.php?page='.($plxAdmin->page-1).$artTitle;
		$n_url = 'index.php?page='.($plxAdmin->page+1).$artTitle;
		$l_url = 'index.php?page='.$last_page.$artTitle;
		$f_url = 'index.php?page=1'.$artTitle;
		# Affichage des liens de pagination
		printf('<span class="p_page">'.L_PAGINATION.'</span>', '<input style="text-align:right;width:35px" onchange="window.location.href=\'index.php?page=\'+this.value+\''.$artTitle.'\'" value="'.$plxAdmin->page.'" />', $last_page);
		$s = $plxAdmin->page>2 ? '<a href="'.$f_url.'" title="'.L_PAGINATION_FIRST_TITLE.'">&laquo;</a>' : '&laquo;';
		echo '<span class="p_first">'.$s.'</span>';
		$s = $plxAdmin->page>1 ? '<a href="'.$p_url.'" title="'.L_PAGINATION_PREVIOUS_TITLE.'">&lsaquo;</a>' : '&lsaquo;';
		echo '<span class="p_prev">'.$s.'</span>';
		for($i=$start;$i<=$stop;$i++) {
			$s = $i==$plxAdmin->page ? $i : '<a href="'.('index.php?page='.$i.$artTitle).'" title="'.$i.'">'.$i.'</a>';
			echo '<span class="p_current">'.$s.'</span>';
		}
		$s = $plxAdmin->page<$last_page ? '<a href="'.$n_url.'" title="'.L_PAGINATION_NEXT_TITLE.'">&rsaquo;</a>' : '&rsaquo;';
		echo '<span class="p_next">'.$s.'</span>';
		$s = $plxAdmin->page<($last_page-1) ? '<a href="'.$l_url.'" title="'.L_PAGINATION_LAST_TITLE.'">&raquo;</a>' : '&raquo;';
		echo '<span class="p_last">'.$s.'</span>';
	}
	?>
</p>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>