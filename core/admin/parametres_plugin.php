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

$plugin = isset($_GET['p']) ? plxUtils::nullbyteRemove(urldecode($_GET['p'])) : '';

if(!empty($plugin)) {
	$filename = realpath(PLX_PLUGINS . $plugin . '/config.php');

	if(is_file($filename)) {
		# chargement du fichier d'administration du plugin

		# si le plugin n'est pas actif, aucune instance n'a été créée, on va donc la créer, sinon on prend celle qui existe
		if(empty($plxAdmin->plxPlugins->aPlugins[$plugin]))
			$plxPlugin = $plxAdmin->plxPlugins->getInstance($plugin);
		else
			$plxPlugin = $plxAdmin->plxPlugins->aPlugins[$plugin];

		# Control des autorisation d'accès à l'écran config.php du plugin
		$plxAdmin->checkProfil($plxPlugin->getConfigProfil());

		# On inclut le header
		include 'top.php';
?>
	<div>
		<h2><?= plxUtils::strCheck($plugin) ?></h2>
		<p><a class="back icon-left-big" href="parametres_plugins.php"><?= L_BACK_TO_PLUGINS ?></a></p>
	</div>
<?php

		# chargement de l'écran de paramétrage du plugin config.php
		include $filename;

		# On inclut le footer
		include 'foot.php';

		exit;
	}
}

# Echec
plxMsg::Error(L_NO_ENTRY);
header('Location: parametres_plugins.php');
