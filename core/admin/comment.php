<?php

/**
 * Edition d'un commentaire
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR);

# Interdire de l'accès à la page si les commentaires sont désactivés
if(!$plxAdmin->aConf['allow_com']) {
	header('Location: index.php');
	exit;
}

# validation de l'id de l'article si passé en paramètre
if(isset($_GET['a']) AND !preg_match('/^_?\d{4}$/', $_GET['a'])) {
	plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
	header('Location: index.php');
	exit;
}

# On édite, supprime ou valide notre commentaire
if(!empty($_POST) AND !empty($_POST['comId'])) {

	# validation du numéro de commentaire
	if(!preg_match('/[[:punct:]]?\d{4}\.\d{10}-\d+$/', $_POST['comId'])) {
		plxMsg::Error(L_ERR_UNKNOWN_COMMENT);
		header('Location: comments.php');
		exit;
	}

	# Suppression, on redirige
	if(isset($_POST['delete'])) {
		$plxAdmin->delCommentaire($_POST['comId']);
		header('Location: comments.php');
		exit;
	}
	# Commentaire en ligne
	if(isset($_POST['online'])) {
		$plxAdmin->editCommentaire($_POST, $_POST['comId']);
		$plxAdmin->modCommentaire($_POST['comId'], 'online');
		header('Location: comment.php?c=' . $_POST['comId'] . (!empty($_GET['a'])?'&a=' . $_GET['a'] : ''));
		exit;
	}
	# Commentaire hors-ligne
	if(isset($_POST['offline'])) {
		$plxAdmin->editCommentaire($_POST, $_POST['comId']);
		$plxAdmin->modCommentaire($_POST['comId'], 'offline');
		header('Location: comment.php?c=' . $_POST['comId'] . (!empty($_GET['a'])?'&a=' . $_GET['a'] : ''));
		exit;
	}
	# Répondre au commentaire
	if(isset($_POST['answer'])) {
		header('Location: comment_new.php?c=' . $_POST['comId']) . (!empty($_GET['a'])?'&a=' . $_GET['a'] : '');
		exit;
	}
	# Edition
	$plxAdmin->editCommentaire($_POST, $_POST['comId']);
	header('Location: comment.php?c=' . $_POST['comId'] . (!empty($_GET['a'])?'&a=' . $_GET['a'] : ''));
	exit;
}

# On va récupérer les infos sur le commentaire
if(!$plxAdmin->getCommentaires('/^' . plxUtils::nullbyteRemove($_GET['c']) . '\.xml$/', '', 0, 1, 'all')) {
	# Commentaire inexistant, on redirige
	plxMsg::Error(L_ERR_UNKNOWN_COMMENT);
	header('Location: comments.php');
	exit;
}

# On va récupérer les infos sur l'article
$artId = $plxAdmin->plxRecord_coms->f('article');
# On va rechercher notre article
if(($aFile = $plxAdmin->plxGlob_arts->query('/^' . $artId . '\.(.+)\.xml$/', '', 'sort', 0, 1)) == false) {
	# On indique que le commentaire est attaché à aucun article
	$article = '<strong>' . L_COMMENT_ORPHAN . '</strong>';
	# Statut du commentaire
	$statut = '<strong>' . L_COMMENT_ORPHAN_STATUS . '</strong>';
} else {
	$result = $plxAdmin->parseArticle(PLX_ROOT . $plxAdmin->aConf['racine_articles'] . $aFile['0']);
	# On génère notre lien
	$article = '<a href="' . $plxAdmin->urlRewrite('?' . L_ARTICLE_URL . intval($result['numero']) . '/' . $result['url']) . '" title="' . L_COMMENT_ARTICLE_LINKED_TITLE . '">';
	$article .= plxUtils::strCheck($result['title']);
	$article .= '</a>';
}

# Statut du commentaire
$com=$plxAdmin->comInfoFromFilename($_GET['c'] . '.xml');
if($com['comStatus']=='_')
	$statut = '<strong>' . L_COMMENT_OFFLINE . '</strong>';
elseif($com['comStatus']=='')
	$statut = '<a href="' . $plxAdmin->urlRewrite('?' . L_ARTICLE_URL . intval($plxAdmin->plxRecord_coms->f('article')) . '/#c' . $plxAdmin->plxRecord_coms->f('index')) . '" title="' . L_COMMENT_ONLINE_TITLE . '">' . L_COMMENT_ONLINE . '</a>';
else
	$statut = '';

# Date du commentaire
$date = plxDate::date2Array($plxAdmin->plxRecord_coms->f('date'));

# On inclut le header
include 'top.php';

if($plxAdmin->plxRecord_coms->f('type') != 'admin') {
	$author = $plxAdmin->plxRecord_coms->f('author');
	$site = $plxAdmin->plxRecord_coms->f('site');
	$content = $plxAdmin->plxRecord_coms->f('content');
} else {
	$author = plxUtils::strCheck($plxAdmin->plxRecord_coms->f('author'));
	$site = plxUtils::strCheck($plxAdmin->plxRecord_coms->f('site'));
	$content = plxUtils::strCheck($plxAdmin->plxRecord_coms->f('content'));
}

?>

<form action="comment.php<?= (!empty($_GET['a']) ? '?a=' . plxUtils::strCheck($_GET['a']) : '') ?>" method="post" id="form_comment">
	<?= plxToken::getTokenPostMethod() ?>
	<div class="inline-form action-bar">
		<h2><?= L_COMMENT_EDITING ?></h2>
<?php
		if(!empty($_GET['a'])) {
?>
		<p><a class="back" href="comments.php?a=<?= $_GET['a'] ?>"><?= L_BACK_TO_ARTICLE_COMMENTS ?></a></p>
<?php
		} else {
?>
		<p><a class="back" href="comments.php"><?= L_BACK_TO_COMMENTS ?></a></p>
<?php
		}
		if($com['comStatus']=='') {
?>
		<input type="submit" name="offline" value="<?= L_COMMENT_OFFLINE_BUTTON ?>" />
		<input type="submit" name="answer" value="<?= L_COMMENT_ANSWER_BUTTON ?>" />
<?php
		} else {
?>
		<input type="submit" name="online" value="<?= L_COMMENT_PUBLISH_BUTTON ?>" />
<?php
		}
?>
		<input type="submit" name="update" value="<?= L_COMMENT_UPDATE_BUTTON ?>" />
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span><input class="red" type="submit" name="delete" value="<?= L_DELETE ?>" onclick="Check=confirm('<?= L_COMMENT_DELETE_CONFIRM ?>');if(Check==false) return false;"/>
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminCommentTop')); # Hook Plugins ?>

	<ul class="unstyled-list">
		<li><?= L_COMMENT_IP_FIELD ?>&nbsp;:&nbsp;<?= $plxAdmin->plxRecord_coms->f('ip'); ?></li>
		<li><?= L_COMMENT_STATUS_FIELD ?>&nbsp;:&nbsp;<?= $statut; ?></li>
		<li><?= L_COMMENT_TYPE_FIELD ?>&nbsp;:&nbsp;<strong><?= $plxAdmin->plxRecord_coms->f('type'); ?></strong></li>
		<li><?= L_COMMENT_LINKED_ARTICLE_FIELD ?>&nbsp;:&nbsp;<?= $article; ?></li>
	</ul>

	<fieldset>
		<?php plxUtils::printInput('comId', $_GET['c'], 'hidden'); ?>

		<div class="grid inline-form publication">
			<div class="col sml-12">
				<label><?= L_COMMENT_DATE_FIELD ?>&nbsp;:</label>
				<?php plxUtils::printInput('date_publication_day', $date['day'], 'text', '2-2', false, 'day', date('d'), 'pattern="\d{2}"', true); ?>
				<?php plxUtils::printInput('date_publication_month', $date['month'], 'text', '2-2', false, 'month', date('m'), 'pattern="\d{2}"', true); ?>
				<?php plxUtils::printInput('date_publication_year', $date['year'], 'text', '2-4', false, 'year', date('Y'), 'pattern="\d{4}"', true); ?>
				<?php plxUtils::printInput('date_publication_time', $date['time'], 'text', '2-5', false, 'time', date('H:i'), 'pattern="\d{2}:\d{2}"', true); ?>
				<a href="javascript:void(0)" onclick="dateNow('date_publication', <?= date('Z') ?>); return false;" title="<?php L_NOW; ?>"><img src="theme/images/date.png" alt="" /></a>
			</div>
		</div>

		<div class="grid">
			<div class="col sml-12">
				<label for="id_author" class="required"><?= L_COMMENT_AUTHOR_FIELD ?>&nbsp;:</label>
				<?php plxUtils::printInput('author', $author, 'text', '40-255', false, '', '', '', true); ?>
			</div>
		</div>

		<div class="grid">
			<div class="col sml-12">
				<label for="id_site"><?= L_COMMENT_SITE_FIELD ?>&nbsp;:<?php

				if(!empty($site)) {
					?>&nbsp;<a href="<?= $site ?>" target="_blank" rel="noreferrer"><?= $site ?></a><?php
				}
?></label>
				<?php plxUtils::printInput('site', $site, 'text', '40-255'); ?>
			</div>
		</div>

		<div class="grid">
			<div class="col sml-12">
				<label for="id_mail"><?= L_COMMENT_EMAIL_FIELD ?>&nbsp;:<?php

				$mail = $plxAdmin->plxRecord_coms->f('mail');
				if(!empty($mail)) {
					?>&nbsp;<a href="mailto:<?= $mail ?>"><?= $mail ?></a><?php
				}
?></label>
				<?php plxUtils::printInput('mail', plxUtils::strCheck($mail), 'text', '40-255'); ?>
			</div>
		</div>

		<div class="grid">
			<div class="col sml-12">
				<label for="id_content" class="required"><?= L_COMMENT_ARTICLE_FIELD ?>&nbsp;:</label>
				<?php plxUtils::printArea('content', $content, 0, 7, false, 'full-width', 'required placeholder=" "'); ?>
				<?php eval($plxAdmin->plxPlugins->callHook('AdminComment')); # Hook Plugins ?>
			</div>
		</div>

	</fieldset>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentFoot'));

# On inclut le footer
include 'foot.php';
