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
if(isset($_POST['sel']) AND isset($_POST['idArt'])) {
	if(!empty($_POST['selection'])) {
		switch($_POST['selection']) {
			case 'delete' :
				# Suppression des articles selectionnes
				if($_SESSION['profil'] <= PROFIL_MODERATOR) {
					foreach ($_POST['idArt'] as $v) {
						if($plxAdmin->delArticle($v) !== true) {
							break;
						}
					}
				} else {
					plxMsg::Error(L_ERR_FORBIDDEN_ARTICLE);
				}
				break;
			case 'draft' :
			case 'undraft' :
				# Basculement du mode brouillon des articles selectionnes
				$status = ($_POST['selection'] == 'draft');
				foreach ($_POST['idArt'] as $v) {
					$plxAdmin->draftToggle($v, $status);
				}
				break;
			default :
		}
	}

	header('Location: index.php');
	exit;
}

# Récuperation de l'id de l'utilisateur
$userId = ($_SESSION['profil'] < PROFIL_EDITOR) ? '\d{3}' : $_SESSION['user'];

# Récuperation des paramètres
if(!empty($_GET['sel']) AND in_array($_GET['sel'], array('all','published', 'draft','mod'))) {
	$_SESSION['sel_get'] = plxUtils::nullbyteRemove($_GET['sel']);
	$_SESSION['sel_cat'] = '';
	unset($_SESSION['page']['index']);
} elseif(empty($_SESSION['sel_get'])) {
	# garde-fou
	$_SESSION['sel_get'] = 'all';
	$_SESSION['sel_cat'] = '';
	unset($_SESSION['page']['index']);
}

if(
	!empty($_POST['sel_cat']) and (
		$_POST['sel_cat'] == 'all' or
		$_POST['sel_cat'] == 'home' or
		$_POST['sel_cat'] == '000' or
		array_key_exists($_POST['sel_cat'], $plxAdmin->aCats)
	) and
	$_SESSION['sel_cat'] != $_POST['sel_cat']
){
	$_SESSION['sel_cat'] = $_POST['sel_cat'];
	$plxAdmin->page = 1;
} else {
	$plxAdmin->getPage();
}

if(empty($_SESSION['sel_cat'])) {
	$_SESSION['sel_cat'] = 'all';
}

# Recherche du motif de sélection des articles en fonction des paramètres
$mod = '_?';
switch ($_SESSION['sel_get']) {
	case 'published':
		$catIdSel = '(?!draft,).*FILTER.*';
		$mod = '';
		break;
	case 'draft':
		$catIdSel = 'draft,.*FILTER.*';
		break;
	case 'mod':
		$mod = '_';
		# no break !
	default: // 'all'
		$catIdSel = '.*FILTER.*';
}
$catIdSel = str_replace('FILTER', ($_SESSION['sel_cat'] == 'all') ? '': $_SESSION['sel_cat'], $catIdSel);

# Récupération du texte à rechercher
if(!empty($_GET['artTitle'])) {
	$artTitle = plxUtils::unSlash(urldecode(trim($_GET['artTitle'])));
} elseif(!empty($_POST['artTitle'])) {
	$artTitle = plxUtils::unSlash(urldecode(trim($_POST['artTitle'])));
} else {
	$artTitle = '';
}

# On génère notre motif de recherche
if(is_numeric($artTitle)) {
	$artId = str_pad($artTitle, 4, '0', STR_PAD_LEFT);
	$motif = '/^' . $mod . $artId . '\.' . $catIdSel . '\.' . $userId . '\.\d{12}\..*\.xml$/';
} elseif(!empty($artTitle)) {
	$motif = '/^' . $mod . '\d{4}\.' . $catIdSel . '\.' . $userId . '\.\d{12}\..*' . plxUtils::urlify($artTitle) . '.*\.xml$/';
} else {
	$motif = '/^' . $mod . '\d{4}\.' . $catIdSel . '\.' . $userId . '\.\d{12}\..*\.xml$/';
}

# Calcul du nombre de pages si on fait une recherche
if($arts = $plxAdmin->plxGlob_arts->query($motif)) {
	$nbArtPagination = sizeof($arts);
} else {
	$nbArtPagination = 0;
}

# Traitement
$plxAdmin->prechauffage($motif);
$arts = $plxAdmin->getArticles('all'); # Recuperation des articles

