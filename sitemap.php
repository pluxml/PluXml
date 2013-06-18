<?php
# ------------------ BEGIN LICENSE BLOCK ------------------
#
# This file is part of PluXml : http://www.pluxml.org
#
# Copyright (c) 2010-2013 Stephane Ferrari and contributors
# Copyright (c) 2008-2009 Florent MONTHEL and contributors
# Copyright (c) 2006-2008 Anthony GUERIN
# Licensed under the GPL license.
# See http://www.gnu.org/licenses/gpl.html
#
# ------------------- END LICENSE BLOCK -------------------

define('PLX_ROOT', './');
define('PLX_CORE', PLX_ROOT.'core/');
include(PLX_ROOT.'config.php');
include(PLX_CORE.'lib/config.php');

# On verifie que PluXml est installé
if(!file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Location: '.PLX_ROOT.'install.php');
	exit;
}

# On inclut les librairies nécessaires
include(PLX_CORE.'lib/class.plx.date.php');
include(PLX_CORE.'lib/class.plx.glob.php');
include(PLX_CORE.'lib/class.plx.utils.php');
include(PLX_CORE.'lib/class.plx.capcha.php');
include(PLX_CORE.'lib/class.plx.erreur.php');
include(PLX_CORE.'lib/class.plx.record.php');
include(PLX_CORE.'lib/class.plx.motor.php');
include(PLX_CORE.'lib/class.plx.plugins.php');

# On impose le charset
header('Content-Type: text/xml; charset='.PLX_CHARSET);

# Creation de l'objet principal et lancement du traitement
$plxMotor = plxMotor::getInstance();

# Hook Plugins
if(eval($plxMotor->plxPlugins->callHook('SitemapBegin'))) return;

# On démarre la bufferisation
ob_start();
ob_implicit_flush(0);

$plxMotor->prechauffage();
$plxMotor->demarrage();

# Entête XML
echo '<?xml version="1.0" encoding="'.strtolower(PLX_CHARSET).'" ?>'."\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<url>
		<loc><?php echo $plxMotor->urlRewrite() ?></loc>
		<changefreq>weekly</changefreq>
		<priority>1.0</priority>
	</url>
<?php
# Les pages statiques
foreach($plxMotor->aStats as $stat_num => $stat_info) {
	if($stat_info['active']==1 AND $stat_num!=$plxMotor->aConf['homestatic']) {
		echo "\n";
		echo "\t<url>\n";
		echo "\t\t<loc>".$plxMotor->urlRewrite("?static".intval($stat_num)."/".$stat_info['url'])."</loc>\n";
		echo "\t\t<changefreq>monthly</changefreq>\n";
		echo "\t\t<priority>0.8</priority>\n";
		echo "\t</url>\n";
	}
}
eval($plxMotor->plxPlugins->callHook('SitemapStatics'));
# Les catégories
foreach($plxMotor->aCats as $cat_num => $cat_info) {
	if($cat_info['active']==1 AND $cat_info['menu']=='oui' AND ($cat_info['articles']!=0 OR $plxMotor->aConf['display_empty_cat'])) {
		echo "\n";
		echo "\t<url>\n";
		echo "\t\t<loc>".$plxMotor->urlRewrite("?categorie".intval($cat_num)."/".$cat_info['url'])."</loc>\n";
		echo "\t\t<changefreq>weekly</changefreq>\n";
		echo "\t\t<priority>0.8</priority>\n";
		echo "\t</url>\n";
	}
}
eval($plxMotor->plxPlugins->callHook('SitemapCategories'));
# Les articles
if($aFiles = $plxMotor->plxGlob_arts->query('/^[0-9]{4}.(?:[0-9]|home|,)*(?:'.$plxMotor->activeCats.'|home)(?:[0-9]|home|,)*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/','art','rsort', 0, false, 'before')) {
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
			echo "\n";
			echo "\t<url>\n";
			echo "\t\t<loc>".$plxMotor->urlRewrite("?article".$num."/".plxUtils::strCheck($plxRecord_arts->f('url')))."</loc>\n";
			echo "\t\t<lastmod>".plxDate::formatDate($plxRecord_arts->f('date'),'#num_year(4)-#num_month-#num_day')."</lastmod>\n";
			echo "\t\t<changefreq>monthly</changefreq>\n";
			echo "\t\t<priority>0.5</priority>\n";
			echo "\t</url>\n";
		}
	}
}
eval($plxMotor->plxPlugins->callHook('SitemapArticles'));
?>
</urlset>
<?php 

# Récuperation de la bufférisation
$output = ob_get_clean();

# Hook Plugins
eval($plxMotor->plxPlugins->callHook('SitemapEnd'));

# Restitution écran
echo $output;
exit;
?>