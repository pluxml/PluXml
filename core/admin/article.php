<?php

/**
 * Edition d'un article
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include 'prepend.php';

# Control du token du formulaire
if(!isset($_POST['preview']))
	plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminArticlePrepend'));

# validation de l'id de l'article si passé en parametre
if(isset($_GET['a']) AND !preg_match('/^_?\d{4}$/', $_GET['a'])) {
	plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
	header('Location: index.php');
	exit;
}

# Formulaire validé
if(!empty($_POST)) { # Création, mise à jour, suppression ou aperçu

	if(!isset($_POST['catId'])) $_POST['catId']=array();
	# Titre par défaut si titre vide
	if(trim($_POST['title'])=='') $_POST['title'] = L_DEFAULT_NEW_ARTICLE_TITLE;
	# Si demande d'enregistrement en brouillon on ajoute la categorie draft à la liste et on retire la demande de validation
	if(isset($_POST['draft']) AND !in_array('draft', $_POST['catId'])) array_unshift($_POST['catId'], 'draft');
	# si aucune catégorie sélectionnée on place l'article dans la catégorie "non classé"
	if(sizeof($_POST['catId'])==1 AND $_POST['catId'][0]=='draft') $_POST['catId'][]='000';
	else $_POST['catId'] = array_filter($_POST['catId'], function($a){return $a!="000";});
	# Si demande de publication ou demande de validation, on supprime la catégorie draft si elle existe
	if((isset($_POST['update']) OR isset($_POST['publish']) OR isset($_POST['moderate'])) AND isset($_POST['catId'])) $_POST['catId'] = array_filter($_POST['catId'], function($a){return $a!="draft";});
	# Si profil PROFIL_WRITER on vérifie l'id du rédacteur connecté et celui de l'article
	if($_SESSION['profil']==PROFIL_WRITER AND isset($_POST['author']) AND $_SESSION['user']!=$_POST['author']) $_POST['author']=$_SESSION['user'];
	# Si profil PROFIL_WRITER on vérifie que l'article n'est pas celui d'un autre utilisateur
	if($_SESSION['profil']==PROFIL_WRITER AND isset($_POST['artId']) AND $_POST['artId']!='0000') {
		# On valide l'article
		if(($aFile = $plxAdmin->plxGlob_arts->query('/^'.$_POST['artId'].'.([home[draft|0-9,]*).'.$_SESSION['user'].'.(.+).xml$/')) == false) { # Article inexistant
			plxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
			header('Location: index.php');
			exit;
		}
	}
	# Previsualisation d'un article
	if(!empty($_POST['preview'])) {
		$art=array();
		$art['title'] = trim($_POST['title']);
		$art['allow_com'] = $_POST['allow_com'];
		$art['template'] = basename($_POST['template']);
		$art['chapo'] = trim($_POST['chapo']);
		$art['content'] = trim($_POST['content']);
		$art['tags'] = trim($_POST['tags']);
		$art['meta_description'] = $_POST['meta_description'];
		$art['meta_keywords'] = $_POST['meta_keywords'];
		$art['title_htmltag'] = $_POST['title_htmltag'];
		$art['filename'] = '';
		$art['numero'] = $_POST['artId'];
		$art['author'] = $_POST['author'];
		$art['thumbnail'] = plxUtils::strCheck(trim($_POST['thumbnail']));
		$art['thumbnail_title'] = plxUtils::strCheck(trim($_POST['thumbnail_title']));
		$art['thumbnail_alt'] = plxUtils::strCheck(trim($_POST['thumbnail_alt']));
		$art['categorie'] = '000';
		if(!empty($_POST['catId'])) {
			$array=array();
			foreach($_POST['catId'] as $k => $v) {
				if($v!='draft') $array[]=$v;
			}
			$art['categorie']=implode(',', $array);
		}
		$art['date'] = $_POST['date_publication_year'].$_POST['date_publication_month'].$_POST['date_publication_day'].substr(str_replace(':', '', $_POST['date_publication_time']), 0, 4);
		$art['date_creation'] = $_POST['date_creation_year'].$_POST['date_creation_month'].$_POST['date_creation_day'].substr(str_replace(':', '', $_POST['date_creation_time']), 0, 4);
		$art['date_update'] = $_POST['date_update_year'].$_POST['date_update_month'].$_POST['date_update_day'].substr(str_replace(':', '', $_POST['date_update_time']), 0, 4);
		$art['nb_com'] = 0;
		$tmpstr = (!empty(trim($_POST['url']))) ? $_POST['url'] : $_POST['title'];
		$art['url'] = plxUtils::urlify($tmpstr);
		if(empty($art['url'])) $art['url'] = L_DEFAULT_NEW_ARTICLE_URL;

		# Hook Plugins
		eval($plxAdmin->plxPlugins->callHook('AdminArticlePreview'));

		$article[0] = $art;
		$_SESSION['preview'] = $article;
		header('Location: '.PLX_ROOT.'index.php?preview');
		exit;
	}
	# Suppression d'un article
	if(isset($_POST['delete'])) {
		$plxAdmin->delArticle($_POST['artId']);
		header('Location: index.php');
		exit;
	}
	# Mode création ou maj
	if(isset($_POST['update']) OR isset($_POST['publish']) OR isset($_POST['moderate']) OR isset($_POST['draft'])) {

		$valid = true;
		# Vérification de l'unicité de l'url
		$url = plxUtils::urlify(!empty($_POST['url']) ? $_POST['url'] : $_POST['title']);
		foreach($plxAdmin->plxGlob_arts->aFiles as $numart => $filename) {
			if(preg_match("/^_?\d{4}.([0-9,|home|draft]*).\d{3}.\d{12}.$url.xml$/", $filename)) {
				if($numart!=str_replace('_', '', $_POST['artId'])) {
					$valid = plxMsg::Error(L_ERR_URL_ALREADY_EXISTS." : ".plxUtils::strCheck($url)) AND $valid;
				}
			}
		}
		# Vérification de la validité de la date de publication
		if(!plxDate::checkDate($_POST['date_publication_day'], $_POST['date_publication_month'], $_POST['date_publication_year'], $_POST['date_publication_time'])) {
			$valid = plxMsg::Error(L_ERR_INVALID_PUBLISHING_DATE) AND $valid;
		}
		# Vérification de la validité de la date de creation
		if(!plxDate::checkDate($_POST['date_creation_day'], $_POST['date_creation_month'], $_POST['date_creation_year'], $_POST['date_creation_time'])) {
			$valid = plxMsg::Error(L_ERR_INVALID_DATE_CREATION) AND $valid;
		}
		# Vérification de la validité de la date de mise à jour
		if(!plxDate::checkDate($_POST['date_update_day'], $_POST['date_update_month'], $_POST['date_update_year'], $_POST['date_update_time'])) {
			$valid = plxMsg::Error(L_ERR_INVALID_DATE_UPDATE) AND $valid;
		}
		if($valid) {
			$plxAdmin->editArticle($_POST, $_POST['artId']);
			header('Location: article.php?a='.$_POST['artId']);
			exit;
		# Si url ou date invalide, on ne sauvegarde pas mais on repasse en mode brouillon
		}else{
			array_unshift($_POST['catId'], 'draft');
		}

	}
	# Ajout d'une catégorie
	if(isset($_POST['new_category'])) {
		# Ajout de la nouvelle catégorie
		$plxAdmin->editCategories($_POST);
		# On recharge la nouvelle liste
		$plxAdmin->getCategories(path('XMLFILE_CATEGORIES'));
		$_GET['a']=$_POST['artId'];
	}
	# Alimentation des variables
	$artId = $_POST['artId'];
	$title = trim($_POST['title']);
	$author = $_POST['author'];
	$catId = isset($_POST['catId']) ? $_POST['catId'] : array();
	$date['day'] = $_POST['date_publication_day'];
	$date['month'] = $_POST['date_publication_month'];
	$date['year'] = $_POST['date_publication_year'];
	$date['time'] = $_POST['date_publication_time'];
	$date_creation['day'] = $_POST['date_creation_day'];
	$date_creation['month'] = $_POST['date_creation_month'];
	$date_creation['year'] = $_POST['date_creation_year'];
	$date_creation['time'] = $_POST['date_creation_time'];
	$date_update['day'] = $_POST['date_update_day'];
	$date_update['month'] = $_POST['date_update_month'];
	$date_update['year'] = $_POST['date_update_year'];
	$date_update['time'] = $_POST['date_update_time'];
	$date_update_old = $_POST['date_update_old'];
	$chapo = trim($_POST['chapo']);
	$content = trim($_POST['content']);
	$tags = trim($_POST['tags']);
	$url = $_POST['url'];
	$allow_com = $_POST['allow_com'];
	$template = $_POST['template'];
	$meta_description = $_POST['meta_description'];
	$meta_keywords = $_POST['meta_keywords'];
	$title_htmltag = $_POST['title_htmltag'];
	$thumbnail = plxUtils::strCheck(trim($_POST['thumbnail']));
	$thumbnail_title = plxUtils::strCheck(trim($_POST['thumbnail_title']));
	$thumbnail_alt = plxUtils::strCheck(trim($_POST['thumbnail_alt']));
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminArticlePostData'));
} elseif(!empty($_GET['a'])) { # On n'a rien validé, c'est pour l'édition d'un article
	# On va rechercher notre article
	if(!($aFile = $plxAdmin->plxGlob_arts->query('/^' . $_GET['a'] . '.(.+).xml$/'))) { # Article inexistant
		plxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
		header('Location: index.php');
		exit;
	}
	# On parse et alimente nos variables
	$result = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$aFile['0']);
	if(is_array($result)) {
		$title = trim($result['title']);
		$chapo = trim($result['chapo']);
		$content = trim($result['content']);
		$tags = trim($result['tags']);
		$author = $result['author'];
		$url = $result['url'];
		$date = plxDate::date2Array($result['date']);
		$date_creation = plxDate::date2Array($result['date_creation']);
		$date_update = plxDate::date2Array($result['date_update']);
		$date_update_old = $result['date_update'];
		$catId = explode(',', $result['categorie']);
		$artId = $result['numero'];
		$allow_com = $result['allow_com'];
		$template = $result['template'];
		$meta_description = $result['meta_description'];
		$meta_keywords = $result['meta_keywords'];
		$title_htmltag = $result['title_htmltag'];
		$thumbnail = $result['thumbnail'];
		$thumbnail_title = $result['thumbnail_title'];
		$thumbnail_alt = $result['thumbnail_alt'];
	} else {
		header('Location: index.php');
		exit;
	}

	if($author!=$_SESSION['user'] AND $_SESSION['profil']==PROFIL_WRITER) {
		plxMsg::Error(L_ERR_FORBIDDEN_ARTICLE);
		header('Location: index.php');
		exit;
	}
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminArticleParseData'));

} else { # On a rien validé, c'est pour la création d'un article
	$title = plxUtils::strRevCheck(L_DEFAULT_NEW_ARTICLE_TITLE);
	$chapo = $url = '';
	$content = '';
	$tags = '';
	$author = $_SESSION['user'];
	$date = array('year' => date('Y'), 'month' => date('m'), 'day' => date('d'), 'time' => date('H:i'));
	$date_creation = array('year' => date('Y'), 'month' => date('m'), 'day' => date('d'), 'time' => date('H:i'));
	$date_update = array('year' => date('Y'), 'month' => date('m'), 'day' => date('d'), 'time' => date('H:i'));
	$date_update_old = '';
	$catId = array('draft');
	$artId = '0000';
	$allow_com = $plxAdmin->aConf['allow_com'];
	$template = 'article.php';
	$meta_description=$meta_keywords=$title_htmltag='';
	$thumbnail = '';
	$thumbnail_title = '';
	$thumbnail_alt = '';
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminArticleInitData'));
}

# On inclut le header
include 'top.php';

# On construit la liste des utilisateurs
foreach($plxAdmin->aUsers as $_userid => $_user) {
	if(
		$_user['profil'] <= PROFIL_WRITER AND
		$_user['active'] AND
		!$_user['delete']
	) {
		$profilName = PROFIL_NAMES[$_user['profil']];
		$_users[$profilName][$_userid] = plxUtils::strCheck($_user['name']);
	}
}

$cat_id='000';
?>
<form method="post" id="form_article">
	<?= plxToken::getTokenPostMethod() ?>
	<div class="inline-form action-bar">
		<h2><?= empty($_GET['a']) ? L_MENU_NEW_ARTICLES : L_ARTICLE_EDITING ?></h2>
		<p><a class="back" href="index.php"><?= L_BACK_TO_ARTICLES ?></a></p>
		<input type="submit" name="preview" formtarget="plx_preview_article" value="<?= L_ARTICLE_PREVIEW_BUTTON ?>"/>
<?php
if($_SESSION['profil']>PROFIL_MODERATOR AND $plxAdmin->aConf['mod_art']) {
	# modération des articles
	if(in_array('draft', $catId)) { # brouillon
		if($artId!='0000') # nouvel article
?>
		<input type="submit" name="draft" value="<?= L_ARTICLE_DRAFT_BUTTON ?>"/>
		<input type="submit" name="moderate" value="<?= L_ARTICLE_MODERATE_BUTTON ?>"/>
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span><input class="red" type="submit" name="delete" value="<?= L_DELETE ?>" onclick="return confirm('<?= L_ARTICLE_DELETE_CONFIRM ?>');" />
<?php
	} else {
		if(isset($_GET['a']) AND preg_match('/^_\d{4}$/', $_GET['a'])) { # en attente de modération
?>
		<input type="submit" name="update" value="<?= L_ARTICLE_UPDATE_BUTTON ?>"/>
		<input type="submit" name="draft" value="<?= L_ARTICLE_DRAFT_BUTTON ?>"/>
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span><input class="red" type="submit" name="delete" value="<?= L_DELETE ?>" onclick="return confirm('<?= L_ARTICLE_DELETE_CONFIRM ?>');" />
<?php
		} else {
?>
		<input type="submit" name="draft" value="<?= L_ARTICLE_DRAFT_BUTTON ?>"/>
		<input type="submit" name="moderate" value="<?= L_ARTICLE_MODERATE_BUTTON ?>"/>
<?php
		}
	}
} else {
	if(in_array('draft', $catId)) {
?>
		<input type="submit" name="draft" value="<?= L_ARTICLE_DRAFT_BUTTON ?>"/>
		<input type="submit" name="publish" value="<?= L_ARTICLE_PUBLISHING_BUTTON ?>"/>
<?php
	} else {
		if(!isset($_GET['a']) OR preg_match('/^_\d{4}$/', $_GET['a'])) {
?>
		<input type="submit" name="publish" value="<?= L_ARTICLE_PUBLISHING_BUTTON ?>"/>
<?php
		} else {
?>
		<input type="submit" name="update" value="<?= L_ARTICLE_UPDATE_BUTTON ?>"/>
		<input type="submit" name="draft" value="<?= L_ARTICLE_OFFLINE_BUTTON ?>"/>
<?php
		}
	}

	if($artId!='0000') {
?>
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span><input class="red" type="submit" name="delete" value="<?= L_DELETE ?>" onclick="return confirm('<?= L_ARTICLE_DELETE_CONFIRM ?>');" />
<?php
	}
}
?>
	</div>
<?php eval($plxAdmin->plxPlugins->callHook('AdminArticleTop')); # Hook Plugins ?>
	<div class="grid">
		<div class="col sml-12 med-7 lrg-9">
			<fieldset>
				<div class="grid">
					<div class="col sml-12">
						<?php plxUtils::printInput('artId', $artId, 'hidden'); ?>
						<label for="id_title" class="required"><?= L_ARTICLE_TITLE ?>&nbsp;:</label>
						<?php plxUtils::printInput('title', plxUtils::strCheck($title), 'text', '42-255', false, 'full-width', L_ARTICLE_TITLE, '', true); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12 small">
<?php
if(!empty($artId) AND $artId!='0000') {
	$link = $plxAdmin->urlRewrite('?' . L_ARTICLE_URL . intval($artId) . '/' . $url)
?>
					 			<small>
					 				<strong><?= L_LINK_FIELD ?>&nbsp;:</strong>
					 				<a href="<?= $link ?>" title="<?= L_LINK_ACCESS ?> : <?= $link ?>" target="_blank"><?= $link ?></a>
					 			</small>
<?php
}
?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<input class="toggler" type="checkbox" id="toggler_chapo"<?= (empty($_GET['a']) || ! empty(trim($chapo))) ? ' checked' : ''; ?> />
						<label for="toggler_chapo"><?= L_HEADLINE_FIELD;?> : <span><?= L_ARTICLE_CHAPO_HIDE;?></span><span><?= L_ARTICLE_CHAPO_DISPLAY;?></span></label>
						<div>
							<?php plxUtils::printArea('chapo', plxUtils::strCheck($chapo), 0, 8, false, 'full-width', 'placeholder=" "'); ?>
						</div>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_content"><?= L_CONTENT_FIELD ?>&nbsp;:</label>
						<?php plxUtils::printArea('content', plxUtils::strCheck($content), 0, 20, false, 'full-width', 'placeholder=" "'); ?>
					</div>
				</div>
			</fieldset>
			<div class="grid gridthumb">
				<div class="col sml-12">
					<label for="id_thumbnail">
						<?= L_THUMBNAIL ?>&nbsp;:&nbsp;
						<a title="<?= L_THUMBNAIL_SELECTION ?>" id="toggler_thumbnail" href="javascript:void(0)" onclick="mediasManager.openPopup('id_thumbnail', true)" style="outline:none; text-decoration: none">+</a>
					</label>
					<?php plxUtils::printInput('thumbnail', plxUtils::strCheck($thumbnail), 'text', '255', false, 'full-width'); ?>
					<div class="grid" style="padding-top:10px">
						<div class="col sml-12 lrg-6">
							<label for="id_thumbnail_alt"><?= L_THUMBNAIL_TITLE ?>&nbsp;:</label>
							<?php plxUtils::printInput('thumbnail_title', plxUtils::strCheck($thumbnail_title), 'text', '255-255', false, 'full-width'); ?>
						</div>
						<div class="col sml-12 lrg-6">
							<label for="id_thumbnail_alt"><?= L_THUMBNAIL_ALT ?>&nbsp;:</label>
							<?php plxUtils::printInput('thumbnail_alt', plxUtils::strCheck($thumbnail_alt), 'text', '255-255', false, 'full-width'); ?>
						</div>
					</div>
					<div id="id_thumbnail_img">
<?php
if(!empty(trim($thumbnail))) {
	if(preg_match('@^(?:https?|data):@', $thumbnail)) {
		$src = $thumbnail;
	} else {
		$src = PLX_ROOT . $thumbnail;
		if(!file_exists($src)) {
			$src = '';
		}
	}

	if(!empty($src)) {
?>
						<img src="<?= $src ?>" title="<?= $thumbnail ?>" />
<?php
	}
}
?>
					</div>
				</div>
			</div>
			<?php eval($plxAdmin->plxPlugins->callHook('AdminArticleContent')); # Hook Plugins ?>
		</div>
<?php

/* ============= sidebar ============== */

