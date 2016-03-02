<?php
/**
 * Edition des fichiers templates du thème en vigueur
 * @package PLX
 * @author	Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# Initialisation
if (isset($_POST['load'])) {
	$tpl = $_POST['template'];
} else {
	$tpl = isset($_POST['tpl']) ? $_POST['tpl'] : 'home.php';
}

$style = $plxAdmin->aConf['style'];
# On teste l'existence du thème
if(empty($style) OR !is_dir(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$style)) {
	plxMsg::Error(L_CONFIG_EDITTPL_ERROR_NOTHEME);
	header('Location: parametres_affichage.php');
	exit;
}

$root_theme = PLX_ROOT.$plxAdmin->aConf['racine_themes'].$style.'/';
$filename = $root_theme.$tpl;

/*
if(!preg_match('#^'.str_replace('\\', '/', $root_theme.'#'), str_replace('\\', '/', $filename))) {
	$tpl='home.php';
	$filename = $root_theme.$tpl;
}
* */

# Traitement du formulaire: sauvegarde du template
if(isset($_POST['submit']) AND trim($_POST['content']) != '') {
	if(plxUtils::write($_POST['content'], $filename)) {
		plxMsg::Info(L_SAVE_FILE_SUCCESSFULLY);
	}
	else {
		plxMsg::Error(L_SAVE_FILE_ERROR);
	}
}

# On récupère les fichiers templates du thèmes

function listFolderFiles($dir, $include){
	global $root_theme;

	$content = [];
	$motif = $dir.'*'.$include;
	$ffs = glob($motif, GLOB_BRACE | GLOB_MARK);
	foreach($ffs as $ff){
		if (is_dir($ff)) {
			$content = array_merge($content, listFolderFiles($ff, $include));
		} else {
			$shortName = substr($ff, strlen($root_theme));
			$content[$shortName] = $shortName;
		}
	}
	return $content;
}

$aTemplates=listFolderFiles($root_theme, '{'.implode(',', array('.php','.css','.htm','.html','.txt','.js','.xml')).'}');

# On récupère le contenu du fichier template
$content = '';
if(is_readable($filename) AND filesize($filename) > 0) {
	$content = file_get_contents($filename);
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>
<form action="parametres_edittpl.php" method="post" id="form_edittpl">

	<div class="inline-form action-bar">
		<h2><?php echo L_CONFIG_EDITTPL_TITLE ?> &laquo;<?php echo plxUtils::strCheck($style) ?>&raquo;</h2>
		<p><?php echo L_CONFIG_VIEW_PLUXML_RESSOURCES ?></p>
		<?php echo plxToken::getTokenPostMethod() ?>
		<?php plxUtils::printSelect('template', $aTemplates, $tpl); ?>
		<input name="load" type="submit" value="<?php echo L_CONFIG_EDITTPL_LOAD ?>" />
		&nbsp;&nbsp;&nbsp;
		<input name="submit" type="submit" value="<?php echo L_SAVE_FILE ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsEdittplTop')) # Hook Plugins ?>

	<div class="grid">
		<div class="col sml-12">
			<label for="id_content"><?php echo L_CONTENT_FIELD ?>&nbsp;:</label>
			<?php plxUtils::printInput('tpl',plxUtils::strCheck($tpl),'hidden'); ?>
			<?php plxUtils::printArea('content',plxUtils::strCheck($content),60,20,false,'full-width'); ?>
			<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsEdittpl')) # Hook Plugins ?>
		</div>
	</div>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsEdittplFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>