<?php

/**
 * Edition du code source d'une page statique
 *
 * @package PLX
 * @author    Stephane F., Florent MONTHEL, Jean-Pierre Pourrez "bazooka07"
 **/

include 'prepend.php';

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticPrepend'));

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_MANAGER);

const BACK_URL = 'Location: statiques.php';

# On édite la page statique
if (!empty($_POST) and preg_match('@^\d{1,3}$@', $_POST['id']) and isset($plxAdmin->aStats[$_POST['id']])) {
    $valid = true;
    # Contrôle de la validité des dates
    foreach (plxAdmin::STATIC_DATES as $k) {
        if (!plxDate::checkDate5($_POST[$k][0], $_POST[$k][1])) {
            $valid = false;
            break;
        }
    }

    if ($valid) {
        $plxAdmin->editStatique($_POST);
        header('Location: statique.php?p=' . $_POST['id']);
        exit;
    } else {
        # Erreur
        plxMsg::Error(L_BAD_DATE_FORMAT);
    }
} elseif (!empty($_GET['p'])) {
    # On affiche le contenu de la page
    $id = plxUtils::strCheck(plxUtils::nullbyteRemove($_GET['p']));
    if (!isset($plxAdmin->aStats[$id])) {
        plxMsg::Error(L_STATIC_UNKNOWN_PAGE);
        header(BACK_URL);
        exit;
    }
} else {
    # Sinon, on redirige
    header(BACK_TO_STATIC_LIST);
    exit;
}

# On récupère les templates des pages statiques
$aTemplates = $plxAdmin->getTemplatesCurrentTheme('static', L_NONE1);

# On inclut le header
include 'top.php';

# On récupère le contenu
$content = trim($plxAdmin->getFileStatique($id));
# $title = $plxAdmin->aStats[$id]['name'];
$url = $plxAdmin->urlRewrite("?static" . intval($id) . "/" . $plxAdmin->aStats[$id]['url']);
?>

    <form action="statique.php" method="post" id="form_static">
        <?= plxToken::getTokenPostMethod() ?>
        <input type="hidden" name="id" value="<?= $id ?>"/>
        <div class="adminheader">
            <div>
                <h2><?= L_STATIC_TITLE ?> "<?= plxUtils::strCheck($plxAdmin->aStats[$id]['name']); ?>"</h2>
                <p><a class="back icon-left-big" href="statiques.php"><?= L_STATIC_BACK_TO_PAGE ?></a></p>
            </div>
            <div>
                <p><a href="<?= $url ?>" target="_blank"><?= L_STATIC_VIEW_PAGE . ' ' . L_STATIC_ON_SITE ?></a></p>
                <div>
                    <button type="submit" class="btn--primary"><?= L_SAVE ?></button>
                </div>
            </div>
        </div>
        <div class="admin">
            <?php

            # Hook Plugins
            eval($plxAdmin->plxPlugins->callHook('AdminStaticTop'))

            ?>
            <fieldset>
                <div class="has-textarea">
                    <label for="id_content"><?= L_CONTENT_FIELD ?></label>
                    <textarea name="content" rows="19" id="id_content"><?= plxUtils::strCheck($content) ?></textarea>
                </div>
                <div>
                    <label class="fullwidth caption-inside">
                        <span><?= L_TEMPLATE . PHP_EOL ?></span>
                        <?php plxUtils::printSelect('template', $aTemplates, $plxAdmin->aStats[$id]['template']) ?>
                    </label>
                    <?php
                    foreach (array(

                                 'title_htmltag' => L_TITLE_HTMLTAG,
                                 'meta_description' => L_META_DESCRIPTION,
                                 'meta_keywords' => L_META_KEYWORDS,
                             ) as $field => $caption) {
                        ?>
                        <label class="fullwidth caption-inside">
                            <span><?= $caption ?></span>
                            <input type="text" name="<?= $field ?>"
                                   value="<?= plxUtils::strCheck($plxAdmin->aStats[$id][$field]) ?>"/>
                        </label>
                        <?php
                    }
                    ?>
                </div>
                <?php

                $dates5 = plxDate::date2html5($plxAdmin->aStats[$id]); # récupère les dates - version PluXml >= 6.0.0
                plxUtils::printDates($dates5);

                ?>
            </fieldset>
            <?php

            # Hook Plugins
            eval($plxAdmin->plxPlugins->callHook('AdminStatic'));

            ?>
        </div>
    </form>
<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticFoot'));

# On inclut le footer

include 'foot.php';
?>