?>
		<div class="sidebar col sml-12 med-5 lrg-3">

			<p><?= L_ARTICLE_STATUS ?>&nbsp;:&nbsp;
				<strong>
<?php
if(isset($_GET['a']) AND preg_match('/^_\d{4}$/', $_GET['a']))
	echo L_AWAITING;
elseif(in_array('draft', $catId)) {
	echo L_DRAFT;
?>
				<input type="hidden" name="catId[]" value="draft" />
<?php
}
else
	echo L_PUBLISHED;
?>
				</strong>
			</p>
			<fieldset>
<?php
	if($_SESSION['profil'] < PROFIL_WRITER) {
?>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_author"><?= L_ARTICLE_LIST_AUTHORS ?>&nbsp;:&nbsp;</label>
<?php
							plxUtils::printSelect('author', $_users, $author);
?>
					</div>
				</div>
<?php
	} else {
?>
							<input type="hidden" id="id_author" name="author" value="<?= $author ?>" />
							<strong><?php plxUtils::strCheck($plxAdmin->aUsers[$author]['name']); ?></strong>
<?php
	}
?>
				<div class="grid">
					<div class="col sml-12">
						<label class="required"><?= L_ARTICLE_DATE ?>&nbsp;:</label>
						<div class="inline-form publication">
							<?php plxUtils::printInput('date_publication_day', $date['day'], 'text', '2-2', false, 'day', date('d'), 'pattern="\d{2}"', true); ?>
							<?php plxUtils::printInput('date_publication_month', $date['month'], 'text', '2-2', false, 'month', date('m'), 'pattern="\d{2}"', true); ?>
							<?php plxUtils::printInput('date_publication_year', $date['year'], 'text', '2-4', false, 'year', date('Y'), 'pattern="\d{4}"', true); ?>
							<?php plxUtils::printInput('date_publication_time', $date['time'], 'text', '2-5', false, 'time', date('H:i'), 'pattern="\d{2}:\d{2}"', true); ?>
							<a class="ico_cal" href="javascript:void(0)" onclick="dateNow('date_publication', <?= date('Z') ?>); return false;" title="<?= L_NOW ?>">
								<img src="theme/images/date.png" alt="calendar" />
							</a>
						</div>
					</div>
				</div>
			<div class="grid">
				<div class="col sml-12">
					<label class="required"><?= L_DATE_CREATION ?>&nbsp;:</label>
					<div class="inline-form creation">
						<?php plxUtils::printInput('date_creation_day', $date_creation['day'], 'text', '2-2', false, 'day', date('d'), 'pattern="\d{2}"', true); ?>
						<?php plxUtils::printInput('date_creation_month', $date_creation['month'], 'text', '2-2', false, 'month', date('m'), 'pattern="\d{2}"', true); ?>
						<?php plxUtils::printInput('date_creation_year', $date_creation['year'], 'text', '2-4', false, 'year', date('Y'), 'pattern="\d{4}"', true); ?>
						<?php plxUtils::printInput('date_creation_time', $date_creation['time'], 'text', '2-5', false, 'time', date('H:i'), 'pattern="\d{2}:\d{2}"', true); ?>
						<a class="ico_cal" href="javascript:void(0)" onclick="dateNow('date_creation', <?= date('Z') ?>); return false;" title="<?= L_NOW ?>">
							<img src="theme/images/date.png" alt="calendar" />
						</a>
					</div>
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12">
					<?php plxUtils::printInput('date_update_old', $date_update_old, 'hidden'); ?>
					<label class="required"><?= L_DATE_UPDATE ?>&nbsp;:</label>
					<div class="inline-form update">
						<?php plxUtils::printInput('date_update_day', $date_update['day'], 'text', '2-2', false, 'day', date('d'), 'pattern="\d{2}"', true); ?>
						<?php plxUtils::printInput('date_update_month', $date_update['month'], 'text', '2-2', false, 'month', date('m'), 'pattern="\d{2}"', true); ?>
						<?php plxUtils::printInput('date_update_year', $date_update['year'], 'text', '2-4', false, 'year', date('Y'), 'pattern="\d{4}"', true); ?>
						<?php plxUtils::printInput('date_update_time', $date_update['time'], 'text', '2-5', false, 'time', date('H:i'), 'pattern="\d{2}:\d{2}"', true); ?>
						<a class="ico_cal" href="javascript:void(0)" onclick="dateNow('date_update', <?= date('Z') ?>); return false;" title="<?= L_NOW ?>">
							<img src="theme/images/date.png" alt="calendar" />
						</a>
					</div>
				</div>
			</div>
