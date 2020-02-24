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
	<link rel="stylesheet" type="text/css" href="<?= PLX_CORE ?>admin/theme/css/fontello.css?v=<?= PLX_VERSION ?>" media="screen" />
	<link rel="icon" href="<?= PLX_CORE ?>admin/theme/images/favicon.png" />
<?php
	PlxUtils::printLinkCss($plxAdmin->aConf['custom_admincss_file'], true);
	PlxUtils::printLinkCss($plxAdmin->aConf['racine_plugins'].'admin.css', true);
?>
	<script src="<?= PLX_CORE ?>lib/functions.js?v=<?= PLX_VERSION ?>"></script>
	<script src="<?= PLX_CORE ?>lib/visual.js?v=<?= PLX_VERSION ?>"></script>
	<script src="<?= PLX_CORE ?>lib/mediasManager.js?v=<?= PLX_VERSION ?>"></script>
	<script defer src="<?= PLX_CORE ?>lib/multifiles.js?v=<?= PLX_VERSION ?>"></script>
	<script src="<?= PLX_CORE ?>lib/vue.js"></script>
<?php
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminTopEndHead'));
?>
</head>
<body id="<?= basename($_SERVER['SCRIPT_NAME'], ".php") ?>">
<main id="app" class="main">
	<aside id="aside" class="aside w15">
		<header class="asideheader">
			<h1 class="h4-like txtcenter"><?= PlxUtils::strCheck($plxAdmin->aConf['title']) ?></h1>
			<ul class="unstyled">
				<?php if(isset($plxAdmin->aConf['homestatic']) AND !empty($plxAdmin->aConf['homestatic'])) : ?>
				<li>
					<a class="back-blog" href="<?= $plxAdmin->urlRewrite('?blog'); ?>" title="<?= L_BACK_TO_BLOG_TITLE ?>"><i class="icon-left-open"></i><?= L_BACK_TO_BLOG;?></a>
				</li>
				<?php endif; ?>
				<li>
					<a class="back-site" href="<?= PLX_ROOT ?>" title="<?= L_BACK_TO_SITE_TITLE ?>"><i class="icon-left-open"></i><?= L_BACK_TO_SITE;?></a>
				</li>
			</ul>
		</header>
		<nav class="responsive-menu">
			<label for="nav"><?php echo L_MENU ?></label>
			<input type="checkbox" id="nav">
			<ul id="responsive-menu" class="unstyled">
