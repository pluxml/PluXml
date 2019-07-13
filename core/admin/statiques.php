<?php

/**
 * Edition des pages statiques
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include_once __DIR__ .'/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticsPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER);

# On édite les pages statiques
if(!empty($_POST)) {
	if(isset($_POST['homeStatic']))
		$plxAdmin->editConfiguration($plxAdmin->aConf, array('homestatic'=>$_POST['homeStatic'][0]));
	else
		$plxAdmin->editConfiguration($plxAdmin->aConf, array('homestatic'=>''));
	$plxAdmin->editStatiques($_POST);
	header('Location: statiques.php');
	exit;
}

# Call the views (mainView must be the last to be called, because it's include the masterTemplate)
include_once __DIR__ .'/views/statiquesView.php';
include_once __DIR__ .'/views/mainView.php';