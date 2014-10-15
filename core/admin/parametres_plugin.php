<?php

/**
 * Gestion de la configuration d'un plugin
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
# chargement du fichier d'administration du plugin
$filename = realpath(PLX_PLUGINS.$plugin.'/config.php');
if(is_file($filename)) {
	# si le plugin n'est pas actif, aucune instance n'a été créée, on va donc la créer, sinon on prend celle qui existe
	if(!isset($plxAdmin->plxPlugins->aPlugins[$plugin]))
		$plxPlugin = $plxAdmin->plxPlugins->getInstance($plugin);
	else
		$plxPlugin = $plxAdmin->plxPlugins->aPlugins[$plugin];
	
	# Control des autorisation d'accès à l'écran config.php du plugin
	$plxAdmin->checkProfil($plxPlugin->getConfigProfil());
	# chargement de l'écran de parametrage du plugin config.php
	ob_start();
	echo '<p><a href="parametres_plugins.php">'.L_BACK_TO_PLUGINS.'</a></p>';
	include($filename);
	$output=ob_get_clean();
}
else {
	plxMsg::Error(L_NO_ENTRY);
	header('Location: parametres_plugins.php');
	exit;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
# Affichage des données
echo $output;
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>
