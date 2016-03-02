<?php
include('config.php');
include(PLX_ROOT.PLX_LIB_PATH.'config.php');

# On verifie que PluXml est installé
if(!file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Location: install.php');
	exit;
}

if (isset($_GET['signin'])) {
	header('Location: '.PLX_ADMIN_PATH.'index.php');
	exit;
}
# On démarre la session
session_start();

# On inclut les librairies nécessaires
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.date.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.glob.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.utils.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.capcha.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.erreur.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.record.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.motor.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.feed.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.show.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.encrypt.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.plugins.php');

# Creation de l'objet principal et lancement du traitement
$plxMotor = plxMotor::getInstance();

# Hook Plugins
eval($plxMotor->plxPlugins->callHook('Index'));

$plxMotor->prechauffage();
$plxMotor->demarrage();

# Creation de l'objet d'affichage
$plxShow = plxShow::getInstance();

eval($plxMotor->plxPlugins->callHook('IndexBegin'));

# On démarre la bufferisation
ob_start();
ob_implicit_flush(0);

# Traitements du thème
if($plxMotor->style == '' or !is_dir(PLX_ROOT.$plxMotor->aConf['racine_themes'].$plxMotor->style)) {
	header('Content-Type: text/plain; charset='.PLX_CHARSET);
	echo L_ERR_THEME_NOTFOUND.' ('.PLX_ROOT.$plxMotor->aConf['racine_themes'].$plxMotor->style.') !';
} elseif(file_exists(PLX_ROOT.$plxMotor->aConf['racine_themes'].$plxMotor->style.'/'.$plxMotor->template)) {
	# On impose le charset
	header('Content-Type: text/html; charset='.PLX_CHARSET);
	# Insertion du template
	include(PLX_ROOT.$plxMotor->aConf['racine_themes'].$plxMotor->style.'/'.$plxMotor->template);
} else {
	header('Content-Type: text/plain; charset='.PLX_CHARSET);
	echo L_ERR_FILE_NOTFOUND.' ('.PLX_ROOT.$plxMotor->aConf['racine_themes'].$plxMotor->style.'/'.$plxMotor->template.') !';
}

# Récuperation de la bufférisation
$output = ob_get_clean();

# Hooks spécifiques au thème
ob_start();
eval($plxMotor->plxPlugins->callHook('ThemeEndHead'));
$output = str_replace('</head>', ob_get_clean().'</head>', $output);
ob_start();
eval($plxMotor->plxPlugins->callHook('ThemeEndBody'));
$output = str_replace('</body>', ob_get_clean().'</body>', $output);

# Hook Plugins
eval($plxMotor->plxPlugins->callHook('IndexEnd'));

# On applique la réécriture d'url si nécessaire
if($plxMotor->aConf['urlrewriting']) {
	$output = plxUtils::rel2abs($plxMotor->aConf['racine'], $output);
}

# On applique la compression gzip si nécessaire et disponible
if($plxMotor->aConf['gzip']) {
	if($encoding=plxUtils::httpEncoding()) {
		header('Content-Encoding: '.$encoding);
		$output = gzencode($output,-1,FORCE_GZIP);
    }
}

# Restitution écran
echo $output;
exit;
?>