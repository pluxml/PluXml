<?php

/**
 * Edition des options d'une catégorie
 *
 * @package PLX
 * @author	Stephane F.
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoryPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR, PROFIL_EDITOR);

# On édite la catégorie
if(!empty($_POST) AND isset($plxAdmin->aCats[ $_POST['id'] ])) {
	$plxAdmin->editCategorie($_POST);
	header('Location: categorie.php?p='.$_POST['id']);
	exit;
}
elseif(!empty($_GET['p'])) { # On vérifie l'existence de la catégorie
	$id = plxUtils::strCheck($_GET['p']);
	if(!isset($plxAdmin->aCats[ $id ])) {
		plxMsg::Error(L_CAT_UNKNOWN);
		header('Location: categorie.php');
		exit;
	}
} else { # Sinon, on redirige
	header('Location: categories.php');
	exit;
}

# On récupère les templates des categories
$files = plxGlob::getInstance(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$plxAdmin->aConf['style']);
if ($array = $files->query('/^categorie(-[a-z0-9-_]+)?.php$/')) {
	foreach($array as $k=>$v)
		$aTemplates[$v] = $v;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<p class="back"><a href="categorie.php"><?php echo L_EDITCAT_BACK_TO_PAGE ?></a></p>

<h2><?php echo L_EDITCAT_PAGE_TITLE ?> "<?php echo plxUtils::strCheck($plxAdmin->aCats[$id]['name']); ?>"</h2>

<?php eval($plxAdmin->plxPlugins->callHook('AdminCategoryTop')) # Hook Plugins ?>

<form action="categorie.php" method="post" id="form_category">
	<fieldset>
		<?php plxUtils::printInput('id', $id, 'hidden');?>
		<p><label for="id_homepage"><?php echo L_EDITCAT_DISPLAY_HOMEPAGE ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('homepage',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aCats[$id]['homepage']);?>
		<p id="p_content"><label for="id_content"><?php echo L_EDITCAT_DESCRIPTION ?>&nbsp;:</label></p>
		<?php plxUtils::printArea('content',plxUtils::strCheck($plxAdmin->aCats[$id]['description']),95,8) ?>
		<p><label for="id_template"><?php echo L_EDITCAT_TEMPLATE ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('template', $aTemplates, $plxAdmin->aCats[$id]['template']) ?>
		<p><label for="id_title_htmltag"><?php echo L_EDITCAT_TITLE_HTMLTAG ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('title_htmltag',plxUtils::strCheck($plxAdmin->aCats[$id]['title_htmltag']),'text','50-255'); ?>
		<p><label for="id_meta_description"><?php echo L_EDITCAT_META_DESCRIPTION ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('meta_description',plxUtils::strCheck($plxAdmin->aCats[$id]['meta_description']),'text','50-255') ?>
		<p><label for="id_meta_keywords"><?php echo L_EDITCAT_META_KEYWORDS ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('meta_keywords',plxUtils::strCheck($plxAdmin->aCats[$id]['meta_keywords']),'text','50-255') ?>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminCategory')) # Hook Plugins ?>
   	<p class="center">
		<?php echo plxToken::getTokenPostMethod() ?>
		<input class="button update" type="submit" value="<?php echo L_EDITCAT_UPDATE ?>"/>
	</p>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoryFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>