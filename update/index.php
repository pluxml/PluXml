<?php

const PLX_ROOT = '../';
include PLX_ROOT . 'core/lib/config.php';

const PLX_UPDATER = true;

// Languages loading
$lang = (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : DEFAULT_LANG;
if (isset($_POST['default_lang'])) $lang = $_POST['default_lang'];
if (!array_key_exists($lang, plxUtils::getLangs())) {
    $lang = DEFAULT_LANG;
}
loadLang(PLX_CORE . 'lang/' . $lang . '/core.php');
loadLang(PLX_CORE . 'lang/' . $lang . '/admin.php');
loadLang(PLX_CORE . 'lang/' . $lang . '/update.php');

// PHP version check
if (version_compare(PHP_VERSION, PHP_VERSION_MIN, '<')) {
    header('Content-Type: text/plain charset=UTF-8');
    printf(L_WRONG_PHP_VERSION, PHP_VERSION_MIN);
    exit;
}

// Remove slash characters
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_POST = plxUtils::unSlash($_POST);
}

// Start PHP session
plxUtils::cleanHeaders();
session_set_cookie_params(0, "/", $_SERVER['SERVER_NAME'], isset($_SERVER["HTTPS"]), true);
session_start();

// CSRF token validation
plxToken::validateFormToken($_POST);

// Data
$plxUpdater = new plxUpdater();
$root = PLX_ROOT . dirname(PLX_CONFIG_PATH);
$writable = is_writable($root);
$version = isset($_POST['version']) ? $_POST['version'] : $plxUpdater->oldVersion;
?>
<!DOCTYPE html>
<head>
    <meta name="robots" content="noindex, nofollow"/>
    <meta charset="<?= strtolower(PLX_CHARSET) ?>"/>
    <meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
    <title><?= L_UPDATE_TITLE . ' ' . plxUtils::strCheck($plxUpdater->newVersion) ?></title>
    <link rel="stylesheet" type="text/css" href="<?= PLX_ADMIN_PATH ?>theme/css/knacss.css" media="screen"/>
    <link rel="stylesheet" type="text/css" href="<?= PLX_ADMIN_PATH ?>theme/css/theme.css" media="screen"/>
    <link rel="stylesheet" href="<?= PLX_ADMIN_PATH ?>theme/fontello/css/fontello.css" media="screen"/>
    <link rel="icon" href="<?= PLX_ADMIN_PATH ?>theme/images/favicon.png"/>
</head>
<body>
<main class="mal flex-container">
    <section class="item-center">

        <div class="txtcenter">
            <p><img src="<?= PLX_ADMIN_PATH ?>theme/images/pluxml.png" alt="PluXml"/></p>
            <p><a href="<?= PLX_URL_REPO ?>" target="_blank"><?= PLX_URL_REPO ?></a></p>
        </div>

        <header class="txtcenter">
            <h1><?= L_UPDATE_TITLE . ' ' . plxUtils::strCheck($plxUpdater->newVersion) ?></h1>
        </header>

        <?php if (!$writable or empty($_POST['submit'])): ?>
            <?php if (version_compare($plxUpdater->oldVersion, $plxUpdater->newVersion) >= 0): ?>
                <div class="txtcenter">
                    <p><strong><?= L_UPDATE_UPTODATE ?></strong></p>
                    <p><?= L_UPDATE_NOT_AVAILABLE ?></p>
                    <p><a href="<?= PLX_ROOT; ?>index.php" title="<?= L_UPDATE_BACK ?>"><?= L_UPDATE_BACK ?></a></p>
                </div>
            <?php else: ?>
                <form method="post" class="txtcenter">
                    <fieldset class=pln">
                        <div class="inbl">
                            <label for="id_default_lang"><?= L_SELECT_LANG ?></label>
                        </div>
                        <div class="inbl">
                            <?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $lang) ?>&nbsp;
                            <input class="btn--inverse" type="submit" name="select_lang"
                                   role="button" value="<?= L_INPUT_CHANGE ?>"/>
                            <?= plxToken::getTokenPostMethod() ?>
                        </div>
                    </fieldset>
                </form>
                <?php if (!$writable): ?>
                    <p class="alert--danger"><?php printf(L_WRITE_NOT_ACCESS, $root) ?></p>
                <?php endif; ?>
                <form method="post" class="txtcenter">
                    <fieldset>
                        <p><strong><?= L_UPDATE_WARNING1 . ' ' . $plxUpdater->oldVersion ?></strong></p>
                        <?php if (empty($plxUpdater->oldVersion)): ?>
                            <p><?= L_UPDATE_SELECT_VERSION ?></p>
                            <p><?php plxUtils::printSelect('version', plxUpdater::VERSIONS); ?></p>
                            <p><?= L_UPDATE_WARNING2 ?></p>
                        <?php endif; ?>
                        <p><?php printf(L_UPDATE_WARNING3, preg_replace('@^([^/]+).*@', '$1', $plxUpdater->plxAdmin->aConf['racine_articles'])); ?></p>
                        <p class="mtm"><input type="submit" name="submit" role="button"
                                              value="<?= L_UPDATE_START ?>"
                                              <?php if (!$writable): ?>disabled<?php endif; ?> /></p>
                        <?= plxToken::getTokenPostMethod() ?>
                    </fieldset>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <div class="txtcenter">
                <?php $plxUpdater->startUpdate($version); ?>
                <form action="<?= PLX_ROOT ?>">
                    <input type="submit" role="button" value="<?= L_UPDATE_BACK ?>"/>
                </form>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>