<?php

const PLX_ROOT = '../';
include PLX_ROOT . 'core/lib/config.php';

const PLX_UPDATER = true;

# Chargement des langues
$lang = (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : DEFAULT_LANG;
if (isset($_POST['default_lang'])) $lang = $_POST['default_lang'];
if (!array_key_exists($lang, plxUtils::getLangs())) {
    $lang = DEFAULT_LANG;
}
loadLang(PLX_CORE . 'lang/' . $lang . '/core.php');
loadLang(PLX_CORE . 'lang/' . $lang . '/admin.php');
loadLang(PLX_CORE . 'lang/' . $lang . '/update.php');

# On vérifie que PHP 5 ou superieur soit installé
if (version_compare(PHP_VERSION, PHP_VERSION_MIN, '<')) {
    header('Content-Type: text/plain charset=UTF-8');
    printf(L_WRONG_PHP_VERSION, PHP_VERSION_MIN);
    exit;
}

# Echappement des caractères
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_POST = plxUtils::unSlash($_POST);
}

# Création de l'objet principal et lancement du traitement
$plxUpdater = new plxUpdater();

plxUtils::cleanHeaders();
session_set_cookie_params(0, "/", $_SERVER['SERVER_NAME'], isset($_SERVER["HTTPS"]), true);
session_start();
# Control du token du formulaire
plxToken::validateFormToken($_POST);
?>
<!DOCTYPE html>
<head>
    <meta name="robots" content="noindex, nofollow"/>
    <meta charset="<?= strtolower(PLX_CHARSET) ?>"/>
    <meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
    <title><?= L_UPDATE_TITLE . ' ' . plxUtils::strCheck($plxUpdater->newVersion) ?></title>
    <link rel="stylesheet" type="text/css" href="<?= PLX_ADMIN_PATH ?>theme/css/knacss.css" media="screen"/>
    <link rel="stylesheet" type="text/css" href="<?= PLX_ADMIN_PATH ?>theme/css/theme.css" media="screen"/>
    <link rel="icon" href="<?= PLX_ADMIN_PATH ?>theme/images/favicon.png"/>
</head>
<body>
<main class="mal flex-container">
    <section class="pal item-center">

        <div class="txtcenter">
            <p><img src="<?= PLX_ADMIN_PATH ?>theme/images/pluxml.png" alt="PluXml"/></p>
            <p><a href="<?= PLX_URL_REPO ?>" target="_blank"><?= PLX_URL_REPO ?></a></p>
        </div>

        <header class="txtcenter">
            <h1><?= L_UPDATE_TITLE . ' ' . plxUtils::strCheck($plxUpdater->newVersion) ?></h1>
        </header>

        <?php
        $root = PLX_ROOT . dirname(PLX_CONFIG_PATH);
        $writable = is_writable($root);
        if (!$writable) {
            ?>
            <p class="alert--danger"><?php printf(L_WRITE_NOT_ACCESS, $root) ?></p>
            <?php
        }

        if (!$writable or empty($_POST['submit'])) {
            if (version_compare($plxUpdater->oldVersion, $plxUpdater->newVersion) >= 0) {
                ?>
                <p><strong><?= L_UPDATE_UPTODATE ?></strong></p>
                <p><?= L_UPDATE_NOT_AVAILABLE ?></p>
                <p><a href="<?= PLX_ROOT; ?>index.php" title="<?= L_UPDATE_BACK ?>"><?= L_UPDATE_BACK ?></a></p>
                <?php
            } else {
                ?>

                <div class="txtcenter">
                    <form method="post">
                        <fieldset class=pln">
                            <div class="inbl">
                                <label for="id_default_lang"><?php echo L_SELECT_LANG ?>&nbsp;:</label>
                            </div>
                            <div class="inbl">
                                <?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $lang) ?>&nbsp;
                                <input class="btn--inverse" type="submit" name="select_lang"
                                       value="<?php echo L_INPUT_CHANGE ?>"/>
                                <?php echo plxToken::getTokenPostMethod() ?>
                            </div>
                        </fieldset>
                    </form>
                </div>

                <form method="post">
                    <fieldset>
                        <p><strong><?= L_UPDATE_WARNING1 . ' ' . $plxUpdater->oldVersion ?></strong></p>
                        <?php
                        if (empty($plxUpdater->oldVersion)) {
                            ?>
                            <p><?= L_UPDATE_SELECT_VERSION ?></p>
                            <p><?php plxUtils::printSelect('version', plxUpdater::VERSIONS); ?></p>
                            <p><?= L_UPDATE_WARNING2 ?></p>
                            <?php
                        }
                        ?>
                        <p><?= L_UPDATE_WARNING3 ?></p>
                        <p><input type="submit" name="submit" value="<?= L_UPDATE_START ?>" <?php if (!$writable) {
                                echo 'disabled';
                            } ?> /></p>
                        <?php echo plxToken::getTokenPostMethod() ?>
                    </fieldset>
                </form>
                <?php
            }
        } else {
            $version = isset($_POST['version']) ? $_POST['version'] : $plxUpdater->oldVersion;
            $plxUpdater->startUpdate($version);
            ?>
            <form action="<?= PLX_ROOT ?>">
                <input type="submit" value="<?= L_UPDATE_BACK ?>"/>
            </form>
            <?php
        }
        ?>
    </section>
</main>
</body>
</html>
