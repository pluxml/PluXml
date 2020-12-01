<?php

/**
 * Edition d'un article
 *
 * @package PLX
 * @author    Stephane F et Florent MONTHEL
 **/

include 'prepend.php';

# Control du token du formulaire
if (!isset($_POST['preview']))
    plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminArticlePrepend'));

# validation de l'id de l'article si passé en parametre
if (isset($_GET['a']) and !preg_match('/^_?\d{4}$/', $_GET['a'])) {
    plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # mauvais format d'identifiant d'article
    header('Location: index.php');
    exit;
}

# Soumission des données du formulaire
if (!empty($_POST)) { # Création, mise à jour, suppression ou aperçu

    # droits réduits pour cet utilisateur
    if ($_SESSION['profil'] == PROFIL_WRITER) {
		# on  force l'identifiant de l'auteur avec l'utilisateur connecté
		if(empty($_POST['author']) or $_SESSION['user'] != $_POST['author']) {
			$_POST['author'] = $_SESSION['user'];
		}
		# On contrôle si l'utilisateur est l'auteur de l'article
		if(
			isset($_POST['artId']) and
			$_POST['artId'] != '0000' and
			# format général d'un nom de fichier-article : '@^_?\d{4}\.(?:draft,|\d{3},)*(?:home|\d{3})(,\d{3})*\.\d{3}\.\d{12}\..*\.xml$@'
			empty($plxAdmin->plxGlob_arts->query(
				'@^_?' . $_POST['artId'] .'\.(?:draft,|\d{3},)*(?:home|\d{3})(,\d{3})*\.' . $_SESSION['user'] . '\.\d{12}\..*\.xml$@')
			)
		) {
			# On rejete la soumission du formulaire
            plxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
            header('Location: index.php');
            exit;
		}
	}

    if (!isset($_POST['catId'])) {
		# article non classé
		$_POST['catId'] = array('000');
	}

    # Si demande d'enregistrement en brouillon on ajoute la categorie draft à la liste et on retire la demande de validation
    if (isset($_POST['draft']) and !in_array('draft', $_POST['catId'])) {
		# draft toujours n°1
		array_unshift($_POST['catId'], 'draft');
	}

    # Si demande de publication ou demande de validation, on supprime la catégorie draft si elle existe
    if (isset($_POST['update']) or isset($_POST['publish']) or isset($_POST['moderate'])) {
		$_POST['catId'] = array_filter($_POST['catId'], function ($a) {
	        return $a != 'draft';
	    });
	}

    # Titre par défaut si titre vide
    if (trim($_POST['title']) == '') {
		$_POST['title'] = L_NEW_ARTICLE;
	}

    # --------- Previsualisation d'un article ---------
    if (!empty($_POST['preview'])) {
        $tmpStr = (!empty(trim($_POST['url']))) ? $_POST['url'] : $_POST['title'];
        $tmpUrl = plxUtils::urlify($tmpStr);
        $art = array(
	        'title'				=> trim($_POST['title']),
	        'url'				=> !empty($tmpUrl) ? $tmpUrl : L_DEFAULT_NEW_ARTICLE_URL,
	        'allow_com'			=> $_POST['allow_com'],
	        'template'			=> basename($_POST['template']),
	        'chapo'				=> trim($_POST['chapo']),
	        'content'			=> trim($_POST['content']),
	        'categorie'			=> implode(',', array_filter($_POST['catId'], function($value) { $value != 'draft'; })),
	        'tags'				=> trim($_POST['tags']),
	        'meta_description'	=> $_POST['meta_description'],
	        'meta_keywords'		=> $_POST['meta_keywords'],
	        'title_htmltag'		=> $_POST['title_htmltag'],
	        'filename'			=> '',
	        'numero'			=> $_POST['artId'],
	        'author'			=> $_POST['author'],
	        'thumbnail'			=> $_POST['thumbnail'],
	        'thumbnail_title'	=> $_POST['thumbnail_title'],
	        'thumbnail_alt'		=> $_POST['thumbnail_alt'],
			'nb_com'			=> 0,
        );
        foreach(plxDate::ENTRIES as $k) {
			$art[$k] = substr(preg_replace('@\D@', '', $_POST[$k][0] . $_POST[$k][1]), 0, 12);
		}

        # Hook Plugins
        eval($plxAdmin->plxPlugins->callHook('AdminArticlePreview'));

        $article[0] = $art;
        $_SESSION['preview'] = $article;
        header('Location: index.php?preview');
        exit;
    }

    # --------- Suppression d'un article --------------
    if (isset($_POST['delete'])) {
        $plxAdmin->delArticle($_POST['artId']);
        header('Location: index.php');
        exit;
    }

    # --------- Mode création ou maj -------------
    if (isset($_POST['update']) or isset($_POST['publish']) or isset($_POST['moderate']) or isset($_POST['draft'])) {

        $valid = true;

        # Vérification de l'unicité de l'url
        # Problème si plusieurs articles ont le même titre !
        $url = plxUtils::urlify(!empty($_POST['url']) ? $_POST['url'] : $_POST['title']);
        $artId = $_POST['artId'];
        $filenames = array_filter($plxAdmin->plxGlob_arts->aFiles, function($value) use($url, $artId) {
			return (
				preg_match('@^_?\d{4}\.(?:draft,|\d{3},)*(?:home|\d{3})(,\d{3})*\.\d{3}\.\d{12}\.' . $url . '\.xml$@', $value) and
				!preg_match('@^_?' . $artId . '\.@', $value)
			);
		});
        if(!empty($filenames)) {
			$valid = false;
			plxMsg::Error(L_ERR_URL_ALREADY_EXISTS . " : " . plxUtils::strCheck($url));
		}

		if($valid) {
			# Contrôle de la validité des dates
			foreach(plxDate::ENTRIES as $k) {
				if(!plxDate::checkDate5($_POST[$k][0], $_POST[$k][1])) {
					$valid = false;
					break;
				}
			}

			if($valid) {
	            $plxAdmin->editArticle($_POST, $_POST['artId']);
	            header('Location: article.php?a=' . $_POST['artId']);
	            exit;
			} else {
				plxMsg::Error(L_BAD_DATE_FORMAT);
			}
		}

		# Le formulaire n'a pas été validé. Retour en mode brouillon (draft) sans sauvegarde
		array_unshift($_POST['catId'], 'draft');
    }

    # ------------ Ajout d'une catégorie -----------
    if (isset($_POST['new_category'])) {
        # Ajout de la nouvelle catégorie
        $plxAdmin->editCategories($_POST);

        # On recharge la nouvelle liste
        $plxAdmin->getCategories(path('XMLFILE_CATEGORIES'));
        $_GET['a'] = $_POST['artId'];
    }

    # Alimentation des variables
    $artId = $_POST['artId'];
    $title = trim($_POST['title']);
    $author = $_POST['author'];
    $catId = isset($_POST['catId']) ? $_POST['catId'] : array();

    $dates5 = array();
    foreach(plxDate::ENTRIES as $k) {
		$dates5[$k][0] = $_POST[$k][0]; # date au format yyyy-mm-dd
		$dates5[$k][1] = $_POST[$k][1]; # heure au format hh:ii
	}
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

    # Hook Plugins
    eval($plxAdmin->plxPlugins->callHook('AdminArticlePostData'));

    # Fin de traitement du formulaire par methode="post"
} elseif (!empty($_GET['a'])) { # On n'a rien validé, c'est pour l'édition d'un article
    # On va rechercher notre article
    if (($aFile = $plxAdmin->plxGlob_arts->query('/^' . $_GET['a'] . '\..+\.xml$/')) == false) { # Article inexistant
        plxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
        header('Location: index.php');
        exit;
    }

    # On parse et alimente nos variables
    $result = $plxAdmin->parseArticle(PLX_ROOT . $plxAdmin->aConf['racine_articles'] . $aFile['0']);
    $title = trim($result['title']);
    $chapo = trim($result['chapo']);
    $content = trim($result['content']);
    $tags = trim($result['tags']);
    $author = $result['author'];
    $url = $result['url'];
	$dates5 = plxDate::date2html5($result); # récupère les dates - version PluXml >= 6.0.0
    $date_update_old = $result['date_update'];
    $catId = explode(',', $result['categorie']);
    $artId = $result['numero'];
    $allow_com = $result['allow_com'];
    $template = $result['template'];
    $meta_description = $result['meta_description'];
    $meta_keywords = $result['meta_keywords'];
    $title_htmltag = $result['title_htmltag'];

    if ($author != $_SESSION['user'] and $_SESSION['profil'] == PROFIL_WRITER) {
        plxMsg::Error(L_ERR_FORBIDDEN_ARTICLE);
        header('Location: index.php');
        exit;
    }
    # Hook Plugins
    eval($plxAdmin->plxPlugins->callHook('AdminArticleParseData'));

} else {
	# Création d'un article
    $title = plxUtils::strRevCheck(L_NEW_ARTICLE);
    $chapo = $url = '';
    $content = '';
    $tags = '';
    $author = $_SESSION['user'];
    $aDatetime = explode('T', date('Y-m-dTH:i'));
    $dates5 = array(); # version PluXml >= 6.0.0
    foreach(plxDate::ENTRIES as $k) {
		$date5[$k] = $aDatetime; # tableau 2 élements pour <input type="date"> et <input type="time">
	}
    $date_update_old = '';
    $catId = array('draft');
    $artId = '0000';
    $allow_com = $plxAdmin->aConf['allow_com'];
    $template = 'article.php';
    $meta_description = $meta_keywords = $title_htmltag = '';

    # Hook Plugins
    eval($plxAdmin->plxPlugins->callHook('AdminArticleInitData'));
}

