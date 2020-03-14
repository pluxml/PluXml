<?php

/**
 * Gestion de l'administration d'un plugin
 *
 * @package PLX
 * @author	Stephane F
 **/
include __DIR__ .'/prepend.php';
use Pluxml\PlxMsg;
use Pluxml\PlxUtils;

$plugin = isset($_GET['p'])?urldecode($_GET['p']):'';
$plugin = PlxUtils::nullbyteRemove($plugin);

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
		<h2>'.PlxUtils::strCheck($plugin).'</h2>
	</div>';
	include $filename;
	$output=ob_get_clean();
}
else {
	PlxMsg::Error(L_NO_ENTRY);
	header('Location: index.php');
	exit;
}

# On inclut le header
include __DIR__ .'/tags/top.php';
# Affichage des données
echo $output;
# On inclut le footer
include __DIR__ .'/tags/foot.php';
?>