# Génération d'un tableau pour sélectionner une catégorie ( <select> élément )
$aFilterCat = array(
);
foreach(array(
	'all' => L_ARTICLES_ALL_CATEGORIES,
	'000' => L_UNCLASSIFIED,
	'home' => L_CATEGORY_HOME,
) as $k=>$v) {
	$artsCount = $plxAdmin->nbArticles($k, $userId, $mod);
	if($artsCount > 0) {
		$v .= ' ('. $artsCount . ' ' . L_ARTICLES .')';
	}
	$aFilterCat[$k] = $v;
}
if($plxAdmin->aCats) {
	foreach($plxAdmin->aCats as $k=>$v) {
		$caption = plxUtils::strCheck($v['name']);
		$aCat[$k] = $caption;
		$artsCount = $plxAdmin->nbArticles($k, $userId, $mod);
		if($artsCount > 0) {
			$caption .= ' ('. $artsCount . ' ' . L_ARTICLES .')';
			if(!empty($v['active'])) {
				$caption .= ' 👁';
			}
		}
		$aFilterCat[$k] = $caption;
	}
	$aAllCat[L_CATEGORIES_TABLE] = $aCat;
}

$aAllCat[L_SPECIFIC_CATEGORIES_TABLE]['home'] = L_CATEGORY_HOME_PAGE;
$aAllCat[L_SPECIFIC_CATEGORIES_TABLE]['draft'] = L_DRAFT;
$aAllCat[L_SPECIFIC_CATEGORIES_TABLE][''] = L_ALL_ARTICLES_CATEGORIES_TABLE;

if(is_dir(PLX_ROOT . PLX_INSTALL_PATH) and $_SESSION['profil'] == PROFIL_ADMIN) {
	if(isset($_GET['del']) AND $_GET['del'] == 'install') {
		# On supprime le dossier d'installation
		$error = false;
		foreach(glob(PLX_ROOT . PLX_INSTALL_PATH . '/*') as $filename) {
			if(!is_writable($filename) or !unlink($filename)) {
				$error = true;
				break;
			}
		}
		if(!$error and @rmdir(PLX_ROOT . PLX_INSTALL_PATH)) {
			plxMsg::Info(L_DELETE_SUCCESSFUL);
		} else {
			plxMsg::Error(L_PLXMEDIAS_DEL_FOLDER_ERR . ' ' . PLX_INSTALL_PATH);
		}
	}
}

# On inclut le header
include 'top.php';

if(is_dir(PLX_ROOT . PLX_INSTALL_PATH) and $_SESSION['profil'] == PROFIL_ADMIN) {
		$urlDeleteInstall = "?" . http_build_query(array_merge($_GET, array('del'=>'install')));
?>
<p class="alert red"><?= sprintf(L_WARNING_INSTALLATION_FOLDER, $urlDeleteInstall) ?></p>
<?php
}

eval($plxAdmin->plxPlugins->callHook('AdminIndexTop')) # Hook Plugins

?>
<form method="post" id="form_articles">

<div class="inline-form action-bar">
	<h2><?= L_ARTICLES_LIST ?></h2>
	<ul class="menu">
<?php
	$breadcrumb = array(
		'all'		=> L_ALL,
		'published'	=> L_ALL_PUBLISHED,
		'draft'		=> L_ALL_DRAFTS,
		'mod'		=> L_ALL_AWAITING_MODERATION,
	);
	foreach($breadcrumb as $k=>$caption) {
?>
		<li><a href="index.php?sel=<?= $k?>" <?php if($_SESSION['sel_get'] == $k) echo 'class="selected"'; ?>><?= $caption ?></a>&nbsp;(<?= $plxAdmin->nbArticles($k, $userId, ($k == 'mod') ? '_' : '_?') ?>)</li>
<?php
	}
?>
	</ul>
	<?= plxToken::getTokenPostMethod(); ?>
<?php
	if($_SESSION['profil'] <= PROFIL_MODERATOR) {
		$options = array(
			 ''			=> L_FOR_SELECTION,
			 'delete'	=> L_DELETE,
			 'draft'	=> L_ARTICLE_DRAFT_BUTTON,
			 'undraft'	=> L_ARTICLE_PUBLISHING_BUTTON,
		);
?>
	<select id="id_selection" name="selection">
<?php
		foreach($options as $value=>$caption) {
?>
		<option value="<?= $value ?>"><?= $caption ?></option>
<?php
		}
?>
	</select>
	<input name="sel" type="submit" value="<?= L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idArt[]', '<?= L_CONFIRM_DELETE ?>')" /><span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
<?php
	}
?>
	<?php plxUtils::printInput('page',1,'hidden'); ?>
</div>

<div class="grid">
	<div class="col sml-6">
		<select name="sel_cat" onchange="this.form.submit(); ">
