<?php
/**
 * Advanced configuration controller
 * @author	Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE
 **/

use Pluxml\PlxToken;

include __DIR__ .'/prepend.php';
//CSRF token validation
PlxToken::validateFormToken($_POST);

//Control access page (admin profil needed)
$plxAdmin->checkProfil(PROFIL_ADMIN);

//Necessary to set "active" CSS class on the administration main menu
$_SERVER['SCRIPT_NAME'] = 'configurationBasic.php';

//PluXml configuration update
if(!empty($_POST)) {
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	unset($_SESSION['medias']); # in case of medias path change (used in medias.php)
	header('Location: configurationAdvanced.php');
	exit;
}
// View call
include __DIR__ .'/views/configurationAdvancedView.php';
?>