<?php /* ------- catégories -------- */ ?>
			<div class="grid">
				<div class="col sml-12">
					<label><?= L_ARTICLE_CATEGORIES ?>&nbsp;:</label>
					<label><input class="no-margin" disabled="disabled" type="checkbox" id="cat_unclassified" name="catId[]" <?= in_array('000', $catId) ? 'checked' : '' ?> value="000" /> <?= L_UNCLASSIFIED  ?></label>
<?php
if($_SESSION['profil'] < PROFIL_EDITOR) {
?>
					<label><input type="checkbox" class="no-margin" id="cat_pin" name="catId[]" <?= in_array('pin', $catId) ? 'checked' : '' ?> value="pin" /> <?= L_PINNED_ARTICLE ?></label>
<?php
} elseif(in_array('pin', $catId)) {
?>
					<input type="hidden" name="catId[]" value="pin" />
<?php
}
?>
					<label><input type="checkbox" class="no-margin" id="cat_home" name="catId[]" <?= in_array('home', $catId) ? 'checked' : '' ?> value="home" /> <?= L_CATEGORY_HOME_PAGE ?></label>
<?php
foreach($plxAdmin->aCats as $cat_id => $cat_name) {
	$selected = in_array($cat_id, $catId) ? 'checked' : '';
	$className = $plxAdmin->aCats[$cat_id]['active'] ? 'class="active"' : '';
?>
					<label <?= $className ?>><input type="checkbox" class="no-margin" id="cat_<?= $cat_id ?>" name="catId[]" <?= $selected ?> value="<?= $cat_id ?>" /> <?= plxUtils::strCheck($cat_name['name']) ?></label>
<?php
}
?>
				</div>
			</div>
