<?php

/**
 * Edition d'un article
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

require 'prepend.php';

if(!isset($plxAdmin)) {
	redirectLogin();
}

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminArticlePrepend'));

/**
 * On contrôle l'id de l'article et si l'utilisateur est son auteur
 *
 * @return never|string|void
 * */
function checkArtId(?string $artId) {
	global $plxAdmin;

	if(!empty($artId) and $artId == plxAdmin::NEW_ART_ID) {
		return;
	}

	$aFiles = null;
	# Si profil > PROFIL_MODERATOR, l'auteur ne peut modifier que ses propres articles.
	$userId = ($_SESSION['profil'] > PROFIL_MODERATOR) ? $_SESSION['user'] : '\d{3}';
	if($artId != null) {
		$artId = ltrim($artId, '_');
	}
	if(
		$artId == null or
		!preg_match('/^_?\d{4}$/', $artId) or
		!($aFiles = $plxAdmin->plxGlob_arts->query('/^_?' . $artId . '\.[^\.]+\.' . $userId . '\.\d{12}\.[\w-]+\.xml$/'))
	) {
		# Article inconnu
		plxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
		redirectAdmin();
	}

	return $aFiles[0];
}

$artId = plxAdmin::NEW_ART_ID;
$isModeration = ($plxAdmin->aConf['mod_art'] and $_SESSION['profil'] > PROFIL_MODERATOR);