<?php
foreach($aFilterCat as $value=>$caption) {
?>
			<option value="<?=$value ?>" <?php if($value == $_SESSION['sel_cat']) { echo 'selected'; } ?>><?= $caption ?></option>
<?php
}
?>
		</select>
		<input class="<?= $_SESSION['sel_cat']!='all'?' select':'' ?>" type="submit" value="<?= L_ARTICLES_FILTER_BUTTON ?>" />
	</div>
	<div class="col sml-6 text-right">
		<input id="index-search" placeholder="<?= L_SEARCH_PLACEHOLDER ?>" type="text" name="artTitle" value="<?= plxUtils::strCheck($artTitle) ?>" />
		<input class="<?= !empty($artTitle) ? ' select' : '' ?>" type="submit" value="<?= L_SEARCH ?>" />
	</div>
</div>

<div class="scrollable-table">
	<table id="articles-table" class="full-width">
		<thead>
			<tr>
				<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idArt[]')" /></th>
				<th><?= L_ID ?></th>
				<th><?= L_ARTICLE_LIST_DATE ?></th>
				<th><?= L_ARTICLE_LIST_TITLE ?></th>
				<th><?= L_ARTICLE_LIST_CATEGORIES ?></th>
				<th><?= L_ARTICLE_LIST_NBCOMS ?></th>
				<th><?= L_ARTICLE_LIST_AUTHOR ?></th>
				<th class="action"><?= L_ARTICLE_LIST_ACTION ?></th>
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
				$publi = (bool)!($plxAdmin->plxRecord_arts->f('date') > $datetime);
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
			<tr>
				<td><input type="checkbox" name="idArt[]" value="<?= $idArt ?>" /></td>
				<td><?= $idArt ?></td>
				<td><?= plxDate::formatDate($plxAdmin->plxRecord_arts->f('date')) ?></td>
				<td class="wrap"><a href="article.php?a=<?= $idArt ?>" title="<?= L_ARTICLE_EDIT_TITLE ?>"><?= plxUtils::strCheck($plxAdmin->plxRecord_arts->f('title')) ?></a><?= $draft.$awaiting ?>&nbsp;</td>
				<td>
<?php
				if(sizeof($aCats) > 1) {
?>
					<select name="sel_cat2" class="ddcat" onchange="this.form.sel_cat.value=this.value;this.form.submit()">
<?= implode(PHP_EOL, $aCats) . PHP_EOL ?>
					</select>
<?php
				} else  {
					echo strip_tags(implode('', $aCats)) . PHP_EOL;
				}
?>
				</td>
				<td><a title="<?= L_NEW_COMMENTS_TITLE ?>" href="comments.php?sel=offline&a=<?= $plxAdmin->plxRecord_arts->f('numero') ?>&page=1"><?= $nbComsToValidate ?></a> / <a title="<?= L_VALIDATED_COMMENTS_TITLE ?>" href="comments.php?sel=online&a=<?= $plxAdmin->plxRecord_arts->f('numero') ?>&page=1"><?= $nbComsValidated ?></a>&nbsp;</td>
				<td><?= plxUtils::strCheck($author)?></td>
				<td>
					<a href="article.php?a=<?= $idArt ?>" title="<?= L_ARTICLE_EDIT_TITLE ?>"><?= L_ARTICLE_EDIT ?></a>
<?php
				if($publi AND $draft=='') {
					# Si l'article est publié
?>
					<a href="<?= $plxAdmin->urlRewrite('?article'.intval($idArt).'/'.$plxAdmin->plxRecord_arts->f('url')) ?>" title="<?= L_ARTICLE_VIEW_TITLE ?>"><?= L_VIEW ?></a>
<?php
				}
?>
				</td>
			</tr>
<?php
			}
		} else {
			# Pas d'article
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

<p id="pagination">
	<?php
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminIndexPagination'));
	# Affichage de la pagination
	if($arts) { # Si on a des articles (hors page)
		# Calcul des pages
		$last_page = ceil($nbArtPagination/$plxAdmin->bypage);
		$plxAdmin->getPage();
		if($plxAdmin->page > $last_page) {
			$plxAdmin->page = 1;
		}
		$stop = $plxAdmin->page + 2;
		if($stop<5) $stop=5;
		if($stop>$last_page) $stop=$last_page;
		$start = $stop - 4;
		if($start<1) $start=1;
		# Génération des URLs
		$url = !empty($artTitle) ? '&artTitle='.urlencode($artTitle) : '';
		$p_url = 'index.php?page='.($plxAdmin->page-1).$url;
		$n_url = 'index.php?page='.($plxAdmin->page+1).$url;
		$l_url = 'index.php?page='.$last_page.$url;
		$f_url = 'index.php?page=1'.$url;
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
include 'foot.php';
