<?php

/**
 * Traitement de l'upload des fichiers sur le serveur à partir du gestionnaire de médias
 *
 * @package PLX
 * @author  Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

# Output JSON
function outputJSON($msg, $status = 'error'){
    header('Content-Type: application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
}

# validation du token de sécurité
if($_SERVER['REQUEST_METHOD']=='POST' AND isset($_SESSION['formtoken'])) {
	if(empty($_POST['token']) OR plxUtils::getValue($_SESSION['formtoken'][$_POST['token']]) < time() - 3600) { # 3600 seconds
		unset($_SESSION['formtoken']);
		die('Security error : invalid or expired token');
	}
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
	$plxMedias->uploadFiles($_FILES, $_POST);
}

outputJSON('upload.php', 'Debug');
exit;

// Check for errors
if($_FILES['SelectedFile']['error'] > 0){
    outputJSON('An error ocurred when uploading.');
}
/*
if(!getimagesize($_FILES['SelectedFile']['tmp_name'])){
    outputJSON('Please ensure you are uploading an image.');
}
*/
// Check filetype
/*
if($_FILES['SelectedFile']['type'] != 'image/png'){
    outputJSON('Unsupported filetype uploaded.');
}

// Check filesize
if($_FILES['SelectedFile']['size'] > 500000){
    outputJSON('File uploaded exceeds maximum upload size.');
}
*/
// Check if the file exists
if(file_exists('uploads/' . $_FILES['SelectedFile']['name'])){
    outputJSON('File with that name already exists - '.$_FILES['SelectedFile']['name']);
}

// Upload file
if(!move_uploaded_file($_FILES['SelectedFile']['tmp_name'], 'uploads/' . $_FILES['SelectedFile']['name'])){
    outputJSON('Error uploading file - check destination is writeable.');
}

// Success!
outputJSON('File uploaded successfully to "' . 'uploads/' . $_FILES['SelectedFile']['name'] . '".', 'success');