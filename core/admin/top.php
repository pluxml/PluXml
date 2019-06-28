<?php if(!defined('PLX_ROOT')) exit; ?>
<?php
if(isset($_GET["del"]) AND $_GET["del"]=="install") {
	if(@unlink(PLX_ROOT.'install.php'))
		plxMsg::Info(L_DELETE_SUCCESSFUL);
	else
		plxMsg::Error(L_DELETE_FILE_ERR.' install.php');
	header("Location: index.php");
	exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $plxAdmin->aConf['default_lang'] ?>">
<head>
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
	<meta name="robots" content="noindex, nofollow" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET) ?>" />
	<title><?php echo plxUtils::strCheck($plxAdmin->aConf['title']) ?> - <?php echo L_ADMIN ?></title>
	<link rel="stylesheet" type="text/css" href="theme/css/knacss.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="theme/css/theme.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="theme/fonts/fontello.css?ver=<?php echo PLX_VERSION ?>" media="screen" />
	<link rel="icon" href="theme/images/favicon.png" />
	<?php if(is_file(PLX_ROOT.$plxAdmin->aConf['custom_admincss_file'])) echo '<link rel="stylesheet" type="text/css" href="'.PLX_ROOT.$plxAdmin->aConf['custom_admincss_file'].'" media="screen" />'."\n" ?>
	<?php
	if(file_exists(PLX_ROOT.$plxAdmin->aConf['racine_plugins'].'admin.css'))
		echo '<link rel="stylesheet" type="text/css" href="'.PLX_ROOT.$plxAdmin->aConf['racine_plugins'].'admin.css" media="screen" />'."\n";
	?>
	<script src="theme/js/functions.js?ver=<?php echo PLX_VERSION ?>"></script>
	<script src="theme/js/visual.js?ver=<?php echo PLX_VERSION ?>"></script>
	<script src="theme/js/mediasManager.js?ver=<?php echo PLX_VERSION ?>"></script>
	<script defer src="theme/js/multifiles.js?ver=<?php echo PLX_VERSION ?>"></script>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminTopEndHead')) ?>
</head>

<body id="<?php echo basename($_SERVER['SCRIPT_NAME'], ".php") ?>">

<?php include __DIR__ .'/profil-nav.php'; ?>

