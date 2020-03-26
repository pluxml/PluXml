<?php

/**
 * Users creation and configuration controller
 * @author	Stephane F., Pedro "P3ter" CADETE"
 **/

use Pluxml\PlxToken;
use Pluxml\PlxUtils;

include __DIR__ .'/prepend.php';

//CSRF token validation
PlxToken::validateFormToken($_POST);

//Control access page (admin profil needed)
$plxAdmin->checkProfil(PROFIL_ADMIN);

//Necessary to set "active" CSS class on the administration main menu
$_SERVER['SCRIPT_NAME'] = 'configurationBasic.php';

//PluXml configuration update
if (!empty($_POST)) {
	$plxAdmin->editUsers($_POST);
	header('Location: configurationUsers.php');
	exit;
}

//Profiles array
$aProfils = array(
	PROFIL_ADMIN => L_PROFIL_ADMIN,
	PROFIL_MANAGER => L_PROFIL_MANAGER,
	PROFIL_MODERATOR => L_PROFIL_MODERATOR,
	PROFIL_EDITOR => L_PROFIL_EDITOR,
	PROFIL_WRITER => L_PROFIL_WRITER
);

// View call
include __DIR__ .'/views/configurationUsersView.php';
?>