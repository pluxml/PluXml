<?php
/**
 * Edition des paramètres avancés
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE
 **/

include __DIR__ .'/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if(!empty($_POST)) {
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	unset($_SESSION['medias']); # réinit de la variable de session medias (pour medias.php) au cas si changmt de chemin medias
	header('Location: parametres_avances.php');
	exit;
}

# Call the views (mainView must be the last to be called, because it's include the masterTemplate)
include __DIR__ .'/views/configAdvancedView.php';
include __DIR__ .'/views/mainView.php';