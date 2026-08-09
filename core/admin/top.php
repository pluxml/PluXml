<?php
if(!defined('PLX_ROOT')) { exit; }
?>
<!DOCTYPE html>
<html lang="<?php echo $plxAdmin->aConf['default_lang'] ?>">
<head>
	<meta name="robots" content="noindex, nofollow" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<meta charset="<?php echo strtolower(PLX_CHARSET) ?>" />
	<title><?php echo plxUtils::strCheck($plxAdmin->aConf['title']) ?> <?php echo L_ADMIN ?></title>
<?php plxUtils::printLinkCss(); ?>
	<script src="js/functions.js?v=<?php echo PLX_VERSION ?>"></script>
	<script src="js/mediasManager.js?v=<?php echo PLX_VERSION ?>"></script>
	<script defer src="js/multifiles.js?v=<?php echo PLX_VERSION ?>"></script>
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
					<small><a class="logout" href="auth.php?d=1" title="<?php echo L_ADMIN_LOGOUT_TITLE ?>"><?php echo L_ADMIN_LOGOUT ?></a></small>
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
					$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '\d{3}' : $_SESSION['user']);
					$nbartsmod = $plxAdmin->nbArticles('all', $userId, '_');
					$arts_mod = ($nbartsmod > 0) ? '<span class="badge" onclick="window.location=\''.'index.php?sel=mod\';return false;">'.$nbartsmod.'</span>' : '';
					$menus[] = plxUtils::formatMenu(L_MENU_ARTICLES, 'index.php', L_MENU_ARTICLES_TITLE, false, false, $arts_mod);

					if(isset($_GET['a'])) # edition article
						$menus[] = plxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, 'article.php', L_MENU_NEW_ARTICLES, false, false, '', false);
					else # nouvel article
						$menus[] = plxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, 'article.php', L_MENU_NEW_ARTICLES);

					$menus[] = plxUtils::formatMenu(L_MENU_MEDIAS, 'medias.php', L_MENU_MEDIAS_TITLE);

					if($_SESSION['profil'] <= PROFIL_MANAGER)
						$menus[] = plxUtils::formatMenu(L_MENU_STATICS, 'statiques.php', L_MENU_STATICS_TITLE);

					if($_SESSION['profil'] <= PROFIL_MODERATOR) {
						$nbcoms = $plxAdmin->nbComments('offline');
						$coms_offline = $nbcoms>0 ? '<span class="badge" onclick="window.location=\''.'comments.php?sel=offline&amp;page=1\';return false;">'.$plxAdmin->nbComments('offline').'</span>':'';
						$menus[] = plxUtils::formatMenu(L_MENU_COMMENTS, 'comments.php?page=1', L_MENU_COMMENTS_TITLE, false, false, $coms_offline);
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

					# récuperation des menus admin pour les plugins
					foreach($plxAdmin->plxPlugins->aPlugins as $plugName => $plugInstance) {
						if(
							$plugInstance AND
							is_file(PLX_PLUGINS.$plugName.'/admin.php') AND
							$plxAdmin->checkProfil($plugInstance->getAdminProfil(),false)
						) {
							if(isset($plugInstance->adminMenu['title'])) {
								$menu = plxUtils::formatMenu(plxUtils::strCheck($plugInstance->adminMenu['title']), 'plugin.php?p='.$plugName, plxUtils::strCheck($plugInstance->adminMenu['caption']));
								$pos = $plugInstance->adminMenu['position'] - 1;
								if($pos >= 0) {
									array_splice($menus, $pos, 0, $menu);
								} else {
									$menus[] = $menu;
								}
							} else {
								$title = plxUtils::strCheck($plugInstance->getInfo('title'));
								$menus[] = plxUtils::formatMenu($title, 'plugin.php?p='.$plugName, $title);
							}
						}
					}

					# Hook Plugins
					eval($plxAdmin->plxPlugins->callHook('AdminTopMenus'));
					echo implode(PHP_EOL, $menus) . PHP_EOL;
?>
			</ul>
		</nav>
	</aside>

	<section class="section col sml-12 med-9 med-offset-3 lrg-10 lrg-offset-2">

<?php
	plxMsg::Display();
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminTopBottom'));