# On inclut le header
include 'top.php';

# On construit la liste des utilisateurs
foreach ($plxAdmin->aUsers as $_userid => $_user) {
    if ($_user['active'] and !$_user['delete']) {
        if ($_user['profil'] == PROFIL_ADMIN)
            $_users[L_PROFIL_ADMIN][$_userid] = plxUtils::strCheck($_user['name']);
        elseif ($_user['profil'] == PROFIL_MANAGER)
            $_users[L_PROFIL_MANAGER][$_userid] = plxUtils::strCheck($_user['name']);
        elseif ($_user['profil'] == PROFIL_MODERATOR)
            $_users[L_PROFIL_MODERATOR][$_userid] = plxUtils::strCheck($_user['name']);
        elseif ($_user['profil'] == PROFIL_EDITOR)
            $_users[L_PROFIL_EDITOR][$_userid] = plxUtils::strCheck($_user['name']);
        else
            $_users[L_PROFIL_WRITER][$_userid] = plxUtils::strCheck($_user['name']);
    }
}

# On récupère les templates des articles
$aTemplates = array();
$files = plxGlob::getInstance(PLX_ROOT . $plxAdmin->aConf['racine_themes'] . $plxAdmin->aConf['style']);
if ($array = $files->query('/^article(-[a-z0-9-_]+)?.php$/')) {
    foreach ($array as $k => $v)
        $aTemplates[$v] = $v;
}
if (empty($aTemplates)) $aTemplates[''] = L_NONE1;
$cat_id = '000';
?>