# Formulaire validé
if(!empty($_POST)) {
	# Création, mise à jour, suppression ou aperçu

	# Validation id Article. Sinon retour liste articles
	checkArtId($_POST['artId']);
	$artId = $_POST['artId'];

	# Titre par défaut si titre vide. On rajoute la date du jour pour éviter la duplication de l'url
	if(empty(trim($_POST['title']))) {
		$_POST['title'] = L_DEFAULT_NEW_ARTICLE_TITLE . ' ' . date('y-m-dTH:i');
	}

	# si aucune catégorie sélectionnée on place l'article dans la catégorie "non classé"
	if(empty($_POST['catId'])) {
		$_POST['catId'] = array('000');
	}

	# Si demande d'enregistrement en brouillon, on ajoute la categorie draft à la liste et on retire la demande de validation
	# Voir test sur $content['catId'] dans plxAdmin::editArticle()
	if(isset($_POST['draft'])) {
		if($_POST['catId'][0] != 'draft') {
			if($_SESSION['user'] == $_POST['author']) {
				array_unshift($_POST['catId'], 'draft');
			} else {
				$artId = '_' . $artId;
			}
		}
	} elseif(isset($_POST['update']) OR isset($_POST['publish']) OR isset($_POST['preview'])) {
		# Si demande de publication, de validation, d'aperçu, on supprime la catégorie draft si elle existe
		if($_POST['catId'][0] == 'draft') {
			$draft = array_shift($_POST['catId']);
		}
	}

	# Vérification de la validité des dates publicatio, création, mise à jour. Sinon on recharge la page actuelle.
	foreach(array(
		'publication' => L_ERR_INVALID_PUBLISHING_DATE,
		'creation' => L_ERR_INVALID_DATE_CREATION,
		'update' => L_ERR_INVALID_DATE_UPDATE,
	) as $dt=>$msg) {
		if(!plxDate::checkDate($_POST['date_' . $dt . '_day'], $_POST['date_' . $dt . '_month'],$_POST['date_' . $dt . '_year'],$_POST['date_' . $dt . '_time'])) {
			plxMsg::Error($msg);
			reloadPage();
		}
	}

	if(!array_key_exists($_POST['author'], $plxAdmin->aUsers)) {
		plxMsg::Error(L_USER_UNKNOWN);
		redirectAdmin();
	}

	# Previsualisation d'un article dans un nouvel onglet
	if(isset($_POST['preview'])) {
		$_POST['url'] = L_DEFAULT_NEW_ARTICLE_URL;

		# Hook Plugins
		eval($plxAdmin->plxPlugins->callHook('AdminArticlePreview'));

		$token = 'preview';
		$plxAdmin->editArticle($_POST, $token);
		exit;
	}

	# Suppression d'un article et retour à la liste articles
	if(isset($_POST['delete'])) {
		$plxAdmin->delArticle($artId);
		redirectAdmin();
	}

	# Mode création ou maj et rechargement de la page actuelle
	if(isset($_POST['update']) OR isset($_POST['publish']) OR isset($_POST['draft'])) {
		# Vérification de l'unicité de l'url
		$url = plxUtils::urlify(empty(trim($_POST['url'])) ? $_POST['title'] : trim($_POST['url']));
		if(isset($_POST['publish'])) {
			foreach($plxAdmin->plxGlob_arts->aFiles as $filename) {
				if(preg_match('#\._?(?!' . $artId . ')\.(?:draft,|\d{3},)*(?:home|\d{3})(,\d{3})*.\d{3}.\d{12}\.' . $url . '\.xml$#', $filename)) {
					plxMsg::Error(L_ERR_URL_ALREADY_EXISTS." : ".plxUtils::strCheck($url));
					reloadPage();
				}
			}
		}

		$plxAdmin->editArticle($_POST, $artId);
		header('Location: ' . $_SERVER['SCRIPT_NAME'] . '?a=' . $artId);
		exit;
	}

	# Ajout d'une catégorie
	if(isset($_POST['new_category'])) {
		# Ajout de la nouvelle catégorie
		$plxAdmin->editCategories($_POST);
		# On recharge la nouvelle liste
		$plxAdmin->getCategories(path('XMLFILE_CATEGORIES'));
	}

	# Alimentation des variables
	$author = $_POST['author']; # Déjà vérifié ci-avant
	$title = trim($_POST['title']);
	$chapo = trim($_POST['chapo']);
	$content = trim($_POST['content']);
	$tags = trim($_POST['tags']);
	$url = $_POST['url'];

	$catId = $_POST['catId'];
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

	# On parse et alimente nos variables
	$result = $plxAdmin->parseArticle(PLX_ROOT . $plxAdmin->aConf['racine_articles'] . checkArtId($_GET['a']));
	$artId = $result['numero'];
	$author = $result['author'];
	# On vérifie l'auteur de l'article. Sinon retour liste articles
	if($_SESSION['profil'] >= PROFIL_EDITOR and $author != $_SESSION['user']) {
		plxMsg::Error(L_ERR_FORBIDDEN_ARTICLE);
		redirectAdmin();
	}

	$title = trim($result['title']);
	$chapo = trim($result['chapo']);
	$content = trim($result['content']);
	$tags = trim($result['tags']);
	$url = $result['url'];
	$date = plxDate::date2Array($result['date']);
	$date_creation = plxDate::date2Array($result['date_creation']);
	$date_update = plxDate::date2Array($result['date_update']);
	$date_update_old = $result['date_update'];
	$catId = explode(',', $result['categorie']);
	$allow_com = $result['allow_com'];
	$template = $result['template'];
	$meta_description = $result['meta_description'];
	$meta_keywords = $result['meta_keywords'];
	$title_htmltag = $result['title_htmltag'];
	$thumbnail = $result['thumbnail'];
	$thumbnail_title = $result['thumbnail_title'];
	$thumbnail_alt = $result['thumbnail_alt'];

	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminArticleParseData'));

} else {
	# On a rien validé, c'est pour la création d'un article
	$title = L_DEFAULT_NEW_ARTICLE_TITLE;
	$chapo = $url = '';
	$content = '';
	$tags = '';
	$author = $_SESSION['user'];
	$date = plxDate::date2Array(date('YmdHi'));
	$date_creation = $date;
	$date_update = $date;
	$date_update_old = '';
	$catId = array('draft');
	$allow_com = $plxAdmin->aConf['allow_com'];
	$template = 'article.php';
	$meta_description=$meta_keywords=$title_htmltag='';
	$thumbnail = '';
	$thumbnail_title = '';
	$thumbnail_alt = '';

	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminArticleInitData'));
}

# On récupère les templates des articles
$glob = plxGlob::getInstance(PLX_ROOT . $plxAdmin->aConf['racine_themes'] . $plxAdmin->aConf['style'] . '/' , false, true, '#^article(?:-[\w-]+)?\.php$#');
if (!empty($glob->aFiles)) {
	$aTemplates = array();
	foreach($glob->aFiles as $v)
		$aTemplates[$v] = basename($v, '.php');
} else {
	$aTemplates = array('' => L_NONE1);
}

