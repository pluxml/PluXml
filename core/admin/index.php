<?php

/**
 * Page d'accueil de l'administration
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

include __DIR__ .'/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexPrepend'));

# Récuperation de l'id de l'utilisateur
$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '[0-9]{3}' : $_SESSION['user']);

if(isset($_GET["del"]) AND $_GET["del"]=="install") {
    if(@unlink(PLX_ROOT.'install.php'))
        plxMsg::Info(L_DELETE_SUCCESSFUL);
        else
            plxMsg::Error(L_DELETE_FILE_ERR.' install.php');
            header("Location: index.php");
            exit;
}

# Call the views
include __DIR__ .'/views/dashboardView.php';
include __DIR__ .'/views/mainView.php';
