<?php

/**
 * Edition d'un article
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include_once __DIR__ .'/prepend.php';

# Control du token du formulaire
if(!isset($_POST['preview']))
	plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminArticlePrepend'));

# validation de l'id de l'article si passé en parametre
if(isset($_GET['a']) AND !preg_match('/^_?[0-9]{4}$/',$_GET['a'])) {
	plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
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
	else $_POST['catId'] = array_filter($_POST['catId'], create_function('$a', 'return $a!="000";'));
	# Si demande de publication ou demande de validation, on supprime la catégorie draft si elle existe
	if((isset($_POST['update']) OR isset($_POST['publish']) OR isset($_POST['moderate'])) AND isset($_POST['catId'])) $_POST['catId'] = array_filter($_POST['catId'], create_function('$a', 'return $a!="draft";'));
	# Si profil PROFIL_WRITER on vérifie l'id du rédacteur connecté et celui de l'article
	if($_SESSION['profil']==PROFIL_WRITER AND isset($_POST['author']) AND $_SESSION['user']!=$_POST['author']) $_POST['author']=$_SESSION['user'];
	# Si profil PROFIL_WRITER on vérifie que l'article n'est pas celui d'un autre utilisateur
	if($_SESSION['profil']==PROFIL_WRITER AND isset($_POST['artId']) AND $_POST['artId']!='0000') {
		# On valide l'article
		if(($aFile = $plxAdmin->plxGlob_arts->query('/^'.$_POST['artId'].'.([home[draft|0-9,]*).'.$_SESSION['user'].'.(.+).xml$/')) == false) { # Article inexistant
			plxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
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
		$art['content'] =  trim($_POST['content']);
		$art['tags'] = trim($_POST['tags']);
		$art['meta_description'] = $_POST['meta_description'];
		$art['meta_keywords'] =  $_POST['meta_keywords'];
		$art['title_htmltag'] =  $_POST['title_htmltag'];
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
		if(trim($_POST['url']) == '')
			$art['url'] = plxUtils::title2url($_POST['title']);
		else
			$art['url'] = plxUtils::title2url($_POST['url']);
		if($art['url'] == '') $art['url'] = L_DEFAULT_NEW_ARTICLE_URL;

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
		header('Location: articles.php');
		exit;
	}
	# Mode création ou maj
	if(isset($_POST['update']) OR isset($_POST['publish']) OR isset($_POST['moderate']) OR isset($_POST['draft'])) {

		$valid = true;
		# Vérification de l'unicité de l'url
		$_POST['url'] = plxUtils::title2url(trim($_POST['url'])==''?$_POST['title']:$_POST['url']);
		foreach($plxAdmin->plxGlob_arts->aFiles as $numart => $filename) {
			if(preg_match("/^_?[0-9]{4}.([0-9,|home|draft]*).[0-9]{3}.[0-9]{12}.".$_POST["url"].".xml$/", $filename)) {
				if($numart!=str_replace('_', '',$_POST['artId'])) {
					$valid = plxMsg::Error(L_ERR_URL_ALREADY_EXISTS." : ".plxUtils::strCheck($_POST["url"])) AND $valid;
				}
			}
		}
		# Vérification de la validité de la date de publication
		if(!plxDate::checkDate($_POST['date_publication_day'],$_POST['date_publication_month'],$_POST['date_publication_year'],$_POST['date_publication_time'])) {
			$valid = plxMsg::Error(L_ERR_INVALID_PUBLISHING_DATE) AND $valid;
		}
		# Vérification de la validité de la date de creation
		if(!plxDate::checkDate($_POST['date_creation_day'],$_POST['date_creation_month'],$_POST['date_creation_year'],$_POST['date_creation_time'])) {
			$valid = plxMsg::Error(L_ERR_INVALID_DATE_CREATION) AND $valid;
		}
		# Vérification de la validité de la date de mise à jour
		if(!plxDate::checkDate($_POST['date_update_day'],$_POST['date_update_month'],$_POST['date_update_year'],$_POST['date_update_time'])) {
			$valid = plxMsg::Error(L_ERR_INVALID_DATE_UPDATE) AND $valid;
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
	$content =  trim($_POST['content']);
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
		plxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
		header('Location: articles.php');
		exit;
	}
	# On parse et alimente nos variables
	$result = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$aFile['0']);
	$title = trim($result['title']);
	$chapo = trim($result['chapo']);
	$content =  trim($result['content']);
	$tags =  trim($result['tags']);
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
	$meta_description=$result['meta_description'];
	$meta_keywords=$result['meta_keywords'];
	$title_htmltag = $result['title_htmltag'];
	$thumbnail = $result['thumbnail'];
	$thumbnail_title = $result['thumbnail_title'];
	$thumbnail_alt = $result['thumbnail_alt'];

	if($author!=$_SESSION['user'] AND $_SESSION['profil']==PROFIL_WRITER) {
		plxMsg::Error(L_ERR_FORBIDDEN_ARTICLE);
		header('Location: articles.php');
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

# On construit la liste des utilisateurs
foreach($plxAdmin->aUsers as $_userid => $_user) {
	if($_user['active'] AND !$_user['delete'] ) {
		if($_user['profil']==PROFIL_ADMIN)
			$_users[L_PROFIL_ADMIN][$_userid] = plxUtils::strCheck($_user['name']);
		elseif($_user['profil']==PROFIL_MANAGER)
			$_users[L_PROFIL_MANAGER][$_userid] = plxUtils::strCheck($_user['name']);
		elseif($_user['profil']==PROFIL_MODERATOR)
			$_users[L_PROFIL_MODERATOR][$_userid] = plxUtils::strCheck($_user['name']);
		elseif($_user['profil']==PROFIL_EDITOR)
			$_users[L_PROFIL_EDITOR][$_userid] = plxUtils::strCheck($_user['name']);
		else
			$_users[L_PROFIL_WRITER][$_userid] = plxUtils::strCheck($_user['name']);
	}
}

# On récupère les templates des articles
$aTemplates = array();
$files = plxGlob::getInstance(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$plxAdmin->aConf['style']);
if ($array = $files->query('/^article(-[a-z0-9-_]+)?.php$/')) {
	foreach($array as $k=>$v)
		$aTemplates[$v] = $v;
}
if(empty($aTemplates)) $aTemplates[''] = L_NONE1;
$cat_id='000';

# Call the views (mainView must be the last to be called, because it's include the masterTemplate)
include_once __DIR__ .'/views/articleView.php';
include_once __DIR__ .'/views/mainView.php';