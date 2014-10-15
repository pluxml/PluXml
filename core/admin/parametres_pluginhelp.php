<?php

/**
 * Affichage de l'aide d'un plugin
 *
 * @package PLX
 * @author	Stephane F
 **/
include(dirname(__FILE__).'/prepend.php');

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

$plugin = isset($_GET['p'])?urldecode($_GET['p']):'';
$plugin = plxUtils::nullbyteRemove($plugin);

$output='';
# chargement du fichier d'aide du plugin
$filename = realpath(PLX_PLUGINS.$plugin.'/lang/'.$plxAdmin->aConf['default_lang'].'-help.php');
if(is_file($filename)) {
	ob_start();
	echo '<p><a href="parametres_plugins.php">'.L_BACK_TO_PLUGINS.'</a></p>';
	include($filename);
	$output=ob_get_clean();
}
else {
	plxMsg::Error(L_NO_ENTRY);
	header('Location: parametres_plugin.php');
	exit;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
# Affichage des données
echo $output;
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>