# On inclut le header
require 'top.php';
?>
<form method="post" id="form_article">
	<div class="inline-form action-bar">
		<h2><?= ($artId == plxAdmin::NEW_ART_ID) ? L_MENU_NEW_ARTICLES : L_ARTICLE_EDITING; ?></h2>
		<p><a class="back" href="index.php"><?= L_BACK_TO_ARTICLES ?></a></p>
		<input type="submit" name="preview" onclick="this.form.target='plx_preview_article'; return true;" value="<?= L_ARTICLE_PREVIEW_BUTTON ?>"/>
<?php
if($catId[0] == 'draft' or $artId == plxAdmin::NEW_ART_ID or $artId[0] == '_') {
	# l'article n'est pas publié : brouillon (draft), nouvel article, article hors-ligne
	$offline = $isModeration ? false : ($artId[0] == '_');
?>
		<input type="submit" name="<?= $offline ? 'update' : 'draft' ?>" value="<?= $offline ? L_ARTICLE_UPDATE_BUTTON : L_ARTICLE_DRAFT_BUTTON ?>" />
		<input type="submit" name="publish" value="<?= $isModeration ? L_ARTICLE_MODERATE_BUTTON : L_ARTICLE_PUBLISHING_BUTTON ?>" />
<?php
} else {
	# L'article est publié
?>
		<input type="submit" name="publish" value="<?= $isModeration ? L_ARTICLE_MODERATE_BUTTON : L_ARTICLE_PUBLISHING_BUTTON ?>" />
		<input type="submit" name="draft" value="<?= L_ARTICLE_OFFLINE_BUTTON ?>" />
<?php
	if($artId != plxAdmin::NEW_ART_ID) {
				# L'article existe. On peut demander sa suppression
?>
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		<input class="red" type="submit" name="delete" value="<?= L_DELETE ?>" onclick="return confirm('<?= L_ARTICLE_DELETE_CONFIRM ?>');" />
<?php
	}
}
?>
	</div>
<?php eval($plxAdmin->plxPlugins->callHook('AdminArticleTop')) # Hook Plugins ?>
	<div class="grid">
		<div class="col sml-12 med-7 lrg-8">
			<fieldset>
				<div class="grid">
					<div class="col sml-12">
						<?php plxUtils::printInput('artId', $artId, 'hidden'); ?>
						<label for="id_title"><?= L_ARTICLE_TITLE ?>&nbsp;:</label>
						<?php plxUtils::printInput('title',plxUtils::strCheck($title),'text','42-255',false,'full-width'); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12 small">
