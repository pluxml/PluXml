<?php
const PLX_ROOT = './';
include PLX_ROOT . 'core/lib/config.php';

$plxMotor = plxMotor::getInstance();

// Language to use (customisable with the hook : Index)
$lang = $plxMotor->aConf['default_lang'];

// Plugin Hook
if (eval($plxMotor->plxPlugins->callHook('SitemapBegin'))) return;

// Language file loading
loadLang(PLX_CORE . 'lang/' . $lang . '/core.php');

$plxMotor->router();
$plxMotor->run();

// Buffer beginning
ob_start();

?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= $plxMotor->urlRewrite() ?></loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <?php

    // Pages
    foreach ($plxMotor->aStats as $stat_num => $stat_info) {
    if ($stat_info['active'] == 1 and $stat_num != $plxMotor->aConf['homestatic']) {
        ?>
        <url>
            <loc><?= $plxMotor->urlRewrite("?static" . intval($stat_num) . '/' . $stat_info['url']) ?></loc>
            <lastmod><?= plxDate::formatDate($plxMotor->aStats[$stat_num]['date_update'], '#num_year(4)-#num_month-#num_day') ?></lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
        <?php
    }
    }
    eval($plxMotor->plxPlugins->callHook('SitemapStatics')); # Hook Plugins

    // Categories
    foreach ($plxMotor->aCats as $cat_num => $cat_info) {
    if ($cat_info['active'] == 1 and $cat_info['menu'] == 'oui' and ($cat_info['articles'] != 0 or $plxMotor->aConf['display_empty_cat'])) {
        ?>
        <url>
            <loc><?= $plxMotor->urlRewrite("?categorie" . intval($cat_num) . "/" . $cat_info['url']) ?></loc>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
        <?php
    }
    }

    // Plugin hook
    eval($plxMotor->plxPlugins->callHook('SitemapCategories'));

    // Articles
    if ($aFiles = $plxMotor->plxGlob_arts->query('/^\d{4}.(?:\d|home|,)*(?:' . $plxMotor->activeCats . '|home)(?:[0-9]|home|,)*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/', 'art', 'rsort', 0, false, 'before')) {
    $plxRecord_arts = false;
    $array = array();
    foreach ($aFiles as $k => $v) { # On parcourt tous les fichiers
        $array[$k] = $plxMotor->parseArticle(PLX_ROOT . $plxMotor->aConf['racine_articles'] . $v);
    }
    // Records are saved in a PlxRecord object
    $plxRecord_arts = new plxRecord($array);
    if ($plxRecord_arts) {
        // Articles loop
        while ($plxRecord_arts->loop()) {
            $num = intval($plxRecord_arts->f('numero'));
            ?>
            <url>
                <loc><?= $plxMotor->urlRewrite("?article" . $num . "/" . plxUtils::strCheck($plxRecord_arts->f('url'))) ?></loc>
                <lastmod><?= plxDate::formatDate($plxRecord_arts->f('date'), '#num_year(4)-#num_month-#num_day') ?></lastmod>
                <changefreq>monthly</changefreq>
                <priority>0.5</priority>
            </url>
            <?php
        }
    }
    }
    // Plugin hook
    eval($plxMotor->plxPlugins->callHook('SitemapArticles'));
    ?>
</urlset>
<?php

// Buffer ending
$output = XML_HEADER . ob_get_clean();

// Plugin hook
eval($plxMotor->plxPlugins->callHook('SitemapEnd'));

// Charset is forced
header('Content-Type: text/xml; charset=' . PLX_CHARSET);

// Display
echo $output;
?>
