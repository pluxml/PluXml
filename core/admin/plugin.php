<?php

/**
 * Gestion de l'administration d'un plugin
 *
 * @package PLX
 * @author	Stephane F
 **/
include(dirname(__FILE__).'/prepend.php');

$plugin = isset($_GET['p'])?urldecode($_GET['p']):'';
$plugin = plxUtils::nullbyteRemove($plugin);

$output='';
# chargement du fichier d'administration du plugin
$filename = realpath(PLX_PLUGINS.$plugin.'/admin.php');
if($plxAdmin->plxPlugins->aPlugins[$plugin]['activate'] AND is_file($filename)) {
	ob_start();
	$plxPlugin = $plxAdmin->plxPlugins->aPlugins[$plugin]['instance'];
	$plxAdmin->checkProfil($plxPlugin->getAdminProfil());
	include($filename);
	$output=ob_get_clean();
}
else {
	plxMsg::Error(L_NO_ENTRY);
	header('Location: index.php');
	exit;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
# Affichage des données
echo $output;
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>