<?php
					$menus = array();
					$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '[0-9]{3}' : $_SESSION['user']);
					$nbartsmod = $plxAdmin->nbArticles('all', $userId, '_');
					$arts_mod = $nbartsmod>0 ? '&nbsp;<span class="tag--primary" onclick="window.location=\''.PLX_CORE.'admin/index.php?sel=mod&amp;page=1\';return false;">'.$nbartsmod.'</span>':'';
					$menus[] = PlxUtils::formatMenu('<i class="icon-doc-inv"></i>'.'Tableau de bord', PLX_CORE.'admin/index.php?page=1', 'Tableau de bord (lang)', false, false);
					$menus[] = PlxUtils::formatMenu('<i class="icon-doc-inv"></i>'.L_MENU_ARTICLES, PLX_CORE.'admin/articles.php?page=1', L_MENU_ARTICLES_TITLE, false, false,$arts_mod);

					if(isset($_GET['a'])) # edition article
						$menus[] = PlxUtils::formatMenu('<i class="icon-pencil"></i>'.L_MENU_NEW_ARTICLES_TITLE, PLX_CORE.'admin/article.php', L_MENU_NEW_ARTICLES, false, false, '', false);
					else # nouvel article
						$menus[] = PlxUtils::formatMenu('<i class="icon-pencil"></i>'.L_MENU_NEW_ARTICLES_TITLE, PLX_CORE.'admin/article.php', L_MENU_NEW_ARTICLES);

						$menus[] = PlxUtils::formatMenu('<i class="icon-camera"></i>'.L_MENU_MEDIAS, PLX_CORE.'admin/medias.php', L_MENU_MEDIAS_TITLE);

					if($_SESSION['profil'] <= PROFIL_MANAGER)
						$menus[] = PlxUtils::formatMenu('<i class="icon-doc-text-inv"></i>'.L_MENU_STATICS, PLX_CORE.'admin/pages.php', L_MENU_STATICS_TITLE);

					if($_SESSION['profil'] <= PROFIL_MODERATOR) {
						$nbcoms = $plxAdmin->nbComments('offline');
						$coms_offline = $nbcoms>0 ? '&nbsp;<span class="tag--primary" onclick="window.location=\''.PLX_CORE.'admin/comments.php?sel=offline&amp;page=1\';return false;">'.$plxAdmin->nbComments('offline').'</span>':'';
						$menus[] = PlxUtils::formatMenu('<i class="icon-comment"></i>'.L_MENU_COMMENTS, PLX_CORE.'admin/comments.php?page=1', L_MENU_COMMENTS_TITLE, false, false, $coms_offline);
					}

					if($_SESSION['profil'] <= PROFIL_EDITOR)
						$menus[] = PlxUtils::formatMenu('<i class="icon-tags"></i>'.L_MENU_CATEGORIES, PLX_CORE.'admin/categories.php', L_MENU_CATEGORIES_TITLE);

						$menus[] = PlxUtils::formatMenu('<i class="icon-user-1"></i>'.L_MENU_PROFIL, PLX_CORE.'admin/profil.php', L_MENU_PROFIL_TITLE);

					if($_SESSION['profil'] == PROFIL_ADMIN) {
						$menus[] = PlxUtils::formatMenu('<i class="icon-sliders"></i>'.L_MENU_CONFIG, PLX_CORE.'admin/parametres_base.php', L_MENU_CONFIG_TITLE, false, false, '', false);
						if (preg_match('/parametres/',basename($_SERVER['SCRIPT_NAME']))) {
							$menus[] = PlxUtils::formatMenu(L_MENU_CONFIG_BASE, PLX_CORE.'admin/parametres_base.php', L_MENU_CONFIG_BASE_TITLE, 'submenu');
							$menus[] = PlxUtils::formatMenu(L_MENU_CONFIG_VIEW, PLX_CORE.'admin/parametres_affichage.php', L_MENU_CONFIG_VIEW_TITLE, 'submenu');
							$menus[] = PlxUtils::formatMenu(L_MENU_CONFIG_USERS, PLX_CORE.'admin/parametres_users.php', L_MENU_CONFIG_USERS_TITLE, 'submenu');
							$menus[] = PlxUtils::formatMenu(L_MENU_CONFIG_ADVANCED, PLX_CORE.'admin/parametres_avances.php', L_MENU_CONFIG_ADVANCED_TITLE, 'submenu');
							$menus[] = PlxUtils::formatMenu(L_THEMES, PLX_CORE.'admin/parametres_themes.php', L_THEMES_TITLE, 'submenu');
							$menus[] = PlxUtils::formatMenu(L_MENU_CONFIG_PLUGINS, PLX_CORE.'admin/parametres_plugins.php', L_MENU_CONFIG_PLUGINS_TITLE, 'submenu');
							$menus[] = PlxUtils::formatMenu(L_MENU_CONFIG_INFOS, PLX_CORE.'admin/parametres_infos.php', L_MENU_CONFIG_INFOS_TITLE, 'submenu');
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
		<div class="plxversion"><a title="PluXml" href="<?= PLX_URL_REPO ?>"><small>PluXml <?= $plxAdmin->aConf['version'] ?></small></a></div>
	</aside>
	<section class="section grid-1">
		<header class="header">
			<div class="txtright">
				<ul class="unstyled">
					<li class="badge" ><a href="profil.php"><img src="theme/images/pluxml.png"/></a></li>
					<li>
						<a href="profil.php"><?= PlxUtils::strCheck($plxAdmin->aUsers[$_SESSION['user']]['name']) ?></a>&nbsp;
						<small><em><?php
							if ($_SESSION ['profil'] == PROFIL_ADMIN) echo L_PROFIL_ADMIN;
							elseif ($_SESSION ['profil'] == PROFIL_MANAGER) echo L_PROFIL_MANAGER;
							elseif ($_SESSION ['profil'] == PROFIL_MODERATOR) echo L_PROFIL_MODERATOR;
							elseif ($_SESSION ['profil'] == PROFIL_EDITOR) echo L_PROFIL_EDITOR;
							else echo L_PROFIL_WRITER;
						?></em></small>
					</li>
					<li><a href="<?= PLX_CORE ?>admin/auth.php?d=1" title="<?= L_ADMIN_LOGOUT_TITLE ?>"><i class="icon-logout"></i></a></li>
				</ul>
			</div>
		</header>
