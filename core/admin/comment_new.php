<?php

/**
 * Création d'un commentaire
 *
 * @package PLX
 * @author	Florent MONTHEL
 **/

include 'prepend.php';

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentNewPrepend'));

# Contrôle de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR);

# Interdire de l'accès à la page si les commentaires sont désactivés
if(!$plxAdmin->aConf['allow_com']) {
	header('Location: index.php');
	exit;
}

# validation de l'id de l'article si passé en paramètre avec $_GET['a']
if(isset($_GET['a'])) {
	if(!preg_match('/^_?(\d{4})$/', $_GET['a'], $capture)) {
		plxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
		header('Location: index.php');
		exit;
	}
} elseif(isset($_GET['c'])) {
	# Mode answer
	if(!preg_match('@^_?(\d{4})\.\d{10}-(\d+)$@', $_GET['c'], $capture)) {
		plxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
		header('Location: index.php');
		exit;
	}

	# Commentaire offline
	if(preg_match('/^_/', $_GET['c'])) {
		# On redirige
		plxMsg::Error(L_ERR_ANSWER_OFFLINE_COMMENT);
		header('Location: comments.php?a=' . $capture[1]);
		exit;
	}

	# On vérifie que le commentaire existe
	if(!$plxAdmin->getCommentaires('@^' . preg_quote(plxUtils::nullbyteRemove($_GET['c'])) . '\.xml$@', '', 0, 1, 'all')) {
		# On redirige
		plxMsg::Error(L_ERR_ANSWER_UNKNOWN_COMMENT);
		header('Location: comments.php?a=' . $capture[1]);
		exit;
	}
} else { # Mode inconnu
	header('Location: index.php');
	exit;
}

# On vérifie que l'article existe bien
$artId = $capture[1];
$aFiles = $plxAdmin->plxGlob_arts->query('@^' . $artId .'\.(.+)\.xml$@', '', 'sort', 0, 1);
if(empty($aFiles)) {
	plxMsg::Error(L_ERR_COMMENT_UNEXISTENT_ARTICLE);
	header('Location: index.php');
	exit;
}

# Contrôle du token du formulaire
plxToken::validateFormToken($_POST);

# On a validé le formulaire
if(!empty($_POST) AND !empty($_POST['content'])) {
	# Création du commentaire
	$content = array(
		'parent' => isset($capture[2]) ? $capture[2] : '',
		'content' => trim($_POST['content']),
	);
	if(!$plxAdmin->newCommentaire($artId, $content)) {
		# Erreur
		plxMsg::Error(L_ERR_CREATING_COMMENT);
	} else {
		# Succès
		plxMsg::Info(L_CREATING_COMMENT_SUCCESSFUL);
	}
	header('Location: comment_new.php?a='.$artId);
	exit;
}

# Variables de traitement
$aArt = $plxAdmin->parseArticle(PLX_ROOT . $plxAdmin->aConf['racine_articles'] . $aFiles['0']);
if(!is_array($aArt)) {
	plxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
	header('Location: index.php');
	exit;
}

# Variable du formulaire
$content = '';

# Ok, on récupère les commentaires de l'article
$plxAdmin->getCommentaires('@^(' . $artId . ')\.(\d{10,})\-(\d+)\.xml$@', 'sort');

# On inclut le header
include 'top.php';

ob_start();
?>
<form method="post" id="form_comment">
	<div class="inline-form action-bar">
		<h2><?= L_CREATE_NEW_COMMENT ?></h2>
		<?php if(!empty($_GET['a'])) : ?>
		<p><a class="back" href="comments.php?a=<?= $artId ?>"><?= L_BACK_TO_ARTICLE_COMMENTS ?></a></p>
		<?php else : ?>
		<p><a class="back" href="comments.php"><?= L_BACK_TO_COMMENTS ?></a></p>
		<?php endif; ?>
		<input type="submit" name="create" value="<?= L_COMMENT_SAVE_BUTTON ?>"/>
	</div>
	<fieldset>
		<div class="grid">
			<div class="col sml-12">
				<div id="id_answer">
					<?= L_REPLY_TO ?> :
				</div>
				<?= plxToken::getTokenPostMethod() ?>
				<?php plxUtils::printArea('content', plxUtils::strCheck($content), 60, 7, false, 'full-width', 'placeholder="' . L_COMMENT_ADMIN_ARTICLE_FIELD . '" autofocus'); ?>
				<?php eval($plxAdmin->plxPlugins->callHook('AdminCommentNew')); # Hook Plugins ?>
			</div>
		</div>
	</fieldset>
