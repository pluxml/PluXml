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
$tpl = isset($_POST['tpl'])?$_POST['tpl']:'home.php';
if(!empty($_POST['load'])) $tpl = $_POST['template'];

$style = $plxAdmin->aConf['style'];
$filename = realpath(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$style.'/'.$tpl);
if(!preg_match('#^'.str_replace('\\', '/', realpath(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$style.'/').'#'), str_replace('\\', '/', $filename))) {
	$tpl='home.php';
}
$filename = realpath(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$style.'/'.$tpl);

# On teste l'existence du thème
if(empty($style) OR !is_dir(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$style)) {
	plxMsg::Error(L_CONFIG_EDITTPL_ERROR_NOTHEME);
	header('Location: parametres_affichage.php');
	exit;
}

# Traitement du formulaire: sauvegarde du template
if(isset($_POST['submit']) AND trim($_POST['content']) != '') {
	if(plxUtils::write($_POST['content'], $filename))
		plxMsg::Info(L_SAVE_FILE_SUCCESSFULLY);
	else
		plxMsg::Error(L_SAVE_FILE_ERROR);
}

# On récupère les fichiers templates du thèmes
$aTemplates=array();
function listFolderFiles($dir, $include, $root=''){
	$content = array();
	$ffs = scandir($dir);
	foreach($ffs as $ff){
		if($ff!='.' && $ff!='..') {
			$ext = strtolower(strrchr($ff,'.'));
			if(!is_dir($dir.'/'.$ff) AND is_array($include) AND in_array($ext,$include)) {
				$f = str_replace($root, "", PLX_ROOT.ltrim($dir.'/'.$ff,'./'));
				$content[$f] = $f;
			}
			if(is_dir($dir.'/'.$ff))
				$content = array_merge($content, listFolderFiles($dir.'/'.$ff,$include,$root));
		}
	}
	return $content;
}
$root = PLX_ROOT.$plxAdmin->aConf['racine_themes'].$style;
$aTemplates=listFolderFiles($root, array('.php','.css','.htm','.html','.txt','.js','.xml'), $root);

# On récupère le contenu du fichier template
$content = '';
if(file_exists($filename) AND filesize($filename) > 0) {
	if($f = fopen($filename, 'r')) {
		$content = fread($f, filesize($filename));
		fclose($f);
	}
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