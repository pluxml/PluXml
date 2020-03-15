<?php

/**
 * Basic configuration controller
 * @author	Stephane F., Pedro "P3ter" CADETE"
 **/
use Pluxml\PlxToken;

include __DIR__ .'/prepend.php';

//CSRF token validation
PlxToken::validateFormToken($_POST);

//Control access page (admin profil needed)
$plxAdmin->checkProfil(PROFIL_ADMIN);

//PluXml configuration update
if(!empty($_POST)) {
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	header('Location: configurationBasic.php');
	exit;
}

// View call
include __DIR__ .'/views/configurationBasicView.php';
?>