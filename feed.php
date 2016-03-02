<?php
include('config.php');
include(PLX_ROOT.PLX_LIB_PATH.'config.php');

define('PLX_FEED_CLASS', true);

# On verifie que PluXml est installé
if(!file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Location: install.php');
	exit;
}

# On inclut les librairies nécessaires
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.date.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.glob.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.utils.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.record.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.motor.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.feed.php');
include(PLX_ROOT.PLX_LIB_PATH.'class.plx.plugins.php');

# Creation de l'objet principal et lancement du traitement
$plxFeed = plxFeed::getInstance();

eval($plxFeed->plxPlugins->callHook('FeedBegin'));

# On démarre la bufferisation
ob_start();
ob_implicit_flush(0);

$plxFeed->fprechauffage();
$plxFeed->fdemarrage();

# Récuperation de la bufférisation
$output = ob_get_clean();

# Hook Plugins
eval($plxFeed->plxPlugins->callHook('FeedEnd'));

# Restitution écran
echo $output;
exit;
?>