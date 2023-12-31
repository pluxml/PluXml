<?php
if (!defined('PLX_ROOT')) {
    exit;
}

/*
 * Pour afficher une page de contact sur votre site, créer une page statique
 * en mettant comme contenu l'entête de la page
 * et choisir static-contact comme gabarit ou modèle (template)
 * */

const SEARCH_ALL            =  1;
const SEARCH_ART            =  2;
const SEARCH_ART_URL        =  3;
const SEARCH_ART_TITLE      =  4;
const SEARCH_ART_CHAPO      =  5;
const SEARCH_ART_CONTENT    =  6;
const SEARCH_TAG            =  7;
const SEARCH_STATIC_TITLE   =  8;
const SEARCH_STATIC_CONTENT =  9;
const SEARCH_STATIC_URL     = 10;

$scopes = array(
    // SEARCH_ALL               => 'SEARCH_ALL',
    SEARCH_ART              => 'SEARCH_ART',
    SEARCH_ART_URL          => 'SEARCH_ART_URL',
    SEARCH_ART_TITLE        => 'SEARCH_ART_TITLE',
    SEARCH_ART_CHAPO        => 'SEARCH_ART_CHAPO',
    SEARCH_ART_CONTENT      => 'SEARCH_ART_CONTENT',
    SEARCH_TAG              => 'SEARCH_TAG',
    SEARCH_STATIC_TITLE     => 'SEARCH_STATIC_TITLE',
    SEARCH_STATIC_URL       => 'SEARCH_STATIC_URL',
    SEARCH_STATIC_CONTENT   => 'SEARCH_STATIC_CONTENT',
);

if (!class_exists('plxToken')) {
    include_once PLX_CORE . 'lib/class.plx.token.php';
}

$pattern = '#.*\.(\d{8})\d{4}\..*\.xml$#';
$dates = array_map(
    function ($item) use ($pattern) {
        return preg_replace($pattern, '$1', $item);
    },
    array_values($plxMotor->plxGlob_arts->aFiles)
);
sort($dates);
$firstDate = preg_replace('#^(\d{4})(\d{2})(\d{2})$#', '$1-$2-$3', $dates[0]);
unset($dates);

if (!isset($_SESSION['search'])) {
    $_SESSION['search'] = array(
        'search'    => '',
        'scope'     => '',
        'category'  => '',
        'author'    => '',
        'from'      => $firstDate,
        'until'     => date('Y-m-d'),
    );
}

// On analyse l'envoi du formulaire
$query = filter_input(INPUT_POST, 'search', FILTER_SANITIZE_STRING);
if (is_string($query) and !empty($query)) {
    plxToken::validateFormToken();

    $_SESSION['search']['search'] = $query;

    $scope = filter_input(INPUT_POST, 'scope', FILTER_VALIDATE_INT);
    if (is_integer($scope)) {
        $_SESSION['search']['scope'] = $scope;

        if (
            !in_array(
                $scope,
                array(
                SEARCH_STATIC_TITLE,
                SEARCH_STATIC_URL,
                SEARCH_STATIC_CONTENT,
                )
            )
        ) {
            // Recherche dans les articles
            $optionDate = array(
                'options' => array(
                    'regexp' => '#^\d{4}-\d{2}-\d{2}$#',
                )
            );
            $option2 = array(
                'options' => array(
                    'regexp' => '#^\d{1,3}$#', // voir plxShow::catList()
                )
            );
            $from = filter_input(INPUT_POST, 'from', FILTER_VALIDATE_REGEXP, $optionDate);
            $until = filter_input(INPUT_POST, 'until', FILTER_VALIDATE_REGEXP, $optionDate);
            $date1 = date('Y-m-d');
            if ($until === false or strcmp($until, $date1) > 0) {
                $until = $date1;
            }
            if ($from === false or strcmp($from, $until) > 0) {
                $from = $firstDate;
            }

            $_SESSION['search']['until'] = $until;
            $_SESSION['search']['from'] = $from;

            // Suppression séparateurs de date
            $from = str_replace('-', '', $from);
            $until = str_replace('-', '', $until);

            $cat = filter_input(INPUT_POST, 'category', FILTER_VALIDATE_REGEXP, $option2);
            if ($cat === false) {
                $cats = '(?:home,\d{3},)*(?:' . $plxMotor->activeCats . ')(?:,\d{3})*';
                $_SESSION['search']['category'] = '';
            } else {
                $cats = '(?:home,\d{3},)*' . str_pad($cat, 3, '0', STR_PAD_LEFT) . '(?:,\d{3})*';
                $_SESSION['search']['category'] = $cat;
            }

            $author = filter_input(INPUT_POST, 'author', FILTER_VALIDATE_REGEXP, $option2);
            if ($author === false) {
                $author = '\d{3}';
                $_SESSION['search']['author'] = '';
            } else {
                $_SESSION['search']['author'] = $author;
            }
        }

        // Pour forcer la mémorisation de $plxMotor->motif
        $_SESSION['search']['motif'] = '';
    }
} elseif (
    !empty($_SESSION['search']['motif'])
) {
    $plxMotor->motif = $_SESSION['search']['motif'];
    $plxMotor->page = preg_match('#/page(\d+)$#', $plxMotor->get, $matches) ? $matches[1] : '1';
    $plxMotor->bypage = $plxMotor->aConf['bypage'];
    $plxMotor->getArticles(); // Les articles seront triés par la fonction
}