</form>
<?php
$theForm = ob_get_clean();
?>
<div>
<?php eval($plxAdmin->plxPlugins->callHook('AdminCommentNewTop')); # Hook Plugins ?>
	<h3 class="no-margin">
		<?= L_COMMENT_LINKED_ARTICLE_FIELD ?>&nbsp;:&nbsp;<a href="article.php?a=<?= $aArt['numero'] ?>" title="<?= L_COMMENT_ARTICLE_LINKED_TITLE ?>"><?= plxUtils::strCheck($aArt['title']) ?></a>
	</h3>
	<ul class="unstyled-list grid">
		<li class="col med-6"><?= L_COMMENT_AUTHOR_FIELD ?> : <strong><?= plxUtils::strCheck($plxAdmin->aUsers[$_SESSION['user']]['name']); ?></strong></li>
		<li class="col med-6 text-right"><?= L_COMMENT_TYPE_FIELD ?> : <strong>admin</strong></li>
	</ul>
</div>
<?php
if(!isset($_GET['c'])) {
	# Ajout nouveau commentaire
	echo $theForm;
}
?>
<?php if(isset($plxAdmin->plxRecord_coms)) : # On a des commentaires ?>
	<h3><?= L_ARTICLE_COMMENTS_LIST ?></h3>
	<div id="comments-list">
	<?php while($plxAdmin->plxRecord_coms->loop()) : # On boucle ?>
		<?php $comId = $plxAdmin->plxRecord_coms->f('article').'.'.$plxAdmin->plxRecord_coms->f('numero'); ?>
		<div id="c<?= $comId ?>" class="comment<?= ((isset($_GET['c']) AND $_GET['c']==$comId)?' current':'') ?> level-<?= $plxAdmin->plxRecord_coms->f('level'); ?>">
			<div id="com-<?= $plxAdmin->plxRecord_coms->f('index'); ?>">
				<small>
					<span class="nbcom">#<?= $plxAdmin->plxRecord_coms->i+1 ?></span>&nbsp;
					<time datetime="<?= plxDate::formatDate($plxAdmin->plxRecord_coms->f('date'), '#num_year(4)-#num_month-#num_day #hour:#minute'); ?>"><?= plxDate::formatDate($plxAdmin->plxRecord_coms->f('date'), '#day #num_day #month #num_year(4) &agrave; #hour:#minute'); ?></time> -
					<?= L_COMMENT_WRITTEN_BY ?>&nbsp;<strong><?= $plxAdmin->plxRecord_coms->f('author'); ?></strong>
					<a class="button" href="comment.php?c=<?= $comId ?>" title="<?= L_COMMENT_EDIT_TITLE ?>"><?= L_COMMENT_EDIT ?></a>
					<a class="button" data-comment="<?= $comId ?>"><?= L_COMMENT_ANSWER ?></a>
				</small>
				<blockquote class="type-<?= $plxAdmin->plxRecord_coms->f('type'); ?>"><?= nl2br($plxAdmin->plxRecord_coms->f('content')); ?></blockquote>
			</div>
			<?php eval($plxAdmin->plxPlugins->callHook('AdminCommentNewList')); # Hook Plugins ?>
		</div>
<?php
	if(isset($_GET['c']) and $comId == $_GET['c']) {
		# Réponse à un commentaire
		echo $theForm;
	}
?>
	<?php endwhile; ?>
</div>
<?php endif; ?>
<script>
	(function () {
		'use strict';
		const commentsList = document.getElementById('comments-list');
		if(commentsList) {
			commentsList.addEventListener('click', function(ev) {
				if(!ev.target.hasAttribute('data-comment')) {
					return;
				}

				ev.preventDefault();
				const commentId = ev.target.dataset.comment;
				console.log(commentId);
				const commentEl = document.getElementById('c' + commentId);
				if(commentEl) {
					const theForm = document.getElementById('form_comment');
					if(theForm) {
						// document.body.remove(theForm);
						theForm.parentElement.removeChild(theForm)
						commentEl.after(theForm);
						theForm.reset();
						theForm.action = '<?= $_SERVER['SCRIPT_NAME'] ?>?c=' + commentId;
						theForm.elements['content'].focus();
					}
				} else {
					console.error(comment + ' comment notfound');
				}
			});
		}
	})();
</script>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentNewFoot'));

# On inclut le footer
include 'foot.php';
