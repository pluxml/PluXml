<?php

/**
 * Edition d'un commentaire
 *
 * @package PLX
 * @author    Stephane F et Florent MONTHEL
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_MODERATOR);

# Interdire de l'accès à la page si les commentaires sont désactivés
if (!$plxAdmin->aConf['allow_com']) {
    plxMsg::Error(L_COMMENTS_CLOSED);
    header('Location: index.php');
    exit;
}

# validation de l'id de l'article si passé en paramètre
if (isset($_GET['a']) and !preg_match('/^_?\d{4}$/', $_GET['a'])) {
    plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
    header('Location: index.php');
    exit;
}

# On édite, supprime ou valide notre commentaire
if (!empty($_POST) and !empty($_POST['comId'])) {

    # validation du numéro de commentaire
    if (!preg_match('@_?\d{4}\.\d{10}-\d+$@', $_POST['comId'])) {
        plxMsg::Error(L_ERR_UNKNOWN_COMMENT);
        header('Location: comments.php');
        exit;
    }

    # Suppression, on redirige
    if (isset($_POST['delete'])) {
        $plxAdmin->delCommentaire($_POST['comId']);
        header('Location: comments.php');
        exit;
    }

    $query = 'c=' . $_POST['comId'];
    if(!empty($_GET['a'])) {
		$query .= '&a=' . $_GET['a'];
	}

    # Commentaire en ligne
    if (isset($_POST['online'])) {
        $plxAdmin->editCommentaire($_POST, $_POST['comId']);
        $plxAdmin->modCommentaire($_POST['comId'], 'online');
        header('Location: comment.php?' . $query);
        exit;
    }
    # Commentaire hors-ligne
    if (isset($_POST['offline'])) {
        $plxAdmin->editCommentaire($_POST, $_POST['comId']);
        $plxAdmin->modCommentaire($_POST['comId'], 'offline');
        header('Location: comment.php?' . $query);
        exit;
    }
    # Répondre au commentaire
    if (isset($_POST['answer'])) {
        header('Location: comment_new.php?' . $query);
        exit;
    }
    # Edition
    $plxAdmin->editCommentaire($_POST, $_POST['comId']);
    header('Location: comment.php?' . $query);
    exit;
}

# On va récupérer les infos sur le commentaire
if (empty($_GET['c']) or !$plxAdmin->getCommentaires('/^' . plxUtils::nullbyteRemove($_GET['c']) . '.xml$/', '', 0, 1, 'all')) {
    # Commentaire inexistant, on redirige
    plxMsg::Error(L_ERR_UNKNOWN_COMMENT);
    header('Location: comments.php');
    exit;
}

# On va récupérer les infos sur l'article
$artId = $plxAdmin->plxRecord_coms->f('article');
# On va rechercher notre article
if (($aFile = $plxAdmin->plxGlob_arts->query('/^' . $artId . '.(.+).xml$/', '', 'sort', 0, 1)) == false) {
    # On indique que le commentaire est attaché à aucun article
    $article = '<strong>' . L_NO_ARTICLE . '</strong>';
    # Statut du commentaire
    $statut = '<strong>' . L_COMMENT_ORPHAN_STATUS . '</strong>';
} else {
    $result = $plxAdmin->parseArticle(PLX_ROOT . $plxAdmin->aConf['racine_articles'] . $aFile['0']);
    # On génère notre lien
    $article = '<a href="' . $plxAdmin->urlRewrite('?article' . intval($result['numero']) . '/' . $result['url']) . '" title="' . L_COMMENT_ARTICLE_LINKED_TITLE . '">';
    $article .= plxUtils::strCheck($result['title']);
    $article .= '</a>';
}

# Statut du commentaire
$com = $plxAdmin->comInfoFromFilename($_GET['c'] . '.xml');
if ($com['comStatus'] == '_')
    $statut = '<strong>' . L_COMMENT_OFFLINE . '</strong>';
elseif ($com['comStatus'] == '')
    $statut = '<a href="' . $plxAdmin->urlRewrite('?article' . intval($plxAdmin->plxRecord_coms->f('article')) . '/#c' . $plxAdmin->plxRecord_coms->f('index')) . '" title="' . L_COMMENT_ONLINE_TITLE . '">' . L_COMMENT_ONLINE . '</a>';
else
    $statut = '';

# Date du commentaire
$dates5 = plxDate::date2html5(array('date_publication' => $plxAdmin->plxRecord_coms->f('date'))); # récupère les dates - version PluXml >= 6.0.0

// $date = plxDate::date2Array($plxAdmin->plxRecord_coms->f('date'));

# On inclut le header
include 'top.php';

if ($plxAdmin->plxRecord_coms->f('type') != 'admin') {
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
	<?php plxUtils::printInput('comId', $_GET['c'], 'hidden'); ?>
    <div class="adminheader">
        <div>
            <h2 class="h3-like"><?= L_COMMENT_EDITING; ?></h2>
            <?php if (!empty($_GET['a'])) : ?>
                <p><a class="back icon-left-big" href="comments.php?a=<?= $_GET['a'] ?>"><?= L_BACK_TO_ARTICLE_COMMENTS ?></a></p>
            <?php else : ?>
                <p><a class="back icon-left-big" href="comments.php"><?= L_BACK_TO_COMMENTS ?></a></p>
            <?php endif; ?>
        </div>
        <div>
			<div>
<?php if ($com['comStatus'] == '') : ?>
	                <input class="btn--primary" type="submit" name="offline" value="<?= L_SET_OFFLINE ?>"/>
	                <input class="btn--primary" type="submit" name="answer" value="<?= L_COMMENT_ANSWER_BUTTON ?>"/>
<?php else : ?>
	                <input class="btn--primary" type="submit" name="online" value="<?= L_COMMENT_PUBLISH_BUTTON ?>"/>
<?php endif; ?>
	            <input class="btn--primary" type="submit" name="update" value="<?= L_COMMENT_UPDATE_BUTTON ?>"/>
	            <input class="btn--warning" type="submit" name="delete"
	                   value="<?= L_DELETE ?>"
	                   onclick="Check=confirm('<?= L_COMMENT_DELETE_CONFIRM ?>');if(Check==false) return false;"/>
			</div>
        </div>
    </div>
    <div class="admin">
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentTop'))
?>
        <ul class="unstyled">
            <li><?= L_COMMENT_IP_FIELD ?> : <?= $plxAdmin->plxRecord_coms->f('ip'); ?></li>
            <li><?= L_COMMENT_STATUS_FIELD ?> : <?= $statut; ?></li>
            <li><?= L_COMMENT_TYPE_FIELD ?> : <strong><?= $plxAdmin->plxRecord_coms->f('type'); ?></strong></li>
            <li><?= L_COMMENT_LINKED_ARTICLE_FIELD ?> : <?= $article; ?></li>
        </ul>
        <fieldset>
            <div>
<?php plxUtils::printDates($dates5); ?>
                <div>
                    <label for="id_author"><?= L_AUTHOR ?></label>
                    <input type="text" name="author" value="<?= $author ?>" maxlength="64" required />
                </div>
	            <div>
                    <label for="id_site">
                        <span><?= L_COMMENT_SITE_FIELD ?></span>
<?php
if($site != '') {
?>
						<a href="<?= $site ?>" target="_blank"><?= L_WATCH ?></a>
<?php
}
?>
                    </label>
                    <?php plxUtils::printInput('site', $site, 'text', '40-255'); ?>
	            </div>
                <div>
                    <label for="id_mail"><?= L_EMAIL ?>
<?php
if ($plxAdmin->plxRecord_coms->f('mail') != '') :
?>
                    <a href="mailto:<?= $plxAdmin->plxRecord_coms->f('mail') ?>"><?= L_SEND_MAIL ?></a>
<?php
endif;
?>
                    </label>
                    <input type="email" name="mail" value="<?= plxUtils::strCheck($plxAdmin->plxRecord_coms->f('mail')) ?>" maxlength="64" />
                </div>
            </div>
			<div>
				<label for="id_content"><?= L_COMMENT_ARTICLE_FIELD ?></label>
				<textarea name="content" rows="7" id><?= $content ?></textarea>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminComment'))
?>
			</div>
        </fieldset>
    </div>
</form>
<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentFoot'));

# On inclut le footer
include 'foot.php';
