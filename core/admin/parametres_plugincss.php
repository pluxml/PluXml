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

$plugin = isset($_GET['p']) ? plxUtils::nullbyteRemove(urldecode($_GET['p'])) : '';

$path1 = PLX_ROOT . PLX_CONFIG_PATH . 'plugins/' . basename($plugin);
# chargement du fichier css du plugin pour le frontend
$file_frontend = $path1 . '.site.css';
# chargement du fichier css du plugin pour le backend
$file_backend = $path1 . '.admin.css';

# Traitement du formulaire: sauvegarde du code css et regénération du cache
if(isset($_POST['submit'])) {
	$frontend = trim($_POST['frontend']);
	$backend = trim($_POST['backend']);
	if(
		(empty($frontend) or (
			plxUtils::write($frontend, $file_frontend) and
			$plxAdmin->plxPlugins->cssCache('site')
		)) and
		(empty($backend) or (
			plxUtils::write($backend, $file_backend) and
			$plxAdmin->plxPlugins->cssCache('admin')
		))
	) {
		plxMsg::Info(L_SAVE_FILE_SUCCESSFULLY);
	} else {
		plxMsg::Error(L_SAVE_FILE_ERROR);
	}
	reloadPage();
}

$backend = is_file($file_backend) ? trim(file_get_contents($file_backend)) : '';
$file_backend_init = PLX_PLUGINS.basename($plugin).'/css/admin.css';
$backend = ($backend=='' AND is_file($file_backend_init)) ? trim(file_get_contents($file_backend_init)) : $backend;
$frontend = is_file($file_frontend) ? trim(file_get_contents($file_frontend)) : '';
$file_frontend_init = PLX_PLUGINS.basename($plugin).'/css/site.css';
$frontend = ($frontend=='' AND is_file($file_frontend_init)) ? trim(file_get_contents($file_frontend_init)) : $frontend;

# On inclut le header
require 'top.php';

?>

<form method="post" id="form_file">
	<div class="inline-form action-bar">
		<h2><?= plxUtils::strCheck($plugin) ?></h2>
		<?= '<p><a class="back" href="parametres_plugins.php">'.L_BACK_TO_PLUGINS.'</a></p>'; ?>
		<input name="submit" type="submit" value="<?= L_SAVE_FILE ?>" />
	</div>

	<fieldset>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_frontend"><?= L_CONTENT_FIELD_FRONTEND ?>&nbsp;:</label>
				<?php plxUtils::printArea('frontend',plxUtils::strCheck($frontend), 0, 20); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_backend"><?= L_CONTENT_FIELD_BACKEND ?>&nbsp;:</label>
				<?php plxUtils::printArea('backend',plxUtils::strCheck($backend), 0, 20); ?>
				<?php eval($plxAdmin->plxPlugins->callHook('AdminPluginCss')) # Hook Plugins ?>
				<?= plxToken::getTokenPostMethod() ?>
			</div>
		</div>
	</fieldset>
</form>
<?php

# On inclut le footer
require 'foot.php';
