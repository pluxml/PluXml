<?php

/**
 * Listing des articles
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include __DIR__ .'/prepend.php';
use Pluxml\PlxToken;
use Pluxml\PlxUtils;
use Pluxml\PlxDate;
use Pluxml\PlxMsg;

# Control du token du formulaire
PlxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexPrepend'));

# Suppression des articles selectionnes
if(isset($_POST['delete']) AND isset($_POST['idArt'])) {
	foreach ($_POST['idArt'] as $k => $v) $plxAdmin->delArticle($v);
	header('Location: index.php');
	exit;
}

# Récuperation de l'id de l'utilisateur
$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '[0-9]{3}' : $_SESSION['user']);

# Récuperation des paramètres
if(!empty($_GET['sel']) AND in_array($_GET['sel'], array('all','published', 'draft','mod'))) {
	$_SESSION['sel_get']=PlxUtils::nullbyteRemove($_GET['sel']);
	$_SESSION['sel_cat']='';
}
else
	$_SESSION['sel_get']=(isset($_SESSION['sel_get']) AND !empty($_SESSION['sel_get']))?$_SESSION['sel_get']:'all';

if(!empty($_POST['sel_cat']))
	if(isset($_SESSION['sel_cat']) AND $_SESSION['sel_cat']==$_POST['sel_cat']) # annulation du filtre
		$_SESSION['sel_cat']='all';
	else # prise en compte du filtre
		$_SESSION['sel_cat']=$_POST['sel_cat'];
else
	$_SESSION['sel_cat']=(isset($_SESSION['sel_cat']) AND !empty($_SESSION['sel_cat']))?$_SESSION['sel_cat']:'all';

# Recherche du motif de sélection des articles en fonction des paramètres
$catIdSel = '';
$mod='';
switch ($_SESSION['sel_get']) {
case 'published':
	$catIdSel = '[home|0-9,]*FILTER[home|0-9,]*';
	$mod='';
	break;
case 'draft':
	$catIdSel = '[home|0-9,]*draft,FILTER[home|0-9,]*';
	$mod='_?';
	break;
case 'all':
	$catIdSel = '[home|draft|0-9,]*FILTER[draft|home|0-9,]*';
	$mod='_?';
	break;
case 'mod':
	$catIdSel = '[home|draft|0-9,]*FILTER[draft|home|0-9,]*';
	$mod='_';
	break;
}

switch ($_SESSION['sel_cat']) {
case 'all' :
	$catIdSel = str_replace('FILTER', '', $catIdSel); break;
case '000' :
	$catIdSel = str_replace('FILTER', '000', $catIdSel); break;
case 'home':
	$catIdSel = str_replace('FILTER', 'home', $catIdSel); break;
case preg_match('/^[0-9]{3}$/', $_SESSION['sel_cat'])==1:
	$catIdSel = str_replace('FILTER', $_SESSION['sel_cat'], $catIdSel);
}

# Nombre d'article sélectionnés
$nbArtPagination = $plxAdmin->nbArticles($catIdSel, $userId);

# Récupération du texte à rechercher
$artTitle = (!empty($_GET['artTitle']))?PlxUtils::unSlash(trim(urldecode($_GET['artTitle']))):'';
if(empty($artTitle)) {
	 $artTitle = (!empty($_POST['artTitle']))?PlxUtils::unSlash(trim(urldecode($_POST['artTitle']))):'';
}
$_GET['artTitle'] = $artTitle;

# On génère notre motif de recherche
if(is_numeric($_GET['artTitle'])) {
	$artId = str_pad($_GET['artTitle'],4,'0',STR_PAD_LEFT);
	$motif = '/^'.$mod.$artId.'.'.$catIdSel.'.'.$userId.'.[0-9]{12}.(.*).xml$/';
} else {
	$motif = '/^'.$mod.'[0-9]{4}.'.$catIdSel.'.'.$userId.'.[0-9]{12}.(.*)'.PlxUtils::urlify($_GET['artTitle']).'(.*).xml$/';
}
# Calcul du nombre de page si on fait une recherche
if($_GET['artTitle']!='') {
	if($arts = $plxAdmin->plxGlob_arts->query($motif))
		$nbArtPagination = sizeof($arts);
}

# Traitement
$plxAdmin->prechauffage($motif);
$plxAdmin->getPage();
$arts = $plxAdmin->getArticles('all'); # Recuperation des articles

# Génération de notre tableau des catégories
$aFilterCat['all'] = L_ARTICLES_ALL_CATEGORIES;
$aFilterCat['home'] = L_CATEGORY_HOME;
$aFilterCat['000'] = L_UNCLASSIFIED;
if($plxAdmin->aCats) {
	foreach($plxAdmin->aCats as $k=>$v) {
		$aCat[$k] = PlxUtils::strCheck($v['name']);
		$aFilterCat[$k] = PlxUtils::strCheck($v['name']);
	}
	$aAllCat[L_CATEGORIES_TABLE] = $aCat;
}
$aAllCat[L_SPECIFIC_CATEGORIES_TABLE]['home'] = L_CATEGORY_HOME_PAGE;
$aAllCat[L_SPECIFIC_CATEGORIES_TABLE]['draft'] = L_DRAFT;
$aAllCat[L_SPECIFIC_CATEGORIES_TABLE][''] = L_ALL_ARTICLES_CATEGORIES_TABLE;

# On inclut le header
include __DIR__ .'/top.php';
?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminIndexTop')) # Hook Plugins ?>

<div class="adminheader">
	<h2>Tableau de bord (lang)</h2>
</div>

<div class="admin">
<?php
if(is_file(PLX_ROOT.'install.php'))
	echo '<p class="alert red">'.L_WARNING_INSTALLATION_FILE.'</p>'."\n";
PlxMsg::Display();
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminTopBottom'));
?>

<div class="autogrid mts mbs">

</div>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexFoot'));
# On inclut le footer
include __DIR__ .'/foot.php';
?>
