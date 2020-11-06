<?php

const PLX_ROOT = './';
include PLX_ROOT . 'core/lib/config.php';

const PLX_INSTALLER = true;

// PHP session init
session_set_cookie_params(0, "/", $_SERVER['SERVER_NAME'], isset($_SERVER["HTTPS"]), true);
session_start();

// Languages loading
$lang = (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : DEFAULT_LANG;
if (!empty($_POST) && $_POST['default_lang']) {
    $lang = $_POST['default_lang'];
}
if (!array_key_exists($lang, plxUtils::getLangs())) {
    $lang = DEFAULT_LANG;
}
loadLang(PLX_CORE . 'lang/' . $lang . '/install.php');
loadLang(PLX_CORE . 'lang/' . $lang . '/core.php');

// PHP version check
if (version_compare(PHP_VERSION, PHP_VERSION_MIN, '<')) {
    header('Content-Type: text/plain; charset=UTF-8');
    printf(L_WRONG_PHP_VERSION, PHP_VERSION_MIN);
    exit();
}

// Check if PluXml already installed
if (file_exists(path('XMLFILE_PARAMETERS'))) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo L_ERR_PLUXML_ALREADY_INSTALLED;
    exit();
}

// CSRF token control
plxToken::validateFormToken($_POST);

// Escape characters
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_POST = plxUtils::unSlash($_POST);
}

// Timezone init
$timezone = 'Europe/Paris';
if (isset($_POST['timezone'])) {
    $timezone = $_POST['timezone'];
}
if (!array_key_exists($timezone, plxTimezones::timezones())) {
    $timezone = date_default_timezone_get();
}

// Check directories
$folders = array(
    PLX_ROOT . $config['medias'],
    PLX_ROOT . PLX_CONFIG_PATH . 'plugins',
    PLX_ROOT . dirname(PLX_CONFIG_PATH) . '/templates'
);
foreach ($folders as $f) {
    if (!is_dir($f)) {
        @mkdir($f, 0755, true);
    }
}

function install($content, $config)
{

    // Default configuration
    $root = dirname(PLX_CONFIG_PATH) . '/';

    // Timezone init
    if (isset($_POST['timezone'])) {
        $timezone = $_POST['timezone'];
        if (array_key_exists($timezone, plxTimezones::timezones())) {
            $config['timezone'] = $timezone;
        }
    }
    date_default_timezone_set($config['timezone']);

    // Get the PlxAdmin facade
    $plxAdmin = plxAdmin::getInstance();

    // Configuration edit
    $racineStatiques = $root . 'statiques/';
    $plxAdmin->editConfiguration(array(
        'description' => plxUtils::strRevCheck(L_SITE_DESCRIPTION),
        'timezone' => $timezone,
        'clef' => plxUtils::charAleatoire(15),
        'medias' => $root . 'medias/',
        'racine_articles' => $root . 'articles/',
        'racine_commentaires' => $root . 'commentaires/',
        'racine_statiques' => $racineStatiques,
        'default_lang' => $config['default_lang'],
    ));

    // Users creation
    $userId = '001';
    $plxAdmin->editUsers(array(
        'update' => 1,
        'userNum' => array($userId),
        $userId . '_profil' => PROFIL_ADMIN,
        $userId . '_active' => 1,
        $userId . '_login' => trim($content['login']),
        $userId . '_name' => trim($content['name']),
        $userId . '_password' => trim($content['pwd']),
        $userId . '_email' => trim($content['email']),
    ), true);

    // Plugins file creation
    $xml = XML_HEADER . '<document>' . PHP_EOL . '</document>';
    plxUtils::write($xml, path('XMLFILE_PLUGINS'));

    if (empty($content['data'])) {
        # Pas de données
        return;
    }

    // Categories creation
    $plxAdmin->editCategories(array(
        'new_category' => 1,
        'new_catname' => plxUtils::strRevCheck(L_DEFAULT_CATEGORY_TITLE),
    ), true);

    // Pages creation
    $idStat = '001';
    $plxAdmin->editStatiques(array(
        'update' => 1,
        'staticNum' => array($idStat),
        $idStat . '_group' => '',
        $idStat . '_name' => plxUtils::strRevCheck(L_DEFAULT_STATIC_TITLE),
        $idStat . '_active' => 1,
        $idStat . '_menu' => 'oui',
        $idStat . '_ordre' => 1,
    ));
    $plxAdmin->editStatique(array(
        'id' => $idStat,
        'content' => file_get_contents(PLX_CORE . '/templates/install-page.txt'),
    ));

    // First article creation
    list ($chapo, $article) = explode('-----', file_get_contents(PLX_CORE . '/templates/install-article.txt'));
    $idArt = '0001';
    $plxAdmin->editArticle(array(
        'title' => plxUtils::strRevCheck(L_DEFAULT_ARTICLE_TITLE),
        'author' => $userId,
        'catId' => array_keys($plxAdmin->aCats), // Juste une catégorie créée
        'allow_com' => 1,
        'template' => 'article.php',
        'chapo' => $chapo,
        'content' => $article,
        'tags' => 'PluXml',
    ), $idArt);

    // First comment creation
    $plxAdmin->addCommentaire(array(
        'author' => 'PluXml',
        'type' => 'normal',
        'ip' => plxUtils::getIp(),
        'mail' => 'demo@pluxml.org',
        'site' => PLX_URL_REPO,
        'content' => plxUtils::strRevCheck(L_DEFAULT_COMMENT_CONTENT),
        'parent' => '',
        'filename' => '0001.' . date('U') . '-1.xml',
    ));
}

