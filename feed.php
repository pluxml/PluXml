<?php
define('PLX_ROOT', './');
define('PLX_CORE', PLX_ROOT.'core/');
include(PLX_ROOT.'config.php');
include(PLX_CORE.'lib/config.php');

define('PLX_FEED', true);

# On verifie que PluXml est installé
if(!file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Location: '.PLX_ROOT.'install.php');
	exit;
}

# On inclut les librairies nécessaires
include(PLX_CORE.'lib/class.plx.date.php');
include(PLX_CORE.'lib/class.plx.glob.php');
include(PLX_CORE.'lib/class.plx.utils.php');
include(PLX_CORE.'lib/class.plx.record.php');
include(PLX_CORE.'lib/class.plx.motor.php');
include(PLX_CORE.'lib/class.plx.feed.php');
include(PLX_CORE.'lib/class.plx.plugins.php');

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