<main class="main grid">

	<aside class="aside col sml-12 med-3 lrg-2 sml-text-left">
		<nav class="responsive-menu">
			<label for="nav"><?php echo L_MENU ?></label>
			<input type="checkbox" id="nav" />
			<ul id="responsive-menu" class="menu vertical expanded">
				<?php
					$menus = array();
					$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '[0-9]{3}' : $_SESSION['user']);

					$menus[] = plxUtils::formatMenu(L_DASHBOARD_TITLE, PLX_CORE.'admin/index.php?page=1', L_MENU_ARTICLES_TITLE, false, false, $arts_mod, '<i class="ico icon-th-large"></i>');

					$nbartsmod = $plxAdmin->nbArticles('all', $userId, '_');
					$arts_mod = $nbartsmod>0 ? '<span class="badge" onclick="window.location=\''.PLX_CORE.'admin/index.php?sel=mod&amp;page=1\';return false;">'.$nbartsmod.'</span>':'';
					$menus[] = plxUtils::formatMenu(L_MENU_ARTICLES, PLX_CORE.'admin/articles.php?page=1', L_MENU_ARTICLES_TITLE, false, false, $arts_mod, '<i class="ico icon-doc-inv"></i>');

					if(isset($_GET['a'])) # edition article
					    $menus[] = plxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, PLX_CORE.'admin/article.php', L_MENU_NEW_ARTICLES, false, false, '', '<i class="ico icon-plus-circled"></i>', false);
					else # nouvel article
					    $menus[] = plxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, PLX_CORE.'admin/article.php', L_MENU_NEW_ARTICLES, false, false, '', '<i class="ico icon-plus-circled"></i>');

					    $menus[] = plxUtils::formatMenu(L_MENU_MEDIAS, PLX_CORE.'admin/medias.php', L_MENU_MEDIAS_TITLE, false, false, '', '<i class="ico icon-picture"></i>');

					if($_SESSION['profil'] <= PROFIL_MANAGER)
					    $menus[] = plxUtils::formatMenu(L_MENU_STATICS, PLX_CORE.'admin/statiques.php', L_MENU_STATICS_TITLE, false, false, '', '<i class="ico icon-doc-text-inv"></i>');

					if($_SESSION['profil'] <= PROFIL_MODERATOR) {
						$nbcoms = $plxAdmin->nbComments('offline');
						$coms_offline = $nbcoms>0 ? '<span class="badge" onclick="window.location=\''.PLX_CORE.'admin/comments.php?sel=offline&amp;page=1\';return false;">'.$plxAdmin->nbComments('offline').'</span>':'';
						$menus[] = plxUtils::formatMenu(L_MENU_COMMENTS, PLX_CORE.'admin/comments.php?page=1', L_MENU_COMMENTS_TITLE, false, false, $coms_offline, '<i class="ico icon-comment"></i>');
					}

					if($_SESSION['profil'] <= PROFIL_EDITOR)
					    $menus[] = plxUtils::formatMenu(L_MENU_CATEGORIES, PLX_CORE.'admin/categories.php', L_MENU_CATEGORIES_TITLE, false, false, '', '<i class="ico icon-tag"></i>');

					    $menus[] = plxUtils::formatMenu(L_MENU_PROFIL, PLX_CORE.'admin/profil.php', L_MENU_PROFIL_TITLE, false, false, '', '<i class="ico icon-user"></i>');

					if($_SESSION['profil'] == PROFIL_ADMIN) {
						$menus[] = plxUtils::formatMenu(L_MENU_CONFIG, PLX_CORE.'admin/parametres_base.php', L_MENU_CONFIG_TITLE, false, false, '', '<i class="ico icon-sliders"></i>', false);
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
								    $menu = plxUtils::formatMenu(plxUtils::strCheck($plugInstance->adminMenu['title']), PLX_CORE.'admin/plugin.php?p='.$plugName, plxUtils::strCheck($plugInstance->adminMenu['caption']), false, false, '', '<i class="ico icon-cog"></i>');
									if($plugInstance->adminMenu['position']!='')
										array_splice($menus, ($plugInstance->adminMenu['position']-1), 0, $menu);
									else
										$menus[] = $menu;
								} else {
								    $menus[] = plxUtils::formatMenu(plxUtils::strCheck($plugInstance->getInfo('title')), PLX_CORE.'admin/plugin.php?p='.$plugName, plxUtils::strCheck($plugInstance->getInfo('title')), false, false, '', '<i class="ico icon-cog"></i>');
								}
							}
						}
					}

					# Hook Plugins
					eval($plxAdmin->plxPlugins->callHook('AdminTopMenus'));
					echo implode('', $menus);
				?>
			</ul>
		   	<ul class="unstyled-list">
   				<li>
   					<small><a class="version" title="PluXml" href="http://www.pluxml.org">PluXml <?php echo $plxAdmin->aConf['version'] ?></a></small>
   					<?php 
                        $maj = $plxAdmin->checkMaj();
                        if ($maj['maj'] == true) {
                            echo '<small><span class="label"><a href="/core/admin/parametres_infos.php">New</a></span></small>';
                        }
   					?>
   				</li>
   			</ul>

		</nav>
	</aside>

	<section class="section col sml-12 med-9 med-offset-3 lrg-10 lrg-offset-2">

		<?php
		if(is_file(PLX_ROOT.'install.php')) echo '<p class="alert red">'.L_WARNING_INSTALLATION_FILE.'</p>';
		plxMsg::Display();
		?>

		<?php eval($plxAdmin->plxPlugins->callHook('AdminTopBottom')) ?>
