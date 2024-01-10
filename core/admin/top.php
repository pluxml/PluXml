<?php
if(isset($_GET["del"]) AND $_GET["del"]=="install") {
	if(@unlink(PLX_ROOT.'install.php'))
		plxMsg::Info(L_DELETE_SUCCESSFUL);
	else
		plxMsg::Error(L_DELETE_FILE_ERR.' install.php');
}
?>
<!DOCTYPE html>
<html lang="<?= $plxAdmin->aConf['default_lang'] ?>">
<head>
	<meta name="robots" content="noindex, nofollow" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title><?= plxUtils::strCheck($plxAdmin->aConf['title']) ?> <?= L_ADMIN ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?= strtolower(PLX_CHARSET) ?>" />
	<link rel="stylesheet" type="text/css" href="theme/plucss.css?v=<?= PLX_VERSION ?>" media="screen" />
	<link rel="stylesheet" type="text/css" href="theme/theme.css?v=<?= PLX_VERSION ?>" media="screen" />
	<link rel="stylesheet" type="text/css" href="theme/fonts/fontello/css/fontello.css?v=<?= PLX_VERSION ?>" media="screen" />
	<link rel="icon" href="theme/images/favicon.png" />
<?php
	plxUtils::printLinkCss($plxAdmin->aConf['custom_admincss_file'], true);
	plxUtils::printLinkCss(PLX_PLUGINS_CSS_PATH . 'admin.css', true);
?>
	<script src="js/functions.js?v=<?= PLX_VERSION ?>"></script>
	<script src="js/mediasManager.js?v=<?= PLX_VERSION ?>"></script>
	<script defer src="js/multifiles.js?v=<?= PLX_VERSION ?>"></script>
<?php
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminTopEndHead'));
?>
</head>

<body id="<?= basename($_SERVER['SCRIPT_NAME'], ".php") ?>">

<main class="main grid">

	<aside class="aside col sml-12 med-3 lrg-2 sml-text-left med-text-right">
		<header class="header sml-text-center med-text-right">
			<ul class="unstyled-list head">
				<li>
					<small><a class="back-site" href="<?= PLX_ROOT ?>" title="<?= L_BACK_TO_SITE_TITLE ?>"><?= L_BACK_TO_SITE;?></a></small>
				</li>
				<li>
<?php if(isset($plxAdmin->aConf['homestatic']) AND !empty($plxAdmin->aConf['homestatic'])) : ?>
					<small><a class="back-blog" href="<?= $plxAdmin->urlRewrite('?blog'); ?>" title="<?= L_BACK_TO_BLOG_TITLE ?>"><?= L_BACK_TO_BLOG;?></a></small>
<?php else: ?>&nbsp;
<?php endif; ?>
				</li>
				<li>
					<small><a class="logout" href="auth.php?d=1" title="<?= L_ADMIN_LOGOUT_TITLE ?>"><?= L_ADMIN_LOGOUT ?></a></small>
				</li>
			</ul>
			<ul class="unstyled-list profil">
				<li>
					<h1 class="h5 no-margin site-name"><strong><?= plxUtils::strCheck($plxAdmin->aConf['title']) ?></strong></h1>
				</li>
				<li>
					<strong><?= plxUtils::strCheck($plxAdmin->aUsers[$_SESSION['user']]['name']) ?></strong>&nbsp;:
					<em><?= array_key_exists($_SESSION['profil'], PROFIL_NAMES) ? PROFIL_NAMES[$_SESSION['profil']] : L_PROFIL_SUBSCRIBER; ?></em>
				</li>
				<li><small><a class="version" title="PluXml" href="<?= PLX_URL_REPO ?>">PluXml <?= PLX_VERSION ?></a></small></li>
			</ul>
		</header>
		<nav class="responsive-menu">
			<label for="nav"><?= L_MENU ?></label>
			<input type="checkbox" id="nav" />
			<ul id="responsive-menu" class="menu vertical expanded">
