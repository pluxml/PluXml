<?php

/**
 * Traitement de l'upload des fichiers sur le serveur à partir du gestionnaire de médias
 *
 * @package PLX
 * @author  Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

# validation du token de sécurité
if($_SERVER['REQUEST_METHOD']=='POST' AND isset($_SESSION['formtoken'])) {
	if(empty($_POST['token']) OR plxUtils::getValue($_SESSION['formtoken'][$_POST['token']]) < time() - 3600) { # 3600 seconds
		unset($_SESSION['formtoken']);
		die('Security error : invalid or expired token');
	}
} else {
	die;
}

# Sécurisation du chemin du dossier
if(isset($_POST['folder']) AND $_POST['folder']!='.' AND !plxUtils::checkSource($_POST['folder'])) {
	$_POST['folder']='.';
}

# Recherche du type de medias à afficher via la session
if(empty($_SESSION['medias'])) {
	$_SESSION['medias'] = $plxAdmin->aConf['medias'];
	$_SESSION['folder'] = '';
}
elseif(!empty($_POST['folder'])) {
	$_SESSION['currentfolder']= (isset($_SESSION['folder'])?$_SESSION['folder']:'');
	$_SESSION['folder'] = ($_POST['folder']=='.'?'':$_POST['folder']);
}
# Nouvel objet de type plxMedias
if($plxAdmin->aConf['userfolders'] AND $_SESSION['profil']==PROFIL_WRITER)
	$plxMedias = new plxMedias(PLX_ROOT.$_SESSION['medias'].$_SESSION['user'].'/',$_SESSION['folder']);
else
	$plxMedias = new plxMedias(PLX_ROOT.$_SESSION['medias'],$_SESSION['folder']);

if(!empty($_POST['token'])) {
	 $res = $plxMedias->uploadFile($_FILES, $_POST);
	 /*
	 switch($res) {
		case L_PLXMEDIAS_WRONG_FILESIZE:
			plxMsg::Error(L_PLXMEDIAS_WRONG_FILESIZE);
			break;
		case L_PLXMEDIAS_WRONG_FILEFORMAT:
			plxMsg::Error(L_PLXMEDIAS_WRONG_FILEFORMAT);
			break;
		case L_PLXMEDIAS_UPLOAD_ERR:
			plxMsg::Error(L_PLXMEDIAS_UPLOAD_ERR);
			break;
		case L_PLXMEDIAS_UPLOAD_SUCCESSFUL:
			plxMsg::Info(L_PLXMEDIAS_UPLOAD_SUCCESSFUL);
			break;
	}
	*/
	$plxMedias->outputJSON($res, ($res == L_PLXMEDIAS_UPLOAD_SUCCESSFUL ? '' : 'error'));

}

exit;