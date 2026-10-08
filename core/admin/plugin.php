<?php

/**
 * Gestion de l'administration d'un plugin
 *
 * @package PLX
 * @author	Stephane F
 **/
require 'prepend.php';

if(!isset($plxAdmin)) {
	redirectLogin();
}

$plugin = isset($_GET['p']) ? plxUtils::nullbyteRemove(urldecode($_GET['p'])) : '';

$output='';
# chargement du fichier d'administration du plugin
$filename = realpath(PLX_PLUGINS.$plugin.'/admin.php');
if(!empty($plxAdmin->plxPlugins->aPlugins[$plugin]) AND is_file($filename)) {
	# utilisation de la variable plxPlugin pour faciliter la syntaxe dans les devs des plugins
	$plxPlugin = $plxAdmin->plxPlugins->aPlugins[$plugin];
	# Control des autorisation d'accès à l'écran admin.php du plugin
	$plxAdmin->checkProfil($plxPlugin->getAdminProfil());
	ob_start();
	echo '
	<div class="inline-form action-bar">
		<h2>'.plxUtils::strCheck($plugin).'</h2>
	</div>';
	include $filename;
	$output=ob_get_clean();
}
else {
	plxMsg::Error(L_NO_ENTRY);
	redirectAdmin();
}

# On inclut le header
require 'top.php';
# Affichage des données
echo $output;
# On inclut le footer
require 'foot.php';
?>