<?php
					$menus = array();

					if($_SESSION['profil'] <= PROFIL_WRITER) {
						$arts_exists = ($plxAdmin->nbArticles('all', ($_SESSION['profil'] < PROFIL_WRITER) ? '\d{3}' : $_SESSION['user']) > 0);
						if ($arts_exists) {
							$nbartsmod = $plxAdmin->nbArticles('all', ($_SESSION['profil'] < PROFIL_WRITER) ? '\d{3}' : $_SESSION['user'], '_');
							$arts_mod = ($nbartsmod > 0) ? '<span class="badge" onclick="window.location=\'index.php?sel=mod\'; return false;">'.$nbartsmod.'</span>' : '';
							$menus[] = plxUtils::formatMenu(L_MENU_ARTICLES, 'index.php?sel=all', L_MENU_ARTICLES_TITLE, false, false,$arts_mod);
						}

						if(isset($_GET['a'])) # edition article
							$menus[] = plxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, 'article.php', L_MENU_NEW_ARTICLES, false, false, '', false);
						else # nouvel article
							$menus[] = plxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, 'article.php', L_MENU_NEW_ARTICLES);

						$menus[] = plxUtils::formatMenu(L_MENU_MEDIAS, 'medias.php', L_MENU_MEDIAS_TITLE);
					}

					if($_SESSION['profil'] <= PROFIL_MANAGER)
						$menus[] = plxUtils::formatMenu(L_MENU_STATICS, 'statiques.php', L_MENU_STATICS_TITLE);

					if($_SESSION['profil'] <= PROFIL_MODERATOR) {
						if($arts_exists and $plxAdmin->nbComments('all') > 0) {
							$nbComsOff = $plxAdmin->nbComments('offline');
							$coms_offline = ($nbComsOff > 0) ? '<span class="badge" onclick="window.location = \'comments.php?sel=offline\'; return false;">' . $nbComsOff . '</span>' : '';
							$menus[] = plxUtils::formatMenu(L_MENU_COMMENTS, 'comments.php?sel=all', L_MENU_COMMENTS_TITLE, false, false, $coms_offline);
						}
					}

					if($_SESSION['profil'] <= PROFIL_EDITOR)
						$menus[] = plxUtils::formatMenu(L_MENU_CATEGORIES, 'categories.php', L_MENU_CATEGORIES_TITLE);

					$menus[] = plxUtils::formatMenu(L_MENU_PROFIL, 'profil.php', L_MENU_PROFIL_TITLE);

					if($_SESSION['profil'] == PROFIL_ADMIN) {
						$menus[] = plxUtils::formatMenu(L_MENU_CONFIG, 'parametres_base.php', L_MENU_CONFIG_TITLE, false, false, '', false);
						if (preg_match('/parametres/',basename($_SERVER['SCRIPT_NAME']))) {
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_BASE, 'parametres_base.php', L_MENU_CONFIG_BASE_TITLE, 'menu-config');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_VIEW, 'parametres_affichage.php', L_MENU_CONFIG_VIEW_TITLE, 'menu-config');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_USERS, 'parametres_users.php', L_MENU_CONFIG_USERS_TITLE, 'menu-config');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_ADVANCED, 'parametres_avances.php', L_MENU_CONFIG_ADVANCED_TITLE, 'menu-config');
							$menus[] = plxUtils::formatMenu(L_THEMES, 'parametres_themes.php', L_THEMES_TITLE, 'menu-config');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_PLUGINS, 'parametres_plugins.php', L_MENU_CONFIG_PLUGINS_TITLE, 'menu-config');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_INFOS, 'parametres_infos.php', L_MENU_CONFIG_INFOS_TITLE, 'menu-config');
						}
					}

					if($_SESSION['profil'] <= PROFIL_WRITER) {
						# récuperation des menus admin pour les plugins
						foreach($plxAdmin->plxPlugins->aPlugins as $plugName => $plugInstance) {
							if($plugInstance AND is_file(PLX_PLUGINS.$plugName.'/admin.php')) {
								if($plxAdmin->checkProfil($plugInstance->getAdminProfil(),false)) {
									if($plugInstance->adminMenu) {
										$menu = plxUtils::formatMenu(plxUtils::strCheck($plugInstance->adminMenu['title']), 'plugin.php?p='.$plugName, plxUtils::strCheck($plugInstance->adminMenu['caption']));
										if($plugInstance->adminMenu['position']!='')
											array_splice($menus, ($plugInstance->adminMenu['position']-1), 0, $menu);
										else
											$menus[] = $menu;
									} else {
										$menus[] = plxUtils::formatMenu(plxUtils::strCheck($plugInstance->getInfo('title')), 'plugin.php?p='.$plugName, plxUtils::strCheck($plugInstance->getInfo('title')));
									}
								}
							}
						}
					}

					# Hook Plugins
					eval($plxAdmin->plxPlugins->callHook('AdminTopMenus'));

					echo implode('', $menus);
?>
			</ul>
		</nav>
	</aside>

	<section class="section col sml-12 med-9 med-offset-3 lrg-10 lrg-offset-2">

<?php if($_SESSION['profil'] == 0 and basename($_SERVER['SCRIPT_FILENAME']) == 'index.php' and is_file(PLX_ROOT.'install.php')) : ?>
		<?php $urlDeleteInstall = "?" . http_build_query(array_merge($_GET, array('del'=>'install'))); ?>
		<p class="alert red"><?= sprintf(L_WARNING_INSTALLATION_FILE, $urlDeleteInstall) ?></p>
<?php endif; ?>
<?php
	plxMsg::Display();

	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminTopBottom'));