// Errors messages
$msg = '';
if (!empty($_POST['install'])) {
    if (trim($_POST['name'] == '')) {
        $msg = L_ERR_MISSING_USER;
    } elseif (trim($_POST['login'] == '')) {
        $msg = L_ERR_MISSING_LOGIN;
    } elseif (trim($_POST['pwd'] == '')) {
        $msg = L_ERR_MISSING_PASSWORD;
    } elseif ($_POST['pwd'] != $_POST['pwd2']) {
        $msg = L_ERR_PASSWORD_CONFIRMATION;
    } elseif (trim($_POST['email'] == '')) {
        $msg = L_ERR_MISSING_EMAIL;
    } else {
        install($_POST, array(
            'timezone' => $timezone,
            'default_lang' => $lang,
        ));
        header('Location: ' . plxUtils::getRacine());
        exit();
    }
    $name = $_POST['name'];
    $login = $_POST['login'];
    $email = $_POST['email'];
    $data = $_POST['data'];
} else {
    $name = '';
    $login = '';
    $email = '';
    $data = '1';
}

plxUtils::cleanHeaders();
?>
<!DOCTYPE html>
<head>
    <meta charset="<?= strtolower(PLX_CHARSET) ?>"/>
    <meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
    <title><?= L_PLUXML_INSTALLATION . ' ' . PLX_VERSION ?></title>
    <link rel="stylesheet" type="text/css" href="<?= PLX_ADMIN_PATH ?>theme/css/knacss.css" media="screen"/>
    <link rel="stylesheet" type="text/css" href="<?= PLX_ADMIN_PATH ?>theme/css/theme.css" media="screen"/>
    <link rel="icon" href="<?= PLX_ADMIN_PATH ?>theme/images/favicon.png"/>
    <script src="<?= PLX_CORE ?>lib/visual.js"></script>
