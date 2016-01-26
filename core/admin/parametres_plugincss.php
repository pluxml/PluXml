<?php

/**
 * Affichage de l'écran de gestion du code css d'un plugin
 *
 * @package PLX
 * @author	Stephane F
 **/
include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

$plugin = isset($_GET['p'])?urldecode($_GET['p']):'';
$plugin = plxUtils::nullbyteRemove($plugin);

# chargement du fichier css du plugin pour le frontend
$file_frontend = PLX_ROOT.PLX_CONFIG_PATH.'plugins/'.basename($plugin).'.site.css';
# chargement du fichier css du plugin pour le backend
$file_backend = PLX_ROOT.PLX_CONFIG_PATH.'plugins/'.basename($plugin).'.admin.css';

# Traitement du formulaire: sauvegarde du code css et regénération du cache
if(isset($_POST['submit'])) {
	$ret_f = plxUtils::write(trim($_POST['frontend']), $file_frontend);
	$ret_b = plxUtils::write(trim($_POST['backend']), $file_backend);
	if($ret_f AND $ret_b) {
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
$file_backend_init = PLX_PLUGINS.basename($plugin).'/css/admin.css';
$backend = ($backend=='' AND is_file($file_backend_init)) ? trim(file_get_contents($file_backend_init)) : $backend;
$frontend = is_file($file_frontend) ? trim(file_get_contents($file_frontend)) : '';
$file_frontend_init = PLX_PLUGINS.basename($plugin).'/css/site.css';
$frontend = ($frontend=='' AND is_file($file_frontend_init)) ? trim(file_get_contents($file_frontend_init)) : $frontend;

# On inclut le header
include(dirname(__FILE__).'/top.php');

?>

<form action="parametres_plugincss.php?p=<?php echo urlencode($plugin) ?>" method="post" id="form_file">

	<div class="inline-form action-bar">
		<h2><?php echo plxUtils::strCheck($plugin) ?></h2>
		<?php echo '<p><a class="back" href="parametres_plugins.php">'.L_BACK_TO_PLUGINS.'</a></p>'; ?>
		<input name="submit" type="submit" value="<?php echo L_SAVE_FILE ?>" />
	</div>

	<fieldset>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_frontend"><?php echo L_CONTENT_FIELD_FRONTEND ?>&nbsp;:</label>
				<?php plxUtils::printArea('frontend',plxUtils::strCheck($frontend),60,20,false,'full-width'); ?>
			</div>
		</div>
		<div class="grid">
			<div class="col sml-12">
				<label for="id_backend"><?php echo L_CONTENT_FIELD_BACKEND ?>&nbsp;:</label>
				<?php plxUtils::printArea('backend',plxUtils::strCheck($backend),60,20,false,'full-width'); ?>
				<?php eval($plxAdmin->plxPlugins->callHook('AdminPluginCss')) # Hook Plugins ?>
				<?php echo plxToken::getTokenPostMethod() ?>
			</div>
		</div>
	</fieldset>
</form>
<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>