<?php
/**
 * Edition des fichiers templates du thème en vigueur
 * @package PLX
 * @author	Stephane F, J.P. Pourrez @bazooka07
 **/

include 'prepend.php';

# extensions autorisées pour les templates
const TEMPLATE_EXTS_PATTERN = '#(?:php|css|html?|txt|js|xml)#i';

# Controle du token du formulaire
plxToken::validateFormToken($_POST);

# Controle de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On récupère tous les fichiers du dossier $dir, y compris dans les sous-dossiers, avec l'extension compatible avec TEMPLATE_EXTS_PATTERN
function listFolderFiles($dir, $firstLevel = true) {
	static $content = null;
	static $root = false;
	static $offset = 0;

	if($firstLevel) {
		# First step
		$content= array();
		$root = $dir;
		$offset = strlen($dir);
	}

	$ffs = scandir($dir); # may return false;
	if(empty($ffs)) {
		return $content;
	}

	foreach($ffs as $ff){
		# On ignore les fichiers/dossiers cachés et les "dossiers" . et ..
		if($ff[0] == '.') {
			continue;
		}

		$filename = $dir . $ff;
		if(!is_dir($filename)) {
			if(preg_match(TEMPLATE_EXTS_PATTERN, pathinfo($ff, PATHINFO_EXTENSION))) {
				$content[] = substr($filename, $offset);
			}
		} else {
			# appel récursif de la fonction
			listFolderFiles($filename . '/', false);
		}
	}

	if($firstLevel) {
		return $content;
	}
}

function printSelectDir($name, $values, $selected, $readonly=false, $class='', $id=true) {
?>
<select id="id_<?= $name ?>" name="<?= $name ?>" class="<?= $class ?>">
<?php
	$suffixe = array(
		false => '├ ',
		true  => '└ ',
	);
	$ccPath = '.';
	$level = 0;
	$spacer = '';
	$lastItem = 0;
	$cnt = array();
	$i = 0;
	foreach($values as $f) {
		$path = pathinfo($f,  PATHINFO_DIRNAME);
		if($ccPath != $path) {
			$i = 0;
			$level = count(explode('/', $f)) - 1;
			if($level > 0) {
				# On compte le nombre d'éléments dans ce dossier
				$cnt[$level] = count(array_filter($values, function($filename) use($path) {
					return preg_match('#^' . $path . '\b#', $filename);
				}));
				# gère le cas où le groupe précèdent n'a qu'un élément
				$spacerGroup = ($level > 1) ? str_repeat(' ', ($level -1) * 4) . '├ ' : ''; # espaces insécables !
?>
	<optgroup label="<?= $spacerGroup . basename($path) ?>" data-level="<?= $level -1 ?>" data-count="<?= $cnt[$level] ?>"></optgroup>
<?php
				$spacer = str_repeat(' ', $level * 4); # espaces insécables !
			} else {
				$spacer = '';
			}
			$ccPath = $path;
		}

		$i++;
		$suff = ($level > 0) ? $suffixe[$cnt[$level] == $i] : '';
?>
	<option value="<?= $f ?>" <?= ($f == $selected) ? 'selected' : '' ?> data-level="<?= $level ?>"><?= $spacer . $suff . basename($f) ?></option>
<?php
	}
?>
</select>
<?php
}

$style = $plxAdmin->aConf['style'];
$root = PLX_ROOT . $plxAdmin->aConf['racine_themes'] . $style . '/';
$aTemplates = listFolderFiles($root, true);

# Initialisation
if(isset($_POST['load'])) {
	$tpl = $_POST['template'];
} elseif(isset($_POST['save'])) {
	$tpl = $_POST['tpl'];
} else {
	$tpl = !empty($_SESSION['template_style']) ? $_SESSION['template_style'] : 'home.php';
	# Avoid infinite loop if not home.php in theme
	if(!in_array($tpl, $aTemplates)) {
		$tpl = $aTemplates[0];
	}
}

# Contrôle du template sélectionné par <select>
if(!in_array($tpl, $aTemplates)) {
	plxMsg::Error(L_CONFIG_EDITTPL_ERROR_NOTHEME);
	header('Location: parametres_themes.php');
	exit;
}

# Remember the choice through others pages of parameters
$_SESSION['template_style'] = $tpl;

$filename = realpath($root . $tpl);

# Traitement du formulaire: sauvegarde du template
if(isset($_POST['save']) AND !empty(trim($_POST['content']))) {
	if(plxUtils::write($_POST['content'], $filename))
		plxMsg::Info(L_SAVE_FILE_SUCCESSFULLY);
	else
		plxMsg::Error(L_SAVE_FILE_ERROR);
}

# On récupère le contenu du fichier template
$content = file_get_contents($filename);

# On inclut le header
include 'top.php';
?>
<form method="post" id="form_edittpl">
	<?= plxToken::getTokenPostMethod() ?>
	<div class="inline-form action-bar">
		<h2><?= L_CONFIG_EDITTPL_TITLE ?> &laquo;<?= plxUtils::strCheck($style) ?>&raquo;</h2>
		<p><?php printf(L_CONFIG_VIEW_PLUXML_RESSOURCES, PLX_RESSOURCES_THEMES_LINK); ?></p>
		<?php printSelectDir('template', $aTemplates, $tpl); ?>
		<input name="load" type="submit" value="<?= L_CONFIG_EDITTPL_LOAD ?>" />
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		<input name="save" type="submit" value="<?= L_SAVE_FILE ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsEdittplTop')); # Hook Plugins ?>

	<div class="grid">
		<div class="col sml-12">
			<?php plxUtils::printInput('tpl', plxUtils::strCheck($tpl), 'hidden'); ?>
			<label for="id_content"><?= L_CONTENT_FIELD ?>&nbsp;:</label>
			<?php plxUtils::printArea('content', plxUtils::strCheck($content), 0, 20, false, 'full-width', 'placeholder=" "'); ?>

			<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsEdittpl')); # Hook Plugins ?>

		</div>
	</div>

</form>
<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsEdittplFoot'));

# On inclut le footer
include 'foot.php';
