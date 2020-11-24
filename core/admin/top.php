<?php

if (!defined('PLX_ROOT')) {
    exit;
}

if (isset($_GET["del"]) and $_GET["del"] == "install") {
    if (@unlink(PLX_ROOT . 'install.php'))
        plxMsg::Info(L_DELETE_SUCCESSFUL);
    else
        plxMsg::Error(L_DELETE_FILE_ERR . ' install.php');
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= $plxAdmin->aConf['default_lang'] ?>">
<head>
    <meta name="robots" content="noindex, nofollow"/>
    <meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
    <title><?= plxUtils::strCheck($plxAdmin->aConf['title']) ?> <?= L_ADMIN ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= strtolower(PLX_CHARSET) ?>"/>
    <link rel="stylesheet" href="theme/css/knacss.css?v=<?= PLX_VERSION ?>" media="screen"/>
    <link rel="stylesheet" href="theme/css/theme.css?v=<?= PLX_VERSION ?>" media="screen"/>
    <link rel="stylesheet" href="theme/fontello/css/fontello.css" media="screen"/>
    <link rel="icon" href="theme/images/favicon.png"/>
    <?php
    plxUtils::printLinkCss($plxAdmin->aConf['custom_admincss_file'], true);
    plxUtils::printLinkCss($plxAdmin->aConf['racine_plugins'] . 'admin.css', true);

    // Plugin hook
    eval($plxAdmin->plxPlugins->callHook('AdminTopEndHead'));
    ?>
</head>

<body id="<?= basename($_SERVER['SCRIPT_NAME'], ".php") ?>" class="profil-<?= $_SESSION['profil'] ?>">

<main id="app" class="main">

    <?php plxMsg::Display(); ?>

    <aside id="aside" class="aside">
        <header class="asideheader">
            <h1 class="h4-like txtcenter"><?= PlxUtils::strCheck($plxAdmin->aConf['title']) ?></h1>
            <ul class="unstyled">
                <?php if (isset($plxAdmin->aConf['homestatic']) and !empty($plxAdmin->aConf['homestatic'])) : ?>
                    <li>
                        <a class="back-blog" href="<?= $plxAdmin->urlRewrite('?blog'); ?>"
                           title="<?= L_BACK_TO_BLOG_TITLE ?>"><i class="icon-left-open"></i><?= L_BACK_TO_BLOG; ?></a>
                    </li>
                <?php endif; ?>
                <li>
                    <a class="back-site" href="<?= PLX_ROOT ?>" title="<?= L_BACK_HOMEPAGE_TITLE ?>"><i
                                class="icon-left-open"></i><?= L_HOMEPAGE; ?></a>
                </li>
            </ul>
        </header>
        <nav class="responsive-menu">
            <label for="nav"><?= L_MENU ?></label>
            <input type="checkbox" id="nav"/>
            <div id="responsive-menu" class="menu vertical expanded">
                <?php
                $menus = array();
                $userId = ($_SESSION['profil'] < PROFIL_WRITER) ? '\d{3}' : $_SESSION['user'];
                $nbartsmod = $plxAdmin->nbArticles('all', $userId, '_');
                $arts_mod = $nbartsmod > 0 ? '<span class="badge" onclick="window.location=\'' . 'index.php?sel=mod&amp;page=1\';return false;">' . $nbartsmod . '</span>' : '';
                $menus[] = plxUtils::formatMenu('<i class="icon-doc-inv"></i>' . L_MENU_ARTICLES, 'index.php?page=1', L_MENU_ARTICLES_TITLE, false, false, $arts_mod);

                if (isset($_GET['a'])) # edition article
                    $menus[] = plxUtils::formatMenu('<i class="icon-plus"></i>' . L_NEW_ARTICLE, 'article.php', L_NEW_ARTICLE, false, false, '', false);
                else # nouvel article
                    $menus[] = plxUtils::formatMenu('<i class="icon-plus"></i>' . L_NEW_ARTICLE, 'article.php', L_NEW_ARTICLE);

                $menus[] = plxUtils::formatMenu('<i class="icon-picture"></i>' . L_MENU_MEDIAS, 'medias.php', L_MENU_MEDIAS_TITLE);

                if ($_SESSION['profil'] <= PROFIL_MANAGER)
                    $menus[] = plxUtils::formatMenu('<i class="icon-doc-text-inv"></i>' . L_MENU_STATICS, 'statiques.php', L_MENU_STATICS_TITLE);

                if (!empty($plxAdmin->aConf['allow_com']) and $_SESSION['profil'] <= PROFIL_MODERATOR) {
                    $nbcoms = $plxAdmin->nbComments('offline');
                    $coms_offline = $nbcoms > 0 ? '<span class="badge" onclick="window.location=\'' . 'comments.php?sel=offline&amp;page=1\';return false;">' . $plxAdmin->nbComments('offline') . '</span>' : '';
                    $menus[] = plxUtils::formatMenu('<i class="icon-comment-inv-alt2"></i>' . L_COMMENTS, 'comments.php?page=1', L_MENU_COMMENTS_TITLE, false, false, $coms_offline);
                }

                if ($_SESSION['profil'] <= PROFIL_EDITOR)
                    $menus[] = plxUtils::formatMenu('<i class="icon-list"></i>' . L_CATEGORIES, 'categories.php', L_MENU_CATEGORIES_TITLE);

                $menus[] = plxUtils::formatMenu('<i class="icon-user"></i>' . L_PROFIL, 'profil.php', L_MENU_PROFIL_TITLE);

                if ($_SESSION['profil'] == PROFIL_ADMIN) {
                    $menus[] = plxUtils::formatMenu('<i class="icon-cog-1"></i>' . L_MENU_CONFIG, 'parametres_base.php', L_MENU_CONFIG_TITLE, false, false, '', false);
                    if (preg_match('/parametres/', basename($_SERVER['SCRIPT_NAME']))) {
                        $menus[] = plxUtils::formatMenu(L_CONFIG_BASE, 'parametres_base.php', L_MENU_CONFIG_BASE_TITLE, 'menu-config');
                        $menus[] = plxUtils::formatMenu(L_MENU_CONFIG_VIEW, 'parametres_affichage.php', L_MENU_CONFIG_VIEW_TITLE, 'menu-config');
                        $menus[] = plxUtils::formatMenu(L_MENU_CONFIG_USERS, 'parametres_users.php', L_MENU_CONFIG_USERS_TITLE, 'menu-config');
                        $menus[] = plxUtils::formatMenu(L_CONFIG_ADVANCED, 'parametres_avances.php', L_MENU_CONFIG_ADVANCED_TITLE, 'menu-config');
                        $menus[] = plxUtils::formatMenu(L_THEMES, 'parametres_themes.php', L_THEMES_TITLE, 'menu-config');
                        $menus[] = plxUtils::formatMenu(L_MENU_CONFIG_PLUGINS, 'parametres_plugins.php', L_MENU_CONFIG_PLUGINS_TITLE, 'menu-config');
                        $menus[] = plxUtils::formatMenu(L_INFOS, 'parametres_infos.php', L_MENU_CONFIG_INFOS_TITLE, 'menu-config');
                    }
                }

                // Get administration menu links from Plugins
                foreach ($plxAdmin->plxPlugins->aPlugins as $plugName => $plugInstance) {
                    if ($plugInstance and is_file(PLX_PLUGINS . $plugName . '/admin.php')) {
                        if ($plxAdmin->checkProfil($plugInstance->getAdminProfil(), false)) {
                            if ($plugInstance->adminMenu) {
                                $menu = plxUtils::formatMenu(plxUtils::strCheck($plugInstance->adminMenu['title']), 'plugin.php?p=' . $plugName, plxUtils::strCheck($plugInstance->adminMenu['caption']));
                                if ($plugInstance->adminMenu['position'] != '')
                                    array_splice($menus, ($plugInstance->adminMenu['position'] - 1), 0, $menu);
                                else
                                    $menus[] = $menu;
                            } else {
                                $menus[] = plxUtils::formatMenu(plxUtils::strCheck($plugInstance->getInfo('title')), 'plugin.php?p=' . $plugName, plxUtils::strCheck($plugInstance->getInfo('title')));
                            }
                        }
                    }
                }

                // Plugin hook
                eval($plxAdmin->plxPlugins->callHook('AdminTopMenus'));
                echo implode(PHP_EOL, $menus) . PHP_EOL;
                ?>
            </div>
        </nav>
        <div class="plxversion"><a title="PluXml"
                                   href="<?= PLX_URL_REPO ?>"><small>PluXml <?= $plxAdmin->aConf['version'] ?></small></a>
        </div>
    </aside>

    <section class="section grid-1">
        <header class="header autogrid">
            <div>
                <button class="burger-button large-hidden" type="button" role="button" aria-label="open/close navigation"><i></i>Menu
                </button>
            </div>
            <div class="txtright">
                <ul class="unstyled">
                    <li class="badge"><a href="profile.php"><img src="theme/images/pluxml.png"/></a></li>
                    <li>
                        <a href="profile.php"><?= PlxUtils::strCheck($plxAdmin->aUsers[$_SESSION['user']]['name']) ?></a>&nbsp;
                        <small><em><?php
                                if ($_SESSION ['profil'] == PROFIL_ADMIN) echo L_PROFIL_ADMIN;
                                elseif ($_SESSION ['profil'] == PROFIL_MANAGER) echo L_PROFIL_MANAGER;
                                elseif ($_SESSION ['profil'] == PROFIL_MODERATOR) echo L_PROFIL_MODERATOR;
                                elseif ($_SESSION ['profil'] == PROFIL_EDITOR) echo L_PROFIL_EDITOR;
                                else echo L_PROFIL_WRITER;
                                ?></em></small>
                    </li>
                    <li><a href="<?= PLX_CORE ?>admin/auth.php?d=1" title="<?= L_ADMIN_LOGOUT_TITLE ?>"><i
                                    class="icon-logout"></i></a></li>
                </ul>
            </div>
        </header>

<?php
if (basename($_SERVER['SCRIPT_FILENAME']) == 'index.php' and is_file(PLX_ROOT . 'install.php')):
?>
            <div class="mlm mrm mbm">
                <p class="alert--danger"><?= L_WARNING_INSTALLATION_FILE ?></p>
            </div>
<?php
endif;

eval($plxAdmin->plxPlugins->callHook('AdminTopBottom'));
?>

        <script>
            (function () {
                function toggleNav() {
                    var button = document.querySelector('.burger-button');
                    var target = document.querySelector('body > main > aside');
                    if (button) {
                        button.addEventListener('click',
                            function (e) {
                                button.classList.toggle('is-active');
                                target.classList.toggle('is-opened');
                                e.preventDefault();
                            }, false);
                    }
                }

                toggleNav();
            }());
        </script>
