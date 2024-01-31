<?php

/**
 * Edition des paramètres d'affichage
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if(!empty($_POST)) {
	$_POST['feed_footer'] = $_POST['content'];
	$_POST['images_l'] = plxUtils::getValue($_POST['images_l'], 800);
	$_POST['images_h'] = plxUtils::getValue($_POST['images_h'], 600);
	$_POST['miniatures_l'] = plxUtils::getValue($_POST['miniatures_l'], 200);
	$_POST['miniatures_h'] = plxUtils::getValue($_POST['miniatures_h'], 100);
	unset($_POST['content']);
	$plxAdmin->editConfiguration($plxAdmin->aConf, $_POST);
	header('Location: parametres_affichage.php');
	exit;
}

# On récupère les templates de la page d'accueil
$glob = plxGlob::getInstance(PLX_ROOT . $plxAdmin->aConf['racine_themes'] . $plxAdmin->aConf['style'], false, true, '#home(?:-[\w-]+)?\.php$#');
if (!empty($glob->aFiles)) {
	$aTemplates = array();
	foreach($glob->aFiles as $v)
		$aTemplates[$v] = basename($v, '.php');
} else {
	$aTemplates = array('' => L_NONE1);
}

# Tableau du tri
$aTriArts = array(
	'desc'		=> L_SORT_DESCENDING_DATE,
	'asc'		=> L_SORT_ASCENDING_DATE,
	'alpha'		=> L_SORT_ALPHABETICAL,
	'ralpha'	=> L_SORT_REVERSE_ALPHABETICAL,
	'random'	=> L_SORT_RANDOM
);

$aTriComs = array(
	'desc'=>L_SORT_DESCENDING_DATE,
	'asc'=>L_SORT_ASCENDING_DATE
);

# On va tester les variables pour les images et miniatures
if(!is_numeric($plxAdmin->aConf['images_l'])) $plxAdmin->aConf['images_l'] = 800;
if(!is_numeric($plxAdmin->aConf['images_h'])) $plxAdmin->aConf['images_h'] = 600;
if(!is_numeric($plxAdmin->aConf['miniatures_l'])) $plxAdmin->aConf['miniatures_l'] = 200;
if(!is_numeric($plxAdmin->aConf['miniatures_h'])) $plxAdmin->aConf['miniatures_h'] = 100;

# On inclut le header
include 'top.php';
?>

<form action="parametres_affichage.php" method="post" id="form_settings">

	<div class="inline-form action-bar">
		<h2><?= L_CONFIG_VIEW_FIELD ?></h2>
		<p><?= L_CONFIG_VIEW_PLUXML_RESSOURCES ?></p>
		<input type="submit" value="<?= L_CONFIG_VIEW_UPDATE ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsDisplayTop')) # Hook Plugins ?>

	<fieldset>

		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_hometemplate"><?= L_CONFIG_HOMETEMPLATE ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('hometemplate', $aTemplates, $plxAdmin->aConf['hometemplate']) ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_tri"><?= L_CONFIG_VIEW_SORT ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('tri', $aTriArts, $plxAdmin->aConf['tri']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_bypage"><?= L_CONFIG_VIEW_BYPAGE ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('bypage', $plxAdmin->aConf['bypage'], 'text', '2-4', false, 'fieldnum'); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_bypage"><?= L_CONFIG_VIEW_BY_HOMEPAGE ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('byhomepage', $plxAdmin->aConf['byhomepage'], 'text', '2-4', false, 'fieldnum'); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_bypage_archives"><?= L_CONFIG_VIEW_BYPAGE_TAGS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('bypage_tags', $plxAdmin->aConf['bypage_tags'], 'text', '2-4', false, 'fieldnum'); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_bypage_archives"><?= L_CONFIG_VIEW_BYPAGE_ARCHIVES ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('bypage_archives', $plxAdmin->aConf['bypage_archives'], 'text', '2-4', false, 'fieldnum'); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_bypage_admin"><?= L_CONFIG_VIEW_BYPAGE_ADMIN ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('bypage_admin', $plxAdmin->aConf['bypage_admin'], 'text', '2-4', false, 'fieldnum'); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_tri_coms"><?= L_CONFIG_VIEW_SORT_COMS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('tri_coms', $aTriComs, $plxAdmin->aConf['tri_coms']); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_bypage_admin_coms"><?= L_CONFIG_VIEW_BYPAGE_ADMIN_COMS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('bypage_admin_coms', $plxAdmin->aConf['bypage_admin_coms'], 'text', '2-4', false, 'fieldnum'); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_display_empty_cat"><?= L_CONFIG_VIEW_DISPLAY_EMPTY_CAT ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('display_empty_cat', array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['display_empty_cat']);?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label><?= L_CONFIG_VIEW_IMAGES ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('images_l', $plxAdmin->aConf['images_l'], 'text', '4-4', false, 'no-margin'); ?>
				&nbsp;x&nbsp;
				<?php plxUtils::printInput('images_h', $plxAdmin->aConf['images_h'], 'text', '4-4', false, 'no-margin'); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label><?= L_CONFIG_VIEW_THUMBS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('miniatures_l', $plxAdmin->aConf['miniatures_l'], 'text', '4-4', false, 'no-margin'); ?>
				&nbsp;x&nbsp;
				<?php plxUtils::printInput('miniatures_h', $plxAdmin->aConf['miniatures_h'], 'text', '4-4', false, 'no-margin'); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_thumbs"><?= L_MEDIAS_THUMBS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('thumbs', array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['thumbs']);?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_bypage_feed"><?= L_CONFIG_VIEW_BYPAGE_FEEDS ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printInput('bypage_feed', $plxAdmin->aConf['bypage_feed'], 'text', '2-2', false, 'fieldnum'); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12 med-5 label-centered">
				<label for="id_feed_chapo"><?= L_CONFIG_VIEW_FEEDS_HEADLINE ?>&nbsp;:</label>
			</div>
			<div class="col sml-12 med-7">
				<?php plxUtils::printSelect('feed_chapo', array('1'=>L_YES, '0'=>L_NO), $plxAdmin->aConf['feed_chapo']);?>
				<a class="hint"><span><?= L_CONFIG_VIEW_FEEDS_HEADLINE_HELP ?></span></a>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_content"><?= L_CONFIG_VIEW_FEEDS_FOOTER ?>&nbsp;:</label>
				<?php plxUtils::printArea('content', plxUtils::strCheck($plxAdmin->aConf['feed_footer']), 140, 5, false, 'full-width', 'placeholder=" "'); ?>
			</div>
		</div>

	</fieldset>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsDisplay')) # Hook Plugins ?>
	<?= plxToken::getTokenPostMethod() ?>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsDisplayFoot'));

# On inclut le footer
include 'foot.php';