<script>
    function refreshImg(dta) {
        if (dta.trim() === '') {
            document.getElementById('id_thumbnail_img').innerHTML = '';
        } else {
            var link = dta.match(/^(?:https?|data):/gi) ? dta : '<?php echo $plxAdmin->racine ?>' + dta;
            document.getElementById('id_thumbnail_img').innerHTML = '<img src="' + link + '" alt="" />';
        }
    }
</script>

<form method="post" id="form_article">
	<?php PlxUtils::printInput('artId', $artId, 'hidden'); ?>
	<?php PlxUtils::printInput('date_update_old', $date_update_old, 'hidden'); ?>
    <div class="adminheader">
        <div>
            <h2 class="h3-like"><?= (empty($_GET['a'])) ? L_NEW_ARTICLE : L_ARTICLE_EDITING; ?></h2>
            <p><a class="back" href="index.php"><?= L_BACK_TO_ARTICLES ?></a></p>
        </div>
        <div>
            <p class="inbl"><span class="label-like"><?= L_ARTICLE_STATUS ?></span>
                <strong><?php
//TODO create a PlxAdmin function to get article status (P3ter)
if (isset($_GET['a']) and preg_match('/^_\d{4}$/', $_GET['a']))
	echo L_AWAITING;
elseif (in_array('draft', $catId)) {
	echo L_DRAFT;
?><input type="hidden" name="catId[]" value="draft" /><?php
} else
	echo L_PUBLISHED;
?></strong>
            </p>
            <div>
	            <input class="btn--primary" type="submit" name="preview" value="<?= L_ARTICLE_PREVIEW_BUTTON ?>" />
