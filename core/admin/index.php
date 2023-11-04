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

# Récuperation des paramètres
if(!empty($_GET['sel']) AND in_array($_GET['sel'], ['all', 'published', 'draft','mod',])) {
	$_SESSION['sel_get'] = plxUtils::nullbyteRemove($_GET['sel']);
	$_SESSION['sel_cat'] = '';
} else {
	$_SESSION['sel_get']=(isset($_SESSION['sel_get']) AND !empty($_SESSION['sel_get']))?$_SESSION['sel_get']:'all';
}

# Récuperation de l'id de l'utilisateur
if($_SESSION['profil'] < PROFIL_WRITER) {
	if(isset($_POST['sel_user'])) {
		if(preg_match('#^\d{3}$#', $_POST['sel_user']) and array_key_exists($_POST['sel_user'], $plxAdmin->aUsers)) {
			$userId = $_POST['sel_user'];
			$_SESSION['sel_user'] = $userId;
		} else {
			$userId = '\d{3}';
			$_SESSION['sel_user'] = '';
		}
	} elseif(isset($_SESSION['sel_user']) and array_key_exists($_SESSION['sel_user'], $plxAdmin->aUsers)) {
		$userId = $_SESSION['sel_user'];
	} else {
		$userId = '\d{3}';
		$_SESSION['sel_user'] = '';
	}
} else {
	$userId = $_SESSION['user'];
	$_SESSION['sel_user'] = $userId;
}

if(!empty($_POST['sel_cat']))
	if(isset($_SESSION['sel_cat']) AND $_SESSION['sel_cat']==$_POST['sel_cat']) # annulation du filtre
		$_SESSION['sel_cat']='all';
	else # prise en compte du filtre
		$_SESSION['sel_cat']=$_POST['sel_cat'];
else
	$_SESSION['sel_cat']=(isset($_SESSION['sel_cat']) AND !empty($_SESSION['sel_cat']))?$_SESSION['sel_cat']:'all';

# Recherche du motif de sélection des articles en fonction des paramètres
switch ($_SESSION['sel_cat']) {
	case 'home':
		$filter = 'home'; break;
	case 'pin':
		$filter = 'pin'; break;
	case preg_match('#^\d{3}$#', $_SESSION['sel_cat'])==1:
		$filter = $_SESSION['sel_cat']; break;
	default:
		$filter = '\d{3}';
}

