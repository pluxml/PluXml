<?php

/**
 * Affichage de l'aide d'un thème
 *
 * @package PLX
 * @author	Stephane F
 **/
include(dirname(__FILE__).'/prepend.php');

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# type d'aide plugin ou theme
$help = isset($_GET['help'])?urldecode($_GET['help']):'';
$help = plxUtils::nullbyteRemove($help);
# page
$page = isset($_GET['page'])?urldecode($_GET['page']):'';
$page = plxUtils::nullbyteRemove($page);

switch($help) {
	case 'plugin':
		$filename = realpath(PLX_PLUGINS.$page.'/lang/'.$plxAdmin->aConf['default_lang'].'-help.php');
		$back_to_title = L_BACK_TO_PLUGINS;
		$back_to = 'parametres_plugins.php';
		break;
	case 'theme':
		$filename = realpath(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$page.'/lang/'.$plxAdmin->aConf['default_lang'].'-help.php');
		$back_to_title = L_BACK_TO_THEMES;
		$back_to = 'parametres_themes.php';
		break;
	default:
		plxMsg::Error(L_NO_ENTRY);
		header('Location: index.php');
		exit;
}

$output='';
# chargement du fichier d'aide du thème
if(is_file($filename)) {
	ob_start();
	echo '
	<div class="inline-form action-bar">
		<h2>'.plxUtils::strCheck($page).'</h2>
		<p><a class="back" href="'.$back_to.'">'.$back_to_title.'</a></p>
	</div>';
	include($filename);
	$output=ob_get_clean();
}
else {
	plxMsg::Error(L_NO_ENTRY);
	header('Location: '.$back_to);
	exit;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
# Affichage des données
echo $output;
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>