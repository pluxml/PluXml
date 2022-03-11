<?php if(!defined('PLX_ROOT')) exit; ?>
<?php
if(isset($_GET["del"]) AND $_GET["del"]=="install") {
	if(@unlink(PLX_ROOT.'install.php'))
		plxMsg::Info(L_DELETE_SUCCESSFUL);
	else
		plxMsg::Error(L_DELETE_FILE_ERR.' install.php');
}
?>
<!DOCTYPE html>
<html lang="<?php echo $plxAdmin->aConf['default_lang'] ?>">
<head>
	<meta name="robots" content="noindex, nofollow" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title><?php echo plxUtils::strCheck($plxAdmin->aConf['title']) ?> <?php echo L_ADMIN ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET) ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/plucss.css?v=<?php echo PLX_VERSION ?>" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/theme.css?v=<?php echo PLX_VERSION ?>" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/fonts/fontello.css?v=<?php echo PLX_VERSION ?>" media="screen" />
	<link rel="icon" href="<?php echo PLX_CORE ?>admin/theme/images/favicon.png" />
<?php
	plxUtils::printLinkCss($plxAdmin->aConf['custom_admincss_file'], true);
	plxUtils::printLinkCss(PLX_PLUGINS_CSS_PATH . 'admin.css', true);
?>
	<script src="<?php echo PLX_CORE ?>lib/functions.js?v=<?php echo PLX_VERSION ?>"></script>
	<script src="<?php echo PLX_CORE ?>lib/visual.js?v=<?php echo PLX_VERSION ?>"></script>
	<script src="<?php echo PLX_CORE ?>lib/mediasManager.js?v=<?php echo PLX_VERSION ?>"></script>
	<script defer src="<?php echo PLX_CORE ?>lib/multifiles.js?v=<?php echo PLX_VERSION ?>"></script>
<?php
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminTopEndHead'));
?>
</head>

<body id="<?php echo basename($_SERVER['SCRIPT_NAME'], ".php") ?>">

<main class="main grid">

	<aside class="aside col sml-12 med-3 lrg-2 sml-text-left med-text-right">
		<header class="header sml-text-center med-text-right">
			<ul class="unstyled-list head">
				<li>
					<small><a class="back-site" href="<?php echo PLX_ROOT ?>" title="<?php echo L_BACK_TO_SITE_TITLE ?>"><?php echo L_BACK_TO_SITE;?></a></small>
				</li>
				<li>
<?php if(isset($plxAdmin->aConf['homestatic']) AND !empty($plxAdmin->aConf['homestatic'])) : ?>
					<small><a class="back-blog" href="<?php echo $plxAdmin->urlRewrite('?blog'); ?>" title="<?php echo L_BACK_TO_BLOG_TITLE ?>"><?php echo L_BACK_TO_BLOG;?></a></small>
<?php else: ?>&nbsp;
<?php endif; ?>
				</li>
				<li>
					<small><a class="logout" href="<?php echo PLX_CORE ?>admin/auth.php?d=1" title="<?php echo L_ADMIN_LOGOUT_TITLE ?>"><?php echo L_ADMIN_LOGOUT ?></a></small>
				</li>
			</ul>
			<ul class="unstyled-list profil">
				<li>
					<h1 class="h5 no-margin site-name"><strong><?php echo plxUtils::strCheck($plxAdmin->aConf['title']) ?></strong></h1>
				</li>
				<li>
					<strong><?php echo plxUtils::strCheck($plxAdmin->aUsers[$_SESSION['user']]['name']) ?></strong>&nbsp;:
					<em>
						<?php if($_SESSION['profil']==PROFIL_ADMIN) echo L_PROFIL_ADMIN;
						elseif($_SESSION['profil']==PROFIL_MANAGER) echo L_PROFIL_MANAGER;
						elseif($_SESSION['profil']==PROFIL_MODERATOR) echo L_PROFIL_MODERATOR;
						elseif($_SESSION['profil']==PROFIL_EDITOR) echo L_PROFIL_EDITOR;
						else echo L_PROFIL_WRITER; ?>
					</em>
				</li>
				<li><small><a class="version" title="PluXml" href="<?php echo PLX_URL_REPO ?>">PluXml <?php echo $plxAdmin->aConf['version'] ?></a></small></li>
			</ul>
		</header>
		<nav class="responsive-menu">
			<label for="nav"><?php echo L_MENU ?></label>
			<input type="checkbox" id="nav" />
			<ul id="responsive-menu" class="menu vertical expanded">