</head>
<body>
<main class="mal flex-container">
    <section class="pal item-center">

        <div class="txtcenter">
            <p><img src="<?= PLX_ADMIN_PATH ?>theme/images/pluxml.png" alt="PluXml"/></p>
            <p><a href="<?= PLX_URL_REPO ?>" target="_blank"><?= PLX_URL_REPO ?></a></p>
        </div>

        <header class="txtcenter">
            <h1><?= L_PLUXML_INSTALLATION . ' ' . PLX_VERSION ?></h1>
        </header>

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

        <?php if ($msg != '') echo '<div class="alert--danger>"' . $msg . '</div>'; ?>

        <form method="post">
            <fieldset class="">
                <div class="grid-2 pbs">
                    <label for="id_default_lang"><?php echo L_INSTALL_DATA ?>&nbsp;:</label>
                    <?php plxUtils::printSelect('data', array('1' => L_YES, '0' => L_NO), $data) ?>
                </div>
                <div class="grid-2 pbs">
                    <label for="id_name"><?php echo L_USERNAME ?>&nbsp;:</label>
                    <?php plxUtils::printInput('name', $name, 'text', '20-255', false, '', '', 'autofocus required') ?>
                </div>
                <div class="grid-2 pbs">
                    <label for="id_login"><?php echo L_PROFIL_LOGIN ?>&nbsp;:</label>
                    <?php plxUtils::printInput('login', $login, 'text', '20-255', '', '', '', 'required') ?>
                </div>
                <div class="grid-2 pbs">
                    <label for="id_pwd"><?php echo L_PASSWORD ?>&nbsp;:</label>
                    <?php
                    list ($very, $weak, $good, $strong) = array(
                        L_PWD_VERY_WEAK,
                        L_PWD_WEAK,
                        L_PWD_GOOD,
                        L_PWD_STRONG
                    );
                    $extras = <<< EOT
onkeyup="pwdStrength(this.id, ['$very', '$weak', '$good', '$strong']);" required
EOT;
                    ?>
                    <div>
                        <?php plxUtils::printInput('pwd', '', 'password', '20-255', false, 'w100', '', $extras); ?>
                        <p id="id_pwd_strenght"></p>
                    </div>
                </div>
                <div class="grid-2 pbs">
                    <label for="id_pwd2"><?php echo L_CONFIRM_PASSWORD ?>&nbsp;:</label>
                    <?php plxUtils::printInput('pwd2', '', 'password', '20-255', '', '', '', 'required') ?>
                </div>
                <div class="grid-2 pbs">
                    <label for="id_email"><?php echo L_USER_EMAIL ?>&nbsp;:</label>
                    <?php plxUtils::printInput('email', $email, 'email', '20-255', '', '', '', 'required') ?>
                </div>
                <div class="grid-2 pbs">
                    <label for="id_timezone"><?php echo L_TIMEZONE ?>&nbsp;:</label>
                    <?php plxUtils::printSelect('timezone', plxTimezones::timezones(), $timezone); ?>
                </div>
                <div class="txtcenter">
                    <input class="btn--primary" type="submit" name="install" value="<?php echo L_INPUT_INSTALL ?>"/>
                </div>
                <?= plxToken::getTokenPostMethod() ?>
            </fieldset>
        </form>

        <ul class="unstyled">
            <li><strong><?= L_PLUXML_VERSION; ?> <?= PLX_VERSION ?> (<?= L_INFO_CHARSET ?> <?= PLX_CHARSET ?>
                    )</strong></li>
            <li><?= L_INFO_PHP_VERSION . ' : ' . phpversion() ?></li>
            <?php if (!empty($_SERVER['SERVER_SOFTWARE'])) { ?>
                <li><?= $_SERVER['SERVER_SOFTWARE']; ?></li>
            <?php } ?>
            <?php plxUtils::testWrite(PLX_ROOT) ?>
            <?php plxUtils::testWrite(PLX_ROOT . PLX_CONFIG_PATH) ?>
            <?php plxUtils::testWrite(PLX_ROOT . PLX_CONFIG_PATH . 'plugins/') ?>
            <?php plxUtils::testWrite(PLX_ROOT . $config['racine_articles']) ?>
            <?php plxUtils::testWrite(PLX_ROOT . $config['racine_commentaires']) ?>
            <?php plxUtils::testWrite(PLX_ROOT . $config['racine_statiques']) ?>
            <?php plxUtils::testWrite(PLX_ROOT . $config['medias']) ?>
            <?php plxUtils::testWrite(PLX_ROOT . $config['racine_plugins']) ?>
            <?php plxUtils::testWrite(PLX_ROOT . $config['racine_themes']) ?>
            <?php plxUtils::testModReWrite() ?>
            <?php plxUtils::testLibGD() ?>
            <?php plxUtils::testLibXml() ?>
            <?php plxUtils::testMail() ?>
        </ul>

    </section>
</main>
<script src="<?= PLX_CORE ?>lib/visual.js"></script>
</body>
</html>
