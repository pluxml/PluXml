<?php

/**
 * Affichage de l'écran de gestion du code css d'un plugin
 *
 * @package PLX
 * @author	Stephane F
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

$plugin = isset($_GET['p']) ? plxUtils::nullbyteRemove(urldecode($_GET['p'])) : '';
$cssPath = PLX_ROOT . PLX_CONFIG_PATH . 'plugins/' . basename($plugin);
$file_backend = $cssPath . '.admin.css';
$file_frontend = $cssPath . '.site.css';

# Traitement du formulaire: sauvegarde du code css et regénération du cache
if(isset($_POST['submit'])) {
	$ret_b = plxUtils::write(trim($_POST['backend']), $file_backend);
	$ret_f = plxUtils::write(trim($_POST['frontend']), $file_frontend);
	if($ret_b AND $ret_f) {
		$ret_1 = $plxAdmin->plxPlugins->cssCache('site');
		$ret_2 = $plxAdmin->plxPlugins->cssCache('admin');
	}
	if($ret_f AND $ret_b AND $ret_1 AND $ret_2)
		plxMsg::Info(L_SAVE_FILE_SUCCESSFULLY);
	else
		plxMsg::Error(L_SAVE_FILE_ERROR);
	header('Location: parametres_plugincss.php?p='.urlencode($plugin));
	exit;
}

$backend = is_file($file_backend) ? trim(file_get_contents($file_backend)) : '';
$file_backend_init = PLX_PLUGINS . basename($plugin) . '/css/admin.css';
$backend = ($backend=='' AND is_file($file_backend_init)) ? trim(file_get_contents($file_backend_init)) : $backend;
$frontend = is_file($file_frontend) ? trim(file_get_contents($file_frontend)) : '';
$file_frontend_init = PLX_PLUGINS.basename($plugin).'/css/site.css';
$frontend = ($frontend=='' AND is_file($file_frontend_init)) ? trim(file_get_contents($file_frontend_init)) : $frontend;

# On inclut le header
include 'top.php';

?>

<form action="parametres_plugincss.php?p=<?= urlencode($plugin) ?>" method="post" id="form_file">
	<?= plxToken::getTokenPostMethod() ?>

	<div>
		<h2><?= plxUtils::strCheck($plugin) ?></h2>
		<p><a class="back icon-left-big" href="parametres_plugins.php"><?= L_BACK_TO_PLUGINS ?></a></p>
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
			</div>
		</div>
	</fieldset>
</form>
<?php

# On inclut le footer
include 'foot.php';
