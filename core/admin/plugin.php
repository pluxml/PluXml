<?php

/**
 * Gestion de l'administration d'un plugin
 *
 * @package PLX
 * @author	Stephane F, Jean-Pierre Pourrez @bazooka07
 **/
require 'prepend.php';

if(!isset($plxAdmin)) {
	redirectLogin();
}

if(isset($_GET['p'])) {
	$plugin = plxUtils::nullbyteRemove(urldecode($_GET['p']));
	if(!empty($plxAdmin->plxPlugins->aPlugins[$plugin])) {
		# utilisation de la variable plxPlugin pour faciliter la syntaxe dans les devs des plugins
		$plxPlugin = $plxAdmin->plxPlugins->aPlugins[$plugin];
		# Control des autorisation d'accès à l'écran admin.php du plugin
		$plxAdmin->checkProfil($plxPlugin->getAdminProfil());

		# chargement du fichier d'administration du plugin
		$filename = realpath(PLX_PLUGINS.$plugin.'/admin.php');
		if(is_file($filename)) {

			# chargement de l'écran de paramétrage du plugin config.php
			$output = '';
			ob_start();
			try {
				include $filename;
			} catch(Exception $e) {
				plxMsg::Error($e->getMessage());
			} finally {
				$output=ob_get_clean();
			}

			# le plugin n'a pas fait de redirection via header('Location: ....')

			# On inclut le header
			require 'top.php';
?>
			<div class="inline-form action-bar">
				<h2><?= plxUtils::strCheck($plugin) ?></h2>
			</div>
<?php
			# Affichage des données
			echo $output;
			# On inclut le footer
			require 'foot.php';

			exit;
		}
	}
}

plxMsg::Error(L_NO_ENTRY);
redirectAdmin();
