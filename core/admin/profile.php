<?php

/**
 * Edition du profil utilisateur
 *
 * @package PLX
 * @author	Stephane F
 **/

include_once __DIR__ .'/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminProfilPrepend'));

# On édite la configuration
if(!empty($_POST)) {

	if(!empty($_POST['profil']))
		$plxAdmin->editProfil($_POST);
	elseif(!empty($_POST['password']))
		$plxAdmin->editPassword($_POST);

	header('Location: profil.php');
	exit;

}

$_profil = $plxAdmin->aUsers[$_SESSION['user']];

# Call the views (mainView must be the last to be called, because it's include the masterTemplate)
include_once __DIR__ .'/views/profileView.php';
include_once __DIR__ .'/views/mainView.php';