<?php
/* ---------- nouvelle catégorie ---------- */
if($_SESSION['profil'] < PROFIL_WRITER) :
?>
			<div class="grid">
				<div class="col sml-12">
					<label for="id_new_catname"><?= L_NEW_CATEGORY ?>&nbsp;:</label>
					<div class="inline-form">
						<?php plxUtils::printInput('new_catname', '', 'text', '17-50'); ?>
						<input type="submit" name="new_category" value="<?= L_CATEGORY_ADD_BUTTON ?>" />
					</div>
				</div>
			</div>
<?php
endif;

/* ------------------ tags --------------*/
?>
				<div class="grid">
					<div class="col sml-12">
						<label for="tags"><?= L_ARTICLE_TAGS_FIELD; ?>&nbsp;:&nbsp;<a class="hint"><span><?= L_ARTICLE_TAGS_FIELD_TITLE; ?></span></a></label>
						<?php plxUtils::printInput('tags', $tags, 'text', '25-255', false, false); ?>
						<input class="toggler" type="checkbox" id="toggler_tags"<?= (empty($_GET['a']) || ! empty(trim($tags))) ? ' checked' : ''; ?> />
						<label for="toggler_tags"><span>-</span><span>+</span></label>
						<div id="tags" style="margin-top: 1rem">
