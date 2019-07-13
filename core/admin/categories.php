<?php

/**
 * Edition des catégories
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include_once __DIR__ .'/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoriesPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR, PROFIL_EDITOR);

# On édite les catégories
if(!empty($_POST)) {
	$plxAdmin->editCategories($_POST);
	header('Location: categories.php');
	exit;
}

# Tableau du tri
$aTri = array(
	'desc'		=> L_SORT_DESCENDING_DATE,
	'asc'		=> L_SORT_ASCENDING_DATE,
	'alpha'		=> L_SORT_ALPHABETICAL,
	'ralpha'	=> L_SORT_REVERSE_ALPHABETICAL,
	'random'	=> L_SORT_RANDOM
);

# Call the views (mainView must be the last to be called, because it's include the masterTemplate)
include_once __DIR__ .'/views/categoriesView.php';
include_once __DIR__ .'/views/mainView.php';