include 'header.php';

?>
        <header class="static-header search">
            <ul class="repertory menu breadcrumb">
                <li><a href="<?php $plxShow->racine() ?>"><?php $plxShow->lang('HOME'); ?></a></li>
                <li><strong><?php $plxShow->lang('SEARCH'); ?></strong></li>
            </ul>
            <div class="search-description">
<?php
$plxShow->staticContent();
$action = 'index.php?' . preg_replace('#/page\d+$#', '', $plxMotor->get);

// ---------- Génération du formulaire ------------------
?>
                <form method="post" id="frm-search" action="<?= $plxMotor->urlRewrite($action) ?>">
                    <?= plxToken::getTokenPostMethod() ?>
                    <div>
                        <input type="text" name="search" placeholder="<?php $plxShow->lang('SEARCH'); ?>" value="<?= $_SESSION['search']['search'] ?>" required ∕>
                    </div>
                    <div class="grid extra">
<?php
foreach (
    array(
    array('scope', $scopes),
    array('category', $plxMotor->aCats),
    array('author', $plxMotor->aUsers),
    ) as $infos
) {
    list($name, $options) = $infos;
    if (count($options) > 1) {
        $required = ($name == 'scope') ? ' required' : ''; ?>
                        <label class="col med-4">
                            <span><?php $plxShow->lang(strtoupper($name)) ?></span>
                            <select name="<?= $name ?>"<?= $required ?>>
                                <option value=""><?php $plxShow->lang('ALL_' . strtoupper($name)) ?></option>
        <?php
        foreach ($options as $id => $infos) {
            if (!is_array($infos) or (!empty($infos['active']) and empty($infos['delete']))) {
                if (!is_array($infos) or !isset($infos['menu']) or in_array($infos['menu'], array('1', 'oui'))) {
                    $caption = is_string($infos) ? $plxShow->getLang($infos) : $infos['name'];
                    $selected = ($_SESSION['search'][$name] == $id) ? ' selected' : ''; ?>
                                <option value="<?= $id ?>"<?= $selected ?>><?= $caption ?></option>
                    <?php
                }
            }
        } ?>
                            </select>
                        </label>
        <?php
    }
}
?>
                    </div>
                    <div class="grid dates">
                        <label class="col sml-6">
                            <span><?php $plxShow->lang('FROM'); ?></span>
                            <input type="date" name="from" value="<?= $_SESSION['search']['from'] ?>" />
                        </label>
                        <label class="col sml-6">
                            <span><?php $plxShow->lang('UNTIL'); ?></span>
                            <input type="date" name="until" value="<?= $_SESSION['search']['until'] ?>" />
                        </label>
                    </div>
                    <div>
                        <input type="submit" value="<?php $plxShow->lang('SEND'); ?>" class="blue" />
                    </div>
                </form>
            </div>
        </header>
