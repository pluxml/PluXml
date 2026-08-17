<?php
# See https://developers.google.com/search/docs/crawling-indexing/sitemaps/news-sitemap?hl=fr

const PLX_ROOT = './';
const PLX_CORE = PLX_ROOT .'core/';

include PLX_CORE.'lib/plx_config.php';
include PLX_CORE.'lib/config.php';

# On inclut les librairies nécessaires
include PLX_CORE.'lib/class.plx.date.php';
include PLX_CORE.'lib/class.plx.glob.php';
include PLX_CORE.'lib/class.plx.utils.php';
include PLX_CORE.'lib/class.plx.capcha.php';
include PLX_CORE.'lib/class.plx.erreur.php';
include PLX_CORE.'lib/class.plx.record.php';
include PLX_CORE.'lib/class.plx.motor.php';
include PLX_CORE.'lib/class.plx.plugins.php';

# Creation de l'objet principal et lancement du traitement
$plxMotor = plxMotor::getInstance();

# Détermination de la langue à utiliser (modifiable par le hook : Index)
$lang = $plxMotor->aConf['default_lang'];

# Hook Plugins
if(eval($plxMotor->plxPlugins->callHook('SitemapBegin'))) return;

# chargement du fichier de langue
loadLang(PLX_CORE.'lang/'.$lang.'/core.php');

# On démarre la bufferisation
ob_start();
ob_implicit_flush(false);

$plxMotor->prechauffage();
$plxMotor->demarrage();

# On impose le charset
header('Content-Type: text/xml; charset=' . PLX_CHARSET);

# Entête XML
echo '<?xml version="1.0" encoding="' . strtolower(PLX_CHARSET) . '" ?>' . PHP_EOL ;

?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
<?php
if($aFiles = $plxMotor->plxGlob_arts->query('/^\d{4}\.(?:\d{3},|home,)*(?:' . $plxMotor->activeCats . ')(?:,\d{3}|,home)*\.\d{3}\.\d{12}\.[\w-]+\.xml$/', 'art', 'rsort', 0, 50, 'before')) {
	# les articles sont triés par odre de publication inversé. On se limite aux derniers 50 articles publiés.
	# Google impose une limite de 1000 articles
	$limiteDateStr = date('YmdHi', strtotime('-2 days'));
	$array=array();
	foreach($aFiles as $k=>$v) { # On parcourt tous les fichiers
		$filename = PLX_ROOT . $plxMotor->aConf['racine_articles'] . $v;
		$tmp = $plxMotor->artInfoFromFilename($filename);
		if($tmp['artDate'] >= $limiteDateStr) {
			$array[$k] = $plxMotor->parseArticle($filename);
		} else {
			# Les articles suivants sont trop anciens
			break;
		}
	}
	# On stocke les enregistrements dans un objet plxRecord
	$plxRecord_arts = new plxRecord($array);

	if($plxRecord_arts) {
		$name = $plxMotor->aConf['title'];
		# On boucle sur nos articles
		while($plxRecord_arts->loop()) {
			$num = intval($plxRecord_arts->f('numero'));
?>
	<url>
		<loc><?= $plxMotor->urlRewrite('?article' . $num . '/' . plxUtils::strCheck($plxRecord_arts->f('url'))) ?></loc>
		<news:news>
			<news:publication>
				<news:name><?= $name ?></news:name>
				<news:language><?= $lang ?></news:language>
			</news:publication>
			<news:publication_date><?= plxDate::formatDate($plxRecord_arts->f('date'), '#num_year(4)-#num_month-#num_dayT#hour:#minute:00TZD') ?></news:publication_date>
			<news:title><?= $plxRecord_arts->f('title') ?></news:title>
		</news:news>
	</url>
<?php
		}
	}
}
?>
</urlset>
<?php

# Récuperation de la bufférisation
$output = ob_get_clean();

# Restitution écran
echo $output;
