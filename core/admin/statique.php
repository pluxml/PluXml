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

	$valid=true;
	# Vérification de la validité de la date de création
	if(!plxDate::checkDate($_POST['date_creation_day'],$_POST['date_creation_month'],$_POST['date_creation_year'],$_POST['date_creation_time'])) {
		$valid = plxMsg::Error(L_ERR_INVALID_DATE_CREATION) AND $valid;
	}
	# Vérification de la validité de la date de mise à jour
	if(!plxDate::checkDate($_POST['date_update_day'],$_POST['date_update_month'],$_POST['date_update_year'],$_POST['date_update_time'])) {
		$valid = plxMsg::Error(L_ERR_INVALID_DATE_UPDATE) AND $valid;
	}
	if($valid) $plxAdmin->editStatique($_POST);
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
	$date_creation = plxDate::date2Array($plxAdmin->aStats[$id]['date_creation']);
	$date_update = plxDate::date2Array($plxAdmin->aStats[$id]['date_update']);
} else { # Sinon, on redirige
	header('Location: statiques.php');
	exit;
}

# On récupère les templates des pages statiques
$aTemplates = array();
$files = plxGlob::getInstance(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$plxAdmin->aConf['style']);
if ($array = $files->query('/^static(-[a-z0-9-_]+)?.php$/')) {
	foreach($array as $k=>$v)
		$aTemplates[$v] = $v;
}
if(empty($aTemplates)) $aTemplates[''] = L_NONE1;

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<form action="statique.php" method="post" id="form_static">

	<div class="inline-form action-bar">
		<h2><?php echo L_STATIC_TITLE ?> "<?php echo plxUtils::strCheck($title); ?>"</h2>
		<p><a class="back" href="statiques.php"><?php echo L_STATIC_BACK_TO_PAGE ?></a></p>
		<input type="submit" value="<?php echo L_STATIC_UPDATE ?>"/>&nbsp;
		<a href="<?php echo PLX_ROOT; ?>?static<?php echo intval($id); ?>/<?php echo $url; ?>"><?php echo L_STATIC_VIEW_PAGE ?> <?php echo plxUtils::strCheck($title); ?> <?php echo L_STATIC_ON_SITE ?></a>
		<?php plxUtils::printInput('id', $id, 'hidden');?>
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminStaticTop')) # Hook Plugins ?>

		<fieldset>
			<div class="grid">
				<div class="col sml-12">
					<label for="id_content"><?php echo L_CONTENT_FIELD ?>&nbsp;:</label>
					<?php plxUtils::printArea('content', plxUtils::strCheck($content),140,30,false,'full-width') ?>
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12">
					<label for="id_template"><?php echo L_STATICS_TEMPLATE_FIELD ?>&nbsp;:</label>
					<?php plxUtils::printSelect('template', $aTemplates, $template) ?>
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12">
					<label for="id_title_htmltag"><?php echo L_STATIC_TITLE_HTMLTAG ?>&nbsp;:</label>
					<?php plxUtils::printInput('title_htmltag',plxUtils::strCheck($title_htmltag),'text','50-255'); ?>
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12">
					<label for="id_meta_description"><?php echo L_STATIC_META_DESCRIPTION ?>&nbsp;:</label>
					<?php plxUtils::printInput('meta_description',plxUtils::strCheck($meta_description),'text','50-255'); ?>
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12">
					<label for="id_meta_keywords"><?php echo L_STATIC_META_KEYWORDS ?>&nbsp;:</label>
					<?php plxUtils::printInput('meta_keywords',plxUtils::strCheck($meta_keywords),'text','50-255'); ?>
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12">
					<label><?php echo L_DATE_CREATION ?>&nbsp;:</label>
					<div class="inline-form">
						<?php plxUtils::printInput('date_creation_day',$date_creation['day'],'text','2-2',false,'day'); ?>
						<?php plxUtils::printInput('date_creation_month',$date_creation['month'],'text','2-2',false,'month'); ?>
						<?php plxUtils::printInput('date_creation_year',$date_creation['year'],'text','2-4',false,'year'); ?>
						<?php plxUtils::printInput('date_creation_time',$date_creation['time'],'text','2-5',false,'time'); ?>
						<a class="ico_cal" href="javascript:void(0)" onclick="dateNow('date_creation', <?php echo date('Z') ?>); return false;" title="<?php L_NOW; ?>">
							<img src="theme/images/date.png" alt="calendar" />
						</a>
					</div>
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12">
					<?php plxUtils::printInput('date_update', $plxAdmin->aStats[$id]['date_update'], 'hidden');?>
					<label><?php echo L_DATE_UPDATE ?>&nbsp;:</label>
					<div class="inline-form">
						<?php plxUtils::printInput('date_update_day',$date_update['day'],'text','2-2',false,'day'); ?>
						<?php plxUtils::printInput('date_update_month',$date_update['month'],'text','2-2',false,'month'); ?>
						<?php plxUtils::printInput('date_update_year',$date_update['year'],'text','2-4',false,'year'); ?>
						<?php plxUtils::printInput('date_update_time',$date_update['time'],'text','2-5',false,'time'); ?>
						<a class="ico_cal" href="javascript:void(0)" onclick="dateNow('date_update', <?php echo date('Z') ?>); return false;" title="<?php L_NOW; ?>">
							<img src="theme/images/date.png" alt="calendar" />
						</a>
					</div>
				</div>
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