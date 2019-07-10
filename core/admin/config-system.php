<?php

/**
 * Edition des paramètres d'affichage
 *
 * @package PLX
 * @author	Florent MONTHEL
 **/

include __DIR__ .'/prepend.php';

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# Call the views (mainView must be the last to be called, because it's include the masterTemplate)
include __DIR__ .'/views/configSystemView.php';
include __DIR__ .'/views/mainView.php';