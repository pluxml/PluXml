<?php

/**
 * Affichage de l'écran de gestion du code css d'un plugin
 *
 * @package PLX
 * @author	Stephane F
 **/
include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
//plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

$plugin = isset($_GET['p'])?urldecode($_GET['p']):'';
$plugin = plxUtils::nullbyteRemove($plugin);

# chargement du fichier css du plugin
$filename = PLX_ROOT.PLX_CONFIG_PATH.'plugins/'.basename($plugin).'.css';

# Traitement du formulaire: sauvegarde du code css et regénération du cache
if(isset($_POST['submit'])) {
	if(plxUtils::write(trim($_POST['content']), $filename)) {
		$plxAdmin->plxPlugins->cssCache(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$plxAdmin->aConf['style']);
		plxMsg::Info(L_SAVE_FILE_SUCCESSFULLY);
	}
	else
		plxMsg::Error(L_SAVE_FILE_ERROR);
}

$content = is_file($filename) ? file_get_contents($filename) : '';

# On inclut le header
include(dirname(__FILE__).'/top.php');

# Affichage des données
echo '<p><a href="parametres_plugins.php">'.L_BACK_TO_PLUGINS.'</a></p>';
?>
<h2><?php echo plxUtils::strCheck($plugin).'.css' ?></h2>

<form action="parametres_plugincss.php?p=<?php echo urlencode($plugin) ?>" method="post" id="form_file">
	<fieldset>
		<p id="p_content"><label for="id_content"><?php echo L_CONTENT_FIELD ?>&nbsp;:</label></p>
		<?php plxUtils::printArea('content',plxUtils::strCheck($content),60,20); ?>
		<?php eval($plxAdmin->plxPlugins->callHook('AdminPluginCss')) # Hook Plugins ?>
		<p class="center">
			<?php echo plxToken::getTokenPostMethod() ?>
			<input  class="button update" name="submit" type="submit" value="<?php echo L_SAVE_FILE ?>" />
		</p>
	</fieldset>
</form>
<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>
