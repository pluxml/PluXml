<?php
/**
 * About page controller
 * @author	Florent MONTHEL, Pedro "P3ter" CADETE
 **/

use Pluxml\PlxToken;

include __DIR__ .'/prepend.php';

//CSRF token validation
PlxToken::validateFormToken($_POST);

//Control access page (admin profil needed)
$plxAdmin->checkProfil(PROFIL_ADMIN);

$email = filter_var($plxAdmin->aUsers[$_SESSION['user']]['email'], FILTER_VALIDATE_EMAIL);
$emailBuild = (is_string($email) and filter_has_var(INPUT_POST, 'sendmail-test'));
if($emailBuild) {
	# body of test e-mail starts here
	ob_start();
}

// View call
include __DIR__ .'/views/configurationAboutView.php';
?>