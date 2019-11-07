<?php
const PLX_ROOT = './';
const PLX_CORE = PLX_ROOT .'core/';

include(PLX_ROOT.'config.php');
include(PLX_CORE.'lib/config.php');

# On verifie que PluXml est installé
if(!file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Location: '.PLX_ROOT.'install.php');
	exit;
}

#autorise le cross-origin des flus rss/atom : Cross-Origin Resource Sharing : enable-cors.org/server_php.html + developer.mozilla.org/fr/docs/Web/HTTP/Headers/Access-Control-Allow-Origin
header('Access-Control-Allow-Origin: *');

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

# Détermination de la langue à utiliser (modifiable par le hook : FeedBegin)
$lang = $plxFeed->aConf['default_lang'];

eval($plxFeed->plxPlugins->callHook('FeedBegin')); # Hook Plugins

# Chargement du fichier de langue du core de PluXml
loadLang(PLX_CORE.'lang/'.$lang.'/core.php');

if(!$plxFeed->aConf['enable_rss']) {
	header('Location: index.php');
	exit;
}

# On démarre la bufferisation
ob_start();
ob_implicit_flush(0);

$plxFeed->fprechauffage();
$plxFeed->fdemarrage();

# Récuperation de la bufférisation
$output = ob_get_clean();

eval($plxFeed->plxPlugins->callHook('FeedEnd')); # Hook Plugins

# Restitution écran
echo $output;
exit;
?>
