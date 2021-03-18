<?php

const PLX_ROOT = './';
include PLX_ROOT . 'core/lib/config.php';

const PLX_INSTALLER = true;
const USER_ID = '001';
const STAT_ID = '001';
const HTACCESS1 = <<< 'EOT'
<Files *>
    Order allow,deny
    Deny from all
</Files>
EOT;
const HTACCESS2 = <<< 'EOT'
Options -Indexes
EOT;
const INDEX = <<< 'EOT'
<!DOCTYPE html>
<html lang="fr"><head>
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>PluXml</title>
</head><body>
<div style="text-align: center;margin-top:10%;font-size:96pt;">Hello</div>
</body></html>
EOT;

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
loadLang(PLX_CORE . 'lang/' . $lang . '/' . basename(PLX_SCRIPT_INSTALL));
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

// Check plugins directory
$pluginsDir = PLX_ROOT . 'plugins/';
if (!is_dir($pluginsDir)) {
    @mkdir($pluginsDir, 0755, true);
}
$filename = $pluginsDir . '.htaccess';
if (!file_exists($filename)) {
    file_put_contents($filename, HTACCESS2);
}

# Configuration de base
$config = array('title' => 'PluXml',
    'description' => plxUtils::strRevCheck(L_SITE_DESCRIPTION),
    'meta_description' => '',
    'meta_keywords' => '',
    'timezone' => $timezone,
    'allow_com' => 1,
    'mod_com' => 0,
    'mod_art' => 0,
    'enable_rss' => 1,
    'enable_rss_comment' => 1,
    'capcha' => 1,
    'lostpassword' => 1,
    'style' => 'defaut',
    'clef' => plxUtils::charAleatoire(15),
    'bypage' => 5,
    'bypage_archives' => 5,
    'bypage_tags' => 5,
    'bypage_admin' => 10,
    'bypage_admin_coms' => 10,
    'bypage_feed' => 8,
    'tri' => 'desc',
    'tri_coms' => 'asc',
    'images_l' => 800,
    'images_h' => 600,
    'miniatures_l' => 200,
    'miniatures_h' => 100,
    'thumbs' => 0,
    'medias' => 'data/medias/',
    'racine_articles' => 'data/articles/',
    'racine_commentaires' => 'data/commentaires/',
    'racine_statiques' => 'data/statiques/',
    'racine_themes' => 'themes/',
    'racine_plugins' => 'plugins/',
    'homestatic' => '',
    'hometemplate' => 'home.php',
    'urlrewriting' => 0,
    'gzip' => 0,
    'feed_chapo' => 0,
    'feed_footer' => '',
    'version' => PLX_VERSION,
    'default_lang' => $lang,
    'userfolders' => 0,
    'display_empty_cat' => 0,
    'custom_admincss_file' => '',
    'email_method' => 'sendmail',
    'smtp_server' => '',
    'smtp_username' => '',
    'smtp_password' => '',
    'smtp_port' => '465',
    'smtp_security' => 'ssl',
    'smtpOauth2_emailAdress' => '',
    'smtpOauth2_clientId' => '',
    'smtpOauth2_clientSecret' => '',
    'smtpOauth2_refreshToken' => ''
);

function install($content, $config)
{
    // Checks folders to store datas
    $datasDir = dirname(PLX_CONFIG_PATH) . '/';
    if (!is_dir($datasDir) and !mkdir($datasDir)) {
        return;
    }

    $htaccess = $datasDir . '.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, HTACCESS2 . PHP_EOL);
    }

    // Use by plxAdmin::editconfiguration()
    $folders = array(
        'racine_articles' => $datasDir . 'articles/',
        'racine_commentaires' => $datasDir . 'commentaires/',
        'racine_statiques' => $datasDir . 'statiques/',
        'medias' => $datasDir . 'medias/',
        'themes' => 'themes/',
        'plugins' => 'plugins/',
    );

    foreach (array_merge(
                 array(
                     PLX_CONFIG_PATH,
                     PLX_CONFIG_PATH . 'plugins/',
                 ),
                 $folders
             ) as $k => $v) {
        // create folder, index.html et .htaccess as needed
        if (!is_dir($v)) {
            mkdir($v);
        }

        $homepage = $v . 'index.html';
        if (!file_exists($homepage)) {
            file_put_contents($homepage, INDEX);
        }

        $htaccess = $v . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, !in_array($k, array('medias', 'themes', 'plugins')) ? HTACCESS1 . PHP_EOL : HTACCESS2 . PHP_EOL);
        }
    }

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
    $plxAdmin->editConfiguration(array_merge(
        array(
            'description' => plxUtils::strRevCheck(L_SITE_DESCRIPTION),
            'timezone' => $timezone,
            'clef' => plxUtils::charAleatoire(15),
            'default_lang' => $config['default_lang'],
        ),
        $folders
    ));

    // Users creation
    $plxAdmin->editUsers(array(
        'update' => 1, // Any value as true
        'login' => array(USER_ID => trim($content['login'])),
        'name' => array(USER_ID => trim($content['name'])),
        'password' => array(USER_ID => trim($content['pwd'])),
        'email' => array(USER_ID => trim($content['email'])),
    ), true);

    // Plugins file creation
    $xml = XML_HEADER . '<document>' . PHP_EOL . '</document>';
    file_put_contents(path('XMLFILE_PLUGINS'), $xml);

    if (empty($content['data'])) {
        # No files
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
        'group' => array(STAT_ID => ''),
        'name' => array(STAT_ID => plxUtils::strRevCheck(L_DEFAULT_STATIC_TITLE)),
        'url' => array(STAT_ID => ''),
        'active' => array(STAT_ID => 1),
        'menu' => array(STAT_ID => 1),
        'ordre' => array(STAT_ID => 1),
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
        'author' => USER_ID,
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
const PLX_LOGO = PLX_ADMIN_PATH . 'theme/images/pluxml.png';
$logoSize = getimagesize(PLX_LOGO);

?>
<!DOCTYPE html>
<head>
    <meta charset="<?= strtolower(PLX_CHARSET) ?>"/>
    <meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
    <title><?= L_PLUXML_INSTALLATION . ' ' . PLX_VERSION ?></title>
    <link rel="stylesheet" type="text/css" href="<?= PLX_ADMIN_PATH ?>theme/css/knacss.css" media="screen"/>
    <link rel="stylesheet" type="text/css" href="<?= PLX_ADMIN_PATH ?>theme/css/theme.css" media="screen"/>
    <link rel="icon" href="<?= PLX_ADMIN_PATH ?>theme/images/favicon.png"/>
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100vh;
            align-items: center;
        }

        main {
            width: 64rem;
            max-width: 100vw;
            padding: 1rem;
        }

        fieldset, fieldset p {
            margin: 0;
        }

        fieldset {
            padding: 0;
        }

        fieldset > div:not(.center) {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
        }

        .center {
            text-align: center;
        }
    </style>
