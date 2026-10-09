<?php

/**
 * Affichage de l'écran de gestion du code css d'un plugin
 *
 * @package PLX
 * @author	Stephane F
 **/
require 'prepend.php';

if(!isset($plxAdmin)) {
	redirectLogin();
}

if(!isset($_GET['p'])) {
	plxMsg::Error(L_NO_ENTRY);
	redirectAdmin();
}

const CSS_FIELDNAMES = array(
	'site' => L_CONTENT_FIELD_FRONTEND,
	'admin' => L_CONTENT_FIELD_BACKEND,
);


$plugin = plxUtils::nullbyteRemove(urldecode($_GET['p']));
$path1 = PLX_ROOT . PLX_CONFIG_PATH . 'plugins/' . $plugin;
# Traitement du formulaire: sauvegarde du code css et regénération du cache
if(isset($_POST['submit'])) {
	$success = true;
 	foreach(array_keys(CSS_FIELDNAMES) as $k) {
		$content = trim($_POST[$k]);
		$filename = $path1 . '.' . $k . '.css';
		if(empty($content)) {
			# Si le fichier existe effacer le et regénérér le cache
			if(is_file($filename)) {
				unlink($filename);
				if(!$plxAdmin->plxPlugins->cssCache($k)) {
					$success = false;
					break;
				}
			}
			continue;
		}
		# vérifier si changement avec le fichier existant
		$old_content = trim(file_get_contents($filename));
		if($content != $old_content) {
			if(!plxUtils::write($content, $filename) or !$plxAdmin->plxPlugins->cssCache($k)) {
				$success = false;
				break;
			}
		}
	}

 	if($success) {
		plxMsg::Info(L_SAVE_FILE_SUCCESSFULLY);
	} else {
		plxMsg::Error(L_SAVE_FILE_ERROR);
	}
	reloadPage();
}

# On inclut le header
require 'top.php';
?>
<form method="post" id="form_file">
	<div class="inline-form action-bar">
		<h2><?= plxUtils::strCheck($plugin) ?></h2>
		<p><a class="back" href="parametres_plugins.php"><?= L_BACK_TO_PLUGINS ?></a></p>
		<input name="submit" type="submit" value="<?= L_SAVE_FILE ?>" />
	</div>

	<fieldset>
<?php
foreach(CSS_FIELDNAMES as $fieldname=>$caption) {
	$filename = $path1 . '.' . $fieldname . '.css';
	$value = is_file($filename) ? trim(file_get_contents($filename)) : '';
	if(empty($value)) {
		# On récupère le css original du plugin
		$filename = PLX_PLUGINS . $plugin . '/css/' . $fieldname . '.css';
		if(is_file($filename)) {
			$value = trim(file_get_contents($filename));
		}
	}
?>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_<?= $fieldname ?>"><?= $caption ?>&nbsp;:</label>
				<?php plxUtils::printArea($fieldname, plxUtils::strCheck($value), 0, 20); ?>
			</div>
		</div>
<?php
}

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminPluginCss'));
?>
		<?= plxToken::getTokenPostMethod() ?>
	</fieldset>
</form>
<?php

# On inclut le footer
require 'foot.php';