<?php
if($plxAdmin->aTags) {
	$array=array();
	foreach($plxAdmin->aTags as $tag) {
		if($tags = array_map('trim', explode(',', $tag['tags']))) {
			foreach($tags as $tag) {
				if($tag!='') {
					$t = plxUtils::urlify($tag);
					if(!isset($array[$tag]))
						$array[$tag]=array('url'=>$t, 'count'=>1);
					else
						$array[$tag]['count']++;
				}
			}
		}
	}
	array_multisort($array);
	foreach($array as $tagname => $tag) {
?>
							<a href="javascript:void(0)" onclick="insTag('tags','<?= addslashes($tagname) ?>')" title="<?= plxUtils::strCheck($tagname) ?> (<?= $tag['count'] ?>)"><?= str_replace(' ', '&nbsp;', plxUtils::strCheck($tagname)) ?></a>&nbsp;(<?= $tag['count'] ?>)
<?php
	}
} else {
	echo L_NO_TAG;
}
?>
						</div>
					</div>
				</div>

				<div class="grid">
					<div class="col sml-12">
						<?php if($plxAdmin->aConf['allow_com'] > 0) : ?>
						<label for="id_allow_com"><?= L_ALLOW_COMMENTS ?>&nbsp;:</label>
						<?php plxUtils::printSelect('allow_com', ($plxAdmin->aConf['allow_com'] == 2) ? ALLOW_COM_SUBSCRIBERS : ALLOW_COM_OPTIONS, $allow_com); ?>
						<?php else: ?>
						<?php plxUtils::printInput('allow_com', '0', 'hidden'); ?>
						<?php endif; ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_url">
							<?= L_ARTICLE_URL_FIELD ?>&nbsp;:&nbsp;<a class="hint"><span><?= L_ARTICLE_URL_FIELD_TITLE ?></span></a>
						</label>
						<?php plxUtils::printInput('url', $url, 'text', '27-255'); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_template"><?= L_ARTICLE_TEMPLATE_FIELD ?>&nbsp;:</label>
						<?php plxUtils::printSelect('template', $plxAdmin->getTemplatesTheme('article'), $template); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_title_htmltag"><?= L_ARTICLE_TITLE_HTMLTAG ?>&nbsp;:</label>
						<?php plxUtils::printInput('title_htmltag', plxUtils::strCheck($title_htmltag), 'text', '27-255'); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_meta_description"><?= L_ARTICLE_META_DESCRIPTION ?>&nbsp;:</label>
						<?php plxUtils::printInput('meta_description', plxUtils::strCheck($meta_description), 'text', '27-255'); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_meta_keywords"><?= L_ARTICLE_META_KEYWORDS ?>&nbsp;:</label>
						<?php plxUtils::printInput('meta_keywords', plxUtils::strCheck($meta_keywords), 'text', '27-255'); ?>
					</div>
				</div>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminArticleSidebar'));