$catIdSel = '';
$mod='';
switch ($_SESSION['sel_get']) {
	case 'published':
		$catIdSel = '(?:pin,|home,|\d{3},)*' . $filter . '(?:,\d{3})*';
		$mod='';
		break;
	case 'draft':
		$catIdSel = 'draft,(pin,|home,|\d{3},)*' . $filter . '(?:,\d{3})*';
		$mod='_?';
		break;
	case 'all':
		$catIdSel = '(?:draft,|pin,|home,|\d{3},)*' . $filter . '(?:,\d{3})*';
		$mod='_?';
		break;
	case 'mod':
		$catIdSel = '(?:draft,|pin,|home,|\d{3},)*' . $filter . '(?:,\d{3})*';
		$mod='_';
		break;
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
$artId = '\d{4}';
$url = '.*';
if(is_numeric($_GET['artTitle'])) {
	$artId = str_pad($_GET['artTitle'],4,'0',STR_PAD_LEFT);
} elseif(!empty($_GET['artTitle'])) {
	$url = '.*' .plxUtils::urlify($_GET['artTitle']) . '.*';
}
$motif = '#^' . $mod . implode('\.', [$artId, $catIdSel, $userId, '\d{12}', $url,]) . '\.xml$#';
# Calcul du nombre de page si on fait une recherche
if($_GET['artTitle']!='') {
	if($arts = $plxAdmin->plxGlob_arts->query($motif))
		$nbArtPagination = sizeof($arts);
}

# Traitement
$plxAdmin->prechauffage($motif);
$plxAdmin->getPage();

if(($plxAdmin->page - 1) * $plxAdmin->bypage > $nbArtPagination) {
	$plxAdmin->page = 1;
}

$arts = $plxAdmin->getArticles('all'); # Recuperation des articles

# Génération de notre tableau des catégories
$aFilterCat = [
	'all' => L_ARTICLES_ALL_CATEGORIES,
	'home' => L_CATEGORY_HOME,
	'000' => L_UNCLASSIFIED,
	'pin' => L_PINNED_ARTICLE,
];
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
include 'top.php';
?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminIndexTop')) # Hook Plugins ?>

<form action="index.php?sel=<?= $_SESSION['sel_get'] ?>" method="post" id="form_articles">

<div class="inline-form action-bar">
	<h2><?= L_ARTICLES_LIST ?></h2>
	<ul class="menu">
<?php
foreach([
	'all' => [L_ALL, '_?'],
	'published' => [L_ALL_PUBLISHED, ''],
	'draft' => [L_ALL_DRAFTS, '_?'],
	'mod' => [L_ALL_AWAITING_MODERATION, '_'],
] as $sel=>$infos) {
	$nb = $plxAdmin->nbArticles($sel, $userId, $infos[1]);
	if($nb > 0) {
?>
		<li><a <?= ($_SESSION['sel_get']== $sel) ? 'class="selected" ' : '' ?>href="index.php?sel=<?= $sel ?>&page=1"><?= $infos[0] ?></a> <em>(<?= $nb ?>)</em></li>
<?php
	}
}
?>
	</ul>
<?php
	echo plxToken::getTokenPostMethod();
	if($_SESSION['profil']<=PROFIL_MODERATOR) {
		plxUtils::printSelect('selection', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, false, 'id_selection');
		echo '<input name="sel" type="submit" value="'.L_OK.'" onclick="return confirmAction(this.form, \'id_selection\', \'delete\', \'idArt[]\', \''.L_CONFIRM_DELETE.'\')" /><span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>';
	}
?>
	<?php plxUtils::printInput('page',1,'hidden'); ?>
</div>

<div class="grid">
	<div class="col sml-6">
		<?php plxUtils::printSelect('sel_cat', $aFilterCat, $_SESSION['sel_cat']) ?>
<?php
if($_SESSION['profil'] < PROFIL_WRITER) {
	$users = array_filter($plxAdmin->aUsers, function($item) {
		return (
			!empty($item['active']) and
			empty($item['delete']) and
			$item['profil'] <= PROFIL_WRITER
		);
	});
	if(count($plxAdmin->aUsers) > 1) {
		$values = array_map(
			function($item) {
				return $item['name'];
			},
			$users
		);
		uasort($values, function($a, $b) {
			$la = preg_replace('#.*\s(\w[\w-]*)$#', '$1', $a);
			$lb = preg_replace('#.*\s(\w[\w-]*)$#', '$1', $b);
			return strcasecmp($la, $lb);
		});
		plxUtils::printSelect('sel_user', array_merge(array('' => L_ARTICLES_ALL_AUTHORS), $values), $_SESSION['sel_user']);
	}
}
?>
		<input class="<?= $_SESSION['sel_cat']!='all'?' select':'' ?>" type="submit" value="<?= L_ARTICLES_FILTER_BUTTON ?>" />
	</div>
	<div class="col sml-6 text-right">
		<input id="index-search" placeholder="<?= L_SEARCH_PLACEHOLDER ?>" type="text" name="artTitle" value="<?= plxUtils::strCheck($_GET['artTitle']) ?>" />
		<input class="<?= (!empty($_GET['artTitle'])?' select':'') ?>" type="submit" value="<?= L_SEARCH ?>" />
	</div>
</div>

<div class="scrollable-table">
	<table id="articles-table" class="full-width">
		<thead>
			<tr>
				<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idArt[]')" /></th>
				<th><?= L_ID ?></th>
				<th class="datetime"><?= L_ARTICLE_LIST_DATE ?></th>
				<th><?= L_ARTICLE_LIST_TITLE ?></th>
				<th><?= L_ARTICLE_LIST_CATEGORIES ?></th>
				<th><?= L_ARTICLE_LIST_NBCOMS ?></th>
<?php
if(!preg_match('#^\d{3}$#', $userId)) {
?>
				<th><?= L_ARTICLE_LIST_AUTHOR ?></th>
<?php
}
?>
				<th class="action"><?= L_ARTICLE_LIST_ACTION ?></th>
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
				$publi = (boolean)!($plxAdmin->plxRecord_arts->f('date') > $datetime);
				# Catégories : liste des libellés de toutes les categories
				$draft='';
				$libCats='';
				$aCats = array();
				$catIds = explode(',', $plxAdmin->plxRecord_arts->f('categorie'));
				if(sizeof($catIds)>0) {
					foreach($catIds as $catId) {
						$selected = ($catId==$_SESSION['sel_cat'] ? ' selected="selected"' : '');
						if($catId=='draft') $draft = ' - <strong>'.L_CATEGORY_DRAFT.'</strong>';
						elseif($catId=='home') $aCats['home'] = '<option value="home"'.$selected.'>'.L_CATEGORY_HOME.'</option>';
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
			<tr <?= in_array('pin', $catIds) ? 'class="pin"' : '' ?>>
				<td><input type="checkbox" name="idArt[]" value="<?= $idArt ?>" /></td>
				<td><?= $idArt ?></td>
				<td><?= plxDate::formatDate($plxAdmin->plxRecord_arts->f('date')) ?></td>
				<td class="wrap"><a href="article.php?a=<?= $idArt ?>" title="<?= L_ARTICLE_EDIT_TITLE ?>"><?= plxUtils::strCheck($plxAdmin->plxRecord_arts->f('title')) ?></a><?= $draft . $awaiting ?></td>
				<td>
<?php
				if(sizeof($aCats)>1) {
?>
					<select name="sel_cat2" class="ddcat" onchange="this.form.sel_cat.value=this.value;this.form.submit()">
						<?= implode('', $aCats) ?>
					</select>
<?php
				} else  {
?>
					<?= strip_tags(implode('', $aCats)) ?>
<?php
				}
?>
				</td>
				<td><a title="<?= L_NEW_COMMENTS_TITLE ?>" href="comments.php?sel=offline&amp;a=<?= $plxAdmin->plxRecord_arts->f('numero') ?>&amp;page=1"><?= $nbComsToValidate ?></a> / <a title="'.L_VALIDATED_COMMENTS_TITLE ?></a>'" href="comments.php?sel=online&amp;a=<?= $plxAdmin->plxRecord_arts->f('numero') ?>&amp;page=1"><?= $nbComsValidated ?></a></td>
<?php
				if(!preg_match('#^\d{3}$#', $userId)) {
					$author = plxUtils::getValue($plxAdmin->aUsers[$plxAdmin->plxRecord_arts->f('author')]['name']);
?>
				<td><?= plxUtils::strCheck($author) ?></td>
<?php
				}
?>
				<td>
					<a href="article.php?a=<?= $idArt ?>" title="<?= L_ARTICLE_EDIT_TITLE ?>"><?= L_ARTICLE_EDIT ?></a>
<?php
				if($publi AND $draft=='') {
					# l'article est publié
?>
					<a href="<?= $plxAdmin->urlRewrite('?article'.intval($idArt).'/'.$plxAdmin->plxRecord_arts->f('url')) ?>" title="<?= L_ARTICLE_VIEW_TITLE ?>"><?= L_VIEW ?></a>
<?php
				}
?>
				</td>
			</tr>
<?php
			}
		} else { # Pas d'article
			$colspan = preg_match('#^\d{3}$#', $userId) ? 7 : 8;
			echo '<tr><td colspan="' . $colspan . '" class="center">'.L_NO_ARTICLE.'</td></tr>';
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
	if($arts) {
		plxUtils::printPagination($nbArtPagination, $plxAdmin->bypage, $plxAdmin->page, 'index.php?page=%d' . $artTitle);
	}
?>
</div>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexFoot'));

# On inclut le footer
include 'foot.php';