</head>
<body>
<main>
    <section>

        <div class="center">
            <p><img src="<?= PLX_LOGO ?>" <?= $logoSize[3] ?> alt="PluXml"/></p>
            <p><a href="<?= PLX_URL_REPO ?>" target="_blank"><?= PLX_URL_REPO ?></a></p>
        </div>

        <header class="center">
            <h1><?= L_PLUXML_INSTALLATION . ' ' . PLX_VERSION ?></h1>
        </header>

        <form method="post">
            <?= plxToken::getTokenPostMethod() ?>
            <fieldset>
                <div>
                    <label for="id_default_lang"><?= L_SELECT_LANG ?></label>
                    <div>
                        <?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $lang) ?>&nbsp;
                        <input class="btn--inverse" type="submit" name="select_lang" value="<?= L_INPUT_CHANGE ?>"/>
                    </div>
                </div>
            </fieldset>
        </form>
        <?php
        if (!empty($msg)) {
            ?>
            <div class="alert--danger>"<?= $msg ?></div>
            <?php
        }

        if (is_writable(PLX_ROOT) and plxUtils::testLibGD(false) and plxUtils::testLibXml(false)) {
            ?>
            <form method="post">
                <input type="hidden" name="default_lang" value="<?= $lang ?>"/>
                <?= plxToken::getTokenPostMethod() ?>
                <fieldset>
                    <div>
                        <label for="id_default_lang"><?= L_INSTALL_DATA ?></label>
                        <?php plxUtils::printSelect('data', array('1' => L_YES, '0' => L_NO), $data) ?>
                    </div>
                    <div>
                        <label for="id_name"><?= L_USERNAME ?></label>
                        <?php plxUtils::printInput('name', $name, 'text', '20-255', false, '', '', 'autofocus required') ?>
                    </div>
                    <div>
                        <label for="id_login"><?= L_PROFIL_LOGIN ?></label>
                        <?php plxUtils::printInput('login', $login, 'text', '20-255', '', '', '', 'required') ?>
                    </div>
                    <div>
                        <label for="id_pwd"><?= L_PASSWORD ?></label>
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
                    <div>
                        <label for="id_pwd2"><?= L_CONFIRM_PASSWORD ?></label>
                        <?php plxUtils::printInput('pwd2', '', 'password', '20-255', '', '', '', 'required') ?>
                    </div>
                    <div>
                        <label for="id_email"><?= L_MAIL_ADDRESS ?></label>
                        <?php plxUtils::printInput('email', $email, 'email', '20-255', '', '', '', 'required') ?>
                    </div>
                    <div>
                        <label for="id_timezone"><?= L_TIMEZONE ?></label>
                        <?php plxUtils::printSelect('timezone', plxTimezones::timezones(), $timezone); ?>
                    </div>
                    <div class="center">
                        <input class="btn--primary" type="submit" name="install" value="<?= L_INPUT_INSTALL ?>"/>
                    </div>
                </fieldset>
            </form>
            <?php
        }
        ?>

        <ul class="unstyled">
            <li><strong><?= L_PLUXML_VERSION; ?> <?= PLX_VERSION ?> (<?= L_INFO_CHARSET ?> <?= PLX_CHARSET ?>
                    )</strong></li>
            <li><?= L_INFO_PHP_VERSION . ' : ' . phpversion() ?></li>
            <?php if (!empty($_SERVER['SERVER_SOFTWARE'])) { ?>
                <li><?= $_SERVER['SERVER_SOFTWARE']; ?></li>
            <?php } ?>
            <?php plxUtils::testWrite(PLX_ROOT) ?>
            <?php plxUtils::testWrite(PLX_ROOT . 'plugins') ?>
            <?php plxUtils::testWrite(PLX_ROOT . 'themes') ?>
            <?php plxUtils::testModReWrite() ?>
            <?php plxUtils::testLibGD() ?>
            <?php plxUtils::testLibXml() ?>
            <?php plxUtils::testMail() ?>
        </ul>

    </section>
</main>
<script src="<?= PLX_ADMIN_PATH ?>scripts/visual.js"></script>
</body>
</html>