<?php if($artId != plxAdmin::NEW_ART_ID and $artId[0] != '_') {
	# Ni nouvel article, ni brouillon
	$link = $plxAdmin->urlRewrite('?article' . intval($artId) . '/' . $url);
?>
			 			<small>
			 				<strong><?= L_LINK_FIELD ?>&nbsp;:</strong>
			 				<a href="<?= $link ?>" target="plx_preview" title="<?= L_LINK_ACCESS ?> : <?= $link ?>"><?= $link ?></a>
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
							<?php plxUtils::printArea('chapo',plxUtils::strCheck($chapo),0,8); ?>
						</div>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_content"><?= L_CONTENT_FIELD ?>&nbsp;:</label>
						<?php plxUtils::printArea('content',plxUtils::strCheck($content),0,20); ?>
					</div>
				</div>
			</fieldset>
			<div class="grid gridthumb">
				<div class="col sml-12">
					<label for="id_thumbnail">
						<?= L_THUMBNAIL ?>&nbsp;:&nbsp;
						<a title="<?= L_THUMBNAIL_SELECTION ?>" id="toggler_thumbnail" href="javascript:void(0)" onclick="mediasManager.openPopup('id_thumbnail', true)" style="outline:none; text-decoration: none">+</a>
					</label>
					<?php plxUtils::printInput('thumbnail',plxUtils::strCheck($thumbnail),'text','255',false,'full-width','','onkeyup="refreshImg(this.value)"'); ?>
					<div class="grid" style="padding-top:10px">
						<div class="col sml-12 lrg-6">
							<label for="id_thumbnail_alt"><?= L_THUMBNAIL_TITLE ?>&nbsp;:</label>
							<?php plxUtils::printInput('thumbnail_title',plxUtils::strCheck($thumbnail_title),'text','255-255',false,'full-width'); ?>
						</div>
						<div class="col sml-12 lrg-6">
							<label for="id_thumbnail_alt"><?= L_THUMBNAIL_ALT ?>&nbsp;:</label>
							<?php plxUtils::printInput('thumbnail_alt',plxUtils::strCheck($thumbnail_alt),'text','255-255',false,'full-width'); ?>
						</div>
					</div>
					<div id="id_thumbnail_img">
<?php
					if(preg_match('@^(?:https?|data):@', $thumbnail)) {
						$src = $thumbnail;
					} else {
						$src = PLX_ROOT . $thumbnail;
						if(!is_file($src)) {
							$src = false;
						}
					}
					if($src) echo '<img src="' . $src . '" alt="' . L_THUMBNAIL . '" />' . PHP_EOL;
?>
					</div>
				</div>
			</div>
<?php eval($plxAdmin->plxPlugins->callHook('AdminArticleContent')) # Hook Plugins ?>
			<?= plxToken::getTokenPostMethod() ?>
		</div>
<?php /* ------------ sidebar article ----------- */ ?>
		<div class="sidebar col sml-12 med-5 lrg-4">
			<p><?= L_ARTICLE_STATUS ?>&nbsp;:&nbsp;
				<strong>
<?php
				if($catId[0] == 'draft') {
					echo L_DRAFT;
				} elseif($artId[0] == '_') {
					echo L_AWAITING;
				} else {
					echo L_PUBLISHED;
				}
?>
				</strong>
			</p>
			<fieldset>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_author"><?= L_ARTICLE_LIST_AUTHORS ?>&nbsp;:&nbsp;</label>
<?php
						if($_SESSION['profil'] < PROFIL_WRITER) {
							# On construit la liste des utilisateurs
							$_users = array();
							foreach($plxAdmin->aUsers as $_userid => $user_infos) {
								if(!$user_infos['active'] or $user_infos['delete'] or $user_infos['profil'] > L_PROFIL_WRITER) {
									continue;
								}

								$_users[$user_infos['profil']][$_userid] = plxUtils::strCheck($user_infos['name']);
							}

							plxUtils::printSelect('author', $_users, $author);
						} else {
?>
						<input type="hidden" id="id_author" name="author" value="<?= $author ?>" />
						<strong><?= plxUtils::strCheck($plxAdmin->aUsers[$author]['name']) ?></strong>
<?php
						}
?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label><?= L_ARTICLE_DATE ?>&nbsp;:</label>
						<div class="inline-form publication">
							<?php plxUtils::printInput('date_publication_day',$date['day'],'text','2-2',false,'day'); ?>
							<?php plxUtils::printInput('date_publication_month',$date['month'],'text','2-2',false,'month'); ?>
							<?php plxUtils::printInput('date_publication_year',$date['year'],'text','2-4',false,'year'); ?>
							<?php plxUtils::printInput('date_publication_time',$date['time'],'text','2-5',false,'time'); ?>
							<a class="ico_cal" href="javascript:void(0)" onclick="dateNow('date_publication', <?= date('Z') ?>); return false;" title="<?= L_NOW ?>">
								<img src="theme/images/date.png" alt="calendar" />
							</a>
						</div>
					</div>
				</div>
			<div class="grid">
				<div class="col sml-12">
					<label><?= L_DATE_CREATION ?>&nbsp;:</label>
					<div class="inline-form creation">
						<?php plxUtils::printInput('date_creation_day',$date_creation['day'],'text','2-2',false,'day'); ?>
						<?php plxUtils::printInput('date_creation_month',$date_creation['month'],'text','2-2',false,'month'); ?>
						<?php plxUtils::printInput('date_creation_year',$date_creation['year'],'text','2-4',false,'year'); ?>
						<?php plxUtils::printInput('date_creation_time',$date_creation['time'],'text','2-5',false,'time'); ?>
						<a class="ico_cal" href="javascript:void(0)" onclick="dateNow('date_creation', <?= date('Z') ?>); return false;" title="<?= L_NOW ?>">
							<img src="theme/images/date.png" alt="calendar" />
						</a>
					</div>
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12">
					<?php plxUtils::printInput('date_update_old', $date_update_old, 'hidden'); ?>
					<label><?= L_DATE_UPDATE ?>&nbsp;:</label>
					<div class="inline-form update">
						<?php plxUtils::printInput('date_update_day',$date_update['day'],'text','2-2',false,'day'); ?>
						<?php plxUtils::printInput('date_update_month',$date_update['month'],'text','2-2',false,'month'); ?>
						<?php plxUtils::printInput('date_update_year',$date_update['year'],'text','2-4',false,'year'); ?>
						<?php plxUtils::printInput('date_update_time',$date_update['time'],'text','2-5',false,'time'); ?>
						<a class="ico_cal" href="javascript:void(0)" onclick="dateNow('date_update', <?= date('Z') ?>); return false;" title="<?= L_NOW ?>">
							<img src="theme/images/date.png" alt="calendar" />
						</a>
					</div>
				</div>
			</div>

				<div class="grid">
					<div class="col sml-12">
						<label><?= L_ARTICLE_CATEGORIES ?>&nbsp;:</label>
<?php
							if($catId[0] == 'draft') {
?>
						<input type="hidden" name="catId[]" value="draft" />
<?php
							}
?>
						<label>
							<input class="no-margin" disabled="disabled" type="checkbox" id="cat_unclassified" name="catId[]" <?php if(in_array('000', $catId)) { echo 'checked'; } ?> value="000" />&nbsp;
							<span><?= L_UNCLASSIFIED ?></span>
						</label>
						<label>
							<input type="checkbox" class="no-margin" name="catId[]" <?php if(in_array('home', $catId)) { echo 'checked'; } ?> value="home" />&nbsp;
							<span><?= L_CATEGORY_HOME_PAGE ?></span>
						</label>
<?php
							foreach($plxAdmin->aCats as $cat_id => $cat_infos) {
?>
						<label>
							<input type="checkbox" class="no-margin" <?php if(empty($cat_infos['active'])) { echo 'disabled'; } ?> name="catId[]" <?php if(in_array($cat_id, $catId)) { echo 'checked'; } ?> value="<?= $cat_id ?>" />&nbsp;
							<span><?= $cat_infos['name'] ?></span>
						</label>
<?php
							}
?>
					</div>
				</div>
<?php if($_SESSION['profil'] <= PROFIL_EDITOR) : ?>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_new_catname"><?= L_NEW_CATEGORY ?>&nbsp;:</label>
						<div class="inline-form">
							<?php plxUtils::printInput('new_catname','','text','17-50') ?>
							<input type="submit" name="new_category" value="<?= L_CATEGORY_ADD_BUTTON ?>" />
						</div>
					</div>
				</div>
<?php endif; ?>
				<div class="grid">
					<div class="col sml-12">
						<label for="tags"><?= L_ARTICLE_TAGS_FIELD; ?>&nbsp;:&nbsp;<a class="hint"><span><?= L_ARTICLE_TAGS_FIELD_TITLE; ?></span></a></label>
						<?php plxUtils::printInput('tags', $tags,'text', '25-255'); ?>
						<input class="toggler" type="checkbox" id="toggler_tags"<?= (empty($_GET['a']) || ! empty(trim($tags))) ? ' checked' : ''; ?> />
						<label for="toggler_tags"><span>-</span><span>+</span></label>
						<div id="tags" style="margin-top: 1rem">
<?php
							if($plxAdmin->aTags) {
								$array=array();
								foreach($plxAdmin->aTags as $tag) {
									if(empty($tag['tags'])) {
										continue;
									}

									$tags = array_map('trim', explode(',', $tag['tags']));
									foreach($tags as $tag) {
										if($tag!='') {
											$t = plxUtils::urlify($tag);
											if(!isset($array[$tag]))
												$array[$tag]=array('url'=>$t,'count'=>1);
											else
												$array[$tag]['count']++;
										}
									}
								}
								array_multisort($array);
								foreach($array as $tagname => $tag) {
									echo '<a href="javascript:void(0)" onclick="insTag(\'tags\',\''.addslashes($tagname).'\')" title="'.plxUtils::strCheck($tagname).' ('.$tag['count'].')">'.
									str_replace(' ', '&nbsp;', plxUtils::strCheck($tagname)).'</a>&nbsp;('.$tag['count'].')&nbsp; ';
								}
							}
							else echo L_NO_TAG;
?>
						</div>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
<?php if($plxAdmin->aConf['allow_com']=='1') : ?>
						<label for="id_allow_com"><?= L_ALLOW_COMMENTS ?>&nbsp;:</label>
						<?php plxUtils::printSelect('allow_com',array('1'=>L_YES,'0'=>L_NO),$allow_com); ?>
<?php else: ?>
						<?php plxUtils::printInput('allow_com','0','hidden'); ?>
<?php endif; ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_url">
							<?= L_ARTICLE_URL_FIELD ?>&nbsp;:&nbsp;<a class="hint"><span><?= L_ARTICLE_URL_FIELD_TITLE ?></span></a>
						</label>
						<?php plxUtils::printInput('url',$url,'text','27-255'); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_template"><?= L_ARTICLE_TEMPLATE_FIELD ?>&nbsp;:</label>
						<?php plxUtils::printSelect('template', $aTemplates, $template); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_title_htmltag"><?= L_ARTICLE_TITLE_HTMLTAG ?>&nbsp;:</label>
						<?php plxUtils::printInput('title_htmltag',plxUtils::strCheck($title_htmltag),'text','27-255'); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_meta_description"><?= L_ARTICLE_META_DESCRIPTION ?>&nbsp;:</label>
						<?php plxUtils::printInput('meta_description',plxUtils::strCheck($meta_description),'text','27-255'); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_meta_keywords"><?= L_ARTICLE_META_KEYWORDS ?>&nbsp;:</label>
						<?php plxUtils::printInput('meta_keywords',plxUtils::strCheck($meta_keywords),'text','27-255'); ?>
					</div>
				</div>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminArticleSidebar'));

if($artId != plxAdmin::NEW_ART_ID) {
	# L'article peut être mis hors-ligne ultérieurement
	$id = ltrim($artId, '_');
	# récupération du nombre de commentaires
	$nbComsToValidate = $plxAdmin->getNbCommentaires('/^_' . $id . '.(.*).xml$/','all');
	$nbComsValidated = $plxAdmin->getNbCommentaires('/^' . $id . '.(.*).xml$/','all');
?>
				<ul class="unstyled-list">
					<li>
						<a href="comments.php?a=<?= $artId ?>&amp;page=1" title="<?= L_ARTICLE_MANAGE_COMMENTS_TITLE ?>"><?= L_ARTICLE_MANAGE_COMMENTS ?></a>
						<ul>
							<li><?= L_COMMENT_OFFLINE ?> : <a title="<?= L_NEW_COMMENTS_TITLE ?>" href="comments.php?sel=offline&a=<?= $id ?>&amp;page=1"><?= $nbComsToValidate ?></a></li>
							<li><?= L_COMMENT_ONLINE ?> : <a title="<?= L_VALIDATED_COMMENTS_TITLE ?>" href="comments.php?sel=online&a=<?= $id ?>&amp;page=1"><?= $nbComsValidated ?></a></li>
						</ul>
					</li>
<?php
	if($artId[0] != '_') {
?>
					<li><a href="comment_new.php?a=<?= $artId ?>" title="<?= L_ARTICLE_NEW_COMMENT_TITLE ?>"><?= L_ARTICLE_NEW_COMMENT ?></a></li>
<?php
	}
?>
				</ul>
<?php
}
?>
			</fieldset>
		</div>
	</div>
</form>

<script>
function refreshImg(dta) {
	if(dta.trim()==='') {
		document.getElementById('id_thumbnail_img').innerHTML = '';
	} else {
		let lt = /</g,
			gt = />/g,
			ap = /’/g,
			ic = /"/g;
		dta = dta.replace(lt, "&lt;").replace(gt, "&gt;").replace(ap, "&\#39;").replace(ic, "&\#34;");
		let link = dta.match(/^(?:https?|data):/gi) ? dta : '<?= $plxAdmin->racine ?>'+dta;
		document.getElementById('id_thumbnail_img').innerHTML = '<img src="'+link+'" alt="" />';
	}
}
</script>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminArticleFoot'));

# On inclut le footer
require 'foot.php';
