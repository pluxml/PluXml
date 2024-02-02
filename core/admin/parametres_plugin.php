<?php

/**
 * Gestion de la configuration d'un plugin
 *
 * @package PLX
 * @author	Stephane F
 **/
include 'prepend.php';

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

$plugin = isset($_GET['p'])?urldecode($_GET['p']):'';
$plugin = plxUtils::nullbyteRemove($plugin);

# chargement du fichier d'administration du plugin
$filename = realpath(PLX_PLUGINS.$plugin.'/config.php');
if(!file_exists($filename)) {
	plxMsg::Error(L_NO_ENTRY);
	header('Location: parametres_plugins.php');
	exit;
}

# si le plugin n'est pas actif, aucune instance n'a été créée, on va donc la créer, sinon on prend celle qui existe
if(empty($plxAdmin->plxPlugins->aPlugins[$plugin])) {
	$plxPlugin = $plxAdmin->plxPlugins->getInstance($plugin);
} else {
	$plxPlugin = $plxAdmin->plxPlugins->aPlugins[$plugin];
}

# Control des autorisation d'accès à l'écran config.php du plugin
$plxAdmin->checkProfil($plxPlugin->getConfigProfil());

# On inclut le header
include 'top.php';
?>
	<div class="inline-form action-bar">
		<h2><?= plxUtils::strCheck($plugin) ?></h2>
		<p><a class="back" href="parametres_plugins.php"><?= L_BACK_TO_PLUGINS ?></a></p>
	</div>
<?php

# chargement de l'écran de paramétrage du plugin config.php
include $filename;

# On inclut le footer
include 'foot.php';
