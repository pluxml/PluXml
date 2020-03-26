<?php
/**
 * Display configuration controller
 * @author	Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE
 **/

use Pluxml\PlxGlob;
use Pluxml\PlxToken;
use Pluxml\PlxUtils;

include __DIR__ .'/prepend.php';

//CSRF token validation
PlxToken::validateFormToken($_POST);

//Control access page (admin profil needed)
$plxAdmin->checkProfil(PROFIL_ADMIN);

//PluXml configuration update
if(!empty($_POST)) {
	$_POST['feed_footer']=$_POST['content'];
	$_POST['images_l']=PlxUtils::getValue($_POST['images_l'],800);
	$_POST['images_h']=PlxUtils::getValue($_POST['images_h'],600);
	$_POST['miniatures_l']=PlxUtils::getValue($_POST['miniatures_l'],200);
	$_POST['miniatures_h']=PlxUtils::getValue($_POST['miniatures_h'],100);
	unset($_POST['content']);
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	header('Location: configurationDisplay.php');
	exit;
}

// Get homepage templates
$aTemplates = array();
$files = PlxGlob::getInstance(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$plxAdmin->aConf['style']);
if ($array = $files->query('/^home(-[a-z0-9-_]+)?.php$/')) {
	foreach($array as $k=>$v)
		$aTemplates[$v] = $v;
}
if(empty($aTemplates)) $aTemplates[''] = L_NONE1;

// Sort array
$aTriArts = array(
	'desc'		=> L_SORT_DESCENDING_DATE,
	'asc'		=> L_SORT_ASCENDING_DATE,
	'alpha'		=> L_SORT_ALPHABETICAL,
	'ralpha'	=> L_SORT_REVERSE_ALPHABETICAL,
	'random'	=> L_SORT_RANDOM
);

$aTriComs = array('desc'=>L_SORT_DESCENDING_DATE, 'asc'=>L_SORT_ASCENDING_DATE);

// Check medias and thumbnails format
if(!is_numeric($plxAdmin->aConf['images_l'])) $plxAdmin->aConf['images_l'] = 800;
if(!is_numeric($plxAdmin->aConf['images_h'])) $plxAdmin->aConf['images_h'] = 600;
if(!is_numeric($plxAdmin->aConf['miniatures_l'])) $plxAdmin->aConf['miniatures_l'] = 200;
if(!is_numeric($plxAdmin->aConf['miniatures_h'])) $plxAdmin->aConf['miniatures_h'] = 100;

// View call
include __DIR__ .'/views/configurationDisplayView.php';
?>