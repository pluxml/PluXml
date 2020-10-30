<?php

/**
 * Listing des articles
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include 'prepend.php';

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
$mod='_?';
switch ($_SESSION['sel_get']) {
	case 'published':
		$catIdSel = '[home|0-9,]*FILTER[home|0-9,]*';
		$mod = '';
		break;
	case 'mod':
		$catIdSel = '[home|draft|0-9,]*FILTER[draft|home|0-9,]*';
		$mod='_';
		break;
	case 'draft':
		$catIdSel = '[home|0-9,]*draft,FILTER[home|0-9,]*';
		break;
	default: // all
		$catIdSel = '[home|draft|0-9,]*FILTER[draft|home|0-9,]*';
}

if(preg_match('/^(\d{3})$/', $_SESSION['sel_cat'], $matches)) {
	$cats = $matches[1];
} else {
	switch ($_SESSION['sel_cat']) {
		case '000' : $cats = '000'; break;
		case 'home': $cats = 'home'; break;
		default: $cats = ''; // all
	}
}
$catIdSel = str_replace('FILTER', $cats, $catIdSel);

# Nombre d'article sélectionnés
$nbArtPagination = $plxAdmin->nbArticles($catIdSel, $userId, $mod);

# Récupération du texte à rechercher
$artTitle = (!empty($_GET['artTitle']))?plxUtils::unSlash(trim(urldecode($_GET['artTitle']))):'';
if(empty($artTitle)) {
	 $artTitle = (!empty($_POST['artTitle']))?plxUtils::unSlash(trim(urldecode($_POST['artTitle']))):'';
}
$_GET['artTitle'] = $artTitle;

# On génère notre motif de recherche
if(is_numeric($_GET['artTitle'])) {
	$artId = str_pad($_GET['artTitle'],4,'0',STR_PAD_LEFT);
	$motif = '/^'.$mod.$artId.'.'.$catIdSel.'.'.$userId.'.[0-9]{12}.(.*).xml$/';
} else {
	$motif = '/^'.$mod.'[0-9]{4}.'.$catIdSel.'.'.$userId.'.[0-9]{12}.(.*)'.plxUtils::urlify($_GET['artTitle']).'(.*).xml$/';
}
# Calcul du nombre de page si on fait une recherche
if($_GET['artTitle']!='') {
	if($arts = $plxAdmin->plxGlob_arts->query($motif))
		$nbArtPagination = sizeof($arts);
}

# Traitement
$plxAdmin->prechauffage($motif);
$plxAdmin->getPage();
$arts = $plxAdmin->getArticles('all'); # Récupération des articles

# Génération de notre tableau des catégories
$aFilterCat['all'] = L_ARTICLES_ALL_CATEGORIES;
$aFilterCat['home'] = L_HOMEPAGE;
$aFilterCat['000'] = L_UNCLASSIFIED;
if($plxAdmin->aCats) {
	foreach($plxAdmin->aCats as $k=>$v) {
		$aCat[$k] = plxUtils::strCheck($v['name']);
		$aFilterCat[$k] = plxUtils::strCheck($v['name']);
	}
	$aAllCat[L_CATEGORIES] = $aCat;
}
$aAllCat[L_SPECIFIC_CATEGORIES_TABLE]['home'] = L_HOMEPAGE;
$aAllCat[L_SPECIFIC_CATEGORIES_TABLE]['draft'] = L_DRAFT;
$aAllCat[L_SPECIFIC_CATEGORIES_TABLE][''] = L_ALL_ARTICLES_CATEGORIES_TABLE;

# On inclut le header
include __DIR__ .'/top.php';
?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminIndexTop')) # Hook Plugins ?>

<form action="index.php" method="post" id="form_articles">

<div class="inline-form action-bar">
	<h2><?= L_ARTICLES_LIST ?></h2>
	<ul class="menu">
		<li><a <?= ($_SESSION['sel_get']=='all') ? 'class="selected" ' : '' ?>href="index.php?sel=all&page=1"><?= L_ALL ?></a><?= '&nbsp;('.$plxAdmin->nbArticles('all', $userId).')' ?></li>
		<li><a <?= ($_SESSION['sel_get']=='published') ? 'class="selected" ' : '' ?>href="index.php?sel=published&page=1"><?= L_ALL_PUBLISHED ?></a><?= '&nbsp;('.$plxAdmin->nbArticles('published', $userId, '').')' ?></li>
		<li><a <?= ($_SESSION['sel_get']=='draft') ? 'class="selected" ' : '' ?>href="index.php?sel=draft&page=1"><?= L_ALL_DRAFTS ?></a><?= '&nbsp;('.$plxAdmin->nbArticles('draft', $userId).')' ?></li>
		<li><a <?= ($_SESSION['sel_get']=='mod') ? 'class="selected" ' : '' ?>href="index.php?sel=mod&page=1"><?= L_AWAITING ?></a><?= '&nbsp;('.$plxAdmin->nbArticles('all', $userId, '_').')' ?></li>
	</ul>
	<?= plxToken::getTokenPostMethod(); ?>
<?php
	if($_SESSION['profil'] <= PROFIL_MODERATOR) {
		plxUtils::printSelect('selection', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, false, 'id_selection');
?>
	<input name="sel" type="submit" value="<?= L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idArt[]', '<?= L_CONFIRM_DELETE ?>')" />
	<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
<?php
	}
?>

	<?php plxUtils::printInput('page', 1, 'hidden'); ?>

</div>

<div class="grid">
	<div class="col med-6">
		<?php plxUtils::printSelect('sel_cat', $aFilterCat, $_SESSION['sel_cat']) ?>
		<input class="<?= $_SESSION['sel_cat']!='all'?' select':'' ?>" type="submit" value="<?= L_ARTICLES_FILTER_BUTTON ?>" />
	</div>
	<div class="col med-6 med-text-right">
		<input id="index-search" placeholder="<?= L_SEARCH_PLACEHOLDER ?>" type="text" name="artTitle" value="<?= plxUtils::strCheck($_GET['artTitle']) ?>" />
		<input class="<?= (!empty($_GET['artTitle'])?' select':'') ?>" type="submit" value="<?= L_SEARCH ?>" />
	</div>
</div>

<div class="scrollable-table">
	<table id="articles-table" class="full-width">
		<thead>
			<tr>
<?php
	if($_SESSION['profil'] <= PROFIL_MODERATOR) {
?>
				<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idArt[]')" /></th>
<?php
	}
?>
				<th>#</th>
				<th><?= L_DATE ?></th>
				<th><?= L_TITLE ?></th>
				<th><?= L_ARTICLE_LIST_CATEGORIES ?></th>
				<th title="<?= L_COMMENT_ARTICLE_FIELD ?>"><?= L_ARTICLE_LIST_NBCOMS ?></th>
				<th><?= L_AUTHOR ?></th>
				<th class="action"><?= L_ACTION ?></th>
			</tr>
		</thead>
		<tbody>
<?php
		# On va lister les articles
		if($arts) { # On a des articles
			# Initialisation de l'ordre
			$datetime = date('YmdHi');
			while($plxAdmin->plxRecord_arts->loop()) { # Pour chaque article
				$author = plxUtils::getValue($plxAdmin->aUsers[$plxAdmin->plxRecord_arts->f('author')]['name']);
				$publi = (boolean)!($plxAdmin->plxRecord_arts->f('date') > $datetime);
				# Catégories : liste des libellés de toutes les categories
				$draft='';
				$libCats='';
				$aCats = array();
				$catIds = explode(',', $plxAdmin->plxRecord_arts->f('categorie'));
				if(sizeof($catIds)>0) {
					foreach($catIds as $catId) {
						$selected = ($catId==$_SESSION['sel_cat'] ? ' selected="selected"' : '');
						if($catId=='draft') $draft = ' - <strong>'.L_DRAFT.'</strong>';
						elseif($catId=='home') $aCats['home'] = '<option value="home"'.$selected.'>'.L_HOMEPAGE.'</option>';
						elseif($catId=='000') $aCats['000'] = '<option value="000"'.$selected.'>'.L_UNCLASSIFIED.'</option>';
						elseif(isset($plxAdmin->aCats[$catId])) $aCats[$catId] = '<option value="'.$catId.'"'.$selected.'>'.plxUtils::strCheck($plxAdmin->aCats[$catId]['name']).'</option>';
					}

				}
				# en attente de validation ?
				$idArt = $plxAdmin->plxRecord_arts->f('numero');
				$awaiting = $idArt[0]=='_' ? ' - <strong>'.L_AWAITING.'</strong>' : '';
				# Commentaires
				$nbComsToValidate = $plxAdmin->getNbCommentaires('/^_'.$idArt.'.(.*).xml$/','all');
				$nbComsValidated = $plxAdmin->getNbCommentaires('/^'.$idArt.'.(.*).xml$/','all');
				# On affiche la ligne
?>
			<tr>
<?php
				if($_SESSION['profil'] <= PROFIL_MODERATOR) {
?>
				<td><input type="checkbox" name="idArt[]" value="<?= $idArt ?>" /></td>
<?php
				}
?>
				<td><?= substr($idArt, -4) ?></td>
				<td><?= plxDate::formatDate($plxAdmin->plxRecord_arts->f('date')) ?></td>
				<td><a href="article.php?a=<?= $idArt?>" title="<?= L_ARTICLE_EDIT_TITLE ?>"><?= plxUtils::strCheck($plxAdmin->plxRecord_arts->f('title')) ?></a><?= $draft . $awaiting ?></td>
				<td>
<?php
				if(sizeof($aCats) > 1) {
?>
					<select name="sel_cat2" class="ddcat" onchange="this.form.sel_cat.value=this.value; this.form.submit();">
					<?= implode(PHP_EOL, $aCats) ?>
					</select>
<?php
				} else {
?>
					<?= strip_tags(implode('', $aCats)) ?>
<?php
				}
?>
				</td>
				<td><a title="<?= L_NEW_COMMENTS_TITLE ?>" href="comments.php?sel=offline&a=<?= $plxAdmin->plxRecord_arts->f('numero') ?>&page=1"><?= $nbComsToValidate ?></a> / <a title="<?= L_VALIDATED_COMMENTS_TITLE ?>" href="comments.php?sel=online&a=<?=$plxAdmin->plxRecord_arts->f('numero') ?>&page=1"><?= $nbComsValidated ?></a></td>
				<td><?= plxUtils::strCheck($author) ?></td>
				<td>
					<a href="article.php?a=<?= $idArt ?>" title="<?= L_ARTICLE_EDIT_TITLE ?>"><?= L_EDIT ?></a>
<?php
				if($publi && empty($draft)) { # Si l'article est publié
					$href = $plxAdmin->urlRewrite('?article' . intval($idArt) . '/' . $plxAdmin->plxRecord_arts->f('url'));
?>
					<a href="<?= $href ?>" title="<?= L_ARTICLE_VIEW_TITLE ?>"><?= L_VIEW ?></a>
<?php
				}
?>
				</td>
			</tr>
<?php
			} // fin de boucle articles
		} else {
			// Pas d'article
?>
			<tr>
				<td colspan="8" class="center"><?= L_NO_ARTICLE ?></td>
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
	eval($plxAdmin->plxPlugins->callHook('AdminIndexPagination'));
	# Affichage de la pagination
	const DELTA_PAGINATION = 3;
	if($arts && $nbArtPagination > $plxAdmin->bypage) { # S'il y a plusieurs pages d'articles
		# Calcul des pages
		$last_page = ceil($nbArtPagination / $plxAdmin->bypage);

		$artTitle = !empty($_GET['artTitle']) ? '&artTitle=' . $_GET['artTitle'] : '';
		# Affichage des liens de pagination
?>
	<div class="col med-4 lrg-2">
<?php printf('<span class="p_page">'.L_PAGINATION.'</span>', '<input style="text-align:right;width:35px" onchange="window.location.href=\'index.php?page=\'+this.value+\''.$artTitle.'\'" value="'.$plxAdmin->page.'" />', $last_page); ?>
	</div>
	<div class="col med-8 lrg-10">
<?php

		if($plxAdmin->page > 2) {
?><a href="index.php?page=<?= '1' . $artTitle ?>" title="<?= L_PAGINATION_FIRST_TITLE ?>">⏪</a><?php
		} else {
?><span>⏪</span><?php
		}
		if($plxAdmin->page > 1) {
?><a href="index.php?page=<?= ($plxAdmin->page-1) . $artTitle ?>" title="<?= L_PAGINATION_PREVIOUS_TITLE ?>">◀️</a><?php
		} else {
?><span>◀️</span><?php
		}

		if($last_page <= 2 * DELTA_PAGINATION  + 1) {
			$iMin = 1; $iMax = $last_page;
		} else {
			if($plxAdmin->page > DELTA_PAGINATION + 1) {
				$iMin = ($last_page - $plxAdmin->page > DELTA_PAGINATION) ? $plxAdmin->page - DELTA_PAGINATION : $last_page - 2 * DELTA_PAGINATION;
			} else {
				$iMin = 1;
			}
			$iMax =  $iMin + 2 * DELTA_PAGINATION;
		}
		for($i=$iMin; $i<=$iMax; $i++) { // On boucle sur les pages
			if($i != $plxAdmin->page) {
?><a href="index.php?page=<?= $i.$artTitle ?>"><?= $i ?></a><?php
			} else {
?><span class="p_current"><?= $i ?></span><?php
			}
		}
		if($plxAdmin->page < $last_page) {
?><a href="index.php?page=<?= ($plxAdmin->page+1) . $artTitle ?>" title="<?= L_PAGINATION_NEXT_TITLE ?>">▶️</a><?php
		} else {
?><span>▶️</span><?php
		}
		if($plxAdmin->page < $last_page - 1) {
?><a href="index.php?page=<?= $last_page . $artTitle ?>" title="<?= L_PAGINATION_LAST_TITLE ?>">⏩</a><?php
		} else {
?><span class="p_last">⏩</span><?php
		}
	} // fin de pagination
?>
	</div>
</div>
<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexFoot'));

# On inclut le footer
include __DIR__ .'/foot.php';
