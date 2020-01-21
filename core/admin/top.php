<?php 

/**
 * PluXml administration header and sidebar
 * @author	Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

use Pluxml\PlxUtils;
use Pluxml\PlxMsg;

if(!defined('PLX_ROOT')) {
	exit;
}

//Display install.php alert message
if(isset($_GET["del"]) AND $_GET["del"]=="install") {
	if(@unlink(PLX_ROOT.'install.php')) {
		PlxMsg::Info(L_DELETE_SUCCESSFUL);
	}
	else {
		PlxMsg::Error(L_DELETE_FILE_ERR.' install.php');
	}
	header("Location: index.php");
	exit;
}
?>
<!DOCTYPE html>
<html lang="<?= $plxAdmin->aConf['default_lang'] ?>">
<head>
	<meta name="robots" content="noindex, nofollow" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title><?= PlxUtils::strCheck($plxAdmin->aConf['title']) ?> <?= L_ADMIN ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?= strtolower(PLX_CHARSET) ?>" />
	<link rel="stylesheet" type="text/css" href="<?= PLX_CORE ?>admin/theme/css/knacss.css?v=<?= PLX_VERSION ?>" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?= PLX_CORE ?>admin/theme/css/theme.css?v=<?= PLX_VERSION ?>" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?= PLX_CORE ?>admin/theme/fonts/fontello.css?v=<?= PLX_VERSION ?>" media="screen" />
	<link rel="icon" href="<?= PLX_CORE ?>admin/theme/images/favicon.png" />
<?php
	PlxUtils::printLinkCss($plxAdmin->aConf['custom_admincss_file'], true);
	PlxUtils::printLinkCss($plxAdmin->aConf['racine_plugins'].'admin.css', true);
?>
	<script src="<?= PLX_CORE ?>lib/functions.js?v=<?= PLX_VERSION ?>"></script>
	<script src="<?= PLX_CORE ?>lib/visual.js?v=<?= PLX_VERSION ?>"></script>
	<script src="<?= PLX_CORE ?>lib/mediasManager.js?v=<?= PLX_VERSION ?>"></script>
	<script defer src="<?= PLX_CORE ?>lib/multifiles.js?v=<?= PLX_VERSION ?>"></script>
<?php
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminTopEndHead'));
?>
</head>
<body id="<?= basename($_SERVER['SCRIPT_NAME'], ".php") ?>">
<main class="main flex-container--column">
	<header class="flex-container">
		<div class="header-left w15">
			<h1><strong><?= PlxUtils::strCheck($plxAdmin->aConf['title']) ?></strong></h1>
		</div>
		<div class="header-right txtright item-fluid">
			<ul class="unstyled">
				<li>
					<strong><?= PlxUtils::strCheck($plxAdmin->aUsers[$_SESSION['user']]['name']) ?></strong>&nbsp;:
					<em>
						<?php if($_SESSION['profil']==PROFIL_ADMIN) echo L_PROFIL_ADMIN;
						elseif($_SESSION['profil']==PROFIL_MANAGER) echo L_PROFIL_MANAGER;
						elseif($_SESSION['profil']==PROFIL_MODERATOR) echo L_PROFIL_MODERATOR;
						elseif($_SESSION['profil']==PROFIL_EDITOR) echo L_PROFIL_EDITOR;
						else echo L_PROFIL_WRITER; ?>
					</em>
				</li>
				<li>
					<small><a href="<?= PLX_CORE ?>admin/auth.php?d=1" title="<?= L_ADMIN_LOGOUT_TITLE ?>"><?= L_ADMIN_LOGOUT ?></a></small>
				</li>
				<li>
<?php if(isset($plxAdmin->aConf['homestatic']) AND !empty($plxAdmin->aConf['homestatic'])) : ?>
					<small><a href="<?= $plxAdmin->urlRewrite('?blog'); ?>" title="<?= L_BACK_TO_BLOG_TITLE ?>"><?= L_BACK_TO_BLOG;?></a></small>
<?php else: ?>&nbsp;
<?php endif; ?>
				</li>
				<li>
					<small><a href="<?= PLX_ROOT ?>" title="<?= L_BACK_TO_SITE_TITLE ?>"><?= L_BACK_TO_SITE;?></a></small>
				</li>
			</ul>
			</div>
		</header>
	<div class="admin flex-container">
	<aside class="aside w15">
		<nav class="responsive-menu">
			<label for="nav"><?= L_MENU ?></label>
			<input type="checkbox" id="nav" />
			<ul class="unstyled">
<?php
					$menus = array();
					$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '[0-9]{3}' : $_SESSION['user']);
					$nbartsmod = $plxAdmin->nbArticles('all', $userId, '_');
					$arts_mod = $nbartsmod>0 ? '<span class="badge" onclick="window.location=\''.PLX_CORE.'admin/index.php?sel=mod&amp;page=1\';return false;">'.$nbartsmod.'</span>':'';
					$menus[] = PlxUtils::formatMenu(L_MENU_ARTICLES, PLX_CORE.'admin/index.php?page=1', L_MENU_ARTICLES_TITLE, false, false,$arts_mod);

					if(isset($_GET['a'])) # edition article
						$menus[] = PlxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, PLX_CORE.'admin/article.php', L_MENU_NEW_ARTICLES, false, false, '', false);
					else # nouvel article
						$menus[] = PlxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, PLX_CORE.'admin/article.php', L_MENU_NEW_ARTICLES);

					$menus[] = PlxUtils::formatMenu(L_MENU_MEDIAS, PLX_CORE.'admin/medias.php', L_MENU_MEDIAS_TITLE);

					if($_SESSION['profil'] <= PROFIL_MANAGER)
						$menus[] = PlxUtils::formatMenu(L_MENU_STATICS, PLX_CORE.'admin/statiques.php', L_MENU_STATICS_TITLE);

					if($_SESSION['profil'] <= PROFIL_MODERATOR) {
						$nbcoms = $plxAdmin->nbComments('offline');
						$coms_offline = $nbcoms>0 ? '<span class="badge" onclick="window.location=\''.PLX_CORE.'admin/comments.php?sel=offline&amp;page=1\';return false;">'.$plxAdmin->nbComments('offline').'</span>':'';
						$menus[] = PlxUtils::formatMenu(L_MENU_COMMENTS, PLX_CORE.'admin/comments.php?page=1', L_MENU_COMMENTS_TITLE, false, false, $coms_offline);
					}

					if($_SESSION['profil'] <= PROFIL_EDITOR)
						$menus[] = PlxUtils::formatMenu(L_MENU_CATEGORIES, PLX_CORE.'admin/categories.php', L_MENU_CATEGORIES_TITLE);

					$menus[] = PlxUtils::formatMenu(L_MENU_PROFIL, PLX_CORE.'admin/profil.php', L_MENU_PROFIL_TITLE);

					if($_SESSION['profil'] == PROFIL_ADMIN) {
						$menus[] = PlxUtils::formatMenu(L_MENU_CONFIG, PLX_CORE.'admin/parametres_base.php', L_MENU_CONFIG_TITLE, false, false, '', false);
						if (preg_match('/parametres/',basename($_SERVER['SCRIPT_NAME']))) {
							$menus[] = PlxUtils::formatMenu(L_MENU_CONFIG_BASE, PLX_CORE.'admin/parametres_base.php', L_MENU_CONFIG_BASE_TITLE, 'menu-config');
							$menus[] = PlxUtils::formatMenu(L_MENU_CONFIG_VIEW, PLX_CORE.'admin/parametres_affichage.php', L_MENU_CONFIG_VIEW_TITLE, 'menu-config');
							$menus[] = PlxUtils::formatMenu(L_MENU_CONFIG_USERS, PLX_CORE.'admin/parametres_users.php', L_MENU_CONFIG_USERS_TITLE, 'menu-config');
							$menus[] = PlxUtils::formatMenu(L_MENU_CONFIG_ADVANCED, PLX_CORE.'admin/parametres_avances.php', L_MENU_CONFIG_ADVANCED_TITLE, 'menu-config');
							$menus[] = PlxUtils::formatMenu(L_THEMES, PLX_CORE.'admin/parametres_themes.php', L_THEMES_TITLE, 'menu-config');
							$menus[] = PlxUtils::formatMenu(L_MENU_CONFIG_PLUGINS, PLX_CORE.'admin/parametres_plugins.php', L_MENU_CONFIG_PLUGINS_TITLE, 'menu-config');
							$menus[] = PlxUtils::formatMenu(L_MENU_CONFIG_INFOS, PLX_CORE.'admin/parametres_infos.php', L_MENU_CONFIG_INFOS_TITLE, 'menu-config');
						}
					}

					# récuperation des menus admin pour les plugins
					foreach($plxAdmin->plxPlugins->aPlugins as $plugName => $plugInstance) {
						if($plugInstance AND is_file(PLX_PLUGINS.$plugName.'/admin.php')) {
							if($plxAdmin->checkProfil($plugInstance->getAdminProfil(),false)) {
								if($plugInstance->adminMenu) {
									$menu = PlxUtils::formatMenu(PlxUtils::strCheck($plugInstance->adminMenu['title']), PLX_CORE.'admin/plugin.php?p='.$plugName, PlxUtils::strCheck($plugInstance->adminMenu['caption']));
									if($plugInstance->adminMenu['position']!='')
										array_splice($menus, ($plugInstance->adminMenu['position']-1), 0, $menu);
									else
										$menus[] = $menu;
								} else {
									$menus[] = PlxUtils::formatMenu(PlxUtils::strCheck($plugInstance->getInfo('title')), PLX_CORE.'admin/plugin.php?p='.$plugName, PlxUtils::strCheck($plugInstance->getInfo('title')));
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
		<small><a title="PluXml" href="<?= PLX_URL_REPO ?>">PluXml <?= $plxAdmin->aConf['version'] ?></a></small>
	</aside>

	<section class="section item-fluid">

<?php
		if(is_file(PLX_ROOT.'install.php'))
			echo '<p class="alert red">'.L_WARNING_INSTALLATION_FILE.'</p>'."\n";
		PlxMsg::Display();

		# Hook Plugins
		eval($plxAdmin->plxPlugins->callHook('AdminTopBottom'));
?>