<?php
if ($_SESSION['profil'] > PROFIL_MODERATOR and $plxAdmin->aConf['mod_art']) {
	# L'utilisateur a des droits réduits (pas de modération).
	if (in_array('draft', $catId)) { # brouillon
		if ($artId != '0000') {
			# article à modérer
?>
				<input class="btn--primary" type="submit" name="draft" value="<?= L_ARTICLE_DRAFT_BUTTON ?>" />
				<input class="btn--primary" type="submit" name="moderate" value="<?= L_ARTICLE_MODERATE_BUTTON ?>" />
<?php
		}
	} else {
		if (isset($_GET['a']) and preg_match('/^_\d{4}$/', $_GET['a'])) {
			# en attente
?>
				<input class="btn--primary" type="submit" name="update" value="<?= L_SAVE ?>" />
				<input class="btn--primary" type="submit" name="draft" value="<?= L_ARTICLE_DRAFT_BUTTON ?>" />
<?php
		} else {
?>
				<input class="btn--inverse" type="submit" name="draft" value="<?= L_ARTICLE_DRAFT_BUTTON ?>"/>
				<input class="btn--inverse" type="submit" name="moderate" value="<?= L_ARTICLE_MODERATE_BUTTON ?>"/>
<?php
		}
	}
} else {
	# L'utilisateur peut modérer l'article.
	if (in_array('draft', $catId)) {
		# brouillon
?>
				<input class="btn--primary" type="submit" name="draft" value="<?= L_ARTICLE_DRAFT_BUTTON ?>" />
				<input class="btn--primary" type="submit" name="publish" value="<?= L_ARTICLE_PUBLISHING_BUTTON ?>" />
<?php
	} else {
		if (!isset($_GET['a']) or preg_match('/^_\d{4}$/', $_GET['a'])) {
?>
				<input class="btn--primary" type="submit" name="publish" value="<?= L_ARTICLE_PUBLISHING_BUTTON ?> "/>
<?php
		}
		else {
?>
				<input class="btn--primary" type="submit" name="update" value="<?= L_SAVE ?>" />
<?php
			if(!empty($_GET['a'] and substr($_GET['a'], 0, 1) != '_')) {
?>
				<input class="btn--primary" type="submit" name="draft" value="<?= L_SET_OFFLINE ?>" />
<?php
			}
		}
	}
}
	if (!empty($artId) and $artId != '0000') {
		# l'article existe déjà. On peut le supprimer.
?>
				<input class="btn--warning" type="submit" name="delete" value="<?= L_DELETE ?>" onclick="return confirm('<?= L_ARTICLE_DELETE_CONFIRM ?>');" />
<?php
	}
?>
	        </div>
        </div>
    </div>

    <div>

<?php eval($plxAdmin->plxPlugins->callHook('AdminArticleTop')) # Hook Plugins ?>

        <div class="grid-8-small-1">
            <div class="col-6-small-1">
                <div>
                    <fieldset>
                        <div>
							<p class="has-label">
	                            <label for="id_title"><?= L_TITLE ?></label>
	                            <?php PlxUtils::printInput('title', PlxUtils::strCheck($title), 'text', '255', false); ?>
							</p>
<?php
if ($artId != '' and $artId != '0000') {
	$link = $plxAdmin->urlRewrite('?article' . intval($artId) . '/' . $url);
?>
							<p>
								<strong class="label-like"><?= L_LINK_FIELD ?></strong>
								<a target="_blank" href="<?= $link ?>" title="<?= L_LINK_ACCESS ?> : <?= $link ?>"><?= $link ?></a>
							</p>
<?php
}
?>
                        </div>
                        <div>
                            <input class="toggle" id="toggle_chapo" type="checkbox" <?= (empty($_GET['a']) || !empty(trim($chapo))) ? ' checked' : ''; ?>>
                            <label class="drop" for="toggle_chapo"><?= L_HEADLINE_FIELD; ?></label>
                            <div><?php PlxUtils::printArea('chapo', PlxUtils::strCheck($chapo), 0, 8); ?></div>
                        </div>
                        <div>
                            <label for="id_content"><?= L_CONTENT_FIELD ?></label>
                            <?php PlxUtils::printArea('content', PlxUtils::strCheck($content), 0, 20); ?>
                        </div>
                    </fieldset>
                    <?php eval($plxAdmin->plxPlugins->callHook('AdminArticleContent')) # Hook Plugins ?>
                    <?= PlxToken::getTokenPostMethod() ?>
                </div>
            </div>

            <!-- SIDEBAR FOR ARTICLE -->
            <div class="col-2-small-1 sidebar">
                <fieldset class="pan">
                    <div class="flex-container--column">
                        <div class="pas">
                            <label for="id_author"><?= L_AUTHOR ?></label>
