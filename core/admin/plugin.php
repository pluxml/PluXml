<?php

/**
 * Gestion de l'administration d'un plugin
 *
 * @package PLX
 * @author	Stephane F
 **/
include 'prepend.php';

$plugin = isset($_GET['p'])?urldecode($_GET['p']):'';
$plugin = plxUtils::nullbyteRemove($plugin);

# chargement du fichier d'administration du plugin
$filename = realpath(PLX_PLUGINS.$plugin.'/admin.php');
if(empty($plxAdmin->plxPlugins->aPlugins[$plugin]) OR !file_exists($filename)) {
	plxMsg::Error(L_NO_ENTRY);
	header('Location: index.php');
	exit;
}

# utilisation de la variable plxPlugin pour faciliter la syntaxe dans les devs des plugins
$plxPlugin = $plxAdmin->plxPlugins->aPlugins[$plugin];

# Control des autorisation d'accès à l'écran admin.php du plugin
$plxAdmin->checkProfil($plxPlugin->getAdminProfil());

# On inclut le header
include 'top.php';
?>
	<div class="inline-form action-bar">
		<h2><?= plxUtils::strCheck($plugin) ?></h2>
	</div>
<?php

# Affichage des données
include $filename;

# On inclut le footer
include 'foot.php';