<?php
if ($query !== null) {
    if ($query === false or strlen(trim($query)) === 0 or empty($scope) or !is_integer($scope)) {
        ?>
                    <p><?php $plxShow->lang('BAD_REQUEST'); ?></p>
        <?php
    } else {
        switch ($scope) {
            case SEARCH_ART_URL:
                // On n'a pas besoin de parser les articles
                $pattern = implode('\.', array(
                    '#^\d{4}', // Id article
                    $cats, // categories
                    $author,
                    '(\d{8})\d{4}', // Date publication
                    '[\w-]*' . $query . '[\w-]*', // url
                    'xml', // extension fichier
                )) . '#i';
                $artIds = array_keys(
                    array_filter(
                        $plxMotor->plxGlob_arts->aFiles,
                        function ($value) use ($pattern, $from, $until) {
                            return (
                            preg_match($pattern, $value, $matches) and
                            strcmp($matches[1], $until) <= 0 and
                            strcmp($from, $matches[1]) >= 0
                            );
                        }
                    )
                );
                if (!empty($artIds)) {
                    sort($artIds);
                    $plxMotor->bypage = $plxMotor->aConf['bypage'];
                    $plxMotor->page = 1;
                    $plxMotor->motif = '#^(?:' . implode('|', $artIds) . ')\.#';
                    $plxMotor->getArticles(); // Les articles seront triés par la fonction
                }
                break;
            case SEARCH_ART_TITLE:
                $field = 'title';
                // no break
            case SEARCH_ART_CHAPO:
                if (empty($field)) {
                    // valeur par défaut
                    $field = 'chapo';
                }
                // no break
            case SEARCH_ART_CONTENT:
            case SEARCH_ART:
                if (empty($field)) {
                    // valeur par défaut
                    $field = 'content';
                }
                // Like plxMotor::getArticles()
                $pattern = '#^\d{4}\.' . $cats . '#';
                $pattern = implode('\.', array(
                    '#^\d{4}', // Id article
                    $cats, // categories
                    $author,
                    '(\d{8})\d{4}', // Date publication
                    '[\w-]+', // url
                    'xml', // extension fichier
                )) . '#i';

                $artsList = array(); // tableau indicé avec artId
                foreach ($plxMotor->plxGlob_arts->aFiles as $artId => $filename) {
                    if (
                        preg_match($pattern, $filename, $matches) and
                        strcmp($matches[1], $until) <= 0 and
                        strcmp($from, $matches[1]) <= 0
                    ) {
                        $art = $plxMotor->parseArticle(PLX_ROOT . $plxMotor->aConf['racine_articles'] . $filename);
                        if (!empty($art)) {
                            if (
                                stripos($art[$field], $query) !== false or
                                (
                                    $scope === 2 and
                                    stripos(implode(PHP_EOL, array(
                                        $art['chapo'],
                                        $art['title'],
                                        $art['tags'],
                                        $art['meta_description'],
                                        $art['meta_keywords'],
                                        $art['title_htmltag'],
                                        $art['thumbnail_title'],
                                        $art['thumbnail_alt'],
                                    )), $query) !== false

                                )
                            ) {
                                $artsList[$artId] = $art;
                            }
                        }
                    }
                }
                // Tri par date publication
                uasort($artsList, function ($article2, $article1) {
                    return strcmp($article1['date'], $article2['date']);
                });
                $arts = array_values($artsList);
                $plxMotor->plxRecord_arts = new plxRecord($arts);

                // Mémoriser les codes articles pour la pagination
                $ids = (count($artsList) > 1) ? '(?:' . implode('|', array_keys($artsList)) . ')' : array_key_first($artsList);
                $plxMotor->motif = '#^' . $ids . '\..*\.xml#';

                break;
            case SEARCH_TAG:
                $artIds = array_keys(array_filter(
                    $plxMotor->aTags,
                    function ($value) use ($query) {
                        # fitrer avec les dates !
                        return (!empty($value['tags']) and stripos($value['tags'], $query) !== false);
                    }
                ));
                if (!empty($artIds)) {
                    $plxMotor->bypage = 1024;
                    $plxMotor->page = 1;
                    $plxMotor->motif = '#^' . implode('|', $artIds) . '\.#';
                    $plxMotor->getArticles();
                }
                break;
            case SEARCH_STATIC_TITLE:
                $field = 'name';
                // no break
            case SEARCH_STATIC_URL:
                if (empty($field)) {
                    $field = 'url';
                }
                $searchStatics = array_filter($plxMotor->aStats, function ($value) use ($field, $query) {
                    return (stripos($value[$field], $query) !== false);
                });
                break;
            case SEARCH_STATIC_CONTENT:
                $prefix = PLX_ROOT . $plxMotor->aConf['racine_statiques'];
                $searchStatics = array();
                foreach ($plxMotor->aStats as $statId => $statInfos) {
                    if (
                        stripos(
                            implode(PHP_EOL, array(
                            $statInfos['name'],
                            $statInfos['meta_description'],
                            $statInfos['meta_keywords'],
                            $statInfos['meta_keywords'],
                            )),
                            $query
                        ) !== false
                    ) {
                        $searchStatics[$statId] = $statInfos;
                    } else {
                        $content = file_get_contents($prefix . $statId . '.' . $statInfos['url'] . '.php');
                        if (stripos($content, $query) !== false) {
                            $searchStatics[$statId] = $statInfos;
                        }
                    }
                }
                break;
            default:
        }
    }
}


// On affiche le résultat de la recherche
if (!empty($plxMotor->plxRecord_arts) and $plxMotor->plxRecord_arts->size > 0) {
    if (empty($_SESSION['search']['motif'])) {
        $_SESSION['search']['motif'] = $plxMotor->motif;
    }
    $found = true;
    include 'posts.php';
    /*
    ?>
    <pre><?= $_SESSION['search']['motif'] ?></pre>
    <?php
    * */
}

if (!empty($searchStatics)) {
    $found = true; ?>
                <ul>
    <?php
    foreach ($searchStatics as $statId => $statInfos) {
        $href = $plxMotor->urlRewrite('index.php?static' . intval($statId) . '/' . $statInfos['url']); ?>
                    <li><a href="<?= $href ?>" target="_blank"><?= $statInfos['name'] ?></a></li>
        <?php
    } ?>
                </ul>
    <?php
}

if (empty($found)) {
    ?>
                <p class="txt-center warning"><?php $plxShow->lang('EMPTY_RESULT'); ?></p>
    <?php
}

include 'footer.php';
