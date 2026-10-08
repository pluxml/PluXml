<?php
/**
 * Edition des fichiers templates du thème en vigueur
 * @package PLX
 * @author	Stephane F
 **/

require 'prepend.php';

if(!isset($plxAdmin)) {
	redirectLogin();
}

# Initialisation
$tpl = isset($_POST['tpl']) ? $_POST['tpl'] : 'home.php';
if(!empty($_POST['load'])) $tpl = $_POST['template'];

# On teste l'existence du thème
$style = $plxAdmin->aConf['style'];
$style_folder = PLX_ROOT . $plxAdmin->aConf['racine_themes'] . $style . '/';
if(empty($style) OR !file_exists($style_folder . 'home.php')) {
	plxMsg::Error(L_CONFIG_EDITTPL_ERROR_NOTHEME);
	redirectAdmin();
}

$filename = realpath($style_folder . $tpl);
if(strpos($filename, realpath($style_folder)) !== 0) {
	$tpl='home.php';
	$filename = realpath($style_folder . $tpl);
} else {
	# Traitement du formulaire: sauvegarde du template
	if(isset($_POST['submit']) AND trim($_POST['content']) != '') {
		# Vérifie si le template contient des fonctions critiques de PHP
		if(preg_match('#\.php$#', $tpl) and preg_match(plxAdmin::CRITICAL_FUNCTIONS_PHP_PATTERN, $_POST['content'], $matches)) {
			error_log('use of ' . $matches[1] . ' from PHP banned in template');
			plxMsg::Error(L_PHP_ERROR_LOG);
		} elseif(plxUtils::write($_POST['content'], $filename)) {
			plxMsg::Info(L_SAVE_FILE_SUCCESSFULLY);
		} else {
			plxMsg::Error(L_SAVE_FILE_ERROR);
		}

		reloadPage();
	}
}

# On récupère le contenu du fichier template
$content = file_get_contents($filename);
if($content === false) {
	$content = '';
}

# On inclut le header
require 'top.php';

?>
<form method="post" id="form_edittpl">
	<?= plxToken::getTokenPostMethod() ?>
	<div class="inline-form action-bar">
		<h2><?= L_CONFIG_EDITTPL_TITLE ?> &laquo;<?= plxUtils::strCheck($style) ?>&raquo;</h2>
		<p><?= L_CONFIG_VIEW_PLUXML_RESSOURCES ?></p>
		<?php plxUtils::printSelectDir('template', $tpl, PLX_ROOT.$plxAdmin->aConf['racine_themes'].$style, 'no-margin', false) ?>
		<input name="load" type="submit" value="<?= L_CONFIG_EDITTPL_LOAD ?>" />
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		<input name="submit" type="submit" value="<?= L_SAVE_FILE ?>" />
	</div>
<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsEdittplTop')) # Hook Plugins ?>
	<div class="grid">
		<div class="col sml-12">
			<label for="id_content"><?= L_CONTENT_FIELD ?>&nbsp;:</label>
			<?php plxUtils::printInput('tpl', $tpl,'hidden'); ?>
			<?php plxUtils::printArea('content', $content, 0, 20); ?>
<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsEdittpl')) # Hook Plugins ?>
		</div>
	</div>
</form>

<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsEdittplFoot'));

# On inclut le footer
require 'foot.php';
