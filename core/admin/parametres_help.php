<?php

/**
 * Affichage de l'aide d'un thème
 *
 * @package PLX
 * @author	Stephane F
 **/
include 'prepend.php';

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
		$lang = isset($_GET['lang']) ? $_GET['lang'] : $plxAdmin->aConf['default_lang'];
		$filename = realpath(PLX_PLUGINS . $page . '/lang/' . $lang . '-help.php');
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

# chargement du fichier d'aide du thème
if(!file_exists($filename)) {
	plxMsg::Error(L_NO_ENTRY);
	header('Location: '.$back_to);
	exit;
}

ob_start();
try {
?>
	<div class="inline-form action-bar">
		<h2><?= plxUtils::strCheck($page) ?></h2>
		<p><a class="back" href="<?= $back_to ?>"><?= $back_to_title ?></a></p>
	</div>
<?php
	include  $filename;
} catch(Exception $e) {
	plxMsg::Error($e->getMessage());
} finally {
	$output=ob_get_clean();
}

# On inclut le header
include 'top.php';
?>
	<div class="inline-form action-bar">
		<h2><?= plxUtils::strCheck($page) ?></h2>
		<p><a class="back" href="<?= $back_to ?>"><?= $back_to_title ?></a></p>
	</div>
<?php
include  $filename;

# On inclut le footer
include 'foot.php';
