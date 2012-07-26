<?php

/**
 * Création d'un commentaire
 *
 * @package PLX
 * @author	Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentNewPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR);

# Interdire de l'accès à la page si les commentaires sont désactivés
if(!$plxAdmin->aConf['allow_com']) {
    header('Location: index.php');
    exit;
}

# validation de l'id de l'article si passé en parametre
if(isset($_GET['a']) AND !preg_match('/^_?[0-9]{4}$/',$_GET['a'])) {
	plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
	header('Location: index.php');
	exit;
}

# On va checker le mode (répondre ou écrire)
if(!empty($_GET['c'])) { # Mode "answer"
	# On check que le commentaire existe et est "online"
//	if(!$plxAdmin->getCommentaires('/^'.plxUtils::nullbyteRemove($_GET['c']).'.xml$/','')) { # Commentaire inexistant
	if(!$plxAdmin->getCommentaires('/^'.plxUtils::nullbyteRemove($_GET['c']).'.xml$/','',0,1,'all')) {
		# On redirige
		plxMsg::Error(L_ERR_ANSWER_UNKNOWN_COMMENT);
		header('Location: comments.php'.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
		exit;
	}
	# Commentaire offline
	if(preg_match('/^_/',$_GET['c'])) {
		# On redirige
		plxMsg::Error(L_ERR_ANSWER_OFFLINE_COMMENT);
		header('Location: comments.php'.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
		exit;
	}
	# On va rechercher notre article
	if(($aFile = $plxAdmin->plxGlob_arts->query('/^'.$plxAdmin->plxRecord_coms->f('article').'.(.+).xml$/','','sort',0,1)) == false) { # Article inexistant
		plxMsg::Error(L_ERR_COMMENT_UNKNOWN_ARTICLE);
		header('Location: index.php');
		exit;
	}
	# Variables de traitement
	$artId = $plxAdmin->plxRecord_coms->f('article');
	if(!empty($_GET['a'])) $get = 'c='.$_GET['c'].'&amp;a='.$_GET['a'];
	else $get = 'c='.$_GET['c'];
	$aArt = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$aFile['0']);
	# Variable du formulaire
	$content = '<a href="#c'.$plxAdmin->plxRecord_coms->f('numero').'">@'.$plxAdmin->plxRecord_coms->f('author')."</a> :\n";
	$article = '<a href="article.php?a='.$aArt['numero'].'" title="'.L_COMMENT_ARTICLE_LINKED_TITLE.'">';
	$article .= plxUtils::strCheck($aArt['title']);
	$article .= '</a>';
	# Ok, on récupère les commentaires de l'article
	$plxAdmin->getCommentaires('/^'.str_replace('_','',$artId).'.(.*).xml$/','rsort');
} elseif(!empty($_GET['a'])) { # Mode "new"
	# On check l'article si il existe bien
	if(($aFile = $plxAdmin->plxGlob_arts->query('/^'.$_GET['a'].'.(.+).xml$/','','sort',0,1)) == false) {
		plxMsg::Error(L_ERR_COMMENT_UNEXISTENT_ARTICLE);
		header('Location: index.php');
		exit;
	}
	# Variables de traitement
	$artId = $_GET['a'];
	$get = 'a='.$_GET['a'];
	$aArt = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$aFile['0']);
	# Variable du formulaire
	$content = '';
	$article = '<a href="article.php?a='.$aArt['numero'].'" title="'.L_COMMENT_ARTICLE_LINKED_TITLE.'">';
	$article .= plxUtils::strCheck($aArt['title']);
	$article .= '</a>';
	# Ok, on récupère les commentaires de l'article
	$plxAdmin->getCommentaires('/^'.str_replace('_','',$artId).'.(.*).xml$/','rsort');
} else { # Mode inconnu
	header('Location: .index.php');
	exit;
}

# On a validé le formulaire
if(!empty($_POST) AND !empty($_POST['content'])) {
	# Création du commentaire
	if(!$plxAdmin->newCommentaire(str_replace('_','',$artId),$_POST['content'])) { # Erreur
		plxMsg::Error(L_ERR_CREATING_COMMENT);
	} else { # Ok
		plxMsg::Info(L_CREATING_COMMENT_SUCCESSFUL);
	}
	header('Location: comment_new.php?a='.$artId);
	exit;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<?php if(!empty($_GET['a'])) : ?>
	<p class="back"><a href="comments.php?a=<?php echo $_GET['a']; ?>"><?php echo L_BACK_TO_ARTICLE_COMMENTS ?></a></p>
<?php else : ?>
	<p class="back"><a href="comments.php"><?php echo L_BACK_TO_COMMENTS ?></a></p>
<?php endif; ?>

<h2><?php echo L_CREATE_NEW_COMMENT ?></h2>
<h3><?php echo L_COMMENTS_ARTICLE_SCOPE ?> &laquo;<?php echo plxUtils::strCheck($aArt['title']); ?>&raquo;</h3>

<?php eval($plxAdmin->plxPlugins->callHook('AdminCommentNewTop')) # Hook Plugins ?>

<ul class="comment_infos">
	<li><?php echo L_COMMENT_AUTHOR_FIELD ?> : <strong><?php echo plxUtils::strCheck($plxAdmin->aUsers[$_SESSION['user']]['name']); ?></strong></li>
	<li><?php echo L_COMMENT_TYPE_FIELD ?> : <strong>admin</strong></li>
	<li><?php echo L_COMMENT_SITE_FIELD ?> : <?php echo '<a href="'.$plxAdmin->racine.'">'.$plxAdmin->racine.'</a>'; ?></li>
	<li><?php echo L_COMMENT_LINKED_ARTICLE_FIELD ?> : <?php echo $article; ?></li>
</ul>

<form action="comment_new.php?<?php echo plxUtils::strCheck($get) ?>" method="post" id="form_comment">
	<fieldset>
		<?php echo plxToken::getTokenPostMethod() ?>
		<p id="p_content"><label for="id_content"><?php echo L_USER_INFOS ?>&nbsp;:</label></p>
		<?php plxUtils::printArea('content',plxUtils::strCheck($content), 60, 7); ?>
		<?php eval($plxAdmin->plxPlugins->callHook('AdminCommentNew')) # Hook Plugins ?>
		<p class="center">
			<input class="button new" type="submit" name="create" value="<?php echo L_COMMENT_SAVE_BUTTON ?>"/>
		</p>
	</fieldset>
</form>

<?php if(isset($plxAdmin->plxRecord_coms)) : # On a des commentaires ?>
	<h2><?php echo L_ARTICLE_COMMENTS_LIST ?></h2>
	<div id="comments">
	<?php while($plxAdmin->plxRecord_coms->loop()) : # On boucle ?>
		<?php $comId = $plxAdmin->plxRecord_coms->f('article').'.'.$plxAdmin->plxRecord_coms->f('numero'); ?>
		<div class="comment<?php echo ((isset($_GET['c']) AND $_GET['c']==$comId?' current':'')) ?>" id="c<?php echo $plxAdmin->plxRecord_coms->f('numero'); ?>">
			<div class="info_comment">
				<p><?php echo L_COMMENT_WRITTEN_BY ?>&nbsp;<strong><?php echo $plxAdmin->plxRecord_coms->f('author'); ?></strong>
				@ <?php echo plxDate::formatDate($plxAdmin->plxRecord_coms->f('date'), '#day #num_day #month #num_year(4) &agrave; #hour:#minute'); ?>
				 - <a href="comment.php<?php echo (!empty($_GET['a']))?'?c='.$comId.'&amp;a='.$_GET['a']:'?c='.$comId; ?>" title="<?php echo L_COMMENT_EDIT_TITLE ?>"><?php echo L_COMMENT_EDIT ?></a>
				 - <a href="javascript:answerCom('content','<?php echo $plxAdmin->plxRecord_coms->f('numero'); ?>','<?php echo plxUtils::strCheck($plxAdmin->plxRecord_coms->f('author')) ?>');" title="<?php echo L_COMMENT_ANSWER_TITLE ?>"><?php echo L_COMMENT_ANSWER ?></a>
				</p>
			</div>
			<blockquote class="type-<?php echo $plxAdmin->plxRecord_coms->f('type') ?>"><p><?php echo nl2br($plxAdmin->plxRecord_coms->f('content')); ?></p></blockquote>
			<?php eval($plxAdmin->plxPlugins->callHook('AdminCommentNewList')) # Hook Plugins ?>
		</div>
	<?php endwhile; ?>
	</div>
<?php endif; ?>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentNewFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>
