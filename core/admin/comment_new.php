<?php

/**
 * Création d'un commentaire
 *
 * @package PLX
 * @author    Florent MONTHEL
 **/

include 'prepend.php';

# Contrôle du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentNewPrepend'));

# Contrôle de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_MODERATOR);

# Interdire de l'accès à la page si les commentaires sont désactivés
if (!$plxAdmin->aConf['allow_com']) {
    plxMsg::Error(L_COMMENTS_CLOSED);
    header('Location: index.php');
    exit;
}

# validation de l'id de l'article si passé en paramètre avec $_GET['a']
if (isset($_GET['a'])) {
    if (!preg_match('/^_?(\d{4})$/', $_GET['a'], $capture)) {
        plxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
        header('Location: index.php');
        exit;
    } else {
        $artId = $capture[1];
    }
}
# validation de l'id de l'article si passé en paramètre avec $_GET['c']
if (isset($_GET['c'])) {
    if (!preg_match('/^_?(\d{4})\.(.*)$/', $_GET['c'], $capture)) {
        plxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
        header('Location: index.php');
        exit;
    } else {
        $artId = $capture[1];
    }
}

# On va checker le mode (répondre ou écrire)
if (!empty($_GET['c'])) { # Mode "answer"
    # On check que le commentaire existe et est "online"
    if (!$plxAdmin->getCommentaires('/^' . plxUtils::nullbyteRemove($_GET['c']) . '.xml$/', '', 0, 1, 'all')) {
        # On redirige
        plxMsg::Error(L_ERR_ANSWER_UNKNOWN_COMMENT);
        header('Location: comments.php' . (!empty($_GET['a']) ? '?a=' . $_GET['a'] : ''));
        exit;
    }
    # Commentaire offline
    if (preg_match('/^_/', $_GET['c'])) {
        # On redirige
        plxMsg::Error(L_ERR_ANSWER_OFFLINE_COMMENT);
        header('Location: comments.php' . (!empty($_GET['a']) ? '?a=' . $_GET['a'] : ''));
        exit;
    }
    # On va rechercher notre article
    if (($aFile = $plxAdmin->plxGlob_arts->query('/^' . $artId . '.(.+).xml$/', '', 'sort', 0, 1)) == false) { # Article inexistant
        plxMsg::Error(L_ERR_COMMENT_UNKNOWN_ARTICLE);
        header('Location: index.php');
        exit;
    }
    # Variables de traitement
    if (!empty($_GET['a'])) $get = 'c=' . $_GET['c'] . '&amp;a=' . $_GET['a'];
    else $get = 'c=' . $_GET['c'];
    $aArt = $plxAdmin->parseArticle(PLX_ROOT . $plxAdmin->aConf['racine_articles'] . $aFile['0']);
    # Variable du formulaire
    $content = '';
    $article = '<a href="article.php?a=' . $aArt['numero'] . '" title="' . L_COMMENT_ARTICLE_LINKED_TITLE . '">';
    $article .= plxUtils::strCheck($aArt['title']);
    $article .= '</a>';
    # Ok, on récupère les commentaires de l'article
    $plxAdmin->getCommentaires('/^' . str_replace('_', '', $artId) . '.(.*).xml$/', 'sort');
    # Recherche du parent à partir de l'url
    if ($com = $plxAdmin->comInfoFromFilename($_GET['c'] . '.xml'))
        $parent = $com['comIdx'];
    else
        $parent = '';

} elseif (!empty($_GET['a'])) { # Mode "new"
    # On check l'article si il existe bien
    if (($aFile = $plxAdmin->plxGlob_arts->query('/^' . $_GET['a'] . '.(.+).xml$/', '', 'sort', 0, 1)) == false) {
        plxMsg::Error(L_ERR_COMMENT_UNEXISTENT_ARTICLE);
        header('Location: index.php');
        exit;
    }
    # Variables de traitement
    $artId = $_GET['a'];
    $get = 'a=' . $_GET['a'];
    $aArt = $plxAdmin->parseArticle(PLX_ROOT . $plxAdmin->aConf['racine_articles'] . $aFile['0']);
    # Variable du formulaire
    $content = '';
    $article = '<a href="article.php?a=' . $aArt['numero'] . '" title="' . L_COMMENT_ARTICLE_LINKED_TITLE . '">';
    $article .= plxUtils::strCheck($aArt['title']);
    $article .= '</a>';
    $parent = '';
    # Ok, on récupère les commentaires de l'article
    $plxAdmin->getCommentaires('/^' . str_replace('_', '', $artId) . '.(.*).xml$/', 'sort');
} else { # Mode inconnu
    header('Location: .index.php');
    exit;
}

# On a validé le formulaire
if (!empty($_POST) and !empty($_POST['content'])) {
    # Création du commentaire
    if (!$plxAdmin->newCommentaire(str_replace('_', '', $artId), $_POST)) { # Erreur
        plxMsg::Error(L_ERR_CREATING_COMMENT);
    } else { # Ok
        plxMsg::Info(L_CREATING_COMMENT_SUCCESSFUL);
    }
    header('Location: comment_new.php?a=' . $artId);
    exit;
}