<?php
	if ($_SESSION['profil'] < PROFIL_WRITER) {
		PlxUtils::printSelect('author', $_users, $author);
	} else {
?>
                            <input type="hidden" id="id_author" name="author" value="<?= $author ?>" />
                            <strong><?= PlxUtils::strCheck($plxAdmin->aUsers[$author]['name']) ?></strong>
<?php
	}
?>
                        </div>
<?php plxUtils::printDates($dates5); ?>
                        <div class="pas">
                            <label for="id_template"><?= L_TEMPLATE ?></label>
                            <?php PlxUtils::printSelect('template', $aTemplates, $template); ?>
                        </div>
                        <input class="toggle" id="toggle_url" type="checkbox">
                        <label class="drop collapsible" for="toggle_url">URL</label>
                        <div class="expander">
                            <div>
                                <label for="id_url"><?= L_URL ?></label>
                                <?php PlxUtils::printInput('url', $url, 'text', '-255'); ?>
                                <p><small><?= L_ARTICLE_URL_FIELD_TITLE ?></small></p>
                            </div>
                        </div>
                        <input class="toggle" id="toggle_categories" type="checkbox">
                        <label class="drop collapsible" for="toggle_categories">Categories</label>
                        <div class="expander">
                            <div>
                                <label><?= L_ARTICLE_CATEGORIES ?></label><br>
                                <?php $selected = (is_array($catId) and in_array('000', $catId)) ? ' checked="checked"' : ''; ?>
                                <label for="cat_unclassified"><input disabled="disabled" type="checkbox"
                                                                     id="cat_unclassified"
                                                                     name="catId[]" <?= $selected ?>
                                                                     value="000"/>&nbsp;<?= L_UNCLASSIFIED ?>
                                </label><br>
                                <?php $selected = (is_array($catId) and in_array('home', $catId)) ? ' checked="checked"' : ''; ?>
                                <label for="cat_home"><input type="checkbox" id="cat_home"
                                                             name="catId[]" <?= $selected ?>
                                                             value="home"/>&nbsp;<?= L_HOMEPAGE ?></label><br>
<?php
# on boucle sur les catégories
foreach ($plxAdmin->aCats as $cat_id => $cat_name) {
	$selected = (is_array($catId) and in_array($cat_id, $catId)) ? ' checked="checked"' : '';
	$className = !empty($plxAdmin->aCats[$cat_id]['active']) ? ' class="active"' : '';
?>
                                        <label for="cat_<?= $cat_id ?>"<?= $className ?>><input type="checkbox" id="cat_?<?= $cat_id ?>"
                                                                               name="catId[]" <?= $selected ?>
                                                                               value="<?= $cat_id ?>"/>&nbsp;<?= PlxUtils::strCheck($cat_name['name']) ?>
                                        </label><br />
<?php
}

