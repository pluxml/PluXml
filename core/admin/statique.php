<?php

/**
 * Edition du code source d'une page statique
 *
 * @package PLX
 * @author	Stephane F. et Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticPrepend'));

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER);

# On édite la page statique
if(!empty($_POST) AND isset($plxAdmin->aStats[$_POST['id']])) {
	$plxAdmin->editStatique($_POST);
	header('Location: statique.php?p='.$_POST['id']);
	exit;
} elseif(!empty($_GET['p'])) { # On affiche le contenu de la page
	$id = plxUtils::strCheck(plxUtils::nullbyteRemove($_GET['p']));
	if(!isset($plxAdmin->aStats[ $id ])) {
		plxMsg::Error(L_STATIC_UNKNOWN_PAGE);
		header('Location: statiques.php');
		exit;
	}
	# On récupère le contenu
	$content = trim($plxAdmin->getFileStatique($id));
	$title = $plxAdmin->aStats[$id]['name'];
	$url = $plxAdmin->aStats[$id]['url'];
	$active = $plxAdmin->aStats[$id]['active'];
	$title_htmltag = $plxAdmin->aStats[$id]['title_htmltag'];
	$meta_description = $plxAdmin->aStats[$id]['meta_description'];
	$meta_keywords = $plxAdmin->aStats[$id]['meta_keywords'];
	$template = $plxAdmin->aStats[$id]['template'];
} else { # Sinon, on redirige
	header('Location: statiques.php');
	exit;
}

# On récupère les templates des pages statiques
$files = plxGlob::getInstance(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$plxAdmin->aConf['style']);
if ($array = $files->query('/^static(-[a-z0-9-_]+)?.php$/')) {
	foreach($array as $k=>$v)
		$aTemplates[$v] = $v;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<form action="statique.php" method="post" id="form_static">

<div class="inline-form action-bar">
	<input type="submit" value="<?php echo L_STATIC_UPDATE ?>"/>&nbsp;
	<a href="<?php echo PLX_ROOT; ?>?static<?php echo intval($id); ?>/<?php echo $url; ?>"><?php echo L_STATIC_VIEW_PAGE ?> <?php echo plxUtils::strCheck($title); ?> <?php echo L_STATIC_ON_SITE ?></a>
	<p>
		<a href="statiques.php"><?php echo L_STATIC_BACK_TO_PAGE ?></a>
	</p>
</div>

<h2><?php echo L_STATIC_TITLE ?> "<?php echo plxUtils::strCheck($title); ?>"</h2>

<?php eval($plxAdmin->plxPlugins->callHook('AdminStaticTop')) # Hook Plugins ?>

	<fieldset>
		<div class="basic-form">
			<?php plxUtils::printInput('id', $id, 'hidden');?>
			<label for="id_content"><?php echo L_CONTENT_FIELD ?>&nbsp;:</label>
			<?php plxUtils::printArea('content', plxUtils::strCheck($content),140,30,false,'full-width') ?>
			<?php if($active) : ?>
		</div>
		<?php endif; ?>
		<div class="basic-form">
			<label for="id_template"><?php echo L_STATICS_TEMPLATE_FIELD ?>&nbsp;:</label>
			<?php plxUtils::printSelect('template', $aTemplates, $template) ?>
		</div>
		<div class="basic-form">
			<label for="id_title_htmltag"><?php echo L_STATIC_TITLE_HTMLTAG ?>&nbsp;:</label>
			<?php plxUtils::printInput('title_htmltag',plxUtils::strCheck($title_htmltag),'text','50-255'); ?>
		</div>
		<div class="basic-form">
			<label for="id_meta_description"><?php echo L_STATIC_META_DESCRIPTION ?>&nbsp;:</label>
			<?php plxUtils::printInput('meta_description',plxUtils::strCheck($meta_description),'text','50-255'); ?>
		</div>
		<div class="basic-form">
			<label for="id_meta_keywords"><?php echo L_STATIC_META_KEYWORDS ?>&nbsp;:</label>
			<?php plxUtils::printInput('meta_keywords',plxUtils::strCheck($meta_keywords),'text','50-255'); ?>
		</div>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminStatic')) # Hook Plugins ?>
	<?php echo plxToken::getTokenPostMethod() ?>
	
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>