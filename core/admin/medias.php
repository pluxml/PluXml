<?php

/**
 * Gestion des médias
 *
 * @package PLX
 * @author  Stephane F, Pedro "P3ter" CADETE
 **/

include __DIR__ .'/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Sécurisation du chemin du dossier
if(isset($_POST['folder']) AND $_POST['folder']!='.' AND !plxUtils::checkSource($_POST['folder'])) {
	$_POST['folder']='.';
}

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminMediasPrepend'));

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
$plxMediasRoot = PLX_ROOT.$_SESSION['medias'];
if($plxAdmin->aConf['userfolders'] AND $_SESSION['profil']==PROFIL_WRITER)
	$plxMediasRoot .= $_SESSION['user'].'/';
$plxMedias = new plxMedias($plxMediasRoot, $_SESSION['folder']);

#----

if(!empty($_POST['btn_newfolder']) AND !empty($_POST['newfolder'])) {
	$newdir = plxUtils::title2filename(trim($_POST['newfolder']));
	if($plxMedias->newDir($newdir)) {
		$_SESSION['folder'] = $_SESSION['folder'].$newdir.'/';
	}
	header('Location: medias.php');
	exit;
}
if(!empty($_POST['btn_renamefile']) AND !empty($_POST['newname'])) {
	$plxMedias->renameFile($_POST['oldname'], $_POST['newname']);
	header('Location: medias.php');
	exit;
}
elseif(!empty($_POST['folder']) AND $_POST['folder']!='.' AND !empty($_POST['btn_delete'])) {
	if($plxMedias->deleteDir($_POST['folder'])) {
		$_SESSION['folder'] = '';
	}
	header('Location: medias.php');
	exit;
}
elseif(!empty($_POST['btn_upload'])) {
	$plxMedias->uploadFiles($_FILES, $_POST);
	header('Location: medias.php');
	exit;
}
elseif(isset($_POST['selection']) AND ((!empty($_POST['btn_ok']) AND $_POST['selection']=='delete')) AND isset($_POST['idFile'])) {
	$plxMedias->deleteFiles($_POST['idFile']);
	header('Location: medias.php');
	exit;
}
elseif(isset($_POST['selection']) AND ((!empty($_POST['btn_ok']) AND $_POST['selection']=='move')) AND isset($_POST['idFile'])) {
	$plxMedias->moveFiles($_POST['idFile'], $_SESSION['currentfolder'], $_POST['folder']);
	header('Location: medias.php');
	exit;
}
elseif(isset($_POST['selection']) AND ((!empty($_POST['btn_ok']) AND $_POST['selection']=='thumbs')) AND isset($_POST['idFile'])) {
	$plxMedias->makeThumbs($_POST['idFile'], $plxAdmin->aConf['miniatures_l'], $plxAdmin->aConf['miniatures_h']);
	header('Location: medias.php');
	exit;
}

# Tri de l'affichage des fichiers
if(isset($_POST['sort']) AND !empty($_POST['sort'])) {
	$sort = $_POST['sort'];
} else {
	$sort = isset($_SESSION['sort_medias']) ? $_SESSION['sort_medias'] : 'title_asc';
}

$sort_title = 'title_desc';
$sort_date = 'date_desc';
switch ($sort) {
	case 'title_asc':
		$sort_title = 'title_desc';
		usort($plxMedias->aFiles, create_function('$b, $a', 'return strcmp($a["name"], $b["name"]);'));
		break;
	case 'title_desc':
		$sort_title = 'title_asc';
		usort($plxMedias->aFiles, create_function('$a, $b', 'return strcmp($a["name"], $b["name"]);'));
		break;
	case 'date_asc':
		$sort_date = 'date_desc';
		usort($plxMedias->aFiles, create_function('$b, $a', 'return strcmp($a["date"], $b["date"]);'));
		break;
	case 'date_desc':
		$sort_date = 'date_asc';
		usort($plxMedias->aFiles, create_function('$a, $b', 'return strcmp($a["date"], $b["date"]);'));
		break;
}
$_SESSION['sort_medias']=$sort;

# Contenu des 2 listes déroulantes
$selectionList = array(''=>L_FOR_SELECTION,'move'=>L_PLXMEDIAS_MOVE_FOLDER,'thumbs'=>L_MEDIAS_RECREATE_THUMB,'-'=>'-----','delete' =>L_DELETE_FILE);

$curFolder = '/'.plxUtils::strCheck(basename($_SESSION['medias']).'/'.$_SESSION['folder']);
$curFolders = explode('/', $curFolder);

# Call the views (mainView must be the last to be called, because it's include the masterTemplate)
include __DIR__ .'/views/mediasView.php';
include __DIR__ .'/views/mainView.php';