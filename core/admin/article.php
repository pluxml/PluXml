<?php

/**
 * Edition d'un article
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
if(!isset($_POST['preview']))
	plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminArticlePrepend'));

# validation de l'id de l'article si passé en parametre
if(isset($_GET['a']) AND !preg_match('/^_?[0-9]{4}$/',$_GET['a'])) {
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
		$art['content'] =  trim($_POST['content']);
		$art['tags'] = trim($_POST['tags']);
		$art['meta_description'] = $_POST['meta_description'];
		$art['meta_keywords'] =  $_POST['meta_keywords'];
		$art['title_htmltag'] =  $_POST['title_htmltag'];
		$art['filename'] = '';
		$art['numero'] = $_POST['artId'];
		$art['author'] = $_POST['author'];
		$art['categorie'] = '';
		if(!empty($_POST['catId'])) {
			$array=array();
			foreach($_POST['catId'] as $k => $v) {
				if($v!='draft') $array[]=$v;
			}
			$art['categorie']=implode(',',$array);
		}
		$art['date'] = $_POST['year'].$_POST['month'].$_POST['day'].substr(str_replace(':','',$_POST['time']),0,4);
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
		header('Location: index.php');
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
		if(!plxDate::checkDate($_POST['day'],$_POST['month'],$_POST['year'],$_POST['time'])) {
			$valid = plxMsg::Error(L_ERR_INVALID_PUBLISHING_DATE) AND $valid;
		}
		if($valid) {
			$plxAdmin->editArticle($_POST,$_POST['artId']);
			header('Location: article.php?a='.$_POST['artId']);
			exit;
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
	$date['day'] = $_POST['day'];
	$date['month'] = $_POST['month'];
	$date['year'] = $_POST['year'];
	$date['time'] = $_POST['time'];
	$chapo = trim($_POST['chapo']);
	$content =  trim($_POST['content']);
	$tags = trim($_POST['tags']);
	$url = $_POST['url'];
	$allow_com = $_POST['allow_com'];
	$template = $_POST['template'];
	$meta_description = $_POST['meta_description'];
	$meta_keywords = $_POST['meta_keywords'];
	$title_htmltag = $_POST['title_htmltag'];
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminArticlePostData'));
} elseif(!empty($_GET['a'])) { # On n'a rien validé, c'est pour l'édition d'un article
	# On va rechercher notre article
	if(($aFile = $plxAdmin->plxGlob_arts->query('/^'.$_GET['a'].'.(.+).xml$/')) == false) { # Article inexistant
		plxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
		header('Location: index.php');
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
	$catId = explode(',', $result['categorie']);
	$artId = $result['numero'];
	$allow_com = $result['allow_com'];
	$template = $result['template'];
	$meta_description=$result['meta_description'];
	$meta_keywords=$result['meta_keywords'];
	$title_htmltag = $result['title_htmltag'];

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
	$date = array ('year' => date('Y'),'month' => date('m'),'day' => date('d'),'time' => date('H:i'));
	$catId = array('draft');
	$artId = '0000';
	$allow_com = $plxAdmin->aConf['allow_com'];
	$template = 'article.php';
	$meta_description=$meta_keywords=$title_htmltag='';
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminArticleInitData'));
}

# On inclut le header
include(dirname(__FILE__).'/top.php');

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
$files = plxGlob::getInstance(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$plxAdmin->aConf['style']);
if ($array = $files->query('/^article(-[a-z0-9-_]+)?.php$/')) {
	foreach($array as $k=>$v)
		$aTemplates[$v] = $v;
}
$cat_id='000';
?>

<form action="article.php" method="post" id="form_article">

	<div id="extra-container">

		<div id="extra-sidebar">
			<p class="field_head"><?php echo L_ARTICLE_STATUS ?>&nbsp;:&nbsp;
				<strong>
				<?php
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
			<fieldset>
				<p><label for="id_author"><?php echo L_ARTICLE_LIST_AUTHORS ?>&nbsp;:&nbsp;</label></p>
				<?php
				if($_SESSION['profil'] < PROFIL_WRITER)
					plxUtils::printSelect('author', $_users, $author);
				else {
					echo '<input type="hidden" id="id_author" name="author" value="'.$author.'" />';
					echo '<strong>'.plxUtils::strCheck($plxAdmin->aUsers[$author]['name']).'</strong>';
				}
				?>
				<p><label><?php echo L_ARTICLE_DATE ?>&nbsp;:</label></p>
				<?php plxUtils::printInput('day',$date['day'],'text','2-2',false,'fld1'); ?>
				<?php plxUtils::printInput('month',$date['month'],'text','2-2',false,'fld1'); ?>
				<?php plxUtils::printInput('year',$date['year'],'text','2-4',false,'fld2'); ?>
				<?php plxUtils::printInput('time',$date['time'],'text','2-5',false,'fld2'); ?>
				<a id="id_cal" href="javascript:void(0)" onclick="dateNow(<?php echo date('Z') ?>); return false;" title="<?php L_NOW; ?>"><img src="theme/images/date.png" alt="" /></a>

				<p><label><?php echo L_ARTICLE_CATEGORIES ?>&nbsp;:</label></p>
				<?php
					$selected = (is_array($catId) AND in_array('000', $catId)) ? ' checked="checked"' : '';
					echo '<input readonly="readonly" disabled="disabled" type="checkbox" id="cat_unclassified" name="catId[]"'.$selected.' value="000" /><label for="cat_unclassified">&nbsp;'. L_UNCLASSIFIED .'</label><br />';
					$selected = (is_array($catId) AND in_array('home', $catId)) ? ' checked="checked"' : '';
					echo '<input type="checkbox" id="cat_home" name="catId[]"'.$selected.' value="home" /><label for="cat_home">&nbsp;'. L_CATEGORY_HOME_PAGE .'</label><br />';
					foreach($plxAdmin->aCats as $cat_id => $cat_name) {
						$selected = (is_array($catId) AND in_array($cat_id, $catId)) ? ' checked="checked"' : '';
						echo '<input type="checkbox" id="cat_'.$cat_id.'" name="catId[]"'.$selected.' value="'.$cat_id.'" />';
						if($plxAdmin->aCats[$cat_id]['active'])
							echo '<label for="cat_'.$cat_id.'">&nbsp;'.plxUtils::strCheck($cat_name['name']).'</label><br />';
						else
							echo '<label for="cat_'.$cat_id.'">&nbsp;<em>'.plxUtils::strCheck($cat_name['name']).'</em></label><br />';
					}
				?>

				<?php if($_SESSION['profil'] < PROFIL_WRITER) : ?>
				<p><label for="id_new_catname"><?php echo L_NEW_CATEGORY ?>&nbsp;:</label></p>
				<?php plxUtils::printInput('new_catname','','text','17-50')	?>
				<input class="button new" type="submit" name="new_category" value="<?php echo L_CATEGORY_ADD_BUTTON ?>" />
				<?php endif; ?>

				<p><label for="id_tags"><?php echo L_ARTICLE_TAGS_FIELD ?>&nbsp;:</label>&nbsp;<a class="help" title="<?php echo L_ARTICLE_TAGS_FIELD_TITLE ?>">&nbsp;</a></p>
				<?php plxUtils::printInput('tags',$tags,'text','25-255'); ?>
				<a title="<?php echo L_ARTICLE_TOGGLER_TITLE ?>" id="toggler" href="javascript:void(0)" onclick="toggleDiv('tags','toggler','+','-')" style="outline:none">+</a>
				<div id="tags" style="display:none;margin-top:5px">
				<?php
				if($plxAdmin->aTags) {
					$array=array();
					foreach($plxAdmin->aTags as $tag) {
						if($tags = array_map('trim', explode(',', $tag['tags']))) {
							foreach($tags as $tag) {
								if($tag!='') {
									$t = plxUtils::title2url($tag);
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
						echo '<a href="javascript:void(0)" onclick="insTag(\'tags\',\''.$tagname.'\')" title="'.plxUtils::strCheck($tagname).' ('.$tag['count'].')">'.plxUtils::strCheck($tagname).'</a> ('.$tag['count'].') ';
					}
				}
				else echo L_NO_TAG;
				?>
				</div>

				<?php if($plxAdmin->aConf['allow_com']=='1') : ?>
				<p><label for="id_allow_com"><?php echo L_ALLOW_COMMENTS ?>&nbsp;:</label></p>
				<?php plxUtils::printSelect('allow_com',array('1'=>L_YES,'0'=>L_NO),$allow_com); ?>
				<?php else: ?>
					<?php plxUtils::printInput('allow_com','0','hidden'); ?>
				<?php endif; ?>

				<p><label for="id_url"><?php echo L_ARTICLE_URL_FIELD ?>&nbsp;:</label>&nbsp;<a class="help" title="<?php echo L_ARTICLE_URL_FIELD_TITLE ?>">&nbsp;</a></p>
				<?php plxUtils::printInput('url',$url,'text','27-255'); ?>

				<p><label for="id_template"><?php echo L_ARTICLE_TEMPLATE_FIELD ?>&nbsp;:</label></p>
				<?php plxUtils::printSelect('template', $aTemplates, $template); ?>

				<p><label for="id_title_htmltag"><?php echo L_ARTICLE_TITLE_HTMLTAG ?>&nbsp;:</label></p>
				<?php plxUtils::printInput('title_htmltag',plxUtils::strCheck($title_htmltag),'text','27-255'); ?>

				<p><label for="id_meta_description"><?php echo L_ARTICLE_META_DESCRIPTION ?>&nbsp;:</label></p>
				<?php plxUtils::printInput('meta_description',plxUtils::strCheck($meta_description),'text','27-255'); ?>

				<p><label for="id_meta_keywords"><?php echo L_ARTICLE_META_KEYWORDS ?>&nbsp;:</label></p>
				<?php plxUtils::printInput('meta_keywords',plxUtils::strCheck($meta_keywords),'text','27-255'); ?>

				<?php eval($plxAdmin->plxPlugins->callHook('AdminArticleSidebar')) # Hook Plugins ?>

				<?php if($artId != '0000') : ?>
				<ul class="opts">
					<li>&nbsp;<a href="comments.php?a=<?php echo $artId ?>&amp;page=1" title="<?php echo L_ARTICLE_MANAGE_COMMENTS_TITLE ?>"><?php echo L_ARTICLE_MANAGE_COMMENTS ?></a></li>
					<li>&nbsp;<a href="comment_new.php?a=<?php echo $artId ?>" title="<?php echo L_ARTICLE_NEW_COMMENT_TITLE ?>"><?php echo L_ARTICLE_NEW_COMMENT ?></a></li>
				</ul>
				<?php endif; ?>

			</fieldset>

		</div><!-- extra sidebar -->

		<div id="extra-content">

			<p class="back"><a href="index.php"><?php echo L_BACK_TO_ARTICLES ?></a></p>

			<h2><?php echo (empty($_GET['a']))?L_MENU_NEW_ARTICLES:L_ARTICLE_EDITING; ?></h2>

			<?php eval($plxAdmin->plxPlugins->callHook('AdminArticleTop')) # Hook Plugins ?>

			<div class="form_content">
				<fieldset>
					<?php plxUtils::printInput('artId',$artId,'hidden'); ?>
					<p><label for="id_title"><?php echo L_ARTICLE_TITLE ?>&nbsp;:</label></p>
					<?php plxUtils::printInput('title',plxUtils::strCheck($title),'text','42-255'); ?>
					<p id="p_chapo">
						<label for="id_chapo"><?php echo L_HEADLINE_FIELD ?>&nbsp;:</label>
						&nbsp;
						<a id="toggler_chapo" href="javascript:void(0)" onclick="toggleDiv('toggle_chapo', 'toggler_chapo', '<?php echo L_ARTICLE_CHAPO_DISPLAY ?>','<?php echo L_ARTICLE_CHAPO_HIDE ?>')"><?php echo $chapo==''?L_ARTICLE_CHAPO_DISPLAY:L_ARTICLE_CHAPO_HIDE ?></a>
					</p>
					<div id="toggle_chapo"<?php echo $chapo!=''?'':' style="display:none"' ?>><?php plxUtils::printArea('chapo',plxUtils::strCheck($chapo),35,8); ?></div>
					<p id="p_content"><label for="id_content"><?php echo L_CONTENT_FIELD ?>&nbsp;:</label></p>
					<?php plxUtils::printArea('content',plxUtils::strCheck($content),35,28); ?>
					<?php if($artId!='' AND $artId!='0000') : ?>
						<?php $link = $plxAdmin->urlRewrite('index.php?article'.intval($artId).'/'.$url) ?>
						<p id="p_link"><label for="id_link"><?php echo L_LINK_FIELD ?>&nbsp;:&nbsp;</label>
							<?php echo '<a onclick="this.target=\'_blank\';return true;" href="'.$link.'" title="'.L_LINK_ACCESS.'">'.L_LINK_VIEW.'</a>'; ?>
						</p>
						<?php echo '<input id="id_link" onclick="this.select()" class="readonly" readonly="readonly" type="text" value="'.$link.'" />' ?>
					<?php endif; ?>
				</fieldset>
				<?php eval($plxAdmin->plxPlugins->callHook('AdminArticleContent')) ?>
			</div>

			<div class="form_bottom">
				<p class="center">
					<?php echo plxToken::getTokenPostMethod() ?>
					<input class="button preview" type="submit" name="preview" onclick="this.form.target='_blank';return true;" value="<?php echo L_ARTICLE_PREVIEW_BUTTON ?>"/>
					<?php
						if($_SESSION['profil']>PROFIL_MODERATOR AND $plxAdmin->aConf['mod_art']) {
							if(in_array('draft', $catId)) { # brouillon
								if($artId!='0000') # nouvel article
									echo '<input class="button delete" type="submit" name="delete" value="'.L_DELETE.'" onclick="Check=confirm(\''.L_ARTICLE_DELETE_CONFIRM.'\');if(Check==false) {return false;} else {this.form.target=\'_self\';return true;}" />';
								echo '<input class="button" onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="'.L_ARTICLE_DRAFT_BUTTON.'"/>';
								echo '<input class="button submit" onclick="this.form.target=\'_self\';return true;" type="submit" name="moderate" value="'.L_ARTICLE_MODERATE_BUTTON.'"/>';
							} else {
								if(isset($_GET['a']) AND preg_match('/^_[0-9]{4}$/',$_GET['a'])) { # en attente
									echo '<input class="button delete" type="submit" name="delete" value="'.L_DELETE.'" onclick="Check=confirm(\''.L_ARTICLE_DELETE_CONFIRM.'\');if(Check==false) {return false;} else {this.form.target=\'_self\';return true;}" />';
									echo '<input class="button" onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="'.L_ARTICLE_DRAFT_BUTTON.'"/>';
									echo '<input class="button update" onclick="this.form.target=\'_self\';return true;" type="submit" name="update" value="' . L_ARTICLE_UPDATE_BUTTON . '"/>';
								} else {
									echo '<input class="button" onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="'.L_ARTICLE_DRAFT_BUTTON.'"/>';
									echo '<input class="button submit" onclick="this.form.target=\'_self\';return true;" type="submit" name="moderate" value="'.L_ARTICLE_MODERATE_BUTTON.'"/>';
								}
							}
						} else {
							if($artId!='0000')
								echo '<input class="button delete" type="submit" name="delete" value="'.L_DELETE.'" onclick="Check=confirm(\''.L_ARTICLE_DELETE_CONFIRM.'\');if(Check==false) {return false;} else {this.form.target=\'_self\';return true;}" />';
							if(in_array('draft', $catId)) {
								echo '<input class="button" onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="' . L_ARTICLE_DRAFT_BUTTON . '"/>';
								echo '<input class="button submit" onclick="this.form.target=\'_self\';return true;" type="submit" name="publish" value="' . L_ARTICLE_PUBLISHING_BUTTON . '"/>';
							} else {
								if(!isset($_GET['a']) OR preg_match('/^_[0-9]{4}$/',$_GET['a']))
									echo '<input class="button submit" onclick="this.form.target=\'_self\';return true;" type="submit" name="publish" value="' . L_ARTICLE_PUBLISHING_BUTTON . '"/>';
								else
									echo '<input class="button" onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="' . L_ARTICLE_OFFLINE_BUTTON . '"/>';
								echo '<input class="button update" onclick="this.form.target=\'_self\';return true;" type="submit" name="update" value="' . L_ARTICLE_UPDATE_BUTTON . '"/>';
							}
						}
					?>
				</p>
			</div>

		</div><!-- extra-content -->

	</div><!-- extra container -->

</form>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminArticleFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>
