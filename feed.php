<?php
const PLX_ROOT = './';
define('PLX_CORE', PLX_ROOT .'core/');
include PLX_CORE.'lib/config.php';

# Autorise le cross-origin des flus rss/atom : Cross-Origin Resource Sharing
# https://enable-cors.org/server_php.html
# https://developer.mozilla.org/fr/docs/Web/HTTP/Headers/Access-Control-Allow-Origin
header('Access-Control-Allow-Origin: *');

# Creation de l'objet principal et lancement du traitement
$plxFeed = plxFeed::getInstance();

# Détermination de la langue à utiliser (modifiable par le hook : FeedBegin)
$lang = $plxFeed->aConf['default_lang'];

if(!empty($plxFeed->plxPlugins)) {
	eval($plxFeed->plxPlugins->callHook('FeedBegin')); # Hook Plugins
}

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

switch($plxFeed->mode) {
	case 'article'		:
		if(!empty($plxFeed->cible)) {
			# catégorie
			$filename = L_CATEGORIES . '-' . $plxFeed->cible;
		} else {
			$filename = L_ARTICLES;
		}
		break;
	case 'comment'		:
	case 'commentaire'	:
		$filename = L_COMMENTS;
		# commentaires pour un article particulier
		if(!empty($plxFeed->cible)) {
			$filename .= '-' . L_ARTICLE . '-' . $plxFeed->cible;
		}
		break;
	case 'categorie'	: $filename = L_CATEGORIE . '-' . $plxFeed->cible; break;
	case 'tag'			: $filename = str_replace('-', '_', L_TAG) . '-' . $plxFeed->cible; break;
	case 'admin'		:
		$filename = L_COMMENTS . '-admin';
		$filename .= ($plxFeed->cible == '_') ? '-offline' : '-online';
		break;
	default				: $filename = L_ALL;
}

if(!empty($plxFeed->plxPlugins)) {
	eval($plxFeed->plxPlugins->callHook('FeedEnd')); # Hook Plugins
}

# Restitution écran
header('Content-Type: text/xml; charset=' . strtolower(PLX_CHARSET));
header('Content-Disposition: attachment; filename="' . plxUtils::urlify($filename) . '.rss' . '"');
echo $output;
?>