<?php
					$menus = array();
					$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '[0-9]{3}' : $_SESSION['user']);
					$nbartsmod = $plxAdmin->nbArticles('all', $userId, '_');
					$arts_mod = $nbartsmod>0 ? '<span class="badge" onclick="window.location=\''.PLX_CORE.'admin/index.php?sel=mod&amp;page=1\';return false;">'.$nbartsmod.'</span>':'';
					$menus[] = plxUtils::formatMenu(L_MENU_ARTICLES, PLX_CORE.'admin/index.php?page=1', L_MENU_ARTICLES_TITLE, false, false,$arts_mod);

					if(isset($_GET['a'])) # edition article
						$menus[] = plxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, PLX_CORE.'admin/article.php', L_MENU_NEW_ARTICLES, false, false, '', false);
					else # nouvel article
						$menus[] = plxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, PLX_CORE.'admin/article.php', L_MENU_NEW_ARTICLES);

					$menus[] = plxUtils::formatMenu(L_MENU_MEDIAS, PLX_CORE.'admin/medias.php', L_MENU_MEDIAS_TITLE);

					if($_SESSION['profil'] <= PROFIL_MANAGER)
						$menus[] = plxUtils::formatMenu(L_MENU_STATICS, PLX_CORE.'admin/statiques.php', L_MENU_STATICS_TITLE);

					if($_SESSION['profil'] <= PROFIL_MODERATOR) {
						$nbcoms = $plxAdmin->nbComments('offline');
						$coms_offline = $nbcoms>0 ? '<span class="badge" onclick="window.location=\''.PLX_CORE.'admin/comments.php?sel=offline&amp;page=1\';return false;">'.$plxAdmin->nbComments('offline').'</span>':'';
						$menus[] = plxUtils::formatMenu(L_MENU_COMMENTS, PLX_CORE.'admin/comments.php?page=1', L_MENU_COMMENTS_TITLE, false, false, $coms_offline);
					}

					if($_SESSION['profil'] <= PROFIL_EDITOR)
						$menus[] = plxUtils::formatMenu(L_MENU_CATEGORIES, PLX_CORE.'admin/categories.php', L_MENU_CATEGORIES_TITLE);

					$menus[] = plxUtils::formatMenu(L_MENU_PROFIL, PLX_CORE.'admin/profil.php', L_MENU_PROFIL_TITLE);

					if($_SESSION['profil'] == PROFIL_ADMIN) {
						$menus[] = plxUtils::formatMenu(L_MENU_CONFIG, PLX_CORE.'admin/parametres_base.php', L_MENU_CONFIG_TITLE, false, false, '', false);
						if (preg_match('/parametres/',basename($_SERVER['SCRIPT_NAME']))) {
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_BASE, PLX_CORE.'admin/parametres_base.php', L_MENU_CONFIG_BASE_TITLE, 'menu-config');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_VIEW, PLX_CORE.'admin/parametres_affichage.php', L_MENU_CONFIG_VIEW_TITLE, 'menu-config');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_USERS, PLX_CORE.'admin/parametres_users.php', L_MENU_CONFIG_USERS_TITLE, 'menu-config');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_ADVANCED, PLX_CORE.'admin/parametres_avances.php', L_MENU_CONFIG_ADVANCED_TITLE, 'menu-config');
							$menus[] = plxUtils::formatMenu(L_THEMES, PLX_CORE.'admin/parametres_themes.php', L_THEMES_TITLE, 'menu-config');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_PLUGINS, PLX_CORE.'admin/parametres_plugins.php', L_MENU_CONFIG_PLUGINS_TITLE, 'menu-config');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_INFOS, PLX_CORE.'admin/parametres_infos.php', L_MENU_CONFIG_INFOS_TITLE, 'menu-config');
						}
					}

					# récuperation des menus admin pour les plugins
					foreach($plxAdmin->plxPlugins->aPlugins as $plugName => $plugInstance) {
						if($plugInstance AND is_file(PLX_PLUGINS.$plugName.'/admin.php')) {
							if($plxAdmin->checkProfil($plugInstance->getAdminProfil(),false)) {
								if($plugInstance->adminMenu) {
									$menu = plxUtils::formatMenu(plxUtils::strCheck($plugInstance->adminMenu['title']), PLX_CORE.'admin/plugin.php?p='.$plugName, plxUtils::strCheck($plugInstance->adminMenu['caption']));
									if($plugInstance->adminMenu['position']!='')
										array_splice($menus, ($plugInstance->adminMenu['position']-1), 0, $menu);
									else
										$menus[] = $menu;
								} else {
									$menus[] = plxUtils::formatMenu(plxUtils::strCheck($plugInstance->getInfo('title')), PLX_CORE.'admin/plugin.php?p='.$plugName, plxUtils::strCheck($plugInstance->getInfo('title')));
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

<?php if(is_file(PLX_ROOT.'install.php')): ?>
		<?php $urlDeleteInstall = "?" . http_build_query(array_merge($_GET, array('del'=>'install'))); ?>
		<p class="alert red"><?= sprintf(L_WARNING_INSTALLATION_FILE, $urlDeleteInstall) ?></p>
<?php endif; ?>
<?php
	plxMsg::Display();
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminTopBottom'));
?>