if ($_SESSION['profil'] < PROFIL_WRITER) { ?>
                                    <label for="id_new_catname"><?= L_NEW_CATEGORY ?></label>
                                    <?php PlxUtils::printInput('new_catname', '', 'text', '-50') ?>
                                    <input class="btn" type="submit" name="new_category" value="<?= L_ADD ?>"/>
<?php
}
?>
                            </div>
                        </div>
                        <input class="toggle" id="toggle_tags" type="checkbox">
                        <label class="drop collapsible" for="toggle_tags">Tags</label>
                        <div class="expander">
                            <div>
                                <label for="tags"><?= L_ARTICLE_TAGS_FIELD; ?></label>
                                <p><small><?= L_ARTICLE_TAGS_FIELD_TITLE ?></small></p>
                                <?php PlxUtils::printInput('tags', $tags, 'text', '-255', false, false); ?>
                                <input class="toggle" id="toggle_tagslist" type="checkbox" <?= empty(trim($tags)) ? ' checked' : ''; ?>>
                                <label class="drop-inline" for="toggle_tagslist"></label>
                                <div style="margin-top: 1rem">
                                    <?php
                                    if ($plxAdmin->aTags) {
                                        $array = array();
                                        foreach ($plxAdmin->aTags as $tag) {
                                            if ($tags = array_map('trim', explode(',', $tag['tags']))) {
                                                foreach ($tags as $tag) {
                                                    if ($tag != '') {
                                                        $t = PlxUtils::urlify($tag);
                                                        if (!isset($array[$tag]))
                                                            $array[$tag] = array('url' => $t, 'count' => 1);
                                                        else
                                                            $array[$tag]['count']++;
                                                    }
                                                }
                                            }
                                        }
                                        array_multisort($array);
                                        foreach ($array as $tagname => $tag) {
                                            echo '<a href="javascript:void(0)" onclick="insTag(\'tags\',\'' . addslashes($tagname) . '\')" title="' . PlxUtils::strCheck($tagname) . ' (' . $tag['count'] . ')">' .
                                                str_replace(' ', '&nbsp;', PlxUtils::strCheck($tagname)) . '</a>&nbsp;(' . $tag['count'] . ')&nbsp; ';
                                        }
                                    } else echo L_NO_TAG;
                                    ?>
                                </div>
                            </div>
                        </div>
                        <input class="toggle" id="toggle_comments" type="checkbox">
                        <label class="drop collapsible" for="toggle_comments">Comments</label>
                        <div class="expander">
                            <div>
                                <?php if ($plxAdmin->aConf['allow_com'] == '1') : ?>
                                    <label for="id_allow_com"><?= L_ALLOW_COMMENTS ?></label>
                                    <?php PlxUtils::printSelect('allow_com', array('1' => L_YES, '0' => L_NO), $allow_com); ?>
                                <?php else: ?>
                                    <?php PlxUtils::printInput('allow_com', '0', 'hidden'); ?>
                                <?php endif; ?>
                                <?php if ($artId != '0000') : ?>
                                    <ul class="unstyled">
                                        <li>
                                            <a href="comments.php?a=<?= $artId ?>&amp;page=1"
                                               title="<?= L_ARTICLE_MANAGE_COMMENTS_TITLE ?>"><?= L_ARTICLE_MANAGE_COMMENTS ?></a>
                                            <?php
                                            // récupération du nombre de commentaires
                                            $nbComsToValidate = $plxAdmin->getNbCommentaires('/^_' . $artId . '.(.*).xml$/', 'all');
                                            $nbComsValidated = $plxAdmin->getNbCommentaires('/^' . $artId . '.(.*).xml$/', 'all');
                                            ?>
                                            <ul>
                                                <li><?= L_COMMENT_OFFLINE ?> : <a title="<?= L_NEW_COMMENTS_TITLE ?>"
                                                                                  href="comments.php?sel=offline&amp;a=<?= $artId ?>&amp;page=1"><?= $nbComsToValidate ?></a>
                                                </li>
                                                <li><?= L_COMMENT_ONLINE ?> : <a
                                                            title="<?= L_VALIDATED_COMMENTS_TITLE ?>"
                                                            href="comments.php?sel=online&amp;a=<?= $artId ?>&amp;page=1"><?= $nbComsValidated ?></a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li><a href="comment_new.php?a=<?= $artId ?>"
                                               title="<?= L_NEW_COMMENTS_TITLE ?>"><?= L_CREATE_NEW_COMMENT ?></a></li>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                        <input class="toggle" id="toggle_seo" type="checkbox">
                        <label class="drop collapsible" for="toggle_seo">SEO</label>
                        <div class="expander">
                            <div>
                                <label for="id_title_htmltag"><?= L_TITLE_HTMLTAG ?></label><br>
                                <?php PlxUtils::printInput('title_htmltag', PlxUtils::strCheck($title_htmltag), 'text', '-255'); ?>
                                <label for="id_meta_description"><?= L_ARTICLE_META_DESCRIPTION ?></label><br>
                                <?php PlxUtils::printInput('meta_description', PlxUtils::strCheck($meta_description), 'text', '-255'); ?>
                                <label for="id_meta_keywords"><?= L_ARTICLE_META_KEYWORDS ?></label><br>
                                <?php //TODO is this still used by Google ? (P3ter)
                                PlxUtils::printInput('meta_keywords', PlxUtils::strCheck($meta_keywords), 'text', '-255');
                                ?>
                            </div>
                        </div>
                        <input class="toggle" id="toggle_thumb" type="checkbox">
                        <label class="drop collapsible" for="toggle_thumb"><?= L_THUMBNAIL ?></label>
                        <div class="expander">
							<?php plxUtils::printThumbnail(!empty($_POST) ? $_POST : !empty($result) ? $result : false); ?>
                        </div>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminArticleSidebar'));
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
include 'foot.php';
