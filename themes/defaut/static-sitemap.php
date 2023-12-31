<?php
if (!defined('PLX_ROOT')) {
    exit;
}

$homeStaticId = $plxShow->plxMotor->aConf['homestatic'];
$stats = array_filter(
    $plxShow->plxMotor->aStats,
    function ($item, $id) use ($homeStaticId) {
        return (!empty($item['active']) and $item['template'] != basename(__FILE__) and $id != $homeStaticId);
    },
    ARRAY_FILTER_USE_BOTH
);

# PluXml ne trie pas les pages statiques par groupe
uasort($stats, function ($a, $b) {
    if (empty($a['group'])) {
        return 1;
    }
    if (empty($b['group'])) {
        return -1;
    }
    if ($a['group'] == $b['group']) {
        return strcmp($a['name'], $b['name']);
    }
    return strcmp($a['group'], $b['group']);
});

include 'header.php';

?>
<!-- begin of static-sitemap.php -->
            <article class="static article sitemap" id="static-page-<?= $plxShow->staticId(); ?>">
                <header class="static-header">
                    <h2><?php $plxShow->staticTitle(); ?></h2>
                </header>
                <main>
<?php
$plxShow->staticContent();
?>
                    <div>
                        <ul class="tabs-container">
<?php
if (!empty($stats)) {
    ?>
                            <li>
                                <input type="radio" name="tabs0" id="tabs0-0" class="toggle" checked />
                                <h3><label for="tabs0-0"><?php $plxShow->lang('STATIC_PAGES'); ?></label></h3>
                                <ul class="tab-content">
    <?php
    foreach ($stats as $statId => $statInfos) {
        ?>
                                    <li><a href="<?php $plxShow->urlRewrite('index.php?static' . ltrim($statId, '0') . '/' . $statInfos['url']); ?>"><?= !empty($statInfos['group']) ? '<span>' . $statInfos['group'] . '</span> : ' : '' ?><?= $statInfos['name']?></a></li>
        <?php
    } ?>
                                </ul>
                            </li>
    <?php
}

$artsRoot = PLX_ROOT . $plxShow->plxMotor->aConf['racine_articles'];

if (!empty($homeStaticId)) {
    # articles en page d'accueil
    $pattern = '@^\d{4}\.(\d{3},)*home(,\d{3})*\..*@';
    $artFiles = $plxShow->plxMotor->plxGlob_arts->query($pattern, 'art', $plxShow->plxMotor->aConf['tri'], 0, false, 'before');
    if (is_array($artFiles)) {
        ?>
                            <li>
                                <input type="radio" name="tabs0" id="tabs0-1" class="toggle" />
                                <h3><label for="tabs0-1"><?= L_BLOG ?></label></h3>
                                <div class="tab-content">
                                    <h4><a href="<?php $plxShow->urlRewrite('index.php?blog'); ?>"><?php $plxShow->lang('DISPLAY_BLOG') ?></a></h4>
                                    <ul class="articles">
        <?php
        foreach ($artFiles as $filename) {
            $artInfos = $plxShow->plxMotor->parseArticle($artsRoot . $filename); ?>
                                        <li><a href="<?php $plxShow->urlRewrite('index.php?article' . ltrim($artInfos['numero']) . '/' . $artInfos['url']); ?>"><?= $artInfos['title'] ?></a><span><?php $plxShow->lang('PUBLISHED_ON'); ?> <?= plxDate::formatDate($artInfos['date'], '#num_day #month #num_year(4)') ?></span></li>
            <?php
        } ?>
                                    </ul>
                                </div>
                            </li>
        <?php
    }
}
?>
                            <li>
                                <input type="radio" name="tabs0" id="tabs0-2" class="toggle" />
                                <h3><label for="tabs0-2"><?= ucfirst(L_CATEGORIES) ?></label></h3>
                                <ul class="tabs-container">
<?php
$cats = array_filter(
    $plxShow->plxMotor->aCats,
    function ($item) {
        return !empty($item['active']);
    }
);

$checked = ' checked';
foreach ($cats as $catId => $catInfos) {
    $pattern = '@^\d{4}\.';
    $pattern .= empty($homeStaticId) ? '(home,|\d{3},)*' . $catId  . '(,home|,\d{3})*' : '(\d{3},)*' . $catId  . '(,\d{3})*';
    $pattern .= '\..*@';
    $artFiles = $plxShow->plxMotor->plxGlob_arts->query($pattern, 'art', $catInfos['tri'], 0, false, 'before');
    if (!empty($artFiles)) {
        $id = 'tabs-cats-' . $catId; ?>
                                    <li>
                                        <input type="radio" name="tabs-cats" id="<?= $id ?>" class="toggle"<?= $checked ?> />
                                        <h4><label for="<?= $id ?>"><?= $catInfos['name'] ?></label></h4>
                                        <div class="tab-content level-2">
                                            <a class="link" href="<?php $plxShow->urlRewrite('index.php?categorie' . ltrim($catId, '0') . '/' . $catInfos['url']); ?>"><?php $plxShow->lang('DISPLAY_CATEGORY') ?></a>
                                            <ul class="articles">
        <?php
        foreach ($artFiles as $filename) {
            $artInfos = $plxShow->plxMotor->parseArticle($artsRoot . $filename); ?>
                                                <li><a href="<?php $plxShow->urlRewrite('index.php?article' . ltrim($artInfos['numero']) . '/' . $artInfos['url']); ?>"><?= $artInfos['title'] ?></a><span><?php $plxShow->lang('PUBLISHED_ON'); ?> <?= plxDate::formatDate($artInfos['date'], '#num_day #month #num_year(4)') ?></span></li>
            <?php
        } ?>
                                            </ul>
                                        </div>
                                    </li>
        <?php
    }

    $checked = '';
}
?>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </main>
            </article>
<!-- end of static-sitemap.php -->

<?php
include 'footer.php';
