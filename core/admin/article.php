<?php

/**
 * Edition d'un article
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include __DIR__ .'/tags/prepend.php';
use Pluxml\PlxDate;
use Pluxml\PlxGlob;
use Pluxml\PlxMsg;
use Pluxml\PlxToken;
use Pluxml\PlxUtils;

# Control du token du formulaire
if(!isset($_POST['preview']))
	PlxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminArticlePrepend'));

# validation de l'id de l'article si passé en parametre
if(isset($_GET['a']) AND !preg_match('/^_?[0-9]{4}$/',$_GET['a'])) {
	PlxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
	header('Location: articles.php');
	exit;
}

# Formulaire validé
if(!empty($_POST)) { # Création, mise à jour, suppression ou aperçu

	if(!isset($_POST['catId'])) $_POST['catId']=array();
	# Titre par défaut si titre vide
	if(trim($_POST['title'])=='') $_POST['title'] = L_DEFAULT_NEW_ARTICLE_TITLE;
	# Si demande d'enregistrement en brouillon on ajoute la categorie draft à la liste et on retire la demande de validation
	if(isset($_POST['draft']) AND !in_array('draft',$_POST['catId'])) array_unshift($_POST['catId'], 'draft');
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
			PlxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
			header('Location: articles.php');
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
		$art['thumbnail'] = $_POST['thumbnail'];
		$art['thumbnail_title'] = $_POST['thumbnail_title'];
		$art['thumbnail_alt'] = $_POST['thumbnail_alt'];
		$art['categorie'] = '000';
		if(!empty($_POST['catId'])) {
			$array=array();
			foreach($_POST['catId'] as $k => $v) {
				if($v!='draft') $array[]=$v;
			}
			$art['categorie']=implode(',',$array);
		}
		$art['date'] = $_POST['date_publication_year'].$_POST['date_publication_month'].$_POST['date_publication_day'].substr(str_replace(':','',$_POST['date_publication_time']),0,4);
		$art['date_creation'] = $_POST['date_creation_year'].$_POST['date_creation_month'].$_POST['date_creation_day'].substr(str_replace(':','',$_POST['date_creation_time']),0,4);
		$art['date_update'] = $_POST['date_update_year'].$_POST['date_update_month'].$_POST['date_update_day'].substr(str_replace(':','',$_POST['date_update_time']),0,4);
		$art['nb_com'] = 0;
		$tmpstr = (!empty(trim($_POST['url']))) ? $_POST['url'] : $_POST['title'];
		$art['url'] = PlxUtils::urlify($tmpstr);
		if(empty($art['url'])) $art['url'] = L_DEFAULT_NEW_ARTICLE_URL;

		# Hook Plugins
		eval($plxAdmin->plxPlugins->callHook('AdminArticlePreview'));

		$article[0] = $art;
		$_SESSION['preview'] = $article;
		header('Location: '.PLX_ROOT.'articles.php?preview');
		exit;
	}
	# Suppression d'un article
	if(isset($_POST['delete'])) {
		$plxAdmin->delArticle($_POST['artId']);
		header('Location: articles.php');
		exit;
	}
	# Mode création ou maj
	if(isset($_POST['update']) OR isset($_POST['publish']) OR isset($_POST['moderate']) OR isset($_POST['draft'])) {

		$valid = true;
		# Vérification de l'unicité de l'url
		$url = PlxUtils::urlify(!empty($_POST['url']) ? $_POST['url'] : $_POST['title']);
		foreach($plxAdmin->plxGlob_arts->aFiles as $numart => $filename) {
			if(preg_match("/^_?[0-9]{4}.([0-9,|home|draft]*).[0-9]{3}.[0-9]{12}.$url.xml$/", $filename)) {
				if($numart!=str_replace('_', '',$_POST['artId'])) {
					$valid = PlxMsg::Error(L_ERR_URL_ALREADY_EXISTS." : ".PlxUtils::strCheck($url)) AND $valid;
				}
			}
		}
		# Vérification de la validité de la date de publication
		if(!PlxDate::checkDate($_POST['date_publication_day'],$_POST['date_publication_month'],$_POST['date_publication_year'],$_POST['date_publication_time'])) {
			$valid = PlxMsg::Error(L_ERR_INVALID_PUBLISHING_DATE) AND $valid;
		}
		# Vérification de la validité de la date de creation
		if(!PlxDate::checkDate($_POST['date_creation_day'],$_POST['date_creation_month'],$_POST['date_creation_year'],$_POST['date_creation_time'])) {
			$valid = PlxMsg::Error(L_ERR_INVALID_DATE_CREATION) AND $valid;
		}
		# Vérification de la validité de la date de mise à jour
		if(!PlxDate::checkDate($_POST['date_update_day'],$_POST['date_update_month'],$_POST['date_update_year'],$_POST['date_update_time'])) {
			$valid = PlxMsg::Error(L_ERR_INVALID_DATE_UPDATE) AND $valid;
		}
		if($valid) {
			$plxAdmin->editArticle($_POST,$_POST['artId']);
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
	$catId = isset($_POST['catId'])?$_POST['catId']:array();
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
	$thumbnail = $_POST['thumbnail'];
	$thumbnail_title = $_POST['thumbnail_title'];
	$thumbnail_alt = $_POST['thumbnail_alt'];
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminArticlePostData'));
} elseif(!empty($_GET['a'])) { # On n'a rien validé, c'est pour l'édition d'un article
	# On va rechercher notre article
	if(($aFile = $plxAdmin->plxGlob_arts->query('/^'.$_GET['a'].'.(.+).xml$/')) == false) { # Article inexistant
		PlxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
		header('Location: articles.php');
		exit;
	}
	# On parse et alimente nos variables
	$result = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$aFile['0']);
	$title = trim($result['title']);
	$chapo = trim($result['chapo']);
	$content = trim($result['content']);
	$tags = trim($result['tags']);
	$author = $result['author'];
	$url = $result['url'];
	$date = PlxDate::date2Array($result['date']);
	$date_creation = PlxDate::date2Array($result['date_creation']);
	$date_update = PlxDate::date2Array($result['date_update']);
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

	if($author!=$_SESSION['user'] AND $_SESSION['profil']==PROFIL_WRITER) {
		PlxMsg::Error(L_ERR_FORBIDDEN_ARTICLE);
		header('Location: articles.php');
		exit;
	}
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminArticleParseData'));

} else { # On a rien validé, c'est pour la création d'un article
	$title = PlxUtils::strRevCheck(L_DEFAULT_NEW_ARTICLE_TITLE);
	$chapo = $url = '';
	$content = '';
	$tags = '';
	$author = $_SESSION['user'];
	$date = array ('year' => date('Y'),'month' => date('m'),'day' => date('d'),'time' => date('H:i'));
	$date_creation = array ('year' => date('Y'),'month' => date('m'),'day' => date('d'),'time' => date('H:i'));
	$date_update = array ('year' => date('Y'),'month' => date('m'),'day' => date('d'),'time' => date('H:i'));
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
include __DIR__ .'/tags/top.php';

# On construit la liste des utilisateurs
foreach($plxAdmin->aUsers as $_userid => $_user) {
	if($_user['active'] AND !$_user['delete'] ) {
		if($_user['profil']==PROFIL_ADMIN)
			$_users[L_PROFIL_ADMIN][$_userid] = PlxUtils::strCheck($_user['name']);
		elseif($_user['profil']==PROFIL_MANAGER)
			$_users[L_PROFIL_MANAGER][$_userid] = PlxUtils::strCheck($_user['name']);
		elseif($_user['profil']==PROFIL_MODERATOR)
			$_users[L_PROFIL_MODERATOR][$_userid] = PlxUtils::strCheck($_user['name']);
		elseif($_user['profil']==PROFIL_EDITOR)
			$_users[L_PROFIL_EDITOR][$_userid] = PlxUtils::strCheck($_user['name']);
		else
			$_users[L_PROFIL_WRITER][$_userid] = PlxUtils::strCheck($_user['name']);
	}
}

# On récupère les templates des articles
$aTemplates = array();
$files = PlxGlob::getInstance(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$plxAdmin->aConf['style']);
if ($array = $files->query('/^article(-[a-z0-9-_]+)?.php$/')) {
	foreach($array as $k=>$v)
		$aTemplates[$v] = $v;
}
if(empty($aTemplates)) $aTemplates[''] = L_NONE1;
$cat_id='000';

//Vue.js datas initialisation
$builkDatas = array(
		'category' => false,
		'tags' => false,
		'comments' => false,
		'url' => false,
		'seo' => false,
		'tab' => true
);
$datas = json_encode($builkDatas);

?>

<script>
function refreshImg(dta) {
	if(dta.trim()==='') {
		document.getElementById('id_thumbnail_img').innerHTML = '';
	} else {
		var link = dta.match(/^(?:https?|data):/gi) ? dta : '<?= $plxAdmin->racine ?>'+dta;
		document.getElementById('id_thumbnail_img').innerHTML = '<img src="'+link+'" alt="" />';
	}
}
</script>

<form action="article.php" method="post" id="form_article">

<div class="adminheader grid-6">
	<div class="col-2">
		<h2 class="h3-like"><?= (empty($_GET['a']))?L_MENU_NEW_ARTICLES:L_ARTICLE_EDITING; ?></h2>
		<p><a class="back" href="articles.php"><?= L_BACK_TO_ARTICLES ?></a></p>
	</div>
	<div class="col-4 mtm txtright">
		<p class="pas inbl"><?= L_ARTICLE_STATUS ?>&nbsp;:&nbsp;
			<strong>
				<?php //TODO create a PlxAdmin fonction to get article status (P3ter)
					if(isset($_GET['a']) AND preg_match('/^_[0-9]{4}$/',$_GET['a']))
						echo L_AWAITING;
					elseif(in_array('draft', $catId)) {
						echo L_DRAFT;
						echo '<input type="hidden" name="catId[]" value="draft" />';
					}
					else
						echo L_PUBLISHED;
				?>
			</strong>
		</p>
		<input class="btn--primary" type="submit" name="preview" onclick="this.form.target='_blank';return true;" value="<?= L_ARTICLE_PREVIEW_BUTTON ?>"/>
			<?php
				if($_SESSION['profil']>PROFIL_MODERATOR AND $plxAdmin->aConf['mod_art']) {
					if(in_array('draft', $catId)) { # brouillon
						if($artId!='0000') # nouvel article
						echo '<input class="btn--primary" onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="'.L_ARTICLE_DRAFT_BUTTON.'"/> ';
						echo '<input class="btn--primary" onclick="this.form.target=\'_self\';return true;" type="submit" name="moderate" value="'.L_ARTICLE_MODERATE_BUTTON.'"/> ';
						echo '<span>&nbsp;&nbsp;&nbsp;</span><input class="red" type="submit" name="delete" value="'.L_DELETE.'" onclick="Check=confirm(\''.L_ARTICLE_DELETE_CONFIRM.'\');if(Check==false) {return false;} else {this.form.target=\'_self\';return true;}" /> ';
					} else {
						if(isset($_GET['a']) AND preg_match('/^_[0-9]{4}$/',$_GET['a'])) { # en attente
							echo '<input class="btn--primary" onclick="this.form.target=\'_self\';return true;" type="submit" name="update" value="' . L_ARTICLE_UPDATE_BUTTON . '"/> ';
							echo '<input class="btn--primary" onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="'.L_ARTICLE_DRAFT_BUTTON.'"/> ';
							echo '<span>&nbsp;&nbsp;&nbsp;</span><input class="red" type="submit" name="delete" value="'.L_DELETE.'" onclick="Check=confirm(\''.L_ARTICLE_DELETE_CONFIRM.'\');if(Check==false) {return false;} else {this.form.target=\'_self\';return true;}" /> ';
						} else {
							echo '<input onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="'.L_ARTICLE_DRAFT_BUTTON.'"/> ';
							echo '<input onclick="this.form.target=\'_self\';return true;" type="submit" name="moderate" value="'.L_ARTICLE_MODERATE_BUTTON.'"/> ';
						}
					}
				} else {
					if(in_array('draft', $catId)) {
						echo '<input class="btn--primary" onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="' . L_ARTICLE_DRAFT_BUTTON . '"/> ';
						echo '<input class="btn--primary" onclick="this.form.target=\'_self\';return true;" type="submit" name="publish" value="' . L_ARTICLE_PUBLISHING_BUTTON . '"/> ';
					} else {
						if(!isset($_GET['a']) OR preg_match('/^_[0-9]{4}$/',$_GET['a']))
							echo '<input class="btn--primary" onclick="this.form.target=\'_self\';return true;" type="submit" name="publish" value="' . L_ARTICLE_PUBLISHING_BUTTON . '"/> ';
						else
							echo '<input class="btn--primary" onclick="this.form.target=\'_self\';return true;" type="submit" name="update" value="' . L_ARTICLE_UPDATE_BUTTON . '"/> ';
							echo '<input class="btn--primary" onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="' . L_ARTICLE_OFFLINE_BUTTON . '"/> ';
					}
					if($artId!='0000')
						echo '<span>&nbsp;&nbsp;&nbsp;</span><input class="btn--warning" type="submit" name="delete" value="'.L_DELETE.'" onclick="Check=confirm(\''.L_ARTICLE_DELETE_CONFIRM.'\');if(Check==false) {return false;} else {this.form.target=\'_self\';return true;}" /> ';
				}
			?>
	</div>
</div>

<div class="">

	<?php eval($plxAdmin->plxPlugins->callHook('AdminArticleTop')) # Hook Plugins ?>

	<div class="grid-8-small-1">
		<div class="col-6-small-1">
			<div class="txtcenter">
				<fieldset>
					<div>
						<?php PlxUtils::printInput('artId',$artId,'hidden'); ?>
						<label for="id_title"><?= L_ARTICLE_TITLE ?>&nbsp;:</label>
						<?php PlxUtils::printInput('title',PlxUtils::strCheck($title),'text','42-255',false,'full-width'); ?>
					</div>
					<div>
						<input class="toggler" type="checkbox" id="toggler_chapo"<?= (empty($_GET['a']) || ! empty(trim($chapo))) ? ' unchecked' : ''; ?> />
						<label for="toggler_chapo"><?= L_HEADLINE_FIELD;?> : <span><?= L_ARTICLE_CHAPO_HIDE;?></span><span><?= L_ARTICLE_CHAPO_DISPLAY;?></span></label>
						<div>
							<?php PlxUtils::printArea('chapo',PlxUtils::strCheck($chapo),0,8); ?>
						</div>
					</div>
					<div>
						<label for="id_content"><?= L_CONTENT_FIELD ?>&nbsp;:</label>
						<?php PlxUtils::printArea('content',PlxUtils::strCheck($content),0,20); ?>
					</div>
				</fieldset>
				<?php eval($plxAdmin->plxPlugins->callHook('AdminArticleContent')) # Hook Plugins ?>
				<?= PlxToken::getTokenPostMethod() ?>
			</div>
		</div>

		<!-- SIDEBAR -->
		<div class="col-2-small-1 sidebar">
			<div>
				<span class="btn" v-on:click="tab=true">tab1</span>
				<span class="btn" v-on:click="tab=false">tab2</span>
			</div>
			<fieldset class="pan">
				<div v-if="tab" class="flex-container--column">
					<div>
						<label for="id_author"><?= L_ARTICLE_LIST_AUTHORS ?>&nbsp;:&nbsp;</label>
						<?php
							if($_SESSION['profil'] < PROFIL_WRITER)
								PlxUtils::printSelect('author', $_users, $author);
							else {
								echo '<input type="hidden" id="id_author" name="author" value="'.$author.'" />';
								echo '<strong>'.PlxUtils::strCheck($plxAdmin->aUsers[$author]['name']).'</strong>';
							}
						?>
					</div>
					<div class="flex-container--column">
						<div>
							<label><?= L_ARTICLE_DATE ?>&nbsp;:</label><br>
							<?php PlxUtils::printInput('date_publication_day',$date['day'],'text','2-2',false,'day'); ?>
							<?php PlxUtils::printInput('date_publication_month',$date['month'],'text','2-2',false,'month'); ?>
							<?php PlxUtils::printInput('date_publication_year',$date['year'],'text','2-4',false,'year'); ?>
							<?php PlxUtils::printInput('date_publication_time',$date['time'],'text','2-5',false,'time'); ?>
							<a class="ico_cal" href="javascript:void(0)" onclick="dateNow('date_publication', <?= date('Z') ?>); return false;" title="<?php L_NOW; ?>">
								<img src="theme/images/date.png" alt="calendar" />
							</a>
						</div>
						<div>
							<label><?= L_DATE_CREATION ?>&nbsp;:</label><br>
							<?php PlxUtils::printInput('date_creation_day',$date_creation['day'],'text','2-2',false,'day'); ?>
							<?php PlxUtils::printInput('date_creation_month',$date_creation['month'],'text','2-2',false,'month'); ?>
							<?php PlxUtils::printInput('date_creation_year',$date_creation['year'],'text','2-4',false,'year'); ?>
							<?php PlxUtils::printInput('date_creation_time',$date_creation['time'],'text','2-5',false,'time'); ?>
							<a class="ico_cal" href="javascript:void(0)" onclick="dateNow('date_creation', <?= date('Z') ?>); return false;" title="<?php L_NOW; ?>">
								<img src="theme/images/date.png" alt="calendar" />
							</a>
						</div>
						<div>
							<?php PlxUtils::printInput('date_update_old', $date_update_old, 'hidden'); ?>
							<label><?= L_DATE_UPDATE ?>&nbsp;:</label><br>
							<?php PlxUtils::printInput('date_update_day',$date_update['day'],'text','2-2',false,'day'); ?>
							<?php PlxUtils::printInput('date_update_month',$date_update['month'],'text','2-2',false,'month'); ?>
							<?php PlxUtils::printInput('date_update_year',$date_update['year'],'text','2-4',false,'year'); ?>
							<?php PlxUtils::printInput('date_update_time',$date_update['time'],'text','2-5',false,'time'); ?>
							<a class="ico_cal" href="javascript:void(0)" onclick="dateNow('date_update', <?= date('Z') ?>); return false;" title="<?php L_NOW; ?>">
								<img src="theme/images/date.png" alt="calendar" />
							</a>
						</div>
					</div>
					<div>
						<label for="id_template"><?= L_ARTICLE_TEMPLATE_FIELD ?>&nbsp;:</label>
						<?php PlxUtils::printSelect('template', $aTemplates, $template); ?>
					</div>
					<div class="expender">
						<span v-if="url" v-on:click="url=false">URL</span>
						<span v-if="!url" v-on:click="url=true">URL</span>
						<div v-if="url">
							<label for="id_url">
								<?= L_ARTICLE_URL_FIELD ?>&nbsp;:&nbsp;<a class="hint"><span><?= L_ARTICLE_URL_FIELD_TITLE ?></span></a>
							</label>
							<?php PlxUtils::printInput('url',$url,'text','27-255'); ?>
							<?php if($artId!='' AND $artId!='0000') : ?>
								<?php $link = $plxAdmin->urlRewrite('?article'.intval($artId).'/'.$url) ?>
								<p>
									<strong><?= L_LINK_FIELD ?>&nbsp;:</strong>
									<a onclick="this.target=\'_blank\';return true;" href="<?= $link ?>" title="<?= L_LINK_ACCESS ?> : <?= $link ?>"><?= $link ?></a>
								</p>
							<?php endif; ?>
						</div>
					</div>
					<div class="expender">
						<span v-if="category" v-on:click="category=false">Category</span>
						<span v-if="!category" v-on:click="category=true">Category</span>
						<div v-if="category">
							<label><?= L_ARTICLE_CATEGORIES ?>&nbsp;:</label>
							<?php
								$selected = (is_array($catId) AND in_array('000', $catId)) ? ' checked="checked"' : '';
								echo '<label for="cat_unclassified"><input class="no-margin" disabled="disabled" type="checkbox" id="cat_unclassified" name="catId[]"'.$selected.' value="000" />&nbsp;'. L_UNCLASSIFIED .'</label>';
								$selected = (is_array($catId) AND in_array('home', $catId)) ? ' checked="checked"' : '';
								echo '<label for="cat_home"><input type="checkbox" class="no-margin" id="cat_home" name="catId[]"'.$selected.' value="home" />&nbsp;'. L_CATEGORY_HOME_PAGE .'</label>';
								foreach($plxAdmin->aCats as $cat_id => $cat_name) {
									$selected = (is_array($catId) AND in_array($cat_id, $catId)) ? ' checked="checked"' : '';
									if($plxAdmin->aCats[$cat_id]['active'])
										echo '<label for="cat_'.$cat_id.'">'.'<input type="checkbox" class="no-margin" id="cat_'.$cat_id.'" name="catId[]"'.$selected.' value="'.$cat_id.'" />&nbsp;'.PlxUtils::strCheck($cat_name['name']).'</label>';
									else
										echo '<label for="cat_'.$cat_id.'">'.'<input type="checkbox" class="no-margin" id="cat_'.$cat_id.'" name="catId[]"'.$selected.' value="'.$cat_id.'" />&nbsp;'.PlxUtils::strCheck($cat_name['name']).'</label>';
								}
							?>
							<?php if($_SESSION['profil'] < PROFIL_WRITER) : ?>
								<label for="id_new_catname"><?= L_NEW_CATEGORY ?>&nbsp;:</label>
								<?php PlxUtils::printInput('new_catname','','text','17-50') ?>
								<input type="submit" name="new_category" value="<?= L_CATEGORY_ADD_BUTTON ?>" />
							<?php endif; ?>
						</div>
					</div>
					<div class="expender">
						<span v-if="tags" v-on:click="tags=false">Tags</span>
						<span v-if="!tags" v-on:click="tags=true">Tags</span>
						<div v-if="tags">
							<label for="tags"><?= L_ARTICLE_TAGS_FIELD; ?>&nbsp;:&nbsp;<a class="hint"><span><?= L_ARTICLE_TAGS_FIELD_TITLE; ?></span></a></label>
							<?php PlxUtils::printInput('tags',$tags,'text','25-255',false,false); ?>
							<input class="toggler" type="checkbox" id="toggler_tags"<?= (empty($_GET['a']) || ! empty(trim($tags))) ? ' unchecked' : ''; ?> />
							<label for="toggler_tags"><span>-</span><span>+</span></label>
							<div style="margin-top: 1rem">
								<?php
								if($plxAdmin->aTags) {
									$array=array();
									foreach($plxAdmin->aTags as $tag) {
										if($tags = array_map('trim', explode(',', $tag['tags']))) {
											foreach($tags as $tag) {
												if($tag!='') {
													$t = PlxUtils::urlify($tag);
													if(!isset($array[$tag]))
														$array[$tag]=array('url'=>$t,'count'=>1);
													else
														$array[$tag]['count']++;
												}
											}
										}
									}
									array_multisort($array);
									foreach($array as $tagname => $tag) {
										echo '<a href="javascript:void(0)" onclick="insTag(\'tags\',\''.addslashes($tagname).'\')" title="'.PlxUtils::strCheck($tagname).' ('.$tag['count'].')">'.
										str_replace(' ', '&nbsp;', PlxUtils::strCheck($tagname)).'</a>&nbsp;('.$tag['count'].')&nbsp; ';
									}
								}
								else echo L_NO_TAG;
								?>
							</div>
						</div>
					</div>
					<div class="expender">
						<span v-if="comments" v-on:click="comments=false">Comments</span>
						<span v-if="!comments" v-on:click="comments=true">Comments</span>
						<div v-if="comments">
							<?php if($plxAdmin->aConf['allow_com']=='1') : ?>
							<label for="id_allow_com"><?= L_ALLOW_COMMENTS ?>&nbsp;:</label>
							<?php PlxUtils::printSelect('allow_com',array('1'=>L_YES,'0'=>L_NO),$allow_com); ?>
							<?php else: ?>
							<?php PlxUtils::printInput('allow_com','0','hidden'); ?>
							<?php endif; ?>
							<?php if($artId != '0000') : ?>
								<ul class="unstyled">
									<li>
										<a href="comments.php?a=<?= $artId ?>&amp;page=1" title="<?= L_ARTICLE_MANAGE_COMMENTS_TITLE ?>"><?= L_ARTICLE_MANAGE_COMMENTS ?></a>
										<?php
											// récupération du nombre de commentaires
											$nbComsToValidate = $plxAdmin->getNbCommentaires('/^_'.$artId.'.(.*).xml$/','all');
											$nbComsValidated = $plxAdmin->getNbCommentaires('/^'.$artId.'.(.*).xml$/','all');
										?>
										<ul>
											<li><?= L_COMMENT_OFFLINE ?> : <a title="<?= L_NEW_COMMENTS_TITLE ?>" href="comments.php?sel=offline&amp;a=<?= $artId ?>&amp;page=1"><?= $nbComsToValidate ?></a></li>
											<li><?= L_COMMENT_ONLINE ?> : <a title="<?= L_VALIDATED_COMMENTS_TITLE ?>" href="comments.php?sel=online&amp;a=<?= $artId ?>&amp;page=1"><?= $nbComsValidated ?></a></li>
										</ul>
									</li>
									<li><a href="comment_new.php?a=<?= $artId ?>" title="<?= L_ARTICLE_NEW_COMMENT_TITLE ?>"><?= L_ARTICLE_NEW_COMMENT ?></a></li>
								</ul>
							<?php endif; ?>
						</div>
					</div>
					<div class="expender">
						<span v-if="seo" v-on:click="seo=false">SEO</span>
						<span v-if="!seo" v-on:click="seo=true">SEO</span>
						<div v-if="seo">
							<label for="id_title_htmltag"><?= L_ARTICLE_TITLE_HTMLTAG ?>&nbsp;:</label><br>
							<?php PlxUtils::printInput('title_htmltag',PlxUtils::strCheck($title_htmltag),'text','27-255'); ?>
							<label for="id_meta_description"><?= L_ARTICLE_META_DESCRIPTION ?>&nbsp;:</label><br>
							<?php PlxUtils::printInput('meta_description',PlxUtils::strCheck($meta_description),'text','27-255'); ?>
							<label for="id_meta_keywords"><?= L_ARTICLE_META_KEYWORDS ?>&nbsp;:</label><br>
							<?php //TODO is this still used by Google ? (P3ter)
								PlxUtils::printInput('meta_keywords',PlxUtils::strCheck($meta_keywords),'text','27-255'); 
							?>
						</div>
					</div>
					<?php eval($plxAdmin->plxPlugins->callHook('AdminArticleSidebar')) # Hook Plugins ?>
				</div>
				
				<div v-else class="flex-container--column">
					<label for="id_thumbnail">
						<?= L_THUMBNAIL ?>&nbsp;:&nbsp;
						<a title="<?= L_THUMBNAIL_SELECTION ?>" id="toggler_thumbnail" href="javascript:void(0)" onclick="mediasManager.openPopup('id_thumbnail', true)" style="outline:none; text-decoration: none">+</a>
					</label><br>
					<?php PlxUtils::printInput('thumbnail',PlxUtils::strCheck($thumbnail),'text','255',false,'full-width','','onkeyup="refreshImg(this.value)"'); ?>
					<label for="id_thumbnail_alt"><?= L_THUMBNAIL_TITLE ?>&nbsp;:</label><br>
					<?php PlxUtils::printInput('thumbnail_title',PlxUtils::strCheck($thumbnail_title),'text','255-255',false,'full-width'); ?>
					<label for="id_thumbnail_alt"><?= L_THUMBNAIL_ALT ?>&nbsp;:</label><br>
					<?php PlxUtils::printInput('thumbnail_alt',PlxUtils::strCheck($thumbnail_alt),'text','255-255',false,'full-width'); ?>
					<?php
						$src = false;
						if(preg_match('@^(?:https?|data):@', $thumbnail)) {
							$src = $thumbnail;
						} else {
							$src = PLX_ROOT.$thumbnail;
							$src = is_file($src) ? $src : false;
						}
						if($src) echo "<img src=\"$src\" title=\"$thumbnail\" />\n";
					?>
				</div>
			</fieldset>
		</div>
	</div>
</div>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminArticleFoot'));
# On inclut le footer
include __DIR__ .'/tags/foot.php';
?>
