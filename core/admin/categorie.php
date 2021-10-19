<?php

/**
 * Edition des options d'une catégorie
 *
 * @package PLX
 * @author	Stephane F.
 **/

include __DIR__ .'/prepend.php';

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

# On récupère les templates des catégories
$glob = plxGlob::getInstance(PLX_ROOT . $plxAdmin->aConf['racine_themes'] . $plxAdmin->aConf['style'], false, true, '#^categorie(?:-[\w-]+)?\.php$#');
if (!empty($glob->aFiles)) {
	$aTemplates = array();
	foreach($glob->aFiles as $v)
		$aTemplates[$v] = basename($v, '.php');
} else {
	$aTemplates = array('' => L_NONE1);
}

# On inclut le header
include __DIR__ .'/top.php';
?>

<form action="categorie.php" method="post" id="form_category">

	<div class="inline-form action-bar">
		<h2><?php echo L_EDITCAT_PAGE_TITLE ?> "<?php echo plxUtils::strCheck($plxAdmin->aCats[$id]['name']); ?>"</h2>
		<p><a class="back" href="categorie.php"><?php echo L_EDITCAT_BACK_TO_PAGE ?></a></p>
		<?php echo plxToken::getTokenPostMethod() ?>
		<input type="submit" value="<?php echo L_EDITCAT_UPDATE ?>"/>
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminCategoryTop')) # Hook Plugins ?>

	<fieldset>
		<div class="grid">
			<div class="col sml-12">
				<?php plxUtils::printInput('id', $id, 'hidden');?>
				<label for="id_homepage"><?php echo L_EDITCAT_DISPLAY_HOMEPAGE ?>&nbsp;:</label>
				<?php plxUtils::printSelect('homepage',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aCats[$id]['homepage']);?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_content"><?php echo L_EDITCAT_DESCRIPTION ?>&nbsp;:</label>
				<?php plxUtils::printArea('content',plxUtils::strCheck($plxAdmin->aCats[$id]['description']),0,8) ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_template"><?php echo L_EDITCAT_TEMPLATE ?>&nbsp;:</label>
				<?php plxUtils::printSelect('template', $aTemplates, $plxAdmin->aCats[$id]['template']) ?>
			</div>
		</div>
		<div class="grid gridthumb">
			<div class="col sml-12">
				<label for="id_thumbnail">
					<?php echo L_THUMBNAIL ?>&nbsp;:&nbsp;
					<a title="<?php echo L_THUMBNAIL_SELECTION ?>" id="toggler_thumbnail" href="javascript:void(0)" onclick="mediasManager.openPopup('id_thumbnail', true)" style="outline:none; text-decoration: none">+</a>
				</label>
				<?php plxUtils::printInput('thumbnail',plxUtils::strCheck($plxAdmin->aCats[$id]['thumbnail']),'text','255',false,'full-width','','onkeyup="refreshImg(this.value)"'); ?>
				<div class="grid" style="padding-top:10px">
					<div class="col sml-12 lrg-6">
						<label for="id_thumbnail_title"><?php echo L_THUMBNAIL_TITLE ?>&nbsp;:</label>
						<?php plxUtils::printInput('thumbnail_title',plxUtils::strCheck($plxAdmin->aCats[$id]['thumbnail_title']),'text','255-255',false,'full-width'); ?>
					</div>
					<div class="col sml-12 lrg-6">
						<label for="id_thumbnail_alt"><?php echo L_THUMBNAIL_ALT ?>&nbsp;:</label>
						<?php plxUtils::printInput('thumbnail_alt',plxUtils::strCheck($plxAdmin->aCats[$id]['thumbnail_alt']),'text','255-255',false,'full-width'); ?>
					</div>
				</div>
				<div id="id_thumbnail_img">
				<?php
				$thumbnail = $plxAdmin->aCats[$id]['thumbnail'];
				$src = false;
				if(preg_match('@^(?:https?|data):@', $thumbnail)) {
					$src = $thumbnail;
				} else {
					$src = PLX_ROOT.$thumbnail;
					$src = is_file($src) ? $src : false;
				}
				if($src) echo "<img src=\"$src\" title=\"$thumbnail\" />\n";
				?>
				</div>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_title_htmltag"><?php echo L_EDITCAT_TITLE_HTMLTAG ?>&nbsp;:</label>
				<?php plxUtils::printInput('title_htmltag',plxUtils::strCheck($plxAdmin->aCats[$id]['title_htmltag']),'text','50-255'); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_meta_description"><?php echo L_EDITCAT_META_DESCRIPTION ?>&nbsp;:</label>
				<?php plxUtils::printInput('meta_description',plxUtils::strCheck($plxAdmin->aCats[$id]['meta_description']),'text','50-255') ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_meta_keywords"><?php echo L_EDITCAT_META_KEYWORDS ?>&nbsp;:</label>
				<?php plxUtils::printInput('meta_keywords',plxUtils::strCheck($plxAdmin->aCats[$id]['meta_keywords']),'text','50-255') ?>
			</div>
		</div>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminCategory')) # Hook Plugins ?>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoryFoot'));
# On inclut le footer
include __DIR__ .'/foot.php';
?>
