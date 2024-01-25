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

# Régénère le fichier tags.xml
if($_SESSION['profil'] <= PROFIL_MODERATOR and isset($_POST['tags'])) {
	/*
	 * Les articles sans tag ou à modérer sont ignorés.
	 * Avant la sauvegarde, les articles seront triés selon leurs identifiants.
	 * */
	$plxAdmin->aTags = array();
	foreach($plxAdmin->plxGlob_arts->aFiles as $artId=>$filename) {
		if($filename[0] != '_') {
			$art = $plxAdmin->parseArticle(PLX_ROOT . $plxAdmin->aConf['racine_articles'] . $filename);
			if(!empty($art['tags'])) {
				$plxAdmin->aTags[$artId] = array(
					'tags'      => $art['tags'],
					'date'      => $art['date'],
					'active'    => preg_match('#\bdraft\b#', $art['categorie']) ? 0 : 1,
				);
			}
			unset($art);
		}
	}

	if(!empty($plxAdmin->aTags)) {
	    ksort($plxAdmin->aTags);
	    if($plxAdmin->editTags()) {
			plxMsg::Info(sprintf(L_TAGS_SAVE_SUCCESS, count($plxAdmin->aTags)));
		} else {
			plxMsg::Error(L_TAGS_SAVE_ERROR);
		}
	}
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

# Récupération du texte à rechercher
$artTitle = '';
if(filter_has_var(INPUT_GET, 'artTitle')) {
	$artTitle = trim(htmlspecialchars($_GET['artTitle'])); # requested by PHP-8.1.0
}

if(empty($artTitle) and filter_has_var(INPUT_POST, 'artTitle')) {
	header('Location: index.php?artTitle=' . plxUtils::unSlash(trim(urldecode($_POST['artTitle']))));
	exit;
}
# On génère notre motif de recherche
$artId = '\d{4}';
$url = '.*';
if(!empty($artTitle)) {
	if(is_numeric($artTitle)) {
		$artId = str_pad($artTitle, 4, '0', STR_PAD_LEFT);
	} else {
		$url = '.*' . plxUtils::urlify($artTitle) . '.*';
	}
}
$motif = '#^' . $mod . implode('\.', [$artId, $catIdSel, $userId, '\d{12}', $url, 'xml', ]) . '$#';

# Nombre d'article sélectionnés ( pour pagination )
$hasPagination = false;
$globArts = $plxAdmin->plxGlob_arts->query($motif);
if(!empty($globArts)) {
	$nbArtPagination = sizeof($globArts);
	$hasPagination = ($nbArtPagination > $plxAdmin->bypage);
}

# Traitement
$plxAdmin->prechauffage($motif);
$plxAdmin->getPage();

if(!$hasPagination or ($plxAdmin->page - 1) * $plxAdmin->bypage > $nbArtPagination) {
	$plxAdmin->page = 1;
}

# Recuperation des articles
$arts = $plxAdmin->getArticles('all'); # return true or false

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

if($_SESSION['profil'] == PROFIL_ADMIN and is_file(PLX_ROOT.'install.php') and $plxAdmin->page == 1) {
	$urlDeleteInstall = 'index.php?' . http_build_query(array_merge($_GET, array('del'=>'install')));
?>
		<p class="alert red text-center"><?php printf(L_WARNING_INSTALLATION_FILE, $urlDeleteInstall) ?></p>
<?php
}

eval($plxAdmin->plxPlugins->callHook('AdminIndexTop')) # Hook Plugins
?>

<form action="index.php?sel=<?= $_SESSION['sel_get'] ?>" method="post" id="form_articles">
<div class="inline-form action-bar">
	<?= plxToken::getTokenPostMethod(); ?>
	<?php plxUtils::printInput('page',1,'hidden'); ?>
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
	if($_SESSION['profil']<=PROFIL_MODERATOR) {
?>
	<div  class="grid">
		<div class="col sml-9 med-6">
			<?php plxUtils::printSelect('selection', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, false, 'id_selection'); ?>
			<input name="sel" type="submit" value="<?= L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idArt[]', '<?= L_CONFIRM_DELETE ?>')" /><span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		</div>
		<div class="col sml-3 med-2 med-offset-4 med-text-right">
			<input name="tags" type="submit" title="Refresh tags list" value="Tags" />
		</div>
	</div>
<?php
	}
?>
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
		<!-- input class="<?= $_SESSION['sel_cat']!='all'?' select':'' ?>" type="submit" value="<?= L_ARTICLES_FILTER_BUTTON ?>" / -->
	</div>
	<div class="col sml-6 text-right">
		<input id="index-search" placeholder="<?= L_SEARCH_PLACEHOLDER ?>" type="text" name="artTitle" value="<?= plxUtils::strCheck($artTitle) ?>" />
		<input class="<?= (!empty($artTitle)?' select':'') ?>" type="submit" value="<?= L_SEARCH ?>" />
	</div>
</div>

<div class="scrollable-table<?= $hasPagination ? ' has-pagination' : '' ?>">
	<table id="articles-table" class="full-width">
		<thead>
			<tr>
				<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idArt[]')" /></th>
				<th class="art-id"><?= L_ID ?></th>
				<th class="datetime"><?= L_ARTICLE_LIST_DATE ?></th>
				<th><?= L_ARTICLE_LIST_TITLE ?></th>
				<th class="cat"><?= L_ARTICLE_LIST_CATEGORIES ?></th>
				<th class="comms"><?= L_ARTICLE_LIST_NBCOMS ?></th>
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
			$datetime = date('YmdHi');
			while($plxAdmin->plxRecord_arts->loop()) { # Pour chaque article
				$publi = (boolean)!($plxAdmin->plxRecord_arts->f('date') > $datetime);
				# Catégories : liste des libellés de toutes les categories
				$draft='';
				$aCats = array();
				$catIds = explode(',', $plxAdmin->plxRecord_arts->f('categorie'));
				if(sizeof($catIds) > 0) {
					foreach($catIds as $catId) {
						if($catId == 'draft') {
							$draft = ' - <strong>'.L_CATEGORY_DRAFT.'</strong>';
						} elseif(array_key_exists($catId, $aFilterCat)) {
							$selected = ($catId==$_SESSION['sel_cat'] ? ' selected="selected"' : '');
							$aCats[$catId] = <<< EOT
<option value="$catId" $selected> ${aFilterCat[$catId]} </option>
EOT;
						}
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
				if(sizeof($aCats) > 1) {
?>
					<select name="sel_cat2" class="ddcat" onchange="this.form.sel_cat.value = this.value; this.form.submit()">
						<?= implode('', $aCats) ?>
					</select>
<?php
				} elseif(count($aCats) == 1)  {
					$catId = array_keys($aCats)[0];
?>
					<span class="as-link" data-cat="<?= $catId ?>"><?= $aFilterCat[$catId] ?></span>
<?php
				} else {
					echo '&nbsp;';
				}
?>
				</td>
				<td>
<?php
				$artId = $plxAdmin->plxRecord_arts->f('numero');
				if($nbComsToValidate > 0) {
?>
					<a title="<?= L_NEW_COMMENTS_TITLE ?>" href="comments.php?sel=offline&amp;a=<?= $artId ?>&amp;page=1"><?= $nbComsToValidate ?></a>
<?php
				} else {
?>
					<span title="<?= L_NEW_COMMENTS_TITLE ?>">0</span>
<?php
				}
				echo ' / ';
				if($nbComsValidated > 0) {
?>
					<a title="<?= L_VALIDATED_COMMENTS_TITLE ?>" href="comments.php?sel=online&amp;a=<?= $artId ?>"><?= $nbComsValidated ?></a></td>
<?php
				} else {
?>
					<span title="<?= L_VALIDATED_COMMENTS_TITLE ?>">0</span>
<?php
		}
?>
<?php
					if(!preg_match('#^\d{3}$#', $userId)) {
?>
				<td>
<?php
				$userArtId = $plxAdmin->plxRecord_arts->f('author');
				if(array_key_exists($userArtId, $plxAdmin->aUsers)) {
					$author = plxUtils::getValue($plxAdmin->aUsers[$userArtId]['name']);
?>
					<span class="as-link" data-user="<?= $userArtId ?>"><?= plxUtils::strCheck($author) ?></span>
<?php
				} else {
					echo '&nbsp;';
				}
?>
				</td>
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
?>
			<tr>
				<td colspan="<?= $colspan ?>" class="text-center"><?= ucfirst(L_NO_ARTICLE) ?></td>
			</tr>
<?php
		}
?>
		</tbody>
	</table>
</div>

</form>
<?php
if($hasPagination) {
?>
<div id="pagination" class="text-center">
<?php

	$urlTemplate = 'index.php?page=%d'; # Aucun % supplémentaire dedans

	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminIndexPagination'));

	# Affichage de la pagination
	plxUtils::printPagination($nbArtPagination, $plxAdmin->bypage, $plxAdmin->page, $urlTemplate);
?>
</div>
<?php
}
?>
<script>
	(function (id) {
		const el = document.getElementById(id);
		if(el) {
			el.addEventListener('click', function(ev) {
				if(ev.target.hasAttribute('data-cat')) {
					el.sel_cat.value = ev.target.dataset.cat;
				} else if(ev.target.hasAttribute('data-user')) {
					el.sel_user.value = ev.target.dataset.user;
				} else {
					return
				}

				ev.preventDefault();
				el.submit();
			});
		} else {
			console.error(`${id} element not found`);
		}
	})('form_articles');
</script>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexFoot'));

# On inclut le footer
include 'foot.php';