# On inclut le header
include 'top.php';
?>

<form action="comment_new.php?<?= plxUtils::strCheck($get) ?>" method="post" id="form_comment_new">
	<?php plxUtils::printInput('parent', $parent, 'hidden'); ?>
    <div class="adminheader">
        <div class="mbm">
            <h2 class="h3-like"><?= L_CREATE_NEW_COMMENT; ?></h2>
<?php if (!empty($_GET['a'])) : ?>
                <p><a class="back" href="comments.php?a=<?= $_GET['a']; ?>"><?= L_BACK_TO_ARTICLE_COMMENTS ?></a></p>
<?php else : ?>
                <p><a class="back" href="comments.php"><?= L_BACK_TO_COMMENTS ?></a></p>
<?php endif; ?>
            <input class="btn--primary" type="submit" name="create" value="<?= L_SAVE ?>"/>
        </div>
    </div>
    <div class="admin">
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentNewTop'));
?>
        <h3 class="no-margin"><?= ucfirst(L_ARTICLE) ?> &laquo;<?= plxUtils::strCheck($aArt['title']); ?>&raquo;</h3>
        <ul>
            <li><?= L_AUTHOR ?> :
                <strong><?= plxUtils::strCheck($plxAdmin->aUsers[$_SESSION['user']]['name']); ?></strong>
            </li>
            <li><?= L_COMMENT_TYPE_FIELD ?> : <strong>admin</strong></li>
        </ul>

        <fieldset>
            <div>
                <div>
                    <div id="id_answer"></div>
                    <?= plxToken::getTokenPostMethod() ?>
                    <label for="id_content"><?= L_COMMENT_ARTICLE_FIELD ?></label>
                    <textarea name="content" rows="7" id="id_content"><?= plxUtils::strCheck($content) ?></textarea>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentNew'))
?>
                </div>
            </div>
        </fieldset>
    </div>
</form>
<div class="admin">
<?php
if (isset($plxAdmin->plxRecord_coms)) {
	# On a des commentaires
?>
        <h3><?= L_ARTICLE_COMMENTS_LIST ?></h3>
<?php
	# On boucle
	while ($plxAdmin->plxRecord_coms->loop()) {
		$comId = $plxAdmin->plxRecord_coms->f('article') . '.' . $plxAdmin->plxRecord_coms->f('numero');
?>
            <div id="c<?= $comId ?>"
                 class="comment<?= ((isset($_GET['c']) and $_GET['c'] == $comId) ? ' current' : '') ?> level-<?= $plxAdmin->plxRecord_coms->f('level'); ?>">
                <div id="com-<?= $plxAdmin->plxRecord_coms->f('index'); ?>">
                    <small>
                        <span class="nbcom">#<?= $plxAdmin->plxRecord_coms->i + 1 ?></span>&nbsp;
                        <time datetime="<?= plxDate::formatDate($plxAdmin->plxRecord_coms->f('date'), '#num_year(4)-#num_month-#num_day #hour:#minute'); ?>">
							<?= plxDate::formatDate($plxAdmin->plxRecord_coms->f('date'), '#day #num_day #month #num_year(4) &agrave; #hour:#minute'); ?>
						</time>
                        -
                        <?= L_WRITTEN_BY ?>&nbsp;<strong><?= $plxAdmin->plxRecord_coms->f('author'); ?></strong>
                        -
                        <a href="comment.php<?= (!empty($_GET['a'])) ? '?c=' . $comId . '&amp;a=' . $_GET['a'] : '?c=' . $comId; ?>"
                           title="<?= L_COMMENT_EDIT_TITLE ?>"><?= L_EDIT ?></a>
                        - <a href="#form_comment"
                             onclick="replyCom('<?= $plxAdmin->plxRecord_coms->f('index') ?>')"><?= L_COMMENT_ANSWER ?></a>
                    </small>
                    <blockquote class="type-<?= $plxAdmin->plxRecord_coms->f('type'); ?>"><?= nl2br($plxAdmin->plxRecord_coms->f('content')); ?></blockquote>
                </div>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentNewList'))
?>
            </div>
<?php
	}
}
?>
</div>
<script>
    function replyCom(idCom) {
        document.getElementById('id_answer').innerHTML = '<?= L_REPLY_TO ?> : ';
        document.getElementById('id_answer').innerHTML += document.getElementById('com-' + idCom).innerHTML;
        document.getElementById('id_answer').innerHTML += '<a href="javascript:void(0)" onclick="cancelCom()"><?= L_CANCEL ?></a>';
        document.getElementById('id_answer').style.display = 'inline-block';
        document.getElementById('id_parent').value = idCom;
        document.getElementById('id_content').focus();
    }

    function cancelCom() {
        document.getElementById('id_answer').style.display = 'none';
        document.getElementById('id_parent').value = '';
    }

    var parent = document.getElementById('id_parent').value;
    if (parent != '') {
        replyCom(parent)
    }
</script>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentNewFoot'));

# On inclut le footer
include 'foot.php';
