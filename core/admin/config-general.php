<?php

/**
 * Edition des paramètres de base
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F
 **/

include_once __DIR__ .'/prepend.php';
include PLX_CORE.'lib/class.plx.timezones.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if(!empty($_POST)) {
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	header('Location: parametres_base.php');
	exit;
}

# Call the views (mainView must be the last to be called, because it's include the masterTemplate)
include_once __DIR__ .'/views/configGeneralView.php';
include_once __DIR__ .'/views/mainView.php';