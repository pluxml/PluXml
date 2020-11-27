<?php

/**
 * Edition du code source d'une page statique
 *
 * @package PLX
 * @author    Stephane F. et Florent MONTHEL
 **/

include 'prepend.php';

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticPrepend'));

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_MANAGER);

const BACK_TO_STATIC_LIST = 'Location: statiques.php';

# On édite la page statique
if (!empty($_POST) and isset($plxAdmin->aStats[$_POST['id']])) {

    $valid = true;
	# Contrôle de la validité des dates
	foreach(plxAdmin::STATIC_DATES as $k) {
		if(!plxDate::checkDate5($_POST[$k][0], $_POST[$k][1])) {
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
} elseif (!empty($_GET['p'])) { # On affiche le contenu de la page
    $id = plxUtils::strCheck(plxUtils::nullbyteRemove($_GET['p']));
    if (!isset($plxAdmin->aStats[$id])) {
        plxMsg::Error(L_STATIC_UNKNOWN_PAGE);
        header(BACK_TO_STATIC_LIST);
        exit;
    }
    # On récupère le contenu
    $content = trim($plxAdmin->getFileStatique($id));
    $title = $plxAdmin->aStats[$id]['name'];
    $url = $plxAdmin->urlRewrite("?static" . intval($id) . "/" . $plxAdmin->aStats[$id]['url']);
    $active = $plxAdmin->aStats[$id]['active'];
    $title_htmltag = $plxAdmin->aStats[$id]['title_htmltag'];
    $meta_description = $plxAdmin->aStats[$id]['meta_description'];
    $meta_keywords = $plxAdmin->aStats[$id]['meta_keywords'];
    $template = $plxAdmin->aStats[$id]['template'];
	$dates5 = plxDate::date2html5($plxAdmin->aStats[$id]); # récupère les dates - version PluXml >= 6.0.0
} else { # Sinon, on redirige
    header(BACK_TO_STATIC_LIST);
    exit;
}

# On récupère les templates des pages statiques
$aTemplates = array();
$files = plxGlob::getInstance(PLX_ROOT . $plxAdmin->aConf['racine_themes'] . $plxAdmin->aConf['style']);
if ($array = $files->query('/^static(-[\w-]+)?\.php$/')) {
    foreach ($array as $k => $v)
        $aTemplates[$v] = $v;
}
if (empty($aTemplates)) {
    $aTemplates[''] = L_NONE1;
} else {
    asort($aTemplates);
}

# On inclut le header
include 'top.php';
?>

<form action="statique.php" method="post" id="form_static">
    <?= plxToken::getTokenPostMethod() ?>
    <?php plxUtils::printInput('id', $id, 'hidden'); ?>
    <div class="adminheader grid-6">
        <div class="col-4">
            <h2><?= L_STATIC_TITLE ?> "<?= plxUtils::strCheck($title); ?>"</h2>
            <p><a class="back" href="statiques.php"><?= L_STATIC_BACK_TO_PAGE ?></a></p>
        </div>
        <div class="col-2 mtm txtright">
            <button type="submit" class="btn--primary"><?= L_SAVE ?></button>
            <p><a href="<?= $url ?>"><?= L_STATIC_VIEW_PAGE ?>&nbsp;<?= plxUtils::strCheck($title); ?>
                    &nbsp;<?= L_STATIC_ON_SITE ?></a></p>
        </div>
    </div>
    <div class="admin mtm">
<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticTop'))

?>
        <fieldset>
			<div>
				<label for="id_content"><?= L_CONTENT_FIELD ?></label>
				<?php plxUtils::printArea('content', plxUtils::strCheck($content), 0, 30) ?>
			</div>
			<div class="has-label">
				<label for="id_template"><?= L_TEMPLATE ?></label>
<?php plxUtils::printSelect('template', $aTemplates, $template) ?>
			</div>
			<div class="fullwidth">
				<label for="id_title_htmltag"><?= L_TITLE_HTMLTAG ?></label>
				<?php plxUtils::printInput('title_htmltag', plxUtils::strCheck($title_htmltag), 'text', '50-255'); ?>
			</div>
			<div class="fullwidth">
				<label for="id_meta_description"><?= L_STATIC_META_DESCRIPTION ?></label>
				<?php plxUtils::printInput('meta_description', plxUtils::strCheck($meta_description), 'text', '50-255'); ?>
			</div>
			<div class="fullwidth">
				<label for="id_meta_keywords"><?= L_STATIC_META_KEYWORDS ?></label>
				<?php plxUtils::printInput('meta_keywords', plxUtils::strCheck($meta_keywords), 'text', '50-255'); ?>
			</div>
            <div id="calendar">
<?php
	# HTML5 : on utilise <input type="date" /> et <input type="time" />
	# Requis ci-dessous : array_keys($dateTitles) == plxDate::ENTRIES
	foreach($dates5 as $k=>$infos) {
?>
				<div>
					<label><?= DATE_TITLES[$k] ?></label><br>
					<input type="date" name="<?= $k ?>[0]" value="<?= $infos[0] ?>" />
					<input type="time" name="<?= $k ?>[1]" value="<?= $infos[1] ?>" />
					<i class="icon-calendar" title="<?= L_NOW ?>" data-datetime5="<?= $k ?>"></i>
				</div>
<?php
	}
?>
            </div>
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
