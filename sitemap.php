<?php
const PLX_ROOT = './';
const PLX_CORE = PLX_ROOT . 'core/';
const HUB = 'index.php?';

include PLX_CORE . 'lib/config.php';

# On verifie que PluXml est installé
if(!file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Location: ' . PLX_ROOT . 'install.php');
	exit;
}

# Creation de l'objet principal et lancement du traitement
$plxMotor = plxMotor::getInstance();

# Détermination de la langue à utiliser (modifiable par le hook : Index)
$lang = $plxMotor->aConf['default_lang'];

# Hook Plugins
if(eval($plxMotor->plxPlugins->callHook('SitemapBegin'))) return;

# chargement du fichier de langue
loadLang(PLX_CORE . 'lang/' . $lang . '/core.php');

$plxMotor->prechauffage();
$plxMotor->demarrage();

# On démarre la bufferisation
ob_start();
ob_implicit_flush(0);

# Entête XML
echo XML_HEADER;
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<url>
		<loc><?= $plxMotor->urlRewrite() ?></loc>
		<changefreq>weekly</changefreq>
		<priority>1.0</priority>
	</url>
<?php

# ============= Les pages statiques ================
foreach($plxMotor->aStats as $stat_num => $stat_info) {
	if($stat_info['active'] == 1 AND $stat_num != $plxMotor->aConf['homestatic']) {
?>
	<url>
		<loc><?= $plxMotor->urlRewrite(HUB . L_STATIC_URL . intval($stat_num) . '/' . $stat_info['url']) ?></loc>
		<lastmod><?= plxDate::formatDate($plxMotor->aStats[$stat_num]['date_update'],'#num_year(4)-#num_month-#num_day') ?></lastmod>
		<changefreq>monthly</changefreq>
		<priority>0.8</priority>
	</url>
<?php
	}
}
eval($plxMotor->plxPlugins->callHook('SitemapStatics')); # Hook plugins

# ================= Les catégories =============
foreach($plxMotor->aCats as $cat_num => $cat_info) {
	if($cat_info['active'] == 1 AND $cat_info['menu'] == 'oui' AND ($cat_info['articles'] != 0 OR $plxMotor->aConf['display_empty_cat'])) {
?>
	<url>
		<loc><?= $plxMotor->urlRewrite(HUB . L_CATEGORY_URL . intval($cat_num) . '/' . $cat_info['url']) ?></loc>
		<changefreq>weekly</changefreq>
		<priority>0.8</priority>
	</url>
<?php
	}
}
eval($plxMotor->plxPlugins->callHook('SitemapCategories')); # Hook Plugins

# ============== Les articles =================
const PATTERN = '#num_year(4)-#num_month-#num_day'; # for formatDate()

# Notice 000 and home are always in activeCats
if($aFiles = $plxMotor->plxGlob_arts->query('#^\d{4}\.(?:pin,|\d{3},)*(?:' . $plxMotor->activeCats . ')(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#','art','rsort', 0, false, 'before')) {
	$plxRecord_arts = false;
	$array=array();
	foreach($aFiles as $k=>$v) { # On parcourt tous les fichiers
		$array[ $k ] = $plxMotor->parseArticle(PLX_ROOT.$plxMotor->aConf['racine_articles'].$v);
	}
	# On stocke les enregistrements dans un objet plxRecord
	$plxRecord_arts = new plxRecord($array);
	if($plxRecord_arts) {
		# On boucle sur nos articles
		while($plxRecord_arts->loop()) {
			$num = intval($plxRecord_arts->f('numero'));
?>
	<url>
		<loc><?= $plxMotor->urlRewrite(HUB . L_ARTICLE_URL . $num . '/' . plxUtils::strCheck($plxRecord_arts->f('url'))) ?></loc>
		<lastmod><?= plxDate::formatDate($plxRecord_arts->f('date_update'), PATTERN) ?></lastmod>
		<changefreq>monthly</changefreq>
		<priority>0.5</priority>
	</url>
<?php
		}
	}
}
eval($plxMotor->plxPlugins->callHook('SitemapArticles')); # Hook Plugins

?>
</urlset>
<?php

# Récuperation de la bufférisation
$output = ob_get_clean();

eval($plxMotor->plxPlugins->callHook('SitemapEnd')); # Hook Plugins

# Restitution écran
header('Content-Type: application/rss+xml; charset=UTF-8');
header('Content-Disposition: attachment; filename="sitemap.xml"');
header('Content-Length: ' . strlen($output));
echo $output;