if($artId != '0000') : ?>
				<ul class="unstyled-list">
					<li>
						<a href="comments.php?a=<?= $artId ?>&amp;page=1" title="<?= L_ARTICLE_MANAGE_COMMENTS_TITLE ?>"><?= L_ARTICLE_MANAGE_COMMENTS ?></a>
						<ul>
<?php
	$status = array(
		'off'	=> array('_', L_COMMENT_OFFLINE, L_NEW_COMMENTS_TITLE ),
		'on'	=> array('', L_COMMENT_ONLINE, L_VALIDATED_COMMENTS_TITLE),
	);
	foreach($status as $k=>$v) {
		list($mod, $caption, $title) = $v;
		# récupération du nombre de commentaires
		$nbComs = $plxAdmin->getNbCommentaires('/^' . $mod . '(' . $artId . ')\.(\d{10,})-(\d+)\.xml$/', 'all');
		if($nbComs != 0) {
?>
							<li class="grid"><span class="col sml-7 sml-offset-1"><?= $caption ?> :</span><a class="col sml-2 sml-push-2 text-right" title="<?= $title ?>" href="comments.php?sel=<?= $k ?>line&a=<?= $artId ?>&page=1"><?= $nbComs ?></a></li>
<?php
		} else {
?>
							<li class="grid"><span class="col sml-7 sml-offset-1"><?= $caption ?> :</span><span class="col sml-2 sml-push-2 text-right" title="<?= $title ?>">0</span></li>
<?php
		}
	}
?>
						</ul>
					</li>
					<li class="text-center"><a class="button" href="comment_new.php?a=<?= $artId ?>" title="<?= L_COMMENT_NEW_COMMENT_TITLE ?>"><?= L_COMMENT_NEW_COMMENT ?></a></li>
				</ul>
<?php
endif;
?>

			</fieldset>

		</div>

	</div>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminArticleFoot'));
?>
<script>
	(function(id) {
		'use strict';
		const el = document.getElementById(id);
		const thumbnailImg = document.getElementById(id + '_img');
		if(!el || !thumbnailImg) {
			return;
		}

		el.addEventListener('change', function(event) {
			let dta = el.value;
			if(dta.trim().length == 0) {
				thumbnailImg.textContent = '';
			} else {
				dta = dta.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/’/g, '&\#39;').replace(/"/g, '&quot;');
				let link = dta.match(/^(?:https?|data):/gi) ? dta : '<?= $plxAdmin->racine ?>'+dta;
				thumbnailImg.innerHTML = '<img src="'+link+'" />';
			}
		});

	})('id_thumbnail');
</script>
<?php
# On inclut le footer
include 'foot.php';
