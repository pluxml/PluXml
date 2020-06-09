<?php

/**
 * PluXml backoffice authentication page
 *
 * @package PLX
 * @author Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

const PLX_AUTHPAGE = true;

include 'prepend.php';

// CSRF token validation
plxToken::validateFormToken($_POST);

// Brut force protection
// connexion maximum attempt number in the time limit
$maxlogin['counter'] = 99;
// Wait time (ine minutes) until the next attempt if maximum is exceeded
$maxlogin['timer'] = 3 * 60;

// Alert message initialisation
$msg = '';
$css = '';

// Plugins hook
eval($plxAdmin->plxPlugins->callHook('AdminAuthPrepend'));

// Identifying connexion error
if (isset($_SESSION['maxtry'])) {
    if (intval($_SESSION['maxtry']['counter']) >= $maxlogin['counter'] and (time() < $_SESSION['maxtry']['timer'] + $maxlogin['timer'])) {
        // write in the logs if thee unsucessfull connexion attempts
        @error_log('PluXml: Max login failed. IP : ' . plxUtils::getIp());
        // alert to display
        $msg = sprintf(L_ERR_MAXLOGIN, ($maxlogin['timer'] / 60));
        $css = 'alert red';
    }
    if (time() > ($_SESSION['maxtry']['timer'] + $maxlogin['timer'])) {
        // reset brut force control if wait time is passed
        $_SESSION['maxtry']['counter'] = 0;
        $_SESSION['maxtry']['timer'] = time();
    }
} else {
    // attempt count initialisation
    $_SESSION['maxtry']['counter'] = 0;
    $_SESSION['maxtry']['timer'] = time();
}

// Attempt number incrementation
$redirect = 'index.php';
if (!empty($_GET['p']) and $css == '') {
    $_SESSION['maxtry']['counter']++;
    $racine = parse_url($plxAdmin->aConf['racine']);
    $get_p = parse_url(urldecode($_GET['p']));
    $css = (!$get_p or (isset($get_p['host']) and $racine['host'] != $get_p['host']));
    if (!$css and !empty($get_p['path']) and file_exists(PLX_ADMIN_PATH . basename($get_p['path']))) {
        // URL parameters filter
        $query = '';
        if (isset($get_p['query'])) {
            $query = strtok($get_p['query'], '=');
            $query = ($query[0] != 'd' ? '?' . $get_p['query'] : '');
        }
        // redirect RUL
        $redirect = $get_p['path'] . $query;
    }
}

// Disconnection (URL parameter is "?d=1")
if (!empty($_GET['d']) and $_GET['d'] == 1) {
    $_SESSION = array();
    session_destroy();
    header('Location: auth.php');
    exit;
}

// Authentication
if (!empty($_POST['login']) and !empty($_POST['password']) and $css == '') {
    $connected = false;
    foreach ($plxAdmin->aUsers as $userid => $user) {
        if ($_POST['login'] == $user['login'] and sha1($user['salt'] . md5($_POST['password'])) === $user['password'] and $user['active'] and !$user['delete']) {
            $_SESSION['user'] = $userid;
            $_SESSION['hash'] = plxUtils::charAleatoire(10);
            $_SESSION['domain'] = $session_domain;
            $_SESSION['admin_lang'] = $user['lang'];
            $connected = true;
            break;
        }
    }
    if ($connected) {
        unset($_SESSION['maxtry']);
        header('Location: ' . htmlentities($redirect));
        exit;
    } else {
        $msg = L_ERR_WRONG_PASSWORD;
        $css = 'alert red';
    }
}

// Send lost password e-mail
if (!empty($_POST['lostpassword_id'])) {
    if (!empty($plxAdmin->sendLostPasswordEmail($_POST['lostpassword_id']))) {
        $msg = L_LOST_PASSWORD_SUCCESS;
        $css = 'alert green';
    } else {
        @error_log("Lost password error. ID : " . $_POST['lostpassword_id'] . " IP : " . plxUtils::getIp());
        $msg = L_UNKNOWN_ERROR;
        $css = 'alert red';
    }
}

// Change password
if (!empty($_POST['editpassword'])) {
    unset($_SESSION['error']);
    unset($_SESSION['info']);
    $plxAdmin->editPassword($_POST);
    if (!empty($msg = isset($_SESSION['error']) ? $_SESSION['error'] : '')) {
        $css = 'alert red';
    } else {
        if (!empty($msg = isset($_SESSION['info']) ? $_SESSION['info'] : '')) {
            $css = 'alert green';
        }
    }
    unset($_SESSION['error']);
    unset($_SESSION['info']);
}

// View construction
plxUtils::cleanHeaders();
?>
<!DOCTYPE html>
<html lang="<?= $plxAdmin->aConf['default_lang'] ?>">
<head>
    <meta name="robots" content="noindex, nofollow" />
    <meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
    <title>PluXml - <?= L_AUTH_PAGE_TITLE ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= strtolower(PLX_CHARSET); ?>" />
    <link rel="stylesheet" type="text/css" href="theme/css/knacss.css?v=<?= PLX_VERSION ?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="theme/css/theme.css?v=<?= PLX_VERSION ?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="theme/fontello/css/fontello.css?v=<?= PLX_VERSION ?>" media="screen" />
    <link rel="icon" href="theme/images/favicon.png" />
    <?php
    PlxUtils::printLinkCss($plxAdmin->aConf['custom_admincss_file'], true);
    PlxUtils::printLinkCss($plxAdmin->aConf['racine_plugins'].'admin.css', true);

    eval($plxAdmin->plxPlugins->callHook('AdminAuthEndHead'));

    $logo = (!empty($plxAdmin->aConf['thumbnail']) and file_exists(PLX_ROOT . $plxAdmin->aConf['thumbnail'])) ? PLX_ROOT . $plxAdmin->aConf['thumbnail'] : 'theme/images/pluxml.png';
    $logoSize = getimagesize($logo);
    ?>
    <script src="<?= PLX_CORE ?>lib/visual.js?v=<?= PLX_VERSION ?>"></script>
</head>
<body id="auth">
<main id="app" class="auth flex-container--column">
    <section class="w350p item-center">
        <div class="logo mam"><a class="txtcenter" href="<?= PLX_ROOT ?>"><img src="<?= $logo ?>" alt="Logo" <?= $logoSize[3] ?> /></a></div>
        <?php eval($plxAdmin->plxPlugins->callHook('AdminAuthBegin')) ?>
        <?php if (isset($_GET['action']) && $_GET['action'] == 'lostpassword'): ?>
            <div class="form mam pas">
                <?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTopLostPassword')); ?>
                <form action="auth.php<?= !empty($redirect)?'?p='.plxUtils::strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
                    <fieldset class="man pan">
                        <div class="flex-container--column">
                            <?= PlxToken::getTokenPostMethod() ?>
                            <h1 class="h3-like txtcenter mam"><?= L_LOST_PASSWORD ?></h1>
                            <?php PlxUtils::printInput('lostpassword_id', (!empty($_POST['lostpassword_id']))?PlxUtils::strCheck($_POST['lostpassword_id']):'', 'text', '-64',false,'txt',L_AUTH_LOST_FIELD,'autofocus required');?>
                            <input class="btn--primary" role="button" type="submit" value="<?= L_SUBMIT_BUTTON ?>" />
                            <?php eval ( $plxAdmin->plxPlugins->callHook ( 'AdminAuthLostPassword' ) ); ?>
                            <a href="?p=/core/admin"><span class="w100 mts btn--info"><?= L_LOST_PASSWORD_LOGIN ?></span></a>
                        </div>
                    </fieldset>
                </form>
            </div>
        <?php elseif (isset($_GET['action']) && $_GET['action'] == 'changepassword'): ?>
            <div class="form mam pas">
                <?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTopChangePassword')); ?>
                <?php if ($plxAdmin->verifyLostPasswordToken(isset($_GET['token']))): ?>
                    <div>
                        <form action="auth.php<?= !empty($redirect)?'?p='.PlxUtils::strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
                            <fieldset class="man pan">
                                <div class="flex-container--column">
                                    <?= PlxToken::getTokenPostMethod() ?>
                                    <input name="lostPasswordToken" value="<?= $_GET['token']; ?>" type="hidden" />
                                    <h1 class="h3-like txtcenter ma"><?= L_PROFIL_CHANGE_PASSWORD ?></h1>
                                    <?php PlxUtils::printInput('password1', '', 'password', '-64',false,'txt', L_PASSWORD, 'onkeyup="pwdStrength(this.id)" required') ?>
                                    <?php PlxUtils::printInput('password2', '', 'password', '-64',false,'txt', L_CONFIRM_PASSWORD, 'required') ?>
                                    <?php eval($plxAdmin->plxPlugins->callHook('AdminAuthChangePassword'));	?>
                                    <input class="btn--primary" role="button" type="submit" name="editpassword" value="<?= L_PROFIL_UPDATE_PASSWORD ?>" />
                                    <a href="?p=/core/admin"><span class="w100 mts btn--info"><?= L_LOST_PASSWORD_LOGIN ?></span></a>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="flex-container--column">
                        <?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTopChangePasswordError')); ?>
                        <h1 class="h3-like txtcenter mam"><?= L_PROFIL_CHANGE_PASSWORD ?></h1>
                        <div class="alert red"><?= L_LOST_PASSWORD_ERROR ?></div>
                        <span class="btn--primary"><a href="?p=/core/admin"><?= L_LOST_PASSWORD_LOGIN ?></a></span>
                        <?php eval($plxAdmin->plxPlugins->callHook('AdminAuthChangePasswordError')) ?>
                    </div>
                <?php endif ?>
            </div>
        <?php else: ?>
            <div class="form mam pas">
                <?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTop')) ?>
                <form action="auth.php<?= !empty($redirect)?'?p='.PlxUtils::strCheck(urlencode($redirect)):'' ?>" method="post" id="form_auth">
                    <fieldset class="man pan">
                        <div class="flex-container--column">
                            <?= PlxToken::getTokenPostMethod() ?>
                            <h1 class="h3-like txtcenter mam"><?= L_LOGIN_PAGE ?></h1>
                            <?php (!empty($msg))?PlxUtils::showMsg($msg, $css):''; ?>
                            <?php PlxUtils::printInput('login', (!empty($_POST['login']))?PlxUtils::strCheck($_POST['login']):'', 'text', '-64',false,'txt',L_AUTH_LOGIN_FIELD, 'autofocus required');?>
                            <?php PlxUtils::printInput('password', '', 'password','-64',false,'txt', L_PASSWORD, 'required');?>
                            <?php eval($plxAdmin->plxPlugins->callHook('AdminAuth')); ?>
                            <input class="btn--primary" role="button" type="submit" value="<?= L_SUBMIT_BUTTON ?>" />
                            <?php if ($plxAdmin->aConf['lostpassword']):?>
                                <a href="?action=lostpassword"><span class="w100 mts btn--warning"><?= L_LOST_PASSWORD ?></span></a>
                            <?php endif ?>
                        </div>
                    </fieldset>
                </form>
            </div>
        <?php endif ?>
        <p class="mam"><small><a class="back" href="<?= PLX_ROOT; ?>">&nbsp;<?= L_HOMEPAGE ?></a></small></p>
    </section>
</main>
<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthEndBody')) ?>
</body>
</html>
